<?php

/**
 * Condition Model
 *
 * This class handles the processing of an individual form condition.
 *
 * @package     Ninja Forms - Conditional Logic
 * @subpackage  Conditions
 * @author      Kyle B. Johnson
 * @copyright   Copyright (c) 2016, The WP Ninjas
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0.0
 */

class NF_ConditionalLogic_ConditionModel
{
    private $when = array();
    private $then = array();
    private $else = array();
    private $fields;
    private $data;
    private $result;

    public function __construct( $condition, &$fieldsCollection, $data = array(), $default = true )
    {
        if( isset( $condition[ 'when' ] ) ) {
            $this->when = $condition[ 'when' ];
        }

        if( isset( $condition[ 'then' ] ) ) {
            $this->then = $condition[ 'then' ];
        }

        if( isset( $condition[ 'else' ] ) ) {
            $this->else = $condition[ 'else' ];
        }

        $this->result = $default;

        $this->fields = $fieldsCollection;
        $this->data = $data;
    }

    public function process()
    {
        array_walk( $this->when, array( $this, 'compare' ) );
        $result = array_reduce( $this->when, array( $this, 'evaluate' ), $this->result );

        $triggers = ( $result ) ? $this->then : $this->else;
        array_map( array( $this, 'trigger' ), $triggers );

        return $result;
    }

    private function compare( &$when )
    {
        if( ! $when[ 'key' ] ) return;

        /**
         * This was originally written only with fields in mind.
         * To handle calcs, we are using the Calcs Merge Tag global (since the values aren't passed in).
         */
        $comparison_target = $when[ 'value' ];

        switch( $when[ 'type' ] ){
            case 'field':
                $fieldModel = $this->fields->get_field( $when[ 'key' ] );
                $value = $this->fields->get_field_original_value( $fieldModel->get_id() );
                // If we have a checkbox....
                if( 'checkbox'  == $fieldModel->get_setting( 'type' ) ) {
                    // Check the value and change it to check or unchecked.
                    if( 0 == $value ) {
                        $value = 'unchecked';
                    } else {
                        $value = 'checked';
                    }
                } else if ( 'date' == $fieldModel->get_setting( 'type' ) ) {
                    /*
                     * Turn our date into a timestamp.
                     */
                    $date_mode = $fieldModel->get_setting( 'date_mode' );

                    if ( 'time_only' === $date_mode ) {
                        // Time Only fields submit their value as a plain string (e.g. "07:00 pm"
                        // for 12-hour fields, "14:30" for 24-hour fields) — the same shape as a
                        // date_only submission, but it's a time, not a date. is_array() can't tell
                        // these apart, so it must be branched on date_mode explicitly, or this
                        // string gets fed to normalize_date_to_iso()/strtotime() as if it were a
                        // date, which always fails and always evaluates the comparison false.
                        $date = '1970/01/01';
                        $hour = '00';
                        $minute = '00';

                        if ( ! empty( $value ) && preg_match( '/^(\d{1,2}):(\d{2})\s*(am|pm)?$/i', trim( $value ), $matches ) ) {
                            $hour = $matches[ 1 ];
                            $minute = $matches[ 2 ];

                            if ( ! empty( $matches[ 3 ] ) ) {
                                $hour = $this->convert_to_24_hour( $hour, strtolower( $matches[ 3 ] ) );
                            }
                        }
                    } else if ( is_array( $value ) ) { // date_and_time
                        if ( empty( $value[ 'date' ] ) ) {
                            $date = '1970/01/01';
                        } else {
                            $date = $this->normalize_date_to_iso( $value[ 'date' ], $fieldModel );
                        }

                        if ( empty( $value[ 'hour' ] ) ) {
                            $hour = '00';
                        } else {
                            $hour = $value[ 'hour' ];
                        }

                        if ( empty ( $value[ 'minute' ] ) ) {
                            $minute = '00';
                        } else {
                            $minute = $value[ 'minute' ];
                        }

                        // 12-hour clock fields (hours_24 = 0) submit hour + ampm separately;
                        // strtotime() needs 24-hour time, so convert before building the date string.
                        if ( ! empty( $value[ 'ampm' ] ) ) {
                            $hour = $this->convert_to_24_hour( $hour, $value[ 'ampm' ] );
                        }
                    } else { // date_only
                        $date = empty( $value ) ? '1970/01/01' : $this->normalize_date_to_iso( $value, $fieldModel );
                        $hour = '00';
                        $minute = '00';
                    }

                    $date_string = $date. ' ' . $hour . ':' . $minute;
                    $value = strtotime( $date_string );

                    // The stored condition target may have been created while the field
                    // was in a different date_mode (e.g. a time baked in from Date and Time
                    // mode, now that the field is Date Only). Rather than compare against a
                    // component the current mode no longer captures, disregard whichever part
                    // (date or time-of-day) isn't relevant to the mode in effect right now.
                    $value = $this->normalize_for_date_mode( $value, $date_mode );
                    $comparison_target = $this->normalize_for_date_mode( $comparison_target, $date_mode );
                }
                break;
            case 'calc':
                try {
                    $value = Ninja_Forms()->merge_tags[ 'calcs' ]->get_calc_value( $when[ 'key' ] );
                }catch( Exception $e ){
                    $value = false;
                }
                break;
            default:
                $value = false;
        }

        $when[ 'result' ] = NF_ConditionalLogic()->comparator( $when[ 'comparator' ] )->compare( $value, $comparison_target );
    }

    /**
     * Disregard whichever component (date, or time-of-day) the field's current
     * date_mode doesn't actually capture, so a condition's stored target value
     * remains meaningful even if it was originally set while the field was in a
     * different date_mode (e.g. a time-of-day baked in from Date and Time mode,
     * now that the field has been switched to Date Only).
     *
     * @param int|false $epoch     Unix timestamp to normalize.
     * @param string    $date_mode Field's current date_mode setting.
     * @return int|false Normalized timestamp, or the original value unchanged if not an int.
     */
    private function normalize_for_date_mode( $epoch, $date_mode )
    {
        if ( ! is_int( $epoch ) ) {
            return $epoch;
        }

        if ( 'date_only' === $date_mode ) {
            // Ignore time-of-day; compare by calendar day only.
            return $epoch - ( $epoch % DAY_IN_SECONDS );
        }

        if ( 'time_only' === $date_mode ) {
            // Ignore whatever date may be baked in; compare by time-of-day only.
            return $epoch % DAY_IN_SECONDS;
        }

        return $epoch;
    }

    /**
     * Convert a date field's raw submitted value into Y/m/d order, parsing it
     * according to the field's own date_format setting rather than assuming
     * YYYY-MM-DD. Falls back to the original string unchanged if it doesn't
     * match the expected format, preserving prior behavior for that case.
     *
     * @param string $date_string Raw value straight from the submission.
     * @param object $fieldModel  Field model for the date field.
     * @return string Y/m/d date string, or the original value if it couldn't be parsed.
     */
    private function normalize_date_to_iso( $date_string, $fieldModel )
    {
        if ( empty( $date_string ) ) {
            return $date_string;
        }

        $php_format = $this->get_php_date_format( $fieldModel->get_setting( 'date_format' ) );

        $parsed = DateTime::createFromFormat( '!' . $php_format, $date_string );

        $errors = DateTime::getLastErrors();
        if ( ! $parsed || ! empty( $errors[ 'warning_count' ] ) || ! empty( $errors[ 'error_count' ] ) ) {
            return $date_string;
        }

        return $parsed->format( 'Y/m/d' );
    }

    /**
     * Map a Ninja Forms date_format setting (e.g. "DD/MM/YYYY", "default") to
     * the equivalent PHP date() format string (e.g. "d/m/Y").
     *
     * @param string $format Value of the field's date_format setting.
     * @return string PHP date() format string.
     */
    private function get_php_date_format( $format )
    {
        $lookup = array(
            'MM/DD/YYYY'        => 'm/d/Y',
            'MM-DD-YYYY'        => 'm-d-Y',
            'MM.DD.YYYY'        => 'm.d.Y',
            'DD/MM/YYYY'        => 'd/m/Y',
            'DD-MM-YYYY'        => 'd-m-Y',
            'DD.MM.YYYY'        => 'd.m.Y',
            'YYYY-MM-DD'        => 'Y-m-d',
            'YYYY/MM/DD'        => 'Y/m/d',
            'YYYY.MM.DD'        => 'Y.m.d',
            'dddd, MMMM D YYYY' => 'l, F d Y',
        );

        if ( isset( $lookup[ $format ] ) ) {
            return $lookup[ $format ];
        }

        if ( empty( $format ) || 'default' == $format ) {
            $default_format = Ninja_Forms()->get_setting( 'date_format' );
            return ! empty( $default_format ) ? $default_format : 'm/d/Y';
        }

        return $format;
    }

    /**
     * Convert a 12-hour clock hour + am/pm indicator into 24-hour form,
     * matching the conversion whenModel.js already performs client-side.
     *
     * @param string $hour Hour value ("01"-"12").
     * @param string $ampm "am" or "pm".
     * @return string 24-hour hour value ("00"-"23").
     */
    private function convert_to_24_hour( $hour, $ampm )
    {
        if ( 'pm' == $ampm && '12' != $hour ) {
            return (string) ( (int) $hour + 12 );
        }

        if ( 'am' == $ampm && '12' == $hour ) {
            return '00';
        }

        return $hour;
    }

    private function evaluate( $current, $when )
    {
        if( ! isset( $when[ 'result' ] ) ) return true;
        return ( 'AND' == $when[ 'connector' ] ) ? $current && $when[ 'result' ] : $current || $when[ 'result' ];
    }

    private function trigger( $trigger )
    {
        $triggerModel = NF_ConditionalLogic()->trigger( $trigger[ 'trigger' ] );

        if( ! $triggerModel ) return;

        switch( $trigger[ 'type' ] ) {
            case 'field':
                $target = $this->fields->get_field( $trigger['key'] );
                break;
            default:
                $target = apply_filters( 'ninja_forms_conditional_logic_trigger_type_' . $trigger[ 'type' ], $trigger[ 'key' ], $this->data );
        }

        if( ! $target ) return;

        $triggerModel->process( $target, $this->fields, $this->data );
    }

}

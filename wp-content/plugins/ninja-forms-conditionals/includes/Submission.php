<?php

class NF_ConditionalLogic_Submission
{
    private $fieldsCollection;

    public function __construct()
    {
        add_filter( 'ninja_forms_submit_data', array( $this, 'parse_fields' ) );
        add_filter( 'ninja_forms_pre_validate_field_settings', array( $this, 'before_validate_field' ) );

        add_filter( 'ninja_forms_submission_actions', array( $this, 'parse_actions' ), 10, 2 );
        add_filter( 'ninja_forms_submission_actions_preview', array( $this, 'parse_actions' ), 10, 2 );
    }

    public function parse_fields( $data )
    {
        // If we don't have a form ID, return early.
        if ( ! isset ( $data[ 'id' ] ) || empty( $data[ 'id' ] ) ) {
            return $data;
        }

        if ( ( isset( $data[ 'settings' ][ 'is_preview' ] ) && ! empty( $data[ 'settings' ][ 'is_preview' ] ) )
            && current_user_can( apply_filters( 'ninja_forms_admin_all_forms_capabilities', 'manage_options' ) ) 
        ) {
            $is_preview = true;
        } else {
            $is_preview = false;
        }

        // If the user is in Preview Mode, grab conditions from the submitted data. Otherwise, pull data from the DB.
        if ( $is_preview ) {
            $form_id = esc_html( $data[ 'id' ] );
            $form_settings = $data[ 'settings' ];
        } else {
            $form_id = absint( $data[ 'id' ] );
            // Grab conditions from form settings rather than relying on $data, as that's merged with user submitted data.
            $form_settings = Ninja_Forms()->form( $form_id )->get_settings();
        }

        // Make sure we build the fieldsCollection, in case there are conditionally triggered Actions.
        $this->fieldsCollection = new NF_ConditionalLogic_FieldsCollection( $data[ 'fields' ], $data[ 'id' ], $is_preview );

        // If we don't have any conditions set on this form, return $data.
        if( ! isset( $form_settings[ 'conditions' ] ) || empty( $form_settings[ 'conditions' ] ) ) {
            return $data;
        }

        // CL processes on ninja_forms_submit_data, before NF core computes calculations.
        // Pre-compute calc values here so calc-based conditions can evaluate correctly.
        $this->pre_process_calcs( $form_settings, $data );

        foreach( $data[ 'settings' ][ 'conditions' ] as $condition ){
            $condition = new NF_ConditionalLogic_ConditionModel( $condition, $this->fieldsCollection, $data );
            $condition->process();
        }

        $this->fieldsCollection = apply_filters( 'ninja_forms_conditional_logic_parse_fields', $this->fieldsCollection );   
        $data[ 'fields' ] = $this->fieldsCollection->to_array();

        return $data;
    }

    public function before_validate_field( $field_settings )
    {
        // Handle the conditionally_required flag set by HideField/ShowField triggers.
        if( isset( $field_settings[ 'conditionally_required' ] ) ) {
            $field_settings[ 'required' ] = $field_settings[ 'conditionally_required' ];
            unset( $field_settings[ 'conditionally_required' ] );
        }

        // A field hidden by Conditional Logic should never block submission, whether
        // or not a separate "unset required" action was also configured on the same
        // rule — this mirrors validateRequired.js, which already skips required
        // validation for any field with visible:false client-side, unconditionally.
        //
        // $field_settings itself can't be trusted for 'visible' here: NF core's
        // submission whitelist (added for #8011) strips everything except
        // value/files/save_id when merging submitted data onto the DB-sourced field
        // settings, before this filter ever runs. 'visible' is a runtime condition
        // result, not a stored setting, so it never survives that merge for plain
        // fields. Look it up in our own FieldsCollection instead — same source
        // is_parent_repeater_hidden() below already relies on for the same reason.
        if ( $this->is_field_hidden( $field_settings ) ) {
            if ( ! empty( $field_settings[ 'required' ] ) ) {
                $field_settings[ 'required' ] = false;
            }

            // Bypass custom field-type validation for hidden confirmation fields.
            // When a passwordconfirm or confirm field is hidden, its value is cleared to false.
            // Without this bypass, the password/email matching validation still runs and fails.
            $fields_with_custom_validation = array( 'passwordconfirm', 'confirm' );
            if( isset( $field_settings[ 'type' ] ) && in_array( $field_settings[ 'type' ], $fields_with_custom_validation ) ) {
                $field_settings[ 'type' ] = 'textbox';
            }
        }
        // Bypass required validation for child fields inside a hidden repeater.
        // They aren't standalone entries in the FieldsCollection at all (nested
        // inside the parent Repeater's 'fields' setting instead), so the check
        // above can't find them by ID — kept as a fallback.
        // Repeater children are validated individually by NF core but are not
        // present as standalone entries in the FieldsCollection — they are nested
        // inside the parent Repeater's 'fields' setting. When a Repeater is hidden
        // by conditional logic, only the parent gets conditionally_required=false;
        // children retain required=1 and block submission unless we intercept here.
        if ( ! empty( $field_settings[ 'required' ] )
             && ! empty( $this->fieldsCollection )
             && $this->is_parent_repeater_hidden( $field_settings ) ) {
            $field_settings[ 'required' ] = false;
        }

        return $field_settings;
    }

    /**
     * Check whether a field itself has been hidden by conditional logic.
     *
     * $field_settings can't be trusted for this — core's submission whitelist
     * strips 'visible' before before_validate_field() runs. Look it up directly
     * in our own FieldsCollection, which reflects the real-time result of this
     * request's own condition processing (parse_fields(), run earlier on
     * ninja_forms_submit_data).
     *
     * @param  array $field_settings Settings for the field being validated.
     * @return bool True if this field has been conditionally hidden.
     */
    private function is_field_hidden( array $field_settings ): bool
    {
        if ( empty( $this->fieldsCollection ) ) {
            return false;
        }

        $field_id = $field_settings[ 'id' ] ?? null;
        if ( empty( $field_id ) ) {
            return false;
        }

        $tracked_fields = $this->fieldsCollection->to_array();
        if ( ! isset( $tracked_fields[ $field_id ] ) ) {
            return false;
        }

        $tracked = $tracked_fields[ $field_id ];
        return isset( $tracked[ 'visible' ] ) && false === $tracked[ 'visible' ];
    }

    /**
     * Check whether a field is a child of a Repeater that has been hidden by
     * conditional logic.
     *
     * Repeater child fields are not standalone entries in the FieldsCollection —
     * they are stored inside the parent Repeater's 'fields' setting. This method
     * finds the field's ID in the child definitions of any hidden Repeater,
     * which correctly scopes the check to the specific parent even when a form
     * contains multiple Repeater fields.
     *
     * @param  array $field_settings Settings for the field being validated.
     * @return bool True if a parent repeater for this field is hidden.
     */
    private function is_parent_repeater_hidden( array $field_settings ): bool
    {
        $field_id = $field_settings[ 'id' ] ?? null;
        if ( empty( $field_id ) ) {
            return false;
        }

        foreach ( $this->fieldsCollection->to_array() as $potential_parent ) {
            // Only consider Repeater-type fields.
            if ( ! isset( $potential_parent[ 'type' ] ) || 'repeater' !== $potential_parent[ 'type' ] ) {
                continue;
            }

            // Only consider Repeaters that have been conditionally hidden.
            // HideField sets visible = false (boolean); if not set, the field is visible.
            if ( ! isset( $potential_parent[ 'visible' ] ) || false !== $potential_parent[ 'visible' ] ) {
                continue;
            }

            // Check whether this field is a direct child of this hidden Repeater.
            if ( empty( $potential_parent[ 'fields' ] ) || ! is_array( $potential_parent[ 'fields' ] ) ) {
                continue;
            }

            foreach ( $potential_parent[ 'fields' ] as $child_def ) {
                if ( isset( $child_def[ 'id' ] ) && $child_def[ 'id' ] === $field_id ) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Pre-compute calculation values so that calc-based conditions can evaluate
     * correctly on the server side.
     *
     * NF core computes calculations inside process(), which runs after the
     * ninja_forms_submit_data filter where CL hooks. Field merge tags are also
     * not yet registered at that point. We resolve {field:key} tags manually
     * from FieldsCollection, then apply the ninja_forms_calc_setting filter to
     * handle any {calc:name} cross-references from previously computed calcs.
     *
     * @param array $form_settings  Form settings from the DB (includes 'calculations').
     * @param array $data           Full form submission data.
     */
    protected function pre_process_calcs( $form_settings, $data )
    {
        if ( empty( $form_settings[ 'calculations' ] ) ) {
            return;
        }

        $calcs_merge_tags = Ninja_Forms()->merge_tags[ 'calcs' ];

        // Build a field key => submitted value map from the FieldsCollection.
        $fields_by_key = array();
        foreach ( $this->fieldsCollection->to_array() as $field ) {
            if ( isset( $field[ 'key' ] ) ) {
                $fields_by_key[ $field[ 'key' ] ] = isset( $field[ 'value' ] ) ? (string) $field[ 'value' ] : '';
            }
        }

        $decimal_point = isset( $data[ 'settings' ][ 'decimal_point' ] ) ? $data[ 'settings' ][ 'decimal_point' ] : '.';
        $thousands_sep  = isset( $data[ 'settings' ][ 'thousands_sep' ] )  ? $data[ 'settings' ][ 'thousands_sep' ]  : ',';

        foreach ( $form_settings[ 'calculations' ] as $calc ) {
            $eq = $calc[ 'eq' ];

            // Replace {field:key} tags with submitted values.
            // NF core's field merge tags are not registered yet at this hook priority.
            foreach ( $fields_by_key as $field_key => $field_value ) {
                $eq = str_replace( '{field:' . $field_key . '}', $field_value, $eq );
            }

            // Apply the calc setting filter to resolve any {calc:name} cross-references
            // from calcs already processed in this loop.
            $eq = apply_filters( 'ninja_forms_calc_setting', $eq );

            // Scrub remaining unresolved merge tags (deleted/non-existent fields or calcs).
            $eq = preg_replace( '/{([a-zA-Z0-9]|:|_|-)*}/', '0', $eq );

            $dec = ( isset( $calc[ 'dec' ] ) && '' !== $calc[ 'dec' ] ) ? $calc[ 'dec' ] : 2;

            $calcs_merge_tags->set_merge_tags( $calc[ 'name' ], $eq, $dec, $decimal_point, $thousands_sep );
        }
    }

    public function parse_actions( $actions, $form_data )
    {
        // If we don't have a fieldsCollection (such as if this is a resume) skip this filter.
        if ( ! empty( $this->fieldsCollection ) ) {
            array_walk( $actions, array( $this, 'parse_action' ), $this->fieldsCollection );
        }

        return $actions;
    }

    public function parse_action( &$action, $key, $fieldsCollection )
    {
        $return = $this->exitParseAction($action);

        if($return){
            return;
        }

        $action_condition = ( is_object( $action ) ) ? $action->get_setting( 'conditions' ) : $action[ 'settings' ][ 'conditions' ];

        if( ! $action_condition ) return;

        unset( $action_condition[ 'then' ] );
        unset( $action_condition[ 'else' ] );

        $valid_conditions = array(); 

        $input_field_types = array(
            'address',
            'address2',
            'confirm',
            'date',
            'email',
            'firstname',
            'lastname',
            'number',
            'password',
            'passwordconfirm',
            'phone', 
            'textbox',
            'textarea',
            'hidden',
        );

        $field_collection_array = $this->fieldsCollection->to_array();

        foreach( $action_condition[ 'when' ] as &$when ){
            $when[ 'connector' ] = ( 'all' == $action_condition[ 'connector' ] ) ? 'AND' : 'OR';
            $field_type = $this->get_field_type( $when[ 'key' ], $field_collection_array  );
            
            // Input field conditions are valid, even with empty values
            $is_input_field = in_array( $field_type, $input_field_types );
            $is_valid = $this->validate_action_when_value( $when );
           
            if ( $is_input_field || $is_valid ) {
                $valid_conditions[] = $when; 
            }
        }

        // Merge our valid conditions back into our action conditions array. 
        $action_condition[ 'when' ] = $valid_conditions;

        $default = ( 'all' == $action_condition[ 'connector' ] );

        $condition = new NF_ConditionalLogic_ConditionModel( $action_condition, $fieldsCollection, array(), $default );
        $result = $condition->process();

        if( 1 != $action_condition[ 'process' ] ) {
            $result = ! $result;
        }

        if( is_object( $action ) ){
            $action->update_setting( 'active', $result );
        } else {
            $action[ 'settings' ][ 'active' ] = $result;
        }
    }

    /**
     * Stop parse_action function because required array keys aren't set?
     *
     * @param array $action
     * @return boolean
     */
    protected function exitParseAction(array $action): bool
    {
        $return = true;

        if (! isset($action['settings']['active']) || ! $action['settings']['active']) {
            return $return;
        }

        if (
            !isset($action['settings']['conditions'])
            || !isset($action['settings']['conditions']['process'])
        ) {
            return $return;
        }

        // Checks to see if conditional setting is in the default position and returns early if it is. 
        if (
            0 !== intval($action['settings']['conditions']['process'])
            && 1 !== intval($action['settings']['conditions']['process'])
        ) {

            return $return;
        }

        if( empty( $action[ 'settings' ][ 'conditions' ][ 'when' ] ) ){

            return $return;
        } 

        return false;
    }

    /**
     * Validates if our when condition has a valid value. 
     * 
     * @return bool
     */
    public function validate_action_when_value( $when_condition ) 
    {
        if( empty( $when_condition[ 'value' ] ) ) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Compares our field key to our field model to get the field type.
     * 
     * @return string - field type. 
     */
    public function get_field_type( $field_key, $field_models ) 
    {
        foreach( $field_models as $field ) {
            if( $field_key == $field[ 'key' ] ) {
                return $field[ 'type' ];
            }
        }  
    }
}

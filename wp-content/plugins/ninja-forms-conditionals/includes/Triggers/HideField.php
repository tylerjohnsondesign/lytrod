<?php

final class NF_ConditionalLogic_Triggers_HideField implements NF_ConditionalLogic_Trigger
{
    public function process( &$field, &$fieldCollection, &$data )
    {
        $value = $field->get_setting( 'value' );

        // Repeater fields store child data as an array. NF core iterates over
        // the value with foreach(), so setting it to false causes PHP warnings.
        // Use an empty array to signal "no submitted data" without breaking iteration.
        $hidden_value = ( 'repeater' === $field->get_setting( 'type' ) ) ? [] : false;
        $field->update_setting( 'value', $hidden_value );
        $field->update_setting( 'submitted_value', $value );
        $field->update_setting( 'visible', false );

        // Hidden fields should NOT be validated for required.
        if( 1 == $field->get_setting( 'required' ) ) {

            // Set bypass flag.
            $field->update_setting( 'conditionally_required', false );
        }
    }
}
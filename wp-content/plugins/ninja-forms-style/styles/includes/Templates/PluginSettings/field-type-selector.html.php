<div class="nf-styles-admin-field-type">
    <label for="ninja-forms-styles-field-type-selector"><?php esc_html_e( 'Field type', 'ninja-forms-layout-styles' ); ?></label>
    <select name="" id="ninja-forms-styles-field-type-selector">
        <option value=""><?php echo esc_html__( 'Select a Field Type', 'ninja-forms-layout-styles' ); ?></option>
        <?php foreach( Ninja_Forms()->fields as $field ): ?>
            <?php if( in_array( $field->get_type(), array( 'hidden', 'note', 'creditcard', 'unknown' ) ) ) continue; ?>
            <option value="<?php echo esc_attr( $field->get_name() ); ?>" <?php selected( isset( $_GET[ 'field_type' ] ) ? WPN_Helper::sanitize_text_field( $_GET[ 'field_type' ] ) : '', $field->get_name() ); ?>><?php echo esc_html( $field->get_nicename() ); ?></option>
        <?php endforeach; ?>
    </select>
</div>

<?php
$setting = $data[ 'setting' ];
$control = $data[ 'control' ];
$value = $data[ 'value' ];
$is_editable = $data[ 'is_editable' ];
$label = $control[ 'label' ];
$color = $is_editable && preg_match( '/^#[0-9a-f]{6}$/i', $value ) ? $value : '#ffffff';
?>
    <div class="nf-styles-visual-swatch nf-styles-admin-control<?php echo $value && ! $is_editable ? ' has-css-value' : ''; ?>">
        <span class="nf-styles-visual-swatch-label"><?php echo esc_html( $label ); ?></span>
        <span class="dashicons dashicons-admin-appearance nf-styles-visual-color-icon" aria-hidden="true"></span>
        <?php if ( $value && ! $is_editable ) : ?><span class="nf-styles-css-value"><?php esc_html_e( 'CSS', 'ninja-forms-layout-styles' ); ?></span><?php endif; ?>
        <button type="button" class="nf-styles-visual-swatch-chip<?php echo $value && $is_editable ? '' : ' is-empty'; ?>" style="background-color: <?php echo esc_attr( $color ); ?>" aria-expanded="false" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: color control label (e.g. Background, Text, Border) */ __( 'Choose %s color', 'ninja-forms-layout-styles' ), $label ) ); ?>" title="<?php echo esc_attr( sprintf( /* translators: %s: color control label (e.g. Background, Text, Border) */ __( 'Choose %s color', 'ninja-forms-layout-styles' ), $label ) ); ?>"<?php disabled( $value && ! $is_editable ); ?>></button>
        <button type="button" class="nf-styles-visual-reset<?php echo $value ? '' : ' is-empty'; ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: color control label (e.g. Background, Text, Border) */ __( 'Clear %s color', 'ninja-forms-layout-styles' ), $label ) ); ?>" title="<?php echo esc_attr( sprintf( /* translators: %s: color control label (e.g. Background, Text, Border) */ __( 'Clear %s color', 'ninja-forms-layout-styles' ), $label ) ); ?>" onclick="ninjaFormsStyles.resetColorControl(this)">×</button>
        <input type="hidden" class="nf-styles-admin-value" name="<?php echo esc_attr( $view->get_field_name( $setting ) ); ?>" id="<?php echo esc_attr( $view->get_field_id( $setting ) ); ?>" value="<?php echo esc_attr( $value ); ?>">
        <input type="color" class="nf-styles-visual-color nf-styles-admin-color" tabindex="-1" aria-hidden="true" id="<?php echo esc_attr( $view->get_field_id( $setting ) ); ?>_picker" value="<?php echo esc_attr( $color ); ?>" oninput="ninjaFormsStyles.updateColorControl(this)" onchange="ninjaFormsStyles.updateColorControl(this)"<?php echo $value && ! $is_editable ? ' data-css-managed="true"' : ''; ?><?php disabled( $value && ! $is_editable ); ?>>
    </div>

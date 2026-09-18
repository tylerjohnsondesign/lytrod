<?php
$setting = $data[ 'setting' ];
$control = $data[ 'control' ];
$value = $data[ 'value' ];
$is_editable = $data[ 'is_editable' ];
$unit = $data[ 'unit' ];
$range = $data[ 'range' ];
preg_match( '/-?\d+(\.\d+)?/', $is_editable ? $value : '', $match );
$number = isset( $match[0] ) ? $match[0] : $control[ 'default' ];
$display = $value ? ( $is_editable ? $value : __( 'CSS', 'ninja-forms-layout-styles' ) ) : __( 'Default', 'ninja-forms-layout-styles' );
?>
    <div class="nf-styles-visual-control nf-styles-visual-control-range nf-styles-admin-control<?php echo $value && ! $is_editable ? ' has-css-value' : ''; ?>">
        <div class="nf-styles-visual-range-header">
            <label for="<?php echo esc_attr( $view->get_field_id( $setting ) ); ?>_range"><?php echo esc_html( $control[ 'label' ] ); ?></label>
            <span class="nf-styles-visual-control-actions">
                <?php if ( $value && $is_editable ) : ?>
                    <span class="nf-styles-visual-value nf-styles-visual-value-editable"><span class="nf-styles-visual-value-input nf-styles-admin-value-input" contenteditable="true" inputmode="numeric" role="spinbutton" aria-valuemin="<?php echo esc_attr( $range[ 'min' ] ); ?>" aria-valuemax="<?php echo esc_attr( $range[ 'max' ] ); ?>" aria-valuenow="<?php echo esc_attr( $number ); ?>" data-unit="<?php echo esc_attr( $unit ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: control label (e.g. Font size, Padding) */ __( 'Set %s', 'ninja-forms-layout-styles' ), $control[ 'label' ] ) ); ?>"><?php echo esc_html( $number ); ?></span><span class="nf-styles-visual-value-unit"><?php echo esc_html( $unit ); ?></span></span>
                <?php else : ?>
                    <span class="nf-styles-visual-value<?php echo $value ? '' : ' is-default'; ?>"><?php echo esc_html( $display ); ?></span>
                <?php endif; ?>
                <?php $view->get_part( 'visual-control-unit', array( 'control' => $control, 'unit' => $unit, 'disabled' => $value && ! $is_editable ) ); ?>
                <button type="button" class="nf-styles-visual-reset<?php echo $value ? '' : ' is-empty'; ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: control label (e.g. Font size, Padding, Border style) */ __( 'Clear %s', 'ninja-forms-layout-styles' ), $control[ 'label' ] ) ); ?>" title="<?php echo esc_attr( sprintf( /* translators: %s: control label (e.g. Font size, Padding, Border style) */ __( 'Clear %s', 'ninja-forms-layout-styles' ), $control[ 'label' ] ) ); ?>">×</button>
            </span>
        </div>
        <input type="hidden" class="nf-styles-admin-value" name="<?php echo esc_attr( $view->get_field_name( $setting ) ); ?>" id="<?php echo esc_attr( $view->get_field_id( $setting ) ); ?>" value="<?php echo esc_attr( $value ); ?>">
        <input type="range" class="nf-styles-visual-range nf-styles-admin-range" id="<?php echo esc_attr( $view->get_field_id( $setting ) ); ?>_range" min="<?php echo esc_attr( $range[ 'min' ] ); ?>" max="<?php echo esc_attr( $range[ 'max' ] ); ?>" step="<?php echo esc_attr( $range[ 'step' ] ); ?>" value="<?php echo esc_attr( $number ); ?>" data-unit="<?php echo esc_attr( $unit ); ?>" data-default-value="<?php echo esc_attr( $control[ 'default' ] ); ?>"<?php echo $value && ! $is_editable ? ' data-css-managed="true"' : ''; ?><?php disabled( $value && ! $is_editable ); ?>>
    </div>

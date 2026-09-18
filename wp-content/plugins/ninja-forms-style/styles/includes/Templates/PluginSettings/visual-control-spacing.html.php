<?php
$setting = $data[ 'setting' ];
$control = $data[ 'control' ];
$value = $data[ 'value' ];
$is_editable = $data[ 'is_editable' ];
$unit = $data[ 'unit' ];
$range = $data[ 'range' ];
$parts = preg_split( '/\s+/', trim( $is_editable ? $value : '' ) );
$top = isset( $parts[0] ) && '' !== $parts[0] ? preg_replace( '/[^0-9.-]/', '', $parts[0] ) : 0;
$right = isset( $parts[1] ) ? preg_replace( '/[^0-9.-]/', '', $parts[1] ) : $top;
$bottom = isset( $parts[2] ) ? preg_replace( '/[^0-9.-]/', '', $parts[2] ) : $top;
$left = isset( $parts[3] ) ? preg_replace( '/[^0-9.-]/', '', $parts[3] ) : $right;
$is_split = $is_editable && 1 < count( array_filter( $parts ) );
$side_labels = array(
    'top'    => __( 'Top', 'ninja-forms-layout-styles' ),
    'right'  => __( 'Right', 'ninja-forms-layout-styles' ),
    'bottom' => __( 'Bottom', 'ninja-forms-layout-styles' ),
    'left'   => __( 'Left', 'ninja-forms-layout-styles' ),
);
?>
    <div class="nf-styles-visual-control nf-styles-visual-control-spacing nf-styles-admin-control<?php echo $is_split ? ' is-split' : ''; ?><?php echo $value && ! $is_editable ? ' has-css-value' : ''; ?>">
        <div class="nf-styles-visual-range-header">
            <label for="<?php echo esc_attr( $view->get_field_id( $setting ) ); ?>_range"><?php echo esc_html( $control[ 'label' ] ); ?></label>
            <span class="nf-styles-visual-control-actions">
                <?php if ( $value && $is_editable && ! $is_split ) : ?>
                    <span class="nf-styles-visual-value nf-styles-visual-value-editable"><span class="nf-styles-visual-value-input nf-styles-admin-value-input" contenteditable="true" inputmode="numeric" role="spinbutton" aria-valuemin="<?php echo esc_attr( $range[ 'min' ] ); ?>" aria-valuemax="<?php echo esc_attr( $range[ 'max' ] ); ?>" aria-valuenow="<?php echo esc_attr( $top ); ?>" data-unit="<?php echo esc_attr( $unit ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: control label (e.g. Font size, Padding) */ __( 'Set %s', 'ninja-forms-layout-styles' ), $control[ 'label' ] ) ); ?>"><?php echo esc_html( $top ); ?></span><span class="nf-styles-visual-value-unit"><?php echo esc_html( $unit ); ?></span></span>
                <?php else : ?>
                    <span class="nf-styles-visual-value<?php echo $value ? '' : ' is-default'; ?>"><?php echo esc_html( $value ? ( $is_editable ? $value : __( 'CSS', 'ninja-forms-layout-styles' ) ) : __( 'Default', 'ninja-forms-layout-styles' ) ); ?></span>
                <?php endif; ?>
                <?php $view->get_part( 'visual-control-unit', array( 'control' => $control, 'unit' => $unit, 'disabled' => $value && ! $is_editable ) ); ?>
                <button type="button" class="nf-styles-visual-reset<?php echo $value ? '' : ' is-empty'; ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: control label (e.g. Font size, Padding, Border style) */ __( 'Clear %s', 'ninja-forms-layout-styles' ), $control[ 'label' ] ) ); ?>" title="<?php echo esc_attr( sprintf( /* translators: %s: control label (e.g. Font size, Padding, Border style) */ __( 'Clear %s', 'ninja-forms-layout-styles' ), $control[ 'label' ] ) ); ?>">×</button>
            </span>
        </div>
        <input type="hidden" class="nf-styles-admin-value" name="<?php echo esc_attr( $view->get_field_name( $setting ) ); ?>" id="<?php echo esc_attr( $view->get_field_id( $setting ) ); ?>" value="<?php echo esc_attr( $value ); ?>">
        <input type="range" class="nf-styles-visual-range nf-styles-visual-spacing-all nf-styles-admin-spacing-all" id="<?php echo esc_attr( $view->get_field_id( $setting ) ); ?>_range" min="<?php echo esc_attr( $range[ 'min' ] ); ?>" max="<?php echo esc_attr( $range[ 'max' ] ); ?>" step="<?php echo esc_attr( $range[ 'step' ] ); ?>" value="<?php echo esc_attr( $top ); ?>" data-unit="<?php echo esc_attr( $unit ); ?>" data-default-value="<?php echo esc_attr( $control[ 'min' ] ); ?>"<?php echo $value && ! $is_editable ? ' data-css-managed="true"' : ''; ?><?php disabled( $value && ! $is_editable ); ?>>
        <button type="button" class="nf-styles-visual-sides-toggle" aria-expanded="<?php echo $is_split ? 'true' : 'false'; ?>"<?php echo $value && ! $is_editable ? ' data-css-managed="true"' : ''; ?><?php disabled( $value && ! $is_editable ); ?>><?php esc_html_e( 'Sides', 'ninja-forms-layout-styles' ); ?></button>
        <div class="nf-styles-visual-sides">
            <?php foreach ( array( 'top' => $top, 'right' => $right, 'bottom' => $bottom, 'left' => $left ) as $side => $side_value ) : ?>
                <label class="nf-styles-visual-side">
                    <span><?php echo esc_html( $side_labels[ $side ] ); ?></span>
                    <input class="nf-styles-visual-side-range nf-styles-admin-side-range" type="range" min="<?php echo esc_attr( $range[ 'min' ] ); ?>" max="<?php echo esc_attr( $range[ 'max' ] ); ?>" step="<?php echo esc_attr( $range[ 'step' ] ); ?>" value="<?php echo esc_attr( $side_value ); ?>" data-side="<?php echo esc_attr( $side ); ?>" data-unit="<?php echo esc_attr( $unit ); ?>"<?php echo $value && ! $is_editable ? ' data-css-managed="true"' : ''; ?><?php disabled( $value && ! $is_editable ); ?>>
                </label>
            <?php endforeach; ?>
        </div>
    </div>

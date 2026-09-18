<?php
$control = $data[ 'control' ];
$unit = $data[ 'unit' ];
$disabled = $data[ 'disabled' ];
$units = isset( $control[ 'units' ] ) ? $control[ 'units' ] : array( $control[ 'unit' ] );

if ( 2 > count( $units ) ) {
    return;
}
?>
    <select class="nf-styles-visual-unit nf-styles-admin-unit" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: control label (e.g. Font size, Width) */ __( '%s unit', 'ninja-forms-layout-styles' ), $control[ 'label' ] ) ); ?>"<?php echo $disabled ? ' data-css-managed="true"' : ''; ?><?php disabled( $disabled ); ?>>
        <?php foreach ( $units as $option ) : ?>
            <?php $range = isset( $control[ 'unit_options' ][ $option ] ) ? $control[ 'unit_options' ][ $option ] : array(); ?>
            <option value="<?php echo esc_attr( $option ); ?>" data-min="<?php echo esc_attr( isset( $range[ 'min' ] ) ? $range[ 'min' ] : $control[ 'min' ] ); ?>" data-max="<?php echo esc_attr( isset( $range[ 'max' ] ) ? $range[ 'max' ] : $control[ 'max' ] ); ?>" data-step="<?php echo esc_attr( isset( $range[ 'step' ] ) ? $range[ 'step' ] : 1 ); ?>"<?php selected( $option, $unit ); ?>><?php echo esc_html( $option ); ?></option>
        <?php endforeach; ?>
    </select>

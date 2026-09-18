<?php
$setting_name = $data[ 'setting_name' ];
$setting = $data[ 'setting' ];
$control = $data[ 'control' ];
$value = $data[ 'value' ];
$is_editable = $data[ 'is_editable' ];
?>
    <div class="nf-styles-visual-control nf-styles-visual-control-segmented nf-styles-admin-control<?php echo $value && ! $is_editable ? ' has-css-value' : ''; ?>">
        <div class="nf-styles-visual-range-header">
            <span class="nf-styles-visual-label"><?php echo esc_html( $control[ 'label' ] ); ?></span>
            <span class="nf-styles-visual-control-actions">
                <?php if ( $value && ! $is_editable ) : ?><span class="nf-styles-css-value"><?php esc_html_e( 'CSS', 'ninja-forms-layout-styles' ); ?></span><?php endif; ?>
                <button type="button" class="nf-styles-visual-reset<?php echo $value ? '' : ' is-empty'; ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: control label (e.g. Font size, Padding, Border style) */ __( 'Clear %s', 'ninja-forms-layout-styles' ), $control[ 'label' ] ) ); ?>" title="<?php echo esc_attr( sprintf( /* translators: %s: control label (e.g. Font size, Padding, Border style) */ __( 'Clear %s', 'ninja-forms-layout-styles' ), $control[ 'label' ] ) ); ?>">×</button>
            </span>
        </div>
        <input type="hidden" class="nf-styles-admin-value" name="<?php echo esc_attr( $view->get_field_name( $setting ) ); ?>" id="<?php echo esc_attr( $view->get_field_id( $setting ) ); ?>" value="<?php echo esc_attr( $value ); ?>">
        <div class="nf-styles-visual-segments">
            <?php foreach ( $control[ 'options' ] as $option ) : ?>
                <button type="button" class="nf-styles-visual-segment<?php echo $is_editable && $value === $option[ 'value' ] ? ' is-active' : ''; ?>" data-value="<?php echo esc_attr( $option[ 'value' ] ); ?>"<?php echo $value && ! $is_editable ? ' data-css-managed="true"' : ''; ?><?php disabled( $value && ! $is_editable ); ?>>
                    <span class="nf-styles-visual-segment-label"><?php echo esc_html( $option[ 'label' ] ); ?></span>
                    <?php if ( 'border-style' === $setting_name ) : ?>
                        <span class="nf-styles-visual-line-preview<?php echo $option[ 'value' ] ? ' is-' . esc_attr( $option[ 'value' ] ) : ' is-none'; ?>"></span>
                    <?php endif; ?>
                </button>
            <?php endforeach; ?>
        </div>
    </div>

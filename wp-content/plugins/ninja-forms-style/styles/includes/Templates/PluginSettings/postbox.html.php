<?php
$settings = isset( $data[ 'settings' ] ) ? $data[ 'settings' ] : array();
$setting_map = array();
foreach ( $settings as $setting ) {
    $setting_map[ $setting[ 'name' ] ] = $setting;
}

// Paired contract: the Config/VisualControls config and these validation helpers
// ( $is_visual_value_editable, $get_value_unit, $get_unit_range, $is_unit_value,
// $is_spacing_value ) mirror the settings/isEditableVisualValue/getValueUnit/
// getUnitRangeConfig/isUnitValue/isSpacingValue logic in
// layouts/assets/js/builder/controllers/stylesVisualEditor.js. Unit ranges,
// editability, and spacing rules must be kept in sync across these files.
$visual_controls = NF_Styles::config( 'VisualControls' );

$hover_controls = array( 'background-color', 'color', 'border-color' );
$is_hover_section = isset( $data[ 'name' ] ) && false !== strpos( (string) $data[ 'name' ], 'hover' );

$visual_groups = array(
    array(
        'label'         => __( 'Color', 'ninja-forms-layout-styles' ),
        'content_class' => 'nf-styles-visual-swatch-grid',
        'controls'      => array( 'background-color', 'color' ),
    ),
    array(
        'label'         => __( 'Type and Size', 'ninja-forms-layout-styles' ),
        'content_class' => 'nf-styles-visual-two-col',
        'controls'      => array( 'font-size', 'width', 'height' ),
    ),
    array(
        'label'         => __( 'Spacing', 'ninja-forms-layout-styles' ),
        'content_class' => 'nf-styles-visual-two-col',
        'controls'      => array( 'padding', 'margin' ),
    ),
    array(
        'label'    => __( 'Border', 'ninja-forms-layout-styles' ),
        'controls' => array( 'border-style' ),
        'nested'   => array(
            array(
                'content_class' => 'nf-styles-visual-two-col',
                'controls'      => array( 'border', 'border-color' ),
            ),
        ),
    ),
);

$should_render_setting = function( $setting_name ) use ( $setting_map, $is_hover_section, $hover_controls ) {
    return isset( $setting_map[ $setting_name ] ) && ( ! $is_hover_section || in_array( $setting_name, $hover_controls, true ) );
};

$group_has_controls = function( $group ) use ( $should_render_setting ) {
    foreach ( $group[ 'controls' ] as $setting_name ) {
        if ( $should_render_setting( $setting_name ) ) {
            return true;
        }
    }

    foreach ( isset( $group[ 'nested' ] ) ? $group[ 'nested' ] : array() as $nested_group ) {
        foreach ( $nested_group[ 'controls' ] as $setting_name ) {
            if ( $should_render_setting( $setting_name ) ) {
                return true;
            }
        }
    }

    return false;
};

$get_control_units = function( $control ) {
    return isset( $control[ 'units' ] ) ? $control[ 'units' ] : array( $control[ 'unit' ] );
};

$get_value_unit = function( $value, $control ) use ( $get_control_units ) {
    $parts = preg_split( '/\s+/', trim( (string) $value ) );
    $parts = array_filter( $parts, 'strlen' );

    foreach ( $get_control_units( $control ) as $unit ) {
        $matches = true;
        foreach ( $parts as $part ) {
            if ( ! preg_match( '/^-?\d+(\.\d+)?' . preg_quote( $unit, '/' ) . '$/', trim( (string) $part ) ) ) {
                $matches = false;
                break;
            }
        }

        if ( $matches && ! empty( $parts ) ) {
            return $unit;
        }
    }

    return $control[ 'unit' ];
};

$get_unit_range = function( $control, $unit ) {
    $range = isset( $control[ 'unit_options' ][ $unit ] ) ? $control[ 'unit_options' ][ $unit ] : array();

    return array(
        'min'  => isset( $range[ 'min' ] ) ? $range[ 'min' ] : $control[ 'min' ],
        'max'  => isset( $range[ 'max' ] ) ? $range[ 'max' ] : $control[ 'max' ],
        'step' => isset( $range[ 'step' ] ) ? $range[ 'step' ] : 1,
    );
};

$is_unit_value = function( $value, $unit ) {
    return (bool) preg_match( '/^-?\d+(\.\d+)?' . preg_quote( $unit, '/' ) . '$/', trim( (string) $value ) );
};

$is_supported_unit_value = function( $value, $control ) use ( $is_unit_value, $get_control_units ) {
    foreach ( $get_control_units( $control ) as $unit ) {
        if ( $is_unit_value( $value, $unit ) ) {
            return true;
        }
    }

    return false;
};

$is_spacing_value = function( $value, $control ) use ( $is_unit_value, $get_control_units ) {
    $parts = preg_split( '/\s+/', trim( (string) $value ) );
    $parts = array_filter( $parts, 'strlen' );

    if ( empty( $parts ) || 4 < count( $parts ) ) {
        return false;
    }

    foreach ( $get_control_units( $control ) as $unit ) {
        $matches = true;
        foreach ( $parts as $part ) {
            if ( ! $is_unit_value( $part, $unit ) ) {
                $matches = false;
                break;
            }
        }

        if ( $matches ) {
            return true;
        }
    }

    return false;
};

$is_visual_value_editable = function( $setting_name, $value ) use ( $visual_controls, $is_supported_unit_value, $is_spacing_value ) {
    if ( ! $value || ! isset( $visual_controls[ $setting_name ] ) ) {
        return true;
    }

    $control = $visual_controls[ $setting_name ];

    if ( 'color' === $control[ 'type' ] ) {
        return (bool) preg_match( '/^#[0-9a-f]{6}$/i', $value );
    }

    if ( 'range' === $control[ 'type' ] ) {
        return $is_supported_unit_value( $value, $control );
    }

    if ( 'spacing' === $control[ 'type' ] ) {
        return $is_spacing_value( $value, $control );
    }

    if ( 'segmented' === $control[ 'type' ] ) {
        foreach ( $control[ 'options' ] as $option ) {
            if ( $value === $option[ 'value' ] ) {
                return true;
            }
        }

        return false;
    }

    return true;
};

$has_css_only_values = function() use ( $view, $setting_map, $visual_controls, $is_visual_value_editable ) {
    foreach ( $setting_map as $setting_name => $setting ) {
        $value = $view->get_field_value( $setting );

        if ( isset( $visual_controls[ $setting_name ] ) ) {
            if ( $value && ! $is_visual_value_editable( $setting_name, $value ) ) {
                return true;
            }

            continue;
        }

        if ( 'show_advanced_css' === $setting_name ) {
            continue;
        }

        if ( $value ) {
            return true;
        }
    }

    return false;
};

$render_control = function( $setting_name ) use ( $view, $visual_controls, $setting_map, $should_render_setting, $is_visual_value_editable, $get_value_unit, $get_unit_range ) {
    if ( ! $should_render_setting( $setting_name ) ) return;
    if ( ! isset( $visual_controls[ $setting_name ] ) ) return;

    $control = $visual_controls[ $setting_name ];

    if ( ! in_array( $control[ 'type' ], array( 'color', 'range', 'spacing', 'segmented' ), true ) ) return;

    $setting = $setting_map[ $setting_name ];
    $value = $view->get_field_value( $setting );
    $is_editable = $is_visual_value_editable( $setting_name, $value );

    $part_data = array(
        'setting_name' => $setting_name,
        'setting'      => $setting,
        'control'      => $control,
        'value'        => $value,
        'is_editable'  => $is_editable,
    );

    if ( 'range' === $control[ 'type' ] || 'spacing' === $control[ 'type' ] ) {
        $unit = $is_editable ? $get_value_unit( $value, $control ) : $control[ 'unit' ];
        $part_data[ 'unit' ]  = $unit;
        $part_data[ 'range' ] = $get_unit_range( $control, $unit );
    }

    $view->get_part( 'visual-control-' . $control[ 'type' ], $part_data );
};


?>

<?php $section_id = isset( $data[ 'name' ] ) ? 'nf-styles-section-' . sanitize_html_class( $data[ 'name' ] ) : uniqid( 'nf-styles-section-' ); ?>

<section class="postbox nf-styles-admin-section is-collapsed is-design-mode">
    <button class="nf-styles-admin-section-header" type="button" aria-expanded="false" aria-controls="<?php echo esc_attr( $section_id ); ?>">
        <span><?php echo esc_html( $data[ 'label' ] ); ?></span>
        <span class="nf-styles-admin-section-icon" aria-hidden="true"></span>
    </button>
    <div id="<?php echo esc_attr( $section_id ); ?>" class="inside nf-styles-section-body" hidden>
        <div class="nf-styles-section-controls">
            <div class="nf-styles-mode-control">
                <span class="nf-setting-label nf-styles-mode-label">
                    <?php esc_html_e( 'CSS mode', 'ninja-forms-layout-styles' ); ?>
                    <span class="nf-help-wrap">
                        <button type="button" class="nf-help" aria-label="<?php esc_attr_e( 'About CSS mode', 'ninja-forms-layout-styles' ); ?>"><span class="dashicons dashicons-admin-comments" aria-hidden="true"></span></button>
                        <div class="nf-help-text"><div><?php esc_html_e( 'CSS mode shows the original advanced style fields for users who want direct CSS-style control.', 'ninja-forms-layout-styles' ); ?></div></div>
                    </span>
                </span>
                <div class="nf-styles-mode-toggle">
                    <input type="checkbox" id="<?php echo esc_attr( $section_id ); ?>-mode" class="nf-toggle nf-styles-mode-switch" data-style-mode-switch aria-label="<?php esc_attr_e( 'CSS mode', 'ninja-forms-layout-styles' ); ?>">
                    <label class="nf-styles-mode-switch-label" for="<?php echo esc_attr( $section_id ); ?>-mode"><?php esc_html_e( 'CSS mode', 'ninja-forms-layout-styles' ); ?></label>
                    <?php if ( $has_css_only_values() ) : ?>
                        <span class="nf-styles-css-active"><?php esc_html_e( 'CSS values active', 'ninja-forms-layout-styles' ); ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <button type="button" class="nf-styles-reset-section" title="<?php esc_attr_e( 'Reset styles in this section', 'ninja-forms-layout-styles' ); ?>"><?php esc_html_e( 'Reset styles', 'ninja-forms-layout-styles' ); ?></button>
        </div>

        <div class="nf-styles-visual-editor" data-style-mode-panel="design">
            <?php foreach ( $visual_groups as $group ) : ?>
                <?php if ( ! $group_has_controls( $group ) ) continue; ?>
                <section class="nf-styles-visual-group">
                    <div class="nf-styles-visual-group-title"><?php echo esc_html( $group[ 'label' ] ); ?></div>
                    <div class="nf-styles-visual-group-content <?php echo esc_attr( isset( $group[ 'content_class' ] ) ? $group[ 'content_class' ] : '' ); ?>">
                        <?php foreach ( $group[ 'controls' ] as $setting_name ) : ?>
                            <?php $render_control( $setting_name ); ?>
                        <?php endforeach; ?>

                        <?php foreach ( isset( $group[ 'nested' ] ) ? $group[ 'nested' ] : array() as $nested_group ) : ?>
                            <div class="<?php echo esc_attr( $nested_group[ 'content_class' ] ); ?>">
                                <?php foreach ( $nested_group[ 'controls' ] as $setting_name ) : ?>
                                    <?php $render_control( $setting_name ); ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endforeach; ?>
        </div>

        <div class="nf-styles-css-editor" data-style-mode-panel="css">
            <table class="form-table">
                <?php
                    foreach( $data[ 'settings' ] as $setting ) {
                        echo $view->get_part('postbox-content', $setting );
                    }
                ?>
            </table>
        </div>
    </div>
</section>

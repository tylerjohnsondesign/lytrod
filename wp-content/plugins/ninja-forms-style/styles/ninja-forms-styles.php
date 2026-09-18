<?php if ( ! defined( 'ABSPATH' ) ) exit;

if( class_exists( 'NF_Styles', false ) ) return;

/**
 * Class NF_Styles
 */
final class NF_Styles
{
    const VERSION = '3.0.29';
    const SLUG    = 'styles';
    const NAME    = 'Styles';
    const AUTHOR  = 'WP Ninjas';
    const PREFIX  = 'NF_Styles';
    const FIELD_STYLES_CACHE_VERSION = '3';

    /**
     * @var NF_Layouts
     * @since 3.0
     */
    private static $instance;

    /**
     * Plugin Directory
     *
     * @since 3.0
     * @var string $dir
     */
    public static $dir = '';

    /**
     * Plugin URL
     *
     * @since 3.0
     * @var string $url
     */
    public static $url = '';

    /**
     * NF_Layouts constructor.
     */
    public function __construct()
    {
        add_action( 'ninja_forms_loaded', array( $this, 'ninja_forms_loaded' ) );

        add_action( 'ninja_forms_before_container', array( $this, 'localize_plugin_styles' ), 10, 3 );
        add_action( 'ninja_forms_before_container_preview', array( $this, 'localize_plugin_styles' ), 10, 3 );

        add_action( 'ninja_forms_before_container', array( $this, 'localize_form_styles' ), 10, 3 );
        add_action( 'ninja_forms_before_container_preview', array( $this, 'localize_form_styles' ), 10, 3 );

        add_action( 'ninja_forms_before_container', array( $this, 'localize_field_styles' ), 10, 3 );
        add_action( 'ninja_forms_before_container_preview', array( $this, 'localize_field_styles' ), 10, 3 );

        add_filter( 'ninja_forms_from_settings_types', array( $this, 'add_form_settings_groups' ) );
        add_filter( 'ninja_forms_localize_form_styles_settings', array( $this, 'add_form_settings' ) );

        add_filter( 'ninja_forms_field_settings_groups', array( $this, 'add_field_settings_groups' ) );
        add_filter( 'ninja_forms_field_load_settings', array( $this, 'add_field_settings' ), 10, 3 );


        add_filter( 'ninja_forms_styles_output_rule_border', array( $this, 'filter_output_rule_border' ) );
        add_filter( 'ninja_forms_styles_apply_standard_units', array( $this, 'apply_standard_units' ) );

        add_action( 'ninja_forms_save_form', array( $this, 'bust_form_styles_cache' ), 10, 1 );
        add_action( 'ninja_forms_save_form', array( $this, 'bust_field_styles_cache' ), 10, 1 );
        add_action( 'ninja_forms_save_form_preview', array( $this, 'bust_form_styles_cache' ), 10, 1 );
        add_action( 'ninja_forms_save_form_preview', array( $this, 'bust_field_styles_cache' ), 10, 1 );
        add_action( 'ninja_forms_styles_update_styles', array( $this, 'bust_plugin_styles_cache' ), 10, 1 );

        /*
         * Add a filter for importing/exporting plugin-wide styles
         */
        add_filter( 'ninja_forms_import_export_tabs', array( $this, 'add_export_tab' ) );
        /*
         * Listen for importing/exporting
         */
        add_action( 'admin_init', array( $this, 'import_export' ) );


        /*
         * Multi-Part Integration
         */
        add_filter( 'ninja_forms_multi_part_advanced_settings', array( $this, 'add_multi_part_settings' ) );
    }

    public function ninja_forms_loaded()
    {
        new NF_Styles_Admin_Submenu();
    }

    /**
     * Build the color palette exposed to the visual style controls.
     *
     * Reads colors from the active theme's theme.json global settings and
     * add_theme_support( 'editor-color-palette' ), validating and de-duplicating
     * each entry into name/slug/color items.
     *
     * @return (Array) List of palette colors as associative arrays.
     */
    public static function get_theme_color_palette()
    {
        $palette = array();
        $groups = array();

        if ( function_exists( 'wp_get_global_settings' ) ) {
            $settings = wp_get_global_settings();
            if ( isset( $settings[ 'color' ][ 'palette' ] ) && is_array( $settings[ 'color' ][ 'palette' ] ) ) {
                $groups = $settings[ 'color' ][ 'palette' ];
            }
        }

        $theme_support = get_theme_support( 'editor-color-palette' );
        if ( empty( $groups ) && is_array( $theme_support ) && isset( $theme_support[0] ) && is_array( $theme_support[0] ) ) {
            $groups[ 'theme' ] = $theme_support[0];
        }

        if ( ! empty( $groups[ 'theme' ] ) ) {
            $groups = array( 'theme' => $groups[ 'theme' ] );
        } elseif ( ! empty( $groups[ 'custom' ] ) ) {
            $groups = array( 'custom' => $groups[ 'custom' ] );
        } elseif ( ! empty( $groups[ 'default' ] ) ) {
            $groups = array( 'default' => $groups[ 'default' ] );
        }

        foreach ( $groups as $origin => $colors ) {
            if ( ! is_array( $colors ) ) {
                continue;
            }

            foreach ( $colors as $color ) {
                if ( empty( $color[ 'color' ] ) || ! is_string( $color[ 'color' ] ) ) {
                    continue;
                }

                if ( ! preg_match( '/^#[0-9a-f]{3}([0-9a-f]{3})?$/i', $color[ 'color' ] ) ) {
                    continue;
                }

                $palette[] = array(
                    'name' => isset( $color[ 'name' ] ) ? sanitize_text_field( $color[ 'name' ] ) : '',
                    'slug' => isset( $color[ 'slug' ] ) ? sanitize_title( $color[ 'slug' ] ) : '',
                    'color' => sanitize_hex_color( $color[ 'color' ] ),
                    'origin' => sanitize_key( $origin ),
                );
            }
        }

        return array_values( array_filter( $palette, function( $color ) {
            return ! empty( $color[ 'color' ] );
        } ) );
    }

    public function add_form_settings_groups( $groups )
    {
        $new_groups = self::config( 'FormSettingsGroups' );
        $groups = array_merge( $groups, $new_groups );
        return $groups;
    }

    public function add_form_settings( $settings )
    {
        $form_settings = self::config( 'FormSettings' );

        foreach( $form_settings as $name => $form_setting ){
            $form_setting[ 'group' ] = 'primary';

            foreach( self::config( 'CommonSettings' ) as $common_setting ){

                switch( $name ){
                    case 'container_styles':
                        $blacklist = array( 'color', 'font-size', 'width' );
                        break;
                    case 'row_styles':
                    case 'odd_styles':
                        $blacklist = array( 'height', 'color' );
                        break;
                    case 'error_msg_styles':
                        $blacklist = array( 'height' );
                        break;
                    default:
                        $blacklist = array();
                }

                if( in_array( $common_setting[ 'name' ], $blacklist ) ) continue;

                if( 'float' == $common_setting[ 'name' ] && in_array( $form_setting[ 'name' ], array( 'row_styles', 'row-odd_styles', 'success-msg_styles', 'error_msg_styles' ) ) ){
                    continue;
                }

                $common_setting[ 'name' ] = $name . '_' . $common_setting[ 'name' ];

                if ( isset ( $common_setting[ 'deps' ] ) ) {
                    foreach( $common_setting[ 'deps' ] as $dep_name => $val ) {
                        $common_setting[ 'deps' ][ $name . '_' . $dep_name ] = $val;
                        unset( $common_setting[ 'deps' ][ $dep_name ] );
                    }
                }

                $form_setting[ 'settings' ][] = $common_setting;
            }

            $form_settings[ $name ] = $form_setting;
        }

        $settings = array_merge( $settings, $form_settings );
        return $settings;
    }

    public function add_field_settings_groups( $groups )
    {
        return $groups = array_merge( $groups, self::config( 'FieldSettingsGroups' ) );
    }

    public function add_field_settings( $settings, $field_type, $field_parent_type )
    {
        $style_settings = self::config( 'FieldSettings' );

        if( 'list' == $field_parent_type ){
            if( 'listselect' != $field_type && 'listmultiselect' != $field_type ) {
                $style_settings = array_merge( $style_settings, self::config( 'ListFieldSettings' ) );
            }
        }
        
        if('starrating' == $field_type ){
            $style_settings = array_merge( $style_settings, self::config( 'RatingFieldSettings' ) );
        }

        if( in_array( $field_type, array( 'button', 'submit' ), true ) ){
            $style_settings = array_merge( $style_settings, self::config( 'ButtonFieldSettings' ) );
            if( isset( $style_settings[ 'label_styles' ] ) ){
                unset( $style_settings[ 'label_styles' ] );
            }
        }

	    $style_settings = apply_filters( 'ninja_forms_styles_field_settings', $style_settings, $field_type, $field_parent_type );

        foreach( $style_settings as $name => $style_setting ){

            $style_setting[ 'group' ] = 'styles';

            if( 'recaptcha' == $field_type && 'element_styles' == $name ) continue;
            if( 'hr' == $field_type && 'label_styles' == $name ) continue;

            foreach( self::config( 'CommonSettings' ) as $common_setting ){

                switch( $name ){
                    case 'wrap_styles':
                        $blacklist = array( 'color', 'font-size', 'height' );
                        break;
                    case 'label_styles':
                    case 'element_styles':
                    case 'submit_element_hover_styles':
                        $blacklist = array( 'height' );
                        break;
                    default:
                        $blacklist = array();
                }

                if( 'hr' == $field_type && 'element_styles' == $name ){
                    $blacklist = array_merge( $blacklist, array( 'color', 'font-size', 'padding') );
                    if( isset( $blacklist[ 'height' ] ) ) unset( $blacklist[ 'height' ] );
                }

                if( in_array( $common_setting[ 'name' ], $blacklist ) ) continue;

                $common_setting[ 'name' ] = $name . '_' . $common_setting[ 'name' ];

                if ( isset ( $common_setting[ 'deps' ] ) ) {
                    foreach( $common_setting[ 'deps' ] as $dep_name => $val ) {
                        $common_setting[ 'deps' ][ $name . '_' . $dep_name ] = $val;
                        unset( $common_setting[ 'deps' ][ $dep_name ] );
                    }
                }

                $style_setting[ 'settings' ][] = $common_setting;
            }

            $settings[ $name ] = $style_setting;
        }

        return $settings;
    }

    public function localize_plugin_styles( $form_id, $settings, $fields )
    {
        $cache = get_transient( 'ninja_forms_styles_plugin_styles' );
        if( $cache ){
            echo $cache;
            return;
        }

        $style_settings = Ninja_Forms()->get_setting( 'style' );
        $settings_groups = self::config( 'PluginSettingGroups' );

        $styles = array();
        foreach( $settings_groups as $setting_group ){

            $use_important = ( 'error_settings' == $setting_group[ 'name' ] ) ? TRUE : FALSE;

            if( 'error_settings' == $setting_group[ 'name' ] ) $setting_group[ 'name' ] = 'form_settings';
            if( 'datepicker_settings' == $setting_group[ 'name' ] ) $setting_group[ 'name' ] = 'form_settings';

            if( ! isset( $setting_group[ 'sections' ] ) || ! $setting_group[ 'sections' ] ) continue;

            $group_name = $setting_group[ 'name' ];

            if( ! isset( $style_settings[ $group_name ] ) || ! $style_settings[ $group_name ] ) continue;

            foreach( $setting_group[ 'sections' ] as $section ){

                if( ! isset( $section[ 'selector' ] ) || ! $section[ 'selector' ] ) continue;

                $section_name = $section[ 'name' ];

                $selector = $section[ 'selector' ];

                if( ! isset( $style_settings[ $group_name ][ $section_name ] ) ) continue;

                foreach( $style_settings[ $group_name ][ $section_name ] as $element => $style ){

                    if( ! $style ) continue;

                    if( $use_important ){
                        $style .= ' !important';
                    }

                    $styles[ $selector ][ $element ] = $style;

                    if( 'form_settings' == $group_name && 'container' == $section_name && 'color' == $element ){
                        // Themes commonly pin legend color at the root, which beats
                        // inheritance from .nf-form-cont, so the configured container
                        // text color must reach fieldset legends (repeater groups) explicitly.
                        $styles[ '.nf-form-cont .nf-form-content fieldset legend' ][ $element ] = $style;
                    }

                    if( 'field_settings' == $group_name && 'field' == $section_name ){
                        $this->add_default_field_element_style_mappings( $styles, $element, $style );
                    }
                }
            }
        }

        if( isset( $style_settings[ 'field_type' ] ) && $style_settings[ 'field_type' ] ){

            $base_selector = $settings_groups[ 'field_type' ][ 'selector' ];

            $field_type_sections = NF_Styles::config( 'FieldTypeSections' );

            $field_type_lookup = NF_Styles::config( 'FieldTypeLookup' );
            $field_type_lookup = array_flip( $field_type_lookup );

            foreach( $style_settings[ 'field_type' ] as $field_type => $style_setting_field ){
                foreach( $style_setting_field as $section => $style_setting_section ){

                    if( ! is_array( $style_setting_section ) )
                        continue;

                    foreach( $style_setting_section as $rule => $value ){

                        if( ! $value ) continue;

                        if( ! isset( $field_type_sections[ $section ][ 'selector' ] ) || ! $field_type_sections[ $section ][ 'selector' ] ) continue;

                        if( isset( $field_type_lookup[ $field_type ] ) ) $field_type = $field_type_lookup[ $field_type ];

                        $selector = $field_type_sections[ $section ][ 'selector' ];

                        if( Ninja_Forms()->get_setting( 'opinionated_styles' ) ) {

                            if( 'checkbox' == $field_type ){
                                if( 'element' == $section ){
                                    switch( $rule ){
                                        case 'background-color':
                                        case 'border':
                                        case 'border-style':
                                        case 'border-color':
                                            $selector = '.nf-field-label label::after';
                                            break;
                                        case 'color':
                                        case 'font-size':
                                            $selector = '.nf-field-label label::before';
                                        case 'display':
                                        case 'float':
                                        case 'height':
                                        case 'width':
                                            continue 2;
                                    }
                                }
                            }

                            if( 'listcheckbox' == $field_type ){
                                if( 'element' == $section ){
                                    switch( $rule ){
                                        case 'background-color':
                                        case 'border':
                                        case 'border-style':
                                        case 'border-color':
                                            $selector = '.nf-field-element label::after';
                                            break;
                                        case 'color':
                                        case 'font-size':
                                            $selector = '.nf-field-element label::before';
                                        case 'display':
                                        case 'float':
                                        case 'height':
                                        case 'width':
                                            continue 2;
                                    }
                                }
                            }

                            if( 'listradio' == $field_type ){
                                if( 'element' == $section ){
                                    switch( $rule ){
                                        case 'background-color':
                                        case 'border':
                                        case 'border-style':
                                        case 'border-color':
                                            $selector = '.nf-field-element label::after';
                                            break;
                                        case 'font-size':
                                            $selector = '.nf-field-element label::before';
                                            break;
                                        case 'display':
                                        case 'float':
                                        case 'height':
                                        case 'width':
                                            continue 2;
                                    }

                                    if( 'color' == $rule ){
                                        $styles[ str_replace( '{field-type}' , $field_type, $base_selector ) . ' label.nf-checked-label::before' ][ 'background-color' ] = $value;
                                    }

                                    if( 'border-color' == $rule ){
                                        $styles[ str_replace( '{field-type}' , $field_type, $base_selector ) . ' .nf-field-element label::after' ][ 'color' ] = $value;
                                    }
                                }
                            }

                            if( 'listselect' == $field_type ) {
                                if ('element' == $section) {
                                    switch ($rule) {
                                        case 'background-color':
                                        case 'border':
                                        case 'border-style':
                                        case 'border-color':
                                            $selector = '.nf-field-element > div';
                                            break;
                                        case 'color':
                                        case 'font-size':
                                            $selector = '.ninja-forms-field';
                                            break;
                                        case 'display':
                                        case 'float':
                                            continue 2;
                                        default:
                                            $selector = '.ninja-forms-field';
                                            $styles[ str_replace( '{field-type}' , $field_type, $base_selector ) . ' .nf-field-element > div' ][ $rule ] = $value;
                                    }

                                    if( 'border-color' == $rule ){
                                        $styles[ str_replace( '{field-type}' , $field_type, $base_selector ) . ' div::after' ][ 'color' ] = $value;
                                    }
                                }
                            }

                            if( 'list-item-row' == $section ){
                                $selector = '.nf-field-element li';
                            }

                        }

                        $selector = str_replace( '{field-type}' , $field_type, $base_selector ) . ' ' . $selector;

                        $selector = apply_filters( 'ninja_forms_styles_' . $field_type . '_selector', $selector );

                        $styles[$selector][$rule] = $value;

                        $this->maybe_add_native_choice_styles( $styles, $selector, $field_type, $section, $rule, $value );

                    }
                }
            }
        }


        ob_start();
        $this->localize_styles( $styles, 'Plugin Wide Styles' );
        $output = ob_get_clean();

        set_transient( 'ninja_forms_styles_plugin_styles', $output );
        echo $output;

    }

    /**
     * Map a single style rule onto the default field element selectors.
     *
     * Writes the rule/value into the shared $styles map for native select
     * shells and text, honoring the opinionated-styles setting.
     *
     * @param (Array)  $styles Style map, passed by reference and mutated in place.
     * @param (String) $rule   CSS property name.
     * @param (String) $value  CSS value to assign.
     * @return (void)
     */
    protected function add_default_field_element_style_mappings( &$styles, $rule, $value )
    {
        $opinionated_styles = Ninja_Forms()->get_setting( 'opinionated_styles' );

        $native_select_selector = '.nf-form-content .nf-field-element select.ninja-forms-field';
        $select_shell_selector = '.nf-form-content .list-select-wrap .nf-field-element > div, .nf-form-content .listselect-wrap .nf-field-element > div, .nf-form-content .listcountry-wrap .nf-field-element > div, .nf-form-content .liststate-wrap .nf-field-element > div';
        $select_text_selector = '.nf-form-content .list-select-wrap .ninja-forms-field, .nf-form-content .listselect-wrap .ninja-forms-field, .nf-form-content .listcountry-wrap .ninja-forms-field, .nf-form-content .liststate-wrap .ninja-forms-field';
        $select_arrow_selector = '.nf-form-content .list-select-wrap .nf-field-element > div::after, .nf-form-content .listselect-wrap .nf-field-element > div::after, .nf-form-content .listcountry-wrap .nf-field-element > div::after, .nf-form-content .liststate-wrap .nf-field-element > div::after';
        $choice_input_selector = '.nf-form-content .checkbox-wrap .nf-field-element input[type="checkbox"].ninja-forms-field, .nf-form-content .listcheckbox-wrap .nf-field-element input[type="checkbox"], .nf-form-content .listradio-wrap .nf-field-element input[type="radio"], .nf-form-content .terms-wrap .nf-field-element input[type="checkbox"]';
        $checkbox_input_selector = '.nf-form-content .checkbox-wrap .nf-field-element input[type="checkbox"].ninja-forms-field, .nf-form-content .listcheckbox-wrap .nf-field-element input[type="checkbox"], .nf-form-content .terms-wrap .nf-field-element input[type="checkbox"]';
        $radio_input_selector = '.nf-form-content .listradio-wrap .nf-field-element input[type="radio"]';
        $choice_box_selector = '.nf-form-content .checkbox-wrap .nf-field-label label::after, .nf-form-content .listcheckbox-wrap .nf-field-element label::after, .nf-form-content .listradio-wrap .nf-field-element label::after, .nf-form-content .terms-wrap .nf-field-element label::after';
        $choice_checked_selector = '.nf-form-content .checkbox-wrap .nf-field-label label.nf-checked-label::before, .nf-form-content .listcheckbox-wrap .nf-field-element label.nf-checked-label::before, .nf-form-content .terms-wrap .nf-field-element label.nf-checked-label::before';
        $radio_checked_selector = '.nf-form-content .listradio-wrap .nf-field-element label.nf-checked-label::before';
        $radio_checked_border_selector = '.nf-form-content .listradio-wrap .nf-field-element label.nf-checked-label::after';

        switch( $rule ){
            case 'background-color':
            case 'border':
            case 'border-style':
            case 'border-color':
                $styles[ $choice_input_selector ][ $rule ] = $value;
                if( $opinionated_styles ){
                    $styles[ $select_shell_selector ][ $rule ] = $value;
                    $styles[ $choice_box_selector ][ $rule ] = $value;
                } else {
                    $styles[ $native_select_selector ][ $rule ] = $value;
                    $this->maybe_add_native_choice_styles( $styles, $choice_input_selector, 'checkbox', 'element', $rule, $value, $checkbox_input_selector, $radio_input_selector );
                }
                if( 'border-color' == $rule && $opinionated_styles ){
                    $styles[ $select_arrow_selector ][ 'color' ] = $value;
                    $styles[ $radio_checked_border_selector ][ 'border-color' ] = $value;
                }
                break;
            case 'color':
                if( $opinionated_styles ){
                    $styles[ $select_text_selector ][ $rule ] = $value;
                    $styles[ $select_arrow_selector ][ $rule ] = $value;
                    $styles[ $choice_checked_selector ][ $rule ] = $value;
                    $styles[ $radio_checked_selector ][ 'background-color' ] = $value;
                    $styles[ $radio_checked_border_selector ][ 'border-color' ] = $value;
                } else {
                    $styles[ $native_select_selector ][ $rule ] = $value;
                    $this->maybe_add_native_choice_styles( $styles, $choice_input_selector, 'checkbox', 'element', $rule, $value, $checkbox_input_selector, $radio_input_selector );
                }
                break;
            case 'font-size':
                if( $opinionated_styles ){
                    $styles[ $select_text_selector ][ $rule ] = $value;
                    $styles[ $choice_checked_selector ][ $rule ] = $value;
                } else {
                    $styles[ $native_select_selector ][ $rule ] = $value;
                }
                break;
            case 'margin':
                $styles[ $choice_input_selector ][ $rule ] = $value;
                $styles[ $opinionated_styles ? $select_shell_selector : $native_select_selector ][ $rule ] = $value;
                break;
            case 'padding':
            case 'height':
            case 'width':
                $styles[ $opinionated_styles ? $select_shell_selector : $native_select_selector ][ $rule ] = $value;
                break;
            case 'display':
            case 'float':
                break;
            default:
                if( $opinionated_styles ){
                    $styles[ $select_shell_selector ][ $rule ] = $value;
                }
        }
    }

    /**
     * Add native checkbox/radio choice styling when opinionated styles are off.
     *
     * No-ops when opinionated styles are enabled. Otherwise writes the rule/value
     * (and, for the relevant rules, derived checked-state styling) into the shared
     * $styles map for the given selector and field type.
     *
     * @param (Array)  $styles            Style map, passed by reference and mutated in place.
     * @param (String) $selector          Base CSS selector for the choice element.
     * @param (String) $field_type        Field type being styled (e.g. listcheckbox).
     * @param (String) $section           Style section the rule belongs to.
     * @param (String) $rule              CSS property name.
     * @param (String) $value             CSS value to assign.
     * @param (String) $checkbox_selector Optional selector for native checkbox inputs.
     * @param (String) $radio_selector    Optional selector for native radio inputs.
     * @return (void)
     */
    protected function maybe_add_native_choice_styles( &$styles, $selector, $field_type, $section, $rule, $value, $checkbox_selector = '', $radio_selector = '' )
    {
        if( Ninja_Forms()->get_setting( 'opinionated_styles' ) ){
            return;
        }

        if( ! in_array( $field_type, array( 'checkbox', 'listcheckbox', 'listradio', 'terms' ), true ) ){
            return;
        }

        if( ! in_array( $section, array( 'element', 'element_styles', 'list_item_element_styles' ), true ) ){
            return;
        }

        if( ! $checkbox_selector && in_array( $field_type, array( 'checkbox', 'listcheckbox', 'terms' ), true ) ){
            $checkbox_selector = $selector;
        }

        if( ! $radio_selector && 'listradio' == $field_type ){
            $radio_selector = $selector;
        }

        $styles[ $selector ][ '-webkit-appearance' ] = 'none';
        $styles[ $selector ][ 'appearance' ] = 'none';
        $styles[ $selector ][ 'display' ] = 'inline-flex';
        $styles[ $selector ][ 'align-items' ] = 'center';
        $styles[ $selector ][ 'justify-content' ] = 'center';
        $styles[ $selector ][ 'line-height' ] = '1';
        $styles[ $selector ][ 'overflow' ] = 'hidden';
        $styles[ $selector ][ 'box-sizing' ] = 'border-box';
        $styles[ $selector ][ 'border-width' ] = isset( $styles[ $selector ][ 'border-width' ] ) ? $styles[ $selector ][ 'border-width' ] : '1px';
        $styles[ $selector ][ 'border-style' ] = isset( $styles[ $selector ][ 'border-style' ] ) ? $styles[ $selector ][ 'border-style' ] : 'solid';
        $styles[ $selector ][ 'border-color' ] = isset( $styles[ $selector ][ 'border-color' ] ) ? $styles[ $selector ][ 'border-color' ] : '#767676';
        $styles[ $selector ][ 'min-width' ] = '1em';
        $styles[ $selector ][ 'max-width' ] = '1em';
        $styles[ $selector ][ 'width' ] = '1em';
        $styles[ $selector ][ 'min-height' ] = '1em';
        $styles[ $selector ][ 'max-height' ] = '1em';
        $styles[ $selector ][ 'height' ] = '1em';
        $styles[ $selector ][ 'padding' ] = '0';
        $styles[ $selector ][ 'margin' ] = '0 0.5em 0 0';
        $styles[ $selector ][ 'vertical-align' ] = 'middle';

        if( $checkbox_selector ){
            $styles[ $checkbox_selector ][ 'border-radius' ] = '2px';
        }

        if( $radio_selector ){
            $styles[ $radio_selector ][ 'border-radius' ] = '50%';
        }

        if( in_array( $rule, array( 'background-color', 'border-color', 'color' ), true ) ){
            $styles[ $selector ][ 'accent-color' ] = $value;
        }

        if( in_array( $rule, array( 'border-color', 'color' ), true ) ){
            $checked_selector = $this->append_selector_suffix( $selector, ':checked' );
            $styles[ $checked_selector ][ 'background-color' ] = $value;
            $styles[ $checked_selector ][ 'border-color' ] = $value;

            if( $checkbox_selector ){
                $checked_checkbox_selector = $this->append_selector_suffix( $checkbox_selector, ':checked::before' );
                $styles[ $checked_checkbox_selector ][ 'content' ] = '"\2713"';
                $styles[ $checked_checkbox_selector ][ 'display' ] = 'block';
                $styles[ $checked_checkbox_selector ][ 'color' ] = '#fff';
                $styles[ $checked_checkbox_selector ][ 'font-size' ] = '0.85em';
                $styles[ $checked_checkbox_selector ][ 'line-height' ] = '1';
            }

            if( $radio_selector ){
                $checked_radio_selector = $this->append_selector_suffix( $radio_selector, ':checked' );
                $styles[ $checked_radio_selector ][ 'box-shadow' ] = 'inset 0 0 0 3px #fff';
            }
        }
    }

    /**
     * Append a suffix to each selector in a comma-separated selector list.
     *
     * @param (String) $selector Comma-separated CSS selector list.
     * @param (String) $suffix   Suffix to append to each selector (e.g. ':checked').
     * @return (String) Comma-separated list with the suffix applied to each selector.
     */
    protected function append_selector_suffix( $selector, $suffix )
    {
        $selectors = array_map( 'trim', explode( ',', $selector ) );
        $selectors = array_filter( $selectors );

        foreach( $selectors as $index => $single_selector ){
            $selectors[ $index ] = $single_selector . $suffix;
        }

        return implode( ', ', $selectors );
    }

    public function localize_form_styles( $form_id, $settings, $fields )
    {
        $cache = get_transient( 'ninja_forms_styles_form_' . $form_id . '_styles' );
        if( $cache ){
            echo $cache;
            return;
        }

        $form_settings_groups = self::config( 'FormSettings' );
        $common_settings = self::config( 'CommonSettings' );

        $styles = array();
        foreach( $form_settings_groups as $form_settings_group ){

            if( ! isset( $form_settings_group[ 'selector' ] ) ) continue;

            $selector = str_replace( '{ID}', $form_id, $form_settings_group[ 'selector' ] );

            foreach( $common_settings as $common_setting ){

                $setting = $form_settings_group[ 'name' ] . '_' . $common_setting[ 'name' ];
                if( ! isset( $settings[ $setting ] ) || ! $settings[ $setting ] ) continue;

                $rule = $common_setting[ 'name' ];

                $styles[ $selector ][ $rule ] = $settings[ $setting ];
            }
        }

        /*
         * Multi-Part Styles
         */
        $part_styles = self::config( 'MultiPartSettings' );
        foreach( $part_styles as &$part ){
            foreach( self::config( 'CommonSettings' ) as $common_setting ) {
                $name =  $part[ 'name' ] . '_' . $common_setting[ 'name' ];

                if( ! isset( $settings[ $name ] ) || ! $settings[ $name ] ) continue;

                $selector = $part[ 'selector' ];
                $rule = $common_setting[ 'name' ];
                $styles[$selector][$rule] = $settings[ $name ];
            }
        }
        /* End Multi-Part Styles */

        ob_start();
        $this->localize_styles( $styles, 'Form Styles' );
        $output = ob_get_clean();

        set_transient( 'ninja_forms_styles_form_' . $form_id . '_styles', $output );
        echo $output;
    }

    public function localize_field_styles( $form_id, $settings, $fields )
    {
        $form_instance_id = 0;
        if(strpos($form_id, '_')) list($real_form_id, $form_instance_id) = explode('_', $form_id);

        $cache = get_transient( $this->field_styles_cache_key( $form_id ) );
        if( $cache ){
            echo $cache;
            return;
        }

        $field_settings_groups = self::config( 'FieldSettings' );

        $field_settings_groups = array_merge( $field_settings_groups, self::config( 'ButtonFieldSettings' ) );
        
        $field_settings_groups = array_merge( $field_settings_groups, self::config( 'ListFieldSettings' ) );
        
        $field_settings_groups = array_merge( $field_settings_groups, self::config( 'RatingFieldSettings' ) );

	    $field_settings_groups = apply_filters( 'ninja_forms_styles_field_settings_groups', $field_settings_groups );

        $common_settings = self::config( 'CommonSettings' );

        $styles = array();
        foreach( $fields as $field ){

            if( is_object( $field ) ){
                $field->get_settings(); // Initialize object settigns.
            } else {
                if (isset($field['settings'])) $field = array_merge($field, $field['settings']);
            }

            foreach( $field_settings_groups as $field_settings_group ){

                if( ! isset( $field_settings_group[ 'selector' ] ) ) continue;

                if( is_object( $field ) ){
                    $field_id = $field->get_id();
                } elseif( isset( $field[ 'id' ] ) ){
                    $field_id = $field['id'];
                }

                if($form_instance_id) {
                    $field_id .= '_' . $form_instance_id;
                }

                $selector = str_replace( '{ID}', $field_id, $field_settings_group[ 'selector' ] );

                // Get field type for selector adjustments.
                $field_type = '';
                if( is_object( $field ) ){
                    $field_type = $field->get_setting( 'type' );
                } elseif( isset( $field[ 'type' ] ) ){
                    $field_type = $field[ 'type' ];
                }

                // HTML fields render content directly into .nf-field-element without a .ninja-forms-field wrapper.
                // Adjust element_styles selector to target .nf-field-element directly for these fields.
                if( 'html' === $field_type && 'element_styles' === $field_settings_group[ 'name' ] ){
                    $selector = str_replace( '.nf-field-element .ninja-forms-field', '.nf-field-element', $selector );
                }

                foreach( $common_settings as $common_setting ){

                    $setting = $field_settings_group[ 'name' ] . '_' . $common_setting[ 'name' ];

                    $field_setting = '';
                    if( is_object( $field ) ){
                        $field_setting = $field->get_setting( $setting );
                    } elseif( isset( $field[ $setting ] ) ){
                        $field_setting = $field[ $setting ];
                    }

                    if( ! $field_setting ) continue;

                    $rule = $common_setting[ 'name' ];
                    $skip_base_style = false;

                    if( Ninja_Forms()->get_setting( 'opinionated_styles' ) ){

                        if( is_object( $field ) ){
                            $field_type = $field->get_setting( 'type' );
                        } elseif( isset( $field[ 'type' ] ) ){
                            $field_type = $field[ 'type' ];
                        }

                        if( 'listradio' == $field_type ){
                                switch( $rule ){
                                    case 'background-color':
                                    case 'border':
                                    case 'border-style':
                                    case 'border-color':
//                                        $selector = '.nf-field-element label::after';
                                        $styles[ str_replace( '.ninja-forms-field', '', $selector ) . ' label::after' ][$rule] = $field_setting;
                                        break;
                                    case 'font-size':
//                                        $selector = '.nf-field-element label::before';
                                        $styles[ str_replace( '.ninja-forms-field', '', $selector ) . ' .nf-field-element label::before' ][$rule] = $field_setting;
                                        break;
                                    case 'display':
                                    case 'float':
                                    case 'height':
                                    case 'width':
                                        continue 2;
                                }

                                if( 'color' == $rule ){
                                    $styles[ str_replace( '.ninja-forms-field', '', $selector ) . ' label.nf-checked-label::before' ][ 'background-color' ] = $field_setting;
                                }

                                if( 'border-color' == $rule ){
                                    $styles[ str_replace( '.ninja-forms-field', '', $selector ) . ' .nf-field-element label::after' ][ 'color' ] = $field_setting;
                                }
                        }

                        if( 'element_styles' == $field_settings_group[ 'name' ] && in_array( $field_type, array( 'listselect', 'listcountry', 'liststate' ), true ) ){
                            switch ($rule) {
                                case 'background-color':
                                case 'border':
                                case 'border-style':
                                    $styles[ str_replace( '.ninja-forms-field', '', $selector ) . ' > div' ][$rule] = $field_setting;
                                    $skip_base_style = true;
                                    break;
                                case 'border-color':
                                    $styles[ str_replace( '.ninja-forms-field', '', $selector ) . ' > div' ][$rule] = $field_setting;
                                    $styles[ str_replace( '.ninja-forms-field', '', $selector ) . ' > div::after' ][ 'color' ] = $field_setting;
                                    $skip_base_style = true;
                                    break;
                                case 'color':
                                    $styles[ str_replace( '.ninja-forms-field', '', $selector ) . ' > div::after' ][ 'color' ] = $field_setting;
                                    break;
                                case 'font-size':
                                    $styles[ $selector . ''][$rule] = $field_setting;
                                    break;
                                case 'margin':
                                case 'padding':
                                case 'height':
                                case 'width':
                                    $styles[ str_replace( '.ninja-forms-field', '', $selector ) . ' > div' ][$rule] = $field_setting;
                                    $skip_base_style = true;
                                    break;
                                case 'display':
                                case 'float':
                                    continue 2;
                                default:
                                    $styles[ str_replace( '.ninja-forms-field', '', $selector ) . ' > div' ][ $rule ] = $field_setting;
                                    $skip_base_style = true;
                                    break;
                            }
                        }

                        if( 'checkbox' == $field_type ){
                            switch ($rule) {
                                case 'background-color':
                                case 'border':
                                case 'border-style':
                                case 'border-color':
                                $styles[ str_replace( '.nf-field-element .ninja-forms-field', '', $selector ) . ' label:after'][$rule] = $field_setting;
                                    break;
                                case 'color':
                                case 'font-size':
                                    $styles[ str_replace( '.nf-field-element .ninja-forms-field', '', $selector ) . ' label:before'][$rule] = $field_setting;
                                    break;
                                case 'display':
                                case 'float':
                                    continue 2;
                            }
                        }

                        if( 'listcheckbox' == $field_type ){
                            switch( $rule ){
                                case 'background-color':
                                case 'border':
                                case 'border-style':
                                case 'border-color':
                                    $styles[ str_replace( '.nf-field-element .ninja-forms-field', '', $selector ) . ' label:after'][$rule] = $field_setting;
                                    break;
                                case 'color':
                                case 'font-size':
                                    $styles[ str_replace( '.nf-field-element .ninja-forms-field', '', $selector ) . ' label:before'][$rule] = $field_setting;
                                case 'display':
                                case 'float':
                                case 'height':
                                case 'width':
                                    continue 2;
                            }
                        }
                    }

                    if( $skip_base_style ){
                        continue;
                    }

                    $is_choice_text_color =
                        ! Ninja_Forms()->get_setting( 'opinionated_styles' )
                        && 'element_styles' === $field_settings_group[ 'name' ]
                        && in_array( $field_type, array( 'listcheckbox', 'listradio' ), true )
                        && 'color' === $rule;

                    if( $is_choice_text_color ){
                        $label_selector = str_replace( '.nf-field-element .ninja-forms-field', '.nf-field-element li label', $selector );
                        $styles[ $label_selector ][ $rule ] = $field_setting;
                        continue;
                    }

                    $styles[$selector][$rule] = $field_setting;

                    $this->maybe_add_native_choice_styles( $styles, $selector, $field_type, $field_settings_group[ 'name' ], $rule, $field_setting );
                }
            }

            // Process repeater child fields.
            // Child fields are stored in $field['fields'] and have IDs like "29.1", "29.2".
            // The DOM renders them with fieldset instance suffix: "29.1_0", "29.1_1", etc.
            // We use CSS attribute selectors to match all fieldset instances.
            if ( 'repeater' === $field_type ) {
                $child_fields = array();
                if ( is_object( $field ) ) {
                    $child_fields = $field->get_setting( 'fields' );
                } elseif ( isset( $field['fields'] ) ) {
                    $child_fields = $field['fields'];
                }

                if ( ! empty( $child_fields ) && is_array( $child_fields ) ) {
                    foreach ( $child_fields as $child_field ) {
                        // Merge settings if needed (same as parent field processing).
                        if ( isset( $child_field['settings'] ) ) {
                            $child_field = array_merge( $child_field, $child_field['settings'] );
                        }

                        $child_field_id = isset( $child_field['id'] ) ? $child_field['id'] : '';
                        if ( empty( $child_field_id ) ) {
                            continue;
                        }

                        // Get child field type for selector adjustments.
                        $child_field_type = isset( $child_field['type'] ) ? $child_field['type'] : '';

                        foreach ( $field_settings_groups as $field_settings_group ) {
                            if ( ! isset( $field_settings_group['selector'] ) ) {
                                continue;
                            }

                            // Build CSS attribute selector that matches all fieldset instances.
                            // Child field IDs are like "29.1", DOM IDs are "nf-field-29.1_0-wrap", etc.
                            // Selector pattern: [id^="nf-field-{child_id}_"] matches all instances.
                            // Including the underscore prevents matching similar IDs (e.g., 29.1 won't match 29.10).
                            $base_selector = $field_settings_group['selector'];

                            // Replace #nf-field-{ID}-wrap with attribute selector.
                            // Original: .nf-form-content .nf-field-container #nf-field-{ID}-wrap
                            // New:      .nf-form-content .nf-field-container [id^="nf-field-{ID}_"][id$="-wrap"]
                            $child_selector = preg_replace(
                                '/#nf-field-\{ID\}-wrap/',
                                '[id^="nf-field-' . $child_field_id . '_"][id$="-wrap"]',
                                $base_selector
                            );

                            // Handle element_styles selector adjustment for HTML fields.
                            if ( 'html' === $child_field_type && 'element_styles' === $field_settings_group['name'] ) {
                                $child_selector = str_replace( '.nf-field-element .ninja-forms-field', '.nf-field-element', $child_selector );
                            }

                            foreach ( $common_settings as $common_setting ) {
                                $setting = $field_settings_group['name'] . '_' . $common_setting['name'];

                                $child_field_setting = isset( $child_field[ $setting ] ) ? $child_field[ $setting ] : '';
                                if ( ! $child_field_setting ) {
                                    continue;
                                }

                                $rule = $common_setting['name'];

                                // Apply the same opinionated styles logic for special field types.
                                if ( Ninja_Forms()->get_setting( 'opinionated_styles' ) ) {
                                    if ( 'listradio' === $child_field_type ) {
                                        switch ( $rule ) {
                                            case 'background-color':
                                            case 'border':
                                            case 'border-style':
                                            case 'border-color':
                                                $styles[ str_replace( '.ninja-forms-field', '', $child_selector ) . ' label::after' ][ $rule ] = $child_field_setting;
                                                break;
                                            case 'font-size':
                                                $styles[ str_replace( '.ninja-forms-field', '', $child_selector ) . ' .nf-field-element label::before' ][ $rule ] = $child_field_setting;
                                                break;
                                            case 'display':
                                            case 'float':
                                            case 'height':
                                            case 'width':
                                                continue 2;
                                        }

                                        if ( 'color' === $rule ) {
                                            $styles[ str_replace( '.ninja-forms-field', '', $child_selector ) . ' label.nf-checked-label::before' ]['background-color'] = $child_field_setting;
                                        }

                                        if ( 'border-color' === $rule ) {
                                            $styles[ str_replace( '.ninja-forms-field', '', $child_selector ) . ' .nf-field-element label::after' ]['color'] = $child_field_setting;
                                        }
                                    }

                                    if ( 'listselect' === $child_field_type ) {
                                        switch ( $rule ) {
                                            case 'background-color':
                                            case 'border':
                                            case 'border-style':
                                            case 'border-color':
                                                $styles[ str_replace( '.ninja-forms-field', '', $child_selector ) . ' > div' ][ $rule ] = $child_field_setting;
                                                break;
                                            case 'color':
                                            case 'font-size':
                                                $styles[ $child_selector ][ $rule ] = $child_field_setting;
                                                break;
                                            case 'display':
                                            case 'float':
                                                continue 2;
                                            default:
                                                $styles[' .nf-field-element > div'][ $rule ] = $child_field_setting;
                                        }

                                        if ( 'border-color' === $rule ) {
                                            $styles[ str_replace( '.ninja-forms-field', '', $child_selector ) . ' > div::after' ]['color'] = $child_field_setting;
                                        }
                                    }

                                    if ( 'checkbox' === $child_field_type ) {
                                        switch ( $rule ) {
                                            case 'background-color':
                                            case 'border':
                                            case 'border-style':
                                            case 'border-color':
                                                $styles[ str_replace( '.nf-field-element .ninja-forms-field', '', $child_selector ) . ' label:after' ][ $rule ] = $child_field_setting;
                                                break;
                                            case 'color':
                                            case 'font-size':
                                                $styles[ str_replace( '.nf-field-element .ninja-forms-field', '', $child_selector ) . ' label:before' ][ $rule ] = $child_field_setting;
                                                break;
                                            case 'display':
                                            case 'float':
                                                continue 2;
                                        }
                                    }

                                    if ( 'listcheckbox' === $child_field_type ) {
                                        switch ( $rule ) {
                                            case 'background-color':
                                            case 'border':
                                            case 'border-style':
                                            case 'border-color':
                                                $styles[ str_replace( '.nf-field-element .ninja-forms-field', '', $child_selector ) . ' label:after' ][ $rule ] = $child_field_setting;
                                                break;
                                            case 'color':
                                            case 'font-size':
                                                $styles[ str_replace( '.nf-field-element .ninja-forms-field', '', $child_selector ) . ' label:before' ][ $rule ] = $child_field_setting;
                                            case 'display':
                                            case 'float':
                                            case 'height':
                                            case 'width':
                                                continue 2;
                                        }
                                    }
                                }

                                $styles[ $child_selector ][ $rule ] = $child_field_setting;

                                $this->maybe_add_native_choice_styles( $styles, $child_selector, $child_field_type, $field_settings_group['name'], $rule, $child_field_setting );
                            }
                        }
                    }
                }
            }
        }

        ob_start();
        $this->localize_styles( $styles, 'Fields Styles' );
        $output = ob_get_clean();

        set_transient( $this->field_styles_cache_key( $form_id ), $output );
        echo $output;
    }

    private function localize_styles( $styles, $title = '' )
    {
        // $styles[ $selector ][ $element ] = $style;
        $styles = apply_filters( 'ninja_forms_styles_apply_standard_units', $styles );
        if( $styles ) self::template( 'display-form-styles.css.php', compact( 'styles', 'title' ) );
    }

    public function bust_form_styles_cache( $form_id )
    {
        delete_transient( 'ninja_forms_styles_form_' . $form_id . '_styles' );

        // Fallback for form instance IDs. @NOTE Does not support memcache (or similar), where transients are not stored in the database
        global $wpdb;
        $result = $wpdb->query( "DELETE FROM `$wpdb->options` WHERE `option_name` LIKE ('_transient_ninja_forms_styles_form_" . $form_id . "_%_styles')" );
    }

    public function bust_field_styles_cache( $form_id )
    {
        delete_transient( $this->field_styles_cache_key( $form_id ) );
        delete_transient( 'ninja_forms_styles_form_' . $form_id . '_field_styles' );

        // Fallback for form instance IDs. @NOTE Does not support memcache (or similar), where transients are not stored in the database
        global $wpdb;
        $wpdb->query( "DELETE FROM `$wpdb->options` WHERE `option_name` LIKE ('_transient_ninja_forms_styles_form_" . $form_id . "_%_field_styles')" );        
    }

    protected function field_styles_cache_key( $form_id )
    {
        return 'ninja_forms_styles_form_' . $form_id . '_v' . self::FIELD_STYLES_CACHE_VERSION . '_field_styles';
    }

    public function bust_plugin_styles_cache( $style_settings )
    {
        delete_transient( 'ninja_forms_styles_plugin_styles' );
    }
    
    /**
     * Function to apply standard units to non-specific css rules.
     * @param (Object) $styles[ $selector ][ $element ] = $style
     * @return (Object) the filtered rules
     */
    public function apply_standard_units( $styles )
    {
        foreach( $styles as $style => $rules ){
            foreach( $rules as $rule => $value ) {
                $important = '';
                // To prevent PHP warnings in the output buffer,
                // bail if our value is an array instead of a string.
                if ( is_array( $value ) ) continue;
                if ( 'border' == $rule || 'height' == $rule || 'width' == $rule || 'margin' == $rule || 'padding' == $rule ) {
                    if ( false !== strpos( $value, ' !important' ) ) {
                        $value = substr( $value, 0, strpos( $value, ' !important' ) );
                        $important = ' !important';
                    }
                    if ( is_numeric( $value ) ) {
                        $styles[ $style ][ $rule ] = $value . 'px' . $important;
                    }
                }
                elseif ( 'font-size' == $rule ) {
                    if ( false !== strpos( $value, ' !important' ) ) {
                        $value = substr( $value, 0, strpos( $value, ' !important' ) );
                        $important = ' !important';
                    }
                    if ( is_numeric( $value ) ) {
                        $styles[ $style ][ $rule ] = $value . 'pt' . $important;
                    }
                }
            }
        }
        return $styles;
    }

    public function filter_output_rule_border( $rule )
    {
        return 'border-width';
    }

    /**
     * Cache-busting version for one of this component's assets.
     *
     * @since 3.0.30
     * @param string $relative_path Asset path relative to this file's directory.
     * @return string Version string for wp_enqueue_style/script.
     */
    public static function asset_version( $relative_path )
    {
        return NF_Layout_Styles_Assets::version( plugin_dir_path( __FILE__ ) . $relative_path, self::VERSION );
    }

    /**
     * Main Plugin Instance
     *
     * Insures that only one instance of a plugin class exists in memory at any one
     * time. Also prevents needing to define globals all over the place.
     *
     * @since 3.0
     * @static
     * @static var array $instance
     * @return NF_Layouts Highlander Instance
     */
    public static function instance()
    {
        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof NF_Styles ) ) {
            self::$instance = new NF_Styles();
            self::$dir = plugin_dir_path(__FILE__);
            self::$url = plugin_dir_url(__FILE__);
            spl_autoload_register( array( self::$instance, 'autoloader' ) );
        }
        return self::$instance;
    }

    /**
     * Autoloader
     *
     * @param $class_name
     */
    public function autoloader( $class_name )
    {
        if (class_exists($class_name)) return;

        if ( false === strpos( $class_name, self::PREFIX ) ) return;

        $class_name = str_replace( self::PREFIX, '', $class_name );
        $classes_dir = realpath(plugin_dir_path(__FILE__)) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR;
        $class_file = str_replace('_', DIRECTORY_SEPARATOR, $class_name) . '.php';

        if (file_exists($classes_dir . $class_file)) {
            require_once $classes_dir . $class_file;
        }
    }

    /**
     * Template
     *
     * @param string $file_name
     * @param array $data
     */
    public static function template( $file_name = '', array $data = array() )
    {
        if( ! $file_name ) return;

        extract( $data );

        include self::$dir . 'includes/Templates/' . $file_name;
    }

    /**
     * Config
     *
     * @param $file_name
     * @return mixed
     */
    public static function config( $file_name )
    {
        return include self::$dir . 'includes/Config/' . $file_name . '.php';
    }

    /**
     * License Setup
     */
    public function setup_license()
    {
        if ( ! class_exists( 'NF_Extension_Updater' ) ) return;

        new NF_Extension_Updater( self::NAME, self::VERSION, self::AUTHOR, __FILE__, self::SLUG );
    }

    /**
     * Add a tab for styles to the import/export screen.
     */
    public function add_export_tab( $tabs ) {
        $tabs[ 'styles' ] = __( 'Styles', 'ninja-forms-layout-styles' );
        $this->add_export_metaboxes();
        return $tabs;
    }

    /**
     * Register our metaboxes
     */
    public function add_export_metaboxes() {
        /*
         * Import
         */
        add_meta_box(
            'nf_import_export_styles_import',
            __( 'Import Styles', 'ninja-forms-layout-styles' ),
            array( $this, 'template_import_styles' ),
            'nf_import_export_styles'
        );

        /*
         * Export
         */
        add_meta_box(
            'nf_import_export_styles_export',
            __( 'Export Styles', 'ninja-forms-layout-styles' ),
            array( $this, 'template_export_styles' ),
            'nf_import_export_styles'
        );
    }

    /**
     * Output our import metabox content
     */
    public function template_import_styles() {
         NF_Styles::template( 'admin-settings-import-metabox.html.php' );
    }


    /**
     * Output our export metabox content
     */
    public function template_export_styles() {
        NF_Styles::template( 'admin-settings-export-metabox.html.php' );
    }

    /**
     * Handle import/export when the user clicks the appropriate button.
     *
     * @since  3.0
     * @return bool
     */
    public function import_export() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return false;
        }

        // CSRF protection: verify nonce on all import/export actions.
        if ( ! isset( $_POST['nf_styles_security'] )
            || ! wp_verify_nonce( $_POST['nf_styles_security'], 'nf_styles_import_export_nonce' ) ) {
            return false;
        }

        if ( isset ( $_POST[ 'nf_export_styles_submit' ] ) ) {
            /*
             * Get our current style settings.
             */
            $style_settings = Ninja_Forms()->get_setting( 'style', false );

            /*
             * Initiate a file download with our JSON-encoded settings.
             */
            header("Content-type: application/json");
            header("Content-Disposition: attachment; filename=ninja-forms-default-styles-" . time() . ".nfs");
            header("Pragma: no-cache");
            header("Expires: 0");

            echo wp_json_encode( $style_settings );

            die();
        } else if ( isset( $_POST[ 'nf_import_style_submit' ] ) ) {
            /*
             * Check for upload errors.
             */
            $this->upload_error_check( $_FILES[ 'nf_import_style' ] );

            $file_contents = file_get_contents( $_FILES[ 'nf_import_style' ][ 'tmp_name' ] );

            // Try JSON first (new format), fall back to safe unserialization (legacy .nfs files).
            $import = json_decode( $file_contents, true );
            if ( null === $import && JSON_ERROR_NONE !== json_last_error() ) {
                $import = unserialize( $file_contents, array( 'allowed_classes' => false ) );
            }

            /*
             * If we have an array, update our style settings with the imported array.
             */
            if ( is_array( $import ) ) {
               Ninja_Forms()->update_setting( 'style', $import );
            }
        }
    }

    private function upload_error_check( $file )
    {
        if( ! $file[ 'error' ] ) return;

        switch ( $file[ 'error' ] ) {
            case UPLOAD_ERR_INI_SIZE:
                $error_message = __( 'The uploaded file exceeds the upload_max_filesize directive in php.ini.', 'ninja-forms' );
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $error_message = __( 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.', 'ninja-forms' );
                break;
            case UPLOAD_ERR_PARTIAL:
                $error_message = __( 'The uploaded file was only partially uploaded.', 'ninja-forms' );
                break;
            case UPLOAD_ERR_NO_FILE:
                $error_message = __( 'No file was uploaded.', 'ninja-forms' );
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $error_message = __( 'Missing a temporary folder.', 'ninja-forms' );
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $error_message = __( 'Failed to write file to disk.', 'ninja-forms' );
                break;
            case UPLOAD_ERR_EXTENSION:
                $error_message = __( 'File upload stopped by extension.', 'ninja-forms' );
                break;
            default:
                $error_message = __( 'Unknown upload error.', 'ninja-forms' );
                break;
        }

        $args = array(
            'title' => __( 'File Upload Error', 'ninja-forms' ),
            'message' => $error_message,
            'debug' => $file,
        );
        $message = Ninja_Forms()->template( 'admin-wp-die.html.php', $args );
        wp_die( $message, $args[ 'title' ], array( 'back_link' => TRUE ) );
    }

    public function add_multi_part_settings( $settings )
    {
        $part_styles = self::config( 'MultiPartSettings' );

        foreach( $part_styles as &$part ){
            $part[ 'group' ] = 'styles';

            switch( $part[ 'name' ] ){
                case 'breadcrumb_container_styles':
                case 'progress_bar_container_styles':
                case 'navigation_container_styles':
                    $blacklist = array( 'color', 'font-size' );
                    break;
                case 'progress_bar_fill_styles':
                    $blacklist = array( 'color', 'font-size', 'width', 'display', 'float' );
                    break;
                case 'next_button_styles':
                case 'previous_button_styles':
                case 'navigation_hover_styles':
                    $blacklist = array( 'display', 'float' );
                    break;
                default:
                    $blacklist = array();
            }

            foreach( self::config( 'CommonSettings' ) as $common_setting ) {

                if( in_array( $common_setting[ 'name' ], $blacklist ) ) continue;

                if ( isset ( $common_setting[ 'deps' ] ) ) {
                    foreach( $common_setting[ 'deps' ] as $dep_name => $val ) {
                        $common_setting[ 'deps' ][ $part[ 'name' ] . '_' . $dep_name ] = $val;
                        unset( $common_setting[ 'deps' ][ $dep_name ] );
                    }
                }

                $name =  $part[ 'name' ] . '_' . $common_setting[ 'name' ];
                $part[ 'settings' ][ $name ] = $common_setting;
                $part[ 'settings' ][ $name ][ 'name' ] = $name;
            }
        }

        $settings[ 'multi_part' ] = array_merge( $settings[ 'multi_part' ], $part_styles );
        return $settings;
    }

}

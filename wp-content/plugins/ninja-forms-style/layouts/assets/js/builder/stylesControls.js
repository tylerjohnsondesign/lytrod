/**
 * Control renderer utilities for the Visual Styles Editor.
 *
 * Renders color, range, spacing, and segmented controls for style properties.
 * Extracted from stylesVisualEditor.js for testability and reuse.
 *
 * @since 3.0.30
 */
define( ['stylesUnits'], function( StylesUnits ) {
	'use strict';

	var StylesControls = {

		/**
		 * Control configuration keyed by property name.
		 * Set via init() before rendering controls.
		 * @type {Object}
		 */
		settings: null,

		/**
		 * Initialize the module with control settings.
		 * @since 3.0.30
		 * @param {Object} settings Control configuration keyed by property.
		 * @return {Object} This module for chaining.
		 */
		init: function( settings ) {
			this.settings = settings;
			return this;
		},

		/**
		 * Build the full setting name from a setting model and property.
		 * @since 3.0.30
		 * @param {Backbone.Model} settingModel Style setting group model.
		 * @param {string}         property     Style property key.
		 * @return {string} The composite setting name.
		 */
		getSettingName: function( settingModel, property ) {
			return settingModel.get( 'name' ) + '_' + property;
		},

		/**
		 * Determine whether a value can be edited by visual controls.
		 *
		 * Returns true when the value is empty/missing, matches a segmented
		 * option, or is a valid unit/spacing/color value for the property type.
		 *
		 * @since 3.0.30
		 * @param {string} property Style property key.
		 * @param {string} value    Saved style value.
		 * @return {boolean} Whether the value is editable in the visual UI.
		 */
		isEditableVisualValue: function( property, value ) {
			var config = this.settings[ property ];

			if ( ! value || ! config ) {
				return true;
			}

			if ( 'color' === config.type ) {
				return StylesUnits.isColor( value );
			}

			if ( 'segmented' === config.type ) {
				return _.some( config.options, function( option ) {
					return value === option.value;
				} );
			}

			if ( -1 !== [ 'padding', 'margin' ].indexOf( property ) ) {
				return StylesUnits.isSpacingValue( value, config );
			}

			if ( 'range' === config.type ) {
				return StylesUnits.isSupportedUnitValue( value, config );
			}

			return true;
		},

		/**
		 * Build the color picker popover (theme swatches and hex input).
		 * @since 3.0.30
		 * @param {string} value Current color value to preselect.
		 * @return {jQuery} The rendered color popover element.
		 */
		renderColorPopover: function( value ) {
			return window.nfStylesShared.renderColorPopover( value, window.nfStylesShared.getColorPopoverLabels() );
		},

		/**
		 * Render a color swatch control (with popover picker) for a style property.
		 *
		 * @since 3.0.30
		 * @param {string}         property     Style property key (e.g. background-color).
		 * @param {Backbone.Model} settingModel Style setting group model.
		 * @param {Backbone.Model} dataModel    Data model holding the saved value.
		 * @param {string}         groupName    Style group name the control belongs to.
		 * @return {jQuery} The rendered control element.
		 */
		renderColorControl: function( property, settingModel, dataModel, groupName ) {
			var config = this.settings[ property ];
			var name = this.getSettingName( settingModel, property );
			var value = dataModel.get( name ) || '';
			var isEditable = this.isEditableVisualValue( property, value );
			var colorValue = isEditable && StylesUnits.isColor( value ) ? value : '#ffffff';
			var clearClass = value ? '' : ' is-empty';
			var emptyClass = value && isEditable ? '' : ' is-empty';
			var control = jQuery( '<div class="nf-styles-visual-swatch' + ( value && ! isEditable ? ' has-css-value' : '' ) + '" />' );

			control.append( '<span class="nf-styles-visual-swatch-label">' + config.label + '</span>' );
			control.append( '<span class="dashicons dashicons-admin-appearance nf-styles-visual-color-icon" aria-hidden="true"></span>' );
			if ( value && ! isEditable ) {
				control.append( '<span class="nf-styles-css-value">' + _.escape( window.nfStylesShared.l10n('cssBadge', 'CSS' ) ) + '</span>' );
			}
			control.append( '<button type="button" class="nf-styles-visual-swatch-chip' + emptyClass + '" style="background-color:' + colorValue + '" aria-expanded="false" aria-label="' + _.escape( window.nfStylesShared.format(window.nfStylesShared.l10n('chooseColor', 'Choose %s color' ), config.label ) ) + '" title="' + _.escape( window.nfStylesShared.format(window.nfStylesShared.l10n('chooseColor', 'Choose %s color' ), config.label ) ) + '"' + ( value && ! isEditable ? ' disabled' : '' ) + '></button>' );
			control.append( '<button type="button" class="nf-styles-visual-reset' + clearClass + '" data-setting-name="' + name + '" aria-label="' + _.escape( window.nfStylesShared.format(window.nfStylesShared.l10n('clearColor', 'Clear %s color' ), config.label ) ) + '" title="' + _.escape( window.nfStylesShared.format(window.nfStylesShared.l10n('clearColor', 'Clear %s color' ), config.label ) ) + '">×</button>' );
			control.append( this.renderColorPopover( isEditable ? value : '' ) );
			control.append( '<input id="nf-styles-' + name + '" class="nf-styles-visual-color" type="color" tabindex="-1" aria-hidden="true" value="' + colorValue + '" data-setting-name="' + name + '" data-style-group="' + groupName + '"' + ( value && ! isEditable ? ' data-css-managed="true" disabled' : '' ) + ' />' );

			return control;
		},

		/**
		 * Render a single-value range control (with unit select) for a style property.
		 *
		 * @since 3.0.30
		 * @param {string}         property     Style property key (e.g. font-size).
		 * @param {Backbone.Model} settingModel Style setting group model.
		 * @param {Backbone.Model} dataModel    Data model holding the saved value.
		 * @param {string}         groupName    Style group name the control belongs to.
		 * @return {jQuery} The rendered control element.
		 */
		renderRangeControl: function( property, settingModel, dataModel, groupName ) {
			var config = this.settings[ property ];
			var name = this.getSettingName( settingModel, property );
			var value = dataModel.get( name ) || '';
			var isEditable = this.isEditableVisualValue( property, value );
			var unit = isEditable ? StylesUnits.getValueUnit( value, config ) : config.unit;
			var rangeConfig = StylesUnits.getUnitRangeConfig( config, unit );
			var numeric = isEditable ? StylesUnits.getNumericValue( value, config.defaultValue || rangeConfig.min ) : ( config.defaultValue || rangeConfig.min );
			var label = value ? ( isEditable ? numeric + unit : window.nfStylesShared.l10n('cssBadge', 'CSS' ) ) : window.nfStylesShared.l10n('defaultLabel', 'Default' );
			var control = jQuery( '<div class="nf-styles-visual-control nf-styles-visual-control-range' + ( value && ! isEditable ? ' has-css-value' : '' ) + '" />' );

			control.append( '<div class="nf-styles-visual-range-header"><label for="nf-styles-' + name + '">' + config.label + '</label><span class="nf-styles-visual-control-actions">' + this.renderValueControl( label, value && isEditable ? numeric : '', unit, name, config ) + this.renderUnitControl( unit, config, value && ! isEditable ) + '<button type="button" class="nf-styles-visual-reset' + ( value ? '' : ' is-empty' ) + '" data-setting-name="' + name + '" aria-label="' + _.escape( window.nfStylesShared.format(window.nfStylesShared.l10n('clearValue', 'Clear %s' ), config.label ) ) + '" title="' + _.escape( window.nfStylesShared.format(window.nfStylesShared.l10n('clearValue', 'Clear %s' ), config.label ) ) + '">×</button></span></div>' );
			control.append( '<input id="nf-styles-' + name + '" class="nf-styles-visual-range" type="range" min="' + rangeConfig.min + '" max="' + rangeConfig.max + '" step="' + rangeConfig.step + '" value="' + numeric + '" data-setting-name="' + name + '" data-style-group="' + groupName + '" data-unit="' + unit + '" data-default-value="' + ( config.defaultValue || rangeConfig.min ) + '"' + ( value && ! isEditable ? ' data-css-managed="true" disabled' : '' ) + ' />' );

			return control;
		},

		/**
		 * Render a spacing (padding/margin) control with optional per-side inputs.
		 *
		 * @since 3.0.30
		 * @param {string}         property     Style property key (padding or margin).
		 * @param {Backbone.Model} settingModel Style setting group model.
		 * @param {Backbone.Model} dataModel    Data model holding the saved value.
		 * @param {string}         groupName    Style group name the control belongs to.
		 * @return {jQuery} The rendered control element.
		 */
		renderSpacingControl: function( property, settingModel, dataModel, groupName ) {
			var config = this.settings[ property ];
			var name = this.getSettingName( settingModel, property );
			var value = dataModel.get( name ) || '';
			var isEditable = this.isEditableVisualValue( property, value );
			var unit = isEditable ? StylesUnits.getValueUnit( value, config ) : config.unit;
			var rangeConfig = StylesUnits.getUnitRangeConfig( config, unit );
			var spacing = StylesUnits.getSpacingValues( isEditable ? value : '', config, unit );
			var isSplit = isEditable && StylesUnits.isSplitSpacingValue( value );
			var control = jQuery( '<div class="nf-styles-visual-control nf-styles-visual-control-spacing' + ( isSplit ? ' is-split' : '' ) + ( value && ! isEditable ? ' has-css-value' : '' ) + '" data-spacing-property="' + property + '" />' );
			var sides = [
				{ key: 'top', label: window.nfStylesShared.l10n('top', 'Top' ) },
				{ key: 'right', label: window.nfStylesShared.l10n('right', 'Right' ) },
				{ key: 'bottom', label: window.nfStylesShared.l10n('bottom', 'Bottom' ) },
				{ key: 'left', label: window.nfStylesShared.l10n('left', 'Left' ) }
			];

			control.append( '<div class="nf-styles-visual-range-header"><label for="nf-styles-' + name + '">' + config.label + '</label><span class="nf-styles-visual-control-actions">' + this.renderValueControl( value ? ( isEditable ? value : window.nfStylesShared.l10n('cssBadge', 'CSS' ) ) : window.nfStylesShared.l10n('defaultLabel', 'Default' ), value && isEditable && ! isSplit ? spacing.top : '', unit, name, config ) + this.renderUnitControl( unit, config, value && ! isEditable ) + '<button type="button" class="nf-styles-visual-reset' + ( value ? '' : ' is-empty' ) + '" data-setting-name="' + name + '" aria-label="' + _.escape( window.nfStylesShared.format(window.nfStylesShared.l10n('clearValue', 'Clear %s' ), config.label ) ) + '" title="' + _.escape( window.nfStylesShared.format(window.nfStylesShared.l10n('clearValue', 'Clear %s' ), config.label ) ) + '">×</button></span></div>' );
			control.append( '<input id="nf-styles-' + name + '" class="nf-styles-visual-range nf-styles-visual-spacing-all" type="range" min="' + rangeConfig.min + '" max="' + rangeConfig.max + '" step="' + rangeConfig.step + '" value="' + spacing.top + '" data-setting-name="' + name + '" data-style-group="' + groupName + '" data-unit="' + unit + '" data-default-value="' + ( config.defaultValue || rangeConfig.min ) + '"' + ( value && ! isEditable ? ' data-css-managed="true" disabled' : '' ) + ' />' );
			control.append( '<button type="button" class="nf-styles-visual-sides-toggle" aria-expanded="' + ( isSplit ? 'true' : 'false' ) + '"' + ( value && ! isEditable ? ' data-css-managed="true" disabled' : '' ) + '>' + _.escape( window.nfStylesShared.l10n('sides', 'Sides' ) ) + '</button>' );

			var sideGrid = jQuery( '<div class="nf-styles-visual-sides" />' );
			_.each( sides, function( side ) {
				sideGrid.append( '<label class="nf-styles-visual-side"><span>' + side.label + '</span><input class="nf-styles-visual-side-range" type="range" min="' + rangeConfig.min + '" max="' + rangeConfig.max + '" step="' + rangeConfig.step + '" value="' + spacing[ side.key ] + '" data-side="' + side.key + '" data-setting-name="' + name + '" data-style-group="' + groupName + '" data-unit="' + unit + '"' + ( value && ! isEditable ? ' data-css-managed="true" disabled' : '' ) + ' /></label>' );
			} );
			control.append( sideGrid );

			return control;
		},

		/**
		 * Build the editable numeric value/label markup for a range control.
		 * @since 3.0.30
		 * @param {string}          label       Display label ('Default', 'CSS', or a value).
		 * @param {(number|string)} numeric     Numeric value, or '' when not editable.
		 * @param {string}          unit        Value unit (e.g. px).
		 * @param {string}          settingName Full setting name the value edits.
		 * @param {Object}          config      Control configuration (label/min/max).
		 * @return {string} HTML string for the value control.
		 */
		renderValueControl: function( label, numeric, unit, settingName, config ) {
			if ( '' === numeric || window.nfStylesShared.l10n('cssBadge', 'CSS' ) === label || window.nfStylesShared.l10n('defaultLabel', 'Default' ) === label ) {
				return '<span class="nf-styles-visual-value' + ( window.nfStylesShared.l10n('defaultLabel', 'Default' ) === label ? ' is-default' : '' ) + '">' + label + '</span>';
			}

			return '<span class="nf-styles-visual-value nf-styles-visual-value-editable"><span class="nf-styles-visual-value-input" contenteditable="true" inputmode="numeric" role="spinbutton" aria-valuemin="' + config.min + '" aria-valuemax="' + config.max + '" aria-valuenow="' + numeric + '" data-setting-name="' + settingName + '" data-unit="' + unit + '" aria-label="' + _.escape( window.nfStylesShared.format(window.nfStylesShared.l10n('setValue', 'Set %s' ), config.label ) ) + '">' + numeric + '</span><span class="nf-styles-visual-value-unit">' + unit + '</span></span>';
		},

		/**
		 * Build the unit select for a range control when multiple units exist.
		 * @since 3.0.30
		 * @param {string}  unit     Currently selected unit.
		 * @param {Object}  config   Control configuration (available units/label).
		 * @param {boolean} disabled Whether the select is CSS-managed and disabled.
		 * @return {string} HTML string for the unit select, or '' when single-unit.
		 */
		renderUnitControl: function( unit, config, disabled ) {
			var units = config.units || [ config.unit ];
			var options = '';

			if ( 2 > units.length ) {
				return '';
			}

			_.each( units, function( option ) {
				options += '<option value="' + _.escape( option ) + '"' + ( option === unit ? ' selected' : '' ) + '>' + option + '</option>';
			}, this );

			return '<select class="nf-styles-visual-unit" aria-label="' + _.escape( window.nfStylesShared.format(window.nfStylesShared.l10n('unitFor', '%s unit' ), config.label ) ) + '"' + ( disabled ? ' data-css-managed="true" disabled' : '' ) + '>' + options + '</select>';
		},

		/**
		 * Render a segmented button control (e.g. border style) for a style property.
		 *
		 * @since 3.0.30
		 * @param {string}         property     Style property key (e.g. border-style).
		 * @param {Backbone.Model} settingModel Style setting group model.
		 * @param {Backbone.Model} dataModel    Data model holding the saved value.
		 * @param {string}         groupName    Style group name the control belongs to.
		 * @return {jQuery} The rendered control element.
		 */
		renderSegmentedControl: function( property, settingModel, dataModel, groupName ) {
			var config = this.settings[ property ];
			var name = this.getSettingName( settingModel, property );
			var value = dataModel.get( name ) || '';
			var isEditable = this.isEditableVisualValue( property, value );
			var control = jQuery( '<div class="nf-styles-visual-control nf-styles-visual-control-segmented' + ( value && ! isEditable ? ' has-css-value' : '' ) + '" />' );
			var segments = jQuery( '<div class="nf-styles-visual-segments" />' );

			control.append( '<div class="nf-styles-visual-range-header"><span class="nf-styles-visual-label">' + config.label + '</span><span class="nf-styles-visual-control-actions">' + ( value && ! isEditable ? '<span class="nf-styles-css-value">' + _.escape( window.nfStylesShared.l10n('cssBadge', 'CSS' ) ) + '</span>' : '' ) + '<button type="button" class="nf-styles-visual-reset' + ( value ? '' : ' is-empty' ) + '" data-setting-name="' + name + '" aria-label="' + _.escape( window.nfStylesShared.format(window.nfStylesShared.l10n('clearValue', 'Clear %s' ), config.label ) ) + '" title="' + _.escape( window.nfStylesShared.format(window.nfStylesShared.l10n('clearValue', 'Clear %s' ), config.label ) ) + '">×</button></span></div>' );
			_.each( config.options, function( option ) {
				var activeClass = isEditable && value === option.value ? ' is-active' : '';
				var lineClass = option.value ? ' is-' + option.value : ' is-none';
				segments.append( '<button type="button" class="nf-styles-visual-segment' + activeClass + '" data-setting-name="' + name + '" data-style-group="' + groupName + '" data-value="' + option.value + '"' + ( value && ! isEditable ? ' data-css-managed="true" disabled' : '' ) + '><span class="nf-styles-visual-segment-label">' + option.label + '</span><span class="nf-styles-visual-line-preview' + lineClass + '"></span></button>' );
			} );
			control.append( segments );

			return control;
		}
	};

	return StylesControls;
} );

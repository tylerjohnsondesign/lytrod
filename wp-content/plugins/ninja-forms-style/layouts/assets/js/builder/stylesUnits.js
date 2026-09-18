/**
 * Unit and value parsing utilities for the Visual Styles Editor.
 *
 * Pure functions for validating and parsing CSS values, units, and spacing
 * shorthand. Extracted from stylesVisualEditor.js for testability and reuse.
 *
 * @since 3.0.30
 */
define( [], function() {
	'use strict';

	var StylesUnits = {

		/**
		 * Determine whether a value is a number in the given unit.
		 * @since 3.0.30
		 * @param {string} value Value to test.
		 * @param {string} unit  Unit the value must end with.
		 * @return {boolean} Whether the value matches the unit.
		 */
		isUnitValue: function( value, unit ) {
			var escapedUnit = String( unit || '' ).replace( /[.*+?^${}()|[\]\\]/g, '\\$&' );
			return new RegExp( '^-?\\d+(\\.\\d+)?' + escapedUnit + '$' ).test( String( value || '' ).trim() );
		},

		/**
		 * Determine whether a value uses one of a control's supported units.
		 * @since 3.0.30
		 * @param {string} value  Value to test.
		 * @param {Object} config Control configuration (available units).
		 * @return {boolean} Whether the value uses a supported unit.
		 */
		isSupportedUnitValue: function( value, config ) {
			var self = this;
			return _.some( config.units || [ config.unit ], function( unit ) {
				return self.isUnitValue( value, unit );
			} );
		},

		/**
		 * Determine whether a value is a 1-4 part spacing shorthand in one unit.
		 * @since 3.0.30
		 * @param {string} value  Value to test.
		 * @param {Object} config Control configuration (available units).
		 * @return {boolean} Whether the value is a valid spacing shorthand.
		 */
		isSpacingValue: function( value, config ) {
			var self = this;
			var parts = String( value || '' ).trim().split( /\s+/ ).filter( Boolean );
			var units = config.units || [ config.unit ];
			var matchedUnit;

			if ( ! parts.length || 4 < parts.length ) {
				return false;
			}

			matchedUnit = _.find( units, function( unit ) {
				return _.every( parts, function( part ) {
					return self.isUnitValue( part, unit );
				} );
			} );

			return !! matchedUnit;
		},

		/**
		 * Determine whether a value is a 6-digit hex color.
		 * @since 3.0.30
		 * @param {string} value Value to test.
		 * @return {boolean} Whether the value is a #rrggbb color.
		 */
		isColor: function( value ) {
			return /^#[0-9a-f]{6}$/i.test( value || '' );
		},

		/**
		 * Extract the leading numeric portion of a value, or a fallback.
		 * @since 3.0.30
		 * @param {string} value    Value to read a number from.
		 * @param {*}      fallback Value returned when no number is present.
		 * @return {(string|*)} The numeric string, or the fallback.
		 */
		getNumericValue: function( value, fallback ) {
			var match = String( value || '' ).match( /-?\d+(\.\d+)?/ );
			return match ? match[0] : fallback;
		},

		/**
		 * Detect which configured unit a value (or spacing shorthand) uses.
		 * @since 3.0.30
		 * @param {string} value  Value to inspect.
		 * @param {Object} config Control configuration (available units).
		 * @return {string} The matched unit, or the control's default unit.
		 */
		getValueUnit: function( value, config ) {
			var self = this;
			var units = config.units || [ config.unit ];
			var parts = String( value || '' ).trim().split( /\s+/ ).filter( Boolean );
			var matched = _.find( units, function( unit ) {
				if ( 1 < parts.length ) {
					return _.every( parts, function( part ) {
						return self.isUnitValue( part, unit );
					} );
				}

				return self.isUnitValue( value, unit );
			} );

			if ( matched ) {
				return matched;
			}

			return config.unit;
		},

		/**
		 * Resolve the min/max/step range for a control in a given unit.
		 * @since 3.0.30
		 * @param {Object} config Control configuration with unit options.
		 * @param {string} unit   Unit to resolve the range for.
		 * @return {Object} Range with min, max, and step values.
		 */
		getUnitRangeConfig: function( config, unit ) {
			var unitConfig = config.unitOptions && config.unitOptions[ unit ] ? config.unitOptions[ unit ] : {};

			return {
				min: 'undefined' !== typeof unitConfig.min ? unitConfig.min : config.min,
				max: 'undefined' !== typeof unitConfig.max ? unitConfig.max : config.max,
				step: 'undefined' !== typeof unitConfig.step ? unitConfig.step : 1
			};
		},

		/**
		 * Parse a spacing shorthand into top/right/bottom/left numeric values.
		 * @since 3.0.30
		 * @param {string} value  Spacing shorthand value to parse.
		 * @param {Object} config Control configuration (default/min fallback).
		 * @return {Object} Numeric values keyed by top, right, bottom, and left.
		 */
		getSpacingValues: function( value, config ) {
			var parts = String( value || '' ).trim().split( /\s+/ );
			var fallback = config.defaultValue || config.min;
			var top = this.getNumericValue( parts[0], fallback );
			var right = this.getNumericValue( parts[1], top );
			var bottom = this.getNumericValue( parts[2], top );
			var left = this.getNumericValue( parts[3], right );

			return {
				top: top,
				right: right,
				bottom: bottom,
				left: left
			};
		},

		/**
		 * Determine whether a spacing value specifies more than one side.
		 * @since 3.0.30
		 * @param {string} value Spacing shorthand value to test.
		 * @return {boolean} Whether the value has multiple parts.
		 */
		isSplitSpacingValue: function( value ) {
			return 1 < String( value || '' ).trim().split( /\s+/ ).filter( Boolean ).length;
		}
	};

	return StylesUnits;
} );

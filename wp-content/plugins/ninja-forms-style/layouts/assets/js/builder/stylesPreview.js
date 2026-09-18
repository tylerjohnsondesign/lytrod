/**
 * Preview utilities for the Visual Styles Editor.
 *
 * Handles live preview updates, stylesheet generation, and target resolution
 * for style changes in the form builder. Extracted from stylesVisualEditor.js
 * for better separation of concerns.
 *
 * @since 3.0.30
 */
define( [], function() {
	'use strict';

	var StylesPreview = {

		/**
		 * Control configuration keyed by property name.
		 * Set via init() before using preview functions.
		 * @type {Object}
		 */
		settings: null,

		/**
		 * Form-level style group names.
		 * @type {string[]}
		 */
		formStyleGroups: null,

		/**
		 * Field-level style group names.
		 * @type {string[]}
		 */
		fieldStyleGroups: null,

		/**
		 * Sorted style group names for prefix matching.
		 * Lazily computed on first use.
		 * @type {string[]|null}
		 */
		styleGroupNames: null,

		/**
		 * Initialize the module with controller state references.
		 * @since 3.0.30
		 * @param {Object}   settings         Control configuration keyed by property.
		 * @param {string[]} formStyleGroups  Form-level style group names.
		 * @param {string[]} fieldStyleGroups Field-level style group names.
		 * @return {Object} This module for chaining.
		 */
		init: function( settings, formStyleGroups, fieldStyleGroups ) {
			this.settings = settings;
			this.formStyleGroups = formStyleGroups;
			this.fieldStyleGroups = fieldStyleGroups;
			this.styleGroupNames = null;
			return this;
		},

		/**
		 * Resolve the style group name that a full setting name belongs to.
		 *
		 * Matches the longest style-group prefix first so nested groups resolve
		 * before their shorter counterparts.
		 *
		 * @since 3.0.30
		 * @param {string} name Full setting name (e.g. element_styles_padding).
		 * @return {string} Matched style group name, or '' when none matches.
		 */
		getStyleGroupFromSettingName: function( name ) {
			var matched;

			if ( ! this.styleGroupNames ) {
				this.styleGroupNames = this.formStyleGroups.concat( this.fieldStyleGroups ).sort( function( a, b ) {
					return b.length - a.length;
				} );
			}

			matched = _.find( this.styleGroupNames, function( group ) {
				return 0 === name.indexOf( group + '_' );
			} );

			if ( matched ) {
				return matched;
			}

			return '';
		},

		/**
		 * Build the preview stylesheet selector(s) for a field's style group.
		 * @since 3.0.30
		 * @param {(number|string)} fieldId   Field id (or slug) being previewed.
		 * @param {string}          groupName Style group name to target.
		 * @param {string}          property  Style property being applied.
		 * @return {string} Comma-joined selector(s), or '' when unsupported.
		 */
		getPreviewStylesheetSelector: function( fieldId, groupName, property ) {
			var scope = function( suffix ) {
				var selectors = [
					'#field-' + fieldId + ' ' + suffix,
					'.nf-field-wrap[data-id="' + fieldId + '"] ' + suffix
				];

				if ( ! jQuery.isNumeric( fieldId ) ) {
					selectors.push( '#' + fieldId + ' ' + suffix );
				}

				return selectors.join( ', ' );
			};

			if ( 'label_styles' === groupName ) {
				return scope( '.nf-realistic-field--label' ) + ', ' +
					scope( '.nf-realistic-field--label label' ) + ', ' +
					scope( '.nf-realistic-field--label .nf-label-span' ) + ', ' +
					scope( '.nf-field-label' );
			}

			if ( 'wrap_styles' === groupName ) {
				return scope( '.nf-realistic-field' );
			}

			if ( 'list_item_row_styles' === groupName ) {
				return scope( '.nf-realistic-field--element li' );
			}

			if ( 'list_item_label_styles' === groupName ) {
				return scope( '.nf-realistic-field--element li label' ) + ', ' +
					scope( '.nf-realistic-field--element li > div' );
			}

			if ( 'list_item_element_styles' === groupName ) {
				return scope( '.nf-realistic-field--element li .nf-element' ) + ', ' +
					scope( '.nf-realistic-field--element li input' );
			}

			if ( 0 === groupName.indexOf( 'rating-item' ) ) {
				return scope( '.nf-realistic-field--element .fa-star' ) + ', ' +
					scope( '.nf-realistic-field--element .starrating' );
			}

			if ( 'element_styles' === groupName || 'submit_element_hover_styles' === groupName ) {
				return scope( '.nf-realistic-field--element .ninja-forms-field' ) + ', ' +
					scope( '.nf-realistic-field--element .nf-element' ) + ', ' +
					scope( '.nf-realistic-field--element button' ) + ', ' +
					scope( '.nf-realistic-field--element input' ) + ', ' +
					scope( '.nf-realistic-field--element select' ) + ', ' +
					scope( '.nf-realistic-field--element textarea' );
			}

			return '';
		},

		/**
		 * Set or remove an !important inline style on preview target element(s).
		 * @since 3.0.30
		 * @param {jQuery} target   Preview target element(s).
		 * @param {string} property CSS property to set or remove.
		 * @param {string} value    Value to apply, or '' to remove the property.
		 * @return {void}
		 */
		setPreviewStyle: function( target, property, value ) {
			target.each( function() {
				if ( value ) {
					this.style.setProperty( property, value, 'important' );
				} else {
					this.style.removeProperty( property );
				}
			} );
		},

		/**
		 * Resolve the element-level preview target for a field by type and property.
		 *
		 * @since 3.0.30
		 * @param {jQuery}         field     Rendered field wrapper to search within.
		 * @param {Backbone.Model} dataModel Field data model (provides the field type).
		 * @param {string}         property  Style property being applied.
		 * @return {jQuery} Matched preview target element(s).
		 */
		getElementPreviewTarget: function( field, dataModel, property ) {
			var type = dataModel.get( 'type' );
			var element = field.find( '.nf-realistic-field--element' );
			var target;

			if ( ! element.length ) {
				if ( -1 !== [ 'html', 'listimage', 'note', 'repeater', 'spam', 'terms' ].indexOf( type ) ) {
					return field;
				}

				return jQuery();
			}

			if ( -1 !== [ 'listselect', 'listmultiselect', 'listcountry', 'liststate' ].indexOf( type ) ) {
				if ( 'listmultiselect' === type ) {
					return element.find( 'select.nf-element' ).first();
				}

				if ( -1 !== [ 'background-color', 'border', 'border-style', 'border-color', 'margin' ].indexOf( property ) ) {
					target = element.find( 'select.nf-element + div' ).first();
				} else {
					target = element.find( 'select.nf-element, select.nf-element + div' );
				}

				return target;
			}

			if ( -1 !== [ 'listcheckbox', 'listradio' ].indexOf( type ) ) {
				if ( -1 !== [ 'background-color', 'border', 'border-style', 'border-color', 'padding', 'margin' ].indexOf( property ) ) {
					target = element.find( 'li' );
				} else {
					target = element.find( 'li > div' );
				}

				return target;
			}

			if ( 'checkbox' === type ) {
				return field.find( '.nf-realistic-field--label label, .nf-realistic-field--element input[type="checkbox"]' ).first();
			}

			if ( -1 !== [ 'button', 'submit' ].indexOf( type ) ) {
				return element.find( 'button, input[type="button"], input[type="submit"], .nf-element' ).first();
			}

			if ( 'hr' === type ) {
				return element.find( 'hr' ).first();
			}

			if ( 'starrating' === type ) {
				return element.find( '.fa-star, .starrating' );
			}

			if ( 'signature' === type ) {
				return element.find( 'canvas, .signature-pad, .nf-element' ).first();
			}

			if ( 'terms' === type || 'listimage' === type ) {
				if ( -1 !== [ 'background-color', 'border', 'border-style', 'border-color', 'padding', 'margin' ].indexOf( property ) ) {
					return element.find( 'li' );
				}

				return element.find( 'li label, li > div' );
			}

			return element.find( '.ninja-forms-field, .nf-element' ).first();
		},

		/**
		 * Resolve the builder preview element(s) a style group should apply to.
		 *
		 * @since 3.0.30
		 * @param {Backbone.Model} dataModel              Field data model being previewed.
		 * @param {string}         groupName              Style group name (e.g. label_styles).
		 * @param {jQuery}         fieldEl                Rendered field element, or empty to look it up.
		 * @param {string}         property               Style property being applied.
		 * @param {Function}       getRenderedFieldElement Function to get rendered field element.
		 * @return {jQuery} Matched preview target element(s).
		 */
		getPreviewTarget: function( dataModel, groupName, fieldEl, property, getRenderedFieldElement ) {
			var field = fieldEl && fieldEl.length ? fieldEl : getRenderedFieldElement( dataModel );
			var activeField;
			var target;

			if ( ! fieldEl ) {
				activeField = jQuery( '.nf-field-wrap.' + dataModel.get( 'type' ) + '.active' );
				if ( activeField.length ) {
					field = activeField.first();
				}
			}

			if ( -1 !== [ 'html', 'listimage', 'note', 'repeater', 'spam', 'terms' ].indexOf( dataModel.get( 'type' ) ) && ( 'element_styles' === groupName || 'wrap_styles' === groupName ) ) {
				target = field;
				if ( target.length ) {
					return target;
				}
			}

			if ( ! field.length ) {
				field = jQuery( '.nf-field-wrap[data-id="' + dataModel.get( 'id' ) + '"]' );
			}

			if ( ! field.length ) {
				field = jQuery( '.nf-field-wrap.active' );
			}

			if ( 'label_styles' === groupName ) {
				target = field.find( '.nf-realistic-field--label, .nf-realistic-field--label #nf-label-field-' + dataModel.get( 'id' ) + ', .nf-realistic-field--label label, .nf-realistic-field--label .nf-label-span, .nf-realistic-field--label .nf-field-label, .nf-realistic-field--label .nf-field-label > div' );

				if ( ! target.length && dataModel.get( 'editActive' ) ) {
					target = jQuery( '.nf-field-wrap.active' ).find( '.nf-realistic-field--label, .nf-realistic-field--label #nf-label-field-' + dataModel.get( 'id' ) + ', .nf-realistic-field--label label, .nf-realistic-field--label .nf-label-span, .nf-realistic-field--label .nf-field-label, .nf-realistic-field--label .nf-field-label > div' );
				}

				if ( ! target.length ) {
					target = field.find( '.nf-placeholder-label .nf-field-label, .nf-field-label' ).first();
				}

				return target;
			}

			if ( 'list_item_row_styles' === groupName ) {
				target = field.find( '.nf-realistic-field--element li' );

				if ( ! target.length && dataModel.get( 'editActive' ) ) {
					target = jQuery( '.nf-field-wrap.active' ).find( '.nf-realistic-field--element li' );
				}

				return target;
			}

			if ( 'list_item_label_styles' === groupName ) {
				target = field.find( '.nf-realistic-field--element li label, .nf-realistic-field--element li > div' );

				if ( ! target.length && dataModel.get( 'editActive' ) ) {
					target = jQuery( '.nf-field-wrap.active' ).find( '.nf-realistic-field--element li label, .nf-realistic-field--element li > div' );
				}

				return target;
			}

			if ( 'list_item_element_styles' === groupName ) {
				target = field.find( '.nf-realistic-field--element li .nf-element, .nf-realistic-field--element li input' );

				if ( ! target.length && dataModel.get( 'editActive' ) ) {
					target = jQuery( '.nf-field-wrap.active' ).find( '.nf-realistic-field--element li .nf-element, .nf-realistic-field--element li input' );
				}

				return target;
			}

			if ( 0 === groupName.indexOf( 'rating-item' ) ) {
				target = field.find( '.nf-realistic-field--element .fa-star, .nf-realistic-field--element .starrating' );

				if ( ! target.length && dataModel.get( 'editActive' ) ) {
					target = jQuery( '.nf-field-wrap.active' ).find( '.nf-realistic-field--element .fa-star, .nf-realistic-field--element .starrating' );
				}

				return target;
			}

			if ( 'wrap_styles' === groupName ) {
				target = field.find( '.nf-realistic-field' ).first();

				if ( ! target.length && dataModel.get( 'editActive' ) ) {
					target = jQuery( '.nf-field-wrap.active' ).find( '.nf-realistic-field' ).first();
				}

				return target.length ? target : field;
			}

			target = this.getElementPreviewTarget( field, dataModel, property );

			if ( ! target.length && dataModel.get( 'editActive' ) ) {
				target = this.getElementPreviewTarget( jQuery( '.nf-field-wrap.active' ), dataModel, property );
			}

			return target;
		},

		/**
		 * Filter preview targets to exclude those already claimed by another pass.
		 *
		 * When setting a value, claims the target for the current property.
		 * When clearing a value, filters out already-claimed targets.
		 *
		 * @since 3.0.30
		 * @param {jQuery}      target      Preview target element(s).
		 * @param {string}      cssProperty CSS property being applied.
		 * @param {string}      value       Value being applied ('' = clearing).
		 * @param {Array|null}  claims      Claims array, or null to skip claim logic.
		 * @return {jQuery} Filtered target element(s).
		 */
		claimPreviewTargets: function( target, cssProperty, value, claims ) {
			var isClaimed = function( el ) {
				return _.some( claims, function( claim ) {
					return claim.el === el && claim.property === cssProperty;
				} );
			};

			if ( ! claims ) {
				return target;
			}

			if ( ! value ) {
				return target.filter( function() {
					return ! isClaimed( this );
				} );
			}

			target.each( function() {
				if ( ! isClaimed( this ) ) {
					claims.push( { el: this, property: cssProperty } );
				}
			} );

			return target;
		},

		/**
		 * Rebuild the injected builder preview stylesheet from the value cache.
		 * @since 3.0.30
		 * @param {Object} previewStyleCache Cache of preview values by field ID.
		 * @return {void}
		 */
		renderBuilderPreviewStylesheet: function( previewStyleCache ) {
			var self = this;
			var css = '';
			var styleEl;

			_.each( previewStyleCache, function( values, fieldId ) {
				_.each( values, function( value, name ) {
					var groupName = self.getStyleGroupFromSettingName( name );
					var property = groupName ? name.replace( groupName + '_', '' ) : '';
					var cssProperty = 'border' === property ? 'border-width' : property;
					var selector;

					if ( ! value || ! groupName || ! self.settings[ property ] ) {
						return;
					}

					selector = self.getPreviewStylesheetSelector( fieldId, groupName, property );
					if ( ! selector ) {
						return;
					}

					css += selector + '{' + cssProperty + ':' + value + ' !important;}';
				} );
			} );

			jQuery( '#nf-styles-builder-preview-cache' ).remove();

			if ( css ) {
				styleEl = document.createElement( 'style' );
				styleEl.id = 'nf-styles-builder-preview-cache';
				styleEl.textContent = css;
				document.head.appendChild( styleEl );
			}
		}
	};

	return StylesPreview;
} );

/**
 * Shared Layout & Styles visual editor helpers.
 *
 * The single implementation of the color, range, spacing, tooltip, and
 * reset-dialog helpers used by both visual style surfaces: the standalone
 * Styling page (styles/assets/js/admin.js) and the form builder's visual
 * styles editor (layouts/assets/js/builder/controllers/stylesVisualEditor.js).
 * Both surfaces delegate here so behavior cannot drift between them.
 *
 * Translation-agnostic: every user-facing string is passed in by the consumer,
 * which reads it from its own PHP-localized object. jQuery is the only
 * dependency, and this file must load before both consumers.
 *
 * @since 3.0.30
 */
window.nfStylesShared = {
	/**
	 * Normalize a color string to a lowercase 6-digit hex value.
	 *
	 * Expands shorthand and adds a leading '#'; returns '' when invalid.
	 * @since 3.0.30
	 * @param {string} value Raw color string to normalize.
	 * @return {string} Normalized #rrggbb value, or '' when invalid.
	 */
	normalizeHexColor: function( value ) {
		value = String( value || '' ).trim();

		if ( /^[0-9a-f]{3}$/i.test( value ) || /^[0-9a-f]{6}$/i.test( value ) ) {
			value = '#' + value;
		}

		if ( /^#[0-9a-f]{3}$/i.test( value ) ) {
			value = value.replace( /^#([0-9a-f])([0-9a-f])([0-9a-f])$/i, '#$1$1$2$2$3$3' );
		}

		return /^#[0-9a-f]{6}$/i.test( value ) ? value.toLowerCase() : '';
	},

	/**
	 * Escape a value for safe use inside an HTML attribute.
	 * @since 3.0.30
	 * @param {string} value Raw value to escape.
	 * @return {string} The attribute-escaped value.
	 */
	escapeAttr: function( value ) {
		return String( value || '' ).replace( /&/g, '&amp;' ).replace( /"/g, '&quot;' ).replace( /</g, '&lt;' ).replace( />/g, '&gt;' );
	},

	/**
	 * Read a translated UI string from the PHP-localized object.
	 * @since 3.0.30
	 * @param {string} key      Key within the localized object.
	 * @param {string} fallback English fallback when no localized string exists.
	 * @return {string} The translated string, or the fallback.
	 */
	l10n: function( key, fallback ) {
		var strings = window.nfStylesL10n || {};
		return strings[ key ] || fallback;
	},

	/**
	 * Insert a value into a translated '%s' template.
	 * @since 3.0.30
	 * @param {string} template Template containing a '%s' placeholder.
	 * @param {string} value    Value to insert.
	 * @return {string} The formatted string.
	 */
	format: function( template, value ) {
		return String( template ).replace( '%s', value );
	},

	/**
	 * Get the translated labels the color popover builder needs.
	 * @since 3.0.30
	 * @return {Object} Labels keyed by theme, themeColors, hex, openColorPicker.
	 */
	getColorPopoverLabels: function() {
		return {
			theme: this.l10n( 'theme', 'Theme' ),
			themeColors: this.l10n( 'themeColors', 'Theme colors' ),
			hex: this.l10n( 'hex', 'Hex' ),
			openColorPicker: this.l10n( 'openColorPicker', 'Open color picker' )
		};
	},

	/**
	 * Get the configured theme palette, filtered to valid hex colors.
	 *
	 * Every entry passes through normalizeHexColor, so 3-digit hex palette
	 * colors are accepted and normalized to their 6-digit form on both
	 * surfaces.
	 *
	 * @since 3.0.30
	 * @return {Array} Palette entries with a normalized color value.
	 */
	getThemePalette: function() {
		var palette = [];

		jQuery.each( window.nfStylesThemePalette || [], function( index, color ) {
			var normalized = color ? window.nfStylesShared.normalizeHexColor( color.color ) : '';

			if ( ! normalized ) {
				return;
			}

			palette.push( { name: color.name, color: normalized } );
		} );

		return palette;
	},

	/**
	 * Build the theme color swatches, marking the active color.
	 * @since 3.0.30
	 * @param {string} value  Currently selected color value, if any.
	 * @param {Object} labels Translated strings ({ themeColors }).
	 * @return {jQuery} The rendered theme palette element.
	 */
	renderThemePalette: function( value, labels ) {
		var shared = this;
		var palette = this.getThemePalette();
		var normalized = this.normalizeHexColor( value );
		var colors = jQuery( '<div class="nf-styles-theme-palette" aria-label="' + this.escapeAttr( labels.themeColors ) + '" />' );

		jQuery.each( palette, function( index, color ) {
			var label = color.name ? color.name + ' ' + color.color : color.color;
			var activeClass = normalized && color.color === normalized ? ' is-active' : '';
			colors.append( '<button type="button" class="nf-styles-theme-color' + activeClass + '" data-color="' + shared.escapeAttr( color.color ) + '" aria-label="' + shared.escapeAttr( label ) + '" title="' + shared.escapeAttr( label ) + '"><span style="background-color:' + shared.escapeAttr( color.color ) + '"></span></button>' );
		} );

		return colors;
	},

	/**
	 * Build the color picker popover (theme swatches and hex input).
	 * @since 3.0.30
	 * @param {string} value  Current color value to preselect.
	 * @param {Object} labels Translated strings ({ theme, themeColors, hex, openColorPicker }).
	 * @return {jQuery} The rendered color popover element.
	 */
	renderColorPopover: function( value, labels ) {
		var popover = jQuery( '<div class="nf-styles-color-popover" />' );
		var hexValue = this.normalizeHexColor( value );

		popover.append( '<div class="nf-styles-color-popover-title">' + this.escapeAttr( labels.theme ) + '</div>' );
		popover.append( this.renderThemePalette( hexValue, labels ) );
		popover.append( '<div class="nf-styles-color-custom-row"><label>' + this.escapeAttr( labels.hex ) + '</label><input type="text" class="nf-styles-color-hex" value="' + this.escapeAttr( hexValue ) + '" placeholder="#000000" maxlength="7" inputmode="text" autocomplete="off"><button type="button" class="nf-styles-color-custom" aria-label="' + this.escapeAttr( labels.openColorPicker ) + '" title="' + this.escapeAttr( labels.openColorPicker ) + '"><span style="background-color:' + this.escapeAttr( hexValue || '#ffffff' ) + '"></span></button></div>' );

		return popover;
	},

	/**
	 * Open or close color swatch popovers, keeping the trigger's state in sync.
	 *
	 * The swatch chip is the keyboard-reachable trigger, so its aria-expanded
	 * has to track the popover rather than only the container's class.
	 *
	 * @since 3.0.30
	 * @param {jQuery}  swatches Swatch controls to update.
	 * @param {boolean} open     Whether the popover should be open.
	 * @return {void}
	 */
	setSwatchOpen: function( swatches, open ) {
		if ( ! swatches || ! swatches.length ) {
			return;
		}

		swatches.toggleClass( 'is-open', !! open );
		swatches.find( '.nf-styles-visual-swatch-chip' ).attr( 'aria-expanded', open ? 'true' : 'false' );
	},

	/**
	 * Mark the theme swatch matching a color value as active within a control.
	 * @since 3.0.30
	 * @param {jQuery} control Color swatch control element.
	 * @param {string} value   Color value to match against the palette.
	 * @return {void}
	 */
	syncPaletteSelection: function( control, value ) {
		var normalized = String( value || '' ).toLowerCase();
		control.find( '.nf-styles-theme-color' ).each( function() {
			var swatch = jQuery( this );
			swatch.toggleClass( 'is-active', normalized && String( swatch.data( 'color' ) || '' ).toLowerCase() === normalized );
		} );
	},

	/**
	 * Show a modal confirmation dialog before a destructive reset.
	 *
	 * Removes any pre-existing reset modal before opening a new one.
	 * @since 3.0.30
	 * @param {string}   title       Dialog heading text.
	 * @param {string}   content     Dialog body text.
	 * @param {string}   confirmText Confirm button label.
	 * @param {string}   cancelText  Cancel button label.
	 * @param {Function} onConfirm   Callback invoked when the reset is confirmed.
	 * @return {void}
	 */
	confirmReset: function( title, content, confirmText, cancelText, onConfirm ) {
		var modal = jQuery( '<div class="nf-styles-confirm-reset" role="dialog" aria-modal="true" />' );
		var dialog = jQuery( '<div class="nf-styles-confirm-reset-dialog" />' );
		var actions = jQuery( '<div class="nf-styles-confirm-reset-actions" />' );
		var close = function() {
			modal.remove();
			jQuery( document ).off( 'keydown.nfStylesResetModal' );
		};

		jQuery( '.nf-styles-confirm-reset' ).remove();

		dialog.append( jQuery( '<h2 />' ).text( title ) );
		dialog.append( jQuery( '<p />' ).text( content ) );
		actions.append( jQuery( '<button type="button" class="nf-styles-confirm-reset-secondary" />' ).text( cancelText ).on( 'click', close ) );
		actions.append( jQuery( '<button type="button" class="nf-styles-confirm-reset-primary" />' ).text( confirmText ).on( 'click', function() {
			close();
			onConfirm();
		} ) );
		dialog.append( actions );
		modal.append( dialog );
		modal.on( 'click', function( e ) {
			if ( e.target === modal[0] ) {
				close();
			}
		} );

		jQuery( document ).off( 'keydown.nfStylesResetModal' ).on( 'keydown.nfStylesResetModal', function( e ) {
			if ( 'Escape' === e.key ) {
				close();
			}
			if ( 'Enter' === e.key && modal.is( ':visible' ) ) {
				e.preventDefault();
				close();
				onConfirm();
			}
		} );

		jQuery( 'body' ).append( modal );
		modal.find( '.nf-styles-confirm-reset-secondary' ).focus();
	},

	/**
	 * Initialize jBox tooltips for any help triggers within a scope.
	 * @since 3.0.30
	 * @param {jQuery} scope Container to search for help triggers.
	 * @return {void}
	 */
	initTooltips: function( scope ) {
		scope.find( '.nf-help' ).each( function() {
			var trigger = jQuery( this );
			var content = trigger.next( '.nf-help-text' );

			if ( ! content.length ) {
				return;
			}

			if ( 'function' !== typeof trigger.jBox ) {
				window.nfStylesShared.initHelpFallback( trigger );
				return;
			}

			trigger.jBox( 'Tooltip', {
				content: content,
				maxWidth: 200,
				theme: 'TooltipBorder',
				trigger: 'click',
				closeOnClick: true
			} );
		} );
	},

	/**
	 * Reveal help text with a plain positioned bubble when jBox is absent.
	 *
	 * jBox ships with core, so a missing plugin used to leave the help icon
	 * silently inert. This keeps the text reachable on its own.
	 *
	 * @since 3.0.30
	 * @param {jQuery} trigger Help trigger button.
	 * @return {void}
	 */
	initHelpFallback: function( trigger ) {
		var wrap = trigger.closest( '.nf-help-wrap' );

		trigger.on( 'click', function( e ) {
			e.preventDefault();
			wrap.toggleClass( 'is-open' );
		} );

		jQuery( document )
			.on( 'click.nfStylesHelp', function( e ) {
				if ( ! jQuery( e.target ).closest( '.nf-help-wrap' ).length ) {
					wrap.removeClass( 'is-open' );
				}
			} )
			.on( 'keydown.nfStylesHelp', function( e ) {
				if ( 'Escape' === e.key ) {
					wrap.removeClass( 'is-open' );
				}
			} );
	},

	/**
	 * Enable only the inputs for the active mode's panel within a container.
	 * @since 3.0.30
	 * @param {jQuery} container Section or fieldset holding the mode panels.
	 * @param {string} mode      Active editing mode ('css' or 'design').
	 * @return {void}
	 */
	setPanelInputs: function( container, mode ) {
		container.find( '[data-style-mode-panel]' ).each( function() {
			var panel = jQuery( this );
			var isActive = panel.data( 'style-mode-panel' ) === mode;

			panel.find( ':input' ).each( function() {
				var input = jQuery( this );
				input.prop( 'disabled', ! isActive || !! input.attr( 'data-css-managed' ) );
			} );
		} );
	},

	/**
	 * Clamp a numeric value to a range input's min/max bounds.
	 * @since 3.0.30
	 * @param {(number|string)} value Raw value to normalize.
	 * @param {jQuery}          range Range input providing min/max bounds.
	 * @return {string} The clamped numeric value as a string.
	 */
	normalizeRangeValue: function( value, range ) {
		var min = Number( range.attr( 'min' ) );
		var max = Number( range.attr( 'max' ) );
		var numeric = Number( value );

		if ( isNaN( numeric ) ) {
			numeric = Number( range.val() || range.data( 'default-value' ) || min || 0 );
		}

		if ( ! isNaN( min ) ) {
			numeric = Math.max( min, numeric );
		}

		if ( ! isNaN( max ) ) {
			numeric = Math.min( max, numeric );
		}

		return String( numeric );
	},

	/**
	 * Collapse per-side spacing values into the shortest CSS shorthand.
	 * @since 3.0.30
	 * @param {Object} values Spacing values keyed by top/right/bottom/left.
	 * @return {string} The shorthand spacing value.
	 */
	getSpacingShorthand: function( values ) {
		if ( values.top === values.right && values.top === values.bottom && values.top === values.left ) {
			return values.top;
		}

		if ( values.top === values.bottom && values.right === values.left ) {
			return values.top + ' ' + values.right;
		}

		if ( values.right === values.left ) {
			return values.top + ' ' + values.right + ' ' + values.bottom;
		}

		return values.top + ' ' + values.right + ' ' + values.bottom + ' ' + values.left;
	},

	/**
	 * Read a spacing control's per-side ranges into a shorthand value.
	 * @since 3.0.30
	 * @param {jQuery} control           Spacing control element.
	 * @param {string} sideRangeSelector Selector for the per-side range inputs.
	 * @return {string} The shorthand spacing value with units.
	 */
	getSpacingFromSides: function( control, sideRangeSelector ) {
		var unit = control.find( sideRangeSelector ).first().data( 'unit' ) || '';
		var values = {
			top: control.find( '[data-side="top"]' ).val() + unit,
			right: control.find( '[data-side="right"]' ).val() + unit,
			bottom: control.find( '[data-side="bottom"]' ).val() + unit,
			left: control.find( '[data-side="left"]' ).val() + unit
		};

		return this.getSpacingShorthand( values );
	}
};

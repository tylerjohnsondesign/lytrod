jQuery( document ).ready( function( $ ) {
	$( '#ninja-forms-styles-field-type-selector' ).on( 'change', function() {
		var url = new URL( window.location.href );
		var value = $( this ).val();

		if ( value ) {
			url.searchParams.set( 'field_type', value );
		} else {
			url.searchParams.delete( 'field_type' );
		}

		window.location.href = url.toString();
	} );

	$( document ).on( 'click', '.nf-styles-admin-section-header', function() {
		ninjaFormsStyles.toggleSection( this );
	} );

	$( '.nf-styles-admin-section' ).each( function() {
		ninjaFormsStyles.setPanelInputs( $( this ), 'design' );
	} );
	ninjaFormsStyles.initTooltips( $( document ) );

	$( document ).on( 'change', '.nf-styles-mode-switch', function() {
		ninjaFormsStyles.setEditingMode( $( this ).closest( '.nf-styles-admin-section' ), this.checked ? 'css' : 'design' );
	} );

	$( document ).on( 'click', '.nf-styles-reset-section', function( e ) {
		var section = $( this ).closest( '.nf-styles-admin-section' );
		var label = section.find( '.nf-styles-admin-section-header span' ).first().text() || ninjaFormsStyles.l10n( 'thisSection', 'this section' );

		e.preventDefault();
		ninjaFormsStyles.confirmReset(
			ninjaFormsStyles.format( ninjaFormsStyles.l10n( 'resetSectionTitle', 'Reset %s?' ), label ),
			ninjaFormsStyles.format( ninjaFormsStyles.l10n( 'resetSectionBody', 'This will clear Design and CSS mode values for %s. This cannot be undone after you save.' ), label ),
			ninjaFormsStyles.l10n( 'resetSectionConfirm', 'Reset Section' ),
			function() {
				ninjaFormsStyles.resetSection( section );
			}
		);
	} );

	$( document ).on( 'click', '.nf-styles-reset-all-settings', function( e ) {
		var button = $( this );
		var form = button.closest( 'form' );

		e.preventDefault();
		ninjaFormsStyles.confirmReset(
			ninjaFormsStyles.l10n( 'clearAllTitle', 'Clear all styles?' ),
			ninjaFormsStyles.l10n( 'clearAllBody', 'This will clear all Layout & Styles settings, including Design and CSS mode values. This cannot be undone after you save.' ),
			ninjaFormsStyles.l10n( 'clearAllConfirm', 'Clear All Styles' ),
			function() {
				if ( ! form.find( 'input[name="nuke_styles"]' ).not( button ).length ) {
					form.append( '<input type="hidden" name="nuke_styles" value="' + button.val() + '">' );
				}
				form.get( 0 ).submit();
			}
		);
	} );

	$( document ).on( 'input change', '.nf-styles-admin-color', function() {
		ninjaFormsStyles.removeResetClearInput( $( this ).closest( '.nf-styles-admin-section' ), $( this ).closest( '.nf-styles-admin-control' ).find( '.nf-styles-admin-value' ).attr( 'name' ) );
		ninjaFormsStyles.updateColorControl( this );
	} );

	$( document ).on( 'click', '.nf-styles-visual-swatch', function( e ) {
		var target = $( e.target );
		var control = target.closest( '.nf-styles-visual-swatch' );

		if ( target.closest( '.nf-styles-visual-reset, .nf-styles-color-popover, .nf-styles-admin-color' ).length ) {
			return;
		}

		e.preventDefault();
		ninjaFormsStyles.setSwatchOpen( $( '.nf-styles-visual-swatch' ).not( control ), false );
		ninjaFormsStyles.setSwatchOpen( control, ! control.hasClass( 'is-open' ) );
	} );

	$( document ).on( 'click', function( e ) {
		if ( ! $( e.target ).closest( '.nf-styles-visual-swatch' ).length ) {
			ninjaFormsStyles.setSwatchOpen( $( '.nf-styles-visual-swatch' ), false );
		}
	} );

	$( document ).on( 'click', '.nf-styles-visual-reset', function( e ) {
		e.preventDefault();
		e.stopPropagation();

		if ( $( this ).closest( '.nf-styles-visual-swatch' ).length ) {
			ninjaFormsStyles.resetColorControl( this );
			return;
		}

		ninjaFormsStyles.resetVisualControl( this );
	} );

	$( document ).on( 'click', '.nf-styles-theme-color', function( e ) {
		e.preventDefault();
		e.stopPropagation();
		ninjaFormsStyles.applyThemeColor( this );
	} );

	$( document ).on( 'click', '.nf-styles-color-custom', function( e ) {
		e.preventDefault();
		e.stopPropagation();
		$( this ).closest( '.nf-styles-admin-control' ).find( '.nf-styles-admin-color' ).trigger( 'click' );
	} );

	$( document ).on( 'keydown', '.nf-styles-color-hex', function( e ) {
		if ( 'Enter' === e.key ) {
			e.preventDefault();
			$( this ).blur();
		}
	} );

	$( document ).on( 'blur change', '.nf-styles-color-hex', function() {
		ninjaFormsStyles.applyHexColor( this );
	} );

	$( '.nf-styles-visual-swatch' ).each( function() {
		ninjaFormsStyles.addColorPopover( $( this ) );
	} );

	$( document ).on( 'input change', '.nf-styles-admin-range', function() {
		var range = $( this );
		var control = range.closest( '.nf-styles-admin-control' );
		var value = range.val() + ( range.data( 'unit' ) || '' );

		ninjaFormsStyles.removeResetClearInput( control.closest( '.nf-styles-admin-section' ), control.find( '.nf-styles-admin-value' ).attr( 'name' ) );
		control.find( '.nf-styles-admin-value' ).val( value );
		ninjaFormsStyles.updateEditableValueLabel( control, range.val(), range.data( 'unit' ) || '' );
		control.find( '.nf-styles-visual-reset' ).removeClass( 'is-empty' );
	} );

	$( document ).on( 'change', '.nf-styles-admin-unit', function() {
		var unitControl = $( this );
		var control = unitControl.closest( '.nf-styles-admin-control' );
		var unit = unitControl.val();
		var rangeConfig = ninjaFormsStyles.getUnitRangeConfigFromControl( unitControl, unit );

		control.find( '.nf-styles-visual-range, .nf-styles-admin-side-range' )
			.attr( {
				min: rangeConfig.min,
				max: rangeConfig.max,
				step: rangeConfig.step
			} )
			.data( 'unit', unit )
			.attr( 'data-unit', unit );
		control.find( '.nf-styles-admin-value-input' ).data( 'unit', unit ).attr( 'data-unit', unit );
		ninjaFormsStyles.removeResetClearInput( control.closest( '.nf-styles-admin-section' ), control.find( '.nf-styles-admin-value' ).attr( 'name' ) );

		if ( control.hasClass( 'nf-styles-visual-control-spacing' ) ) {
			if ( control.hasClass( 'is-split' ) ) {
				ninjaFormsStyles.updateSpacingFromSides( control );
			} else {
				ninjaFormsStyles.updateSpacingValue( control, control.find( '.nf-styles-admin-spacing-all' ).val() + unit );
			}
			return;
		}

		control.find( '.nf-styles-admin-value' ).val( control.find( '.nf-styles-admin-range' ).val() + unit );
		ninjaFormsStyles.updateEditableValueLabel( control, control.find( '.nf-styles-admin-range' ).val(), unit );
		control.find( '.nf-styles-visual-reset' ).removeClass( 'is-empty' );
	} );

	$( document ).on( 'keydown', '.nf-styles-admin-value-input', function( e ) {
		if ( 'Enter' === e.key ) {
			e.preventDefault();
			$( this ).blur();
		}
	} );

	$( document ).on( 'blur', '.nf-styles-admin-value-input', function() {
		var input = $( this );
		var control = input.closest( '.nf-styles-admin-control' );
		var range = control.find( '.nf-styles-visual-range' ).first();
		var value = ninjaFormsStyles.normalizeRangeValue( input.text(), range );

		input.text( value ).attr( 'aria-valuenow', value );
		range.val( value );
		ninjaFormsStyles.removeResetClearInput( control.closest( '.nf-styles-admin-section' ), control.find( '.nf-styles-admin-value' ).attr( 'name' ) );

		if ( range.hasClass( 'nf-styles-admin-spacing-all' ) ) {
			if ( control.hasClass( 'is-split' ) ) {
				control.find( '.nf-styles-admin-side-range' ).val( value );
				ninjaFormsStyles.updateSpacingFromSides( control );
				return;
			}

			ninjaFormsStyles.updateSpacingValue( control, value + ( input.data( 'unit' ) || '' ) );
			return;
		}

		control.find( '.nf-styles-admin-value' ).val( value + ( input.data( 'unit' ) || '' ) );
		ninjaFormsStyles.updateEditableValueLabel( control, value, input.data( 'unit' ) || '' );
		control.find( '.nf-styles-visual-reset' ).removeClass( 'is-empty' );
	} );

	$( document ).on( 'input change', '.nf-styles-admin-spacing-all', function() {
		var range = $( this );
		var control = range.closest( '.nf-styles-admin-control' );

		ninjaFormsStyles.removeResetClearInput( control.closest( '.nf-styles-admin-section' ), control.find( '.nf-styles-admin-value' ).attr( 'name' ) );
		if ( control.hasClass( 'is-split' ) ) {
			control.find( '.nf-styles-admin-side-range' ).val( range.val() );
			ninjaFormsStyles.updateSpacingFromSides( control );
			return;
		}

		ninjaFormsStyles.updateSpacingValue( control, range.val() + ( range.data( 'unit' ) || '' ) );
	} );

	$( document ).on( 'input change', '.nf-styles-admin-side-range', function() {
		var control = $( this ).closest( '.nf-styles-admin-control' );
		ninjaFormsStyles.removeResetClearInput( control.closest( '.nf-styles-admin-section' ), control.find( '.nf-styles-admin-value' ).attr( 'name' ) );
		ninjaFormsStyles.updateSpacingFromSides( control );
	} );

	$( document ).on( 'input change', '.nf-styles-css-editor :input[name]', function() {
		ninjaFormsStyles.removeResetClearInput( $( this ).closest( '.nf-styles-admin-section' ), $( this ).attr( 'name' ) );
	} );

	$( document ).on( 'click', '.nf-styles-visual-sides-toggle', function() {
		var button = $( this );
		var control = button.closest( '.nf-styles-admin-control' );
		var expanded = ! control.hasClass( 'is-split' );

		control.toggleClass( 'is-split', expanded );
		button.attr( 'aria-expanded', expanded ? 'true' : 'false' );

		if ( expanded ) {
			control.find( '.nf-styles-admin-side-range' ).val( control.find( '.nf-styles-admin-spacing-all' ).val() );
			ninjaFormsStyles.updateSpacingFromSides( control );
		} else {
			ninjaFormsStyles.updateSpacingValue( control, control.find( '.nf-styles-admin-spacing-all' ).val() + ( control.find( '.nf-styles-admin-spacing-all' ).data( 'unit' ) || '' ) );
		}
	} );

	$( document ).on( 'click', '.nf-styles-visual-segment', function() {
		var button = $( this );
		var control = button.closest( '.nf-styles-admin-control' );

		button.siblings().removeClass( 'is-active' );
		button.addClass( 'is-active' );
		ninjaFormsStyles.removeResetClearInput( control.closest( '.nf-styles-admin-section' ), control.find( '.nf-styles-admin-value' ).attr( 'name' ) );
		control.find( '.nf-styles-admin-value' ).val( button.data( 'value' ) );
		control.find( '.nf-styles-visual-reset' ).toggleClass( 'is-empty', ! button.data( 'value' ) );
	} );
} );

var ninjaFormsStyles = {
	/**
	 * Read a translated UI string from the shared localization object.
	 * @since 3.0.30
	 * @param {string} key      Key within the localized object.
	 * @param {string} fallback English fallback when no localized string exists.
	 * @return {string} The translated string, or the fallback.
	 */
	l10n: function( key, fallback ) {
		return window.nfStylesShared.l10n( key, fallback );
	},

	/**
	 * Insert a value into a translated '%s' template.
	 * @since 3.0.30
	 * @param {string} template Template containing a '%s' placeholder.
	 * @param {string} value    Value to insert.
	 * @return {string} The formatted string.
	 */
	format: function( template, value ) {
		return window.nfStylesShared.format( template, value );
	},

	/**
	 * Show a modal confirmation dialog before a destructive reset.
	 * @since 3.0.30
	 * @param {string}   title       Dialog heading text.
	 * @param {string}   content     Dialog body text.
	 * @param {string}   confirmText Confirm button label.
	 * @param {Function} onConfirm   Callback invoked when the reset is confirmed.
	 * @return {void}
	 */
	confirmReset: function( title, content, confirmText, onConfirm ) {
		window.nfStylesShared.confirmReset( title, content, confirmText, window.nfStylesShared.l10n( 'cancel', 'Cancel' ), onConfirm );
	},

	/**
	 * Sync a color picker's value into its control's swatch, hex, and setting.
	 * @since 3.0.30
	 * @param {HTMLElement} input Color input element that changed.
	 * @return {void}
	 */
	updateColorControl: function( input ) {
		var picker = jQuery( input );
		var control = picker.closest( '.nf-styles-admin-control' );
		var value = picker.val();

		control.removeClass( 'has-css-value' );
		control.find( '.nf-styles-css-value' ).remove();
		control.find( '.nf-styles-admin-value' ).val( value );
		control.find( '.nf-styles-visual-swatch-chip' ).css( 'background-color', value ).removeClass( 'is-empty' );
		control.find( '.nf-styles-visual-reset' ).removeClass( 'is-empty' );
		control.find( '.nf-styles-color-hex' ).val( value );
		control.find( '.nf-styles-color-custom span' ).css( 'background-color', value );
		this.syncPaletteSelection( control, value );
	},

	/**
	 * Open or close color swatch popovers, keeping the trigger's state in sync.
	 * @since 3.0.30
	 * @param {jQuery}  swatches Swatch controls to update.
	 * @param {boolean} open     Whether the popover should be open.
	 * @return {void}
	 */
	setSwatchOpen: function( swatches, open ) {
		window.nfStylesShared.setSwatchOpen( swatches, open );
	},

	/**
	 * Build and attach the theme/hex color popover to a swatch control.
	 *
	 * No-ops when the control already has a popover.
	 * @since 3.0.30
	 * @param {jQuery} control Color swatch control element.
	 * @return {void}
	 */
	addColorPopover: function( control ) {
		var selected = control.find( '.nf-styles-admin-value' ).val() || '';

		if ( control.find( '.nf-styles-color-popover' ).length ) {
			return;
		}

		control.find( '.nf-styles-visual-reset' ).after( window.nfStylesShared.renderColorPopover( selected, window.nfStylesShared.getColorPopoverLabels() ) );
	},

	/**
	 * Apply a clicked theme swatch's color to its control.
	 * @since 3.0.30
	 * @param {HTMLElement} button Theme color button that was clicked.
	 * @return {void}
	 */
	applyThemeColor: function( button ) {
		var target = jQuery( button );
		var control = target.closest( '.nf-styles-admin-control' );
		var value = target.data( 'color' );

		this.applyColorValue( control, value );
		this.removeResetClearInput( control.closest( '.nf-styles-admin-section' ), control.find( '.nf-styles-admin-value' ).attr( 'name' ) );
	},

	/**
	 * Apply a normalized hex value from the popover's text input to its control.
	 *
	 * Restores the current color when the entered value is invalid.
	 * @since 3.0.30
	 * @param {HTMLElement} input Hex color text input.
	 * @return {void}
	 */
	applyHexColor: function( input ) {
		var target = jQuery( input );
		var control = target.closest( '.nf-styles-admin-control' );
		var value = this.normalizeHexColor( target.val() );

		if ( ! value ) {
			target.val( control.find( '.nf-styles-admin-color' ).val() );
			return;
		}

		this.applyColorValue( control, value );
		this.removeResetClearInput( control.closest( '.nf-styles-admin-section' ), control.find( '.nf-styles-admin-value' ).attr( 'name' ) );
	},

	/**
	 * Normalize and apply a color value to a swatch control and its inputs.
	 *
	 * No-ops when the value is not a valid hex color.
	 * @since 3.0.30
	 * @param {jQuery} control Color swatch control element.
	 * @param {string} value   Color value to apply.
	 * @return {void}
	 */
	applyColorValue: function( control, value ) {
		value = this.normalizeHexColor( value );

		if ( ! value ) {
			return;
		}

		control.removeClass( 'has-css-value' );
		control.find( '.nf-styles-css-value' ).remove();
		control.find( '.nf-styles-admin-value' ).val( value );
		control.find( '.nf-styles-admin-color' ).val( value ).removeAttr( 'data-css-managed' ).prop( 'disabled', false );
		control.find( '.nf-styles-color-hex' ).val( value );
		control.find( '.nf-styles-color-custom span' ).css( 'background-color', value );
		control.find( '.nf-styles-visual-swatch-chip' ).css( 'background-color', value ).removeClass( 'is-empty' );
		control.find( '.nf-styles-visual-reset' ).removeClass( 'is-empty' );
		this.syncPaletteSelection( control, value );
	},

	/**
	 * Normalize a color string to a lowercase 6-digit hex value.
	 * @since 3.0.30
	 * @param {string} value Raw color string to normalize.
	 * @return {string} Normalized #rrggbb value, or '' when invalid.
	 */
	normalizeHexColor: function( value ) {
		return window.nfStylesShared.normalizeHexColor( value );
	},

	/**
	 * Mark the theme swatch matching a color value as active within a control.
	 * @since 3.0.30
	 * @param {jQuery} control Color swatch control element.
	 * @param {string} value   Color value to match against the palette.
	 * @return {void}
	 */
	syncPaletteSelection: function( control, value ) {
		window.nfStylesShared.syncPaletteSelection( control, value );
	},

		/**
		 * Reset a color swatch control back to its empty/default state.
		 * @since 3.0.30
		 * @param {HTMLElement} button Reset button within the swatch control.
		 * @return {void}
		 */
		resetColorControl: function( button ) {
		var reset = jQuery( button );
		var control = reset.closest( '.nf-styles-admin-control' );

		control.removeClass( 'has-css-value' );
		control.find( '.nf-styles-admin-value' ).val( '' );
		control.find( '.nf-styles-css-value' ).remove();
		control.find( '.nf-styles-admin-color' ).val( '#ffffff' ).removeAttr( 'data-css-managed' ).prop( 'disabled', false );
		control.find( '.nf-styles-visual-swatch-chip' ).css( 'background-color', '#ffffff' ).addClass( 'is-empty' ).prop( 'disabled', false );
		control.find( '.nf-styles-color-hex' ).val( '' );
		control.find( '.nf-styles-color-custom span' ).css( 'background-color', '#ffffff' );
		this.setSwatchOpen( control, false );
		this.syncPaletteSelection( control, '' );
		reset.addClass( 'is-empty' );
		},

		/**
		 * Initialize jBox tooltips for any help triggers within a scope.
		 * @since 3.0.30
		 * @param {jQuery} scope Container to search for help triggers.
		 * @return {void}
		 */
		initTooltips: function( scope ) {
			window.nfStylesShared.initTooltips( scope );
		},

		/**
		 * Reset a range, spacing, or segmented control to its default state.
		 * @since 3.0.30
		 * @param {HTMLElement} button Reset button within the visual control.
		 * @return {void}
		 */
		resetVisualControl: function( button ) {
		var reset = jQuery( button );
		var control = reset.closest( '.nf-styles-admin-control' );
		var defaultValue;

		control.removeClass( 'has-css-value' );
		control.find( '.nf-styles-css-value' ).remove();
		control.find( '.nf-styles-admin-value' ).val( '' );
		control.find( '.nf-styles-visual-value' ).addClass( 'is-default' ).text( window.nfStylesShared.l10n( 'defaultLabel', 'Default' ) );
		reset.addClass( 'is-empty' );

		if ( control.hasClass( 'nf-styles-visual-control-spacing' ) ) {
			defaultValue = control.find( '.nf-styles-admin-spacing-all' ).data( 'default-value' ) || 0;
			control.removeClass( 'is-split' );
			control.find( '.nf-styles-visual-sides-toggle' ).attr( 'aria-expanded', 'false' ).removeAttr( 'data-css-managed' ).prop( 'disabled', false );
			control.find( '.nf-styles-visual-range, .nf-styles-admin-side-range' ).val( defaultValue ).removeAttr( 'data-css-managed' ).prop( 'disabled', false );
			return;
		}

		if ( control.hasClass( 'nf-styles-visual-control-range' ) ) {
			defaultValue = control.find( '.nf-styles-admin-range' ).data( 'default-value' ) || 0;
			control.find( '.nf-styles-admin-range' ).val( defaultValue ).removeAttr( 'data-css-managed' ).prop( 'disabled', false );
			return;
		}

		if ( control.hasClass( 'nf-styles-visual-control-segmented' ) ) {
			control.find( '.nf-styles-visual-segment' ).removeClass( 'is-active' );
			control.find( '.nf-styles-visual-segment' ).removeAttr( 'data-css-managed' ).prop( 'disabled', false );
			control.find( '.nf-styles-visual-segment[data-value=""]' ).addClass( 'is-active' );
		}
	},

		/**
		 * Toggle an admin style section between expanded and collapsed.
		 * @since 3.0.30
		 * @param {HTMLElement} button Section header element that was clicked.
		 * @return {void}
		 */
		toggleSection: function( button ) {
			var toggle = jQuery( button );
			var section = toggle.closest( '.nf-styles-admin-section' );
			var body = section.find( '.nf-styles-section-body' ).first();
			var isExpanded = 'true' === toggle.attr( 'aria-expanded' );

		toggle.attr( 'aria-expanded', isExpanded ? 'false' : 'true' );
		section.toggleClass( 'is-collapsed', isExpanded );
			body.prop( 'hidden', isExpanded );
		},

		/**
		 * Switch an admin style section between design and CSS editing modes.
		 * @since 3.0.30
		 * @param {jQuery} section Admin style section element.
		 * @param {string} mode    Active editing mode ('css' or 'design').
		 * @return {void}
		 */
		setEditingMode: function( section, mode ) {
			var isCss = 'css' === mode;

			section.toggleClass( 'is-css-mode', isCss );
			section.toggleClass( 'is-design-mode', ! isCss );
			this.setPanelInputs( section, mode );
			section.find( '.nf-styles-mode-switch' ).prop( 'checked', isCss );
			section.find( '.nf-styles-mode-label.is-design' ).toggleClass( 'is-active', ! isCss );
			section.find( '.nf-styles-mode-label.is-css' ).toggleClass( 'is-active', isCss );
		},

		/**
		 * Enable only the inputs for the active mode's panel within a section.
		 * @since 3.0.30
		 * @param {jQuery} section Admin style section element.
		 * @param {string} mode    Active editing mode ('css' or 'design').
		 * @return {void}
		 */
		setPanelInputs: function( section, mode ) {
			window.nfStylesShared.setPanelInputs( section, mode );
		},

		/**
		 * Recompute a spacing control's value from its per-side range inputs.
		 * @since 3.0.30
		 * @param {jQuery} control Spacing control element.
		 * @return {void}
		 */
		updateSpacingFromSides: function( control ) {
		this.updateSpacingValue( control, window.nfStylesShared.getSpacingFromSides( control, '.nf-styles-admin-side-range' ) );
	},

	/**
	 * Apply a spacing shorthand value to a control and refresh its label.
	 * @since 3.0.30
	 * @param {jQuery} control Spacing control element.
	 * @param {string} value   Spacing shorthand value to apply.
	 * @return {void}
	 */
	updateSpacingValue: function( control, value ) {
		var range = control.find( '.nf-styles-visual-range' ).first();
		var unit = range.data( 'unit' ) || '';
		var parts = String( value || '' ).trim().split( /\s+/ ).filter( Boolean );
		var numeric = 1 === parts.length ? String( parseFloat( value ) ) : '';

		control.find( '.nf-styles-admin-value' ).val( value );
		if ( numeric ) {
			this.updateEditableValueLabel( control, numeric, unit );
		} else {
			control.find( '.nf-styles-visual-value' ).removeClass( 'is-default nf-styles-visual-value-editable' ).text( value );
		}
		control.find( '.nf-styles-visual-reset' ).toggleClass( 'is-empty', ! value );
	},

	/**
	 * Update (or build) a range control's editable numeric value label.
	 * @since 3.0.30
	 * @param {jQuery}          control The range/spacing control element.
	 * @param {(number|string)} value   Numeric value to display.
	 * @param {string}          unit    Value unit (e.g. px).
	 * @return {void}
	 */
	updateEditableValueLabel: function( control, value, unit ) {
		var range = control.find( '.nf-styles-visual-range' ).first();
		var label = control.find( '.nf-styles-visual-range-header label' ).first().text();
		var valueControl = control.find( '.nf-styles-visual-value' ).first();

		if ( ! valueControl.find( '.nf-styles-visual-value-input' ).length ) {
			valueControl.replaceWith( '<span class="nf-styles-visual-value nf-styles-visual-value-editable"><span class="nf-styles-visual-value-input nf-styles-admin-value-input" contenteditable="true" inputmode="numeric" role="spinbutton" aria-valuemin="' + range.attr( 'min' ) + '" aria-valuemax="' + range.attr( 'max' ) + '" aria-valuenow="' + value + '" data-unit="' + unit + '" aria-label="' + window.nfStylesShared.format( window.nfStylesShared.l10n( 'setValue', 'Set %s' ), label ) + '">' + value + '</span><span class="nf-styles-visual-value-unit">' + unit + '</span></span>' );
			return;
		}

		valueControl.removeClass( 'is-default' );
		valueControl.find( '.nf-styles-visual-value-input' ).text( value ).attr( 'aria-valuenow', value );
		valueControl.find( '.nf-styles-visual-value-unit' ).text( unit );
	},

	/**
	 * Clamp a numeric value to a range input's min/max bounds.
	 * @since 3.0.30
	 * @param {(number|string)} value Raw value to normalize.
	 * @param {jQuery}          range Range input providing min/max bounds.
	 * @return {string} The clamped numeric value as a string.
	 */
	normalizeRangeValue: function( value, range ) {
		return window.nfStylesShared.normalizeRangeValue( value, range );
	},

	/**
	 * Resolve the min/max/step range for a unit from a unit select control.
	 *
	 * Reads the range from the select's option data attributes; the builder's
	 * config-object counterpart is getUnitRangeConfig in stylesVisualEditor.js.
	 *
	 * @since 3.0.30
	 * @param {jQuery} unitControl Unit select element with range data attributes.
	 * @param {string} unit        Unit to resolve the range for.
	 * @return {Object} Range with min, max, and step values.
	 */
	getUnitRangeConfigFromControl: function( unitControl, unit ) {
		var option = unitControl.find( 'option[value="' + unit + '"]' );

		return {
			min: option.data( 'min' ) || unitControl.data( 'min' ) || 0,
			max: option.data( 'max' ) || unitControl.data( 'max' ) || 100,
			step: option.data( 'step' ) || unitControl.data( 'step' ) || 1
		};
	},

	/**
	 * Reset every color, visual, and CSS-mode input within an admin section.
	 * @since 3.0.30
	 * @param {jQuery} section Admin style section element.
	 * @return {void}
	 */
	resetSection: function( section ) {
		var representedNames = [];

		section.find( '.nf-styles-reset-clear-input' ).remove();
		section.find( '.nf-styles-css-active' ).addClass( 'is-empty' );
		section.find( '.nf-styles-admin-value[name]' ).each( function() {
			representedNames.push( jQuery( this ).attr( 'name' ) );
		} );

		section.find( '.nf-styles-visual-swatch .nf-styles-visual-reset' ).each( function() {
			ninjaFormsStyles.resetColorControl( this );
		} );
		section.find( '.nf-styles-visual-control .nf-styles-visual-reset' ).each( function() {
			ninjaFormsStyles.resetVisualControl( this );
		} );

		section.find( '.nf-styles-css-editor :input[name]' ).each( function() {
			var input = jQuery( this );
			var name = input.attr( 'name' );

			if ( -1 !== [ 'button', 'submit' ].indexOf( input.attr( 'type' ) ) ) {
				return;
			}

			input.val( '' );
			if ( -1 === representedNames.indexOf( name ) ) {
				section.append( '<input type="hidden" class="nf-styles-reset-clear-input" name="' + name + '" value="">' );
			}
		} );
	},

	/**
	 * Remove any hidden reset-clear input for a setting name within a section.
	 * @since 3.0.30
	 * @param {jQuery} section Admin style section element.
	 * @param {string} name    Setting name whose clear input should be removed.
	 * @return {void}
	 */
	removeResetClearInput: function( section, name ) {
		if ( ! name ) {
			return;
		}

		section.find( '.nf-styles-reset-clear-input' ).filter( function() {
			return jQuery( this ).attr( 'name' ) === name;
		} ).remove();
	}
};

/**
 * Handle showing/hiding fields
 * 
 * @package Ninja Forms Conditional Logic
 * @copyright (c) 2016 WP Ninjas
 * @since 3.0
 */
define( [], function() {
	var controller = Marionette.Object.extend( {
		initialize: function() {
			nfRadio.channel( 'condition:trigger' ).reply( 'hide_field', this.hideField, this );
			nfRadio.channel( 'condition:trigger' ).reply( 'show_field', this.showField, this );
		},

		hideField: function( conditionModel, then ) {
			var formId = conditionModel.collection.formModel.get( 'id' );
			var targetFieldModel = nfRadio.channel( 'form-' + formId ).request( 'get:fieldByKey', then.key );

			if( 'undefined' == typeof targetFieldModel ) return;
			targetFieldModel.set( 'visible', false );
            if ( ! targetFieldModel.get( 'clean' ) ) {
				targetFieldModel.trigger( 'change:value', targetFieldModel );
			}

			nfRadio.channel( 'fields' ).request( 'remove:error', targetFieldModel.get( 'id' ), 'required-error' );

			// For repeater fields, mark child field models as not visible so JS
			// validation skips their required check on submission.
			if ( 'repeater' === targetFieldModel.get( 'type' ) ) {
				this.setRepeaterChildrenVisible( targetFieldModel, false );
			}
		},

		/**
		 * Set the visible state on all child field models of a Repeater field.
		 *
		 * Child models live inside the Repeater's own sets collection, not in the
		 * top-level form field collection. When visible is false, NF core's
		 * validateModelData skips required validation for those fields.
		 */
		setRepeaterChildrenVisible: function( repeaterModel, visible ) {
			var sets = repeaterModel.get( 'sets' );
			if ( ! sets ) return;

			sets.each( function( set ) {
				var fields = set.get( 'fields' );
				if ( ! fields ) return;

				fields.each( function( fieldModel ) {
					fieldModel.set( 'visible', visible );
					if ( ! visible ) {
						nfRadio.channel( 'fields' ).request( 'remove:error', fieldModel.get( 'id' ), 'required-error' );
					}
				} );
			} );
		},

		showField: function( conditionModel, then ) {
			var targetFieldModel = nfRadio.channel( 'form-' + conditionModel.collection.formModel.get( 'id' ) ).request( 'get:fieldByKey', then.key );
			//TODO: Add an error to let the user know the show/hide field is empty.
			if( 'undefined' == typeof targetFieldModel ) return;
			targetFieldModel.set( 'visible', true );
            if ( ! targetFieldModel.get( 'clean' ) ) {
                targetFieldModel.trigger( 'change:value', targetFieldModel );
			}
			// For repeater fields, restore child field models to visible so
			// required validation applies again when the Repeater is shown.
			if ( 'repeater' === targetFieldModel.get( 'type' ) ) {
				this.setRepeaterChildrenVisible( targetFieldModel, true );
			}
			if ( 'recaptcha' === targetFieldModel.get( 'type' ) ) {
				this.renderRecaptcha();
			}
			if ( 'turnstile' === targetFieldModel.get( 'type' ) ) {
				this.renderTurnstile();
			}
			var viewEl = { el: nfRadio.channel( 'form-' + conditionModel.collection.formModel.get( 'id' ) ).request( 'get:el' ) };
            nfRadio.channel( 'form' ).request( 'init:help', viewEl );
		},

		renderRecaptcha: function() {
			jQuery( '.g-recaptcha' ).each( function() {
                var callback = jQuery( this ).data( 'callback' );
                var fieldID = jQuery( this ).data( 'fieldid' );
                if ( typeof window[ callback ] !== 'function' ){
                    window[ callback ] = function( response ) {
                        nfRadio.channel( 'recaptcha' ).request( 'update:response', response, fieldID );
                    };
                }
				var opts = {
					theme: jQuery( this ).data( 'theme' ),
					sitekey: jQuery( this ).data( 'sitekey' ),
					callback: callback
				};
				
				grecaptcha.render( jQuery( this )[0], opts );
			} );
		},

		renderTurnstile: function() {
			// Ensure turnstile API is loaded
			if ( typeof turnstile === 'undefined' ) {
				return;
			}

			jQuery( '.cf-turnstile, .nf-cf-turnstile' ).each( function() {
				var element = this;
				var sitekey = jQuery( element ).data( 'sitekey' );
				var fieldID = jQuery( element ).data( 'fieldid' );
				
				// Skip if no sitekey or already rendered
				if ( ! sitekey || jQuery( element ).children().length > 0 ) {
					return;
				}

				try {
					// Create callback function for this specific field
					var callbackName = 'nfTurnstileCallback_' + fieldID;
					
					// Define the callback function
					window[ callbackName ] = function( token ) {
						// Update the hidden input field
						var input = document.getElementById( 'nf-field-' + fieldID );
						if ( input ) {
							input.value = token;
							jQuery( input ).trigger( 'change' );
							
							// Remove error states
							jQuery( input ).closest( '.field-wrap' ).removeClass( 'nf-error' );
							jQuery( input ).closest( '.field-wrap' ).find( '.nf-error-msg' ).remove();
						}

						// Update via radio channel if available
						if ( typeof nfRadio !== 'undefined' && nfRadio.channel ) {
							try {
								nfRadio.channel( 'turnstile' ).request( 'update:response', token, fieldID );
							} catch( e ) {
								// Silent fail
							}
						}
					};

					// Render the turnstile widget
					turnstile.render( element, {
						sitekey: sitekey,
						theme: jQuery( element ).data( 'theme' ) || 'auto',
						size: jQuery( element ).data( 'size' ) || 'normal',
						callback: callbackName
					} );
					
				} catch( e ) {
					// Silent fail for turnstile render errors
				}
			} );
		}
	});

	return controller;
} );
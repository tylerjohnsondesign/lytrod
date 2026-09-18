/**
 * Handle adding or removing an option from our list
 * 
 * @package Ninja Forms Conditional Logic
 * @copyright (c) 2016 WP Ninjas
 * @since 3.0
 */
define( [], function() {
	var controller = Marionette.Object.extend( {
		initialize: function() {
			nfRadio.channel( 'condition:trigger' ).reply( 'show_option', this.showOption, this );

			nfRadio.channel( 'condition:trigger' ).reply( 'hide_option', this.hideOption, this );
		},

		showOption: function( conditionModel, then ) {
			var option = this.getOption( conditionModel, then );
			option.visible = true;
			this.restoreFieldValue( conditionModel, then, option );
			this.updateFieldModel( conditionModel, then );
		},

		hideOption: function( conditionModel, then ) {
			var option = this.getOption( conditionModel, then );
			option.visible = false;
			this.applyFallbackValue( conditionModel, then, option );
			this.updateFieldModel( conditionModel, then );
		},

		getFieldModel: function( conditionModel, then ) {
			return nfRadio.channel( 'form-' + conditionModel.collection.formModel.get( 'id' ) ).request( 'get:fieldByKey', then.key );
		},

		getOption: function( conditionModel, then ) {
			var targetFieldModel = this.getFieldModel( conditionModel, then );
			var options = targetFieldModel.get( 'options' );
			return _.find( options, function( option ) { return option.value == then.value } );
		},

		/**
		 * If the option being hidden is the field's current value, fall back to the
		 * first remaining visible option (or blank). The original value is remembered
		 * so showOption can restore it automatically - unless the user picks a
		 * different option manually in the meantime (see bindManualOverrideListener).
		 */
		applyFallbackValue: function( conditionModel, then, hiddenOption ) {
			var targetFieldModel = this.getFieldModel( conditionModel, then );
			var currentValue = targetFieldModel.get( 'value' );

			if ( hiddenOption.visible || currentValue != hiddenOption.value ) return;

			// Only remember the original value the first time we fall back, so a
			// cascade of hides (multiple options hidden at once) still reverts to
			// the value the user actually had selected before any of this happened.
			if ( ! targetFieldModel.get( 'clAutoFallback' ) ) {
				targetFieldModel.set( 'clAutoFallbackValue', currentValue );
				targetFieldModel.set( 'clAutoFallback', true );
				this.bindManualOverrideListener( targetFieldModel );
			}

			var options = targetFieldModel.get( 'options' );
			var firstVisibleOption = _.find( options, function( option ) {
				return option.visible !== false;
			} );

			this.setFieldValue( targetFieldModel, firstVisibleOption ? firstVisibleOption.value : '' );
		},

		/**
		 * If the option becoming visible is the one we auto-fell-back from, restore
		 * it - but only while that fallback is still active (i.e. the user hasn't
		 * manually selected something else since it was hidden).
		 */
		restoreFieldValue: function( conditionModel, then, shownOption ) {
			var targetFieldModel = this.getFieldModel( conditionModel, then );

			if ( ! targetFieldModel.get( 'clAutoFallback' ) ) return;
			if ( targetFieldModel.get( 'clAutoFallbackValue' ) != shownOption.value ) return;

			this.setFieldValue( targetFieldModel, shownOption.value );
			targetFieldModel.unset( 'clAutoFallback' );
			targetFieldModel.unset( 'clAutoFallbackValue' );
		},

		/**
		 * Sets the field's value and notifies merge tags / dependent fields, mirroring
		 * the change:value handling other conditional logic actions (e.g. showHide) use.
		 */
		setFieldValue: function( targetFieldModel, value ) {
			targetFieldModel.set( 'value', value );
			if ( ! targetFieldModel.get( 'clean' ) ) {
				targetFieldModel.trigger( 'change:value', targetFieldModel );
			}
		},

		/**
		 * A real user interaction (change/blur on the field itself) means any value
		 * we auto-assigned as a fallback is no longer "ours" to revert - it's now the
		 * user's deliberate choice, so clear our tracking and leave it alone.
		 */
		bindManualOverrideListener: function( targetFieldModel ) {
			if ( targetFieldModel.clManualListenerBound ) return;
			targetFieldModel.clManualListenerBound = true;

			this.listenTo( nfRadio.channel( 'field-' + targetFieldModel.get( 'id' ) ), 'change:field', function() {
				targetFieldModel.unset( 'clAutoFallback' );
				targetFieldModel.unset( 'clAutoFallbackValue' );
			} );
		},

		updateFieldModel: function( conditionModel, then ) {
			var targetFieldModel = this.getFieldModel( conditionModel, then );
			var options = targetFieldModel.get( 'options' );
			targetFieldModel.set( 'options', options );
			targetFieldModel.trigger( 'reRender' );
		}
	});

	return controller;
} );
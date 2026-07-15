/**
 * Condition Sorting Controller
 *
 * Handles the reordering and management of condition order
 *
 * @package Ninja Forms Conditional Logic
 * @copyright (c) 2024 Saturday Drive
 * @since 3.0
 */
define( [], function() {
	var controller = Marionette.Object.extend({
		initialize: function() {
			/*
			 * Listen for condition reordering events
			 */
			this.listenTo( nfRadio.channel( 'conditions' ), 'reorder:conditions', this.handleReorder );

			/*
			 * Listen for new conditions being added to set proper order
			 */
			this.listenTo( nfRadio.channel( 'conditions' ), 'init:model', this.setInitialOrder );
		},

		handleReorder: function( collection ) {
			/*
			 * When conditions are reordered via drag-and-drop,
			 * ensure the collection is sorted by the new order
			 */
			collection.comparator = function( model ) {
				// Safety check - ensure model exists and has the get method
				if ( ! model || 'function' !== typeof model.get ) {
					return 0;
				}
				return model.get( 'order' ) || 0;
			};
			collection.sort();

			/*
			 * Mark form as dirty and trigger database save
			 */
			nfRadio.channel( 'app' ).request( 'update:setting', 'clean', false );
			nfRadio.channel( 'app' ).request( 'update:db' );
		},

		setInitialOrder: function( model ) {
			/*
			 * When a new condition is created, set its order to be last
			 */
			if ( ! model.get( 'order' ) && 0 !== model.get( 'order' ) ) {
				var collection = model.collection;
				var maxOrder = 0;

				if ( collection && collection.length > 0 ) {
					collection.each( function( conditionModel ) {
						var order = conditionModel.get( 'order' ) || 0;
						if ( order > maxOrder ) {
							maxOrder = order;
						}
					});
				}

				model.set( 'order', maxOrder + 1 );
			}
		}
	});

	return controller;
} );
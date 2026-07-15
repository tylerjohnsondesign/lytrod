/**
 * Collection view for our conditions
 *
 * @package Ninja Forms Conditional Logic
 * @copyright (c) 2016 WP Ninjas
 * @since 3.0
 */
define( [ 'views/advanced/conditionItem' ], function( conditionItem ) {
	var view = Marionette.CollectionView.extend({
		childView: conditionItem,

		initialize: function( options ) {
			this.collection = options.dataModel.get( 'conditions' );
		},

        onShow: function() {
            /*
             * If we don't have any conditions, add an empty one as we render.
             */
            if ( 0 == this.collection.length ) {
                this.collection.add( {} );
            }

            /*
             * Initialize jQuery UI sortable for drag-and-drop reordering
             * Allow free-floating movement anywhere with controlled dropping
             */
            var that = this;
            this.$el.sortable({
                handle: '.nf-drag-condition',
                placeholder: 'nf-condition-placeholder',
                containment: false,
                tolerance: 'pointer',
                opacity: 0.5,
                update: function( event, ui ) {
                    that.handleSort( event, ui );
                }
            });
        },

        handleSort: function( event, ui ) {
            /*
             * Update the order of models when drag-and-drop completes
             */
            var that = this;
            var hasValidModels = false;

            this.$el.children().each( function( index ) {
                var cid = jQuery( this ).attr( 'data-cid' );
                if ( cid ) {
                    var model = that.collection.get( cid );
                    if ( model && 'function' === typeof model.set ) {
                        model.set( 'order', index );
                        hasValidModels = true;
                    }
                }
            });

            /*
             * Only trigger reorder event if we actually have valid models
             */
            if ( hasValidModels ) {
                nfRadio.channel( 'conditions' ).trigger( 'reorder:conditions', this.collection );
            }
        },

        onBeforeDestroy: function() {
            /*
             * If we don't have any conditions or we have more than one, just return.
             */
            if ( 0 == this.collection.length || 1 < this.collection.length ) return;
            /*
             * If we only have one condition, and we didn't change the "key" attribute, reset our collection.
             * This empties it.
             */
            if ( '' == this.collection.models[0].get( 'when' ).models[0].get( 'key' ) ) {
                this.collection.reset();
            }
        }
	});

	return view;
} );

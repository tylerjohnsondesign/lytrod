/**
 * Layout view for our conditions
 *
 * @package Ninja Forms Conditional Logic
 * @copyright (c) 2016 WP Ninjas
 * @since 3.0
 */
define( [ 'views/advanced/whenCollection', 'views/advanced/thenCollection', 'views/advanced/elseCollection' ], function( WhenCollectionView, ThenCollectionView, ElseCollectionView ) {
	var view = Marionette.LayoutView.extend({
		template: "#tmpl-nf-cl-advanced-condition",

		attributes: function() {
			return {
				'data-cid': this.model.cid
			};
		},

		regions: {
			'when': '.nf-when-region',
			'then': '.nf-then-region',
			'else': '.nf-else-region'
		},

		initialize: function() {
			/*
			 * When we change the "collapsed" attribute of our model, re-render.
			 */
			this.listenTo( this.model, 'change:collapsed', this.render );

			/*
			 * When our drawer closes, send out a radio message on our setting type channel.
			 */
			this.listenTo( nfRadio.channel( 'drawer' ), 'closed', this.drawerClosed );
		},

		onRender: function() {
			var firstWhenDiv = jQuery( this.el ).find( '.nf-first-when' );
			this.when.show( new WhenCollectionView( { collection: this.model.get( 'when' ), firstWhenDiv: firstWhenDiv, conditionModel: this.model } ) );
			if ( ! this.model.get( 'collapsed' ) ) {
				this.then.show( new ThenCollectionView( { collection: this.model.get( 'then' ) } ) );
				this.else.show( new ElseCollectionView( { collection: this.model.get( 'else' ) } ) );
			}
		},

		events: {
			'click .nf-remove-condition': 'clickRemove',
			'click .nf-collapse-condition': 'clickCollapse',
			'click .nf-add-when': 'clickAddWhen',
			'click .nf-add-then': 'clickAddThen',
			'click .nf-add-else': 'clickAddElse',
			'change .nf-condition-label-field': 'changeSetting',
			'keyup .nf-condition-label-field': 'updateLabelOnEnter'
		},

		clickRemove: function( e ) {
			var that = this;
			var deleteModal = new NinjaModal({
				width: 400,
				closeOnClick: false,
				closeOnEsc: true,
				title: nfcli18n.deleteConditionTitle,
				content: nfcli18n.deleteConditionConfirm,
				btnPrimary: {
					text: nfcli18n.deleteConditionBtn,
					callback: function() {
						deleteModal.toggleModal( false );
						deleteModal.destroy();
						nfRadio.channel( 'conditions' ).trigger( 'click:removeCondition', e, that.model );
					}
				},
				btnSecondary: {
					text: nfcli18n.cancelBtn,
					callback: function() {
						deleteModal.toggleModal( false );
						deleteModal.destroy();
					}
				}
			});
		},

		clickCollapse: function( e ) {
			nfRadio.channel( 'conditions' ).trigger( 'click:collapseCondition', e, this.model );
		},

		clickAddWhen: function( e ) {
			nfRadio.channel( 'conditions' ).trigger( 'click:addWhen', e, this.model );
		},

		clickAddThen: function( e ) {
			nfRadio.channel( 'conditions' ).trigger( 'click:addThen', e, this.model );
		},

		clickAddElse: function( e ) {
			nfRadio.channel( 'conditions' ).trigger( 'click:addElse', e, this.model );
		},

		changeSetting: function( e ) {
			/*
			 * Handle label changes directly since condition models don't fit
			 * the standard updateSettings flow (which expects sub-models with
			 * collection.options.conditionModel set).
			 */
			var value = jQuery( e.target ).val();
			var id = jQuery( e.target ).data( 'id' );
			this.model.set( id, value );

			// Mark form as dirty and trigger save
			nfRadio.channel( 'app' ).request( 'update:setting', 'clean', false );
			nfRadio.channel( 'app' ).request( 'update:db' );
		},

		updateLabelOnEnter: function( e ) {
			/*
			 * Blur on Enter key to trigger the change event
			 */
			if ( 13 === e.keyCode ) {
				jQuery( e.target ).blur();
			}
		}
	});

	return view;
} );
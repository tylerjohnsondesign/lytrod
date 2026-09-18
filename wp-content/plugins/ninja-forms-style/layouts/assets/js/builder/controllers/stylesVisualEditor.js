/**
 * Visual Styles Editor controller.
 *
 * Mounts the simplified, config-driven visual style controls (color, range,
 * spacing, and segmented inputs) over the raw Layout & Styles sub-settings in
 * the form builder, keeps a live builder preview in sync as values change, and
 * resolves setting names back to their style group for preview and reset. The
 * raw sub-settings stay available behind the per-section "CSS mode" toggle.
 *
 * @since 3.0.30
 */
define( ['stylesUnits', 'stylesControls', 'stylesPreview'], function( StylesUnits, StylesControls, StylesPreview ) {
	var controller = Marionette.Object.extend( {
		fieldTypes: [
			'textbox',
			'email',
			'phone',
			'firstname',
			'lastname',
			'address',
			'address2',
			'city',
			'zip',
			'number',
			'password',
			'passwordconfirm',
			'textarea',
			'listselect',
			'listmultiselect',
			'listcheckbox',
			'listradio',
			'checkbox',
			'date',
			'submit',
			'html',
			'hr',
			'starrating',
			'terms',
			'listimage',
			'button',
			'confirm',
			'listcountry',
			'liststate',
			'note',
			'product',
			'quantity',
			'repeater',
			'signature',
			'spam'
		],

		previewChannels: [
			'fields-textbox',
			'fields-email',
			'fields-tel',
			'fields-firstname',
			'fields-lastname',
			'fields-address',
			'fields-address2',
			'fields-city',
			'fields-zip',
			'fields-number',
			'fields-input',
			'fields-textarea',
			'fields-listselect',
			'fields-listcheckbox',
			'fields-listradio',
			'fields-checkbox',
			'fields-date',
			'fields-submit',
			'fields-html',
			'fields-hr',
			'fields-starrating',
			'fields-listimage',
			'fields-button',
			'fields-confirm',
			'fields-listcountry',
			'fields-liststate',
			'fields-note',
			'fields-product',
			'fields-quantity',
			'fields-repeater',
			'fields-signature',
			'fields-spam'
		],

		// Control config is passed from PHP (styles/includes/Config/VisualControls.php)
		// via wp_add_inline_script in layouts/ninja-forms-layouts.php. This ensures
		// unit ranges, editability rules, and labels stay in sync across PHP and JS.
		// The controlGroups below organize these controls into the visual UI.
		settings: window.nfStylesVisualControls || {},

		controlGroups: [
			{
				label: window.nfStylesShared.l10n('groupColor', 'Color' ),
				contentClass: 'nf-styles-visual-swatch-grid',
				controls: [ 'background-color', 'color' ],
				skipForGroups: {
					wrap_styles: [ 'color' ]
				}
			},
			{
				label: window.nfStylesShared.l10n('groupType', 'Type' ),
				controls: [ 'font-size' ],
				skipForGroups: {
					wrap_styles: [ 'font-size' ]
				}
			},
			{
				label: window.nfStylesShared.l10n('groupSpacing', 'Spacing' ),
				contentClass: 'nf-styles-visual-two-col',
				controls: [ 'padding', 'margin' ]
			},
			{
				label: window.nfStylesShared.l10n('groupLayout', 'Layout' ),
				contentClass: 'nf-styles-visual-two-col',
				controls: [ 'width', 'height' ]
			},
			{
				label: window.nfStylesShared.l10n('groupBorder', 'Border' ),
				controls: [ 'border-style' ],
				nested: [
					{
						contentClass: 'nf-styles-visual-two-col',
						controls: [ 'border', 'border-color' ]
					}
				]
			}
		],

		formStyleGroups: [
			'container_styles',
			'title_styles',
			'row_styles',
			'row-odd_styles',
			'success-msg_styles',
			'error_msg_styles',
			'breadcrumb_container_styles',
			'breadcrumb_buttons_styles',
			'breadcrumb_button_hover_styles',
			'breadcrumb_active_button_styles',
			'progress_bar_container_styles',
			'progress_bar_fill_styles',
			'part_titles_styles',
			'navigation_container_styles',
			'previous_button_styles',
			'next_button_styles',
			'navigation_hover_styles'
		],

		fieldStyleGroups: [
			'wrap_styles',
			'element_styles',
			'label_styles',
			'submit_element_hover_styles',
			'list_item_row_styles',
			'list_item_label_styles',
			'list_item_element_styles',
			'rating-item',
			'rating-item-hover',
			'rating-item-selected'
		],

		previewStyleCache: {},
		previewPassClaims: null,
		hoverControls: [ 'background-color', 'color', 'border-color' ],

		/**
		 * Wire up setting-render listeners, preview refresh triggers, and the
		 * delegated panel mode/reset DOM handlers when the controller starts.
		 * @since 3.0.30
		 * @return {void}
		 */
		initialize: function() {
			StylesControls.init( this.settings );
			StylesPreview.init( this.settings, this.formStyleGroups, this.fieldStyleGroups );
			this.listenTo( nfRadio.channel( 'app' ), 'after:appStart', this.addModeClass );
			this.listenTo( nfRadio.channel( 'app' ), 'after:appStart', this.refreshBuilderPreviewStyles );
			this.listenTo( nfRadio.channel( 'app' ), 'render:fieldsSortable', this.refreshBuilderPreviewStyles );
			this.listenTo( nfRadio.channel( 'app' ), 'click:edit', this.refreshBuilderPreviewStyles );
			this.listenTo( nfRadio.channel( 'app' ), 'click:closeDrawer', this.refreshBuilderPreviewStyles );
			this.listenTo( nfRadio.channel( 'drawer' ), 'before:close', this.refreshBuilderPreviewStyles );
			this.listenTo( nfRadio.channel( 'setting-wrap_styles' ), 'render:setting', this.renderWrapStyles );
				this.listenTo( nfRadio.channel( 'setting-element_styles' ), 'render:setting', this.renderElementStyles );
				this.listenTo( nfRadio.channel( 'setting-label_styles' ), 'render:setting', this.renderLabelStyles );
				this.listenTo( nfRadio.channel( 'setting-submit_element_hover_styles' ), 'render:setting', this.renderSubmitElementHoverStyles );
				this.listenTo( nfRadio.channel( 'setting-list_item_row_styles' ), 'render:setting', this.renderListItemRowStyles );
				this.listenTo( nfRadio.channel( 'setting-list_item_label_styles' ), 'render:setting', this.renderListItemLabelStyles );
				this.listenTo( nfRadio.channel( 'setting-list_item_element_styles' ), 'render:setting', this.renderListItemElementStyles );
			this.listenTo( nfRadio.channel( 'setting-rating-item' ), 'render:setting', this.renderRatingItemStyles );
			this.listenTo( nfRadio.channel( 'setting-rating-item-hover' ), 'render:setting', this.renderRatingItemHoverStyles );
			this.listenTo( nfRadio.channel( 'setting-rating-item-selected' ), 'render:setting', this.renderRatingItemSelectedStyles );
			_.each( this.formStyleGroups, function( groupName ) {
				this.listenTo( nfRadio.channel( 'setting-' + groupName ), 'render:setting', this.renderFormStyleGroup );
			}, this );
			_.each( this.previewChannels, function( channel ) {
				this.listenTo( nfRadio.channel( channel ), 'render:itemView', this.applyStylesToItemView );
			}, this );
			jQuery( document )
				.off( 'input.nfStylesPanel change.nfStylesPanel click.nfStylesPanel', '.nf-styles-panel-controls .nf-styles-mode-switch' )
				.on( 'input.nfStylesPanel change.nfStylesPanel click.nfStylesPanel', '.nf-styles-panel-controls .nf-styles-mode-switch', this.handlePanelModeSwitch.bind( this ) )
				.off( 'click.nfStylesPanel', '.nf-styles-panel-controls .nf-styles-reset-all' )
				.on( 'click.nfStylesPanel', '.nf-styles-panel-controls .nf-styles-reset-all', this.handlePanelResetAll.bind( this ) )
				.off( 'click.nfStylesSection', '.nf-styles-visual-legend-toggle' )
				.on( 'click.nfStylesSection', '.nf-styles-visual-legend-toggle', this.handleSectionToggle.bind( this ) )
				.off( 'click.nfStylesDocLink', '.nf-styles-doc-link' )
				.on( 'click.nfStylesDocLink', '.nf-styles-doc-link', function( e ) {
					e.stopPropagation();
				} )
				.off( 'click.nfStylesColorPopover' )
				.on( 'click.nfStylesColorPopover', function( e ) {
					if ( ! jQuery( e.target ).closest( '.nf-styles-visual-swatch' ).length ) {
						this.setSwatchOpen( jQuery( '.nf-styles-visual-swatch' ), false );
					}
				}.bind( this ) );
		},

		/**
		 * Flag the builder element so the visual editor replaces raw settings.
		 *
		 * Skipped in dev mode, where the raw style sub-settings stay visible.
		 * @since 3.0.30
		 * @return {void}
		 */
		addModeClass: function() {
			if ( nfAdmin.devMode ) {
				return;
			}

			jQuery( nfRadio.channel( 'app' ).request( 'get:builderEl' ) ).addClass( 'nf-layout-styles-non-dev' );
		},

		/**
		 * Mount the visual editor over a supported field's element styles setting.
		 *
		 * @since 3.0.30
		 * @param {Backbone.Model} settingModel Style setting group model.
		 * @param {Backbone.Model} dataModel    Field data model being edited.
		 * @param {Backbone.View}  settingView  Rendered setting view to augment.
		 * @return {void}
		 */
		renderElementStyles: function( settingModel, dataModel, settingView ) {
			this.renderSupportedGroup( settingModel, dataModel, settingView, 'element_styles' );
		},

		/**
		 * Mount the visual editor over a supported field's wrapper styles setting.
		 *
		 * @since 3.0.30
		 * @param {Backbone.Model} settingModel Style setting group model.
		 * @param {Backbone.Model} dataModel    Field data model being edited.
		 * @param {Backbone.View}  settingView  Rendered setting view to augment.
		 * @return {void}
		 */
		renderWrapStyles: function( settingModel, dataModel, settingView ) {
			this.renderSupportedGroup( settingModel, dataModel, settingView, 'wrap_styles' );
		},

			/**
			 * Mount the visual editor over a field's label styles setting.
			 *
			 * Hidden for button-like fields, whose visible text is the button element.
			 *
			 * @since 3.0.30
			 * @param {Backbone.Model} settingModel Style setting group model.
			 * @param {Backbone.Model} dataModel    Field data model being edited.
			 * @param {Backbone.View}  settingView  Rendered setting view to augment.
			 * @return {void}
			 */
			renderLabelStyles: function( settingModel, dataModel, settingView ) {
				if ( ! this.supportsField( dataModel ) ) {
					return;
				}

				if ( this.isButtonLikeField( dataModel ) ) {
					jQuery( settingView.el ).hide();
					return;
				}

				setTimeout( function() {
					this.mountEditor( settingModel, dataModel, settingView, 'label_styles' );
				}.bind( this ), 0 );
			},

			/**
			 * Mount the visual editor over the submit/button element hover styles setting.
			 *
			 * Only rendered for button-like fields; hover controls are limited to color.
			 *
			 * @since 3.0.30
			 * @param {Backbone.Model} settingModel Style setting group model.
			 * @param {Backbone.Model} dataModel    Field data model being edited.
			 * @param {Backbone.View}  settingView  Rendered setting view to augment.
			 * @return {void}
			 */
			renderSubmitElementHoverStyles: function( settingModel, dataModel, settingView ) {
				if ( ! this.supportsField( dataModel ) || ! this.isButtonLikeField( dataModel ) ) {
					return;
				}

				setTimeout( function() {
					this.mountEditor( settingModel, dataModel, settingView, 'submit_element_hover_styles' );
				}.bind( this ), 0 );
			},

		/**
		 * Mount the visual editor over a list field's item row styles setting.
		 *
		 * @since 3.0.30
		 * @param {Backbone.Model} settingModel Style setting group model.
		 * @param {Backbone.Model} dataModel    Field data model being edited.
		 * @param {Backbone.View}  settingView  Rendered setting view to augment.
		 * @return {void}
		 */
		renderListItemRowStyles: function( settingModel, dataModel, settingView ) {
			this.renderSupportedGroup( settingModel, dataModel, settingView, 'list_item_row_styles' );
		},

			/**
			 * Mount the visual editor over a list field's item label styles setting.
			 *
			 * @since 3.0.30
			 * @param {Backbone.Model} settingModel Style setting group model.
			 * @param {Backbone.Model} dataModel    Field data model being edited.
			 * @param {Backbone.View}  settingView  Rendered setting view to augment.
			 * @return {void}
			 */
			renderListItemLabelStyles: function( settingModel, dataModel, settingView ) {
				this.renderSupportedGroup( settingModel, dataModel, settingView, 'list_item_label_styles' );
			},

			/**
			 * Mount the visual editor over a list field's item element styles setting.
			 *
			 * @since 3.0.30
			 * @param {Backbone.Model} settingModel Style setting group model.
			 * @param {Backbone.Model} dataModel    Field data model being edited.
			 * @param {Backbone.View}  settingView  Rendered setting view to augment.
			 * @return {void}
			 */
			renderListItemElementStyles: function( settingModel, dataModel, settingView ) {
				this.renderSupportedGroup( settingModel, dataModel, settingView, 'list_item_element_styles' );
			},

		/**
		 * Mount the visual editor over a rating field's item styles setting.
		 *
		 * @since 3.0.30
		 * @param {Backbone.Model} settingModel Style setting group model.
		 * @param {Backbone.Model} dataModel    Field data model being edited.
		 * @param {Backbone.View}  settingView  Rendered setting view to augment.
		 * @return {void}
		 */
		renderRatingItemStyles: function( settingModel, dataModel, settingView ) {
			this.renderSupportedGroup( settingModel, dataModel, settingView, 'rating-item' );
		},

		/**
		 * Mount the visual editor over a rating field's item hover styles setting.
		 *
		 * @since 3.0.30
		 * @param {Backbone.Model} settingModel Style setting group model.
		 * @param {Backbone.Model} dataModel    Field data model being edited.
		 * @param {Backbone.View}  settingView  Rendered setting view to augment.
		 * @return {void}
		 */
		renderRatingItemHoverStyles: function( settingModel, dataModel, settingView ) {
			this.renderSupportedGroup( settingModel, dataModel, settingView, 'rating-item-hover' );
		},

		/**
		 * Mount the visual editor over a rating field's selected item styles setting.
		 *
		 * @since 3.0.30
		 * @param {Backbone.Model} settingModel Style setting group model.
		 * @param {Backbone.Model} dataModel    Field data model being edited.
		 * @param {Backbone.View}  settingView  Rendered setting view to augment.
		 * @return {void}
		 */
		renderRatingItemSelectedStyles: function( settingModel, dataModel, settingView ) {
			this.renderSupportedGroup( settingModel, dataModel, settingView, 'rating-item-selected' );
		},

		/**
		 * Mount the visual editor over a form-level style group setting.
		 *
		 * @since 3.0.30
		 * @param {Backbone.Model} settingModel Style setting group model.
		 * @param {Backbone.Model} dataModel    Form data model being edited.
		 * @param {Backbone.View}  settingView  Rendered setting view to augment.
		 * @return {void}
		 */
		renderFormStyleGroup: function( settingModel, dataModel, settingView ) {
			if ( ! this.supportsFormStyleGroup( settingModel ) ) {
				return;
			}

			setTimeout( function() {
				this.mountEditor( settingModel, dataModel, settingView, settingModel.get( 'name' ) );
			}.bind( this ), 0 );
		},

		/**
		 * Mount the visual editor for a named group when the field is supported.
		 *
		 * @since 3.0.30
		 * @param {Backbone.Model} settingModel Style setting group model.
		 * @param {Backbone.Model} dataModel    Field data model being edited.
		 * @param {Backbone.View}  settingView  Rendered setting view to augment.
		 * @param {string}         groupName    Style group name to mount.
		 * @return {void}
		 */
		renderSupportedGroup: function( settingModel, dataModel, settingView, groupName ) {
			if ( ! this.supportsField( dataModel ) ) {
				return;
			}

			setTimeout( function() {
				this.mountEditor( settingModel, dataModel, settingView, groupName );
			}.bind( this ), 0 );
		},

			/**
			 * Determine whether a field type supports the visual style editor.
			 * @since 3.0.30
			 * @param {Backbone.Model} dataModel Field data model to test.
			 * @return {boolean} Whether the field's type is styleable.
			 */
			supportsField: function( dataModel ) {
				if ( ! dataModel || 'function' !== typeof dataModel.get ) {
					return false;
				}

				return -1 !== this.fieldTypes.indexOf( dataModel.get( 'type' ) );
			},

			/**
			 * Determine whether a field renders as a button (button or submit).
			 * @since 3.0.30
			 * @param {Backbone.Model} dataModel Field data model to test.
			 * @return {boolean} Whether the field is button-like.
			 */
			isButtonLikeField: function( dataModel ) {
				return dataModel && 'function' === typeof dataModel.get && -1 !== [ 'button', 'submit' ].indexOf( dataModel.get( 'type' ) );
			},

			/**
			 * Determine whether a setting is a supported form-level style group.
			 * @since 3.0.30
			 * @param {Backbone.Model} settingModel Style setting group model to test.
			 * @return {boolean} Whether the group is a known form style group.
			 */
			supportsFormStyleGroup: function( settingModel ) {
				return settingModel && 'function' === typeof settingModel.get && -1 !== this.formStyleGroups.indexOf( settingModel.get( 'name' ) );
			},

			/**
			 * Resolve a human label for a style group, falling back to its name.
			 * @since 3.0.30
			 * @param {Backbone.Model} settingModel Style setting group model.
			 * @return {string} Group label, name, or 'styles' when unavailable.
			 */
			getStyleGroupLabel: function( settingModel ) {
				if ( ! settingModel || 'function' !== typeof settingModel.get ) {
					return window.nfStylesShared.l10n('styles', 'styles' );
				}

				return settingModel.get( 'label' ) || settingModel.get( 'name' ) || window.nfStylesShared.l10n('styles', 'styles' );
			},

		/**
		 * Build and inject the visual editor panel into a setting's fieldset.
		 *
		 * Renders the configured control groups, wires the raw sub-settings as the
		 * CSS-mode panel, adds the drawer mode/reset controls, and applies the
		 * current values as a live builder preview. No-ops when the fieldset is
		 * missing, already mounted, or produces no controls.
		 *
		 * @since 3.0.30
		 * @param {Backbone.Model} settingModel Style setting group model.
		 * @param {Backbone.Model} dataModel    Field or form data model being edited.
		 * @param {Backbone.View}  settingView  Rendered setting view to augment.
		 * @param {string}         groupName    Style group name (e.g. element_styles).
		 * @return {void}
		 */
		mountEditor: function( settingModel, dataModel, settingView, groupName ) {
			var el = jQuery( settingView.el );
			var fieldset = el.find( 'fieldset' ).first();
			var rawSettings = fieldset.find( '.nf-field-sub-settings' );
			var panel;
			var drawer;

			if ( ! fieldset.length || fieldset.find( '.nf-styles-visual-editor' ).length ) {
				return;
			}

			panel = this.renderPanel( settingModel, dataModel, groupName );
			if ( ! panel.children().length ) {
				return;
			}

			drawer = fieldset.closest( '#nf-drawer' );
			rawSettings.addClass( 'nf-styles-css-editor' ).attr( 'data-style-mode-panel', 'css' );
			fieldset.addClass( 'nf-styles-visual-fieldset is-design-mode' ).attr( {
				'data-style-group-name': groupName,
				'data-style-group-label': this.getStyleGroupLabel( settingModel )
			} );
			fieldset.data( 'style-setting-model', settingModel );
			fieldset.data( 'style-data-model', dataModel );
			this.insertGroupReset( fieldset, settingModel );
			fieldset.append( panel );
			this.makeSectionCollapsible( fieldset, panel, groupName );
			this.ensureDrawerControls( drawer.length ? drawer : fieldset.parent(), this.hasCssOnlyValues( settingModel, dataModel ) );
			this.setPanelInputs( fieldset, 'design' );
			this.bindPanel( fieldset, settingModel, dataModel );
			this.applyStyles( dataModel, groupName );
		},

		/**
		 * Turn a mounted style fieldset into a keyboard-operable collapsible section.
		 *
		 * The three per-field style sections hold near-identical control sets, so
		 * they collapse by default and open one at a time. The legend becomes a
		 * real button (the drawer's own legends carry only a click binding, which
		 * no keyboard user can reach) following the standalone Styling page's
		 * accordion pattern.
		 *
		 * Collapsing is a class on the fieldset rather than a wrapper around the
		 * panels: the drawer's own views and this controller both walk the
		 * fieldset's children, and re-parenting the panels breaks the field's
		 * live preview.
		 *
		 * @since 3.0.30
		 * @param {jQuery} fieldset  Mounted visual style fieldset.
		 * @param {jQuery} panel     Rendered design panel the toggle controls.
		 * @param {string} groupName Style group name (e.g. element_styles).
		 * @return {void}
		 */
		makeSectionCollapsible: function( fieldset, panel, groupName ) {
			var legend = fieldset.children( 'legend' ).first();
			var panelId = 'nf-styles-section-' + groupName + '-' + Math.random().toString( 36 ).slice( 2 );
			var reset;
			var toggle;

			if ( ! legend.length || legend.find( '.nf-styles-visual-legend-toggle' ).length ) {
				return;
			}

			panel.attr( 'id', panelId );
			toggle = jQuery( '<button type="button" class="nf-styles-visual-legend-toggle" aria-expanded="false" aria-controls="' + panelId + '"><span class="nf-styles-visual-legend-text"></span><span class="nf-styles-visual-legend-icon" aria-hidden="true"></span></button>' );
			toggle.find( '.nf-styles-visual-legend-text' ).text( legend.text() );

			// The reset moves into the legend so it shares the header row. A
			// fieldset's containing block for absolute children starts below the
			// legend, so positioning it against the header is not an option.
			reset = fieldset.children( '.nf-styles-reset-group' ).detach();

			legend.empty().append( toggle ).append( reset );
			fieldset.addClass( 'is-collapsed' );
		},

		/**
		 * Expand or collapse a style section from its legend toggle.
		 * @since 3.0.30
		 * @param {Object} e Click event from a section legend toggle.
		 * @return {void}
		 */
		handleSectionToggle: function( e ) {
			var toggle = jQuery( e.currentTarget );
			var fieldset = toggle.closest( '.nf-styles-visual-fieldset' );
			var expanded = fieldset.hasClass( 'is-collapsed' );

			e.preventDefault();
			e.stopPropagation();
			fieldset.toggleClass( 'is-collapsed', ! expanded );
			toggle.attr( 'aria-expanded', expanded ? 'true' : 'false' );
		},

		/**
		 * Insert the per-section reset button into a mounted style fieldset.
		 * @since 3.0.30
		 * @param {jQuery}         fieldset     Mounted visual style fieldset.
		 * @param {Backbone.Model} settingModel Style setting group model.
		 * @return {void}
		 */
		insertGroupReset: function( fieldset, settingModel ) {
			var legend = fieldset.children( 'legend' ).first();
			var label = this.getStyleGroupLabel( settingModel );
			var reset = jQuery( '<button type="button" class="nf-styles-reset-group" title="' + _.escape( window.nfStylesShared.l10n('resetSectionHint', 'Reset styles in this section' ) ) + '"><span aria-hidden="true">×</span></button>' );

			if ( legend.length ) {
				legend.after( reset.attr( 'aria-label', window.nfStylesShared.format(window.nfStylesShared.l10n('resetLabel', 'Reset %s' ), label ) ) );
				return;
			}

			fieldset.prepend( reset.attr( 'aria-label', window.nfStylesShared.format(window.nfStylesShared.l10n('resetLabel', 'Reset %s' ), window.nfStylesShared.l10n('styles', 'styles' ) ) ) );
		},

		/**
		 * Ensure the drawer's shared CSS-mode/reset controls exist and reflect state.
		 * @since 3.0.30
		 * @param {jQuery}  drawer           Drawer (or fieldset parent) to host the controls.
		 * @param {boolean} hasCssOnlyValues Whether any group holds CSS-only values.
		 * @return {void}
		 */
		ensureDrawerControls: function( drawer, hasCssOnlyValues ) {
			var controls;

			if ( ! drawer || ! drawer.length ) {
				return;
			}

			controls = drawer.find( '.nf-styles-panel-controls' ).first();
			if ( controls.length ) {
				if ( hasCssOnlyValues ) {
					controls.find( '.nf-styles-css-active' ).removeClass( 'is-empty' );
				}
				return;
			}

			controls = this.renderPanelControls( hasCssOnlyValues );
			drawer.find( '.nf-styles-visual-fieldset' ).first().before( controls );
			this.initStyleTooltips( controls );
		},

		/**
		 * Build the drawer panel controls (CSS-mode toggle and reset-all button).
		 * @since 3.0.30
		 * @param {boolean} hasCssOnlyValues Whether to show the "CSS values active" badge.
		 * @return {jQuery} The rendered panel controls element.
		 */
		renderPanelControls: function( hasCssOnlyValues ) {
			var id = 'nf-styles-mode-' + Math.random().toString( 36 ).slice( 2 );
			var controls = jQuery( '<div class="nf-styles-panel-controls" />' );
			var control = jQuery( '<div class="nf-styles-mode-control" />' );
			var row = jQuery( '<div class="nf-styles-mode-toggle" />' );

			control.append( '<span class="nf-setting-label nf-styles-mode-label">' + _.escape( window.nfStylesShared.l10n('cssMode', 'CSS mode' ) ) + ' ' + this.renderHelpTooltip( window.nfStylesShared.l10n('cssModeHelp', 'CSS mode shows the original advanced style fields for users who want direct CSS-style control.' ), window.nfStylesShared.l10n('aboutCssMode', 'About CSS mode' ) ) + '</span>' );
			row.append( '<input type="checkbox" id="' + id + '" class="nf-toggle nf-styles-mode-switch" data-style-mode-switch aria-label="' + _.escape( window.nfStylesShared.l10n('cssMode', 'CSS mode' ) ) + '" />' );
			row.append( '<label class="nf-styles-mode-switch-label" for="' + id + '">' + _.escape( window.nfStylesShared.l10n('cssMode', 'CSS mode' ) ) + '</label>' );
			row.append( '<span class="nf-styles-css-active' + ( hasCssOnlyValues ? '' : ' is-empty' ) + '">' + _.escape( window.nfStylesShared.l10n('cssValuesActive', 'CSS values active' ) ) + '</span>' );
			control.append( row );
			controls.append( control );
			controls.append( '<button type="button" class="nf-styles-reset-all" title="' + _.escape( window.nfStylesShared.l10n('resetAllHint', 'Reset every style section in this panel' ) ) + '">' + _.escape( window.nfStylesShared.l10n('resetStyles', 'Reset styles' ) ) + '</button>' );

			return controls;
		},

		/**
		 * Toggle the drawer between design and CSS editing modes from the switch.
		 * @since 3.0.30
		 * @param {Object} e Change/click event from the mode switch input.
		 * @return {void}
		 */
		handlePanelModeSwitch: function( e ) {
			this.setDrawerEditingMode( this.getActiveStylesScope( jQuery( e.currentTarget ).closest( '#nf-drawer' ) ), e.currentTarget.checked ? 'css' : 'design' );
		},

		/**
		 * Confirm and reset every mounted style section in the active panel.
		 * @since 3.0.30
		 * @param {Object} e Click event from the reset-all button.
		 * @return {void}
		 */
		handlePanelResetAll: function( e ) {
			var scope = this.getActiveStylesScope( jQuery( e.currentTarget ).closest( '#nf-drawer' ) );

			e.preventDefault();
			this.confirmReset( window.nfStylesShared.l10n('resetAllTitle', 'Reset all styles?' ), window.nfStylesShared.l10n('resetAllBody', 'This will clear all custom styles in this Styles panel, including Design and CSS mode values. This cannot be undone after you save.' ), window.nfStylesShared.l10n('resetAllConfirm', 'Reset All Styles' ), function() {
				this.getMountedStyleFieldsets( scope ).each( function( index, fieldset ) {
					this.resetStyleFieldset( jQuery( fieldset ) );
				}.bind( this ) );
				this.updateCssActiveBadge( scope );
			}.bind( this ) );
		},

		/**
		 * Build the markup for an inline help tooltip trigger and its text.
		 * @since 3.0.30
		 * @param {string} helpText Help text shown in the tooltip.
		 * @param {string} label    Short accessible name for the trigger.
		 * @return {string} HTML string for the help tooltip.
		 */
		renderHelpTooltip: function( helpText, label ) {
			return '<span class="nf-help-wrap"><button type="button" class="nf-help nf-styles-help" aria-label="' + _.escape( label ) + '"><span class="dashicons dashicons-admin-comments" aria-hidden="true"></span></button><div class="nf-help-text"><div>' + helpText + '</div></div></span>';
		},

		/**
		 * Initialize jBox tooltips for any help triggers within a scope.
		 * @since 3.0.30
		 * @param {jQuery} scope Container to search for help triggers.
		 * @return {void}
		 */
		initStyleTooltips: function( scope ) {
			window.nfStylesShared.initTooltips( scope );
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
		 * Resolve the active styles drawer, falling back to the open drawer.
		 * @since 3.0.30
		 * @param {jQuery} fallback Element to derive the drawer scope from.
		 * @return {jQuery} The resolved drawer scope element.
		 */
		getActiveStylesScope: function( fallback ) {
			var scope = fallback && fallback.length ? fallback.closest( '#nf-drawer' ) : jQuery();

			if ( scope.length ) {
				return scope;
			}

			scope = jQuery( '#nf-drawer' );

			return scope.length ? scope : fallback;
		},

		/**
		 * Collect the mounted visual style fieldsets within a scope.
		 * @since 3.0.30
		 * @param {jQuery} scope Container to search for mounted fieldsets.
		 * @return {jQuery} Matched style fieldsets, or all mounted fieldsets.
		 */
		getMountedStyleFieldsets: function( scope ) {
			var fieldsets = scope && scope.length ? scope.find( '.nf-styles-visual-fieldset' ) : jQuery();
			var mounted = jQuery( '.nf-styles-visual-fieldset' );

			return fieldsets.length ? fieldsets : mounted;
		},

		/**
		 * Determine whether a setting group holds values the visual UI cannot edit.
		 *
		 * Returns true when any saved value is non-empty and not representable by
		 * the visual controls (used to flag "CSS values active").
		 *
		 * @since 3.0.30
		 * @param {Backbone.Model} settingModel Style setting group model.
		 * @param {Backbone.Model} dataModel    Data model holding saved values.
		 * @return {boolean} Whether CSS-only values are present.
		 */
		hasCssOnlyValues: function( settingModel, dataModel ) {
			var settings;
			var hasValues = false;

			if ( ! settingModel || 'function' !== typeof settingModel.get || ! dataModel || 'function' !== typeof dataModel.get ) {
				return false;
			}

			settings = settingModel.get( 'settings' );
			if ( ! settings || 'function' !== typeof settings.each ) {
				return false;
			}

			settings.each( function( childSetting ) {
				var settingName = childSetting.get( 'name' );
				var property = settingName.replace( settingModel.get( 'name' ) + '_', '' );
				var value = dataModel.get( settingName );

				if ( this.settings[ property ] ) {
					if ( value && ! this.isEditableVisualValue( property, value ) ) {
						hasValues = true;
					}
					return;
				}

				if ( 'show_advanced_css' === property ) {
					return;
				}

				if ( value ) {
					hasValues = true;
				}
			}, this );

			return hasValues;
		},

		/**
		 * Build the visual editor panel from the configured control groups.
		 * @since 3.0.30
		 * @param {Backbone.Model} settingModel Style setting group model.
		 * @param {Backbone.Model} dataModel    Data model holding saved values.
		 * @param {string}         groupName    Style group name being mounted.
		 * @return {jQuery} The rendered panel element.
		 */
		renderPanel: function( settingModel, dataModel, groupName ) {
			var panel = jQuery( '<div class="nf-styles-visual-editor" data-style-mode-panel="design" />' );

			_.each( this.controlGroups, function( groupConfig ) {
				this.appendConfiguredGroup( panel, groupConfig, settingModel, dataModel, groupName );
			}, this );

			return panel;
		},

		/**
		 * Render one configured control group (and its nested rows) into the panel.
		 *
		 * No-ops when the group produces no renderable controls.
		 * @since 3.0.30
		 * @param {jQuery}         panel        Panel element to append the group to.
		 * @param {Object}         groupConfig  Control group configuration.
		 * @param {Backbone.Model} settingModel Style setting group model.
		 * @param {Backbone.Model} dataModel    Data model holding saved values.
		 * @param {string}         groupName    Style group name being mounted.
		 * @return {void}
		 */
		appendConfiguredGroup: function( panel, groupConfig, settingModel, dataModel, groupName ) {
			var group = this.renderGroup( groupConfig.label, groupConfig.contentClass );
			var content = group.find( '.nf-styles-visual-group-content' );

			this.appendConfiguredControls( content, groupConfig.controls, groupConfig, settingModel, dataModel, groupName );

			_.each( groupConfig.nested || [], function( nestedConfig ) {
				var nestedContent = jQuery( '<div class="' + nestedConfig.contentClass + '" />' );
				this.appendConfiguredControls( nestedContent, nestedConfig.controls, nestedConfig, settingModel, dataModel, groupName );
				if ( nestedContent.children().length ) {
					content.append( nestedContent );
				}
			}, this );

			if ( ! content.children().length ) {
				return;
			}

			panel.append( group );
		},

		/**
		 * Render each configured control in a list into a container.
		 * @since 3.0.30
		 * @param {jQuery}         container    Element to append the controls to.
		 * @param {string[]}       controls     Style property keys to render.
		 * @param {Object}         groupConfig  Owning control group configuration.
		 * @param {Backbone.Model} settingModel Style setting group model.
		 * @param {Backbone.Model} dataModel    Data model holding saved values.
		 * @param {string}         groupName    Style group name being mounted.
		 * @return {void}
		 */
		appendConfiguredControls: function( container, controls, groupConfig, settingModel, dataModel, groupName ) {
			_.each( controls || [], function( property ) {
				this.appendConfiguredControl( container, groupConfig, settingModel, dataModel, groupName, property );
			}, this );
		},

		/**
		 * Render a single style control into a container when it applies.
		 *
		 * Skipped when the property has no renderer, is filtered out for the
		 * group, or the setting group has no matching child setting.
		 * @since 3.0.30
		 * @param {jQuery}         container    Element to append the control to.
		 * @param {Object}         groupConfig  Owning control group configuration.
		 * @param {Backbone.Model} settingModel Style setting group model.
		 * @param {Backbone.Model} dataModel    Data model holding saved values.
		 * @param {string}         groupName    Style group name being mounted.
		 * @param {string}         property     Style property key to render.
		 * @return {void}
		 */
		appendConfiguredControl: function( container, groupConfig, settingModel, dataModel, groupName, property ) {
			var renderer = this.getControlRenderer( property );

			if ( ! renderer || this.shouldSkipControl( groupConfig, groupName, property ) || ! this.hasSetting( settingModel, property ) ) {
				return;
			}

			container.append( renderer.call( this, property, settingModel, dataModel, groupName ) );
		},

		/**
		 * Determine whether a control should be skipped for a style group.
		 * @since 3.0.30
		 * @param {Object} groupConfig Owning control group configuration.
		 * @param {string} groupName   Style group name being mounted.
		 * @param {string} property    Style property key to test.
		 * @return {boolean} Whether the control should be omitted.
		 */
		shouldSkipControl: function( groupConfig, groupName, property ) {
			if ( 'submit_element_hover_styles' === groupName && -1 === this.hoverControls.indexOf( property ) ) {
				return true;
			}

			return !! (
				groupConfig.skipForGroups &&
				groupConfig.skipForGroups[ groupName ] &&
				-1 !== groupConfig.skipForGroups[ groupName ].indexOf( property )
			);
		},

		/**
		 * Resolve the renderer function for a style property's control type.
		 * @since 3.0.30
		 * @param {string} property Style property key to render.
		 * @return {(Function|null)} The control renderer, or null when unsupported.
		 */
		getControlRenderer: function( property ) {
			var config = this.settings[ property ];

			if ( ! config ) {
				return null;
			}

			if ( 'color' === config.type ) {
				return this.renderColorControl;
			}

			if ( 'segmented' === config.type ) {
				return this.renderSegmentedControl;
			}

			if ( -1 !== [ 'padding', 'margin' ].indexOf( property ) ) {
				return this.renderSpacingControl;
			}

			if ( 'range' === config.type ) {
				return this.renderRangeControl;
			}

			return null;
		},

			/**
			 * Determine whether a setting group contains a given style property.
			 * @since 3.0.30
			 * @param {Backbone.Model} settingModel Style setting group model.
			 * @param {string}         property     Style property key to look up.
			 * @return {boolean} Whether the child setting exists.
			 */
			hasSetting: function( settingModel, property ) {
				var settings;

				if ( ! settingModel || 'function' !== typeof settingModel.get ) {
					return false;
				}

				settings = settingModel.get( 'settings' );
				if ( ! settings || 'function' !== typeof settings.findWhere ) {
					return false;
				}

				return !! settings.findWhere( { name: this.getSettingName( settingModel, property ) } );
			},

		/**
		 * Build an empty titled control group section.
		 * @since 3.0.30
		 * @param {string} label        Group title text.
		 * @param {string} contentClass Extra class for the group content wrapper.
		 * @return {jQuery} The rendered group section.
		 */
		renderGroup: function( label, contentClass ) {
			return jQuery( '<section class="nf-styles-visual-group"><div class="nf-styles-visual-group-title">' + label + '</div><div class="nf-styles-visual-group-content ' + ( contentClass || '' ) + '"></div></section>' );
		},

		/**
		 * Build the color picker popover (theme swatches and hex input).
		 * @since 3.0.30
		 * @param {string} value Current color value to preselect.
		 * @return {jQuery} The rendered color popover element.
		 */
		renderColorPopover: function( value ) {
			return StylesControls.renderColorPopover( value );
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
			return StylesControls.renderColorControl( property, settingModel, dataModel, groupName );
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
			return StylesControls.renderRangeControl( property, settingModel, dataModel, groupName );
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
			return StylesControls.renderSpacingControl( property, settingModel, dataModel, groupName );
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
			return StylesControls.renderValueControl( label, numeric, unit, settingName, config );
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
			return StylesControls.renderUnitControl( unit, config, disabled );
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
			return StylesControls.renderSegmentedControl( property, settingModel, dataModel, groupName );
		},

		/**
		 * Bind the panel's interaction handlers (color, range, spacing, reset).
		 *
		 * Delegates input/click/keydown events on the fieldset to persist values,
		 * drive the live preview, and manage per-control display state.
		 * @since 3.0.30
		 * @param {jQuery}         fieldset     Mounted visual style fieldset.
		 * @param {Backbone.Model} settingModel Style setting group model.
		 * @param {Backbone.Model} dataModel    Data model being edited.
		 * @return {void}
		 */
		bindPanel: function( fieldset, settingModel, dataModel ) {
			fieldset.on( 'click', '.nf-styles-reset-group', function( e ) {
				var label = fieldset.attr( 'data-style-group-label' ) || window.nfStylesShared.l10n('styles', 'styles' );
				e.preventDefault();
				this.confirmReset( window.nfStylesShared.format(window.nfStylesShared.l10n('resetGroupTitle', 'Reset %s?' ), label ), window.nfStylesShared.format(window.nfStylesShared.l10n('resetGroupBody', 'This will clear Design and CSS mode values for %s. This cannot be undone after you save.' ), label ), window.nfStylesShared.l10n('resetGroupConfirm', 'Reset Styles' ), function() {
					this.resetStyleFieldset( fieldset );
					this.updateCssActiveBadge( fieldset.closest( '#nf-drawer' ) );
				}.bind( this ) );
			}.bind( this ) );

			fieldset.on( 'input change', '.nf-styles-visual-color', function( e ) {
				var target = jQuery( e.target );
				this.applyColorValue( target.closest( '.nf-styles-visual-swatch' ), target.val(), settingModel, dataModel, e );
			}.bind( this ) );

			fieldset.on( 'click', '.nf-styles-visual-swatch', function( e ) {
				var target = jQuery( e.target );
				var control = target.closest( '.nf-styles-visual-swatch' );

				if ( target.closest( '.nf-styles-visual-reset, .nf-styles-color-popover, .nf-styles-visual-color' ).length ) {
					return;
				}

				e.preventDefault();
				this.setSwatchOpen( fieldset.find( '.nf-styles-visual-swatch' ).not( control ), false );
				this.setSwatchOpen( control, ! control.hasClass( 'is-open' ) );
			}.bind( this ) );

			fieldset.on( 'click', '.nf-styles-theme-color', function( e ) {
				var target = jQuery( e.currentTarget );
				var control = target.closest( '.nf-styles-visual-swatch' );
				var value = target.data( 'color' );
				e.preventDefault();
				e.stopPropagation();
				this.applyColorValue( control, value, settingModel, dataModel, e );
			}.bind( this ) );

			fieldset.on( 'click', '.nf-styles-color-custom', function( e ) {
				e.preventDefault();
				e.stopPropagation();
				jQuery( e.currentTarget ).closest( '.nf-styles-visual-swatch' ).find( '.nf-styles-visual-color' ).trigger( 'click' );
			} );

			fieldset.on( 'keydown', '.nf-styles-color-hex', function( e ) {
				if ( 'Enter' === e.key ) {
					e.preventDefault();
					jQuery( e.target ).blur();
				}
			} );

			fieldset.on( 'blur change', '.nf-styles-color-hex', function( e ) {
				var input = jQuery( e.target );
				var value = this.normalizeHexColor( input.val() );
				if ( ! value ) {
					input.val( input.closest( '.nf-styles-visual-swatch' ).find( '.nf-styles-visual-color' ).val() );
					return;
				}
				this.applyColorValue( input.closest( '.nf-styles-visual-swatch' ), value, settingModel, dataModel, e );
			}.bind( this ) );

			fieldset.on( 'input change', '.nf-styles-visual-range', function( e ) {
				var target = jQuery( e.target );
				var value = target.val();
				var unit = target.data( 'unit' ) || '';

				if ( target.hasClass( 'nf-styles-visual-spacing-all' ) ) {
					this.updateSpacingFromAllControl( target, settingModel, dataModel, e );
					return;
				}

				this.updateSetting( target.data( 'setting-name' ), value + unit, settingModel, dataModel, e );
				this.updateEditableValueLabel( target.closest( '.nf-styles-visual-control' ), value, unit, target.data( 'setting-name' ) );
				target.closest( '.nf-styles-visual-control' ).find( '.nf-styles-visual-reset' ).removeClass( 'is-empty' );
			}.bind( this ) );

			fieldset.on( 'change', '.nf-styles-visual-unit', function( e ) {
				var unitControl = jQuery( e.target );
				var control = unitControl.closest( '.nf-styles-visual-control' );
				var unit = unitControl.val();
				var settingName = control.find( '.nf-styles-visual-range' ).first().data( 'setting-name' );
				var property = settingName ? settingName.replace( fieldset.attr( 'data-style-group-name' ) + '_', '' ) : '';
				var config = this.settings[ property ];
				var rangeConfig;

				if ( ! config ) {
					return;
				}

				rangeConfig = this.getUnitRangeConfig( config, unit );
				control.find( '.nf-styles-visual-range, .nf-styles-visual-side-range' )
					.attr( {
						min: rangeConfig.min,
						max: rangeConfig.max,
						step: rangeConfig.step
					} )
					.data( 'unit', unit )
					.attr( 'data-unit', unit );
				control.find( '.nf-styles-visual-value-input' ).data( 'unit', unit ).attr( 'data-unit', unit );

				if ( control.hasClass( 'nf-styles-visual-control-spacing' ) ) {
					if ( control.hasClass( 'is-split' ) ) {
						this.updateSpacingFromSideControls( control, settingModel, dataModel, e );
					} else {
						this.updateSpacingFromAllControl( control.find( '.nf-styles-visual-spacing-all' ), settingModel, dataModel, e );
					}
					return;
				}

				this.updateSetting( settingName, control.find( '.nf-styles-visual-range' ).first().val() + unit, settingModel, dataModel, e );
				this.updateEditableValueLabel( control, control.find( '.nf-styles-visual-range' ).first().val(), unit, settingName );
				control.find( '.nf-styles-visual-reset' ).removeClass( 'is-empty' );
			}.bind( this ) );

			fieldset.on( 'keydown', '.nf-styles-visual-value-input', function( e ) {
				if ( 'Enter' === e.key ) {
					e.preventDefault();
					jQuery( e.target ).blur();
				}
			} );

			fieldset.on( 'blur', '.nf-styles-visual-value-input', function( e ) {
				var input = jQuery( e.target );
				var control = input.closest( '.nf-styles-visual-control' );
				var range = control.find( '.nf-styles-visual-range' ).first();
				var value = this.normalizeRangeValue( input.text(), range );

				input.text( value ).attr( 'aria-valuenow', value );
				range.val( value );

				if ( range.hasClass( 'nf-styles-visual-spacing-all' ) ) {
					this.updateSpacingFromAllControl( range, settingModel, dataModel, e );
					return;
				}

				this.updateSetting( input.data( 'setting-name' ), value + ( input.data( 'unit' ) || '' ), settingModel, dataModel, e );
				this.updateEditableValueLabel( control, value, input.data( 'unit' ) || '', input.data( 'setting-name' ) );
				control.find( '.nf-styles-visual-reset' ).removeClass( 'is-empty' );
			}.bind( this ) );

			fieldset.on( 'input change', '.nf-styles-visual-side-range', function( e ) {
				this.updateSpacingFromSideControls( jQuery( e.target ).closest( '.nf-styles-visual-control-spacing' ), settingModel, dataModel, e );
			}.bind( this ) );

			fieldset.on( 'click', '.nf-styles-visual-sides-toggle', function( e ) {
				var control = jQuery( e.target ).closest( '.nf-styles-visual-control-spacing' );
				var expanded = ! control.hasClass( 'is-split' );

				control.toggleClass( 'is-split', expanded );
				jQuery( e.target ).attr( 'aria-expanded', expanded ? 'true' : 'false' );

				if ( expanded ) {
					this.syncSideControlsFromAllControl( control );
					this.updateSpacingFromSideControls( control, settingModel, dataModel, e );
				} else {
					this.updateSpacingFromAllControl( control.find( '.nf-styles-visual-spacing-all' ), settingModel, dataModel, e );
				}
			}.bind( this ) );

			fieldset.on( 'click', '.nf-styles-visual-segment', function( e ) {
				var target = jQuery( e.currentTarget );
				this.updateSetting( target.data( 'setting-name' ), target.data( 'value' ), settingModel, dataModel, e );
				target.siblings().removeClass( 'is-active' );
				target.addClass( 'is-active' );
				target.closest( '.nf-styles-visual-control' ).find( '.nf-styles-visual-reset' ).toggleClass( 'is-empty', ! target.data( 'value' ) );
			}.bind( this ) );

			fieldset.on( 'click', '.nf-styles-visual-reset', function( e ) {
				var target = jQuery( e.target );
				var control = target.closest( '.nf-styles-visual-control, .nf-styles-visual-swatch' );
				e.preventDefault();
				e.stopPropagation();
				this.updateSetting( target.data( 'setting-name' ), '', settingModel, dataModel, e );
				target.addClass( 'is-empty' );
				this.resetVisualControlDisplay( control );
			}.bind( this ) );
		},

		/**
		 * Normalize and apply a color value to a swatch control and its setting.
		 *
		 * No-ops when the value is not a valid hex color.
		 * @since 3.0.30
		 * @param {jQuery}         control      Color swatch control element.
		 * @param {string}         value        Color value to apply.
		 * @param {Backbone.Model} settingModel Style setting group model.
		 * @param {Backbone.Model} dataModel    Data model to update.
		 * @param {Object}         event        Originating DOM/synthetic event.
		 * @return {void}
		 */
		applyColorValue: function( control, value, settingModel, dataModel, event ) {
			var input = control.find( '.nf-styles-visual-color' );

			value = this.normalizeHexColor( value );
			if ( ! value ) {
				return;
			}

			this.updateSetting( input.data( 'setting-name' ), value, settingModel, dataModel, event );
			input.val( value );
			control.removeClass( 'has-css-value' );
			control.find( '.nf-styles-css-value' ).remove();
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
		 * Mark the theme swatch matching a value as active within a control.
		 * @since 3.0.30
		 * @param {jQuery} control Color swatch control element.
		 * @param {string} value   Color value to match against the palette.
		 * @return {void}
		 */
		syncPaletteSelection: function( control, value ) {
			window.nfStylesShared.syncPaletteSelection( control, value );
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
			window.nfStylesShared.confirmReset( title, content, confirmText, window.nfStylesShared.l10n('cancel', 'Cancel' ), onConfirm );
		},

		/**
		 * Clear every design and CSS value in a mounted style fieldset.
		 * @since 3.0.30
		 * @param {jQuery} fieldset Mounted visual style fieldset to reset.
		 * @return {void}
		 */
		resetStyleFieldset: function( fieldset ) {
			var settingModel = fieldset.data( 'style-setting-model' );
			var dataModel = fieldset.data( 'style-data-model' );
			var groupName = fieldset.attr( 'data-style-group-name' );
			var settings;

			fieldset.find( '.nf-styles-css-editor :input' ).not( '[type="button"], [type="submit"]' ).val( '' );
			fieldset.find( '.nf-styles-visual-swatch, .nf-styles-visual-control' ).each( function( index, control ) {
				this.resetVisualControlDisplay( jQuery( control ) );
			}.bind( this ) );

			if ( ! settingModel || ! dataModel || 'function' !== typeof settingModel.get ) {
				return;
			}

			settings = settingModel.get( 'settings' );
			if ( settings && 'function' === typeof settings.each ) {
				settings.each( function( childSetting ) {
					var settingName = childSetting.get( 'name' );
					if ( /_show_advanced_css$/.test( settingName ) ) {
						return;
					}
					this.updateSetting( settingName, '', settingModel, dataModel, { type: 'reset' } );
					this.syncRawSettingInput( fieldset, settingName, '' );
				}, this );
			}

			if ( groupName ) {
				_.each( this.settings, function( config, property ) {
					var settingName = groupName + '_' + property;

					if ( ! this.hasSetting( settingModel, property ) ) {
						return;
					}

					this.syncRawSettingInput( fieldset, settingName, '' );
					if ( dataModel && 'function' === typeof dataModel.set ) {
						dataModel.set( settingName, '' );
					}
					this.applyLivePreview( settingName, '', dataModel );
				}, this );
			}

			this.refreshVisualFieldset( fieldset );
			this.syncResetFieldsetRawInputs( fieldset );
			setTimeout( function() {
				this.syncResetFieldsetRawInputs( fieldset );
			}.bind( this ), 0 );

			if ( groupName ) {
				this.applyStyles( dataModel, groupName );
			}
		},

		/**
		 * Clear the raw CSS-mode inputs for every visual property in a fieldset.
		 * @since 3.0.30
		 * @param {jQuery} fieldset Mounted visual style fieldset to sync.
		 * @return {void}
		 */
		syncResetFieldsetRawInputs: function( fieldset ) {
			var groupName = fieldset.attr( 'data-style-group-name' );

			if ( ! groupName ) {
				return;
			}

			_.each( this.settings, function( config, property ) {
				this.syncRawSettingInput( fieldset, groupName + '_' + property, '' );
			}, this );
		},

		/**
		 * Re-render a fieldset's visual panel from current values, preserving mode.
		 * @since 3.0.30
		 * @param {jQuery} fieldset Mounted visual style fieldset to refresh.
		 * @return {void}
		 */
		refreshVisualFieldset: function( fieldset ) {
			var settingModel = fieldset.data( 'style-setting-model' );
			var dataModel = fieldset.data( 'style-data-model' );
			var groupName = fieldset.attr( 'data-style-group-name' );
			var mode = fieldset.hasClass( 'is-css-mode' ) ? 'css' : 'design';
			var existing;
			var panel;

			if ( ! settingModel || ! dataModel || ! groupName ) {
				return;
			}

			panel = this.renderPanel( settingModel, dataModel, groupName );
			existing = fieldset.children( '.nf-styles-visual-editor' ).first();

			// The section toggle's aria-controls points at the panel, so a
			// replacement panel has to keep the same id.
			if ( existing.attr( 'id' ) ) {
				panel.attr( 'id', existing.attr( 'id' ) );
			}

			existing.remove();
			fieldset.append( panel );
			this.setPanelInputs( fieldset, mode );
		},

		/**
		 * Reset a single visual control's display to its default (empty) state.
		 * @since 3.0.30
		 * @param {jQuery} control Swatch or range/spacing/segmented control element.
		 * @return {void}
		 */
		resetVisualControlDisplay: function( control ) {
			var defaultValue;

			if ( control.hasClass( 'nf-styles-visual-swatch' ) ) {
				control.removeClass( 'has-css-value' );
				control.find( '.nf-styles-visual-swatch-chip' ).css( 'background-color', '#ffffff' ).addClass( 'is-empty' ).prop( 'disabled', false );
				control.find( '.nf-styles-css-value' ).remove();
				control.find( '.nf-styles-visual-color' ).val( '#ffffff' ).removeAttr( 'data-css-managed' ).prop( 'disabled', false );
				control.find( '.nf-styles-color-hex' ).val( '' );
				control.find( '.nf-styles-color-custom span' ).css( 'background-color', '#ffffff' );
				this.setSwatchOpen( control, false );
				this.syncPaletteSelection( control, '' );
				return;
			}

			control.removeClass( 'has-css-value' );
			control.find( '.nf-styles-visual-value' ).replaceWith( '<span class="nf-styles-visual-value is-default">' + _.escape( window.nfStylesShared.l10n('defaultLabel', 'Default' ) ) + '</span>' );
			control.find( '.nf-styles-visual-reset' ).addClass( 'is-empty' );

			if ( control.hasClass( 'nf-styles-visual-control-spacing' ) ) {
				defaultValue = control.find( '.nf-styles-visual-spacing-all' ).data( 'default-value' ) || 0;
				control.removeClass( 'is-split' );
				control.find( '.nf-styles-visual-sides-toggle' ).attr( 'aria-expanded', 'false' ).removeAttr( 'data-css-managed' ).prop( 'disabled', false );
				control.find( '.nf-styles-visual-range, .nf-styles-visual-side-range' ).val( defaultValue ).removeAttr( 'data-css-managed' ).prop( 'disabled', false );
				return;
			}

			if ( control.hasClass( 'nf-styles-visual-control-range' ) ) {
				defaultValue = control.find( '.nf-styles-visual-range' ).data( 'default-value' );
				control.find( '.nf-styles-visual-range' ).val( defaultValue ).removeAttr( 'data-css-managed' ).prop( 'disabled', false );
				return;
			}

			if ( control.hasClass( 'nf-styles-visual-control-segmented' ) ) {
				control.find( '.nf-styles-css-value' ).remove();
				control.find( '.nf-styles-visual-segment' ).removeClass( 'is-active' );
				control.find( '.nf-styles-visual-segment' ).removeAttr( 'data-css-managed' ).prop( 'disabled', false );
				control.find( '.nf-styles-visual-segment[data-value=""]' ).addClass( 'is-active' );
			}
		},

		/**
		 * Switch every mounted fieldset in a drawer to design or CSS mode.
		 * @since 3.0.30
		 * @param {jQuery} drawer Drawer scope containing the fieldsets.
		 * @param {string} mode   Editing mode ('css' or 'design').
		 * @return {void}
		 */
		setDrawerEditingMode: function( drawer, mode ) {
			var isCss = 'css' === mode;

			drawer.toggleClass( 'nf-styles-css-mode-active', isCss );
			drawer.toggleClass( 'nf-styles-design-mode-active', ! isCss );
			drawer.find( '.nf-styles-panel-controls .nf-styles-mode-switch' ).prop( 'checked', isCss );
			drawer.find( '.nf-styles-panel-controls .nf-styles-mode-label.is-design' ).toggleClass( 'is-active', ! isCss );
			drawer.find( '.nf-styles-panel-controls .nf-styles-mode-label.is-css' ).toggleClass( 'is-active', isCss );
			this.getMountedStyleFieldsets( drawer ).each( function( index, fieldset ) {
				this.setEditingMode( jQuery( fieldset ), mode );
			}.bind( this ) );
		},

		/**
		 * Toggle a single fieldset between design and CSS editing mode.
		 * @since 3.0.30
		 * @param {jQuery} fieldset Mounted visual style fieldset.
		 * @param {string} mode     Editing mode ('css' or 'design').
		 * @return {void}
		 */
		setEditingMode: function( fieldset, mode ) {
			var isCss = 'css' === mode;

			fieldset.toggleClass( 'is-css-mode', isCss );
			fieldset.toggleClass( 'is-design-mode', ! isCss );
			this.setPanelInputs( fieldset, mode );
		},

		/**
		 * Update the drawer's "CSS values active" badge from current values.
		 * @since 3.0.30
		 * @param {jQuery} drawer Drawer scope to evaluate and update.
		 * @return {void}
		 */
		updateCssActiveBadge: function( drawer ) {
			var hasCssValues = false;

			this.getMountedStyleFieldsets( drawer ).each( function( index, fieldsetEl ) {
				var fieldset = jQuery( fieldsetEl );
				var settingModel = fieldset.data( 'style-setting-model' );
				var dataModel = fieldset.data( 'style-data-model' );
				if ( this.hasCssOnlyValues( settingModel, dataModel ) ) {
					hasCssValues = true;
				}
			}.bind( this ) );

			drawer.find( '.nf-styles-panel-controls .nf-styles-css-active' ).toggleClass( 'is-empty', ! hasCssValues );
		},

		/**
		 * Enable only the inputs for the active mode's panel within a fieldset.
		 * @since 3.0.30
		 * @param {jQuery} fieldset Mounted visual style fieldset.
		 * @param {string} mode     Active editing mode ('css' or 'design').
		 * @return {void}
		 */
		setPanelInputs: function( fieldset, mode ) {
			window.nfStylesShared.setPanelInputs( fieldset, mode );
		},

		/**
		 * Persist a style value and refresh caches and the live builder preview.
		 *
		 * @since 3.0.30
		 * @param {string}         name         Full setting name being changed.
		 * @param {string}         value        New value to store ('' clears it).
		 * @param {Backbone.Model} settingModel Style setting group model.
		 * @param {Backbone.Model} dataModel    Data model to update.
		 * @param {Object}         event        Originating DOM/synthetic event.
		 * @return {void}
		 */
		updateSetting: function( name, value, settingModel, dataModel, event ) {
			var childSettingModel = settingModel.get( 'settings' ).findWhere( { name: name } );

			if ( ! childSettingModel ) {
				return;
			}

			nfRadio.channel( 'app' ).request( 'change:setting', event, childSettingModel, dataModel, value );
			if ( dataModel && 'function' === typeof dataModel.set ) {
				dataModel.set( name, value );
			}
			this.setCachedPreviewStyle( dataModel, name, value );
			this.syncRawSettingInput( this.getFieldsetForSetting( settingModel, dataModel ), name, value );
			this.applyLivePreview( name, value, dataModel );
			setTimeout( function() {
				this.applyLivePreview( name, value, dataModel );
				this.refreshBuilderPreviewStyles();
			}.bind( this ), 50 );
		},

		/**
		 * Cache a preview value and rebuild the builder preview stylesheet.
		 * @since 3.0.30
		 * @param {Backbone.Model} dataModel Field data model being previewed.
		 * @param {string}         name      Full setting name being cached.
		 * @param {string}         value     Value to cache.
		 * @return {void}
		 */
		setCachedPreviewStyle: function( dataModel, name, value ) {
			this.cachePreviewStyleValue( dataModel, name, value );
			this.renderBuilderPreviewStylesheet();
		},

		/**
		 * Store a single preview style value in the per-field preview cache.
		 * @since 3.0.30
		 * @param {Backbone.Model} dataModel Field data model being previewed.
		 * @param {string}         name      Full setting name being cached.
		 * @param {string}         value     Value to cache.
		 * @return {void}
		 */
		cachePreviewStyleValue: function( dataModel, name, value ) {
			var fieldId = dataModel && 'function' === typeof dataModel.get ? dataModel.get( 'id' ) : '';

			if ( ! fieldId ) {
				return;
			}

			if ( ! this.previewStyleCache[ fieldId ] ) {
				this.previewStyleCache[ fieldId ] = {};
			}
			this.previewStyleCache[ fieldId ][ name ] = value;
		},

		/**
		 * Find the mounted fieldset bound to a setting/data model pair.
		 * @since 3.0.30
		 * @param {Backbone.Model} settingModel Style setting group model.
		 * @param {Backbone.Model} dataModel    Data model being edited.
		 * @return {jQuery} Matched fieldset, or an empty jQuery set.
		 */
		getFieldsetForSetting: function( settingModel, dataModel ) {
			var matched = jQuery();

			jQuery( '.nf-styles-visual-fieldset' ).each( function() {
				var fieldset = jQuery( this );
				if ( fieldset.data( 'style-setting-model' ) === settingModel && fieldset.data( 'style-data-model' ) === dataModel ) {
					matched = fieldset;
					return false;
				}
			} );

			return matched;
		},

		/**
		 * Mirror a value into the matching raw CSS-mode input(s) for a setting.
		 * @since 3.0.30
		 * @param {jQuery} fieldset Mounted visual style fieldset.
		 * @param {string} name     Full setting name to sync.
		 * @param {string} value    Value to write into the raw input(s).
		 * @return {void}
		 */
		syncRawSettingInput: function( fieldset, name, value ) {
			var inputs = jQuery();
			var fieldsetInputs;

			if ( ! fieldset || ! fieldset.length ) {
				return;
			}

			fieldsetInputs = fieldset.find( '.nf-styles-css-editor #'+ name + ', .nf-styles-css-editor [name="' + name + '"], .nf-styles-css-editor [data-setting="' + name + '"]' );
			inputs = inputs.add( fieldsetInputs );

			inputs = inputs.add( fieldset.find( '.nf-styles-css-editor .setting' ).filter( function() {
				return this.id === name || jQuery( this ).data( 'setting' ) === name || jQuery( this ).attr( 'name' ) === name;
			} ) );

			inputs = inputs.add( jQuery( '#' + name + ', [name="' + name + '"], [data-setting="' + name + '"]' ).filter( '.setting, :input' ) );

			if ( ! inputs.length ) {
				return;
			}

			inputs.each( function() {
				var input = jQuery( this );

				if ( input.is( ':checkbox' ) ) {
					input.prop( 'checked', !! value );
					return;
				}

				input.val( value );
			} );
		},

		/**
		 * Apply saved styles to a field's builder item view when supported.
		 * @since 3.0.30
		 * @param {Backbone.View} itemView Rendered field item view.
		 * @return {void}
		 */
		applyStylesToItemView: function( itemView ) {
			if ( ! itemView || ! this.supportsField( itemView.model ) ) {
				return;
			}

			setTimeout( function() {
				this.applyFieldStyles( itemView.model, itemView.$el );
			}.bind( this ), 0 );
		},

		/**
		 * Re-apply every supported field's styles and rebuild the preview sheet.
		 *
		 * Runs on a few delayed passes so late-rendered builder markup is covered.
		 * @since 3.0.30
		 * @return {void}
		 */
		refreshBuilderPreviewStyles: function() {
			var refresh = function() {
				var collection;

				try {
					collection = nfRadio.channel( 'fields' ).request( 'get:collection' );
				} catch ( e ) {
					collection = null;
				}

				if ( ! collection || ! collection.models ) {
					return;
				}

				_.each( collection.models, function( fieldModel ) {
					if ( ! this.supportsField( fieldModel ) ) {
						return;
					}

					this.applyFieldStyles( fieldModel, this.getRenderedFieldElement( fieldModel ) );
				}, this );
				this.renderBuilderPreviewStylesheet();
			}.bind( this );

			setTimeout( refresh, 0 );
			setTimeout( refresh, 100 );
			setTimeout( refresh, 300 );
			setTimeout( refresh, 600 );
		},

		/**
		 * Apply every field-level style group to a field's builder preview.
		 * @since 3.0.30
		 * @param {Backbone.Model} dataModel Field data model being previewed.
		 * @param {jQuery}         fieldEl   Rendered field element to style.
		 * @return {void}
		 */
		applyFieldStyles: function( dataModel, fieldEl ) {
			// Several style groups resolve to the same preview element for some field
			// types (the hover group falls back to the element target, and the list
			// item row group shares the element target on choice fields). Without a
			// record of what this pass has already written, a later group with no
			// saved values clears the value an earlier group just applied.
			this.previewPassClaims = [];

			_.each( this.fieldStyleGroups, function( groupName ) {
				this.applyStyles( dataModel, groupName, fieldEl );
			}, this );

			this.previewPassClaims = null;
		},

		/**
		 * Filter preview targets so a pass never clears what it already set.
		 *
		 * Outside a full pass (a single control edit) every target is returned
		 * unchanged, so an explicit clear still clears.
		 *
		 * @since 3.0.30
		 * @param {jQuery} target      Matched preview target element(s).
		 * @param {string} cssProperty CSS property being written.
		 * @param {string} value       Value being written ('' clears).
		 * @return {jQuery} Targets that should actually receive the write.
		 */
		claimPreviewTargets: function( target, cssProperty, value ) {
			return StylesPreview.claimPreviewTargets( target, cssProperty, value, this.previewPassClaims );
		},

		/**
		 * Apply all visual properties of a style group to the live preview.
		 * @since 3.0.30
		 * @param {Backbone.Model} dataModel Field or form data model being previewed.
		 * @param {string}         groupName Style group name to apply.
		 * @param {jQuery}         [fieldEl] Optional rendered field element to target.
		 * @return {void}
		 */
		applyStyles: function( dataModel, groupName, fieldEl ) {
			var prefix = groupName + '_';

			_.each( this.settings, function( config, property ) {
				var settingName = prefix + property;
				var value = this.getPreviewStyleValue( dataModel, settingName );

				this.cachePreviewStyleValue( dataModel, settingName, value );
				this.applyLivePreview( settingName, value, dataModel, fieldEl );
			}.bind( this ) );

			this.applyVisibleBorderFallback( dataModel, groupName, fieldEl );
		},

		/**
		 * Get a setting's value, falling back to the cached preview value.
		 * @since 3.0.30
		 * @param {Backbone.Model} dataModel Field data model being previewed.
		 * @param {string}         name      Full setting name to read.
		 * @return {string} The saved or cached value, or '' when unset.
		 */
		getPreviewStyleValue: function( dataModel, name ) {
			var value = dataModel.get( name );
			var fieldId = dataModel.get( 'id' );

			if ( 'undefined' !== typeof value && null !== value && '' !== value ) {
				return value;
			}

			if ( this.previewStyleCache[ fieldId ] && 'undefined' !== typeof this.previewStyleCache[ fieldId ][ name ] ) {
				return this.previewStyleCache[ fieldId ][ name ];
			}

			return '';
		},

		/**
		 * Rebuild the injected builder preview stylesheet from the value cache.
		 * @since 3.0.30
		 * @return {void}
		 */
		renderBuilderPreviewStylesheet: function() {
			StylesPreview.renderBuilderPreviewStylesheet( this.previewStyleCache );
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
			return StylesPreview.getPreviewStylesheetSelector( fieldId, groupName, property );
		},

		/**
		 * Find the rendered builder element for a field by id.
		 * @since 3.0.30
		 * @param {Backbone.Model} dataModel Field data model to locate.
		 * @return {jQuery} The field's rendered element, or an empty set.
		 */
		getRenderedFieldElement: function( dataModel ) {
			var id = dataModel && 'function' === typeof dataModel.get ? dataModel.get( 'id' ) : '';
			var field = jQuery( '#field-' + id );

			if ( ! field.length ) {
				field = jQuery( '#' + id + '.nf-field-wrap' );
			}

			if ( ! field.length ) {
				field = jQuery( '.nf-field-wrap[data-id="' + id + '"]' );
			}

			return field;
		},

		/**
		 * Apply a single style value to its live preview target in the builder.
		 *
		 * @since 3.0.30
		 * @param {string}         name      Full setting name (group_property).
		 * @param {string}         value     Value to apply ('' removes the style).
		 * @param {Backbone.Model} dataModel Field data model being previewed.
		 * @param {jQuery}         [fieldEl] Optional rendered field element to target.
		 * @return {void}
		 */
		applyLivePreview: function( name, value, dataModel, fieldEl ) {
			var groupName = this.getStyleGroupFromSettingName( name );
			var property = groupName ? name.replace( groupName + '_', '' ) : '';
			var cssProperty = 'border' === property ? 'border-width' : property;
			var target;

			if ( ! groupName || ! this.settings[ property ] ) {
				return;
			}

			target = this.getPreviewTarget( dataModel, groupName, fieldEl, property );

			if ( ! target.length ) {
				return;
			}

			target = this.claimPreviewTargets( target, cssProperty, value );

			if ( ! target.length ) {
				return;
			}

			this.setPreviewStyle( target, cssProperty, value );

			if ( 'border' === property || 'border-style' === property ) {
				this.applyVisibleBorderFallback( dataModel, groupName, fieldEl );
			}
		},

		/**
		 * Resolve the builder preview element(s) a style group should apply to.
		 *
		 * @since 3.0.30
		 * @param {Backbone.Model} dataModel Field data model being previewed.
		 * @param {string}         groupName Style group name (e.g. label_styles).
		 * @param {jQuery}         fieldEl   Rendered field element, or empty to look it up.
		 * @param {string}         property  Style property being applied.
		 * @return {jQuery} Matched preview target element(s).
		 */
		getPreviewTarget: function( dataModel, groupName, fieldEl, property ) {
			return StylesPreview.getPreviewTarget( dataModel, groupName, fieldEl, property, this.getRenderedFieldElement.bind( this ) );
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
			return StylesPreview.getElementPreviewTarget( field, dataModel, property );
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
			StylesPreview.setPreviewStyle( target, property, value );
		},

		/**
		 * Force a solid border style when a width is set without an explicit style.
		 *
		 * Keeps a configured border width visible in the preview when no
		 * border-style value has been chosen.
		 * @since 3.0.30
		 * @param {Backbone.Model} dataModel Field data model being previewed.
		 * @param {string}         groupName Style group name being applied.
		 * @param {jQuery}         [fieldEl] Optional rendered field element to target.
		 * @return {void}
		 */
		applyVisibleBorderFallback: function( dataModel, groupName, fieldEl ) {
			var target = this.getPreviewTarget( dataModel, groupName, fieldEl, 'border-style' );
			var width = dataModel.get( groupName + '_border' );
			var style = dataModel.get( groupName + '_border-style' );

			if ( ! target.length || style ) {
				return;
			}

			this.setPreviewStyle( target, 'border-style', width && '0px' !== width ? 'solid' : '' );
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
			return StylesPreview.getStyleGroupFromSettingName( name );
		},

		/**
		 * Persist a spacing value from the single "all sides" range control.
		 *
		 * Defers to the per-side controls when the control is in split mode.
		 * @since 3.0.30
		 * @param {jQuery}         target       The all-sides spacing range input.
		 * @param {Backbone.Model} settingModel Style setting group model.
		 * @param {Backbone.Model} dataModel    Data model to update.
		 * @param {Object}         event        Originating DOM/synthetic event.
		 * @return {void}
		 */
		updateSpacingFromAllControl: function( target, settingModel, dataModel, event ) {
			var value = target.val();
			var unit = target.data( 'unit' ) || '';
			var control = target.closest( '.nf-styles-visual-control-spacing' );
			var settingValue = value + unit;

			if ( control.hasClass( 'is-split' ) ) {
				this.syncSideControlsFromAllControl( control );
				this.updateSpacingFromSideControls( control, settingModel, dataModel, event );
				return;
			}

			this.updateSetting( target.data( 'setting-name' ), settingValue, settingModel, dataModel, event );
			this.updateSpacingValueLabel( control, settingValue );
			control.find( '.nf-styles-visual-reset' ).removeClass( 'is-empty' );
		},

		/**
		 * Persist a spacing value from the per-side range controls as shorthand.
		 * @since 3.0.30
		 * @param {jQuery}         control      The spacing control element.
		 * @param {Backbone.Model} settingModel Style setting group model.
		 * @param {Backbone.Model} dataModel    Data model to update.
		 * @param {Object}         event        Originating DOM/synthetic event.
		 * @return {void}
		 */
		updateSpacingFromSideControls: function( control, settingModel, dataModel, event ) {
			var settingName = control.find( '.nf-styles-visual-side-range' ).first().data( 'setting-name' );
			var settingValue = window.nfStylesShared.getSpacingFromSides( control, '.nf-styles-visual-side-range' );

			this.updateSetting( settingName, settingValue, settingModel, dataModel, event );
			this.updateSpacingValueLabel( control, settingValue );
			control.find( '.nf-styles-visual-reset' ).removeClass( 'is-empty' );
		},

		/**
		 * Seed the per-side range controls from the all-sides value.
		 * @since 3.0.30
		 * @param {jQuery} control The spacing control element.
		 * @return {void}
		 */
		syncSideControlsFromAllControl: function( control ) {
			var value = control.find( '.nf-styles-visual-spacing-all' ).val();

			control.find( '.nf-styles-visual-side-range' ).val( value );
		},

		/**
		 * Update a spacing control's value label from a shorthand value.
		 * @since 3.0.30
		 * @param {jQuery} control The spacing control element.
		 * @param {string} value   Spacing shorthand value to display.
		 * @return {void}
		 */
		updateSpacingValueLabel: function( control, value ) {
			var range = control.find( '.nf-styles-visual-range' ).first();
			var unit = range.data( 'unit' ) || '';
			var settingName = range.data( 'setting-name' );
			var parts = String( value || '' ).trim().split( /\s+/ ).filter( Boolean );
			var numeric = 1 === parts.length ? this.getNumericValue( value, range.data( 'default-value' ) || range.attr( 'min' ) || 0 ) : '';

			if ( '' === numeric ) {
				control.find( '.nf-styles-visual-value' ).removeClass( 'is-default nf-styles-visual-value-editable' ).text( value );
				return;
			}

			this.updateEditableValueLabel( control, numeric, unit, settingName );
		},

		/**
		 * Update (or build) a range control's editable numeric value label.
		 * @since 3.0.30
		 * @param {jQuery}          control     The range/spacing control element.
		 * @param {(number|string)} value       Numeric value to display.
		 * @param {string}          unit        Value unit (e.g. px).
		 * @param {string}          settingName Full setting name the value edits.
		 * @return {void}
		 */
		updateEditableValueLabel: function( control, value, unit, settingName ) {
			var label = control.find( '.nf-styles-visual-range-header label' ).first().text();
			var range = control.find( '.nf-styles-visual-range' ).first();
			var valueControl = control.find( '.nf-styles-visual-value' ).first();
			var config = {
				label: label,
				min: range.attr( 'min' ),
				max: range.attr( 'max' )
			};

			if ( ! valueControl.find( '.nf-styles-visual-value-input' ).length ) {
				valueControl.replaceWith( this.renderValueControl( value + unit, value, unit, settingName, config ) );
				return;
			}

			valueControl.removeClass( 'is-default' );
			valueControl.find( '.nf-styles-visual-value-input' ).text( value ).attr( 'aria-valuenow', value ).data( 'unit', unit ).attr( 'data-unit', unit );
			valueControl.find( '.nf-styles-visual-value-unit' ).text( unit );
		},

		/**
		 * Clamp a raw value to a range input's min/max, falling back sensibly.
		 * @since 3.0.30
		 * @param {string} value Raw value to normalize.
		 * @param {jQuery} range The range input providing min/max bounds.
		 * @return {string} The clamped numeric value as a string.
		 */
		normalizeRangeValue: function( value, range ) {
			return window.nfStylesShared.normalizeRangeValue( value, range );
		},

		/**
		 * Parse a spacing shorthand into top/right/bottom/left numeric values.
		 * @since 3.0.30
		 * @param {string} value  Spacing shorthand value to parse.
		 * @param {Object} config Control configuration (default/min fallback).
		 * @return {Object} Numeric values keyed by top, right, bottom, and left.
		 */
		getSpacingValues: function( value, config ) {
			return StylesUnits.getSpacingValues( value, config );
		},

		/**
		 * Determine whether a spacing value specifies more than one side.
		 * @since 3.0.30
		 * @param {string} value Spacing shorthand value to test.
		 * @return {boolean} Whether the value has multiple parts.
		 */
		isSplitSpacingValue: function( value ) {
			return StylesUnits.isSplitSpacingValue( value );
		},

		/**
		 * Build a child setting's full name from its group and property.
		 * @since 3.0.30
		 * @param {Backbone.Model} settingModel Style setting group model.
		 * @param {string}         property     Style property key.
		 * @return {string} Full setting name (group_property).
		 */
		getSettingName: function( settingModel, property ) {
			return StylesControls.getSettingName( settingModel, property );
		},

		/**
		 * Extract the leading numeric portion of a value, or a fallback.
		 * @since 3.0.30
		 * @param {string} value    Value to read a number from.
		 * @param {*}      fallback Value returned when no number is present.
		 * @return {(string|*)} The numeric string, or the fallback.
		 */
		getNumericValue: function( value, fallback ) {
			return StylesUnits.getNumericValue( value, fallback );
		},

		/**
		 * Detect which configured unit a value (or spacing shorthand) uses.
		 * @since 3.0.30
		 * @param {string} value  Value to inspect.
		 * @param {Object} config Control configuration (available units).
		 * @return {string} The matched unit, or the control's default unit.
		 */
		getValueUnit: function( value, config ) {
			return StylesUnits.getValueUnit( value, config );
		},

		/**
		 * Resolve the min/max/step range for a control in a given unit.
		 * @since 3.0.30
		 * @param {Object} config Control configuration with unit options.
		 * @param {string} unit   Unit to resolve the range for.
		 * @return {Object} Range with min, max, and step values.
		 */
		getUnitRangeConfig: function( config, unit ) {
			return StylesUnits.getUnitRangeConfig( config, unit );
		},

		/**
		 * Determine whether a saved value can be edited by the visual controls.
		 * @since 3.0.30
		 * @param {string} property Style property key.
		 * @param {string} value    Saved value to test.
		 * @return {boolean} Whether the value is representable in the visual UI.
		 */
		isEditableVisualValue: function( property, value ) {
			return StylesControls.isEditableVisualValue( property, value );
		},

		/**
		 * Determine whether a value is a number in the given unit.
		 * @since 3.0.30
		 * @param {string} value Value to test.
		 * @param {string} unit  Unit the value must end with.
		 * @return {boolean} Whether the value matches the unit.
		 */
		isUnitValue: function( value, unit ) {
			return StylesUnits.isUnitValue( value, unit );
		},

		/**
		 * Determine whether a value uses one of a control's supported units.
		 * @since 3.0.30
		 * @param {string} value  Value to test.
		 * @param {Object} config Control configuration (available units).
		 * @return {boolean} Whether the value uses a supported unit.
		 */
		isSupportedUnitValue: function( value, config ) {
			return StylesUnits.isSupportedUnitValue( value, config );
		},

		/**
		 * Determine whether a value is a 1-4 part spacing shorthand in one unit.
		 * @since 3.0.30
		 * @param {string} value  Value to test.
		 * @param {Object} config Control configuration (available units).
		 * @return {boolean} Whether the value is a valid spacing shorthand.
		 */
		isSpacingValue: function( value, config ) {
			return StylesUnits.isSpacingValue( value, config );
		},

		/**
		 * Determine whether a value is a 6-digit hex color.
		 * @since 3.0.30
		 * @param {string} value Value to test.
		 * @return {boolean} Whether the value is a #rrggbb color.
		 */
		isColor: function( value ) {
			return StylesUnits.isColor( value );
		}
	} );

	return controller;
} );

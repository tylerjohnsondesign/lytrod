/* global jQuery */
/**
 * Add and remove rows in the Licenses and Term Discounts tables.
 *
 * Rows use bare `name="thing[]"` arrays, so DOM order is the index and nothing needs
 * reindexing after an add, a delete or a drag — the same approach WooCommerce uses for
 * downloadable files (assets/js/admin/meta-boxes-product.js:351-369).
 */
jQuery( function ( $ ) {
	'use strict';

	var $panel = $( '#woocommerce-product-data' );

	$panel.on( 'click', '.lytrod-rows .lytrod-add-row', function ( e ) {
		e.preventDefault();
		$( this ).closest( '.lytrod-rows' ).find( 'tbody' ).append( $( this ).data( 'row' ) );
		$( document.body ).trigger( 'wc-enhanced-select-init' );
	} );

	$panel.on( 'click', '.lytrod-rows .lytrod-delete-row', function ( e ) {
		e.preventDefault();

		var $body = $( this ).closest( 'tbody' );

		// Never leave the table with no rows at all — an empty tier table would price
		// everything at zero, so blank the last row instead of removing it.
		if ( $body.find( 'tr' ).length > 1 ) {
			$( this ).closest( 'tr' ).remove();
		} else {
			$body.find( 'input' ).val( '' );
		}
	} );

	if ( $.fn.sortable ) {
		$( '.lytrod-rows tbody' ).sortable( {
			items: 'tr',
			cursor: 'move',
			axis: 'y',
			handle: 'td.sort',
			forcePlaceholderSize: true,
			helper: 'clone',
			opacity: 0.65
		} );
	}
} );

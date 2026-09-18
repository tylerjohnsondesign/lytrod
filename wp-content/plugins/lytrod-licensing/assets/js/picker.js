/* global jQuery, lytrodLicence */
/**
 * Live licence pricing on the product page.
 *
 * Mirrors Lytrod_Licensing_Rates::price() in the browser so the figure moves as the customer
 * changes the term or the licence count. The server recomputes independently at add-to-cart and
 * again in the cart, so nothing here decides money — it is display only.
 */
( function ( $ ) {
	'use strict';

	if ( typeof lytrodLicence === 'undefined' ) {
		return;
	}

	var $picker = $( '.lytrod-picker' );

	if ( ! $picker.length ) {
		return;
	}

	var $seats = $picker.find( '#lytrod-seats' );
	var $tier = $picker.find( '.lytrod-picker__tier' );
	var $total = $picker.find( '.lytrod-quote__total' );
	var $note = $picker.find( '.lytrod-quote__note' );

	var maxSeats = parseInt( lytrodLicence.maxSeats, 10 ) || 999;
	var decimals = parseInt( lytrodLicence.decimals, 10 );

	if ( isNaN( decimals ) ) {
		decimals = 2;
	}

	function money( amount ) {
		return lytrodLicence.currency + amount.toLocaleString( undefined, {
			minimumFractionDigits: decimals,
			maximumFractionDigits: decimals
		} );
	}

	/**
	 * Graduated annual total: each tier prices only the seats inside it.
	 */
	function annualFor( seats ) {
		var total = 0;
		var tier = '';

		$.each( lytrodLicence.tiers || [], function ( _, row ) {
			var from = Math.max( 1, parseInt( row.from, 10 ) );
			var to = ( null === row.to || '' === row.to || undefined === row.to ) ? Infinity : parseInt( row.to, 10 );
			var inTier;

			if ( seats < from ) {
				return;
			}

			inTier = Math.min( seats, to ) - from + 1;

			if ( inTier > 0 ) {
				total += inTier * parseFloat( row.rate );
				tier = row.name;
			}
		} );

		return { annual: total, tier: tier };
	}

	function currentTerm() {
		var checked = $picker.find( 'input[name="lytrod_term"]:checked' );
		var years;

		if ( ! checked.length ) {
			checked = $picker.find( 'input[name="lytrod_term"]' ).first();
		}

		years = parseInt( checked.val(), 10 );

		return isNaN( years ) || years < 1 ? 1 : years;
	}

	function currentSeats() {
		var seats = parseInt( $seats.val(), 10 );

		if ( isNaN( seats ) || seats < 1 ) {
			seats = 1;
		}

		if ( seats > maxSeats ) {
			seats = maxSeats;
		}

		return seats;
	}

	function update() {
		var seats = currentSeats();
		var years = currentTerm();
		var priced = annualFor( seats );
		var discount = parseFloat( ( lytrodLicence.discounts || {} )[ years ] );
		var termTotal;
		var notes = [];

		if ( isNaN( discount ) ) {
			discount = 0;
		}

		termTotal = priced.annual * years * ( 1 - ( discount / 100 ) );

		if ( String( seats ) !== $seats.val() ) {
			$seats.val( seats );
		}

		$tier.text( priced.tier
			? priced.tier + ' — ' + seats + ( 1 === seats ? ' licence' : ' licences' )
			: seats + ( 1 === seats ? ' licence' : ' licences' ) );

		if ( lytrodLicence.freeFor ) {
			// Nothing is due now; the term price applies from the first renewal.
			$total.text( money( 0 ) );
			notes.push( 'Free for ' + lytrodLicence.freeFor + ', then ' + money( termTotal ) +
				( years > 1 ? ' every ' + years + ' years' : ' per year' ) );
		} else {
			$total.text( money( termTotal ) );
			notes.push( years > 1
				? 'Renews at ' + money( termTotal ) + ' every ' + years + ' years'
				: 'Renews at ' + money( termTotal ) + ' per year' );
		}

		if ( discount > 0 ) {
			notes.push( 'includes ' + String( parseFloat( discount.toFixed( 2 ) ) ) + '% term discount' );
		}

		$note.text( notes.join( ' · ' ) );
	}

	$picker.on( 'click', '.lytrod-stepper__btn', function () {
		var step = parseInt( $( this ).data( 'step' ), 10 ) || 0;

		$seats.val( Math.min( maxSeats, Math.max( 1, currentSeats() + step ) ) );
		update();
	} );

	$seats.on( 'input change', update );
	$picker.on( 'change', 'input[name="lytrod_term"]', update );

	update();
} )( jQuery );

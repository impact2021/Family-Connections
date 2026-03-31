/**
 * FC Courses – Front-end scripts.
 *
 * Handles:
 *  - Dynamic course date loading when a course is selected.
 *  - Showing/hiding the discount code, price summary, and payment method
 *    sections for paid courses.
 *  - AJAX discount code validation.
 *
 * Global: fcCourses { ajaxUrl, nonce, stripeKey, currency, i18n }
 *
 * @package FC_Courses
 */

( function ( $ ) {
	'use strict';

	var $courseSelect  = $( '#fc_course_select' );
	var $dateSelect    = $( '#course_date_id' );
	var $discountField = $( '#fc-discount-field' );
	var $priceRow      = $( '#fc-price-summary' );
	var $paymentField  = $( '#fc-payment-field' );
	var $priceAmount   = $( '#fc-price-amount' );

	/**
	 * Show or hide the paid-course fields based on course type.
	 *
	 * @param {string} courseType 'paid' | 'free'
	 * @param {number} price
	 * @param {string} currency
	 */
	function togglePaidFields( courseType, price, currency ) {
		if ( 'paid' === courseType && parseFloat( price ) > 0 ) {
			$discountField.show();
			$paymentField.show();
			$priceRow.show();
			$priceAmount.text( currency.toUpperCase() + ' ' + parseFloat( price ).toFixed( 2 ) );
		} else {
			$discountField.hide();
			$paymentField.hide();
			$priceRow.hide();
		}
	}

	/**
	 * Load available dates for the selected course via AJAX.
	 */
	function loadDates( courseId ) {
		if ( ! courseId ) {
			$dateSelect.html( '<option value="">' + ( fcCourses.i18n.chooseCourseFirst || '— Choose a course first —' ) + '</option>' );
			$discountField.hide();
			$paymentField.hide();
			$priceRow.hide();
			return;
		}

		$dateSelect.html( '<option value="">' + ( fcCourses.i18n.loading || 'Loading…' ) + '</option>' );

		$.post( fcCourses.ajaxUrl, {
			action : 'fc_get_course_dates',
			course_id: courseId,
			nonce  : fcCourses.nonce,
		}, function ( response ) {
			if ( response.success && response.data.dates ) {
				var options = '<option value="">' + ( fcCourses.i18n.chooseDate || '— Choose a date —' ) + '</option>';
				$.each( response.data.dates, function ( i, d ) {
					options += '<option value="' + d.id + '">' + d.label + '</option>';
				} );
				$dateSelect.html( options );
			} else {
				$dateSelect.html( '<option value="">' + ( fcCourses.i18n.noDates || '— No upcoming dates —' ) + '</option>' );
			}
		} );
	}

	// ── Initialise on page load ─────────────────────────────────

	// If a course is already pre-selected (course_id in shortcode attr),
	// show the appropriate paid/free fields immediately.
	var initialType     = $courseSelect.data( 'type' );
	var initialPrice    = $courseSelect.data( 'price' );
	var initialCurrency = $courseSelect.data( 'currency' ) || fcCourses.currency;

	if ( initialType ) {
		togglePaidFields( initialType, initialPrice, initialCurrency );
	}

	// ── Course selector change ──────────────────────────────────

	$courseSelect.on( 'change', function () {
		var $opt     = $( this ).find( ':selected' );
		var type     = $opt.data( 'type' );
		var price    = $opt.data( 'price' );
		var currency = $opt.data( 'currency' ) || fcCourses.currency;

		loadDates( $( this ).val() );
		togglePaidFields( type, price, currency );
	} );

	// ── Discount code ────────────────────────────────────────────

	$( '.fc-apply-code' ).on( 'click', function () {
		var code     = $( '#discount_code' ).val().trim().toUpperCase();
		var courseId = $courseSelect.val();
		var $msg     = $( '.fc-discount-message' );

		if ( ! code ) {
			return;
		}

		$msg.text( fcCourses.i18n.processing );

		$.post( fcCourses.ajaxUrl, {
			action    : 'fc_validate_discount_code',
			code      : code,
			course_id : courseId,
			nonce     : fcCourses.nonce,
		}, function ( response ) {
			if ( response.success ) {
				$msg.css( 'color', '#2e7d32' ).text( fcCourses.i18n.codeApplied );

				// Update displayed price.
				var newAmount = response.data.amount;
				$priceAmount.text( ( response.data.currency || fcCourses.currency ).toUpperCase() + ' ' + parseFloat( newAmount ).toFixed( 2 ) );
			} else {
				$msg.css( 'color', '#c62828' ).text( fcCourses.i18n.invalidCode );
			}
		} );
	} );

} )( jQuery );

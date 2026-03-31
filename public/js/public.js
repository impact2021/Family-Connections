/**
 * FC Courses – Front-end scripts.
 *
 * Handles:
 *  - Dynamic course date loading when a course is selected.
 *  - Showing/hiding the discount code, price summary, and payment method
 *    sections for paid courses.
 *  - AJAX discount code validation.
 *  - Inline registration form in [fc_course_calendar course_id="N"].
 *
 * Global: fcCourses { ajaxUrl, nonce, stripeKey, currency, currencySymbol, i18n }
 *
 * @package FC_Courses
 */

( function ( $ ) {
	'use strict';

	// ── Shared helpers ──────────────────────────────────────────

	/**
	 * Return the currency symbol to display.
	 * Uses fcCourses.currencySymbol when available, otherwise falls back to
	 * the uppercased currency code stored in a data attribute.
	 *
	 * @param {string} currencyCode  e.g. 'nzd'
	 * @return {string}
	 */
	function currencySymbol( currencyCode ) {
		if ( fcCourses.currencySymbol ) {
			return fcCourses.currencySymbol;
		}
		return ( currencyCode || fcCourses.currency || '' ).toUpperCase();
	}

	// ── Standalone [fc_course_registration] form ───────────────

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
	 * @param {jQuery} $discField
	 * @param {jQuery} $priceField
	 * @param {jQuery} $payField
	 * @param {jQuery} $amount
	 */
	function togglePaidFields( courseType, price, currency, $discField, $priceField, $payField, $amount ) {
		$discField  = $discField  || $discountField;
		$priceField = $priceField || $priceRow;
		$payField   = $payField   || $paymentField;
		$amount     = $amount     || $priceAmount;

		if ( 'paid' === courseType && parseFloat( price ) > 0 ) {
			$discField.show();
			$payField.show();
			$priceField.show();
			$amount.text( currencySymbol( currency ) + parseFloat( price ).toFixed( 2 ) );
		} else {
			$discField.hide();
			$payField.hide();
			$priceField.hide();
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

	// ── Initialise standalone form on page load ─────────────────

	var initialType     = $courseSelect.data( 'type' );
	var initialPrice    = $courseSelect.data( 'price' );
	var initialCurrency = $courseSelect.data( 'currency' ) || fcCourses.currency;

	if ( initialType ) {
		togglePaidFields( initialType, initialPrice, initialCurrency );
	}

	// ── Standalone course selector change ──────────────────────

	$courseSelect.on( 'change', function () {
		var $opt     = $( this ).find( ':selected' );
		var type     = $opt.data( 'type' );
		var price    = $opt.data( 'price' );
		var currency = $opt.data( 'currency' ) || fcCourses.currency;

		loadDates( $( this ).val() );
		togglePaidFields( type, price, currency );
	} );

	// ── Standalone discount code ────────────────────────────────

	$( '.fc-apply-code' ).not( '.fc-cal-apply-code' ).on( 'click', function () {
		var code     = $( '#discount_code' ).val().trim().toUpperCase();
		var courseId = $courseSelect.val();
		var $msg     = $( '.fc-discount-message' ).not( '.fc-cal-discount-message' );

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
				var newAmount = response.data.discounted_price;
				var sym       = currencySymbol( $courseSelect.find( ':selected' ).data( 'currency' ) || $courseSelect.data( 'currency' ) || fcCourses.currency );
				$priceAmount.text( sym + parseFloat( newAmount ).toFixed( 2 ) );
			} else {
				$msg.css( 'color', '#c62828' ).text( fcCourses.i18n.invalidCode );
			}
		} );
	} );

	// ── Inline calendar registration form ──────────────────────

	var $calWrap        = $( '#fc-calendar-register-wrap' );
	var $calTable       = $( '.fc-calendar-table' );
	var $calDateId      = $( '#fc-cal-date-id' );
	var $calDateLabel   = $( '#fc-cal-reg-date-label' );
	var $calCourseData  = $( '#fc-cal-course-data' );
	var $calDiscField   = $( '.fc-cal-discount-field' );
	var $calPriceSumm   = $( '.fc-cal-price-summary' );
	var $calPayField    = $( '.fc-cal-payment-field' );
	var $calPriceAmount = $( '.fc-cal-price-amount' );

	// Initialise inline form paid/free fields based on course data.
	if ( $calCourseData.length ) {
		var calType     = $calCourseData.data( 'type' );
		var calPrice    = $calCourseData.data( 'price' );
		var calCurrency = $calCourseData.data( 'currency' ) || fcCourses.currency;
		togglePaidFields( calType, calPrice, calCurrency, $calDiscField, $calPriceSumm, $calPayField, $calPriceAmount );
	}

	// Register button click — reveal inline form with selected date.
	$( '.fc-cal-register-btn' ).on( 'click', function () {
		var $btn     = $( this );
		var dateId   = $btn.data( 'date-id' );
		var label    = $btn.data( 'date-label' );
		var type     = $btn.data( 'type' );
		var price    = $btn.data( 'price' );
		var currency = $btn.data( 'currency' ) || fcCourses.currency;

		$calDateId.val( dateId );
		$calDateLabel.text( label );

		// Show/hide paid fields for this specific date's price.
		togglePaidFields( type, price, currency, $calDiscField, $calPriceSumm, $calPayField, $calPriceAmount );

		$calTable.hide();
		$calWrap.slideDown( 200 );

		// Scroll to form.
		$( 'html, body' ).animate( { scrollTop: $calWrap.offset().top - 40 }, 300 );
	} );

	// "← Back" button — hide inline form, show table.
	$( '.fc-cal-back-btn' ).on( 'click', function () {
		$calWrap.slideUp( 200, function () {
			$calTable.show();
		} );
	} );

	// Calendar inline discount code.
	$( '.fc-cal-apply-code' ).on( 'click', function () {
		var code     = $( '#fc_cal_discount_code' ).val().trim().toUpperCase();
		var courseId = $calCourseData.data( 'id' ) || '';
		var $msg     = $( '.fc-cal-discount-message' );

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
				var newAmount = response.data.discounted_price;
				var sym       = currencySymbol( $calCourseData.data( 'currency' ) || fcCourses.currency );
				$calPriceAmount.text( sym + parseFloat( newAmount ).toFixed( 2 ) );
			} else {
				$msg.css( 'color', '#c62828' ).text( fcCourses.i18n.invalidCode );
			}
		} );
	} );

	// If there was a form error (inline form submitted with errors), re-show the inline form.
	if ( $calWrap.length && $calWrap.find( '.fc-notice-error' ).length ) {
		$calTable.hide();
		$calWrap.show();
	}

} )( jQuery );

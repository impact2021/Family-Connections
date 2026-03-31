/**
 * FC Courses – Admin scripts.
 *
 * Handles toggling the "Add New Course" and "Add New Date" forms so they
 * are hidden until the corresponding button is clicked.
 *
 * @package FC_Courses
 */

( function ( $ ) {
	'use strict';

	// ── Courses page ────────────────────────────────────────────

	var $courseFormCol  = $( '#fc-course-form-col' );
	var $toggleCourseBtn = $( '#fc-toggle-course-form' );
	var $cancelCourseBtn = $( '#fc-cancel-course-form' );

	$toggleCourseBtn.on( 'click', function () {
		$courseFormCol.slideDown( 200 );
		$( this ).hide();
	} );

	$cancelCourseBtn.on( 'click', function () {
		$courseFormCol.slideUp( 200 );
		$toggleCourseBtn.show();
	} );

	// ── Course Dates page ────────────────────────────────────────

	var $dateFormCol  = $( '#fc-date-form-col' );
	var $toggleDateBtn = $( '#fc-toggle-date-form' );
	var $cancelDateBtn = $( '#fc-cancel-date-form' );

	$toggleDateBtn.on( 'click', function () {
		$dateFormCol.slideDown( 200 );
		$( this ).hide();
	} );

	$cancelDateBtn.on( 'click', function () {
		$dateFormCol.slideUp( 200 );
		$toggleDateBtn.show();
	} );

} )( jQuery );

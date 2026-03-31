<?php
/**
 * Shortcodes for the front-end course pages.
 *
 * Registered shortcodes
 * ─────────────────────
 * [fc_course_list]                       – card grid of all published courses
 * [fc_course_list type="paid|free"]      – filter by course type
 * [fc_course_calendar]                   – upcoming dates for all courses
 * [fc_course_calendar course_id="N"]     – upcoming dates for one course; includes inline registration form
 * [fc_course_calendar limit="N"]         – limit the number of rows shown
 * [fc_course_registration]               – sign-up form (course selector included)
 * [fc_course_registration course_id="N"] – sign-up form locked to a specific course
 *
 * @package FC_Courses
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class FC_Courses_Shortcodes
 */
class FC_Courses_Shortcodes {

	/**
	 * Return the NZ$ symbol (or appropriate symbol) for a currency code.
	 *
	 * @param string $code ISO 4217 currency code (e.g. 'NZD'). Defaults to plugin setting.
	 * @return string Currency symbol.
	 */
	public static function currency_symbol( $code = '' ) {
		if ( ! $code ) {
			$code = get_option( 'fc_currency', 'NZD' );
		}
		$map = array(
			'NZD' => 'NZ$',
			'AUD' => 'A$',
			'USD' => '$',
			'CAD' => 'CA$',
		);
		$code = strtoupper( trim( $code ) );
		return isset( $map[ $code ] ) ? $map[ $code ] : 'NZ$';
	}

	/**
	 * Return the list of participant type options configured in settings.
	 *
	 * @return string[] Array of option strings.
	 */
	public static function get_participant_types() {
		$saved = get_option( 'fc_participant_types', '' );
		if ( ! is_string( $saved ) || '' === trim( $saved ) ) {
			return array(
				__( 'Clinician / Professional', 'fc-courses' ),
				__( 'Whānau Member', 'fc-courses' ),
				__( 'Other', 'fc-courses' ),
			);
		}
		$types = array_filter( array_map( 'trim', explode( "\n", $saved ) ) );
		return array_values( $types );
	}

	/**
	 * Return the configuration for every registration form field.
	 *
	 * Each entry has:
	 *  default_label  string  – the hard-coded fallback label (never changes)
	 *  label          string  – the admin-editable label (falls back to default_label)
	 *  enabled        string  – '1' | '0'  whether the field is shown
	 *  required       string  – '1' | '0'  whether the field is required
	 *
	 * first_name, last_name, and email are always visible and always required;
	 * only their label is customisable.
	 *
	 * @return array<string, array>
	 */
	public static function get_form_fields() {
		$defaults = array(
			'first_name'       => array(
				'default_label' => __( 'First Name', 'fc-courses' ),
				'label'         => __( 'First Name', 'fc-courses' ),
				'enabled'       => '1',
				'required'      => '1',
			),
			'last_name'        => array(
				'default_label' => __( 'Last Name', 'fc-courses' ),
				'label'         => __( 'Last Name', 'fc-courses' ),
				'enabled'       => '1',
				'required'      => '1',
			),
			'email'        => array(
				'default_label' => __( 'Email Address', 'fc-courses' ),
				'label'         => __( 'Email Address', 'fc-courses' ),
				'enabled'       => '1',
				'required'      => '1',
			),
			'phone'            => array(
				'default_label' => __( 'Phone', 'fc-courses' ),
				'label'         => __( 'Phone', 'fc-courses' ),
				'enabled'       => '1',
				'required'      => '0',
			),
			'organisation'     => array(
				'default_label' => __( 'Organisation', 'fc-courses' ),
				'label'         => __( 'Organisation', 'fc-courses' ),
				'enabled'       => '1',
				'required'      => '0',
			),
			'participant_type' => array(
				'default_label' => __( 'Participant Type', 'fc-courses' ),
				'label'         => __( 'Participant Type', 'fc-courses' ),
				'enabled'       => '1',
				'required'      => '1',
			),
		);

		$saved = get_option( 'fc_form_fields', array() );
		if ( ! is_array( $saved ) ) {
			$saved = array();
		}

		foreach ( $defaults as $key => $default ) {
			if ( isset( $saved[ $key ] ) && is_array( $saved[ $key ] ) ) {
				// Label is editable for every field.
				if ( ! empty( $saved[ $key ]['label'] ) ) {
					$defaults[ $key ]['label'] = $saved[ $key ]['label'];
				}
				// Visibility and required are only saved/honoured for phone, organisation, and participant_type;
				// first_name / last_name / email are always on and always required.
				if ( in_array( $key, array( 'phone', 'organisation', 'participant_type' ), true ) ) {
					$defaults[ $key ]['enabled']  = isset( $saved[ $key ]['enabled'] ) ? $saved[ $key ]['enabled'] : $default['enabled'];
					$defaults[ $key ]['required'] = isset( $saved[ $key ]['required'] ) ? $saved[ $key ]['required'] : $default['required'];
				}
			}
		}

		return $defaults;
	}

	/**
	 * Register hooks.
	 */
	public function init() {
		add_shortcode( 'fc_course_registration', array( $this, 'registration_form' ) );
		add_shortcode( 'fc_course_list', array( $this, 'course_list' ) );
		add_shortcode( 'fc_course_calendar', array( $this, 'course_calendar' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Enqueue front-end CSS / JS.
	 */
	public function enqueue_assets() {
		wp_enqueue_style(
			'fc-courses-public',
			FC_COURSES_PLUGIN_URL . 'public/css/public.css',
			array(),
			FC_COURSES_VERSION
		);

		wp_enqueue_script(
			'fc-courses-public',
			FC_COURSES_PLUGIN_URL . 'public/js/public.js',
			array( 'jquery' ),
			FC_COURSES_VERSION,
			true
		);

		$stripe_key = get_option( 'fc_stripe_publishable_key', '' );
		if ( $stripe_key ) {
			wp_enqueue_script(
				'stripe-js',
				'https://js.stripe.com/v3/',
				array(),
				null, // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
				true
			);
		}

		wp_localize_script(
			'fc-courses-public',
			'fcCourses',
			array(
				'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
				'nonce'          => wp_create_nonce( 'fc_courses_public_nonce' ),
				'stripeKey'      => $stripe_key,
				'currency'       => strtolower( get_option( 'fc_currency', 'NZD' ) ),
				'currencySymbol' => self::currency_symbol(),
				'i18n'           => array(
					'processing'   => __( 'Processing…', 'fc-courses' ),
					'invalidCode'  => __( 'Invalid or expired discount code.', 'fc-courses' ),
					'codeApplied'  => __( 'Discount applied!', 'fc-courses' ),
				),
			)
		);
	}

	// ------------------------------------------------------------------
	// [fc_course_list]
	// ------------------------------------------------------------------

	/**
	 * Render a list of available courses.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function course_list( $atts ) {
		$atts = shortcode_atts(
			array(
				'type' => '',
			),
			$atts,
			'fc_course_list'
		);

		global $wpdb;
		$where = "status = 'publish'";
		if ( ! empty( $atts['type'] ) ) {
			$where .= $wpdb->prepare( ' AND course_type = %s', sanitize_text_field( $atts['type'] ) );
		}
		$courses = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}fc_courses WHERE {$where} ORDER BY title ASC" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		ob_start();
		include FC_COURSES_PLUGIN_DIR . 'public/views/course-list.php';
		return ob_get_clean();
	}

	// ------------------------------------------------------------------
	// [fc_course_calendar]
	// ------------------------------------------------------------------

	/**
	 * Render a table of upcoming course dates.
	 *
	 * When course_id is provided, an inline registration form is embedded and shown
	 * when the visitor clicks a Register button — no separate registration page needed.
	 *
	 * Attributes:
	 *  course_id  int   Limit to a specific course (0 = all courses).
	 *  limit      int   Maximum number of rows to show (0 = unlimited).
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function course_calendar( $atts ) {
		$atts = shortcode_atts(
			array(
				'course_id' => 0,
				'limit'     => 0,
			),
			$atts,
			'fc_course_calendar'
		);

		global $wpdb;

		$course_id = absint( $atts['course_id'] );
		$limit     = absint( $atts['limit'] );

		$sql  = "SELECT cd.*, c.title AS course_title, c.course_type, c.price, c.currency,
		                (SELECT COUNT(*) FROM {$wpdb->prefix}fc_enrollments e WHERE e.course_date_id = cd.id) AS enrolment_count
		         FROM {$wpdb->prefix}fc_course_dates cd
		         INNER JOIN {$wpdb->prefix}fc_courses c ON c.id = cd.course_id
		         WHERE cd.status = 'open' AND cd.start_date > NOW() AND c.status = 'publish'";
		$args = array();

		if ( $course_id > 0 ) {
			$sql   .= ' AND cd.course_id = %d';
			$args[] = $course_id;
		}

		$sql .= ' ORDER BY cd.start_date ASC';

		if ( $limit > 0 ) {
			$sql   .= ' LIMIT %d';
			$args[] = $limit;
		}

		$dates = $args
			? $wpdb->get_results( $wpdb->prepare( $sql, $args ) ) // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			: $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		// Load the course object when a specific course_id is given (used by the inline form).
		$calendar_course = null;
		if ( $course_id > 0 ) {
			$calendar_course = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}fc_courses WHERE id = %d AND status = 'publish'", $course_id ) );
		}

		// Handle inline registration form submission.
		$form_message = '';
		$form_error   = '';
		if ( isset( $_POST['fc_register_nonce'] ) && isset( $_POST['fc_inline_calendar'] ) ) {
			$result       = $this->process_registration();
			$form_message = $result['message'] ?? '';
			$form_error   = $result['error'] ?? '';
		}

		$fields           = self::get_form_fields();
		$participant_types = self::get_participant_types();

		ob_start();
		include FC_COURSES_PLUGIN_DIR . 'public/views/course-calendar.php';
		return ob_get_clean();
	}

	// ------------------------------------------------------------------
	// [fc_course_registration]
	// ------------------------------------------------------------------

	/**
	 * Render the registration / sign-up form for a specific course.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function registration_form( $atts ) {
		$atts = shortcode_atts(
			array(
				'course_id' => 0,
			),
			$atts,
			'fc_course_registration'
		);

		global $wpdb;

		$course_id = absint( $atts['course_id'] );

		// Pre-populate from URL query params (e.g. the Register button in [fc_course_calendar]).
		// These are UI-only hints: absint() sanitises the values and they only pre-select
		// dropdown options; the form submission is independently validated via fc_register_nonce.
		if ( 0 === $course_id ) {
			$course_id = isset( $_GET['fc_course'] ) ? absint( $_GET['fc_course'] ) : 0;
		}
		$preselect_date_id = isset( $_GET['fc_date'] ) ? absint( $_GET['fc_date'] ) : 0;

		$course    = null;
		$dates     = array();

		if ( $course_id > 0 ) {
			$course = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}fc_courses WHERE id = %d AND status = 'publish'", $course_id ) );
		}

		// If no specific course given, get all published courses.
		$courses = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}fc_courses WHERE status = 'publish' ORDER BY title ASC" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		if ( $course ) {
			$dates = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT cd.*, (SELECT COUNT(*) FROM {$wpdb->prefix}fc_enrollments e WHERE e.course_date_id = cd.id) AS enrolment_count
					FROM {$wpdb->prefix}fc_course_dates cd
					WHERE cd.course_id = %d AND cd.status = 'open' AND cd.start_date > NOW()
					ORDER BY cd.start_date ASC",
					$course_id
				)
			);
		}

		// Handle form submission.
		$form_message = '';
		$form_error   = '';
		if ( isset( $_POST['fc_register_nonce'] ) ) {
			$result       = $this->process_registration();
			$form_message = $result['message'] ?? '';
			$form_error   = $result['error'] ?? '';
		}

		$fields            = self::get_form_fields();
		$participant_types = self::get_participant_types();

		ob_start();
		include FC_COURSES_PLUGIN_DIR . 'public/views/registration-form.php';
		return ob_get_clean();
	}

	/**
	 * Process the submitted registration form.
	 *
	 * @return array  Keys: 'message' (success) or 'error'.
	 */
	private function process_registration() {
		if ( ! isset( $_POST['fc_register_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['fc_register_nonce'] ), 'fc_course_register' ) ) {
			return array( 'error' => __( 'Security check failed. Please try again.', 'fc-courses' ) );
		}

		global $wpdb;

		$course_date_id   = absint( $_POST['course_date_id'] ?? 0 );
		$first_name       = sanitize_text_field( wp_unslash( $_POST['first_name'] ?? '' ) );
		$last_name        = sanitize_text_field( wp_unslash( $_POST['last_name'] ?? '' ) );
		$email            = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
		$phone            = sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) );
		$organisation     = sanitize_text_field( wp_unslash( $_POST['organisation'] ?? '' ) );
		$participant_type = sanitize_text_field( wp_unslash( $_POST['participant_type'] ?? '' ) );
		$payment_method   = in_array( $_POST['payment_method'] ?? '', array( 'stripe', 'bank_transfer' ), true ) ? sanitize_text_field( wp_unslash( $_POST['payment_method'] ) ) : 'bank_transfer';
		$discount_code    = strtoupper( sanitize_text_field( wp_unslash( $_POST['discount_code'] ?? '' ) ) );

		// Validate required fields.
		if ( ! $first_name || ! $last_name || ! is_email( $email ) || ! $course_date_id ) {
			return array( 'error' => __( 'Please fill in all required fields.', 'fc-courses' ) );
		}

		// Validate dynamically required fields (phone, organisation, participant_type).
		$fields = self::get_form_fields();
		if ( '1' === $fields['phone']['required'] && '1' === $fields['phone']['enabled'] && ! $phone ) {
			return array( 'error' => __( 'Please fill in all required fields.', 'fc-courses' ) );
		}
		if ( '1' === $fields['organisation']['required'] && '1' === $fields['organisation']['enabled'] && ! $organisation ) {
			return array( 'error' => __( 'Please fill in all required fields.', 'fc-courses' ) );
		}
		if ( '1' === $fields['participant_type']['required'] && '1' === $fields['participant_type']['enabled'] && ! $participant_type ) {
			return array( 'error' => __( 'Please fill in all required fields.', 'fc-courses' ) );
		}

		// Validate participant type is one of the allowed values (if field is enabled).
		if ( '1' === $fields['participant_type']['enabled'] && $participant_type ) {
			$allowed_types = self::get_participant_types();
			if ( ! in_array( $participant_type, $allowed_types, true ) ) {
				return array( 'error' => __( 'Please select a valid participant type.', 'fc-courses' ) );
			}
		}

		// Load course date.
		$course_date = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}fc_course_dates WHERE id = %d AND status = 'open'", $course_date_id ) );
		if ( ! $course_date ) {
			return array( 'error' => __( 'Sorry, that course date is no longer available.', 'fc-courses' ) );
		}

		// Check capacity.
		$enrolment_count = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}fc_enrollments WHERE course_date_id = %d", $course_date_id ) );
		if ( $course_date->max_enrolees > 0 && $enrolment_count >= $course_date->max_enrolees ) {
			return array( 'error' => __( 'Sorry, this course date is full.', 'fc-courses' ) );
		}

		// Prevent duplicate registrations.
		$existing = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}fc_enrollments WHERE course_date_id = %d AND email = %s", $course_date_id, $email ) );
		if ( $existing ) {
			return array( 'error' => __( 'You are already registered for this course date.', 'fc-courses' ) );
		}

		// Load course.
		$course = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}fc_courses WHERE id = %d", $course_date->course_id ) );
		if ( ! $course ) {
			return array( 'error' => __( 'Course not found.', 'fc-courses' ) );
		}

		// Resolve discount code.
		$discount_code_id = null;
		$amount           = (float) $course->price;
		if ( $discount_code ) {
			$code_row = $wpdb->get_row( $wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}fc_discount_codes WHERE code = %s AND status = 'active' AND (expires_at IS NULL OR expires_at > NOW()) AND (max_uses = 0 OR uses_count < max_uses) AND (course_id IS NULL OR course_id = %d)",
				$discount_code,
				$course->id
			) );
			if ( $code_row ) {
				$discount_code_id = $code_row->id;
				if ( 'percentage' === $code_row->discount_type ) {
					$amount = $amount * ( 1 - min( (float) $code_row->discount_value, 100 ) / 100 );
				} else {
					$amount = max( 0, $amount - (float) $code_row->discount_value );
				}
				// Increment usage count.
				$wpdb->update( $wpdb->prefix . 'fc_discount_codes', array( 'uses_count' => $code_row->uses_count + 1 ), array( 'id' => $code_row->id ), array( '%d' ), array( '%d' ) );
			} else {
				return array( 'error' => __( 'Invalid or expired discount code.', 'fc-courses' ) );
			}
		}

		$amount = round( $amount, 2 );

		// Create enrollment.
		$wpdb->insert(
			$wpdb->prefix . 'fc_enrollments',
			array(
				'course_date_id'   => $course_date_id,
				'course_id'        => $course->id,
				'first_name'       => $first_name,
				'last_name'        => $last_name,
				'email'            => $email,
				'phone'            => $phone,
				'organisation'     => $organisation,
				'participant_type' => $participant_type,
				'payment_method'   => $payment_method,
				'payment_status'   => 'pending',
				'discount_code_id' => $discount_code_id,
				'amount_paid'      => $amount,
			),
			array( '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%f' )
		);
		$enrollment_id = $wpdb->insert_id;

		// Handle payment routing.
		if ( 'stripe' === $payment_method && $amount > 0 ) {
			$payments   = new FC_Courses_Payments();
			// Use the current page as the cancel URL (user returns here if they cancel payment).
			$cancel_url = get_permalink( get_queried_object_id() ) ?: home_url( '/' );
			$checkout_url = $payments->create_stripe_checkout( $enrollment_id, $course, $amount, $cancel_url );
			if ( $checkout_url ) {
				wp_safe_redirect( $checkout_url );
				exit;
			}
			return array( 'error' => __( 'Could not create a Stripe checkout session. Please try again or choose bank transfer.', 'fc-courses' ) );
		}

		// Bank transfer or free course – send confirmation email.
		$this->send_confirmation_email( $enrollment_id, $course, $course_date, $amount, $payment_method );

		$message = 'bank_transfer' === $payment_method
			? __( 'Thank you! Your registration has been received. Please transfer the payment using the bank details in your confirmation email.', 'fc-courses' )
			: __( 'Thank you! Your registration has been received.', 'fc-courses' );

		return array( 'message' => $message );
	}

	/**
	 * Send a confirmation email to the enrollee and the admin.
	 *
	 * @param int    $enrollment_id Enrollment row ID.
	 * @param object $course        Course row.
	 * @param object $course_date   Course date row.
	 * @param float  $amount        Amount due.
	 * @param string $payment_method stripe|bank_transfer.
	 */
	private function send_confirmation_email( $enrollment_id, $course, $course_date, $amount, $payment_method ) {
		global $wpdb;
		$enrollment = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}fc_enrollments WHERE id = %d", $enrollment_id ) );
		if ( ! $enrollment ) {
			return;
		}

		$from_name  = get_option( 'fc_from_name', get_bloginfo( 'name' ) );
		$from_email = get_option( 'fc_from_email', get_option( 'admin_email' ) );
		$admin_email = get_option( 'fc_admin_email', get_option( 'admin_email' ) );
		$headers    = array(
			'Content-Type: text/html; charset=UTF-8',
			"From: {$from_name} <{$from_email}>",
		);

		$bank_html = '';
		if ( 'bank_transfer' === $payment_method && $amount > 0 ) {
			$bank_name    = get_option( 'fc_bank_name', '' );
			$sort_code    = get_option( 'fc_bank_sort_code', '' );
			$account_no   = get_option( 'fc_bank_account_number', '' );
			$account_name = get_option( 'fc_bank_account_name', '' );
			$iban         = get_option( 'fc_bank_iban', '' );

			$bank_html = '<h3>' . esc_html__( 'Bank Transfer Details', 'fc-courses' ) . '</h3><ul>';
			if ( $bank_name ) {
				$bank_html .= '<li>' . esc_html__( 'Bank:', 'fc-courses' ) . ' ' . esc_html( $bank_name ) . '</li>';
			}
			if ( $account_name ) {
				$bank_html .= '<li>' . esc_html__( 'Account Name:', 'fc-courses' ) . ' ' . esc_html( $account_name ) . '</li>';
			}
			if ( $sort_code ) {
				$bank_html .= '<li>' . esc_html__( 'Sort Code:', 'fc-courses' ) . ' ' . esc_html( $sort_code ) . '</li>';
			}
			if ( $account_no ) {
				$bank_html .= '<li>' . esc_html__( 'Account Number:', 'fc-courses' ) . ' ' . esc_html( $account_no ) . '</li>';
			}
			if ( $iban ) {
				$bank_html .= '<li>' . esc_html__( 'IBAN:', 'fc-courses' ) . ' ' . esc_html( $iban ) . '</li>';
			}
			$bank_html .= '</ul>';
			$bank_html .= '<p>' . sprintf(
				/* translators: reference number */
				esc_html__( 'Please use reference: FC-%s', 'fc-courses' ),
				esc_html( $enrollment_id )
			) . '</p>';
		}

		$body  = '<p>' . sprintf( esc_html__( 'Dear %s,', 'fc-courses' ), esc_html( $enrollment->first_name ) ) . '</p>';
		$body .= '<p>' . sprintf( esc_html__( 'Thank you for registering for <strong>%s</strong>.', 'fc-courses' ), esc_html( $course->title ) ) . '</p>';
		$body .= '<p>' . esc_html__( 'Course date:', 'fc-courses' ) . ' ' . esc_html( wp_date( get_option( 'date_format' ), strtotime( $course_date->start_date ) ) ) . '</p>';
		if ( $amount > 0 ) {
			$currency_symbol = self::currency_symbol( $course->currency );
			$body           .= '<p>' . sprintf( esc_html__( 'Amount due: %s%s', 'fc-courses' ), esc_html( $currency_symbol ), esc_html( number_format( $amount, 2 ) ) ) . '</p>';
		}
		$body .= $bank_html;
		$body .= '<p>' . esc_html__( 'We look forward to seeing you!', 'fc-courses' ) . '</p>';

		wp_mail( $enrollment->email, sprintf( __( 'Registration Confirmation: %s', 'fc-courses' ), $course->title ), $body, $headers );

		// Admin notification.
		$admin_body  = '<p>' . sprintf( esc_html__( 'New enrolment for <strong>%s</strong>.', 'fc-courses' ), esc_html( $course->title ) ) . '</p>';
		$admin_body .= '<ul>';
		$admin_body .= '<li>' . esc_html__( 'Name:', 'fc-courses' ) . ' ' . esc_html( $enrollment->first_name . ' ' . $enrollment->last_name ) . '</li>';
		$admin_body .= '<li>' . esc_html__( 'Email:', 'fc-courses' ) . ' ' . esc_html( $enrollment->email ) . '</li>';
		$admin_body .= '<li>' . esc_html__( 'Payment method:', 'fc-courses' ) . ' ' . esc_html( $payment_method ) . '</li>';
		$admin_body .= '<li>' . esc_html__( 'Amount:', 'fc-courses' ) . ' ' . esc_html( number_format( $amount, 2 ) ) . '</li>';
		$admin_body .= '</ul>';
		$admin_body .= '<p><a href="' . esc_url( admin_url( 'admin.php?page=fc-courses' ) ) . '">' . esc_html__( 'View Enrolments', 'fc-courses' ) . '</a></p>';

		wp_mail( $admin_email, sprintf( __( 'New Enrolment: %s', 'fc-courses' ), $course->title ), $admin_body, $headers );
	}
}

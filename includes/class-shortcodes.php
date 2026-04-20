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
 * [fc_expression_of_interest]            – FC participant expression of interest (simplified)
 * [fc_full_enrolment]                    – FC participant full enrolment form (code-gated)
 * [fc_leader_eoi]                        – Leaders Training expression of interest
 * [fc_leader_enrolment]                  – Leaders Training full enrolment form (code-gated)
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
	 * Return the default approval email body (used as fallback in settings).
	 *
	 * @return string
	 */
	public static function get_default_approval_email() {
		return __( "Dear {name},\n\nWe are delighted to let you know that your expression of interest in the Family Connections course has been approved.\n\nYour unique enrolment code is: {code}\n\nPlease visit the enrolment form to complete your enrolment: {full_form_url}\n\nWe look forward to welcoming you to the programme.\n\nWarm regards,\nThe Family Connections Team", 'fc-courses' );
	}

	/**
	 * Return the default rejection email body (used as fallback in settings).
	 *
	 * @return string
	 */
	public static function get_default_rejection_email() {
		return __( "Dear {name},\n\nThank you for your interest in the Family Connections course.\n\nUnfortunately, we are unable to approve your application at this time. This may be because the course is currently full, or because we feel another programme may be better suited to your needs.\n\nWe encourage you to reapply in the future or contact us if you would like to discuss your options further.\n\nWarm regards,\nThe Family Connections Team", 'fc-courses' );
	}

	/**
	 * Return the default leader approval email body (used as fallback in settings).
	 *
	 * @return string
	 */
	public static function get_default_leader_approval_email() {
		return __( "Dear {name},\n\nWe are delighted to let you know that your Leaders Training expression of interest has been approved.\n\nYour unique enrolment code is: {code}\n\nPlease visit the Leaders Training enrolment form to complete your enrolment: {full_form_url}\n\nWe look forward to welcoming you to the programme.\n\nWarm regards,\nThe Family Connections Team", 'fc-courses' );
	}

	/**
	 * Return the default leader rejection email body (used as fallback in settings).
	 *
	 * @return string
	 */
	public static function get_default_leader_rejection_email() {
		return __( "Dear {name},\n\nThank you for your interest in the Family Connections Leaders Training.\n\nUnfortunately, we are unable to approve your application at this time.\n\nWe encourage you to contact us if you would like to discuss your options further.\n\nWarm regards,\nThe Family Connections Team", 'fc-courses' );
	}

	/**
	 * Return the default Family Connections™ Leader Code of Conduct text.
	 *
	 * @return string
	 */
	public static function get_default_leader_coc() {
		return "Family Connections™ Leader Code of Conduct\n\nFamily Connections™ is offered by the BPD Alliance (previously National Education Alliance for Borderline Personality Disorder (NEA BPD)) and is built around principles of mutual trust and respect among participants and leaders. As representatives of Family Connections™ (NEA BPD) NZ Inc, Leaders are held to standards of conduct during the provision of its Family Connections™ course.\n\nWhat we ask of you as a Family Connections™ Leader:\n\n• Provide a safe and respectful environment. Respect participant's cultural, political and religious differences. Please refrain from promoting your own personal, political or spiritual beliefs.\n• If you have someone in your course with whom you have a personal relationship, please work with your leader liaison to make sure that the relationship does not cause discomfort or conflict in class.\n• Respect participant privacy by creating an environment of confidentiality and hold sensitive, private and personal information in confidence.\n• If your personal situation changes such that your ability to lead Family Connections™ course is compromised, please reach out to your leader liaison to discuss a substitute or replacement as necessary.\n• Be prepared to encourage participants to get immediate help when there is a danger of harm to a participant or others.\n• Recognize that your actions and behaviours reflect on the Family Connections™ programme and impact the public perception of Family Connections™ (NEA BPD) NZ Inc as an organisation.\n• Lead with a co-leader. Each course has a minimum of two (2) leaders who have been trained by NEA BPD sanctioned Leader Trainers.\n• It is important that the co-leader relationship be an equal partnership. Although clinicians typically have significant subject matter expertise, peer validation of the family member is the foundation of the Family Connections™ model.\n• Present the Family Connections™ curriculum in its entirety, with no additions or deletions unless approved by Family Connections™ (NEA BPD) NZ Inc.\n• Leaders are not expected to and are discouraged from providing one to one support or guidance to participants except during scheduled class times when co-leader is present.\n• Leaders are asked not to endorse/promote individuals, groups or businesses in which they have a personal or financial interest or promote any non-Family Connections™ (NEA BPD) NZ Inc fundraising activity to Family Connections™ participants.\n\nI have read the above information and I agree to abide by the rules as explained in this document.";
	}

	/**
	 * Return the Leader Code of Conduct text (admin-configured or default).
	 *
	 * @return string
	 */
	public static function get_leader_coc() {
		$saved = get_option( 'fc_leader_coc', '' );
		return '' !== trim( $saved ) ? $saved : self::get_default_leader_coc();
	}

	/**
	 * Register hooks.
	 */
	public function init() {
		add_shortcode( 'fc_course_registration', array( $this, 'registration_form' ) );
		add_shortcode( 'fc_course_list', array( $this, 'course_list' ) );
		add_shortcode( 'fc_course_calendar', array( $this, 'course_calendar' ) );
		add_shortcode( 'fc_expression_of_interest', array( $this, 'expression_of_interest' ) );
		add_shortcode( 'fc_full_enrolment', array( $this, 'full_enrolment' ) );
		add_shortcode( 'fc_leader_eoi', array( $this, 'leader_eoi' ) );
		add_shortcode( 'fc_leader_enrolment', array( $this, 'leader_enrolment' ) );
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
	 * Return the list of ethnicity options configured in settings.
	 *
	 * @return string[] Array of option strings.
	 */
	public static function get_ethnicity_options() {
		$saved = get_option( 'fc_ethnicity_options', '' );
		if ( ! is_string( $saved ) || '' === trim( $saved ) ) {
			return array(
				__( 'NZ European / Pākehā', 'fc-courses' ),
				__( 'Māori', 'fc-courses' ),
				__( 'Samoan', 'fc-courses' ),
				__( 'Tongan', 'fc-courses' ),
				__( 'Cook Island Māori', 'fc-courses' ),
				__( 'Niuean', 'fc-courses' ),
				__( 'Fijian', 'fc-courses' ),
				__( 'Indian', 'fc-courses' ),
				__( 'Chinese', 'fc-courses' ),
				__( 'Other Asian', 'fc-courses' ),
				__( 'Other Pacific peoples', 'fc-courses' ),
				__( 'Middle Eastern / Latin American / African', 'fc-courses' ),
				__( 'Other ethnicity', 'fc-courses' ),
			);
		}
		$options = array_filter( array_map( 'trim', explode( "\n", $saved ) ) );
		return array_values( $options );
	}

	/**
	 * Return the Code of Conduct text configured in settings.
	 *
	 * @return string
	 */
	public static function get_code_of_conduct() {
		$saved = get_option( 'fc_code_of_conduct', '' );
		if ( '' !== trim( $saved ) ) {
			return $saved;
		}
		return "Family Connections™ Participant Code of Conduct\n\nFamily Connections™ is taught in a confidential, supportive environment conducive to learning skills to support your relationship with a person with BPD or its symptoms, including emotion dysregulation. Our code of conduct is the standard through which we honour and respect the needs of our participants and co-leaders. To enrol in a Family Connections™ course, we ask that you agree to abide by our code of conduct.\n\nWhat we ask of you as a Family Connections™ program participant:\n\n• Maintain the confidentiality of all participants and leaders by not discussing their personal information and situations outside the program. If the course is being delivered over telehealth please don't allow people who are not registered participants to listen in to the session. Everyone shares personal histories and issues concerning themselves, their families and the person with BPD they love. No recordings are permitted.\n• Plan to attend all 12 classes since each class builds on the previous one. Weekly engagement also increases trust and reinforces skills.\n• Respect the experiences and feelings of each participant and honour their time to share.\n• Respect each others' cultural, political and religious differences.\n• Provide an atmosphere of open-mindedness, support and non-judgment so that others will feel comfortable sharing. All of us have different experiences with people who suffer from BPD.\n• Refrain from any endorsement or promotion of individuals, groups, or businesses in which you have a personal or financial interest in as this is not allowed.\n\nBy checking this box, I certify I have read the above Family Connections™ Participant Code of Conduct and agree to abide by the rules as explained in this document when I enrol in a course.";
	}

	// ------------------------------------------------------------------
	// [fc_expression_of_interest]
	// ------------------------------------------------------------------

	/**
	 * Render the expression of interest form for the Family Connections course.
	 *
	 * @param array $atts Shortcode attributes (unused).
	 * @return string HTML output.
	 */
	public function expression_of_interest( $atts ) {
		$form_message = '';
		$form_error   = '';

		if ( isset( $_POST['fc_eoi_nonce'] ) ) {
			$result       = $this->process_expression_of_interest();
			$form_message = $result['message'] ?? '';
			$form_error   = $result['error'] ?? '';
		}

		ob_start();
		include FC_COURSES_PLUGIN_DIR . 'public/views/expression-of-interest.php';
		return ob_get_clean();
	}

	/**
	 * Process the submitted expression of interest form.
	 *
	 * @return array Keys: 'message' (success) or 'error'.
	 */
	private function process_expression_of_interest() {
		if ( ! isset( $_POST['fc_eoi_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['fc_eoi_nonce'] ), 'fc_expression_of_interest' ) ) {
			return array( 'error' => __( 'Security check failed. Please try again.', 'fc-courses' ) );
		}

		$full_name             = sanitize_text_field( wp_unslash( $_POST['full_name'] ?? '' ) );
		$town_region           = sanitize_text_field( wp_unslash( $_POST['town_region'] ?? '' ) );
		$phone                 = sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) );
		$email                 = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
		$mental_health_current = sanitize_text_field( wp_unslash( $_POST['mental_health_current'] ?? '' ) );
		$mental_health_past    = sanitize_text_field( wp_unslash( $_POST['mental_health_past'] ?? '' ) );
		$loved_one_age         = sanitize_text_field( wp_unslash( $_POST['loved_one_age'] ?? '' ) );

		// Validate required fields.
		if ( ! $full_name || ! $town_region || ! $phone || ! is_email( $email ) || ! $loved_one_age ) {
			return array( 'error' => __( 'Please fill in all required fields.', 'fc-courses' ) );
		}

		$allowed_answers = array( 'yes', 'no', 'unsure' );
		if ( ! in_array( $mental_health_current, $allowed_answers, true ) ) {
			return array( 'error' => __( 'Please answer all required questions.', 'fc-courses' ) );
		}

		// "Have they ever been?" is only required when not currently under a service.
		if ( 'yes' !== $mental_health_current && ! in_array( $mental_health_past, $allowed_answers, true ) ) {
			return array( 'error' => __( 'Please answer all required questions.', 'fc-courses' ) );
		}

		// If currently under a service, past is not applicable.
		if ( 'yes' === $mental_health_current ) {
			$mental_health_past = '';
		}

		global $wpdb;

		$wpdb->insert(
			$wpdb->prefix . 'fc_applicants',
			array(
				'full_name'             => $full_name,
				'town_region'           => $town_region,
				'phone'                 => $phone,
				'email'                 => $email,
				'mental_health_current' => $mental_health_current,
				'mental_health_past'    => $mental_health_past,
				'loved_one_age'         => $loved_one_age,
				'status'                => 'pending',
			),
			array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		// Notify admin.
		$from_name   = get_option( 'fc_from_name', get_bloginfo( 'name' ) );
		$from_email  = get_option( 'fc_from_email', get_option( 'admin_email' ) );
		$admin_email = get_option( 'fc_admin_email', get_option( 'admin_email' ) );
		$headers     = array(
			'Content-Type: text/html; charset=UTF-8',
			"From: {$from_name} <{$from_email}>",
		);

		$mh_labels = array( 'yes' => 'Yes', 'no' => 'No', 'unsure' => 'Unsure', '' => '—' );

		$admin_body  = '<p>' . esc_html__( 'A new expression of interest has been submitted for the Family Connections course.', 'fc-courses' ) . '</p>';
		$admin_body .= '<ul>';
		$admin_body .= '<li><strong>' . esc_html__( 'Name:', 'fc-courses' ) . '</strong> ' . esc_html( $full_name ) . '</li>';
		$admin_body .= '<li><strong>' . esc_html__( 'Email:', 'fc-courses' ) . '</strong> ' . esc_html( $email ) . '</li>';
		$admin_body .= '<li><strong>' . esc_html__( 'Phone:', 'fc-courses' ) . '</strong> ' . esc_html( $phone ) . '</li>';
		$admin_body .= '<li><strong>' . esc_html__( 'Town/Region:', 'fc-courses' ) . '</strong> ' . esc_html( $town_region ) . '</li>';
		$admin_body .= '<li><strong>' . esc_html__( 'Age of loved one:', 'fc-courses' ) . '</strong> ' . esc_html( $loved_one_age ) . '</li>';
		$admin_body .= '<li><strong>' . esc_html__( 'Currently under public mental health service:', 'fc-courses' ) . '</strong> ' . esc_html( $mh_labels[ $mental_health_current ] ?? $mental_health_current ) . '</li>';
		if ( '' !== $mental_health_past ) {
			$admin_body .= '<li><strong>' . esc_html__( 'Ever been under public mental health service:', 'fc-courses' ) . '</strong> ' . esc_html( $mh_labels[ $mental_health_past ] ?? $mental_health_past ) . '</li>';
		}
		$admin_body .= '</ul>';
		$admin_body .= '<p><a href="' . esc_url( admin_url( 'admin.php?page=fc-courses-applicants' ) ) . '">' . esc_html__( 'View Applicants', 'fc-courses' ) . '</a></p>';

		wp_mail( $admin_email, __( 'New Expression of Interest: Family Connections', 'fc-courses' ), $admin_body, $headers );

		return array( 'message' => __( 'Thank you for your expression of interest! We will review your application and be in touch soon.', 'fc-courses' ) );
	}

	// ------------------------------------------------------------------
	// [fc_full_enrolment]
	// ------------------------------------------------------------------

	/**
	 * Render the code-gated full enrolment form for the Family Connections course.
	 *
	 * Step 1 – visitor enters their approval code.
	 * Step 2 – form pre-filled from EOI data; collects relationship, ethnicity, CoC.
	 *
	 * @param array $atts Shortcode attributes (unused).
	 * @return string HTML output.
	 */
	public function full_enrolment( $atts ) {
		$form_message      = '';
		$form_error        = '';
		$applicant         = null;
		$step              = 'code';
		$code              = '';
		$ethnicity_options = self::get_ethnicity_options();
		$relationship_options = array(
			'child'                => __( 'Child', 'fc-courses' ),
			'romantic_partner'     => __( 'Romantic partner', 'fc-courses' ),
			'ex_partner_co_parent' => __( 'Ex-partner / co-parent', 'fc-courses' ),
			'sibling'              => __( 'Sibling', 'fc-courses' ),
			'parent'               => __( 'Parent', 'fc-courses' ),
			'friend'               => __( 'Friend', 'fc-courses' ),
			'other'                => __( 'Other', 'fc-courses' ),
		);
		$code_of_conduct = self::get_code_of_conduct();

		if ( isset( $_POST['fc_full_code_nonce'] ) ) {
			if ( ! wp_verify_nonce( sanitize_key( $_POST['fc_full_code_nonce'] ), 'fc_full_enrolment_code' ) ) {
				$form_error = __( 'Security check failed. Please try again.', 'fc-courses' );
			} else {
				$code      = strtoupper( sanitize_text_field( wp_unslash( $_POST['fc_approval_code'] ?? '' ) ) );
				$applicant = $this->get_fc_applicant_by_code( $code );
				if ( ! $applicant ) {
					$form_error = __( 'Invalid code. Please check your email and try again.', 'fc-courses' );
				} elseif ( 'approved' !== $applicant->status ) {
					$form_error = __( 'This code has already been used or is no longer valid.', 'fc-courses' );
				} else {
					$step = 'form';
				}
			}
		} elseif ( isset( $_POST['fc_full_nonce'] ) ) {
			$result       = $this->process_full_enrolment();
			$form_message = $result['message'] ?? '';
			$form_error   = $result['error'] ?? '';
			if ( $form_error ) {
				$code      = strtoupper( sanitize_text_field( wp_unslash( $_POST['fc_approval_code'] ?? '' ) ) );
				$applicant = $this->get_fc_applicant_by_code( $code );
				if ( $applicant && 'approved' === $applicant->status ) {
					$step = 'form';
				}
			}
		}

		ob_start();
		include FC_COURSES_PLUGIN_DIR . 'public/views/full-enrolment.php';
		return ob_get_clean();
	}

	/**
	 * Process the full enrolment form submission.
	 *
	 * @return array Keys: 'message' (success) or 'error'.
	 */
	private function process_full_enrolment() {
		if ( ! isset( $_POST['fc_full_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['fc_full_nonce'] ), 'fc_full_enrolment' ) ) {
			return array( 'error' => __( 'Security check failed. Please try again.', 'fc-courses' ) );
		}

		$code      = strtoupper( sanitize_text_field( wp_unslash( $_POST['fc_approval_code'] ?? '' ) ) );
		$applicant = $this->get_fc_applicant_by_code( $code );

		if ( ! $applicant ) {
			return array( 'error' => __( 'Invalid code. Please contact us for assistance.', 'fc-courses' ) );
		}
		if ( 'approved' !== $applicant->status ) {
			return array( 'error' => __( 'This code has already been used or is no longer valid.', 'fc-courses' ) );
		}

		$full_name             = sanitize_text_field( wp_unslash( $_POST['full_name'] ?? '' ) );
		$town_region           = sanitize_text_field( wp_unslash( $_POST['town_region'] ?? '' ) );
		$phone                 = sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) );
		$email                 = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
		$loved_one_age         = isset( $_POST['loved_one_age'] ) ? absint( $_POST['loved_one_age'] ) : 0;
		$mental_health_current = sanitize_text_field( wp_unslash( $_POST['mental_health_current'] ?? '' ) );
		$mental_health_past    = sanitize_text_field( wp_unslash( $_POST['mental_health_past'] ?? '' ) );
		$relationship          = sanitize_text_field( wp_unslash( $_POST['relationship'] ?? '' ) );
		$ethnicity             = isset( $_POST['ethnicity'] ) && is_array( $_POST['ethnicity'] )
			? array_map( 'sanitize_text_field', array_map( 'wp_unslash', $_POST['ethnicity'] ) )
			: array();
		$coc_agreed            = ! empty( $_POST['coc_agreed'] );

		$allowed_mh = array( 'yes', 'no', 'unsure' );

		if ( ! $full_name || ! $town_region || ! $phone || ! is_email( $email ) || ! $relationship ) {
			return array( 'error' => __( 'Please fill in all required fields.', 'fc-courses' ) );
		}

		if ( $loved_one_age <= 0 ) {
			return array( 'error' => __( 'Please enter the age of your loved one.', 'fc-courses' ) );
		}

		if ( ! in_array( $mental_health_current, $allowed_mh, true ) ) {
			return array( 'error' => __( 'Please answer whether your loved one is currently under a public mental health service.', 'fc-courses' ) );
		}

		if ( 'yes' !== $mental_health_current && ! in_array( $mental_health_past, $allowed_mh, true ) ) {
			return array( 'error' => __( 'Please answer whether your loved one has previously been under a public mental health service.', 'fc-courses' ) );
		}

		$allowed_relationships = array( 'child', 'romantic_partner', 'ex_partner_co_parent', 'sibling', 'parent', 'friend', 'other' );
		if ( ! in_array( $relationship, $allowed_relationships, true ) ) {
			return array( 'error' => __( 'Please select a valid relationship.', 'fc-courses' ) );
		}

		if ( ! $coc_agreed ) {
			return array( 'error' => __( 'Please agree to the Participant Code of Conduct to continue.', 'fc-courses' ) );
		}

		$allowed_ethnicities = self::get_ethnicity_options();
		$ethnicity           = array_values( array_filter( $ethnicity, function ( $e ) use ( $allowed_ethnicities ) {
			return in_array( $e, $allowed_ethnicities, true );
		} ) );

		global $wpdb;
		$mh_past_to_save = ( 'yes' === $mental_health_current ) ? '' : $mental_health_past;
		$wpdb->update(
			$wpdb->prefix . 'fc_applicants',
			array(
				'full_name'             => $full_name,
				'town_region'           => $town_region,
				'phone'                 => $phone,
				'email'                 => $email,
				'loved_one_age'         => $loved_one_age,
				'mental_health_current' => $mental_health_current,
				'mental_health_past'    => $mh_past_to_save,
				'relationship'          => $relationship,
				'ethnicity'             => implode( ', ', $ethnicity ),
				'coc_agreed'            => 1,
				'status'                => 'enrolled',
			),
			array( 'id' => $applicant->id ),
			array( '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%d', '%s' ),
			array( '%d' )
		);

		return array( 'message' => __( 'Thank you! Your enrolment has been received. We will be in touch with further details about your course dates.', 'fc-courses' ) );
	}

	/**
	 * Look up an FC applicant by their approval code.
	 *
	 * @param string $code Approval code (uppercased).
	 * @return object|null
	 */
	private function get_fc_applicant_by_code( $code ) {
		if ( ! $code ) {
			return null;
		}
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}fc_applicants WHERE approval_code = %s",
			$code
		) );
	}

	// ------------------------------------------------------------------
	// [fc_leader_eoi]
	// ------------------------------------------------------------------

	/**
	 * Render the expression of interest form for the Leaders Training.
	 *
	 * @param array $atts Shortcode attributes (unused).
	 * @return string HTML output.
	 */
	public function leader_eoi( $atts ) {
		$form_message = '';
		$form_error   = '';

		if ( isset( $_POST['fc_leader_eoi_nonce'] ) ) {
			$result       = $this->process_leader_eoi();
			$form_message = $result['message'] ?? '';
			$form_error   = $result['error'] ?? '';
		}

		ob_start();
		include FC_COURSES_PLUGIN_DIR . 'public/views/leader-eoi.php';
		return ob_get_clean();
	}

	/**
	 * Process the Leaders Training expression of interest form.
	 *
	 * @return array Keys: 'message' (success) or 'error'.
	 */
	private function process_leader_eoi() {
		if ( ! isset( $_POST['fc_leader_eoi_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['fc_leader_eoi_nonce'] ), 'fc_leader_eoi' ) ) {
			return array( 'error' => __( 'Security check failed. Please try again.', 'fc-courses' ) );
		}

		$full_name        = sanitize_text_field( wp_unslash( $_POST['full_name'] ?? '' ) );
		$participant_type = sanitize_text_field( wp_unslash( $_POST['participant_type'] ?? '' ) );
		$town_region      = sanitize_text_field( wp_unslash( $_POST['town_region'] ?? '' ) );
		$phone            = sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) );
		$email            = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );

		// Always-required fields.
		if ( ! $full_name || ! $town_region || ! $phone || ! is_email( $email ) ) {
			return array( 'error' => __( 'Please fill in all required fields.', 'fc-courses' ) );
		}

		$allowed_types = array( 'clinician', 'whanau' );
		if ( ! in_array( $participant_type, $allowed_types, true ) ) {
			return array( 'error' => __( 'Please select whether you are a clinician or whānau member.', 'fc-courses' ) );
		}

		// Clinician-specific fields.
		$profession          = '';
		$place_of_employment = '';
		$management_approval = '';

		if ( 'clinician' === $participant_type ) {
			$profession          = sanitize_text_field( wp_unslash( $_POST['profession'] ?? '' ) );
			$place_of_employment = sanitize_text_field( wp_unslash( $_POST['place_of_employment'] ?? '' ) );
			$management_approval = sanitize_text_field( wp_unslash( $_POST['management_approval'] ?? '' ) );

			if ( ! $profession || ! $place_of_employment ) {
				return array( 'error' => __( 'Please fill in all required clinician fields.', 'fc-courses' ) );
			}
			$allowed_answers = array( 'yes', 'no', 'unsure' );
			if ( ! in_array( $management_approval, $allowed_answers, true ) ) {
				return array( 'error' => __( 'Please indicate management approval.', 'fc-courses' ) );
			}
		}

		// Whānau-specific fields.
		$fc_participation  = '';
		$leader_endorsement = '';

		if ( 'whanau' === $participant_type ) {
			$fc_participation  = sanitize_textarea_field( wp_unslash( $_POST['fc_participation'] ?? '' ) );
			$leader_endorsement = sanitize_text_field( wp_unslash( $_POST['leader_endorsement'] ?? '' ) );
		}

		global $wpdb;
		$wpdb->insert(
			$wpdb->prefix . 'fc_leader_applicants',
			array(
				'full_name'           => $full_name,
				'participant_type'    => $participant_type,
				'profession'          => $profession,
				'place_of_employment' => $place_of_employment,
				'management_approval' => $management_approval,
				'town_region'         => $town_region,
				'phone'               => $phone,
				'email'               => $email,
				'fc_participation'    => $fc_participation,
				'leader_endorsement'  => $leader_endorsement,
				'status'              => 'pending',
			),
			array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		// Notify admin.
		$from_name   = get_option( 'fc_from_name', get_bloginfo( 'name' ) );
		$from_email  = get_option( 'fc_from_email', get_option( 'admin_email' ) );
		$admin_email = get_option( 'fc_admin_email', get_option( 'admin_email' ) );
		$headers     = array(
			'Content-Type: text/html; charset=UTF-8',
			"From: {$from_name} <{$from_email}>",
		);

		$type_label  = 'clinician' === $participant_type ? __( 'Clinician', 'fc-courses' ) : __( 'Whānau member', 'fc-courses' );
		$admin_body  = '<p>' . esc_html__( 'A new expression of interest has been submitted for the Leaders Training.', 'fc-courses' ) . '</p>';
		$admin_body .= '<ul>';
		$admin_body .= '<li><strong>' . esc_html__( 'Name:', 'fc-courses' ) . '</strong> ' . esc_html( $full_name ) . '</li>';
		$admin_body .= '<li><strong>' . esc_html__( 'Type:', 'fc-courses' ) . '</strong> ' . esc_html( $type_label ) . '</li>';
		$admin_body .= '<li><strong>' . esc_html__( 'Email:', 'fc-courses' ) . '</strong> ' . esc_html( $email ) . '</li>';
		$admin_body .= '<li><strong>' . esc_html__( 'Phone:', 'fc-courses' ) . '</strong> ' . esc_html( $phone ) . '</li>';
		$admin_body .= '<li><strong>' . esc_html__( 'Town/Region:', 'fc-courses' ) . '</strong> ' . esc_html( $town_region ) . '</li>';
		if ( 'clinician' === $participant_type ) {
			$admin_body .= '<li><strong>' . esc_html__( 'Profession:', 'fc-courses' ) . '</strong> ' . esc_html( $profession ) . '</li>';
			$admin_body .= '<li><strong>' . esc_html__( 'Place of Employment:', 'fc-courses' ) . '</strong> ' . esc_html( $place_of_employment ) . '</li>';
			$admin_body .= '<li><strong>' . esc_html__( 'Management Approval:', 'fc-courses' ) . '</strong> ' . esc_html( ucfirst( $management_approval ) ) . '</li>';
		}
		if ( 'whanau' === $participant_type ) {
			$admin_body .= '<li><strong>' . esc_html__( 'FC Participation:', 'fc-courses' ) . '</strong> ' . esc_html( $fc_participation ) . '</li>';
			$admin_body .= '<li><strong>' . esc_html__( 'Leader Endorsement:', 'fc-courses' ) . '</strong> ' . esc_html( $leader_endorsement ) . '</li>';
		}
		$admin_body .= '</ul>';
		$admin_body .= '<p><a href="' . esc_url( admin_url( 'admin.php?page=fc-courses-leader-applicants' ) ) . '">' . esc_html__( 'View Leader Applicants', 'fc-courses' ) . '</a></p>';

		wp_mail( $admin_email, __( 'New Expression of Interest: Leaders Training', 'fc-courses' ), $admin_body, $headers );

		return array( 'message' => __( 'Thank you for your expression of interest in the Leaders Training! We will review your application and be in touch soon.', 'fc-courses' ) );
	}

	// ------------------------------------------------------------------
	// [fc_leader_enrolment]
	// ------------------------------------------------------------------

	/**
	 * Render the code-gated full enrolment form for the Leaders Training.
	 *
	 * @param array $atts Shortcode attributes (unused).
	 * @return string HTML output.
	 */
	public function leader_enrolment( $atts ) {
		$form_message      = '';
		$form_error        = '';
		$applicant         = null;
		$step              = 'code';
		$code              = '';
		$ethnicity_options = self::get_ethnicity_options();
		$leader_coc        = self::get_leader_coc();

		if ( isset( $_POST['fc_leader_code_nonce'] ) ) {
			if ( ! wp_verify_nonce( sanitize_key( $_POST['fc_leader_code_nonce'] ), 'fc_leader_enrolment_code' ) ) {
				$form_error = __( 'Security check failed. Please try again.', 'fc-courses' );
			} else {
				$code      = strtoupper( sanitize_text_field( wp_unslash( $_POST['fc_approval_code'] ?? '' ) ) );
				$applicant = $this->get_leader_applicant_by_code( $code );
				if ( ! $applicant ) {
					$form_error = __( 'Invalid code. Please check your email and try again.', 'fc-courses' );
				} elseif ( 'approved' !== $applicant->status ) {
					$form_error = __( 'This code has already been used or is no longer valid.', 'fc-courses' );
				} else {
					$step = 'form';
				}
			}
		} elseif ( isset( $_POST['fc_leader_full_nonce'] ) ) {
			$result       = $this->process_leader_enrolment();
			$form_message = $result['message'] ?? '';
			$form_error   = $result['error'] ?? '';
			if ( $form_error ) {
				$code      = strtoupper( sanitize_text_field( wp_unslash( $_POST['fc_approval_code'] ?? '' ) ) );
				$applicant = $this->get_leader_applicant_by_code( $code );
				if ( $applicant && 'approved' === $applicant->status ) {
					$step = 'form';
				}
			}
		}

		ob_start();
		include FC_COURSES_PLUGIN_DIR . 'public/views/leader-enrolment.php';
		return ob_get_clean();
	}

	/**
	 * Process the Leaders Training full enrolment form submission.
	 *
	 * @return array Keys: 'message' (success) or 'error'.
	 */
	private function process_leader_enrolment() {
		if ( ! isset( $_POST['fc_leader_full_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['fc_leader_full_nonce'] ), 'fc_leader_enrolment' ) ) {
			return array( 'error' => __( 'Security check failed. Please try again.', 'fc-courses' ) );
		}

		$code      = strtoupper( sanitize_text_field( wp_unslash( $_POST['fc_approval_code'] ?? '' ) ) );
		$applicant = $this->get_leader_applicant_by_code( $code );

		if ( ! $applicant ) {
			return array( 'error' => __( 'Invalid code. Please contact us for assistance.', 'fc-courses' ) );
		}
		if ( 'approved' !== $applicant->status ) {
			return array( 'error' => __( 'This code has already been used or is no longer valid.', 'fc-courses' ) );
		}

		$full_name        = sanitize_text_field( wp_unslash( $_POST['full_name'] ?? '' ) );
		$participant_type = $applicant->participant_type; // type is fixed from EOI
		$town_region      = sanitize_text_field( wp_unslash( $_POST['town_region'] ?? '' ) );
		$phone            = sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) );
		$email            = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
		$training_dates   = sanitize_textarea_field( wp_unslash( $_POST['training_dates'] ?? '' ) );
		$payment_method   = sanitize_text_field( wp_unslash( $_POST['payment_method'] ?? '' ) );
		$payment_reference = sanitize_text_field( wp_unslash( $_POST['payment_reference'] ?? '' ) );
		$payment_notes    = sanitize_textarea_field( wp_unslash( $_POST['payment_notes'] ?? '' ) );
		$coc_agreed       = ! empty( $_POST['coc_agreed'] );

		if ( ! $full_name || ! $town_region || ! $phone || ! is_email( $email ) ) {
			return array( 'error' => __( 'Please fill in all required fields.', 'fc-courses' ) );
		}
		if ( ! $coc_agreed ) {
			return array( 'error' => __( 'Please agree to the Leader Code of Conduct to continue.', 'fc-courses' ) );
		}

		// Clinician-specific fields.
		$profession          = '';
		$place_of_employment = '';
		$management_approval = '';
		$dbt_trained         = '';
		$billing_contact     = '';
		$fc_participation    = $applicant->fc_participation;
		$leader_endorsement  = $applicant->leader_endorsement;

		if ( 'clinician' === $participant_type ) {
			$profession          = sanitize_text_field( wp_unslash( $_POST['profession'] ?? '' ) );
			$place_of_employment = sanitize_text_field( wp_unslash( $_POST['place_of_employment'] ?? '' ) );
			$management_approval = sanitize_text_field( wp_unslash( $_POST['management_approval'] ?? '' ) );
			$dbt_trained         = sanitize_text_field( wp_unslash( $_POST['dbt_trained'] ?? '' ) );
			$billing_contact     = sanitize_textarea_field( wp_unslash( $_POST['billing_contact'] ?? '' ) );

			if ( ! $profession || ! $place_of_employment ) {
				return array( 'error' => __( 'Please fill in all required clinician fields.', 'fc-courses' ) );
			}
			$allowed_answers = array( 'yes', 'no', 'unsure' );
			if ( ! in_array( $management_approval, $allowed_answers, true ) ) {
				return array( 'error' => __( 'Please indicate management approval.', 'fc-courses' ) );
			}
		} elseif ( 'whanau' === $participant_type ) {
			$fc_participation   = sanitize_textarea_field( wp_unslash( $_POST['fc_participation'] ?? '' ) );
			$leader_endorsement = sanitize_text_field( wp_unslash( $_POST['leader_endorsement'] ?? '' ) );
		}

		$ethnicity = isset( $_POST['ethnicity'] ) && is_array( $_POST['ethnicity'] )
			? array_map( 'sanitize_text_field', array_map( 'wp_unslash', $_POST['ethnicity'] ) )
			: array();
		$allowed_ethnicities = self::get_ethnicity_options();
		$ethnicity           = array_values( array_filter( $ethnicity, function ( $e ) use ( $allowed_ethnicities ) {
			return in_array( $e, $allowed_ethnicities, true );
		} ) );

		global $wpdb;
		$wpdb->update(
			$wpdb->prefix . 'fc_leader_applicants',
			array(
				'full_name'           => $full_name,
				'profession'          => $profession,
				'place_of_employment' => $place_of_employment,
				'management_approval' => $management_approval,
				'dbt_trained'         => $dbt_trained,
				'billing_contact'     => $billing_contact,
				'town_region'         => $town_region,
				'phone'               => $phone,
				'email'               => $email,
				'fc_participation'    => $fc_participation,
				'leader_endorsement'  => $leader_endorsement,
				'ethnicity'           => implode( ', ', $ethnicity ),
				'training_dates'      => $training_dates,
				'payment_method'      => $payment_method,
				'payment_reference'   => $payment_reference,
				'payment_notes'       => $payment_notes,
				'coc_agreed'          => 1,
				'status'              => 'enrolled',
			),
			array( 'id' => $applicant->id ),
			array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s' ),
			array( '%d' )
		);

		return array( 'message' => __( 'Thank you! Your Leaders Training enrolment has been received. We will be in touch with further details.', 'fc-courses' ) );
	}

	/**
	 * Look up a leader applicant by their approval code.
	 *
	 * @param string $code Approval code (uppercased).
	 * @return object|null
	 */
	private function get_leader_applicant_by_code( $code ) {
		if ( ! $code ) {
			return null;
		}
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}fc_leader_applicants WHERE approval_code = %s",
			$code
		) );
	}

	// ------------------------------------------------------------------
	// Confirmation email (course enrolments)
	// ------------------------------------------------------------------

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

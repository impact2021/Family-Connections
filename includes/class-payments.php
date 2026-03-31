<?php
/**
 * Payment handling: Stripe Checkout and bank transfer.
 *
 * @package FC_Courses
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class FC_Courses_Payments
 *
 * Handles Stripe Checkout session creation and the Stripe webhook.
 * Bank transfer payments are simply recorded as "pending" and confirmed manually.
 */
class FC_Courses_Payments {

	/**
	 * Register hooks.
	 */
	public function init() {
		// Stripe webhook endpoint.
		add_action( 'rest_api_init', array( $this, 'register_webhook_endpoint' ) );
		// Return from Stripe Checkout.
		add_action( 'template_redirect', array( $this, 'handle_stripe_return' ) );
	}

	// ------------------------------------------------------------------
	// REST endpoint for Stripe webhooks
	// ------------------------------------------------------------------

	/**
	 * Register the Stripe webhook REST route.
	 */
	public function register_webhook_endpoint() {
		register_rest_route(
			'fc-courses/v1',
			'/stripe-webhook',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'handle_stripe_webhook' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * Handle Stripe webhook payload.
	 *
	 * @param \WP_REST_Request $request Incoming REST request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function handle_stripe_webhook( $request ) {
		$payload       = $request->get_body();
		$sig_header    = $request->get_header( 'stripe-signature' );
		$webhook_secret = get_option( 'fc_stripe_webhook_secret', '' );

		if ( empty( $webhook_secret ) ) {
			return new WP_REST_Response( array( 'error' => 'Webhook secret not configured.' ), 400 );
		}

		$event = $this->verify_stripe_signature( $payload, $sig_header, $webhook_secret );
		if ( is_wp_error( $event ) ) {
			return new WP_REST_Response( array( 'error' => $event->get_error_message() ), 400 );
		}

		if ( 'checkout.session.completed' === $event['type'] ) {
			$session = $event['data']['object'];
			$this->mark_payment_complete( $session );
		}

		return new WP_REST_Response( array( 'received' => true ), 200 );
	}

	/**
	 * Verify a Stripe webhook signature without the Stripe PHP library.
	 *
	 * @param string $payload       Raw request body.
	 * @param string $sig_header    Value of the Stripe-Signature header.
	 * @param string $secret        Webhook signing secret (whsec_…).
	 * @return array|\WP_Error  Decoded event array or WP_Error.
	 */
	private function verify_stripe_signature( $payload, $sig_header, $secret ) {
		if ( ! $payload || ! $sig_header ) {
			return new WP_Error( 'missing_payload', 'Missing payload or signature.' );
		}

		$parts    = array();
		$elements = explode( ',', $sig_header );
		foreach ( $elements as $element ) {
			$kv = explode( '=', $element, 2 );
			if ( 2 === count( $kv ) ) {
				$parts[ $kv[0] ] = $kv[1];
			}
		}

		if ( empty( $parts['t'] ) || empty( $parts['v1'] ) ) {
			return new WP_Error( 'invalid_signature', 'Invalid Stripe signature header.' );
		}

		$timestamp     = $parts['t'];
		$expected_sig  = hash_hmac( 'sha256', $timestamp . '.' . $payload, $secret );

		if ( ! hash_equals( $expected_sig, $parts['v1'] ) ) {
			return new WP_Error( 'signature_mismatch', 'Stripe signature verification failed.' );
		}

		// Reject replays older than 5 minutes.
		if ( abs( time() - (int) $timestamp ) > 300 ) {
			return new WP_Error( 'replay_attack', 'Stripe webhook timestamp too old.' );
		}

		$event = json_decode( $payload, true );
		if ( ! $event ) {
			return new WP_Error( 'json_decode', 'Could not decode Stripe webhook payload.' );
		}

		return $event;
	}

	/**
	 * Mark a payment as complete after a successful Stripe Checkout.
	 *
	 * @param array $session Stripe Checkout Session object.
	 */
	private function mark_payment_complete( $session ) {
		global $wpdb;
		$session_id = sanitize_text_field( $session['id'] );

		$enrollment = $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}fc_enrollments WHERE stripe_session_id = %s",
			$session_id
		) );

		if ( ! $enrollment ) {
			return;
		}

		$wpdb->update(
			$wpdb->prefix . 'fc_enrollments',
			array( 'payment_status' => 'paid' ),
			array( 'id' => $enrollment->id ),
			array( '%s' ),
			array( '%d' )
		);

		// Update or insert payment record.
		$wpdb->update(
			$wpdb->prefix . 'fc_payments',
			array(
				'status'            => 'paid',
				'stripe_payment_id' => sanitize_text_field( $session['payment_intent'] ?? '' ),
			),
			array( 'stripe_session_id' => $session_id ),
			array( '%s', '%s' ),
			array( '%s' )
		);
	}

	// ------------------------------------------------------------------
	// Stripe return page
	// ------------------------------------------------------------------

	/**
	 * Handle the return from Stripe Checkout (success / cancel).
	 */
	public function handle_stripe_return() {
		if ( ! isset( $_GET['fc_stripe'] ) ) {
			return;
		}

		$action = sanitize_key( $_GET['fc_stripe'] );
		if ( 'success' === $action ) {
			$success_page_id = (int) get_option( 'fc_success_page_id', 0 );
			if ( $success_page_id ) {
				wp_safe_redirect( get_permalink( $success_page_id ) );
				exit;
			}
		}
		// For 'cancel', Stripe has already redirected the user back to the cancel_url
		// that was passed when creating the checkout session — no additional redirect needed.
	}

	// ------------------------------------------------------------------
	// Create Stripe Checkout session (server-side)
	// ------------------------------------------------------------------

	/**
	 * Create a Stripe Checkout session and return the URL.
	 *
	 * @param int    $enrollment_id Enrollment ID.
	 * @param object $course        Course row.
	 * @param float  $amount        Amount in major currency units (e.g. NZD 15.00).
	 * @param string $cancel_url    URL to redirect to if the user cancels. Defaults to home URL.
	 * @return string|false Checkout URL or false on failure.
	 */
	public function create_stripe_checkout( $enrollment_id, $course, $amount, $cancel_url = '' ) {
		$secret_key = get_option( 'fc_stripe_secret_key', '' );
		if ( empty( $secret_key ) ) {
			return false;
		}

		$test_mode = '1' === get_option( 'fc_stripe_test_mode', '0' );
		$currency  = strtolower( get_option( 'fc_currency', 'NZD' ) );

		// Amount must be in smallest currency unit (cents for NZD).
		$amount_cents = (int) round( $amount * 100 );

		$success_page_id = (int) get_option( 'fc_success_page_id', 0 );
		$success_url     = $success_page_id > 0
			? add_query_arg( array( 'fc_stripe' => 'success', 'enrollment_id' => $enrollment_id ), get_permalink( $success_page_id ) )
			: add_query_arg( array( 'fc_stripe' => 'success', 'enrollment_id' => $enrollment_id ), home_url( '/' ) );

		if ( ! $cancel_url ) {
			$cancel_url = home_url( '/' );
		}

		$body = array(
			'payment_method_types' => array( 'card' ),
			'line_items'           => array(
				array(
					'price_data' => array(
						'currency'     => $currency,
						'product_data' => array(
							'name' => $course->title,
						),
						'unit_amount'  => $amount_cents,
					),
					'quantity'   => 1,
				),
			),
			'mode'        => 'payment',
			'success_url' => $success_url,
			'cancel_url'  => $cancel_url,
			'metadata'    => array(
				'enrollment_id' => $enrollment_id,
				'plugin'        => 'fc-courses',
			),
		);

		$response = wp_remote_post(
			'https://api.stripe.com/v1/checkout/sessions',
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $secret_key,
					'Content-Type'  => 'application/x-www-form-urlencoded',
				),
				'body'    => $this->build_form_data( $body ),
				'timeout' => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( empty( $data['id'] ) || empty( $data['url'] ) ) {
			return false;
		}

		// Store session ID on the enrollment.
		global $wpdb;
		$wpdb->update(
			$wpdb->prefix . 'fc_enrollments',
			array( 'stripe_session_id' => sanitize_text_field( $data['id'] ) ),
			array( 'id' => $enrollment_id ),
			array( '%s' ),
			array( '%d' )
		);

		// Insert payment record.
		$wpdb->insert(
			$wpdb->prefix . 'fc_payments',
			array(
				'enrollment_id'    => $enrollment_id,
				'payment_method'   => 'stripe',
				'amount'           => $amount,
				'currency'         => strtoupper( $currency ),
				'status'           => 'pending',
				'stripe_session_id' => sanitize_text_field( $data['id'] ),
			),
			array( '%d', '%s', '%f', '%s', '%s', '%s' )
		);

		return esc_url_raw( $data['url'] );
	}

	/**
	 * Recursively build URL-encoded form data from a nested array.
	 * Stripe's API expects PHP SDK–style nested keys: line_items[0][price_data][currency].
	 *
	 * @param array  $data   Data to encode.
	 * @param string $prefix Key prefix.
	 * @return string URL-encoded string.
	 */
	private function build_form_data( $data, $prefix = '' ) {
		$pairs = array();
		foreach ( $data as $key => $value ) {
			$full_key = $prefix ? $prefix . '[' . $key . ']' : (string) $key;
			if ( is_array( $value ) ) {
				$pairs[] = $this->build_form_data( $value, $full_key );
			} else {
				$pairs[] = rawurlencode( $full_key ) . '=' . rawurlencode( (string) $value );
			}
		}
		return implode( '&', $pairs );
	}

	// ------------------------------------------------------------------
	// AJAX: validate discount code (front-end live check)
	// ------------------------------------------------------------------

	/**
	 * AJAX handler – validate a discount code and return the discounted price.
	 * Called via fc_validate_discount_code action.
	 */
	public static function ajax_validate_discount_code() {
		check_ajax_referer( 'fc_courses_public_nonce', 'nonce' );

		$code      = strtoupper( sanitize_text_field( wp_unslash( $_POST['code'] ?? '' ) ) );
		$course_id = absint( $_POST['course_id'] ?? 0 );
		$price     = (float) ( $_POST['price'] ?? 0 );

		if ( ! $code ) {
			wp_send_json_error( array( 'message' => __( 'Please enter a discount code.', 'fc-courses' ) ) );
		}

		global $wpdb;
		$code_row = $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}fc_discount_codes WHERE code = %s AND status = 'active' AND (expires_at IS NULL OR expires_at > NOW()) AND (max_uses = 0 OR uses_count < max_uses) AND (course_id IS NULL OR course_id = %d)",
			$code,
			$course_id
		) );

		if ( ! $code_row ) {
			wp_send_json_error( array( 'message' => __( 'Invalid or expired discount code.', 'fc-courses' ) ) );
		}

		if ( 'percentage' === $code_row->discount_type ) {
			$discounted = $price * ( 1 - min( (float) $code_row->discount_value, 100 ) / 100 );
			$label      = (int) $code_row->discount_value . '% ' . __( 'discount', 'fc-courses' );
		} else {
			$discounted = max( 0, $price - (float) $code_row->discount_value );
			$label      = FC_Courses_Shortcodes::currency_symbol() . number_format( (float) $code_row->discount_value, 2 ) . ' ' . __( 'discount', 'fc-courses' );
		}

		wp_send_json_success( array(
			'discounted_price' => round( $discounted, 2 ),
			'label'            => $label,
		) );
	}
}

// Register AJAX handlers at global scope so they fire before FC_Courses_Payments::init().
add_action( 'wp_ajax_fc_validate_discount_code', array( 'FC_Courses_Payments', 'ajax_validate_discount_code' ) );
add_action( 'wp_ajax_nopriv_fc_validate_discount_code', array( 'FC_Courses_Payments', 'ajax_validate_discount_code' ) );

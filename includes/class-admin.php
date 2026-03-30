<?php
/**
 * Admin menu, subpages, and all admin AJAX / form handlers.
 *
 * @package FC_Courses
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class FC_Courses_Admin
 */
class FC_Courses_Admin {

	/**
	 * Register hooks.
	 */
	public function init() {
		add_action( 'admin_menu', array( $this, 'register_menus' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );

		// Form handlers.
		add_action( 'admin_post_fc_save_course', array( $this, 'handle_save_course' ) );
		add_action( 'admin_post_fc_delete_course', array( $this, 'handle_delete_course' ) );
		add_action( 'admin_post_fc_save_course_date', array( $this, 'handle_save_course_date' ) );
		add_action( 'admin_post_fc_delete_course_date', array( $this, 'handle_delete_course_date' ) );
		add_action( 'admin_post_fc_save_discount_code', array( $this, 'handle_save_discount_code' ) );
		add_action( 'admin_post_fc_delete_discount_code', array( $this, 'handle_delete_discount_code' ) );
		add_action( 'admin_post_fc_save_settings', array( $this, 'handle_save_settings' ) );
		add_action( 'admin_post_fc_update_enrollment', array( $this, 'handle_update_enrollment' ) );
		add_action( 'admin_post_fc_delete_enrollment', array( $this, 'handle_delete_enrollment' ) );
	}

	// ------------------------------------------------------------------
	// Menu registration
	// ------------------------------------------------------------------

	/**
	 * Register the top-level "FC Courses" menu and all subpages.
	 */
	public function register_menus() {
		$capability = 'manage_options';
		$icon       = 'dashicons-welcome-learn-more';

		add_menu_page(
			__( 'FC Courses', 'fc-courses' ),
			__( 'FC Courses', 'fc-courses' ),
			$capability,
			'fc-courses',
			array( $this, 'page_enrollments' ),
			$icon,
			30
		);

		// Enrollments (same callback as top-level so it shows as the first item).
		add_submenu_page(
			'fc-courses',
			__( 'Enrolments', 'fc-courses' ),
			__( 'Enrolments', 'fc-courses' ),
			$capability,
			'fc-courses',
			array( $this, 'page_enrollments' )
		);

		add_submenu_page(
			'fc-courses',
			__( 'Courses', 'fc-courses' ),
			__( 'Courses', 'fc-courses' ),
			$capability,
			'fc-courses-courses',
			array( $this, 'page_courses' )
		);

		add_submenu_page(
			'fc-courses',
			__( 'Discount Codes', 'fc-courses' ),
			__( 'Discount Codes', 'fc-courses' ),
			$capability,
			'fc-courses-discount-codes',
			array( $this, 'page_discount_codes' )
		);

		add_submenu_page(
			'fc-courses',
			__( 'Settings', 'fc-courses' ),
			__( 'Settings', 'fc-courses' ),
			$capability,
			'fc-courses-settings',
			array( $this, 'page_settings' )
		);

		add_submenu_page(
			'fc-courses',
			__( 'Docs', 'fc-courses' ),
			__( 'Docs', 'fc-courses' ),
			$capability,
			'fc-courses-docs',
			array( $this, 'page_docs' )
		);
	}

	// ------------------------------------------------------------------
	// Assets
	// ------------------------------------------------------------------

	/**
	 * Enqueue admin CSS and JS only on FC Courses pages.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_assets( $hook ) {
		$fc_pages = array(
			'toplevel_page_fc-courses',
			'fc-courses_page_fc-courses-courses',
			'fc-courses_page_fc-courses-discount-codes',
			'fc-courses_page_fc-courses-settings',
			'fc-courses_page_fc-courses-docs',
		);

		if ( ! in_array( $hook, $fc_pages, true ) ) {
			return;
		}

		wp_enqueue_style(
			'fc-courses-admin',
			FC_COURSES_PLUGIN_URL . 'admin/css/admin.css',
			array(),
			FC_COURSES_VERSION
		);

		wp_enqueue_script(
			'fc-courses-admin',
			FC_COURSES_PLUGIN_URL . 'admin/js/admin.js',
			array( 'jquery' ),
			FC_COURSES_VERSION,
			true
		);

		wp_localize_script(
			'fc-courses-admin',
			'fcCoursesAdmin',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'fc_courses_admin_nonce' ),
			)
		);
	}

	// ------------------------------------------------------------------
	// Page renderers
	// ------------------------------------------------------------------

	/**
	 * Render the Enrolments page.
	 */
	public function page_enrollments() {
		$this->render_view( 'page-enrollments' );
	}

	/**
	 * Render the Courses page.
	 */
	public function page_courses() {
		$this->render_view( 'page-courses' );
	}

	/**
	 * Render the Discount Codes page.
	 */
	public function page_discount_codes() {
		$this->render_view( 'page-discount-codes' );
	}

	/**
	 * Render the Settings page.
	 */
	public function page_settings() {
		$this->render_view( 'page-settings' );
	}

	/**
	 * Render the Docs page.
	 */
	public function page_docs() {
		$this->render_view( 'page-docs' );
	}

	/**
	 * Helper: include an admin view file.
	 *
	 * @param string $view View file name (without .php).
	 */
	private function render_view( $view ) {
		$file = FC_COURSES_PLUGIN_DIR . 'admin/views/' . sanitize_file_name( $view ) . '.php';
		if ( file_exists( $file ) ) {
			include $file;
		}
	}

	// ------------------------------------------------------------------
	// Form handlers – Courses
	// ------------------------------------------------------------------

	/**
	 * Handle save (create / update) a course.
	 */
	public function handle_save_course() {
		check_admin_referer( 'fc_save_course' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorised', 'fc-courses' ) );
		}

		global $wpdb;
		$table = $wpdb->prefix . 'fc_courses';

		$id          = isset( $_POST['course_id'] ) ? absint( $_POST['course_id'] ) : 0;
		$title       = sanitize_text_field( wp_unslash( $_POST['title'] ?? '' ) );
		$slug        = sanitize_title( $title );
		$description = wp_kses_post( wp_unslash( $_POST['description'] ?? '' ) );
		$course_type = in_array( $_POST['course_type'] ?? '', array( 'free', 'paid' ), true ) ? sanitize_text_field( wp_unslash( $_POST['course_type'] ) ) : 'free';
		$price       = round( (float) ( $_POST['price'] ?? 0 ), 2 );
		$currency    = strtoupper( sanitize_text_field( wp_unslash( $_POST['currency'] ?? 'GBP' ) ) );
		$max         = absint( $_POST['max_enrolees'] ?? 0 );
		$status      = in_array( $_POST['status'] ?? '', array( 'publish', 'draft' ), true ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : 'publish';

		$data   = compact( 'title', 'slug', 'description', 'course_type', 'price', 'currency', 'status' );
		$data['max_enrolees'] = $max;
		$format = array( '%s', '%s', '%s', '%s', '%f', '%s', '%s', '%d' );

		if ( $id > 0 ) {
			$wpdb->update( $table, $data, array( 'id' => $id ), $format, array( '%d' ) );
		} else {
			$wpdb->insert( $table, $data, $format );
		}

		wp_safe_redirect( admin_url( 'admin.php?page=fc-courses-courses&saved=1' ) );
		exit;
	}

	/**
	 * Handle delete a course.
	 */
	public function handle_delete_course() {
		check_admin_referer( 'fc_delete_course' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorised', 'fc-courses' ) );
		}

		global $wpdb;
		$id = absint( $_GET['course_id'] ?? 0 );
		if ( $id > 0 ) {
			$wpdb->delete( $wpdb->prefix . 'fc_courses', array( 'id' => $id ), array( '%d' ) );
		}

		wp_safe_redirect( admin_url( 'admin.php?page=fc-courses-courses&deleted=1' ) );
		exit;
	}

	// ------------------------------------------------------------------
	// Form handlers – Course dates
	// ------------------------------------------------------------------

	/**
	 * Handle save a course date.
	 */
	public function handle_save_course_date() {
		check_admin_referer( 'fc_save_course_date' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorised', 'fc-courses' ) );
		}

		global $wpdb;
		$table = $wpdb->prefix . 'fc_course_dates';

		$id         = isset( $_POST['date_id'] ) ? absint( $_POST['date_id'] ) : 0;
		$course_id  = absint( $_POST['course_id'] ?? 0 );
		$start_date = sanitize_text_field( wp_unslash( $_POST['start_date'] ?? '' ) );
		$end_date   = sanitize_text_field( wp_unslash( $_POST['end_date'] ?? '' ) );
		$location   = sanitize_text_field( wp_unslash( $_POST['location'] ?? '' ) );
		$is_online  = isset( $_POST['is_online'] ) ? 1 : 0;
		$max        = absint( $_POST['max_enrolees'] ?? 0 );
		$status     = in_array( $_POST['status'] ?? '', array( 'open', 'closed', 'full' ), true ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : 'open';

		$data = array(
			'course_id'    => $course_id,
			'start_date'   => $start_date,
			'end_date'     => $end_date ?: null,
			'location'     => $location,
			'is_online'    => $is_online,
			'max_enrolees' => $max,
			'status'       => $status,
		);
		$fmt  = array( '%d', '%s', '%s', '%s', '%d', '%d', '%s' );

		if ( $id > 0 ) {
			$wpdb->update( $table, $data, array( 'id' => $id ), $fmt, array( '%d' ) );
		} else {
			$wpdb->insert( $table, $data, $fmt );
		}

		wp_safe_redirect( admin_url( 'admin.php?page=fc-courses-courses&course_id=' . $course_id . '&saved=1' ) );
		exit;
	}

	/**
	 * Handle delete a course date.
	 */
	public function handle_delete_course_date() {
		check_admin_referer( 'fc_delete_course_date' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorised', 'fc-courses' ) );
		}

		global $wpdb;
		$id        = absint( $_GET['date_id'] ?? 0 );
		$course_id = absint( $_GET['course_id'] ?? 0 );
		if ( $id > 0 ) {
			$wpdb->delete( $wpdb->prefix . 'fc_course_dates', array( 'id' => $id ), array( '%d' ) );
		}

		wp_safe_redirect( admin_url( 'admin.php?page=fc-courses-courses&course_id=' . $course_id . '&deleted=1' ) );
		exit;
	}

	// ------------------------------------------------------------------
	// Form handlers – Discount codes
	// ------------------------------------------------------------------

	/**
	 * Handle save a discount code.
	 */
	public function handle_save_discount_code() {
		check_admin_referer( 'fc_save_discount_code' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorised', 'fc-courses' ) );
		}

		global $wpdb;
		$table = $wpdb->prefix . 'fc_discount_codes';

		$id             = isset( $_POST['code_id'] ) ? absint( $_POST['code_id'] ) : 0;
		$code           = strtoupper( sanitize_text_field( wp_unslash( $_POST['code'] ?? '' ) ) );
		$description    = sanitize_text_field( wp_unslash( $_POST['description'] ?? '' ) );
		$discount_type  = in_array( $_POST['discount_type'] ?? '', array( 'percentage', 'fixed' ), true ) ? sanitize_text_field( wp_unslash( $_POST['discount_type'] ) ) : 'percentage';
		$discount_value = round( (float) ( $_POST['discount_value'] ?? 0 ), 2 );
		$max_uses       = absint( $_POST['max_uses'] ?? 0 );
		$course_id      = absint( $_POST['course_id'] ?? 0 );
		$expires_at     = sanitize_text_field( wp_unslash( $_POST['expires_at'] ?? '' ) );
		$status         = in_array( $_POST['status'] ?? '', array( 'active', 'inactive' ), true ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : 'active';

		$data = array(
			'code'           => $code,
			'description'    => $description,
			'discount_type'  => $discount_type,
			'discount_value' => $discount_value,
			'max_uses'       => $max_uses,
			'course_id'      => $course_id ?: null,
			'expires_at'     => $expires_at ?: null,
			'status'         => $status,
		);
		$fmt  = array( '%s', '%s', '%s', '%f', '%d', '%d', '%s', '%s' );

		if ( $id > 0 ) {
			$wpdb->update( $table, $data, array( 'id' => $id ), $fmt, array( '%d' ) );
		} else {
			$wpdb->insert( $table, $data, $fmt );
		}

		wp_safe_redirect( admin_url( 'admin.php?page=fc-courses-discount-codes&saved=1' ) );
		exit;
	}

	/**
	 * Handle delete a discount code.
	 */
	public function handle_delete_discount_code() {
		check_admin_referer( 'fc_delete_discount_code' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorised', 'fc-courses' ) );
		}

		global $wpdb;
		$id = absint( $_GET['code_id'] ?? 0 );
		if ( $id > 0 ) {
			$wpdb->delete( $wpdb->prefix . 'fc_discount_codes', array( 'id' => $id ), array( '%d' ) );
		}

		wp_safe_redirect( admin_url( 'admin.php?page=fc-courses-discount-codes&deleted=1' ) );
		exit;
	}

	// ------------------------------------------------------------------
	// Form handlers – Enrollments
	// ------------------------------------------------------------------

	/**
	 * Handle update enrollment (e.g. change payment status).
	 */
	public function handle_update_enrollment() {
		check_admin_referer( 'fc_update_enrollment' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorised', 'fc-courses' ) );
		}

		global $wpdb;
		$id             = absint( $_POST['enrollment_id'] ?? 0 );
		$payment_status = sanitize_text_field( wp_unslash( $_POST['payment_status'] ?? 'pending' ) );
		$notes          = sanitize_textarea_field( wp_unslash( $_POST['notes'] ?? '' ) );

		if ( $id > 0 ) {
			$wpdb->update(
				$wpdb->prefix . 'fc_enrollments',
				array(
					'payment_status' => $payment_status,
					'notes'          => $notes,
				),
				array( 'id' => $id ),
				array( '%s', '%s' ),
				array( '%d' )
			);
		}

		wp_safe_redirect( admin_url( 'admin.php?page=fc-courses&updated=1' ) );
		exit;
	}

	/**
	 * Handle delete enrollment.
	 */
	public function handle_delete_enrollment() {
		check_admin_referer( 'fc_delete_enrollment' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorised', 'fc-courses' ) );
		}

		global $wpdb;
		$id = absint( $_GET['enrollment_id'] ?? 0 );
		if ( $id > 0 ) {
			$wpdb->delete( $wpdb->prefix . 'fc_enrollments', array( 'id' => $id ), array( '%d' ) );
		}

		wp_safe_redirect( admin_url( 'admin.php?page=fc-courses&deleted=1' ) );
		exit;
	}

	// ------------------------------------------------------------------
	// Form handlers – Settings
	// ------------------------------------------------------------------

	/**
	 * Handle save plugin settings.
	 */
	public function handle_save_settings() {
		check_admin_referer( 'fc_save_settings' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorised', 'fc-courses' ) );
		}

		$fields = array(
			'fc_stripe_publishable_key',
			'fc_stripe_secret_key',
			'fc_stripe_webhook_secret',
			'fc_bank_name',
			'fc_bank_sort_code',
			'fc_bank_account_number',
			'fc_bank_account_name',
			'fc_bank_iban',
			'fc_admin_email',
			'fc_from_email',
			'fc_from_name',
			'fc_currency',
			'fc_success_page_id',
			'fc_cancel_page_id',
		);

		foreach ( $fields as $field ) {
			if ( isset( $_POST[ $field ] ) ) {
				update_option( $field, sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) );
			}
		}

		// Checkbox.
		update_option( 'fc_stripe_test_mode', isset( $_POST['fc_stripe_test_mode'] ) ? '1' : '0' );
		update_option( 'fc_enable_bank_transfer', isset( $_POST['fc_enable_bank_transfer'] ) ? '1' : '0' );

		wp_safe_redirect( admin_url( 'admin.php?page=fc-courses-settings&saved=1' ) );
		exit;
	}
}

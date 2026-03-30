<?php
/**
 * Database schema installation and upgrade.
 *
 * @package FC_Courses
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class FC_Courses_Database
 *
 * Manages all custom database tables used by the plugin.
 *
 * Tables
 * ------
 * {prefix}fc_courses          – course definitions
 * {prefix}fc_course_dates     – individual scheduled dates for a course
 * {prefix}fc_enrollments      – who signed up for which date
 * {prefix}fc_discount_codes   – discount / promo codes
 * {prefix}fc_payments         – payment records
 */
class FC_Courses_Database {

	/** Current schema version. Bump when altering tables. */
	const SCHEMA_VERSION = 1;

	/** Option key used to track installed schema version. */
	const OPTION_KEY = 'fc_courses_db_version';

	/**
	 * Constructor – run upgrade check on every request (cheap: compares a single option).
	 */
	public function __construct() {
		if ( (int) get_option( self::OPTION_KEY, 0 ) < self::SCHEMA_VERSION ) {
			self::install();
		}
	}

	/**
	 * Create / upgrade all tables.
	 * Called on activation and when the stored version is out of date.
	 */
	public static function install() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset = $wpdb->get_charset_collate();

		// ------------------------------------------------------------------
		// Courses
		// ------------------------------------------------------------------
		$sql = "CREATE TABLE {$wpdb->prefix}fc_courses (
			id             BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			title          VARCHAR(255)        NOT NULL DEFAULT '',
			slug           VARCHAR(255)        NOT NULL DEFAULT '',
			description    LONGTEXT,
			course_type    VARCHAR(50)         NOT NULL DEFAULT 'free',
			price          DECIMAL(10,2)       NOT NULL DEFAULT 0.00,
			currency       VARCHAR(3)          NOT NULL DEFAULT 'GBP',
			max_enrolees   INT(11)             NOT NULL DEFAULT 0,
			stripe_product_id VARCHAR(100)     DEFAULT NULL,
			status         VARCHAR(20)         NOT NULL DEFAULT 'publish',
			created_at     DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at     DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY slug (slug)
		) $charset;";
		dbDelta( $sql );

		// ------------------------------------------------------------------
		// Course dates
		// ------------------------------------------------------------------
		$sql = "CREATE TABLE {$wpdb->prefix}fc_course_dates (
			id             BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			course_id      BIGINT(20) UNSIGNED NOT NULL,
			start_date     DATETIME            NOT NULL,
			end_date       DATETIME            DEFAULT NULL,
			location       VARCHAR(255)        DEFAULT '',
			is_online      TINYINT(1)          NOT NULL DEFAULT 0,
			max_enrolees   INT(11)             NOT NULL DEFAULT 0,
			status         VARCHAR(20)         NOT NULL DEFAULT 'open',
			created_at     DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY course_id (course_id)
		) $charset;";
		dbDelta( $sql );

		// ------------------------------------------------------------------
		// Enrollments
		// ------------------------------------------------------------------
		$sql = "CREATE TABLE {$wpdb->prefix}fc_enrollments (
			id               BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			course_date_id   BIGINT(20) UNSIGNED NOT NULL,
			course_id        BIGINT(20) UNSIGNED NOT NULL,
			first_name       VARCHAR(100)        NOT NULL DEFAULT '',
			last_name        VARCHAR(100)        NOT NULL DEFAULT '',
			email            VARCHAR(255)        NOT NULL DEFAULT '',
			phone            VARCHAR(30)         DEFAULT '',
			organisation     VARCHAR(255)        DEFAULT '',
			payment_method   VARCHAR(30)         NOT NULL DEFAULT 'bank_transfer',
			payment_status   VARCHAR(30)         NOT NULL DEFAULT 'pending',
			discount_code_id BIGINT(20) UNSIGNED DEFAULT NULL,
			amount_paid      DECIMAL(10,2)       NOT NULL DEFAULT 0.00,
			stripe_session_id VARCHAR(255)       DEFAULT NULL,
			notes            TEXT                DEFAULT NULL,
			enrolled_at      DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at       DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY course_date_id (course_date_id),
			KEY course_id (course_id),
			KEY email (email)
		) $charset;";
		dbDelta( $sql );

		// ------------------------------------------------------------------
		// Discount codes
		// ------------------------------------------------------------------
		$sql = "CREATE TABLE {$wpdb->prefix}fc_discount_codes (
			id               BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			code             VARCHAR(50)         NOT NULL,
			description      VARCHAR(255)        DEFAULT '',
			discount_type    VARCHAR(20)         NOT NULL DEFAULT 'percentage',
			discount_value   DECIMAL(10,2)       NOT NULL DEFAULT 0.00,
			max_uses         INT(11)             NOT NULL DEFAULT 0,
			uses_count       INT(11)             NOT NULL DEFAULT 0,
			course_id        BIGINT(20) UNSIGNED DEFAULT NULL,
			expires_at       DATETIME            DEFAULT NULL,
			status           VARCHAR(20)         NOT NULL DEFAULT 'active',
			created_at       DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY code (code)
		) $charset;";
		dbDelta( $sql );

		// ------------------------------------------------------------------
		// Payments
		// ------------------------------------------------------------------
		$sql = "CREATE TABLE {$wpdb->prefix}fc_payments (
			id                  BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			enrollment_id       BIGINT(20) UNSIGNED NOT NULL,
			payment_method      VARCHAR(30)         NOT NULL DEFAULT 'bank_transfer',
			amount              DECIMAL(10,2)       NOT NULL DEFAULT 0.00,
			currency            VARCHAR(3)          NOT NULL DEFAULT 'GBP',
			status              VARCHAR(30)         NOT NULL DEFAULT 'pending',
			stripe_payment_id   VARCHAR(255)        DEFAULT NULL,
			stripe_session_id   VARCHAR(255)        DEFAULT NULL,
			bank_reference      VARCHAR(100)        DEFAULT NULL,
			notes               TEXT                DEFAULT NULL,
			created_at          DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at          DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY enrollment_id (enrollment_id)
		) $charset;";
		dbDelta( $sql );

		update_option( self::OPTION_KEY, self::SCHEMA_VERSION );

		// Seed default courses if the table was just created.
		self::maybe_seed_courses();
	}

	/**
	 * Insert the two default courses (Family Connections + Train the Trainer)
	 * if no courses exist yet.
	 */
	private static function maybe_seed_courses() {
		global $wpdb;
		$table = $wpdb->prefix . 'fc_courses';

		if ( (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" ) > 0 ) { // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			return;
		}

		$wpdb->insert(
			$table,
			array(
				'title'       => 'Family Connections Course',
				'slug'        => 'family-connections-course',
				'description' => 'The main Family Connections training course.',
				'course_type' => 'paid',
				'price'       => 150.00,
				'currency'    => 'GBP',
				'status'      => 'publish',
			),
			array( '%s', '%s', '%s', '%s', '%f', '%s', '%s' )
		);

		$wpdb->insert(
			$table,
			array(
				'title'       => 'Train the Trainer',
				'slug'        => 'train-the-trainer',
				'description' => 'Train the Trainer course for Family Connections facilitators.',
				'course_type' => 'paid',
				'price'       => 250.00,
				'currency'    => 'GBP',
				'status'      => 'publish',
			),
			array( '%s', '%s', '%s', '%s', '%f', '%s', '%s' )
		);
	}
}

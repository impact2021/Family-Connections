<?php
/**
 * Main plugin orchestrator.
 *
 * @package FC_Courses
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class FC_Courses_Plugin
 *
 * Wires together all sub-components and registers WordPress hooks.
 */
class FC_Courses_Plugin {

	/**
	 * Run the plugin.
	 */
	public function run() {
		$this->init_components();
	}

	/**
	 * Instantiate and initialise every component.
	 */
	private function init_components() {
		// Database.
		new FC_Courses_Database();

		// Admin.
		if ( is_admin() ) {
			$admin = new FC_Courses_Admin();
			$admin->init();
		}

		// Shortcodes (always needed so front-end pages work).
		$shortcodes = new FC_Courses_Shortcodes();
		$shortcodes->init();

		// Payments.
		$payments = new FC_Courses_Payments();
		$payments->init();
	}

	/**
	 * Plugin deactivation callback.
	 */
	public static function deactivate() {
		// Intentionally left empty for now (tables are kept on deactivation).
	}
}

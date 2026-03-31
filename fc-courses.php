<?php
/**
 * Plugin Name:       FC Courses
 * Plugin URI:        https://github.com/impact2021/Family-Connections
 * Description:       Course management for Family Connections — enrolments, payments (Stripe & bank transfer), and discount codes.
 * Version:           1.1.0
 * Requires at least: 5.6
 * Requires PHP:      7.4
 * Author:            Family Connections
 * Author URI:        https://familyconnections.org
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       fc-courses
 * Domain Path:       /languages
 *
 * @package FC_Courses
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin constants.
define( 'FC_COURSES_VERSION', '1.1.0' );
define( 'FC_COURSES_PLUGIN_FILE', __FILE__ );
define( 'FC_COURSES_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'FC_COURSES_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Autoload classes from the includes directory.
spl_autoload_register(
	function ( $class ) {
		$prefix = 'FC_Courses_';
		$len    = strlen( $prefix );
		if ( strncmp( $prefix, $class, $len ) !== 0 ) {
			return;
		}
		$relative = substr( $class, $len );
		$file     = FC_COURSES_PLUGIN_DIR . 'includes/class-' . strtolower( str_replace( '_', '-', $relative ) ) . '.php';
		if ( file_exists( $file ) ) {
			require $file;
		}
	}
);

/**
 * Bootstrap the plugin.
 */
function fc_courses_init() {
	// Load translations.
	load_plugin_textdomain( 'fc-courses', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

	$plugin = new FC_Courses_Plugin();
	$plugin->run();
}
add_action( 'plugins_loaded', 'fc_courses_init' );

// Activation / deactivation hooks.
register_activation_hook( __FILE__, array( 'FC_Courses_Database', 'install' ) );
register_deactivation_hook( __FILE__, array( 'FC_Courses_Plugin', 'deactivate' ) );

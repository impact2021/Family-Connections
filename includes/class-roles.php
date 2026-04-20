<?php
/**
 * User role and capability management.
 *
 * Creates the "National Co-ordinator" role on plugin activation and removes it
 * on deactivation. The custom capability `manage_fc_courses` is used throughout
 * the plugin instead of the built-in `manage_options`, so that users assigned
 * the National Co-ordinator role can manage all FC Courses pages without being
 * full WordPress administrators.
 *
 * Administrators are also granted `manage_fc_courses` automatically on
 * activation so that they retain full access.
 *
 * @package FC_Courses
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class FC_Courses_Roles
 */
class FC_Courses_Roles {

	/**
	 * The custom capability that gates every FC Courses admin action.
	 */
	const CAP = 'manage_fc_courses';

	/**
	 * The slug for the National Co-ordinator role.
	 */
	const ROLE_SLUG = 'fc_national_coordinator';

	/**
	 * Called on plugin activation.
	 * Adds the custom role and grants the capability to Administrators.
	 */
	public static function activate() {
		// Add (or refresh) the National Co-ordinator role.
		remove_role( self::ROLE_SLUG );
		add_role(
			self::ROLE_SLUG,
			__( 'National Co-ordinator', 'fc-courses' ),
			array(
				'read'           => true,  // Required to access wp-admin.
				self::CAP        => true,
			)
		);

		// Grant the capability to Administrators so they keep full access.
		$admin_role = get_role( 'administrator' );
		if ( $admin_role ) {
			$admin_role->add_cap( self::CAP );
		}
	}

	/**
	 * Called on plugin deactivation.
	 * Removes the custom role and revokes the capability from Administrators.
	 *
	 * The capability is revoked from Administrators on deactivation so that
	 * there are no orphaned capabilities left in the database after the plugin
	 * is removed.
	 */
	public static function deactivate() {
		remove_role( self::ROLE_SLUG );

		$admin_role = get_role( 'administrator' );
		if ( $admin_role ) {
			$admin_role->remove_cap( self::CAP );
		}
	}

	/**
	 * Ensure the role and capability exist on every request.
	 * This is a lightweight check (single option lookup) that repairs the setup
	 * if the role was somehow lost without a proper deactivation/reactivation
	 * cycle (e.g. manual database migration).
	 */
	public static function maybe_repair() {
		if ( ! get_role( self::ROLE_SLUG ) ) {
			self::activate();
		}
	}
}

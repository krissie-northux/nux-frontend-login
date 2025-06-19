<?php
/**
 * Members class file.
 *
 * @package NorthUX\FrontendLogin
 */

namespace NorthUX\FrontendLogin;

/**
 * Members class to manage all things members.
 */
 class Members {

 	/**
	 * Base role to use when checking login redirects
	 *
	 * @var string $roles_to_check
	 */
	public static $roles_to_check = array( 'custom_role_1', 'custom_role_2' );

	/**
	 * Landing page for a logged in user if not otherwise on a member page
	 *
	 * @var string $logged_in_landing
	 */
	public static $logged_in_landing = '/';

	/**
	 * Constructor method. Hooks to WordPress.
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'disable_admin_bar' ) );
		add_action( 'admin_init', array( $this, 'disable_dashboard' ) );
		add_filter( 'login_redirect', array( $this, 'login_redirect' ), 10, 3 );
	}

	/**
	 * Disables admin bar for basic roles.
	 *
	 * Hooks to `show_admin_bar` filter.
	 *
	 * @hook action `plugins_loaded`
	 *
	 * @return void
	 */
	public function disable_admin_bar() {
		if ( ! current_user_can( 'manage_options' ) ) {
			add_filter( 'show_admin_bar', '__return_false' );
		}
	}

	/**
	 * Redirect basic roles to homepage instead of dashboard.
	 *
	 * @hook filter `login_redirect`
	 *
	 * @return void
	 */
	public function login_redirect( $redirect_to, $requested_redirect_to, $user ) {

		// If we don't have a logged in user
		if ( is_wp_error( $user ) ) {
			return $redirect_to;
		}

		// If we have a selected member type and are not on the sign in page
		if ( nux_user_has_access( $user, self::$roles_to_check ) && strpos( $requested_redirect_to, 'login' ) == false ) {
			return $requested_redirect_to;

		// If we have a selected member type and are on the login page
		} elseif ( nux_user_has_access( $user, self::$roles_to_check ) && strpos( $requested_redirect_to, 'login' ) !== false ) {
			return site_url( self::$logged_in_landing );

		// If we are logged in but not an admin or a selected member type
		} elseif ( ! current_user_can( 'manage_options' ) ) {
			$redirect_to = site_url();
		}

		return $redirect_to;
	}

	public function disable_dashboard() {
		if ( ! defined( 'DOING_AJAX' ) && ! current_user_can( 'manage_options' ) ) {
			wp_redirect( site_url() );
			exit();
		}
	}

}

if ( ! function_exists( 'get_members_instance' ) ) :

	function get_members_instance() {
		return new Members();
	}

endif;

get_members_instance();

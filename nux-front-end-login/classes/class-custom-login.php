<?php
/**
 * Custom login class file.
 *
 * @package NorthUX\FrontendLogin
 */

namespace NorthUX\FrontendLogin;

/**
 * Class `Custom_Login`
 *
 * Handles custom login form and password reset logic.
 */
class Custom_Login {
	/**
	 * Holds boolean on whether method already hooked to Wordpress.
	 *
	 * @var bool $hooked
	 */
	private static $hooked = false;

	/**
	 * Slug for login page created in WordPress.
	 *
	 * @var string $login_page_slug
	 */
	public static $login_page_slug = 'on-demand-login';

	/**
	 * if there is an error processing set true
	 */
	public static $has_error = false;

	/**
	 * holds an notifications that need to be displayed to the user.
	 *
	 * @var string $login_page_slug
	 */
	public static $user_notifications = array();

	/**
	 * Action for Login form. Used in AJAX call.
	 *
	 * @var string $login_action
	 */
	public static $login_action = 'nux_login';

	/**
	 * Action for Reset Password form. Used in AJAX call.
	 *
	 * @var string $reset_password_action
	 */
	public static $reset_password_action = 'nux_reset_password';

	/**
	 * Action for Forgot Password form. Used in AJAX call.
	 *
	 * @var string $forgot_password_action
	 */
	public static $forgot_password_action = 'nux_forgot_password';

	/**
	 * Minimum password length. Used in validation.
	 *
	 * @var int $password_min_length
	 */
	public static $password_min_length = 6;

	/**
	 * User meta key for storing hash for recovering password.
	 *
	 * @var string $hash_user_meta_key
	 */
	private static $hash_user_meta_key = '_custom_login_rp_hash';

	/**
	 * Init method used to hook to WordPress. Used as a constructor in this case.
	 *
	 * @return void
	 */
	public function init() {
		if ( self::$hooked ) {
			return;
		}

		/**
		 * AJAX actions.
		 */
		add_action( 'wp_ajax_nopriv_' . self::$forgot_password_action, array( $this, 'handle_forgot_password_submit' ) );
		add_action( 'wp_ajax_nopriv_' . self::$reset_password_action, array( $this, 'handle_reset_password_submit' ) );
		add_action( 'wp_ajax_nopriv_' . self::$login_action, array( $this, 'handle_login_submit' ) );

		/**
		 * Password hash validation.
		 */
		add_action( 'parse_request', array( $this, 'handle_password_hash' ) );

		add_shortcode( 'nux_login_form', array( $this, 'render_login_form' ) );

		/**
		 * Login form scripts.
		 */
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// set hooked to true after completing hooks
		self::$hooked = true;
	}

	private function set_notification( $notification, $type ) {
		if ($type === 'error') {
			self::$has_error = true;
		}
		self::$user_notifications[] = array('type' => $type, 'notification' => $notification);
	}

	private function error_check() {
		return self::$has_error;
	}

	private function print_notifications() {
		$notifications = self::$user_notifications;

		if ($notifications) { ?>
			<div class="nux-custom-login-notifications">
			<?php foreach ($notifications as $notification) { ?>
				<div class="nux-custom-login-notifications__notification notification-type-<?php echo $notification['type']; ?>"><?php echo $notification['notification']; ?></div>
			<?php } ?>
			</div>
		<?php }
		
	}

	/**
	 * Handles 'Login' form submit.
	 *
	 * @hooked action `'wp_ajax_nopriv_' . self::$login_action`
	 *
	 * @return string `wp_send_json_error|wp_send_json_success` response
	 */
	public function handle_login_submit() {
		if ( ! isset( $_REQUEST['_wpnonce'] ) ) {
			wp_send_json_error();
		}

		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], self::$login_action ) ) {
			wp_send_json_error();
		}

		if ( ! isset( $_REQUEST['email'] ) || ! isset( $_REQUEST['password'] ) ) {
			wp_send_json_error( array(
				'message' => __( 'Please fill in all fields.', 'nux' ),
			) );
		}

		/**
		 * Sanitize inputs.
		 */
		$email       = sanitize_text_field( $_REQUEST['email'] );
		$password    = sanitize_text_field( $_REQUEST['password'] );
		$remember_me = isset( $_REQUEST['remember_me'] ) ? intval( $_REQUEST['remember_me'] ) : 0;

		$credentials = array(
			'user_login'     => $email,
			'user_password'  => $password,
			'remember'       => $remember_me,
		);

		$sign_on = wp_signon( $credentials, true );

		/**
		 * Set current user and redirect to homepage on success.
		 */
		if ( ! is_wp_error( $sign_on ) ) {
			wp_set_current_user( $sign_on->data->ID );
			$user = wp_get_current_user();
			$requested_redirect_to = $_REQUEST['redirect_to'] ? $_REQUEST['redirect_to'] : site_url();

			$redirect_to = site_url( $_REQUEST['_wp_http_referer'] );

			wp_send_json_success( array(
				'redirect' => apply_filters( 'login_redirect', $redirect_to, $requested_redirect_to, $user ),
			) );
		/**
		 * On failure, return error message to client.
		 */
		} else {
			error_log('sending json error');
			wp_send_json_error( array(
				'message' => sprintf(
					__( 'The email or password you entered is incorrect. <a href="%s">Lost your password?</a>', 'nux' ),
					add_query_arg( 'forgot', '', site_url( '/' . self::$login_page_slug ) )
				),
			) );
		}
	}

	/**
	 * Handles 'Forgot Password' form submit.
	 *
	 * @hooked action `'wp_ajax_nopriv_' . self::$forgot_password_action`
	 *
	 * @return string `wp_send_json_error|wp_send_json_success` response
	 */
	public function handle_forgot_password_submit() {
		if ( ! isset( $_REQUEST['_wpnonce'] ) ) {
			wp_send_json_error();
		}

		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], self::$forgot_password_action ) ) {
			wp_send_json_error();
		}

		$email = sanitize_email( $_REQUEST['email'] );

		/**
		 * Return early if email is empty.
		 */
		if ( empty( $email ) ) {
			wp_send_json_error( array(
			 	'message' => __( 'Email Address is a required field.', 'nux' )
			) );
		}

		$user = get_user_by( 'email', $email );

		/**
		 * Return early if user is not found.
		 */
		if ( ! $user ) {
			wp_send_json_error( array(
				'message' => __( 'This email address does not appear to be in our records.', 'nux' ),
			) );
		}

		/**
		 * Generate hash for password reset at 32 length.
		 */
		$hash = get_password_reset_key( $user );

		/**
		 * Template data.
		 */
		$mail_data = array(
			'link'       => site_url( "/" . self::$login_page_slug . "?hash={$hash}&login=" . $email ),
			'site_title' => get_bloginfo( 'name' ),
		);

		$mail_content = '';

		ob_start();
		require_once( get_plugin_dir_path() . '/templates/header-default.php' );
		require_once( get_plugin_dir_path() . '/templates/email-password-reset.php' );
		require_once( get_plugin_dir_path() . '/templates/footer-default.php' );
		$mail_content = ob_get_clean();

		/**
		 * Send email.
		 */
		$mail = wp_mail(
			$user->data->user_email,
			sprintf(
				__( 'Your password reset link for %s', 'nux' ),
				get_bloginfo( 'name' )
			),
			$mail_content,
			'Content-Type:  text/html; charset="UTF-8"'
			
		);
		if ( $mail ) {
			wp_send_json_success( array(
				'message' => __( "Your request has been submitted. Please check your inbox for instructions on resetting your password. If you don't see an email in your inbox, make sure to check your spam folder as well.", 'nux' ),
			) );
		} else {
			wp_send_json_error( array(
				'message' => __( 'Oops! Something went wrong. Please try submitting the form again.', 'nux' ),
			) );
		}
	}

	/**
	 * Handles validation of password hash. Redirects to homepage if hash is not valid.
	 *
	 * @hooked action `parse_request`
	 *
	 * @return void
	 */
	public function handle_password_hash() {

		/**
		 * Return early if hash parameter is not found in URL.
		 */
		if ( ! isset( $_GET['hash'] ) || ! isset( $_GET['login'] ) ) {
			return;
		}

		$hash = sanitize_text_field( $_GET['hash'] );
		$login = sanitize_text_field( $_GET['login'] );

		//check if login is an email, if it is, get username instead.
		if ( email_exists( $login ) ) {
			$user = get_user_by( 'email', $login );
			$email = $login;
			$login = $user->data->user_login;
		} else {
			$user = get_user_by( 'login', $login );
			$email = $user->data->user_email;
		}

		if ( is_user_logged_in() ) {
			wp_safe_redirect( site_url() );
			exit();
		}
		/**
		 * If hash is empty, redirect to homepage.
		 */
		if ( empty( $hash ) ) {
			self::set_notification('Empty reset hash, we can not reset your password.', 'error');
		}

		/**
		 * If login is empty or user doesn't exist, redirect to homepage.
		 */
		if ( empty( $login ) || ! email_exists( $email ) ) {
			self::set_notification('Empty or invalid login email, we can not reset your password.', 'error');
		}
		// This function exists in woocommerce and overrides wp core, therefore this will not work when using woocommerce
		$reset = check_password_reset_key($hash,$login);

		if ( ! $reset || is_wp_error( $reset ) ) {
			if ( $reset->errors ) {
				foreach ($reset->errors as $error) {
					self::set_notification('We could not validate your reset key: ' . $error[0], 'error');
				}
			}
			
		}
	}

	/**
	 * Handles 'Reset Password' form submit.
	 *
	 * Validates all the input and sets new password for the user. If successful, it removes the hash from user meta.
	 *
	 * @hooked action `wp_ajax_nopriv_' . self::$reset_password_action`
	 *
	 * @return void
	 */
	public function handle_reset_password_submit() {
		if ( ! isset( $_REQUEST['_wpnonce'] ) ) {
			wp_send_json_error();
		}

		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], self::$reset_password_action ) ) {
			wp_send_json_error();
		}

		/**
		 * Return validation error if required fields are not set.
		 */
		if ( ! isset( $_REQUEST['password'] ) || ! isset( $_REQUEST['confirm_password'] ) || ! isset( $_REQUEST['hash'] ) || ! isset( $_REQUEST['login'] ) ) {
			wp_send_json_error( array(
				'message' => __( 'Please fill in all fields.', 'nux' ),
			) );
		}

		/**
		 * Sanitize inputs.
		 */
		error_log('sanitizing inputs');
		error_log(print_r($_REQUEST, true));
		$password              = sanitize_text_field( $_REQUEST['password'] );
		$password_confirmation = sanitize_text_field( $_REQUEST['confirm_password'] );
		$hash                  = sanitize_text_field( $_REQUEST['hash'] );
		$login                 = sanitize_text_field( $_REQUEST['login'] );
		$generic_error_message = __( 'Oops! Something went wrong. Please try submitting the form again or contact us.', 'nux' );

		if ( email_exists( $login ) ) {
			$user = get_user_by( 'email', $login );
			$email = $login;
			$login = $user->data->user_login;
		} else {
			$user = get_user_by( 'login', $login );
			$email = $user->data->user_email;
		}

		/**
		 * Return generic error message if hash is empty.
		 */
		if ( empty( $hash ) ) {
			wp_send_json_error( array(
				'message' => $generic_error_message,
			) );
		}

		/**
		 * Return validation error if password does not meet minimum length requirements.
		 */
		if ( self::$password_min_length > strlen( $password ) ) {
			wp_send_json_error( array(
				'message' => sprintf(
					__( 'Password needs to be at least %s characters long.', 'nux' ),
					self::$password_min_length
				),
			) );
		}

		/**
		 * Return validation error if passwords do not match.
		 */
		if ( $password !== $password_confirmation ) {
			wp_send_json_error( array(
				'message' => __( 'Passwords do not match.', 'nux' ),
			) );
		}

		$reset = check_password_reset_key($hash,$login);

		/**
		 * Return generic error message if user is not found.
		 */
		if ( ! $reset || is_wp_error( $reset ) ) {
			wp_send_json_error( array(
				'message' => $generic_error_message,
			) );
		}

		$user = $reset;

		/**
		 * Update user password.
		 *
		 * `wp_update_user` automatically applies all salts on plain text password, so we don't need to call
		 * any additional function on that.
		 */
		$update = wp_update_user(
			array(
				'ID'        => $user->ID,
				'user_pass' => $password,
			)
		);

		/**
		 * If successful, return success message to client.
		 */
		if ( ! is_wp_error( $update ) ) {
			wp_send_json_success( array(
				'message' => sprintf(
					__( 'All done! You can now <a href="%s">log in</a> with new password.', 'nux' ),
					site_url( '/' . self::$login_page_slug )
				),
			) );
		/**
		 * If error, return generic error message.
		 */
		} else {
			wp_send_json_error( array(
				'message' => $generic_error_message,
			) );
		}
	}

	/**
	 * Renders shortcode.
	 *
	 * @hooked function `add_shortcode`
	 *
	 * @return void
	 */
	public function render_login_form() {

		self::enqueue_scripts_shortcode();
		$html = '';

		/**
		 * If user is not logged in, include form template.
		 */
		if ( ! is_user_logged_in() ) {
			ob_start();
			require_once( get_plugin_dir_path() . '/templates/login-form.php' );
			$html = ob_get_clean();
		/**
		 * Otherwise, return message and option to log out in order to access login form.
		 */
		} else {
			ob_start();
			echo sprintf(
				'<div class="container container--spacing">' .  __( 'You are already logged in. <a href="%s">Log out</a>?', 'nux' ) . '</div>',
				wp_logout_url( site_url( '/' . self::$login_page_slug ) )
			);
			$html = ob_get_clean();
		}

		return $html;
	}

	/**
	 * Enqueues scripts for custom login form.
	 *
	 * @hooked action `wp_enqueue_scripts`
	 *
	 * @return void
	 */
	public function enqueue_scripts() {

		/**
		 * Return early if not on login page.
		 */
		if ( ! is_page( self::$login_page_slug ) ) {
			return;
		}

		wp_register_script(
			'nux-custom-login',
			get_plugin_url() . '/assets/js/custom-login.js',
			array( 'jquery' ),
			FRONTENDLOGIN_PLUGIN_VERSION,
			true
		);

		wp_localize_script(
			'nux-custom-login',
			'_nuxCustomLoginSettings',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
			)
		);

		wp_enqueue_script( 'nux-custom-login' );
	}

	public function enqueue_scripts_shortcode() {
		wp_register_script(
			'nux-custom-login',
			get_plugin_url() . '/assets/js/custom-login.js',
			array( 'jquery' ),
			FRONTENDLOGIN_PLUGIN_VERSION,
			true
		);

		wp_localize_script(
			'nux-custom-login',
			'_nuxCustomLoginSettings',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
			)
		);

		wp_enqueue_script( 'nux-custom-login' );
	}

	/**
	 * Enqueues scripts for custom login form template tag.
	 *
	 * @hooked action `wp_enqueue_scripts`
	 *
	 * @return void
	 */
	public function enqueue_scripts_tag( $slug ) {
		if ( ! is_page( $slug ) ) {
			return;
		}

		wp_register_script(
			'nux-custom-login',
			get_plugin_url() . '/assets/js/custom-login.js',
			array( 'jquery' ),
			FRONTENDLOGIN_PLUGIN_VERSION,
			true
		);

		wp_localize_script(
			'nux-custom-login',
			'_nuxCustomLoginSettings',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
			)
		);

		wp_enqueue_script( 'nux-custom-login' );
	}


	public static function if_has_access( $user, $roles ) {
		$has_access = false;
		if ( null === $user ) {
			$user = get_current_user();
		}
		if ( is_user_logged_in() ) {
			
			if ( in_array( 'administrator', $user->roles, true ) ) {
				$has_access = true;
			} else {
				foreach ( $roles as $role ) {
					if ( in_array( $role, $user->roles, true ) ) {
						$has_access = true;
					}
				}	
			} 
		}

		return $has_access;

	}

}

$custom_login = new Custom_Login();
$custom_login->init();

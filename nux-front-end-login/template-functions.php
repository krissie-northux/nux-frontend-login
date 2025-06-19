<?php
// Template function to use the Custom Login form
function nux_login_form() {
	$custom_login = new NorthUX\FrontendLogin\Custom_Login();
	global $post;
	$custom_login->enqueue_scripts_tag( $post->post_name );
	return $custom_login->render_login_form();
}

// Checks if user has appropriate access
function nux_user_has_access( $user, $role ) {
	return NorthUX\FrontendLogin\Custom_Login::if_has_access( $user, $role );
}
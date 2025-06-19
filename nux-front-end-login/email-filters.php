<?php 

add_filter( 'password_change_email', 'nux_password_change_email', 10, 3 );
function nux_password_change_email( $pass_change_email, $user, $userdata ) {
	add_filter ("wp_mail_content_type", "nux_mail_content_type");
	ob_start();
	include('templates/header-default.php');
	include('templates/email-reset-confirm.php');
	include('templates/footer-default.php');
	$pass_change_email['message'] = ob_get_clean();
	$pass_change_email['subject'] = 'Your ' . get_bloginfo( 'name' ) . ' password has been updated!';
	return $pass_change_email;
}

function nux_mail_content_type() {
    return "text/html";
}
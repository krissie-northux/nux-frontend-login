<?php
/*
Plugin Name: NUX Front-end Login and Member Landing
Plugin URI:  https://northuxdesign.com/
Description: Set up front-end login process and member landing pages based on role
Version:     1.1
Author:      North UX Design
Author URI:  https://northuxdesign.com/
*/
namespace NorthUX\FrontendLogin;

define( 'FRONTENDLOGIN_PLUGIN_VERSION', '1.1' );

defined( 'ABSPATH' ) || die();

function get_plugin_dir_path() {
	return plugin_dir_path( __FILE__ );
}

function get_plugin_url() {
	return plugin_dir_url( __FILE__ );
}

require get_plugin_dir_path() . '/classes/class-members.php';
require get_plugin_dir_path() . '/classes/class-custom-login.php';
require get_plugin_dir_path() . '/email-filters.php';
require get_plugin_dir_path() . '/template-functions.php';

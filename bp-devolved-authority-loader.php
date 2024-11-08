<?php
/*
Plugin Name: BP Devolved Authority
Plugin URI: https://buddyuser.com/plugin-bp-devolved-authority
Description: This plugin allows key aspects of BuddyPress administration to be devolved to non admin users.
Author: Venutius
Author URI: https://buddyuser.com
License: GNU GENERAL PUBLIC LICENSE 3.0 http://www.gnu.org/licenses/gpl.txt
Version: 1.2.0
Text Domain: bp-devolved-authority
Copyright: 2024 Venutius @buddyuser
Domain Path: /languages
*/

if(!defined('ABSPATH')) {
	exit;
}

function bpda_bp_devolved_authority_init() {

	//load if we care
	if ( file_exists( dirname( __FILE__ ).'/langs/bp-devolved-authority-' . get_locale() . 'mo' ) ) {
		load_plugin_textdomain('bp-devolved-authority', dirname( __FILE__ ).'/langs/bp-devolved-authority-' . get_locale() . 'mo' );
	}
	
	require( dirname( __FILE__ ) . '/bp-devolved-authority.php' );
	
	if ( get_option( 'bpda_xprofile_individual') == 'on' )
		require( dirname( __FILE__ ) . '/includes/bpda-individual-xprofile-management.php' );
	
	add_action( bp_core_admin_hook(), 'bpda_bp_devolved_authority_admin_add_admin_menu' );
	
	add_action( 'admin_enqueue_scripts', 'bpda_devolved_authority_admin_enqueue_scripts' );
	
}
add_action( 'bp_include', 'bpda_bp_devolved_authority_init', 88 );

//add admin_menu page
function bpda_bp_devolved_authority_admin_add_admin_menu() {
	global $bp;
	
	if ( ! current_user_can( 'manage_options' ) )
		return false;

	//Add the component's administration tab under the "Setting" menu for site administrators
	require ( dirname( __FILE__ ) . '/admin/bp-devolved-authority-admin.php' );

	add_submenu_page( 'options-general.php', esc_attr__( 'Devolved Authority Admin', 'bp-devolved-authority' ), sanitize_text_field(esc_attr__( 'BP Devolved Authority', 'bp-devolved-authority' )), 'manage_options', 'bp-devolved-authority-settings', 'bpda_bp_devolved_authority_admin' );

}

// Enqueue scripts
function bpda_devolved_authority_admin_enqueue_scripts() {
	wp_register_script( 'bpda-admin-js', plugins_url( 'js/bp-devolved-authority-admin.js', __FILE__ ), array(), '1.0.0', array( 'in_footer' => true ));
	wp_enqueue_script( 'bpda-admin-js' );
	wp_localize_script( 'bpda-admin-js', 'ajax_object', array( 'ajaxurl' => admin_url( 'admin-ajax.php'), 'check_nonce' => wp_create_nonce('bpda-nonce') ) );
	
}

function bpda_bp_devolved_authority_admin_add_action_link( $links, $file ) {
	if ( 'bp-devolved-authority/bp-devolved-authority-loader.php' != $file )
		return $links;

	if ( function_exists( 'bp_core_do_network_admin' ) ) {
		$settings_url = add_query_arg( 'page', 'bp-devolved-authority-settings', bp_core_do_network_admin() ? network_admin_url( 'options-general.php' ) : admin_url( 'options-general.php' ) );
	} else {
		$settings_url = add_query_arg( 'page', 'bp-devolved-authority-settings', is_multisite() ? network_admin_url( 'admin.php' ) : admin_url( 'options-general.php' ) );
	}

	$settings_link = '<a href="' . $settings_url . '">' . sanitize_text_field(esc_attr__( 'Settings', 'bp-devolved-authority' )) . '</a>';
	array_unshift( $links, $settings_link );

	return $links;
}
add_filter( 'plugin_action_links', 'bpda_bp_devolved_authority_admin_add_action_link', 10, 2 );
?>

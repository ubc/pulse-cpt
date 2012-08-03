<?php
/*
Plugin Name: Pulse CPT
Plugin URI: 
Description: 
Version: 0.1
Author: 
Author URI: 
License: GPLv2 or later.
*/
if ( !defined('ABSPATH') )
	die('-1');

define( 'PULSE_CPT_DIR_PATH', plugin_dir_path( __FILE__ ) );
define( 'PULSE_CPT_BASENAME', plugin_basename(__FILE__) );
define( 'PULSE_CPT_DIR_URL',  plugins_url( ''  , PULSE_CPT_BASENAME ) );

require_once( 'lib/class.pulse_cpt.php' );
require_once( 'lib/class.pulse_cpt_form.php' );
require_once( 'lib/class.pulse_cpt_form_widget.php' );
require_once( 'lib/class.pulse_cpt_settings.php' );

add_action( 'init',        			array( 'Pulse_CPT', 'init' ) );
add_action( 'widgets_init',        	array( 'Pulse_CPT', 'widgets_init' ) );

add_action( 'wp_footer', 			array( 'Pulse_CPT', 'print_form_script' ) );
add_action( 'template_redirect', 	array( 'Pulse_CPT', 'template_redirect' ) );

add_action( 'wp_ajax_pulse_cpt_insert', 			array( 'Pulse_CPT_Form', 'insert' ) );
add_action( 'template_redirect', 	array( 'Pulse_CPT', 'template_redirect' ) );
add_action( 'wp_footer',			array( 'Pulse_CPT', 'footer'), 1 ); // templates should be generated before calling the js


add_action( 'admin_menu', array( 'Pulse_CPT_Settings', 'admin_menu' ) );

// filters
add_filter( 'wp_insert_post_data' , array( 'Pulse_CPT_Form', 'edit_post_data' ), 10, 2 );


// install and uninstall
register_activation_hook( __FILE__, array( 'Pulse_CPT', 'install' ) );






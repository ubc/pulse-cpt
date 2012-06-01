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
require_once( 'lib/class.pulse_cpt_form_widget.php' );

add_action( 'init',        			array( 'Pulse_CPT', 'init' ) );
add_action( 'widgets_init',        	array( 'Pulse_CPT', 'widgets_init' ) );
add_action( 'wp_footer', 			array( 'Pulse_CPT', 'print_form_script' ) );
add_action( 'template_redirect', 	array( 'Pulse_CPT', 'template_redirect' ) );

add_action( 'wp_footer', 			array( 'Pulse_CPT', 'print_form_script' ) );
add_action( 'template_redirect', 	array( 'Pulse_CPT', 'template_redirect' ) );


// install and uninstall
register_activation_hook( __FILE__, array( 'Pulse_CPT', 'install' ) );


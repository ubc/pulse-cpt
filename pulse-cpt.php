<?php
/*
Plugin Name: Pulse CPT
Plugin URI: 
Description: 
Version: 
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

add_action( 'init',        			array( 'Class_Module_CPT', 'init' ) );
add_action( 'widgets_init',        	array( 'Class_Module_CPT', 'widgets_init' ) );
add_action( 'wp_footer', 			array( 'Class_Module_CPT', 'print_form_script' ) );
register_activation_hook( __FILE__, array( 'Class_Module_CPT', 'install' ) );

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
if ( ! defined('ABSPATH') )
  die('-1');

define( 'PULSE_CPT_DIR_PATH', plugin_dir_path(__FILE__) );
define( 'PULSE_CPT_BASENAME', plugin_basename(__FILE__) );
define( 'PULSE_CPT_DIR_URL',  plugins_url('', PULSE_CPT_BASENAME) );
define( 'PULSE_CPT_VERSION',  0.5) ;

require_once( 'lib/class.pulse_cpt.php' );
require_once( 'lib/class.pulse_cpt_form.php' );
require_once( 'lib/class.pulse_cpt_form_widget.php' );
require_once( 'lib/class.pulse_cpt_settings.php' );

add_action( 'init',              array( 'Pulse_CPT', 'init' ) );
add_action( 'widgets_init',      array( 'Pulse_CPT', 'widgets_init' ) );
add_action( 'wp_footer',         array( 'Pulse_CPT', 'print_form_script' ) );
add_action( 'wp_footer',         array( 'Pulse_CPT', 'print_pulse_script' ) );
add_action( 'template_redirect', array( 'Pulse_CPT', 'template_redirect' ) );
add_action( 'wp_footer',         array( 'Pulse_CPT', 'footer' ), 1 ); // templates should be generated before calling the js
add_action( 'wp_ajax_pulse_cpt_insert', array ('Pulse_CPT_Form', 'insert' ) );

//ajax request handler for getting pulse replies
add_action( 'wp_ajax_pulse_cpt_replies', array( 'Pulse_CPT', 'ajax_replies' ) );
add_action( 'wp_ajax_nopriv_pulse_cpt_replies', array( 'Pulse_CPT', 'ajax_replies' ) );

// add column 
add_filter( 'manage_pulse-cpt_posts_columns', array( 'Pulse_CPT', 'add_new_column' ) );

add_action( 'manage_pulse-cpt_posts_custom_column', array( 'Pulse_CPT','manage_columns'), 10, 2 );

add_action( 'admin_init', array( 'Pulse_CPT_Settings', 'init' ) );
add_action( 'admin_menu', array( 'Pulse_CPT_Settings', 'admin_menu' ) );

add_action( 'publish_pulse-cpt', array( 'Pulse_CPT_Form', 'admin_publish' ) );

// filters
add_filter( 'wp_insert_post_data', array( 'Pulse_CPT_Form', 'edit_post_data' ), 10, 2 );
// add_action( 'pre_get_posts', array( 'Pulse_CPT', 'include_pulse_cpt') );
add_filter( 'carry_content_template', array( 'Pulse_CPT', 'load_pulse_template' ) );

// install and uninstall
register_activation_hook( __FILE__, array( 'Pulse_CPT', 'install' ) );


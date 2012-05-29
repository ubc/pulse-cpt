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


require_once( 'lib/class.pulse_cpt.php' );
require_once( 'lib/class.pulse_cpt_widget.php' );

add_action( 'init',        array( 'Class_Module_CPT', 'init' ) );
register_activation_hook( __FILE__, array( 'Class_Module_CPT', 'install') );


?>
<?php
/**
  Plugin Name: Pulse CPT
  Plugin URI: https://github.com/ubc/pulse-cpt
  Version: 1.0
  Description: Enables a widget that allows comments (called pulses) to be posted on any page of the site. Integrates with Stream, Evaluate, and Co Author+ to bring additional functionality to the comment stream.
  Author: Enej Bajgoric, Devindra Payment, CTLT, UBC
  Author URI: http://ctlt.ubc.ca
  Text Domain: pulse_cpt
  License: GPLv2
 */

if ( ! defined('ABSPATH') )
	die('-1');

define( 'PULSE_CPT_DIR_PATH', plugin_dir_path(__FILE__) );
define( 'PULSE_CPT_BASENAME', plugin_basename(__FILE__) );
define( 'PULSE_CPT_DIR_URL',  plugins_url('', PULSE_CPT_BASENAME) );
define( 'PULSE_CPT_VERSION',  0.5 );

require_once( 'lib/class.pulse_cpt.php' );
require_once( 'lib/class.pulse_cpt_form.php' );
require_once( 'lib/class.pulse_cpt_form_widget.php' );
require_once( 'lib/class.pulse_cpt_settings.php' );
require_once( 'lib/class.pulse_cpt_admin.php' );

// Register the activation hooks for the plugin
register_activation_hook( __FILE__, array( 'Pulse_CPT', 'install' ) );

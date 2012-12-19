<?php
/* Settings Screen for the Pulse Custo Post Type */

class Pulse_CPT_Settings {
  
  static $options = array();
  
  public static function admin_menu() {

    $page = add_submenu_page('edit.php?post_type=pulse-cpt', 'Settings', 'Settings', 'manage_options', 'pulse-cpt_settings', array('Pulse_CPT_Settings', 'admin_page'));
  }

  public static function init() {
    //check if CTLT_Stream plugin exists to use with node
    if (!function_exists('is_plugin_active')) {
      //include plugins.php to check for other plugins from the frontend
      include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    }
    self::$options['CTLT_STREAM'] = is_plugin_active('stream/stream.php');
    
    //register settings
    //these need to be in admin_init otherwise Settings API doesn't work
    register_setting('pulse_options', 'pulse_bitly_username');
    register_setting('pulse_options', 'pulse_bitly_key');
    
    //define section
    add_settings_section('pulse_settings', 'Evaluate Settings', function() {
	      echo 'Settings and CTLT_Stream/NodeJS Status';
	    }, 'pulse-cpt_settings');

    //add fields
    add_settings_field('pulse_bitly_username', 'Bit.ly Username', function() {
	      echo '<input id="pulse_bitly_username" name="pulse_bitly_username" value="'.get_option('pulse_bitly_username').'" type="text" />';
	    }, 'pulse-cpt_settings', 'pulse_settings');
	    
    add_settings_field('pulse_bitly_key', 'Bit.ly API Key', function() { ?>
	      <input id="pulse_bitly_key" name="pulse_bitly_key" value="<?php echo get_option('pulse_bitly_key'); ?>" type="text" />
	      <small class="clear">To get your <a href="http://bit.ly" target="_blank">bit.ly</a> API key - <a href="http://bit.ly/a/sign_up" target="_blank">sign up</a> and view your <a href="http://bit.ly/a/your_api_key/" target="_blank">API KEY</a></small>
	    <?php }, 'pulse-cpt_settings', 'pulse_settings');

    //CTLT_Stream and NodeJS indicators, these are not registered/saved
    add_settings_field('ctlt_stream_found', 'CTLT_Stream plugin found', function() {
	      echo '<input id="ctlt_stream_status" name="ctlt_stream_status" type="checkbox" disabled="disabled" ' .
	      checked(1, true, false) . '/>';
	    }, 'pulse-cpt_settings', 'pulse_settings');

    if (self::$options['CTLT_STREAM']) {
      add_settings_field('nodejs_server_status', 'NodeJS Server status', function() {
		echo '<input id="nodejs_server_status" name="nodejs_server_status" type="checkbox" disabled="disabled"' .
		checked(1, true, false) . '/>';
	      }, 'pulse-cpt_settings', 'pulse_settings');
    }
  }
  
  public static function admin_page() {
    ?>
    <form id="pulse_options" method="post" action="options.php">
      <?php
      do_settings_sections('pulse-cpt_settings');
      settings_fields('pulse_options');
      ?>
      <input type="submit" class="button-primary" value="Save Changes" />
    </form>
    <?php
  }

}
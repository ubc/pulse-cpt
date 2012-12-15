<?php
/* Settings Screen for the Pulse Custo Post Type */

class Pulse_CPT_Settings {
  public static function admin_menu() {

    $page = add_submenu_page(
	    'edit.php?post_type=pulse-cpt', 'Settings', 'Settings', 'manage_options', __FILE__, array('Pulse_CPT_Settings', 'admin_page'));
  }

  public static function admin_page() {
    //check if CTLT_Stream plugin exists
    add_settings_section('pulse_settings', 'Pulse CPT Settings', function() {
	      echo 'Settings and CTLT_Stream/NodeJS Status';
	    }, 'pulse-cpt/lib/class.pulse_cpt_settings.php');

    $ctlt_stream_active = is_plugin_active('stream/stream.php');
    
    add_settings_field('ctlt_stream_found', 'CTLT_Stream plugin found', function() use ($ctlt_stream_active) {
	      echo '<input id="ctlt_stream_status" name="ctlt_stream_status" type="checkbox" disabled="disabled" ' .
	      checked(1, $ctlt_stream_active, false) . '/>';
	    }, 'pulse-cpt/lib/class.pulse_cpt_settings.php', 'pulse_settings');

    if ($ctlt_stream_active) {
      add_settings_field('nodejs_server_status', 'NodeJS server status', function() {
		echo '<input id="nodejs_server_status" name="ctlt_stream_status" type="checkbox" disabled="disabled" ' .
		checked(1, CTLT_Stream::is_node_active(), false) . '/>';
	      }, 'pulse-cpt/lib/class.pulse_cpt_settings.php', 'pulse_settings');
    }
    ?>
    <form id="pulse-options" method="post" action="options.php">
      <?php
      do_settings_sections('pulse-cpt/lib/class.pulse_cpt_settings.php');
      ?>
      <input type="submit" class="button-primary" value="Save Changes" />
    </form>
    <?php
  }

}
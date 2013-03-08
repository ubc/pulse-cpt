<?php
/* Settings Screen for the Pulse Custo Post Type */
class Pulse_CPT_Settings {
	
	static $options = array();
	
	public static function admin_menu() {
		$page = add_submenu_page( 'edit.php?post_type=pulse-cpt', 'Settings', 'Settings', 'manage_options', 'pulse-cpt_settings', array( __CLASS__, 'admin_page' ) );
	}
	
	public static function init() {
		//check if CTLT_Stream plugin exists to use with node
		if ( ! function_exists( 'is_plugin_active' ) ):
			//include plugins.php to check for other plugins from the frontend
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		endif;
		self::$options['CTLT_STREAM'] = is_plugin_active('stream/stream.php');
		
		//register settings
		//these need to be in admin_init otherwise Settings API doesn't work
		register_setting( 'pulse_options', 'pulse_bitly_username' );
		register_setting( 'pulse_options', 'pulse_bitly_key' );
		
		//define section
		add_settings_section( 'pulse_settings', 'Pulse-CPT Settings', array( __CLASS__, 'setting_section' ), 'pulse-cpt_settings' );
	  
		//add fields
		add_settings_field( 'pulse_bitly_username', 'Bit.ly Username', array( __CLASS__, 'setting_bitly_username' ), 'pulse-cpt_settings', 'pulse_settings' );
			
		add_settings_field( 'pulse_bitly_key', 'Bit.ly API Key', array( __CLASS__, 'setting_bitly_key' ), 'pulse-cpt_settings', 'pulse_settings' );
	  
		//CTLT_Stream and NodeJS indicators, these are not registered/savesd
		add_settings_field( 'ctlt_stream_found', 'CTLT Stream plugin', array( __CLASS__, 'setting_stream_plugin' ), 'pulse-cpt_settings', 'pulse_settings' );
	  
		if ( self::$options['CTLT_STREAM'] ):
			add_settings_field( 'nodejs_server_status', 'NodeJS Server', array( __CLASS__, 'setting_nodejs_server' ), 'pulse-cpt_settings', 'pulse_settings' );
		endif;
	}
	
	public static function setting_section() {
		?>
		Settings and CTLT Stream/NodeJS Status
		<?php
	}
	
	public static function setting_bitly_username() {
		?>
		<input id="pulse_bitly_username" name="pulse_bitly_username" value="<?php get_option( 'pulse_bitly_username' ); ?>" type="text" />
		<?php
	}
	
	public static function setting_bitly_key() {
		?>
		<input id="pulse_bitly_key" name="pulse_bitly_key" value="<?php echo get_option('pulse_bitly_key'); ?>" type="text" />
		<small class="clear">
			To get your <a href="http://bit.ly" target="_blank">bit.ly</a> API key - <a href="http://bit.ly/a/sign_up" target="_blank">sign up</a> and view your <a href="http://bit.ly/a/your_api_key/" target="_blank">API KEY</a>
		</small>
		<?php
	}
	
	public static function setting_stream_plugin() {
		?>
		<input id="ctlt_stream_status" name="ctlt_stream_status" type="checkbox" style="display: none" disabled="disabled" <?php checked( self::$options['CTLT_STREAM'] ); ?>/>
		
		<?php if ( self::$options['CTLT_STREAM'] == true ): ?>
			<div style="color: green">Enabled</div>
		<?php else: ?>
			<div style="color: red">Not Found</div>
		<?php endif;
	}
	
	public static function setting_nodejs_server() {
		?>
		<input id="nodejs_server_status" name="nodejs_server_status" type="checkbox" style="display: none" disabled="disabled" <?php checked( CTLT_Stream::is_node_active() ); ?>/>
		
		<?php if ( CTLT_Stream::is_node_active() == true ): ?>
			<div style="color: green">Connected</div>
		<?php else: ?>
			<div style="color: red">Not Found</div>
		<?php endif;
	}
	
	public static function admin_page() {
		?>
		<form id="pulse_options" method="post" action="options.php">
			<?php
				do_settings_sections('pulse-cpt_settings');
				settings_fields('pulse_options');
			?>
			<br />
			<input type="submit" class="button-primary" value="Save Changes" />
		</form>
		<?php
	}
}
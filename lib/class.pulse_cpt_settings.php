<?php
/**
 * Settings Screen for the Pulse Custom Post Type
 */
class Pulse_CPT_Settings {
	static $options = array();
	
	public static function admin_menu() {
		$page = add_submenu_page( 'edit.php?post_type=pulse-cpt', 'Settings', 'Settings', 'manage_options', 'pulse-cpt_settings', array( __CLASS__, 'admin_page' ) );
	}
	
	public static function init() {
		if ( ! function_exists( 'is_plugin_active' ) ):
			// Include plugins.php to check for other plugins from the frontend
			include_once( ABSPATH.'wp-admin/includes/plugin.php' );
		endif;
		
		self::$options['CTLT_STREAM'] = is_plugin_active('stream/stream.php');
		self::$options['CTLT_EVALUATE'] = is_plugin_active('evaluate/evaluate.php');
		self::$options['COAUTHOR_PLUGIN'] = defined('COAUTHORS_PLUS_VERSION');
		
		add_action( 'admin_init', array( __CLASS__, 'load' ) );
		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );
	}
	
	public static function load() {
		//register settings
		//these need to be in admin_init otherwise Settings API doesn't work
		register_setting( 'pulse_options', 'pulse_bitly_username' );
		register_setting( 'pulse_options', 'pulse_bitly_key' );
		register_setting( 'pulse_options', 'pulse_favourite' );
		
		// Main settings
		add_settings_section( 'pulse_settings_main', 'Pulse CPT Settings', array( __CLASS__, 'setting_section_main' ), 'pulse-cpt_settings' );
		add_settings_field( 'pulse_bitly_username', 'Bit.ly Username', array( __CLASS__, 'setting_bitly_username' ), 'pulse-cpt_settings', 'pulse_settings_main' );
		add_settings_field( 'pulse_bitly_key',      'Bit.ly API Key',  array( __CLASS__, 'setting_bitly_key' ),      'pulse-cpt_settings', 'pulse_settings_main' );
		
		// Plugin integration
		add_settings_section( 'pulse_settings_plugins', 'Plugin Integration Status', array( __CLASS__, 'setting_section_plugins' ), 'pulse-cpt_settings' );
		add_settings_field( 'ctlt_stream_found', 'CTLT Stream plugin', array( __CLASS__, 'setting_stream_plugin' ), 'pulse-cpt_settings', 'pulse_settings_plugins' );
		
		if ( self::$options['CTLT_STREAM'] == true && class_exists( 'CTLT_Stream' ) ):
			$callback = array( 'CTLT_Stream', 'setting_server_status' );
		else:
			$callback = array( __CLASS__, 'setting_stream_plugin_not_found' );
		endif;
		add_settings_field( 'nodejs_server_status', 'NodeJS Server', $callback, 'pulse-cpt_settings', 'pulse_settings_plugins' );
		
		add_settings_field( 'evaluate_found', 'Evaluate plugin', array( __CLASS__, 'setting_evaluate_plugin' ), 'pulse-cpt_settings', 'pulse_settings_plugins' );
		add_settings_field( 'coauthor_found', 'Co-Author+ plugin', array( __CLASS__, 'setting_coauthor_plugin' ), 'pulse-cpt_settings', 'pulse_settings_plugins' );
		//add_settings_field( 'pulse_rating', 'Enable Favourite Rating', array( __CLASS__, 'setting_rating' ), 'pulse-cpt_settings', 'pulse_settings_plugins' );
	}
	
	public static function setting_section_main() {
		?>
		Main Settings
		<?php
	}
	
	public static function setting_section_plugins() {
		?>
		Integration for the CTLT Stream, Evaluate, and CoAuthor+ plugins
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
		<input id="nodejs_server_status" name="nodejs_server_status" type="checkbox" disabled="disabled" style="display: none" <?php checked( CTLT_Stream::is_node_active() ); ?> />
		
		<?php if ( self::$options['CTLT_STREAM'] != true ): ?>
			<div style="color: red">Stream Plugin Not Found</div>
		<?php elseif ( CTLT_Stream::is_node_active() ): ?>
			<div style="color: green">Connected</div>
		<?php else: ?>
			<div style="color: red">Server Not Found</div>
		<?php endif;
	}
	
	public static function setting_stream_plugin_not_found() {
		?>
		<div style="color: red">Stream Plugin Not Found</div>
		<?php
	}
	
	public static function setting_evaluate_plugin() {
		if ( self::$options['CTLT_EVALUATE'] ): ?>
			<div style="color: green">Enabled</div>
		<?php else: ?>
			<div style="color: red">Not Found</div>
		<?php endif;
	}
	
	public static function setting_coauthor_plugin() {
		if ( self::$options['COAUTHOR_PLUGIN'] ): ?>
			<div style="color: green">Enabled</div>
		<?php else: ?>
			<div style="color: red">Not Found</div>
		<?php endif;
	}
	
	/*
	public static function setting_rating() {
		?>
		<input id="pulse_rating" name="pulse_rating" type="checkbox" <?php hidden( self::$options['CTLT_EVALUATE'] ); ?> value="<?php echo get_option('pulse_rating'); ?>" />
		<?php
	}
	*/
	
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

Pulse_CPT_Settings::init();
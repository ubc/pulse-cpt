<?php
/**
 * Settings Screen for the Pulse Custom Post Type
 */
class Pulse_CPT_Settings {
	static $options = array();
	static $bitly_username;
	static $bitly_key;
	static $breadcrumb = array();
	
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
		
		self::$bitly_username = get_option( 'pulse_bitly_username' );
		self::$bitly_key = get_option( 'pulse_bitly_key' );
		
		self::$breadcrumb['pulse_breadcrumb'] = get_option( 'pulse_breadcrumb' );
		
		add_action( 'admin_init', array( __CLASS__, 'load' ) );
		add_action( 'init', array( __CLASS__, 'start' ) );
		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );
	}
	
	public static function start() {
		if ( self::$options['CTLT_STREAM'] && class_exists('CTLT_Stream') ):
			CTLT_Stream::$add_script = true;
		endif;
	}
	
	public static function load() {
		//register settings
		//these need to be in admin_init otherwise Settings API doesn't work
		register_setting( 'pulse_options', 'pulse_bitly_username' );
		register_setting( 'pulse_options', 'pulse_bitly_key' );
		register_setting( 'pulse_options', 'pulse_default_metric' );
		register_setting( 'pulse_options', 'pulse_breadcrumb' );
		register_setting( 'pulse_options', 'pulse_breadcrumb_length' );
		
		// Main settings
		add_settings_section( 'pulse_settings_main', 'Pulse CPT Settings', array( __CLASS__, 'setting_section_main' ), 	'pulse-cpt_settings' );
		add_settings_field( 'pulse_bitly_username', 'Bit.ly Username', array( __CLASS__, 'setting_bitly_username' ), 	'pulse-cpt_settings', 'pulse_settings_main' );
		add_settings_field( 'pulse_bitly_key',      'Bit.ly API Key',  array( __CLASS__, 'setting_bitly_key' ),      	'pulse-cpt_settings', 'pulse_settings_main' );
		add_settings_field( 'pulse_breadcrumb',		'Show "In response to" for single pulse view', array( __CLASS__, 'setting_breadcrumb' ), 'pulse-cpt_settings', 'pulse_settings_main' );
		
		if ( self::$options['CTLT_EVALUATE'] == TRUE ):
			add_settings_field( 'pulse_default_metric', 'Default Pulse Metric', array( __CLASS__, 'setting_default_metric' ), 'pulse-cpt_settings', 'pulse_settings_main' );
		endif;
		
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
		
		//add in js
		wp_enqueue_script('pulse_options');
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
		<input id="pulse_bitly_username" name="pulse_bitly_username" value="<?php echo self::$bitly_username; ?>" type="text" />
		<?php
	}
	
	public static function setting_bitly_key() {
		?>
		<input id="pulse_bitly_key" name="pulse_bitly_key" value="<?php echo self::$bitly_key; ?>" type="text" />
		<br />
		<small class="clear">
			To get your <a href="http://bit.ly" target="_blank">Bit.ly</a> API key - <a href="http://bit.ly/a/sign_up" target="_blank">sign up</a> and view your <a href="http://bit.ly/a/your_api_key/" target="_blank">API key.</a>
		</small>
		<?php
	}
	
	public static function setting_default_metric() {
		?>
		<select id="pulse_default_metric" name="pulse_default_metric">
			<option value="">None</option>
			<?php
				global $wpdb;
				$metrics = $wpdb->get_results( 'SELECT * FROM '.EVAL_DB_METRICS );
				$value = get_option( 'pulse_default_metric' );
				$has_valid_metrics = false;
				
				foreach ( $metrics as $metric ):
					$params = unserialize( $metric->params );
					
					if ( ! array_key_exists( 'content_types', $params ) ):
						continue; // Metric has no association, move on..
					endif;
					
					$content_types = $params['content_types'];
					if ( in_array( 'pulse-cpt', $content_types ) && $metric->type != 'poll' ):
						$selected = ( $value == $metric->slug );
						
						if ( $metric->slug == "pulse_rating" ): // The metric with this slug is the default default.
							$selected = $selected || ! isset( $value );
						endif;
						?>
						<option value="<?php echo $metric->slug; ?>" <?php selected( $selected ); ?>>
							<?php echo $metric->nicename; ?>
						</option>
						<?php
						$has_valid_metrics = true;
					endif;
				endforeach;
			?>
		</select>
		<?php if ( ! $has_valid_metrics ): ?>
			<br />
			<small class="clear" style="color: darkred;">
				No valid metrics were found. <a href="admin.php?page=evaluate-new">Add a new</a> non-poll metric, to use the Evaluate integration.
			</small>
		<?php endif; ?>
		<br />
		<small class="clear">
			Any Pulse widget that leaves their Pulse Rating as Default, will use the metric chosen above.
		</small>
		<?php
	}
	
	public static function setting_stream_plugin() {
		?>
		<?php if ( self::$options['CTLT_STREAM'] == true ): ?>
			<div style="color: green">Enabled</div>
		<?php else: ?>
			<div style="color: red">Not Found</div>
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

	public static function setting_breadcrumb() {
			?><input id="pulse_breadcrumb" type="checkbox" name="pulse_breadcrumb" value="1" <?php checked( !empty(self::$breadcrumb['pulse_breadcrumb']) ); ?> /><?php 
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

Pulse_CPT_Settings::init();
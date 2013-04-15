<?php
class Pulse_CPT_Form_Widget extends WP_Widget {
	public static $widgets = array();
	public static $quantity = 0;
	
	public static function init() {
		add_action( 'widgets_init', array( __CLASS__, 'load' ) );
	}
	
	public static function load() {
		register_widget( __CLASS__ );
	}
	
	public function __construct() {
		parent::__construct( 'pulse_cpt', 'Pulse Form', array(
			'description' => __( 'A simple way to add new pulses', 'pulse_cpt' ),
		) );
	}
	
	/**
	 * update function.
	 * 
	 * @access public
	 * @param mixed $new_instance
	 * @param mixed $old_instance
	 * @return void
	 */
	function update( $new_instance, $old_instance ) {
		$tabs = array();
		if ( (bool) $new_instance['enable_tagging']      ) $tabs['tagging'] = true;
		if ( (bool) $new_instance['enable_co_authoring'] ) $tabs['co_authoring'] = true;
		if ( false                                       ) $tabs['file_upload'] = true; // todo: implement file uploading ui
		
		return array_merge( $old_instance, array(
			'title'                  => strip_tags( $new_instance['title'] ),
			'display_title'          => (bool) $new_instance['display_title'],
			'placeholder'            => strip_tags( $new_instance['placeholder'] ),
			'enable_character_count' => (bool) $new_instance['enable_character_count'],
			'num_char'               => (int) $new_instance['num_char'],
			'enable_url_shortener'   => (bool) $new_instance['enable_url_shortener'],
			'bitly_user'             => get_option( 'pulse_bitly_username' ),
			'bitly_api_key'          => get_option( 'pulse_bitly_key' ),
			'enable_replies'         => (bool) $new_instance['enable_comments'],
			'rating_metric'          => $new_instance['rating_metric'],
			'tabs'                   => $tabs,
		) );
	}
	
	/**
	 * form function.
	 * 
	 * @access public
	 * @param mixed $instance
	 * @return void
	 */
	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array(
		    'title'                  => '',
		    'display_title'          => false,
		    'placeholder'            => 'What is on your mind?',
		    'enable_character_count' => false,
		    'num_char'               => 140,
		    'enable_url_shortener'   => false,
		    'bitly_user'             => get_option( 'pulse_bitly_username' ),
		    'bitly_api_key'          => get_option( 'pulse_bitly_key' ),
		    'rating_metric'          => false,
		    'enable_replies'         => false,
			'tabs'                   => array(
				'tagging'      => true,
				'co_authoring' => true,
				'file_upload'  => true,
			),
	    ) );
		
		?>
			<!-- Title -->
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>">
					Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($instance['title']); ?>" />
				</label>
				<label for="<?php echo $this->get_field_id('display_title'); ?>">
					<input  id="<?php echo $this->get_field_id('display_title'); ?>" name="<?php echo $this->get_field_name('display_title'); ?>" type="checkbox" <?php echo checked($instance['display_title']); ?> /> Display Title
				</label>
			</p>
			<!-- Placeholder -->
			<p>
				<label for="<?php echo $this->get_field_id('placeholder'); ?>">
					Placeholder: <input class="widefat" id="<?php echo $this->get_field_id('placeholder'); ?>" name="<?php echo $this->get_field_name('placeholder'); ?>" type="text" value="<?php echo esc_attr($instance['placeholder']); ?>" />
				</label>
			</p>
			<!-- Character Count -->
			<p>
				<label for="<?php echo $this->get_field_id('enable_character_count'); ?>">
					<input  id="<?php echo $this->get_field_id('enable_character_count'); ?>" name="<?php echo $this->get_field_name('enable_character_count'); ?>" type="checkbox"<?php echo checked($instance['enable_character_count']); ?> /> Limit Character Count
				</label>
				<br />
				<input  id="<?php echo $this->get_field_id('num_char'); ?>" name="<?php echo $this->get_field_name('num_char'); ?>" type="text" value="<?php echo esc_attr($instance['num_char']); ?>" />
				<br />
				<small class="clear">A counter restricting the number of characters a person can enter.</small>
				<br />
			</p>
			<!-- Enable Tagging -->
			<p>
				<label for="<?php echo $this->get_field_id('enable_tagging'); ?>">
					<input  id="<?php echo $this->get_field_id('enable_tagging'); ?>" name="<?php echo $this->get_field_name('enable_tagging'); ?>" type="checkbox" <?php echo checked($instance['tabs']['tagging']); ?> /> Enable Tagging
				</label>
				<br />
				<small>Pulse authors can add tags to the pulse</small>
			</p>
			<!-- URL Shortening -->
			<p>
				<label for="<?php echo $this->get_field_id('enable_url_shortener'); ?>">
					<input  id="<?php echo $this->get_field_id('enable_url_shortener'); ?>" name="<?php echo $this->get_field_name('enable_url_shortener'); ?>" type="checkbox"<?php echo checked($instance['enable_url_shortener']); ?> /> Enable URL Shortening
				</label>
				<br />
				<small>Make sure to set your Bit.ly Username and API Key in <a href="<?php echo admin_url('edit.php?post_type=pulse-cpt&page=pulse-cpt_settings'); ?>">Pulse Settings.</a></small>
			</p>
			<!-- Enable Evaluate Rating -->
			<p>
				<label for="<?php echo $this->get_field_id('rating_metric'); ?>">
					Pulse Rating
				</label>
				<?php if ( ! Pulse_CPT_Settings::$options['CTLT_EVALUATE'] ): // Evaluate plugin is not enabled  ?>
					<br />
					<small style="color: darkred;">
						Install <a href="http://wordpress.org/extend/plugins/evaluate/">Evaluate</a> to use this functionality.
					</small>
				<?php endif; ?>
				<br />
				<select id="<?php echo $this->get_field_id('rating_metric'); ?>" name="<?php echo $this->get_field_name('rating_metric'); ?>" <?php disabled( ! Pulse_CPT_Settings::$options['CTLT_EVALUATE'] ); ?>>
					<option value="">None</option>
					<?php
						$selected = ( $instance['rating_metric'] == "default" || ! isset( $instance['rating_metric'] ) ) && Pulse_CPT_Settings::$options['CTLT_EVALUATE'] == true;
						$default = get_option( 'pulse_default_metric' );
					?>
					<option value="default" <?php selected( $selected ); ?>>
						Default (<?php echo ( empty( $default ) ? "no metric" : $default ); ?>)
					</option>
					<?php
						if ( Pulse_CPT_Settings::$options['CTLT_EVALUATE'] ): // Evaluate plugin is enabled
							global $wpdb;
							$metrics = $wpdb->get_results( 'SELECT * FROM '.EVAL_DB_METRICS );
							
							foreach ( $metrics as $metric ):
								$params = unserialize( $metric->params );
								
								if ( ! array_key_exists( 'content_types', $params ) ):
									continue; // Metric has no association, move on..
								endif;
								
								$content_types = $params['content_types'];
								if ( in_array( 'pulse-cpt', $content_types ) && $metric->type != 'poll' ):
									?>
									<option value="<?php echo $metric->slug; ?>" <?php selected( $instance['rating_metric'] == $metric->slug ); ?>>
										<?php echo $metric->nicename; ?>
									</option>
									<?php
								endif;
							endforeach;
						endif;
					?>
				</select>
				<br />
				<small>Viewers can rate each pulse.</small>
			</p>
			<!-- Enable Co Authoring -->
			<p>
				<label for="<?php echo $this->get_field_id('enable_co_authoring'); ?>">
					<input  id="<?php echo $this->get_field_id('enable_co_authoring'); ?>" name="<?php echo $this->get_field_name('enable_co_authoring'); ?>" type="checkbox" <?php checked( Pulse_CPT_Settings::$options['COAUTHOR_PLUGIN'] && $instance['tabs']['co_authoring'] ); ?> <?php disabled( ! Pulse_CPT_Settings::$options['COAUTHOR_PLUGIN'] ); ?> />
					Enable Co Authoring
				</label>
				<?php if ( ! Pulse_CPT_Settings::$options['COAUTHOR_PLUGIN'] ): // co authoring plugin is not enabled  ?>
					<br />
					<small style="color: darkred;">
						Install <a href="http://wordpress.org/extend/plugins/co-authors-plus/">Co-Authors Plus</a> to use this functionality.
					</small>
				<?php endif; ?>
				<br />
				<small>Pulse authors can add others as contributing authors</small>
			</p>
		<?php
	}
	
	/**
	 * widget function.
	 * 
	 * @access public
	 * @param mixed $args
	 * @param mixed $instance
	 * @return void
	 */
	function widget( $args, $instance ) {
		global $current_user;
		
		if ( self::$quantity > 0 ):
			if ( current_user_can( 'administrator' ) ):
				echo $args['before_widget']; 
				echo $args['before_title'];
				echo $instance['title'];
				echo $args['after_title'];
				?>
					<div class="pulse-widget-warning">
						<div class="error">
							Pulse CPT only supports one pulse form per page.
						</div>
						<small>
							This message is only displayed to administrators.
							<br />
							Please go to the <a href="wp-admin/widgets.php">Widgets menu</a>, and remove the excess pulse form widgets.
						</small>
					</div>
				<?php
				echo $args['after_widget']; 
			endif;
			
			return;
		else:
			self::$quantity++;
		endif;
		
		echo $args['before_widget']; 
		if ( ! empty( $instance['title'] ) && $instance['display_title'] ):
			echo $args['before_title'];
			echo $instance['title'];
			echo $args['after_title'];
		endif;
		
		if ( $current_user->ID > 0 ):
			Pulse_CPT::$add_form_script = true;
			$id = substr( $args['widget_id'], 10 );
			
			self::$widgets[$id] = array(
				'id'                     => $args['widget_id'],
				'enable_character_count' => (bool) $instance['enable_character_count'],
				'num_char'               => (int) $instance['num_char'],
				'enable_url_shortener'   => (bool) $instance['enable_url_shortener'],
				'bitly_user'             => get_option('pulse_bitly_username'),
				'bitly_api_key'          => get_option('pulse_bitly_key'),
				'rating_metric'          => $instance['rating_metric'],
				'tabs'                   => $instance['tabs'],
			);
			
			$has_tabs_bar = ! empty( $instance['tabs'] ) || $instance['enable_character_count'];
			$tabs_class = ( $has_tabs_bar ? "tabs" : "no-tabs" );
			?>
			<div class="postbox-placeholder">Reply to Current</div>
			<div class="postbox <?php echo $tabs_class; ?>">
				<form action="" method="post" name="new-post" class="pulse-form">
					<div class="pulse-form-input">
						<textarea rows="4" tabindex="1" class="autogrow" name="posttext" placeholder="<?php echo $instance['placeholder']; ?>"></textarea>
					</div>
					
					<?php if ( $instance['enable_url_shortener'] && ! empty( Pulse_CPT_Settings::$bitly_username ) && ! empty( Pulse_CPT_Settings::$bitly_key ) ): ?>
						<div class="pulse-shorten-url">
							<a href="#shorten-url">shorten url</a>
						</div>
					<?php endif; ?>
					
					<?php if ( ! empty( $instance['tabs'] ) ): ?>
						<div class="pulse-tags-shell tagbox-display-shell"></div>
						<div class="pulse-author-shell tagbox-display-shell"></div>
						<div class="pulse-file-shell tagbox-display-shell"></div>
					<?php endif; ?>
						
					<?php if ( $has_tabs_bar ): ?>
						<div class="pulse-tabs">
							<?php if ( $instance['tabs']['tagging'] ): ?>
								<div id="tabs-1">
									<input type="hidden" placeholder="Seperate tags by commas" class="pulse-textarea-tags pulse-meta-textarea" name="tags" />
								</div>
							<?php endif; ?>
							
							<?php if ( $instance['tabs']['co_authoring'] && Pulse_CPT_Settings::$options['COAUTHOR_PLUGIN'] ): ?>
								<div id="tabs-2">
									<input type="hidden" placeholder="People you are posting with" class="pulse-textarea-author pulse-meta-textarea" name="author" />
								</div>
							<?php endif; ?>
							
							<?php if ( $instance['tabs']['file_uploads'] ): ?>
								<div id="tabs-3">
									file upload
								</div>
							<?php endif; ?>
							
							<ul role="tablist" class="pulse-tablist">
								<?php if ( $instance['tabs']['tagging'] ): ?>
									<li><a href="#tabs-1" class="pulse-tabs-tags">tags</a></li>
								<?php endif; ?>
								
								<?php if ( $instance['tabs']['co_authoring'] && Pulse_CPT_Settings::$options['COAUTHOR_PLUGIN'] ): ?>
									<li><a href="#tabs-2" class="pulse-tabs-author">authors</a></li>
								<?php endif; ?>
								
								<?php if ( $instance['tabs']['file_uploads'] ): ?>
									<li><a href="#tabs-3" class="pulse-tabs-file">file</a></li>
								<?php endif; ?>
							</ul>
						</div>
					<?php endif; ?>
					
					<div class="pulse-form-submit-wrap">
						<?php if ( $instance['enable_character_count'] ): ?>
							<span class="pulse-form-counter"><?php echo $instance['num_char']; ?></span>
						<?php endif; ?>
						<span class="pulse-form-progress hide">
							<img title="Loading..." alt="Loading..." src="<?php echo PULSE_CPT_DIR_URL;?>/img/spinner.gif" />
						</span>					
						<input type="submit" value="Post it" tabindex="3" class="pulse-form-submit" />
					</div>
					<input type="hidden" value="<?php echo $instance['enable_location_sensitive']; ?>" name="location_sensitive" />
					<input type="hidden" value="<?php echo $instance['enable_comments']; ?>" name="enable_comments" />
					<input type="hidden" value="pulse_cpt_insert" name="action" />
					<?php wp_nonce_field( 'wpnonce_pulse_form', '_wpnonce_pulse_form' ); ?>
					
					<?php $location = Pulse_CPT_Form_Widget::get_location(); ?>
					<?php if ( $location ): ?> 
						<input type="hidden" value="<?php echo $location['type']; ?>" name="location[type]" />
						<input type="hidden" value="<?php echo $location['ID']; ?>" name="location[ID]" />
					<?php endif; ?>
					
					<?php 
						if ( Pulse_CPT_Settings::$options['CTLT_EVALUATE'] ):
							wp_nonce_field( 'evaluate_pulse-meta', 'evaluate_nonce' );
						endif;
					?>
					<input type="hidden" value="<?php echo $id; ?>" name="widget_id" class="widget-id"></input>
				</form>
			</div>
		<?php endif; ?>
		<div class="pulse-list-actions">
			<span class="pulse-list-filter show">
				<label>
					show:
				</label>
				<select dir="rtl">
					<option value="">all</option>
					<option value="user_<?php echo wp_get_current_user()->user_login;?>">mine</option>
					<option value="vote">voted</option>
					<?php if ( is_single() ): ?> 
						<option value="user_<?php the_author_meta( 'user_login' ); ?>">author's</option>
					<?php endif; ?>
					<option value="admin">admin's</option>
				</select>
			</span>
			<span class="pulse-list-filter sort">
				<label>
					sort:
				</label>
				<select dir="rtl">
					<option value="">chronological</option>
					<option value="score">popular</option>
					<option value="votes">controversial</option>
				</select>
			</span>
		</div>
		<div class="pulse-list">
			<?php 
				$the_query = new WP_Query( Pulse_CPT::query_arguments() );
				
				// The Loop
				while ( $the_query->have_posts() ):
					$the_query->the_post();
					Pulse_CPT::the_pulse( Pulse_CPT::the_pulse_array( $instance['rating_metric'] ) );
				endwhile;
				
				// Reset Post Data
				wp_reset_postdata();
			?>
		</div>
		<?php
		
		echo $args['after_widget'];
		self::footer( $instance );
	}
  	
  	/**
  	 * footer function.
  	 * 
  	 * @access public
  	 * @static
  	 * @return void
  	 */
  	public static function footer( $instance ) {
  		$it = Pulse_CPT::the_pulse_array_js( $instance['rating_metric'] );
  		?>
  		<script id="pulse-cpt-single" type="text/x-dot-template"><?php Pulse_CPT::the_pulse( $it, TRUE ); ?></script>
  		<?php 
  	}
	
	/**
	 * get_location function.
	 * 
	 * @access public
	 * @static
	 * @return void
	 */
	public static function get_location() {
		if ( is_singular() || is_page() ):
			return array(
				'type' => 'singular',
				'ID'   => get_the_ID(),
			);
		elseif ( is_category() ):
			$term = get_queried_object();
			return array(
				'type' => 'category',
				'ID' => $term->term_id,
			);
		endif;
		
		return false;
	}
}

Pulse_CPT_Form_Widget::init();
<?php

global $pulse_cpt_widget_ids;

class Pulse_CPT_Form_Widget extends WP_Widget {
	
	public function __construct() {
		parent::__construct(
			'pulse_cpt', // Base ID
			'Pulse Form', // Name
			array( 'description' => __( 'A way to simply add new pulses', 'pulse_cpt' ) ) // Args
		);
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
		$instance = $old_instance;
		$instance['title']                     = strip_tags($new_instance['title']);
		$instance['display_title']             = (bool) $new_instance['display_title'];
		$instance['placeholder']               = strip_tags($new_instance['placeholder']);
		$instance['enable_character_count']    = (bool) $new_instance['enable_character_count'];
		$instance['num_char']                  = (int) $new_instance['num_char'];
		$instance['enable_url_shortener']      = (bool) $new_instance['enable_url_shortener'];
		$instance['bitly_user']                = get_option('pulse_bitly_username');
		$instance['bitly_api_key']             = get_option('pulse_bitly_key');
		$instance['enable_tagging']            = (bool) $new_instance['enable_tagging'];
		$instance['enable_co_authoring']       = (bool) $new_instance['enable_co_authoring'];
		$instance['enable_file_uploads']       = false; // todo: implement file uploading ui(bool) $new_instance['enable_file_uploads'];
		$instance['enable_location_sensitive'] = (bool) $new_instance['enable_location_sensitive'];
		$instance['enable_comments']           = (bool) $new_instance['enable_comments'];
		$instance['rating_metric']             = $new_instance['rating_metric'];
		
		return $instance;
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
		    'title'                     => '',
		    'display_title'             => false,
		    'placeholder'               => 'What is on your mind?',
		    'enable_character_count'    => false,
		    'num_char'                  => 140,
		    'enable_url_shortener'      => false,
		    'bitly_user'                => get_option('pulse_bitly_username'),
		    'bitly_api_key'             => get_option('pulse_bitly_key'),
		    'rating_metric'             => false,
		    'enable_tagging'            => false,
		    'enable_co_authoring'       => false,
		    'enable_file_uploads'       => false,
		    'enable_location_sensitive' => false,
		    'enable_comments'           => false,
	    ) );
		
		extract($instance);
		
		?>
			<!-- Title -->
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label>
				<label for="<?php echo $this->get_field_id('display_title'); ?>"> <input  id="<?php echo $this->get_field_id('display_title'); ?>" name="<?php echo $this->get_field_name('display_title'); ?>" type="checkbox"<?php echo checked($display_title); ?> /> Display Title</label>
			</p>
			<!-- Placeholder -->
			<p>
				<label for="<?php echo $this->get_field_id('placeholder'); ?>">Placeholder: <input class="widefat" id="<?php echo $this->get_field_id('placeholder'); ?>" name="<?php echo $this->get_field_name('placeholder'); ?>" type="text" value="<?php echo esc_attr($placeholder); ?>" /></label>
			</p>
			<!-- Character Count -->
			<p>
				<label for="<?php echo $this->get_field_id('enable_character_count'); ?>"> <input  id="<?php echo $this->get_field_id('enable_character_count'); ?>" name="<?php echo $this->get_field_name('enable_character_count'); ?>" type="checkbox"<?php echo checked($enable_character_count); ?> /> Character Count</label>
				<br />
				<label for="<?php echo $this->get_field_id('num_char'); ?>"> Number of Characters: <input  id="<?php echo $this->get_field_id('num_char'); ?>" name="<?php echo $this->get_field_name('num_char'); ?>" type="text" value="<?php echo esc_attr($num_char); ?>" /></label>
				<br />
				<small class="clear">A counter restricting the number of characters a person can enter.</small>
				<br />
			</p>
			<!-- URL Shortening -->
			<p>
				<label for="<?php echo $this->get_field_id('enable_url_shortener'); ?>"> <input  id="<?php echo $this->get_field_id('enable_url_shortener'); ?>" name="<?php echo $this->get_field_name('enable_url_shortener'); ?>" type="checkbox"<?php echo checked($enable_url_shortener); ?> /> Enable URL Shortening</label>
				<br />
				<small>Make sure to set your Bit.ly Username and API Key in <a href="<?php echo admin_url('edit.php?post_type=pulse-cpt&page=pulse-cpt_settings'); ?>">Pulse Settings.</a></small>
			</p>
			<!-- Enable Tagging -->
			<p>
				<label for="<?php echo $this->get_field_id('enable_tagging'); ?>"> <input  id="<?php echo $this->get_field_id('enable_tagging'); ?>" name="<?php echo $this->get_field_name('enable_tagging'); ?>" type="checkbox"<?php echo checked($enable_tagging); ?> /> Enable Tagging</label>
				<br />
				<small>Pulse authors can add tags to the pulse</small>
			</p>
			<?php if ( Pulse_CPT_Settings::$options['CTLT_EVALUATE'] ): // co authoring plugin is enabled  ?>
				<!-- Enable Evaluate Rating -->
				<p>
					<label for="<?php echo $this->get_field_id('rating_metric'); ?>">
						Pulse Rating
					</label>
					<br />
					<select id="<?php echo $this->get_field_id('rating_metric'); ?>" name="<?php echo $this->get_field_name('rating_metric'); ?>">
						<option value="">Disabled</option>
						<?php
							global $wpdb;
							$metrics = $wpdb->get_results( 'SELECT * FROM '.EVAL_DB_METRICS );
							
							foreach ( $metrics as $metric ):
								$params = unserialize( $metric->params );
								
								if ( ! array_key_exists( 'content_types', $params ) ):
									continue; //metric has no association, move on..
								endif;
								
								$content_types = $params['content_types'];
								if ( in_array( 'pulse-cpt', $content_types ) && $metric->type != 'poll' ): //not excluded
									?>
									<option value="<?php echo $metric->slug; ?>" <?php selected( $rating_metric == $metric->slug ); ?>>
									<?php echo $metric->nicename; ?>
									</option>
									<?php
								endif;
							endforeach;
						?>
					</select>
					<br />
					<small>Viewers can rate each pulse.</small>
				</p>
			<?php else: // enable co-authoring plugin enable this functionality  ?>
				Enable the <a href="http://wordpress.org/extend/plugins/evaluate/">Evaluate plugin</a> to use this functionality.
			<?php endif; ?>
			<?php if ( Pulse_CPT_Settings::$options['COAUTHOR_PLUGIN'] ): // co authoring plugin is enabled  ?>
				<!-- Enable Co Authoring -->
				<p>
					<label for="<?php echo $this->get_field_id('enable_co_authoring'); ?>">
						<input  id="<?php echo $this->get_field_id('enable_co_authoring'); ?>" name="<?php echo $this->get_field_name('enable_co_authoring'); ?>" type="checkbox"<?php echo checked($enable_co_authoring); ?> />
						Enable Co Authoring
					</label>
					<br />
					<small>Pulse authors can add others as contributing authors</small>
				</p>
			<?php else: // enable co-authoring plugin enable this functionality  ?>
				Enable the <a href="http://wordpress.org/extend/plugins/co-authors-plus/">Co-Authors Plus plugin</a> to use this functionality.
			<?php endif; ?>
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
		global $current_user, $pulse_cpt_widget_ids;
		
		extract( $instance );
		extract( $args, EXTR_SKIP );
		
		echo $before_widget; 
		if ( ! empty( $title ) && $display_title ):
			echo $before_title . $title . $after_title;
		endif;
		
		if ( $current_user->ID > 0 ):
			Pulse_CPT::$add_form_script = true;
			$id = substr( $widget_id, 10 );
			
			$pulse_cpt_widget_ids[$id] = array(
				'id'                     => $widget_id,
				'enable_character_count' => (bool) $enable_character_count,
				'num_char'               => (int) $num_char,
				'enable_url_shortener'   => (bool) $enable_url_shortener,
				'bitly_user'             => get_option('pulse_bitly_username'),
				'bitly_api_key'          => get_option('pulse_bitly_key'),
				'rating_metric'          => $instance['rating_metric'],
				'enable_tagging'         => $enable_tagging,
				'enable_co_authoring'    => $enable_co_authoring,
				'enable_file_uploads'    => $enable_file_uploads,
				'enable_tabs'            => (bool) ( $enable_tagging || $enable_co_authoring || $enable_file_uploads ),
			);
			
			?>
			<div class="postbox-placeholder">Reply to Current</div>
			<div class="postbox">
				<form action="" method="post" name="new-post" class="pulse-form">
					<textarea cols="60" rows="4" tabindex="1" class="pulse-form-textarea autogrow" name="posttext" placeholder="<?php echo $placeholder; ?>"></textarea>
					<?php if ( $enable_url_shortener ): ?>
						<div class="pulse-shorten-url">
							<a href="#shorten-url">shorten url</a>
						</div>
					<?php endif; ?>
					
					<?php if ( $enable_tagging || ( $enable_co_authoring && Pulse_CPT_Settings::$options['COAUTHOR_PLUGIN'] ) || $enable_file_uploads ): ?>
						<div class="pulse-tags-shell tagbox-display-shell"></div>
						<div class="pulse-author-shell tagbox-display-shell"></div>
						<div class="pulse-file-shell tagbox-display-shell"></div>
						
						<div class="pulse-tabs">
							<?php if ( $enable_tagging ): ?>
								<div id="tabs-1">
									<input type="text" placeholder="Seperate tags by commas" class="pulse-textarea-tags pulse-meta-textarea" name="tags" />
								</div>
							<?php endif; ?>
							
							<?php if ( $enable_co_authoring && Pulse_CPT_Settings::$options['COAUTHER_PLUGIN'] ): ?>
								<div id="tabs-2">
									<input type="text" placeholder="People you are posting with" class="pulse-textarea-author pulse-meta-textarea" name="author" />
								</div>
							<?php endif; ?>
							
							<?php if ( $enable_file_uploads ): ?>
								<div id="tabs-3">
									file upload
								</div>
							<?php endif; ?>
							
							<ul>
								<?php if ( $enable_tagging ): ?>
									<li><a href="#tabs-1" class="pulse-tabs-tags">tags</a></li>
								<?php endif; ?>
								
								<?php if ( $enable_co_authoring && Pulse_CPT_Settings::$options['COAUTHER_PLUGIN'] ): ?>
									<li><a href="#tabs-2" class="pulse-tabs-author">author</a></li>
								<?php endif; ?>
								
								<?php if ( $enable_file_uploads ): ?>
									<li><a href="#tabs-3" class="pulse-tabs-file">file</a></li>
								<?php endif; ?>
							</ul>
						</div>
					<?php endif; ?>
					
					<div class="pulse-form-submit-wrap">
						<?php if ( $enable_character_count ): ?>
							<span class="pulse-form-counter"><?php echo $num_char; ?></span>
						<?php endif; ?>
						<span class="pulse-form-progress hide">
							<img title="Loading..." alt="Loading..." src="<?php echo  PULSE_CPT_DIR_URL;?>/img/spinner.gif" />
						</span>					
						<input type="submit" value="Post it" tabindex="3" class="pulse-form-submit" id="submit-pulse" />
					</div>
					<input type="hidden" value="<?php echo $enable_location_sensitive; ?>" name="location_sensitive" />
					<input type="hidden" value="<?php echo $enable_comments; ?>" name="enable_comments" />
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
				</form>
				<div class="clear"></div>
			</div>
		<?php endif; ?>
		<div class="pulse-list">
			<?php 
			$the_query = new WP_Query( Pulse_CPT::query_arguments() );
			
			// The Loop
			while ( $the_query->have_posts() ):
				$the_query->the_post();
				Pulse_CPT::the_pulse( null, $instance['rating_metric'] );
			endwhile;
			
			// Reset Post Data
			wp_reset_postdata();
			?>
		</div>
		<?php
		
		echo $after_widget;
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
			return array( 'type' => 'singular', 'ID' => get_the_ID() );
		elseif ( is_category() ):
			$term = get_queried_object();
			return array( 'type' => 'category', 'ID' => $term->term_id );
		endif;
		
		return false;
	}
}

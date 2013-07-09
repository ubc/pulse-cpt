<?php
class Pulse_CPT_Form_Widget extends WP_Widget {
	public static $widgets = array();
	public static $quantity = 0;
	
	public static function init() {
		add_action( 'widgets_init', array( __CLASS__, 'load' ) );
		
		// Ajax request handler for getting pulse replies
		add_action( 'wp_ajax_pulse_cpt_replies',        array( __CLASS__, 'ajax_replies' ) );
		add_action( 'wp_ajax_nopriv_pulse_cpt_replies', array( __CLASS__, 'ajax_replies' ) );
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
			'display_content_rating' => $new_instance['display_content_rating'],
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
			'display_content_rating' => false,
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
							$metrics = $wpdb->get_results( 'SELECT * FROM '.EVAL_DB_METRICS.' WHERE type <> "poll"' );
							
							foreach ( $metrics as $metric ):
								$params = unserialize( $metric->params );
								
								if ( ! array_key_exists( 'content_types', $params ) ):
									continue; // Metric has no association, move on..
								endif;
								
								$content_types = $params['content_types'];
								if ( in_array( 'pulse-cpt', $content_types ) ):
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
			<!-- Display Content Rating (Evaluate) -->
			<p>
				<label for="<?php echo $this->get_field_id('display_content_rating'); ?>">
					Display Content Rating
				</label>
				<?php if ( ! Pulse_CPT_Settings::$options['CTLT_EVALUATE'] ): // Evaluate plugin is not enabled  ?>
					<br />
					<small style="color: darkred;">
						Install <a href="http://wordpress.org/extend/plugins/evaluate/">Evaluate</a> to use this functionality.
					</small>
				<?php endif; ?>
				<br />
				<select id="<?php echo $this->get_field_id('display_content_rating'); ?>" name="<?php echo $this->get_field_name('display_content_rating'); ?>" <?php disabled( ! Pulse_CPT_Settings::$options['CTLT_EVALUATE'] ); ?>>
					<option value="">None</option>
					<?php
						if ( Pulse_CPT_Settings::$options['CTLT_EVALUATE'] ): // Evaluate plugin is enabled
							global $wpdb;
							$metrics = $wpdb->get_results( 'SELECT * FROM '.EVAL_DB_METRICS );
							
							foreach ( $metrics as $metric ):
								$params = unserialize( $metric->params );
								
								if ( ! array_key_exists( 'content_types', $params ) ):
									continue; // Metric has no association, move on..
								endif;
								
								?>
								<option value="<?php echo $metric->id; ?>" <?php selected( $instance['display_content_rating'] == $metric->id ); ?>>
									<?php echo $metric->nicename; ?>
								</option>
								<?php
							endforeach;
						endif;
					?>
				</select>
				<br />
				<small>Pulses will display the user's rating of the content the pulse is replying to. Only supports Range-type metrics.</small>
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
		global $post, $current_user;
		
		$pulse_query = new WP_Query( self::query_arguments() );
		
		if ( ! $pulse_query->have_posts() && ! is_user_logged_in() ):
			return; // There are no pulses, and we can't post. Don't display anything.
		endif;
		
		$id = substr( $args['widget_id'], 10 );
		$content_identifier = Pulse_CPT::get_content_type_for_node();
		$split = explode( '/', $content_identifier, 2 );
		$content_type = $split[0];
		$content_value = $split[1];
		
		echo $args['before_widget'];
		?>
		<div class="pulse-widget">
			<?php
			if ( ! empty( $instance['title'] ) && $instance['display_title'] ):
				echo $args['before_title'];
				echo $instance['title'];
				
				if ( in_array( $content_type, array( 'author', 'tag', 'date' ) ) ):
					switch ( $content_type ):
					case 'author':
						$suffix = "authored by ".get_the_author();
						break;
					case 'tag':
						$suffix = "tagged with ".$content_value;
						break;
					case 'date':
						$suffix = "posted during ".$content_value;
						break;
					default:
						$suffix = "";
						break;
					endswitch;
					
					echo ': <span class="pulse-widget-title-suffix"> '.$suffix.'</span>';
				endif;
				
				echo $args['after_title'];
			endif;
			
			if ( self::$quantity > 0 ):
				if ( current_user_can( 'administrator' ) ):
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
						</div>
					<?php
					echo $args['after_widget'];
				endif;
				
				return;
			elseif ( $post != null && get_post_meta( $post->ID, 'pulse_cpt-enabled', TRUE ) == 'on' ):
				if ( current_user_can( 'administrator' ) ):
					?>
						<div class="pulse-widget-warning">
							<div class="error">
								Pulse CPT has been disabled on this page.
							</div>
							<small>
								This message is only displayed to administrators.
								<br />
								<a href=" <?php echo get_edit_post_link( $post->ID ); ?> ">Edit this page</a>, if you wish to enable Pulse CPT.
							</small>
						</div>
						</div>
					<?php
					echo $args['after_widget'];
				endif;
				
				return;
			elseif ( ! $pulse_query->have_posts() && current_user_can( 'administrator' ) ):
				/*
				?>
					<div class="pulse-widget-warning">
						<div class="error">
							There are no pulses on this page, therefore the widget is being hidden from anonymous users.
						</div>
						<small>
							This message is only displayed to administrators.
							<br />
							Post a pulse on this page to make the pulse area visible to anonymous users.
						</small>
					</div>
				<?php
				*/
			endif;
			
			self::$quantity++;
			
			if ( $instance['rating_metric'] == 'default' ):
				$instance['rating_metric'] = get_option( 'pulse_default_metric' );
			endif;
			
			if ( class_exists('Evaluate') ):
				$metric_data = Evaluate::get_data_by_slug( $instance['rating_metric'] );
			else:
				$metric_data = array();
			endif;
			
			if ( $current_user->ID > 0 ):
				Pulse_CPT::$add_form_script = true;
				
				self::$widgets[$id] = array(
					'id'                     => $args['widget_id'],
					'enable_character_count' => (bool) $instance['enable_character_count'],
					'num_char'               => (int) $instance['num_char'],
					'enable_url_shortener'   => (bool) $instance['enable_url_shortener'],
					'bitly_user'             => get_option('pulse_bitly_username'),
					'bitly_api_key'          => get_option('pulse_bitly_key'),
					'rating_metric'          => $instance['rating_metric'],
					'display_content_rating' => $instance['display_content_rating'],
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
							<input type="submit" value="Post it" tabindex="3" class="pulse-form-submit btn button" />
						</div>
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
						<input type="hidden" value="<?php echo $content_identifier; ?>" name="content_type" class="content-type"></input>
					</form>
				</div>
			<?php else: ?>
				<input type="hidden" value="<?php echo $id; ?>" name="widget_id" class="widget-id"></input>
			<?php endif; ?>
			
			<?php global $wp_query; ?>
			<input type="hidden" value="<?php echo $wp_query->query_vars['author_name']; ?>" name="filters[author_id]" class="author-id"></input>
			<input type="hidden" value="<?php echo $wp_query->query_vars['cat']; ?>" name="filters[cat_id]" class="cat-id"></input>
			<input type="hidden" value="<?php echo $wp_query->query_vars['tag_id']; ?>" name="filters[tag_id]" class="tag-id"></input>
			<input type="hidden" value="<?php echo ( is_archive() ? $wp_query->query_vars['year'] : "0" ); ?>" name="filters[date][year]" class="date-year"></input>
			<input type="hidden" value="<?php echo ( is_archive() ? $wp_query->query_vars['monthnum'] : "0" ); ?>" name="filters[date][monthnum]" class="date-monthnum"></input>
			<input type="hidden" value="<?php echo ( is_archive() ? $wp_query->query_vars['day'] : "0" ); ?>" name="filters[date][day]" class="date-day"></input>
			<div class="pulse-list-actions">
				<span class="pulse-form-progress hide">
					<img title="Loading..." alt="Loading..." src="<?php echo PULSE_CPT_DIR_URL;?>/img/spinner.gif" />
				</span>	
				<span class="pulse-list-filter show">
					<label>show:</label>
					<select>
						<option value="">all</option>
						<?php if ( is_user_logged_in() && $content_type != 'author' ): ?> 
							<option value="user_<?php echo wp_get_current_user()->user_login;?>">mine</option>
						<?php endif; ?>
						<?php if ( ! $metric_data->require_login || is_user_logged_in() ): ?>
							<option value="vote">voted</option>
						<?php endif; ?>
						<?php if ( is_single() ): ?>
							<option value="user_<?php the_author_meta( 'user_login' ); ?>">author's</option>
						<?php endif; ?>
						<?php if ( $content_type != 'author' ): ?> 
							<option value="admin">admin's</option>
						<?php endif; ?>
					</select>
				</span>
				<span class="pulse-list-filter sort">
					<label>sort:</label>
					<select>
						<option value="/DESC">newest</option>
						<option value="/ASC">oldest</option>
						<?php if ( ! empty( $instance['rating_metric'] ) && Pulse_CPT_Settings::$options['CTLT_EVALUATE'] ): ?>
							<?php
								if ( $metric_data->type == 'two-way' ):
									$order = 'ASC';
								else:
									$order = 'DESC';
								endif;
							?>
							<option value="score/<?php echo $order; ?>">popular</option>
							<?php if ( $metric_data->type != 'one-way' ): ?>
								<option value="controversy">controversial</option>
							<?php endif; ?>
						<?php endif; ?>
					</select>
				</span>
			</div>
			<div class="pulse-list">
				<?php
					while ( $pulse_query->have_posts() ):
						$pulse_query->the_post();
						Pulse_CPT::the_pulse( Pulse_CPT::the_pulse_array( $instance ), false, false );
					endwhile;
					
					// Reset Post Data
					wp_reset_postdata();
				?>
			</div>
			<?php if ( $pulse_query->max_num_pages > 1 ): ?>
				<?php self::pagination( $pulse_query->max_num_pages, 1 ); ?>
			<?php endif; ?>
		</div>
		<?php
		echo $args['after_widget'];
		self::footer( $instance );
	}
	
	/* Function to handle regular and nopriv ajax requests */
	public static function ajax_replies() {
		$return = array(
			'pulses' => array(),
			'pagination' => false,
		);
		
		$data = ( isset( $_POST['data'] ) ? $_POST['data'] : false );
		$data['page'] = ( empty( $data['page'] ) ? 1 : $data['page'] );
		
		if ( $data ):
			$widgets = get_option( 'widget_pulse_cpt' );
			$query_args = self::query_arguments();
			
			if ( isset( $data['parent_id'] ) ):
				$query_args['post_parent'] = $data['parent_id'];
			endif;
			
			if ( ! empty( $data['filters']['author_id'] ) ):
				$query_args['author_name'] = $data['filters']['author_id'];
				unset( $query_args['post_parent'] );
			endif;
			
			if ( ! empty( $data['filters']['cat_id'] ) ):
				$query_args['cat'] = $data['filters']['cat_id'];
			endif;
			
			if ( ! empty( $data['filters']['tag_id'] ) ):
				$query_args['tag_id'] = $data['filters']['tag_id'];
			endif;
			
			if ( ! empty( $data['filters']['date'] ) ):
				if ( ! empty( $data['filters']['date']['year'] ) ):
					$query_args['year'] = $data['filters']['date']['year'];
				endif;
				
				if ( ! empty( $data['filters']['date']['monthnum'] ) ):
					$query_args['monthnum'] = $data['filters']['date']['monthnum'];
				endif;
				
				if ( ! empty( $data['filters']['date']['day'] ) ):
					$query_args['day'] = $data['filters']['date']['day'];
				endif;
			endif;
			
			if ( ! empty( $data['order'] ) ):
				$query_args['order'] = $data['order'];
			endif;
			
			if ( ! empty( $data['sort'] ) ):
				$rating_metric = $widgets[$data['widget_id']]['rating_metric'];
				if ( $rating_metric == 'default' ):
					$rating_metric = get_option( 'pulse_default_metric' );
				endif;
				
				if ( ! empty( $rating_metric ) && Pulse_CPT_Settings::$options['CTLT_EVALUATE'] ):
					$rating_data = (array) Evaluate::get_data_by_slug( $rating_metric, get_the_ID() );
				else:
					$rating_data = array();
				endif;
				
				$query_args['orderby'] = "meta_value_num";
				$query_args['meta_key'] = 'metric-'.$rating_data['metric_id'].'-'.$data['sort'];
			endif;
			
			$posts_per_page = $query_args['posts_per_page'];
			$query_args['posts_per_page'] = -1; //Show all posts, as we are using our own method for paging.
			
			$query = new WP_Query( $query_args );
			$pulse_count = 0;
			$index = 0;
			$offset = ( empty( $data['offset'] ) ? 0 : $data['offset'] );
			
			while ( $query->have_posts() ):
				$post = $query->the_post();
				$pulse_data = Pulse_CPT::the_pulse_array( $widgets[$data['widget_id']] );
				
				if ( $index >= $offset ):
					switch ( $data['show'] ):
					case 'user':
						$valid = $data['user'] == get_the_author_meta( 'user_login' );
						
						if ( ! empty( $pulse_data['authors'] ) ):
							foreach ( $pulse_data['authors'] as $author ):
								$valid |= $data['user'] == $author['name'];
							endforeach;
						endif;
						break;
					case 'vote':
						$valid = $pulse_data['user_vote'];
						break;
					case 'admin':
						$valid = user_can( $pulse_data['author']['ID'], 'administer' );
						
						if ( ! empty( $pulse_data['authors'] ) ):
							foreach ( $pulse_data['authors'] as $author ):
								$valid |= user_can( $author['name'], 'administer' );
							endforeach;
						endif;
						break;
					default:
						$valid = true;
						break;
					endswitch;
					
					if ( $valid ):
						$return['pulses'][] = $pulse_data;
						$pulse_count += 1;
					endif;
				endif;
				
				$index += 1;
				
				if ( $pulse_count >= $posts_per_page ):
					$return['pagination'] = true;
					break;
				endif;
			endwhile;
		endif;
		
		echo json_encode( $return );
		die();
	}

	public static function pagination( $page_count, $selected = 1, $length = 12 ) {
		?>
		<div class="pulse-pagination">
			<span class="pulse-pagination-btn">
				<div id="pulse-pagination-more">Load More</span>
			</span>
		</div>
		<?php
	}
  	
  	/**
  	 * footer function.
  	 * 
  	 * @access public
  	 * @static
  	 * @return void
  	 */
  	public static function footer( $instance ) {
  		$it = Pulse_CPT::the_pulse_array_js( $instance );
  		?>
  		<script id="pulse-cpt-single" type="text/x-dot-template"><?php Pulse_CPT::the_pulse( $it, false, true ); ?></script>
  		<?php 
  	}
	
	/**
	 * query_arguments function.
	 * 
	 * @access public
	 * @static
	 * @return void
	 */
	public static function query_arguments() {
		global $wp_query;
		
		$arg = array(
			'post_type'      => 'pulse-cpt',
			'posts_per_page' => 10,
		);
		
		if ( is_date() ):
			$arg['year'] = $wp_query->query_vars['year'];
			
			if ( ! empty( $wp_query->query_vars['monthnum'] ) ):
				$arg['monthnum'] = $wp_query->query_vars['monthnum'];
			endif;
			
			if ( ! empty( $wp_query->query_vars['day'] ) ):
				$arg['day'] = $wp_query->query_vars['day'];
			endif;
		elseif ( is_author() ):
			$arg['author_name'] = $wp_query->query_vars['author_name'];
		elseif ( is_category() ):
			$arg['cat'] = $wp_query->query_vars['cat'];
		elseif ( is_tag() ):
			$arg['tag_id'] = $wp_query->query_vars['tag_id'];
		elseif ( is_single() || is_page() ):
			$arg['post_parent'] = get_the_ID();
		elseif ( is_front_page() ):
			$arg['post_parent'] = 0;
		elseif( is_404() ):
			return false; // Don't display anything
		endif;
		
		return $arg;
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
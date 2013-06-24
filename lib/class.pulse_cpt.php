<?php
/**
 * Pulse_CPT class.
 */
class Pulse_CPT {
	static $add_form_script;
	
	/**
	 * init function.
	 * 
	 * @access public
	 * @static
	 * @return void
	 */
	public static function init() {
		add_action( 'init',                   array( __CLASS__, 'load' ) );
		add_action( 'wp_footer',              array( __CLASS__, 'print_form_script' ) );
		add_action( 'wp_footer',              array( __CLASS__, 'print_pulse_script' ) );
		add_action( 'template_redirect',      array( __CLASS__, 'template_redirect' ) );
		add_filter( 'carry_content_template', array( __CLASS__, 'load_pulse_template' ) );
		
		// Add new columns
		add_filter( 'manage_pulse-cpt_posts_columns',       array( __CLASS__, 'add_new_column' ) );
		add_action( 'manage_pulse-cpt_posts_custom_column', array( __CLASS__, 'manage_columns' ), 10, 2 );
	}
	
	public static function load() {
		Pulse_CPT::register_pulse();
		
		if ( ! is_admin() ):
			Pulse_CPT::register_script_and_style();
		endif;
	}
	
	/**
	 * template_redirect function.
	 * 
	 * @access public
	 * @static
	 * @return void
	 */
	public static function template_redirect() {
		Pulse_CPT::print_form_style();
	}
	
	/**
	 * install function.
	 * 
	 * @access public
	 * @static
	 * @return void
	 */
	public static function install() {
		Pulse_CPT::register_pulse();
		flush_rewrite_rules();
	}
	
	/**
	 * register_pulse function.
	 * 
	 * @access public
	 * @static
	 * @return void
	 */
	public static function register_pulse() {
		$labels = array(
			'name'               => _x( 'Pulse', 'pulse-cpt' ),
			'singular_name'      => _x( 'Pulse', 'pulse-cpt' ),
			'add_new'            => _x( 'Add New', 'pulse-cpt' ),
			'add_new_item'       => __( 'Add New Pulse' ),
			'edit_item'          => __( 'Edit Pulse' ),
			'new_item'           => __( 'New Pulse' ),
			'all_items'          => __( 'All Pulses' ),
			'view_item'          => __( 'View Pulses' ),
			'search_items'       => __( 'Search Pulses' ),
			'not_found'          => __( 'No pulses found' ),
			'not_found_in_trash' => __( 'No pulses found in Trash' ), 
			'parent_item_colon'  => '',
			'menu_name'          => 'Pulse',
		);
		
		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true, 
			'show_in_menu'       => true, 
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'pulse' ),
			'capability_type'    => 'post',
			'has_archive'        => true, 
			'hierarchical'       => false,
			'menu_position'      => null,
			'taxonomies'         => array( 'category', 'post_tag', 'mention' ),
			'supports'           => array( 'editor', 'author', 'comments' ),
		);
		
  		register_post_type( 'pulse-cpt', $args );
    }
    
    /**
     * register_script_and_style function.
     * 
     * @access public
     * @static
     * @return void
     */
    public static function register_script_and_style() {
    	wp_register_script( 'autoGrowInput', PULSE_CPT_DIR_URL.'/js/jquery.autoGrowInput.js', array( 'jquery' ), '1.0', true );
    	wp_register_script( 'autogrow',      PULSE_CPT_DIR_URL.'/js/autogrow.js',             array( 'jquery' ), '1.0', true );
    	wp_register_script( 'tagbox',        PULSE_CPT_DIR_URL.'/js/tagbox.js',               array( 'jquery','jquery-ui-autocomplete', 'autoGrowInput' ), '1.0', true );
    	wp_register_script( 'doT',           PULSE_CPT_DIR_URL.'/js/doT.js',                  array( 'jquery' ), '1.0', true );
    	wp_register_script( 'charCount',     PULSE_CPT_DIR_URL.'/js/charCount.js',            array( 'jquery' ), '1.0', true );
    	
    	wp_register_script( 'bootstrap-popover',           PULSE_CPT_DIR_URL.'/js/bootstrap-popover.min.js',       array( 'jquery' ), '1.0', true );
    	wp_register_script( 'jquery-ui-position',          PULSE_CPT_DIR_URL.'/js/jquery.ui.position.js',          array( 'jquery' ), '1.0', true );
    	wp_register_script( 'jquery-ui-autocomplete',      PULSE_CPT_DIR_URL.'/js/jquery.ui.autocomplete.js',      array( 'jquery', 'jquery-ui-position', 'jquery-ui-widget', 'jquery-ui-core' ), '1.0', true );
    	wp_register_script( 'jquery-ui-autocomplete-html', PULSE_CPT_DIR_URL.'/js/jquery.ui.autocomplete.html.js', array( 'jquery-ui-autocomplete' ), '1.0', true );
    	
		$cachebuster = filemtime( PULSE_CPT_DIR_PATH.'/js/form.js' );
		wp_register_script( 'pulse-cpt-form', PULSE_CPT_DIR_URL.'/js/form.js?t='.$cachebuster , array( 'jquery', 'autogrow', 'tagbox', 'doT',  'jquery-ui-tabs', 'charCount', 'jquery-ui-autocomplete-html'), '1.0', true );
		$cachebuster = filemtime( PULSE_CPT_DIR_PATH.'/js/pulse.js' );
		wp_register_script( 'pulse-cpt', PULSE_CPT_DIR_URL.'/js/pulse.js?t='.$cachebuster , array( 'jquery' ), '1.0', true );
    	
		$cachebuster = filemtime( PULSE_CPT_DIR_PATH.'/css/form.css' );
    	wp_register_style( 'pulse-cpt-form', PULSE_CPT_DIR_URL.'/css/form.css?t='.$cachebuster);
		$cachebuster = filemtime( PULSE_CPT_DIR_PATH.'/css/pulse.css' );
    	wp_register_style( 'pulse-cpt-list', PULSE_CPT_DIR_URL.'/css/pulse.css?t='.$cachebuster);
		$cachebuster = filemtime( PULSE_CPT_DIR_PATH.'/css/widget.css' );
    	wp_register_style( 'pulse-cpt-widget', PULSE_CPT_DIR_URL.'/css/widget.css?t='.$cachebuster);
		
    	wp_register_style( 'bootstrap-popover', PULSE_CPT_DIR_URL.'/css/bootstrap-popover.min.css');
		wp_register_style( 'speech-bubble-icons', PULSE_CPT_DIR_URL.'/css/bubble.css');
    }
    
    /**
     * add_new_columns function.
     * 
     * @access public
     * @param mixed $columns
     * @return void
     */
    function add_new_column( $columns ) {
    	$last = array_splice( $columns, 3 );
    	$columns = array_merge( $columns , array( 'reply-to' => 'Reply To' ), $last );
    	return  $columns;
    }
    
    /**
     * manage_columns function.
     * 
     * @access public
     * @return void
     */
    function manage_columns( $column_name, $id ) {
    	global $post;
    	if ( 'reply-to' == $column_name ):
    		if ( $post->post_parent ):
    			$reply_to = get_post( $post->post_parent );
				
    			if ( 'pulse-cpt' == $reply_to->post_type ):
    				$title = "Pulse";
    			else:
    				$title = ucfirst( $reply_to->post_type ) ;
    			endif;
				$title .= " #".$reply_to->ID;
				
				$edit_url = admin_url( 'post.php?post='.$post->post_parent.'&action=edit' );
				$visit_url = site_url().'/?p='.$post->post_parent;
    			?>
				<a href="<?php echo $edit_url; ?>"><?php echo $title; ?></a>
				<div class="row-actions">
					<span>
						<a href="<?php echo $edit_url; ?>">Edit</a>
					</span> | <span>
						<a href="<?php echo $visit_url; ?>">View</a>
					</span>
				</div>
				<?php
    		endif;
    	endif;
    }
	
    /**
     * print_form_style function.
     * 
     * @access public
     * @static
     * @return void
     */
    public static function print_form_style() {
    	wp_enqueue_style( 'pulse-cpt-form' );
    	wp_enqueue_style( 'pulse-cpt-widget' );
    	wp_enqueue_style( 'pulse-cpt-list' );
    	wp_enqueue_style( 'speech-bubble-icons' );
    }
    
    /**
     * print_form_script function.
     * 
     * @access public
     * @static
     * @return void
     */
    public static function print_form_script() {
		$global_args = array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
		);
		
		if ( is_home() ):
			$global_args['id'] = 0;
		elseif ( ! is_archive() ):
			$global_args['id'] = get_the_ID();
		endif;
		
		if ( ! self::$add_form_script ): // We still need the ajax url even if user isnt logged in
			wp_localize_script( 'pulse-cpt-form', 'Pulse_CPT_Form_global', $global_args );
		else: //localize full data for logged in user
			$global_args['content_type'] = Pulse_CPT::get_content_type_for_node();
			$global_args['tags'] = Pulse_CPT_Form::get_tags();
			$global_args['authors'] = Pulse_CPT_Form::get_coauthors();
			
			wp_localize_script( 'pulse-cpt-form', 'Pulse_CPT_Form_global', $global_args );
			wp_localize_script( 'pulse-cpt-form', 'Pulse_CPT_Form_local',  Pulse_CPT_Form_Widget::$widgets );
		endif;
		
		wp_print_scripts( 'pulse-cpt-form' );
    }
	
	public static function get_content_type_for_node() {
		if ( is_single() || is_page() ):
			return "page/".get_the_ID();
		elseif ( is_front_page() ):
			return "front";
		elseif ( is_tag() ):
			return "tag/".single_tag_title( "", FALSE );
		elseif ( is_author() ):
			return "author/".get_the_author_meta( 'user_login' );
		elseif ( is_year() ):
			return "date/".get_the_date( 'Y' );
		elseif ( is_month() ):
			return "date/".get_the_date( 'F Y' );
		elseif ( is_date() ):
			return "date/".get_the_date( 'F j, Y' );
		endif;
	}
    
    /**
     * print_pulse_script function.
     * 
     * @access public
     * @static
     * @return void
     */
    public static function print_pulse_script() {
    	wp_print_scripts( 'pulse-cpt' );
    	wp_enqueue_script( 'bootstrap-popover' );
    	wp_enqueue_style( 'bootstrap-popover' );
    }
	
	/**
	 * the_pulse function.
	 * 
	 * @access public
	 * @static
	 * @param mixed $it (default: null)
	 * @return void
	 */
	public static function the_pulse( $it = null, $single = true, $template = false ) {
		if ( $it == null ):
			if ( $template ):
				$it = self::the_pulse_array_js();
			else:
				$it = self::the_pulse_array();
			endif;
		endif;
		
		?>
		<div class="pulse pulse-<?php echo $it['ID']; ?>" data-pulse-id="<?php echo $it['ID']; ?>">
			<div class="pulse-inner">
				<?php if ( ! $single ): ?>
					<div class="pulse-mini visible-collapsed">
						<a class="pulse-timestamp" href="<?php echo $it['permalink']; ?>">
							<?php echo $it['date']; ?>
						</a>
						<span class="reply-to">
							Posted on <?php echo $it['parent_link']; ?>
						</span>
						<?php echo $it['content']; ?>
					</div>
				<?php endif; ?>
				<div class="pulse-wrap pulse-margin hidden-collapsed">
					<div class="pulse-meta">
						<?php if ( ! $single ): ?>
							<div class="pulse-avatar pulse-nomargin">
								<?php if ( $template ): ?>
									{{~it.authors :value:index}}
										<div class="pulse-avatar-stack">
											{{=value.avatar}}
									{{~}}
									
									{{~it.authors :value:index}}
										</div>
									{{~}}
								<?php else: ?>
									<?php foreach( $it['authors'] as $author ): ?>
										<div class="pulse-avatar-stack">
											<?php echo $author['avatar']; ?>
									<?php endforeach; ?>
									
									<?php foreach( $it['authors'] as $author ): ?>
										</div>
									<?php endforeach; ?>
								<?php endif; ?>
							</div>
							
							<a class="pulse-timestamp" href="<?php echo $it['permalink']; ?>">
								<?php echo $it['date']; ?>
							</a>
							
							<span class="pulse-rating">
								<span class="evaluate-wrapper">
									<?php
										if ( ! empty( $it['rating']['slug'] ) && Pulse_CPT_Settings::$options['CTLT_EVALUATE'] ):
											global $wpdb;
											$metric = $wpdb->get_row( "SELECT * FROM ".EVAL_DB_METRICS." WHERE slug='".$it['rating']['slug']."'" );
											
											if ( $template ):
												$data = Evaluate::get_metric_data_js();
												$data->type = $metric->type;
											else:
												$data = Evaluate::get_metric_data( $metric );
												if ( $it['rating']['counter_up'] != null ) $data->counter_up = $it['rating']['counter_up'];
												if ( $it['rating']['counter_down'] != null ) $data->counter_down = $it['rating']['counter_down'];
											endif;
											
											echo Evaluate::display_metric( $data, $template );
										endif;
									?>
								</span>
							</span>
						<?php endif; ?>
					</div>
					
					<div class="pulse-content">
						<?php if ( ! $single ): ?>
							<?php if ( $template ): ?>
								{{? it.authors.length > 1 }}
									<div class="pulse-authors hidden-collapsed">
										<ul>
											{{~it.authors :value:index}}
												<li class="pulse-author">
													{{=value.avatar}}
													<div class="pulse-author-addendum">
														<a href="{{=value.url}}">
															{{=value.name}} <small>@{{=value.login}}</small>
														</a>
													</div>
												</li>
											{{~}}
										</ul>
									</div>
								{{??}}
									<span class="pulse-author hidden-collapsed">
										<a href="{{=value.url}}">
											{{=value.name}} <small>@{{=value.login}}</small>
										</a>
									</span>
								{{?}}
							<?php else: ?>
								<?php if ( sizeof( $it['authors'] ) > 1 ): ?>
									<div class="pulse-authors hidden-collapsed">
										<ul>
											<?php foreach( $it['authors'] as $author ): ?>
												<li class="pulse-author">
													<?php echo $author['avatar']; ?>
													<div class="pulse-author-addendum">
														<a href="<?php echo $author['url']; ?>">
															<?php echo $author['name']; ?> <small>@<?php echo $author['login']; ?></small>
														</a>
													</div>
												</li>
											<?php endforeach; ?>
										</ul>
									</div>
								<?php else: ?>
									<span class="pulse-author hidden-collapsed">
										<a href="<?php echo $author['url']; ?>">
											<?php echo $author['name']; ?> <small>@<?php echo $author['login']; ?></small>
										</a>
									</span>
								<?php endif; ?>
							<?php endif; ?>
						<?php endif; ?>
						
						<?php if ( ! empty( $it['content_rating'] ) && Pulse_CPT_Settings::$options['CTLT_EVALUATE'] ): ?>
							<span class="content-rating">
								<div class="evaluate-wrapper">
									<?php
										if ( $template ):
											echo "<!-- To be replaced via javascript. -->";
										else:
											echo Evaluate::display_metric( $it['content_rating'], $template );
										endif;
									?>
								</div>
							</span>
						<?php endif; ?>
						
						<?php echo $it['content']; ?>
					</div>
					
					<?php if ( ! $single ): ?>
						<div class="pulse-actions pulse-nomargin">
							<ul>
								<li class="hidden-collapsed">
									<a href="#expand-url" class="expand-action">Expand</a>
								</li>
								<?php if ( is_user_logged_in() ): // Display reply only if user is logged in ?>
									<li class="hidden-collapsed">
										<a href="#reply-url" class="reply-action">Reply</a>
									</li>
								<?php endif; ?>
								<li>
									<span class="pulse-reply-counter spch-bub-inside">
										<span class="point"></span>  
										<em><span class="reply-count"><?php echo $it['num_replies']; ?></span></em>
									</span>
									<span> Replies</span>
								</li>
								<li class="hidden-collapsed reply-to">
									<?php echo $it['reply_to']; ?>
								</li>
								<span class="pulse-form-progress hide">
									<img title="Loading..." alt="Loading..." src="<?php echo PULSE_CPT_DIR_URL; ?>/img/spinner.gif" />
								</span>
							</ul>
							<?php if ( $template ): ?>
								{{ if ( it.tags ) { }}
									<ul class="pulse-tags hidden-collapsed">
										{{~it.tags :value:index}}
										<li>
											<a href="{{=value.url}}">{{=value.name}}</a> 
										</li>
										{{~}}
									</ul>
								{{ } }}
							<?php else: ?>
								<?php if ( ! empty( $it['tags'] ) ): ?>
									<ul class="pulse-tags hidden-collapsed">
										<?php foreach( $it['tags'] as $tag ): ?>
											<li>
												<a href="<?php echo $tag['url']; ?>"><?php echo $tag['name']; ?></a>
											</li>
										<?php endforeach; ?>
									</ul>
								<?php endif; ?>
							<?php endif; ?>
						</div>
						
						<div class="pulse-expand-content pulse-nomargin visible-expanded">
							<div class="pulse-pivot"></div>
							<div class="pulse-replies"></div>
						</div> <!-- end of pulse-expand-content -->
					<?php else: ?>
						<div>
							Posted by 
							<?php
								if ( $template ):
									?>
									{{~it.authors :value:index}}
										<a href="{{=value.url}}">
											{{=value.name}}
										</a>
									{{~}}
									<?php
								else:
									$length = count( $it['authors'] );
									$i = 0;
									foreach( $it['authors'] as $author ):
										$i++;
										?>
										<a href="<?php echo $author['url']; ?>">
											<?php echo $author['name'].( $i < $length ? ", " : "" ); ?>
										</a>
										<?php
									endforeach;
								endif;
							?>
						</div>
						<div class="reply-to">
							<?php echo $it['reply_to']; ?>
						</div>
					<?php endif; ?>
				</div> <!-- end of pulse wrap -->
			</div>
		</div>
		<?php
	}
	
	/**
	 * the_pulse_json function.
	 * 
	 * @access public
	 * @static
	 * @return void
	 */
	public static function the_pulse_json( $widget = null ) {
		return json_encode( Pulse_CPT::the_pulse_array( $widget ) );
	}
	
	/**
	 * the_pulse_array function.
	 * 
	 * @access public
	 * @static
	 * @return void
	 */
	public static function the_pulse_array( $widget = null ) {
		global $wpdb, $post;
		$parent = $post->post_parent;
		
		// Tags
		$posttags = get_the_tags();
		
		if ( $posttags ):
			foreach( $posttags as $tag ):
				$tags[] = array(
					'name' => $tag->name,
					'url'  => get_tag_link( $tag->term_id ),
				);
			endforeach;
		else:
			$tags = false;
		endif;
		
		// Coauthors
		$authors = Pulse_CPT_Form::get_coauthors();
		
		// Evaluate
		if ( $widget['rating_metric'] == 'default' ):
			$widget['rating_metric'] = get_option( 'pulse_default_metric' );
		endif;
		
		if ( ! empty( $widget['rating_metric'] ) && Pulse_CPT_Settings::$options['CTLT_EVALUATE'] ):
			$rating_data = (array) Evaluate::get_data_by_slug( $widget['rating_metric'], get_the_ID() );
		else:
			$rating_data = array();
		endif;
		
		if ( $parent != 0 && ! empty( $widget['display_content_rating'] ) ):
			$content_rating = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM '.EVAL_DB_METRICS.' WHERE id=%s', $widget['display_content_rating'] ) );
			$excluded = get_post_meta( $parent, 'metric' );
			$parent_data = get_post( $parent );
			$params = unserialize( $content_rating->params );
			
			if ( array_key_exists( 'content_types', $params ) && ! in_array( $content_rating->id, $excluded ) && in_array( $parent_data->post_type, $params['content_types'] ) ):
				$content_rating->show_user_vote = true;
				
				$post_id = $post->ID;
				$author = get_the_author_meta( 'ID' );
				
				$post = $parent_data;
				setup_postdata( $post );
				
				$content_rating = Evaluate::get_metric_data( $content_rating, $author );
				
				$post = get_post( $post_id );
				setup_postdata( $post );
			else:
				$content_rating = null;
			endif;
		endif;
		
		// Reply To Line
		if ( is_front_page() ):
			$id = 0;
		elseif ( is_single() ):
			$id = $post->ID;
		else:
			$id = null;
		endif;
		
		if ( isset( $parent ) && $id !== $parent ):
			if ( $parent == 0 ):
				$parent_link = '<a href="'.home_url().'">Front Page</a>';
				$reply_to = 'on the '.$parent_link;
			else:
				$parent_link = '<a href="'.get_permalink( $parent ).'">'.get_the_title( $parent ).'</a>';
				$reply_to = 'in reply to '.$parent_link;
			endif;
		else:
			$parent_link = "";
			$reply_to = "";
		endif;
		
		return array_merge( $rating_data, array(
			'ID'        => get_the_ID(),
			'date'      => Pulse_CPT::get_the_date(),
			'content'   => apply_filters( 'the_content', make_clickable( get_the_content() ) ),
			'permalink' => get_permalink(),
			'author'    => array( 
				'ID'           => get_the_author_meta( 'ID' ),
				'avatar_30'    => get_avatar( get_the_author_meta( 'ID' ), '30' ),
				'user_login'   => get_the_author_meta( 'user_login' ),
				'display_name' => get_the_author(),
				'post_url'     => get_author_posts_url( get_the_author_meta('ID') ),
			),
			'tags'	         => $tags,
			'authors'        => $authors,
			'num_replies'    => self::get_num_replies(),
			'parent'         => $parent,
			'parent_link'    => $parent_link,
			'reply_to'       => $reply_to,
			'content_rating' => $content_rating,
			'rating'         => array(
				'slug'         => $widget['rating_metric'],
				'counter_up'   => 0,
				'counter_down' => 0,
			),
		) );
	}
	
	/**
	 * the_pulse_array_js function.
	 * 
	 * @access public
	 * @static
	 * @return void
	 */
	public static function the_pulse_array_js( $widget = null ) {
		if ( $widget['rating_metric'] == 'default' ):
			$widget['rating_metric'] = get_option( 'pulse_default_metric' );
		endif;
		
		if ( isset( $widget['display_content_rating'] ) ):
			$content_rating = array(
				'value'  => '{{=it.content_rating.value}}',
			);
		else:
			$content_rating = null;
		endif;
		
		return array(  
			'ID'        => '{{=it.ID}}',
			'date'      => '{{=it.date}}',
			'content'   => '{{=it.content}}',
			'permalink' => '{{=it.permalink}}',
			'author'    => array( 
				'ID'           => '{{=it.author.ID}}',
				'avatar_30'    => '{{=it.author.avatar_30}}',
				'user_login'   => '{{=it.author.user_login}}',
				'display_name' => '{{=it.author.display_name}}',
				'post_url'     => '{{=it.author.post_url}}',
			),
			'num_replies'    => '{{=it.num_replies}}',
			'parent_link'    => '{{=it.parent_link}}',
			'reply_to'       => '{{=it.reply_to}}',
			'content_rating' => $content_rating,
			'rating'         => array(
				'slug'         => $widget['rating_metric'],
				'counter_up'   => '{{=it.rating.counter_up}}',
				'counter_down' => '{{=it.rating.counter_down}}',
			),
		);
	}
	
	/**
	 * get_the_date function.
	 * 
	 * @access public
	 * @static
	 * @param mixed $date_format (default: null)
	 * @return void
	 */
	public static function get_the_date( $date_format = null ) {
		if ( $date_format ):
			$date = date ( 'Ymd', strtotime( $date_format ) );
		else:
			$date = get_the_date( 'Ymd' );
		endif;
		
		if ( $date == date('Ymd') ):
			return ( $date_format ? date( 'g:iA', strtotime( $date_format ) ) : get_the_date( 'g:iA' ) );
		else:
			return ( $date_format ? date( 'j M', strtotime( $date_format ) ) : get_the_date( 'j M' ) );
		endif;
	}
	
	/**
	 * the_date function.
	 * 
	 * @access public
	 * @static
	 * @return void
	 */
	public static function the_date() {
		echo Pulse_CPT::get_the_date();
	}
	
	/**
	 * load_pulse_template function.
	 * 
	 * @access public
	 * @static
	 * @param mixed $template
	 * @return void
	 */
	public static function load_pulse_template( $template ) {
		global $post;
		
		if ( $post->post_type == 'pulse-cpt' ):
			return 'pulse';
		else:
			return $template;
		endif;
	}
	
	/* return the number of replies for a given pulse */
	public static function get_num_replies() {
		global $post;
		return count( get_children( $post->ID ) );
	}
}

/**
 * the_pulse function.
 * 
 * @access public
 * @return void
 */
function the_pulse() {
	Pulse_CPT::the_pulse();
}

Pulse_CPT::init();
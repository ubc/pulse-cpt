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
		add_action( 'init',              array( __CLASS__, 'load' ) );
		add_action( 'admin_menu',        array( __CLASS__, 'remove_submenus' ) );
		add_action( 'wp_footer',         array( __CLASS__, 'print_form_script' ) );
		add_action( 'wp_footer',         array( __CLASS__, 'print_pulse_script' ) );
		add_action( 'template_redirect', array( __CLASS__, 'template_redirect' ) );
		// add_action( 'pre_get_posts', array( __CLASS__, 'include_pulse_cpt') );
		add_filter( 'carry_content_template', array( __CLASS__, 'load_pulse_template' ) );
		
		// Ajax request handler for getting pulse replies
		add_action( 'wp_ajax_pulse_cpt_replies',        array( __CLASS__, 'ajax_replies' ) );
		add_action( 'wp_ajax_nopriv_pulse_cpt_replies', array( __CLASS__, 'ajax_replies' ) );
		
		// Add new columns
		add_filter( 'manage_pulse-cpt_posts_columns',       array( __CLASS__, 'add_new_column' ) );
		add_action( 'manage_pulse-cpt_posts_custom_column', array( __CLASS__,'manage_columns'), 10, 2 );
	}
	
	public static function load() {
		Pulse_CPT::register_pulse();
	  
		if ( ! is_admin() ):
			Pulse_CPT::register_script_and_style();
		endif;
	}
	
	public static function remove_submenus() {
		remove_submenu_page( 'edit.php?post_type=pulse-cpt', 'edit-tags.php?taxonomy=post_tag&amp;post_type=pulse-cpt' );
		remove_submenu_page( 'edit.php?post_type=pulse-cpt', 'edit-tags.php?taxonomy=category&amp;post_type=pulse-cpt' );
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
			'name'               => _x('Pulse', 'pule-ct'),
			'singular_name'      => _x('Pulse', 'pulse-ct'),
			'add_new'            => _x('Add New', 'pulse-ct'),
			'add_new_item'       => __('Add New Pulse'),
			'edit_item'          => __('Edit Pulse'),
			'new_item'           => __('New Pulse'),
			'all_items'          => __('All Pulses'),
			'view_item'          => __('View Pulses'),
			'search_items'       => __('Search Pulses'),
			'not_found'          => __('No pulses found'),
			'not_found_in_trash' => __('No pulses found in Trash'), 
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
    	wp_register_script( 'tagbox',        PULSE_CPT_DIR_URL.'/js/tagbox.js',               array( 'jquery','jquery-ui-autocomplete', 'autoGrowInput' ), '1.0', true );
    	wp_register_script( 'autoGrowInput', PULSE_CPT_DIR_URL.'/js/jquery.autoGrowInput.js', array( 'jquery' ), '1.0', true );
    	wp_register_script( 'autogrow',      PULSE_CPT_DIR_URL.'/js/autogrow.js',             array( 'jquery' ), '1.0', true );
    	wp_register_script( 'doT',           PULSE_CPT_DIR_URL.'/js/doT.js',                  array( 'jquery' ), '1.0', true );
    	wp_register_script( 'charCount',     PULSE_CPT_DIR_URL.'/js/charCount.js',            array( 'jquery' ), '1.0', true );
    	
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
    	wp_enqueue_style( 'pulse-cpt-list' );
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
			'ajaxUrl' => admin_url('admin-ajax.php'),
		);
		
		if ( ! self::$add_form_script ): // We still need the ajax url even if user isnt logged in
			wp_localize_script( 'pulse-cpt-form', 'Pulse_CPT_Form_global', $global_args );
		else: //localize full data for logged in user
			$global_args['tags'] = Pulse_CPT_Form::get_tags();
			$global_args['authors'] = Pulse_CPT_Form::get_authors();
			
			wp_localize_script( 'pulse-cpt-form', 'Pulse_CPT_Form_global', $global_args );
			wp_localize_script( 'pulse-cpt-form', 'Pulse_CPT_Form_local',  Pulse_CPT_Form_Widget::$widgets );
		endif;
		
		wp_print_scripts( 'pulse-cpt-form' );
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
    }
	
	/**
	 * the_pulse function.
	 * 
	 * @access public
	 * @static
	 * @param mixed $it (default: null)
	 * @return void
	 */
	public static function the_pulse( $it = null ) {
		if ( $it == null ):
			$it = self::the_pulse_array();
		endif;
		
		?>
		<div class="pulse" data-pulse-id="<?php echo $it['ID']; ?>">
			<div class="pulse-wrap">
				<?php echo $it['author']['avatar_30']; ?>
				<div class="pulse-author-meta">
					<a href="<?php echo $it['author']['post_url']; ?>">
						<?php echo $it['author']['display_name']; ?> <small>@<?php echo $it['author']['user_login']; ?></small>
					</a>
				</div>
				<div class="pulse-meta">
					<a class="pulse-timestamp" href="<?php echo $it['permalink']; ?>">
						<?php echo $it['date']; ?>
					</a>
					<?php
						if ( $it['rating']['slug'] != null && Pulse_CPT_Settings::$options['CTLT_EVALUATE'] == true ):
							global $wpdb;
							$metric = $wpdb->get_row( "SELECT * FROM ".EVAL_DB_METRICS." WHERE slug='".$it['rating']['slug']."'" );
							$data = Evaluate::get_metric_data( $metric );
							if ( $it['rating']['counter_up'] != null ) $data->counter_up = $it['rating']['counter_up'];
							if ( $it['rating']['counter_down'] != null ) $data->counter_down = $it['rating']['counter_down'];
							
							echo Evaluate::display_metric( $data );
						endif;
					?>
				</div>
				<div class="pulse-content">
					<?php echo $it['content']; ?>
				</div>
				<div class="pulse-actions">
					<ul>
						<li><a href="#expand-url" class="expand-action">Expand</a></li>
						<?php if ( is_user_logged_in() ): // Display reply and favorite only if user is logged in ?>
							<li><a href="#reply-url" class="reply-action">Reply</a></li>
						<?php endif; ?>
						<li><span class="reply-count"><?php echo $it['num_replies']; ?></span> Replies</li>
					</ul>
				</div>
				<div class="pulse-expand-content">
					<?php if ( isset( $it['tags'] )  && is_array( $it['tags'] ) ): ?>
						<ul class="pulse-tags">
							<?php foreach( $it['tags'] as $tag ): ?>
								<li><a href="<?php echo $tag['url']; ?>"><?php echo $tag['name']; ?></a></li>
							<?php endforeach; ?>
						</ul>
					<?php
						elseif ( isset( $it['tags'] ) && is_string( $it['tags'] )): 
							echo $it['tags'];
						endif;
					?>
					
					<?php if ( isset( $it['authors'] ) && is_array( $it['authors'] ) ): ?>
						<span class="posted-with">posted with</span>
						<ul class="pulse-co-authors">
							<?php foreach( $it['authors'] as $author ): ?>
								<li><a href="<?php echo $author['url']; ?>"><?php echo $author['name']; ?></a></li>
							<?php endforeach; ?>
						</ul>
					<?php 
						elseif ( isset( $it['authors'] ) && is_string( $it['authors'] )): 
							echo $it['authors'];
						endif; 
					?>
					<div class="pulse-pivot"></div>
					<div class="pulse-replies"></div>
					<span class="pulse-form-progress hide">
						<img title="Loading..." alt="Loading..." src="<?php echo  PULSE_CPT_DIR_URL;?>/img/spinner.gif" />
					</span>
				</div> <!-- end of pulse-expand-content -->
			</div> <!-- end of pulse wrap -->
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
	public static function the_pulse_json() {
		return json_encode( Pulse_CPT::the_pulse_array() );
	}
	
	/**
	 * the_pulse_array function.
	 * 
	 * @access public
	 * @static
	 * @return void
	 */
	public static function the_pulse_array( $rating_metric = null ) {
		global $post;
		
		// tags
		$posttags = get_the_tags();
		
		if ( $posttags ):
			foreach( $posttags as $tag ):
				$tags[] = array(
					'name' => $tag->name,
					'url'  => get_tag_link($tag->term_id),
				);
			endforeach;
		else:
			$tags = false;
		endif;
		
		// Coauthors 
		if ( Pulse_CPT_Settings::$options['COAUTHOR_PLUGIN'] ):
			$authors = get_coauthors($post->ID);
			
			$coauthors = array();
			foreach ( $authors as $author ):
				if ( $post->post_author != $author->ID && is_author( $post->post_author ) ):
					$coauthors[] = array(
						'name' => $author->user_nicename,
						'url'  => get_author_posts_url( $author->ID, $author->user_nicename ),
						'ID'   =>  $author->ID
					);
				elseif ( is_author() && $author->ID != get_the_author_meta( "ID" ) ):
					$coauthors[] = array(
						'name' => $author->user_nicename,
						'url'  => get_author_posts_url( $author->ID, $author->user_nicename ),
						'ID'   =>  $author->ID
					);
				elseif ( $post->post_author != $author->ID && ! is_author() ):
					$coauthors[] = array(
						'name' => $author->user_nicename,
						'url'  => get_author_posts_url( $author->ID, $author->user_nicename ),
						'ID'   =>  $author->ID
					);
				endif;
			endforeach;
		endif;
		
		if ( empty( $coauthors ) ):
			$coauthors = false;
		endif;
		
		$counter_up = 0;
		$counter_down = 0;
		
		if ( $rating_metric != null ):
			global $wpdb;
			$metric = $wpdb->get_row( "SELECT * FROM ".EVAL_DB_METRICS." WHERE slug='".$rating_metric."'" );
			$counter_up = $metric->counter_up;
			$counter_down = $metric->counter_down;
		endif;
		
		return array(  
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
			'tags'	      => $tags,
			'authors'     => $coauthors,
			'num_replies' => self::get_num_replies(),
			'parent'      => $post->post_parent,
			'rating'      => array(
				'slug'         => $rating_metric,
				'counter_up'   => $counter_up,
				'counter_down' => $counter_down,
			),
		);
	}
	
	/**
	 * the_pulse_array_js function.
	 * 
	 * @access public
	 * @static
	 * @return void
	 */
	public static function the_pulse_array_js( $rating_metric = null ) {
		return array(  
			"ID"        => '{{=it.ID}}',
			"date"      => '{{=it.date}}',
			"content"   => '{{=it.content}}',
			"permalink" => '{{=it.permalink}}',
			"author"    => array( 
				"ID"            => '{{=it.author.ID}}',
				"avatar_30"     => '{{=it.author.avatar_30}}',
				"user_login"    => '{{=it.author.user_login}}',
				"display_name"  => '{{=it.author.display_name}}',
				"post_url"      => '{{=it.author.post_url}}',
			),
			"tags"        => '{{ if ( it.tags ) { }} <ul class="pulse-tags"> {{~it.tags :value:index}} <li><a href="{{=value.url}}">{{=value.name}}</a></li> {{~}} </ul> {{ } }}',
			'authors'     => '{{ if ( it.authors ) { }} <span class="posted-with">posted with</span><ul class="pulse-co-authors"> {{~it.authors :value:index}} <li ><a href="{{=value.url}}">{{=value.name}}</a></li> {{~}} </ul> {{ } }}',
			'num_replies' => '{{=it.num_replies}}',
			'rating'      => array(
				'slug'         => $rating_metric,
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
	 * query_arguments function.
	 * 
	 * @access public
	 * @static
	 * @return void
	 */
	public static function query_arguments() {
		global $wp_query;
	
		$arg = array( 'post_type' => 'pulse-cpt' );
		
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
		elseif ( ! ( is_single() || is_page() ) ):
			$arg['post_parent'] = 0;
		elseif( is_404() ):
			return false; // Don't display anything
		endif;
		
		if ( is_paged() ):
			$arg['paged'] = $wp_query->query_vars['paged'];
		endif;
		
		return $arg;
	}
	
	/**
	 * include_pulse_cpt function.
	 * 
	 * @access public
	 * @static
	 * @param mixed $query
	 * @return void
	 */
	public static function include_pulse_cpt( $query ) {
		if ( ! is_admin() && $query->is_main_query() && ! $query->is_singular() ):
			$query->set( 'post_type', array( 'post', 'pulse-cpt') );
		endif;
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
	
	/* function to handle regular and nopriv ajax requests */
	public static function ajax_replies() {
		$data = ( isset( $_POST['data'] ) ? $_POST['data'] : false );
		if ( $data ):
			$query_args = self::query_arguments();
			$query_args['post_parent'] = $data['pulse_id'];
			
			$query = new WP_Query( $query_args );
			while ( $query->have_posts() ):
				$query->the_post();
				echo self::the_pulse( self::the_pulse_array() );
			endwhile;
		endif;
		
		die(); //prevent wp appending 0 to output
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
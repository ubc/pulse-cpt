<?php 


class Pulse_CPT {

  static $add_form_script;
  
  public static function init() {
  
	  Pulse_CPT::register_pulse();
	  
	  if( !is_admin() )
	  	Pulse_CPT::register_script_and_style();
  }
  
  public static function template_redirect() {
  		
  		Pulse_CPT::print_form_style();
  }
  
  public static function install() {
  
	  Pulse_CPT::register_pulse();
	  flush_rewrite_rules();
  }
  
  public static function register_pulse(){
  
		$labels = array(
			'name' => _x('Pulse', 'pule-ct'),
			'singular_name' => _x('Pulse', 'pulse-ct'),
			'add_new' => _x('Add New', 'pulse-ct'),
			'add_new_item' => __('Add New Pulse'),
			'edit_item' => __('Edit Pulse'),
			'new_item' => __('New Pulse'),
			'all_items' => __('All Pulses'),
			'view_item' => __('View Pulses'),
			'search_items' => __('Search Pulses'),
			'not_found' =>  __('No pulses found'),
			'not_found_in_trash' => __('No pulses found in Trash'), 
			'parent_item_colon' => '',
			'menu_name' => 'Pulse'
		);
		
		$args = array(
			'labels' => $labels,
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true, 
			'show_in_menu' => true, 
			'query_var' => true,
			'rewrite' => array('slug'=>'pulse'),
			'capability_type' => 'post',
			'has_archive' => true, 
			'hierarchical' => false,
			'menu_position' => null,
			'taxonomies' => array('category', 'post_tag', 'mention'),
			'supports' => array( 'editor', 'author', 'comments' )
		);
		
  		register_post_type( 'pulse-cpt', $args );
    }
    
    public static function register_script_and_style(){
    	
    	wp_register_script( 'autoGrowInput', PULSE_CPT_DIR_URL.'/js/jquery.autoGrowInput.js' , array('jquery'), '1.0', true );
    	wp_register_script( 'tagbox', PULSE_CPT_DIR_URL.'/js/tagbox.js' , array('jquery','jquery-ui-autocomplete', 'autoGrowInput' ), '1.0', true );
    	
    	wp_register_script( 'autoGrowInput', PULSE_CPT_DIR_URL.'/js/jquery.autoGrowInput.js' , array('jquery'), '1.0', true );
    	wp_register_script( 'autogrow', PULSE_CPT_DIR_URL.'/js/autogrow.js' , array('jquery'), '1.0', true );
    	
    	wp_register_script( 'doT', PULSE_CPT_DIR_URL.'/js/doT.js' , array('jquery'), '1.0', true );
    	
    	wp_register_script( 'charCount', PULSE_CPT_DIR_URL.'/js/charCount.js' , array('jquery'), '1.0', true );
    	
    	wp_register_script( 'jquery-ui-position', PULSE_CPT_DIR_URL.'/js/jquery.ui.position.js' , array('jquery'), '1.0', true );
    	wp_register_script( 'jquery-ui-autocomplete', PULSE_CPT_DIR_URL.'/js/jquery.ui.autocomplete.js' , array( 'jquery','jquery-ui-position','jquery-ui-widget','jquery-ui-core'), '1.0', true );
    	
    	wp_register_script( 'jquery-ui-autocomplete-html', PULSE_CPT_DIR_URL.'/js/jquery.ui.autocomplete.html.js' , array( 'jquery-ui-autocomplete'), '1.0', true );
    	
    	wp_register_script( 'pulse-cpt-form', PULSE_CPT_DIR_URL.'/js/form.js' , array( 'jquery', 'autogrow', 'tagbox', 'doT',  'jquery-ui-tabs', 'charCount', 'jquery-ui-autocomplete-html'), '1.0', true );
    	wp_register_script( 'pulse-cpt', PULSE_CPT_DIR_URL.'/js/pulse.js' , array( 'jquery'), '1.0', true );
    	
    	wp_register_style( 'pulse-cpt-form', PULSE_CPT_DIR_URL.'/css/form.css');
    	wp_register_style( 'pulse-cpt-list', PULSE_CPT_DIR_URL.'/css/pulse.css');
		
    }
    
    public static function print_form_style(){
    	
    	wp_enqueue_style( 'pulse-cpt-form' );
    	wp_enqueue_style( 'pulse-cpt-list' );
    }
    
    public static function print_form_script(){
    	
    	if ( ! self::$add_form_script )
			return;
		global $pulse_cpt_widget_ids;
 		
 		wp_localize_script( 'pulse-cpt-form', 'Pulse_CPT_Form_global', 
	    	array(
		  		'ajaxUrl' => admin_url( 'admin-ajax.php' ) ,
		  		'tags' => Pulse_CPT_Form::get_tags(),
		  		'authors' => Pulse_CPT_Form::get_authors()
			)
		);
		wp_localize_script( 'pulse-cpt-form', 'Pulse_CPT_Form_local', $pulse_cpt_widget_ids );
		wp_print_scripts( 'pulse-cpt-form' );
		wp_print_scripts( 'pulse-cpt' );
		
    }
    
    /**
     * widgets_init function.
     * 
     * @access public
     * @static
     * @return void
     */
    public static function widgets_init() {
  
  		register_widget( 'Pulse_CPT_Form_Widget' );
  
  	}
  	
  	/**
  	 * footer function.
  	 * 
  	 * @access public
  	 * @static
  	 * @return void
  	 */
  	public static function footer(){
  		$it = Pulse_CPT::the_pulse_array_js();
  		
  		?>
  		<script id="pulse-cpt-single" type="text/x-dot-template"><?php Pulse_CPT::the_pulse( $it ); ?></script>
  		<?php 
  	}
  	
  	/**
  	 * the_pulse function.
  	 * 
  	 * @access public
  	 * @static
  	 * @param mixed $it (default: null)
  	 * @return void
  	 */
  	public static function the_pulse( $it = null) {
  		 
  		if( $it == null )
  			$it = Pulse_CPT::the_pulse_array();
  			
		?>
		<div class="pulse">
			<div class="pulse-wrap">
			<?php echo $it['author']['avatar_30']; ?>
			<div class="pulse-author-meta"><a href="<?php echo $it['author']['post_url']; ?>"><?php echo $it['author']['display_name']; ?> <small>@<?php echo $it['author']['user_login']; ?></small></a></div>
			<div class="pulse-meta"><a href="<?php echo $it['permalink']; ?>"><?php echo $it['date']; ?></a></div>
			<div class="pulse-content"><?php echo $it['content']; ?></div>
			<div class="pulse-actions">
				<ul>
					<li><a href="#expand-url">Expand</a></li>
					<li><a href="#reply-url">Reply</a></li>
					<li><a href="#favorite">Favorite</a></li>
					<li><span>5</span> Replies</li>
				</ul>
			</div>
			<div class="pulse-expand-content">
			
			<?php 
			if( isset( $it['tags'])  && is_array( $it['tags'] ) ) : ?>
			<ul class="pulse-tags">
			<?php foreach( $it['tags'] as $tag ): ?>
				<li><a href="<?php echo $tag['url']; ?>"><?php echo $tag['name']; ?></a></li>
			<?php endforeach; ?>
			</ul>
			
			<?php elseif( isset( $it['tags'] ) && is_string( $it['tags'] )): 
				echo $it['tags'];
			endif; ?>
			<?php 
			
			if( isset( $it['authors'] ) && is_array( $it['authors'] ) ) : ?>
				<span class="posted-with">posted with</span>
				<ul class="pulse-co-authors">
				<?php foreach( $it['authors'] as $author ): ?>
					<li><a href="<?php echo $author['url']; ?>"><?php echo $author['name']; ?></a></li>
				<?php endforeach; ?>
				</ul>
			<?php 
			elseif( isset( $it['authors'] ) && is_string( $it['authors'] )): 
				echo $it['authors'];
			endif; 
			
			if( isset($it['comments']) && !is_singular('pulse-cpt')): ?>
			<ol class="pulse-commentslist">
				<?php foreach($it['comments'] as $comment): ?>
					<li class="comment">
						<div class="comment-author vcard">
							<?php echo $comment['comment_avatar']; ?>
							<cite class="fn"><?php echo $comment['comment_author']; ?></cite>
						</div>
						<div class="comment-meta"><?php echo $comment['comment_date']; ?></div>
						<div class="comment-content"><?php echo $comment['comment_content']; ?></div>
					</li>
				<?php endforeach; ?>
			</ol>
			<?php endif; ?>
			</div><!-- end of pulse-expand-content -->
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
  	public static function the_pulse_json(){
  		$it = Pulse_CPT::the_pulse_array();
  		
  		return json_encode( $it );
  	}
  	
  	/**
  	 * the_pulse_array function.
  	 * 
  	 * @access public
  	 * @static
  	 * @return void
  	 */
  	public static function the_pulse_array() {
  		global $post;
  		
  		// tags
  		$posttags = get_the_tags();
  		
  		if( $posttags ):
  		
	  		foreach( $posttags as $tag ):
	  			$tags[] = array(
	  				'name' => $tag->name,
	  				'url'  => get_tag_link($tag->term_id)
	  			);
	  		endforeach;
  		else:
  			$tags = false;
  		endif;
  		
  		//  coauthors 
  	    if( defined( 'COAUTHORS_PLUS_VERSION' ) ):
	  		$authors = get_coauthors($post->ID);
	  		
	  		$coauthors = array();
	  		foreach($authors as $author):
	  			
	  			if( $post->post_author != $author->ID && is_author( $post->post_author ) ):
		  			$coauthors[] = array(
		  				'name' => $author->user_nicename,
		  				'url'  => get_author_posts_url($author->ID, $author->user_nicename),
		  				'ID'   =>  $author->ID
		  			);
		  		
		  		elseif( is_author() && $author->ID != get_the_author_meta( "ID" ) ):
		  			$coauthors[] = array(
		  				'name' => $author->user_nicename,
		  				'url'  => get_author_posts_url($author->ID, $author->user_nicename),
		  				'ID'   =>  $author->ID
		  			);
	  			
	  			elseif( $post->post_author != $author->ID && !is_author()):
	  				$coauthors[] = array(
		  				'name' => $author->user_nicename,
		  				'url'  => get_author_posts_url($author->ID, $author->user_nicename),
		  				'ID'   =>  $author->ID
		  			);
	  			endif;
	  		endforeach;
  		endif;
  		
  		if( empty( $coauthors ) )
  			$coauthors = false;
  		
  		// comments 
  		$raw_comments =  get_comments('post_id='.$post->ID.'&order=ASC');
  		$comments = array();
  		foreach($raw_comments as $comment):
  			$comments[] = array( 
  				'ID' 				=> $comment->comment_ID,
  				'comment_author' 	=> $comment->comment_author,
  				'comment_content'	=> $comment->comment_content,
  				'comment_date'		=> Pulse_CPT::get_the_date( $comment->comment_date_gmt ),
  				'comment_avatar'    => get_avatar( $comment->comment_author_email ,'30' )
  				);
  		endforeach;
  		
  		
  		
  		return array(  
  			"ID"	=> get_the_ID(),
  			"date" 	=> Pulse_CPT::get_the_date(),
  			"content" => get_the_content(),
  			"permalink" => get_permalink(),
  			"author"  => array( 
  				"ID" 			=> get_the_author_meta( 'ID' ),
  				"avatar_30" 	=> get_avatar( get_the_author_meta( 'ID' ) , '30'),
  				"user_login"	=> get_the_author_meta( 'user_login' ),
  				"display_name"	=> get_the_author(),
  				"post_url"		=> get_author_posts_url( get_the_author_meta('ID') )
  				),
  			"tags"	=> $tags,
  			"authors" =>$coauthors,
  			"comments" => $comments,
  			);
  	}
  	
  	/**
  	 * the_pulse_array_js function.
  	 * 
  	 * @access public
  	 * @static
  	 * @return void
  	 */
  	public static function the_pulse_array_js() {
  		return array(  
  			"ID"		=> '{{=it.ID}}',
  			"date" 		=> '{{=it.date}}',
  			"content" 	=> '{{=it.content}}',
  			"permalink" => '{{=it.permalink}}',
  			"author"  	=> array( 
  				"ID" 			=> '{{it.author.ID}}',
  				"avatar_30" 	=> '{{=it.author.avatar_30}}',
  				"user_login"	=> '{{=it.author.user_login}}',
  				"display_name"	=> '{{=it.author.display_name}}',
  				"post_url"		=> '{{=it.author.post_url}}'
  				),
  			"tags"  	=> '{{ if( it.tags ) {  }} <ul class="pulse-tags"> {{~it.tags :value:index}} <li><a href="{{=value.url}}">{{=value.name}}</a></li> {{~}} </ul> {{ } }}',
  			'authors'   => '{{ if( it.authors ) {  }} <span class="posted-with">posted with</span><ul class="pulse-co-authors"> {{~it.authors :value:index}} <li ><a href="{{=value.url}}">{{=value.name}}</a></li> {{~}} </ul> {{ } }}'
  			);
  	}
	
  	/**
  	 * get_the_date function.
  	 * 
  	 * @access public
  	 * @static
  	 * @param mixed $date_str (default: null)
  	 * @return void
  	 */
  	public static function get_the_date( $date_str = null ) {
  		if( $date_str ):
  			$date = date ( 'Ymd' , strtotime( $date_str ) );
  		else:
  			$date  = get_the_date('Ymd');
  		endif;
  		
  		if( $date == date('Ymd') ):
  			return ( $date_str ? date( 'g:iA' , strtotime( $date_str ) ) : get_the_date( 'g:iA' ) );
  		else:
  			return ( $date_str ? date( 'j M' , strtotime( $date_str ) ) : get_the_date( 'j M' ) );
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
  	
  	/* 
  	 * this function prepares 
  	 */
  	
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
  		
 		if( is_date() ):
 			$arg['year'] = $wp_query->query_vars['year'];
 			
 			if( !empty( $wp_query->query_vars['monthnum'] ) ):
 				$arg['monthnum'] = $wp_query->query_vars['monthnum'];
 			endif;
 			
 			if( !empty( $wp_query->query_vars['day'] ) ):
 				$arg['day'] = $wp_query->query_vars['day'];
 			endif;
 			
  		elseif( is_author() ):
  			$arg['author_name'] = $wp_query->query_vars['author_name'];
  		elseif( is_category() ):
  			$arg['cat'] = $wp_query->query_vars['cat'];
  			
  		elseif( is_tag() ):
  			$arg['tag_id'] = $wp_query->query_vars['tag_id'];
  			
  		elseif( is_single() || is_page() ):
  			$arg['post_parent'] = get_the_ID(); 
  		
  		elseif( is_404() ):
  			
  			return false; // don't display anything
  		
  		endif;
  		// what
  		
  		if( is_paged() ):
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
  		
  		if ( ! is_admin() && $query->is_main_query() && !$query->is_singular() ):
  		
        
         $query->set( 'post_type', array( 'post', 'pulse-cpt') );
 		 //return $query;
  
        endif;
        //return $query;
       
  	
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
  		
  		if( $post->post_type == 'pulse-cpt')
  			return 'pulse';
  		return $template;
  	}
  	
  	

}

function the_pulse() {
	Pulse_CPT::the_pulse();
}
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
			'supports' => array( 'editor', 'author' )
		); 
  		register_post_type( 'pulse-cpt', $args );
    }
    
    public static function register_script_and_style(){
    	
    	wp_register_script( 'autoGrowInput', PULSE_CPT_DIR_URL.'/js/jquery.autoGrowInput.js' , array('jquery'), '1.0', true );
    	wp_register_script( 'tagbox', PULSE_CPT_DIR_URL.'/js/tagbox.js' , array('jquery','jquery-ui-autocomplete', 'autoGrowInput' ), '1.0', true );
    	
    	wp_register_script( 'autoGrowInput', PULSE_CPT_DIR_URL.'/js/jquery.autoGrowInput.js' , array('jquery'), '1.0', true );
    	wp_register_script( 'autogrow', PULSE_CPT_DIR_URL.'/js/autogrow.js' , array('jquery'), '1.0', true );
    	
    	wp_register_script( 'charCount', PULSE_CPT_DIR_URL.'/js/charCount.js' , array('jquery'), '1.0', true );
    	
    	wp_register_script( 'jquery-ui-position', PULSE_CPT_DIR_URL.'/js/jquery.ui.position.js' , array('jquery'), '1.0', true );
    	wp_register_script( 'jquery-ui-autocomplete', PULSE_CPT_DIR_URL.'/js/jquery.ui.autocomplete.js' , 
    	array( 'jquery','jquery-ui-position','jquery-ui-widget','jquery-ui-core'), '1.0', true );
    	
    	wp_register_script( 'pulse-cpt-form', PULSE_CPT_DIR_URL.'/js/form.js' , array( 'jquery', 'autogrow', 'tagbox', 'jquery-ui-tabs', 'charCount' ), '1.0', true );
    	
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
		wp_localize_script( 'pulse-cpt-form', 'Pulse_CPT_Form_local', 
	    	$pulse_cpt_widget_ids
		);
		
		
		wp_print_scripts( 'pulse-cpt-form' );
    }
    
    public static function widgets_init() {
  
  		register_widget( 'Pulse_CPT_Form_Widget' );
  
  	}
  	
  	public static function the_pulse(){
  		global $post;
  		
  		$author = get_the_author();
			?>
			<div class="pulse">
				<?php echo get_avatar( get_the_author_meta('ID') , '30'); ?>
				<div class="pulse-author-meta"><a href="<?php echo get_author_posts_url( get_the_author_meta('ID') ); ?>"><?php echo get_the_author_meta('display_name'); ?> <small>@<?php echo get_the_author_meta('user_login'); ?></small></a></div>
				<div class="pulse-meta"><a href="<?php the_permalink(); ?>"><?php Pulse_CPT::the_date(); ?></a></div>
				<div class="pulse-content"><?php the_content(); ?></div>
				<ul class="pulse-tags"></ul>
				<ul class="pulse-co-authors"></ul>
				<div class="pulse-footer"><div class="pulse-footer-action"><a href="">Expand</a> · <a href="">Reply</a> · </div><div class="pulse-comments-intro"><?php echo get_avatar($current_user->ID, '14'); ?> Tom is dicussing</div></div>
			</div>
			<?php
  	}
  	
  	public static function the_pulse_json(){
  		
  		return json_encode( array(  
  			"ID"	=> get_the_ID(),
  			"date" 	=> Pulse_CPT::get_the_date(),
  			"content" => get_the_content(),
  			"author"  => array( 
  				"ID" => get_the_author_meta('ID'),
  				"avatar_30" => get_avatar( get_the_author_meta('ID') , '30'),
  				"user_login"=> get_the_author_meta('user_login'),
  				"display_name"=>get_the_author_meta('display_name')
  				)
  			) );
  	}
  	
  	public static function get_the_date() {
  		$date  = get_the_date('Ymd');
  		
  		if( $date = date('Ymd') ):
  			return get_the_date('g:iA');
  		else:
  			return get_the_date('j M');
  		endif;
  		
  	}
  	public static function the_date() {
  		echo Pulse_CPT::get_the_date();
  		
  	}
  	
  	
  	
}
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
    	wp_register_script( 'tagbox', PULSE_CPT_DIR_URL.'/js/tagbox.js' , array('jquery','jquery-ui-autocomplete', 'autoGrowInput'), '1.0', true );
    	
    	// wp_register_script( 'tag-it', PULSE_CPT_DIR_URL.'/js/tag-it.js' , array('jquery','jquery-ui-autocomplete'), '1.0', true );
    	
    	wp_register_script( 'autoGrowInput', PULSE_CPT_DIR_URL.'/js/jquery.autoGrowInput.js' , array('jquery'), '1.0', true );
    	wp_register_script( 'autogrow', PULSE_CPT_DIR_URL.'/js/autogrow.js' , array('jquery'), '1.0', true );
    	
    	wp_register_script( 'charCount', PULSE_CPT_DIR_URL.'/js/charCount.js' , array('jquery'), '1.0', true );
    	
    	wp_register_script( 'jquery-ui-position', PULSE_CPT_DIR_URL.'/js/jquery.ui.position.js' , array('jquery'), '1.0', true );
    	wp_register_script( 'jquery-ui-autocomplete', PULSE_CPT_DIR_URL.'/js/jquery.ui.autocomplete.js' , array( 'jquery','jquery-ui-position','jquery-ui-widget','jquery-ui-core'), '1.0', true );
    	
    	wp_register_script( 'pulse-cpt-form', PULSE_CPT_DIR_URL.'/js/form.js' , array( 'jquery', 'autogrow', 'tagbox', 'jquery-ui-tabs', 'charCount' ), '1.0', true );
    	
    	wp_register_style( 'pulse-cpt-form', PULSE_CPT_DIR_URL.'/css/form.css');
    	
		
    	
		
    }
    
    public static function print_form_style(){
    	
    	wp_enqueue_style( 'pulse-cpt-form' );
    }
    
    public static function print_form_script(){
    	
    	if ( ! self::$add_form_script )
			return;
 		
 		
 		wp_localize_script( 'pulse-cpt-form', 'Pulse_CPT_Form_local', 
	    	array(
		  		'ajaxUrl' => admin_url( 'admin-ajax.php' ) ,
		  		'tags' => Pulse_CPT_Form::get_tags(),
		  		'authors' => Pulse_CPT_Form::get_authors()
			)
		);
		var_dump(Pulse_CPT_Form::get_tags());
		
		wp_print_scripts( 'pulse-cpt-form' );
		
		
    }
    
    public static function widgets_init() {
  
  		register_widget( 'Pulse_CPT_Form_Widget' );
  
  	}
  	
  	
  	
}
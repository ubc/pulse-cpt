<?php 


class Class_Module_CPT {
  public static function init() {
  
	  Class_Module_CPT::register_pulse();
  }
  
  public static function install(){
	  Class_Module_CPT::register_pulse();
	  
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

  
}
<?php


/** 
 *  Hadles form submition, ajax, regular, though wordpress, interface fun stuff, basically the creation of the pulse content type
 *  as well as. 
 */ 
class Pulse_CPT_Form {
	
	public static function get_tags(){
	
		$tags = get_terms( 'post_tag', 'hide_empty=0' );
		foreach ($tags as $tag):
			$simple_tags[] = $tag->name;
		endforeach;
		
		return $simple_tags;
	}
	
	
	public static function get_authors() {
		$args = array();
		$users =  get_users( $args );
		foreach( $users as $user):
			$simple_user[] = $user->display_name;
		endforeach;
			
		return $simple_user;
		
	}



}


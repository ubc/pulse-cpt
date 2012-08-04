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
	
	public static function insert() {
		
		$user = wp_get_current_user();
		if( !isset( $user->ID ) ):
			echo json_encode( array( 'error' => 'Your login has expired, please login again.' ) );
   			die();
   		endif;
		if ( empty($_POST) || !wp_verify_nonce( $_POST['_wpnonce_pulse_form'], 'wpnonce_pulse_form' ) || empty($user) ):
  	 		echo json_encode( array( 'error' => 'Sorry, your nonce did not verify.' ) );
   			die();
		endif;
		
		
		$user_id		= $user->ID;
		$post_content	= $_POST['posttext'];
		
		if( isset($_POST['location']) )
			$location =  $_POST['location'];
		else
			$location = false;
		
		// tags
		$tags			= trim( $_POST['tags'] );
		$single_tags    = trim( $_POST['single_tags']);
		if( !empty( $single_tags ) )
			$tags .= ",".$single_tags;
		
		// authors
		$authors 		= trim( $_POST['authors'] );
		$single_author   = trim( $_POST['single_author'] );
		if( !empty( $single_author ) )
			$authors .= ",".$single_author;
		
		 // 'post_parent' => [ <post ID> ] //Sets the parent of the new post.
		 
		$post = array(
		  'post_author' => $user_id,
		  'post_content' => $post_content,
		  'post_status' => 'publish',  //Set the status of the new post. 
		  'post_type' => 'pulse-cpt', 
		  'tags_input' => $tags
		);  
		
		if( $location ):
			if( $location['type'] == 'singular' )
				$post['post_parent'] = $location['ID'];
				
			if($location['type'] == 'category')
				$post['post_category'] = array( $location['ID']);
		
		endif;

		$id = wp_insert_post( $post );
		$args = array('post_type' => 'pulse-cpt', 'p' => (int)$id );
		
		global $coauthors_plus, $current_user;
		
		$current_user_user_login =  $current_user->user_login;
		
		$authors_array = explode( ',', $authors );
		if( !in_array( $current_user_user_login, $authors_array ) )
			$authors_array[] = $current_user_user_login;
		
		$coauthors_plus->add_coauthors( $id, $authors_array );
		
		// The Query
		$the_query = new WP_Query( $args );
		
		// The Loop
		while ( $the_query->have_posts() ) : $the_query->the_post();
			echo Pulse_CPT::the_pulse_json();
		endwhile;
		
		// Reset Post Data
		wp_reset_postdata();
		die();
	}
	
	public static function edit_post_data( $data, $postarr ) {
		
		// change the post title into something more meaning full 
		if( $data['post_type'] == 'pulse-cpt' && $data['post_status'] != 'auto-draft' )
			$data['post_title'] = Pulse_CPT_Form::title_from_content( $data['post_content'] );
  		
  		return $data;
  	}
	
	public static function title_from_content( $content ){
		static $strlen =  null;
		if ( !$strlen ) {
				$strlen = function_exists( 'mb_strlen' )? 'mb_strlen' : 'strlen';
		}
		$max_len = 40;
		$title = $strlen( $content ) > $max_len? wp_html_excerpt( $content, $max_len ) . '...' : $content;
		$title = trim( strip_tags( $title ) );
		$title = str_replace("\n", " ", $title);
		
		// Try to detect image or video only posts, and set post title accordingly
		if ( !$title ) {
			if ( preg_match("/<object|<embed/", $content ) )
				$title = __( 'Video Post', 'pulse_press' );
			elseif ( preg_match( "/<img/", $content ) )
				$title = __( 'Image Post', 'pulse_press' );
		}
		return $title;
	}
	
	
	



}


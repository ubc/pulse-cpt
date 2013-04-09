<?php
/**
 * Handles form submition, ajax, regular, though wordpress, interface fun stuff, basically the creation of the pulse content type as well as. 
 */
class Pulse_CPT_Form {
	
	public static function init() {
		add_action( 'wp_ajax_pulse_get_user_image', array( __CLASS__, 'get_user_image' ) );
		add_action( 'wp_ajax_pulse_cpt_insert',     array( __CLASS__, 'insert' ) );
		add_action( 'publish_pulse-cpt',            array( __CLASS__, 'admin_publish' ) );
		add_filter( 'wp_insert_post_data',          array( __CLASS__, 'edit_post_data' ), 10, 2 );
	}
	
	public static function get_user_image( $user = null, $size = 30, $ajax = TRUE ) {
		if ( ! empty( $_POST['user'] ) ):
			$user = get_user_by( 'login', $_POST['user'] );
		elseif ( $user == null ):
			$user = wp_get_current_user();
		endif;
		
		if ( ! empty( $_POST['size'] ) ):
			$size = $_POST['size'];
		endif;
		
		if ( $ajax ):
			echo get_avatar( $user->user_email, $size );
			die();
		else:
			return get_avatar( $user->user_email, $size );
		endif;
	}
	
	/**
	 * get_tags function.
	 * 
	 * @access public
	 * @static
	 * @return void
	 */
	public static function get_tags() {
		$tags = get_terms( 'post_tag', 'hide_empty=0' );
		$simple_tags = array();
		
		foreach ( $tags as $tag ):
			$simple_tags[] = $tag->name;
		endforeach;
	  
		return $simple_tags;
	}
	
	/**
	 * get_authors function.
	 * 
	 * @access public
	 * @static
	 * @return void
	 */
	public static function get_authors() {
		$args = array();
		global $current_user;
		$users = get_users($args);
		foreach ( $users as $user ):
			if ( $user != $current_user ):
				$avatar = get_avatar( $user->user_email, 20 );
				$simple_user[] = array(
					'value' => $user->display_name,
					'label' => $avatar.' '.$user->display_name,
				);
			endif;
		endforeach;
	  
		return $simple_user;
	}
	
	/**
	 * insert function.
	 * 
	 * @access public
	 * @static
	 * @return void
	 */
	public static function insert() {
		$user = wp_get_current_user();
		if ( ! isset( $user->ID ) ):
			echo json_encode( array( 'error' => 'Your login has expired, please login again.' ) );
			die();
		endif;
		
		if ( empty( $_POST ) || ! wp_verify_nonce( $_POST['_wpnonce_pulse_form'], 'wpnonce_pulse_form' ) || empty($user) ):
			echo json_encode( array( 'error' => 'Sorry, your nonce did not verify.' ) );
			die();
		endif;
		
		$user_id = $user->ID;
		$post_content = wp_kses_post( trim( $_POST['posttext'] ) );
		
		if ( empty( $post_content ) ):
			echo json_encode( array( 'error' => 'Please write some content.' ) );
			die();
		endif;
	  
		if ( isset( $_POST['location'] ) ):
			$location = $_POST['location'];
		else:
			$location = false;
		endif;
		
		$tags = trim($_POST['tags']);
		$authors = trim($_POST['author']);
		
		$post = array(
			'post_author'  => $user_id,
			'post_content' => $post_content,
			'post_status'  => 'publish', //Set the status of the new post. 
			'post_type'    => 'pulse-cpt',
			'tags_input'   => $tags,
		);
	  
		if ( $location ):
			if ( $location['type'] == 'singular' ):
				$post['post_parent'] = $location['ID'];
			endif;
			
			if ( $location['type'] == 'category' ):
				$post['post_category'] = array( $location['ID'] );
			endif;
		endif;
		
		$id = wp_insert_post($post);
		$post['num_replies'] = 0; //there can't be any replies just yet
		
		global $coauthors_plus, $current_user;
		
		$current_user_user_login = $current_user->user_login;
		
		$authors_array = explode( ',', $authors );
		if ( ! in_array( $current_user_user_login, $authors_array ) ):
			$authors_array[] = $current_user_user_login;
		endif;
		
		if ( is_object( $coauthors_plus ) ):
			@$coauthors_plus->add_coauthors( $id, $authors_array ); //suppress warnings from Co-Authors plugin
		endif;
		
		// The Query
		$args = array( 'post_type' => 'pulse-cpt', 'p' => (int) $id );
		$the_query = new WP_Query( $args );
	  
		// The Loop
		while ( $the_query->have_posts() ):
			$the_query->the_post();
			echo Pulse_CPT::the_pulse_json();
		endwhile;
	  
		// Reset Post Data
		wp_reset_postdata();
		die();
	}
	
	/**
	 * edit_post_data function.
	 * 
	 * @access public
	 * @static
	 * @param mixed $data
	 * @param mixed $postarr
	 * @return void
	 */
	public static function edit_post_data( $data, $postarr ) {
		// Change the post title into something more meaning full 
		if ( $data['post_type'] == 'pulse-cpt' && $data['post_status'] != 'auto-draft' ):
			$data['post_title'] = Pulse_CPT_Form::title_from_content( $data['post_content'] );
		endif;
		
		return $data;
	}
	
	/**
	 * title_from_content function.
	 * 
	 * @access public
	 * @static
	 * @param mixed $content
	 * @return void
	 */
	public static function title_from_content( $content ) {
		static $strlen = null;
		
		if ( ! $strlen):
			$strlen = function_exists('mb_strlen') ? 'mb_strlen' : 'strlen';
		endif;
		
		$max_len = 40;
		$title = $strlen($content) > $max_len ? wp_html_excerpt( $content, $max_len ).'...' : $content;
		$title = trim( strip_tags( $title ) );
		$title = str_replace( "\n", " ", $title );
	  
		// Try to detect image or video only posts, and set post title accordingly
		if ( ! $title ):
			if ( preg_match( "/<object|<embed/", $content ) ):
				$title = __('Video Post', 'pulse_press');
			elseif ( preg_match( "/<img/", $content ) ):
				$title = __('Image Post', 'pulse_press');
			endif;
		endif;
		
		return $title;
	}
	
	public static function admin_publish( $pulse_id ) {
		//check autosave
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
		//we're only interested in the parent post
		if ( wp_is_post_revision( $pulse_id ) ) return;
		
		//check if CTLT Stream plugin exists to use with node
		if ( ! function_exists('is_plugin_active')):
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		endif;
		
		if ( CTLT_Stream::is_node_active() ):
			// The Query
			$args = array( 'post_type' => 'pulse-cpt', 'p' => (int) $pulse_id );
			$the_query = new WP_Query($args);
			
			$the_query->the_post();
			CTLT_Stream::send( 'pulse', Pulse_CPT::the_pulse_json(), 'new-pulse' );
			
			// Reset Post Data
			wp_reset_postdata();
		endif;
		
		return $pulse_id;
	}
}

Pulse_CPT_Form::init();
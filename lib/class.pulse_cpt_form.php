<?php
/**
 * Handles form submition, ajax, regular, though wordpress, interface fun stuff, basically the creation of the pulse content type as well as. 
 */
class Pulse_CPT_Form {
	
	public static function init() {
		add_action( 'wp_ajax_pulse_get_user_image', array( __CLASS__, 'get_user_image' ) );
		add_action( 'wp_ajax_pulse_cpt_insert',     array( __CLASS__, 'insert' ) );
		
		add_action( 'publish_pulse-cpt',            array( __CLASS__, 'send_to_stream' ), 20 );
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
	public static function get_coauthors() {
		global $post;
		if ( Pulse_CPT_Settings::$options['COAUTHOR_PLUGIN'] ):
			$authors = get_coauthors( $post->ID );
			
			$coauthors = array();
			foreach ( $authors as $author ):
				$coauthors[] = array(
					'name'   => $author->display_name,
					'login'  => $author->user_login,
					'url'    => get_author_posts_url( $author->ID, $author->user_nicename ),
					'ID'     => $author->ID,
					'avatar' => Pulse_CPT_Form::get_user_image( $author, 30, FALSE ),
				);
			endforeach;
		else:
			$author = get_userdata( $post->post_author );
			$coauthors[] = array(
				'name'   => $author->display_name,
				'login'  => $author->user_login,
				'url'    => get_author_posts_url( $author->ID, $author->user_nicename ),
				'ID'     => $author->ID,
				'avatar' => Pulse_CPT_Form::get_user_image( $author, 30, FALSE ),
			);
		endif;
		
		if ( empty( $coauthors ) ):
			$coauthors = false;
		endif;
		
		return $coauthors;
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
		$widgets = get_option('widget_pulse_cpt');
		
		error_log( print_r( $widgets[$_POST['widget_id']], TRUE ) );
		
		if ( ! isset( $user->ID ) ):
			echo json_encode( array( 'error' => 'Your login has expired, please login again.' ) );
			die();
		elseif ( empty( $_POST ) || ! wp_verify_nonce( $_POST['_wpnonce_pulse_form'], 'wpnonce_pulse_form' ) || empty( $user ) ):
			echo json_encode( array( 'error' => 'Sorry, your nonce did not verify.' ) );
			die();
		elseif ( $widgets[$_POST['widget_id']]['enable_character_count'] && strlen( $_POST['posttext'] ) > $widgets[$_POST['widget_id']]['num_char'] ):
			echo json_encode( array( 'error' => 'Too many characters.' ) );
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
		
		$tags = trim( $_POST['tags'] );
		$authors = trim( $_POST['author'] );
		
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
			@$coauthors_plus->add_coauthors( $id, $authors_array ); // The @ symbol suppresses warnings from Co-Authors plugin.
		endif;
		
		// The Query
		$the_query = new WP_Query( array(
			'post_type' => 'pulse-cpt',
			'p'         => (int) $id,
		) );
		
		// The Loop
		while ( $the_query->have_posts() ):
			$the_query->the_post();
			$widgets = get_option( 'widget_pulse_cpt' );
			echo Pulse_CPT::the_pulse_json( $widgets[$_POST['widget_id']] );
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
			$data['post_title'] = wp_unique_post_slug(
				/*slug       */ Pulse_CPT_Form::title_from_content( $data['post_content'] ),
				/*post_ID    */ $data['ID'],
				/*post_status*/ $data['post_status'],
				/*post_type  */ $data['post_type'],
				/*post_parent*/ $data['post_parent']
			);
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
	
	public static function insert_pulse_cpt( $pulse_id ) {
		if ( 'pulse_cpt_insert' == $_POST['action'] ):
			self::send_to_stream( $pulse_id );
		endif;
	}
	
	public static function send_to_stream( $pulse_id ) {
		// Check autosave
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
		// We're only interested in the parent post
		if ( wp_is_post_revision( $pulse_id ) ) return;
		
		
		if ( class_exists('CTLT_Stream') && CTLT_Stream::is_node_active() ):
			// The Query
			$query = new WP_Query( array(
				'post_type' => 'pulse-cpt',
				'p'         => (int) $pulse_id,
			) );
			
			$query->the_post();
			$widgets = get_option( 'widget_pulse_cpt' );
			$data = Pulse_CPT::the_pulse_array( $widgets[$_POST['widget_id']] );
			$data['content_type'] = $_POST['content_type'];
			$data = json_encode( $data );
			
			CTLT_Stream::send( 'pulse', $data, 'new-pulse' );
			
			// Reset Post Data
			wp_reset_postdata();
		endif;
		
		return $pulse_id;
	}
}

Pulse_CPT_Form::init();
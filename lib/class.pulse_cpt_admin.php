<?php
class Pulse_CPT_Admin {
	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'load' ) );
		add_action( 'admin_menu', array( __CLASS__, 'remove_submenus' ) );
	}
	
	public static function load() {
		add_action( 'load-post.php',     array( __CLASS__, 'meta_box_setup' ) );
		add_action( 'load-post-new.php', array( __CLASS__, 'meta_box_setup' ) );
		add_action( 'save_post',         array( __CLASS__, 'save_post_meta' ), 10, 2 );
	}
	
	public static function remove_submenus() {
		remove_submenu_page( 'edit.php?post_type=pulse-cpt', 'edit-tags.php?taxonomy=post_tag&amp;post_type=pulse-cpt' );
		remove_submenu_page( 'edit.php?post_type=pulse-cpt', 'edit-tags.php?taxonomy=category&amp;post_type=pulse-cpt' );
	}
	
	/** Callback function for setting up the meta box for pulse widget configuration on a page-level */
	public static function meta_box_setup() {
		add_action( 'add_meta_boxes', array( __CLASS__, 'meta_box_add' ) );
	}
	
	/** Callback to construct the meta box in post edit pages */
	public static function meta_box_add() {
		// We need one for every type of post we want the metabox to appear in
		$post_types = get_post_types( array( 'public' => true ) );
		foreach ( $post_types as $post_type ):
			add_meta_box( 'pulse-post-meta', __( 'Pulse CPT', 'pulse-cpt' ), array( __CLASS__, 'pulse_meta_box' ), $post_type, 'side', 'default' );
		endforeach;
	}
	
	/** Callback to construct contents of the meta box */
	public static function pulse_meta_box( $post, $box ) {
		?>
		<label>
			<input type="checkbox" name="pulse_cpt-enabled" <?php checked( get_post_meta( $post->ID, 'pulse_cpt-enabled', TRUE ) == 'on' ); ?>/>
				 Disable Pulses on this page.
		</label>
		<?php
		wp_nonce_field( 'pulse-post-meta', 'pulse_cpt_nonce' );
	}
	
	/** Handle saving the post meta after any add/edit action to posts */
	public static function save_post_meta( $post_id, $post_object ) {
		global $meta_box, $wpdb;
		
		// Check autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ):
			return;
		endif;
	  
		// Validate nonce - also check for pulse
		if ( ! isset( $_POST['pulse_cpt_nonce'] ) || ! wp_verify_nonce( $_POST['pulse_cpt_nonce'], 'pulse-post-meta' ) ):
			return $post_id;
		endif;
	  
		// Check user permissions
		if ( isset( $_REQUEST['post_type'] ) && $_REQUEST['post_type'] == 'page' ):
			if ( ! current_user_can( 'edit_page', $post_id ) ):
				return $post_id;
			endif;
		elseif ( ! current_user_can( 'edit_post', $post_id ) ):
			return $post_id;
		endif;
		
		// We're only interested in the parent post
		if ( $post_object->post_type == 'revision' ):
			return;
		endif;
		
		if ( empty( $_POST['pulse_cpt-enabled'] ) ):
			delete_post_meta( $post_id, 'pulse_cpt-enabled' );
		else:
			update_post_meta( $post_id, 'pulse_cpt-enabled', $_POST['pulse_cpt-enabled'] );
		endif;
		
		return true;
	}
}

Pulse_CPT_Admin::init();
<?php 

global $pulse_cpt_widget_ids;
class Pulse_CPT_Form_Widget extends WP_Widget {

	public function __construct() {
		parent::__construct(
	 		'pulse_cpt', // Base ID
			'Pulse Form', // Name
			array( 'description' => __( 'A way to simply add new pulses', 'text_domain' ), ) // Args
		);
	}
	
	function widget( $args, $instance ) {
		global $current_user; global $pulse_cpt_widget_ids;
		Pulse_CPT::$add_form_script = true;
		
		
		extract( $instance );
		// wp_enqueue_script( 'pulse-form', '')
		extract( $args, EXTR_SKIP);
		$id = substr( $widget_id, 10);
		
		$pulse_cpt_widget_ids[$id] = 
		array(
	  		'id' => $widget_id,
			'enable_character_count' => (bool)$enable_character_count,
			'num_char' 				=> (int)$num_char,
			'enable_url_shortener'  => (bool)$enable_url_shortener,
			'bitly_user' 			=> $bitly_user,
			'bitly_api_key' 		=> $bitly_api_key,
			'enable_tagging' 		=> $enable_tagging,
			'enable_co_authoring' 	=> $enable_co_authoring,
			'enable_file_uploads' 	=> $enable_file_uploads,
			'enable_subscribers'	=> $enable_subscribers,
			'enable_tabs'			=> (bool)($enable_tagging || $enable_co_authoring || $enable_file_uploads)
		);
		
		echo $before_widget; 
		if ( ! empty( $title ) && $display_title )
			echo $before_title . $title . $after_title;
		?>
		<div class="postbox">
			<form action="" method="post" name="new-post" class="pulse-form">
				<textarea cols="60" rows="4" tabindex="1" class="pulse-form-textarea autogrow" name="posttext" placeholder="<?php echo $placeholder; ?>"></textarea>
				<?php if( $enable_url_shortener ) { ?>
				<div class="pulse-shorten-url"><a href="#">shorten url</a></div>
				<?php } ?>
				
				
				
				
				<?php if($enable_tagging || $enable_co_authoring || $enable_file_uploads ): ?>
				
				<div class="pulse-tags-shell"></div>
				<div class="pulse-author-shell"></div>
				<div class="pulse-file-shell"></div>
				
				<div class="pulse-tabs">
					<?php if( $enable_tagging ){?>
					<div id="tabs-1">
						<textarea placeholder="Seperate tags by commas" class="pulse-textarea-tags" name="pulse-textarea-tags"></textarea>
					</div>
					<?php } ?>
					<?php if( $enable_co_authoring ){?>
					<div id="tabs-2">
						<textarea placeholder="People you are posting with" class="pulse-textarea-author" name="pulse-textarea-author"></textarea>
					</div>
					<?php } ?>
					<?php if( $enable_file_uploads ){?>
					<div id="tabs-3">
						file upload
					</div>
					<?php } ?>
					
					<ul>
						<?php if( $enable_tagging ){?>
						<li><a href="#tabs-1" class="pulse-tabs-tags">tags</a></li>
						<?php } ?>
						<?php if( $enable_co_authoring ){?>
						<li><a href="#tabs-2" class="pulse-tabs-author">author</a></li>
						<?php } ?>
						<?php if( $enable_file_uploads ){?>
						<li><a href="#tabs-3" class="pulse-tabs-file">file</a></li>
						<?php } ?>
					</ul>
				</div>
				<?php endif; ?>
				
				<div class="pulse-form-submit-wrap">
					<?php if( $enable_character_count ) { ?>
					<span class="pulse-form-counter"><?php echo $num_char; ?></span>
					<?php } ?>
					
					<span class="pulse-form-progress">
						<img title="Loading..." alt="Loading..." src="<?php echo  PULSE_CPT_DIR_URL;?>/img/spinner.gif" />
					</span>					
					<input type="submit" value="Post it" tabindex="3" class="pulse-form-submit" id="submit">
				</div>
				
				<input type="hidden" value="post" name="action">
				<?php wp_nonce_field( 'wpnonce_pulse_form', '_wpnonce_pulse_form' ); ?> 
			</form>
			<div class="clear"></div>
		</div>
		
		<?php	
		echo $after_widget;
	}
	
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		
		$instance['title']					= strip_tags($new_instance['title']);
		$instance['display_title'] 			= (bool) $new_instance['display_title'];
		$instance['placeholder'] 			= strip_tags( $new_instance['placeholder']);
		$instance['enable_character_count'] = (bool) $new_instance['enable_character_count'];
		$instance['num_char'] 				= (int) $new_instance['num_char'];
		$instance['enable_url_shortener'] 	= (bool) $new_instance['enable_url_shortener'];
		$instance['bitly_user'] 			= strip_tags( $new_instance['bitly_user'] );
		$instance['bitly_api_key'] 			= strip_tags( $new_instance['bitly_api_key'] );
		$instance['enable_tagging'] 		= (bool) $new_instance['enable_tagging'];
		$instance['enable_co_authoring'] 	= (bool) $new_instance['enable_co_authoring'];
		$instance['enable_file_uploads'] 	= (bool) $new_instance['enable_file_uploads'];
		$instance['enable_subscribers']		= (bool) $new_instance['enable_subscribers'];
		
		return $instance;
	}
	
	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 
			'title' 			=> '', 
			'display_title' 	=> false, 
			'placeholder' 		=> 'What is on your mind?',
			'enable_character_count' => false,
			'num_char' 			=> 140,
			'enable_url_shortener' => false,
			'bitly_user' 		=> '',
			'bitly_api_key' 	=> '',
			'enable_tagging' 	=> false,
			'enable_co_authoring' => false,
			'enable_file_uploads' => false,
			'enable_subscribers'=> false
		 ) );
		extract( $instance );
		
		?><!-- Title -->
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo attribute_escape( $title); ?>" /></label>
		<label for="<?php echo $this->get_field_id( 'display_title' ); ?>"> <input  id="<?php echo $this->get_field_id( 'display_title' ); ?>" name="<?php echo $this->get_field_name( 'display_title' ); ?>" type="checkbox"<?php echo checked( $display_title ); ?> /> Display Title</label>
		</p>
		<!-- Placeholder -->
		<p><label for="<?php echo $this->get_field_id( 'placeholder' ); ?>">Placeholder: <input class="widefat" id="<?php echo $this->get_field_id( 'placeholder' ); ?>" name="<?php echo $this->get_field_name( 'placeholder' ); ?>" type="text" value="<?php echo attribute_escape( $placeholder); ?>" /></label></p>
		
		<!-- Character Count -->
		<p><label for="<?php echo $this->get_field_id( 'enable_character_count' ); ?>"> <input  id="<?php echo $this->get_field_id( 'enable_character_count' ); ?>" name="<?php echo $this->get_field_name( 'enable_character_count' ); ?>" type="checkbox"<?php echo checked( $enable_character_count); ?> />Character Count</label><br />
		<label for="<?php echo $this->get_field_id( 'num_char' ); ?>"> Number of Characters: <input  id="<?php echo $this->get_field_id( 'num_char' ); ?>" name="<?php echo $this->get_field_name( 'num_char' ); ?>" type="text" value="<?php echo attribute_escape( $num_char ); ?>" /></label>
		<br /><small class="clear">A counter restricting the number of characters a person can enter.</small>
		
		<br />
		<p><label for="<?php echo $this->get_field_id( 'enable_url_shortener' ); ?>"> <input  id="<?php echo $this->get_field_id( 'enable_url_shortener' ); ?>" name="<?php echo $this->get_field_name( 'enable_url_shortener' ); ?>" type="checkbox"<?php echo checked( $enable_url_shortener ); ?> />Enable URL Shortening</label><br />
		<!-- Enable Url Shortener -->
		<label for="<?php echo $this->get_field_id( 'bitly_user' ); ?>"> Bitly Username: <input  id="<?php echo $this->get_field_id( 'bitly_user' ); ?>" name="<?php echo $this->get_field_name( 'bitly_user' ); ?>"  class="widefat" type="text" value="<?php echo attribute_escape( $bitly_user ); ?>" /></label>
		<br />
		<label for="<?php echo $this->get_field_id( 'bitly_api_key' ); ?>"> Bitly API Key: <input  id="<?php echo $this->get_field_id( 'bitly_api_key' ); ?>" name="<?php echo $this->get_field_name( 'bitly_api_key' ); ?>"  class="widefat" type="text" value="<?php echo attribute_escape( $bitly_api_key ); ?>" /></label>
		
		<small class="clear">To get your <a href="http://bit.ly" target="_blank">bit.ly</a> API key - <a href="http://bit.ly/a/sign_up" target="_blank">sign up</a> and view your <a href="http://bit.ly/a/your_api_key/" target="_blank">API KEY</a></small>
		</p>
		
		<!-- Enable Tagging -->
		<p><label for="<?php echo $this->get_field_id( 'enable_tagging' ); ?>"> <input  id="<?php echo $this->get_field_id( 'enable_tagging' ); ?>" name="<?php echo $this->get_field_name( 'enable_tagging' ); ?>" type="checkbox"<?php echo checked( $enable_tagging ); ?> />Enable Tagging</label><br />
		<small>Pulse authors can add tags to the pulse</small>
		</p>
		<?php if( true ): // co authoring plugin is enabled ?>
		<!-- Enable Co Authoring -->
		<p><label for="<?php echo $this->get_field_id( 'enable_co_authoring' ); ?>"> <input  id="<?php echo $this->get_field_id( 'enable_co_authoring' ); ?>" name="<?php echo $this->get_field_name( 'enable_co_authoring' ); ?>" type="checkbox"<?php echo checked( $enable_co_authoring ); ?> />Enable Co Authoring</label><br />
		<small>Pulse authors can add other as contributing authors</small>
		</p>
		<?php else: // enable co-authoring plugin enable this functionality ?>
		
		<?php endif; ?>
		
		<!-- Enable File Uploads -->
		<p><label for="<?php echo $this->get_field_id( 'enable_file_uploads' ); ?>"> <input  id="<?php echo $this->get_field_id( 'enable_file_uploads' ); ?>" name="<?php echo $this->get_field_name( 'enable_file_uploads' ); ?>" type="checkbox"<?php echo checked( $enable_file_uploads ); ?> />Enable File Uploads</label><br />
		<small>Pulse authors can upload a files</small>
		</p>
		
		<!-- Enable Subscribers -->
		<p><label for="<?php echo $this->get_field_id( 'enable_subscribers' ); ?>"> <input  id="<?php echo $this->get_field_id( 'enable_subscribers' ); ?>" name="<?php echo $this->get_field_name( 'enable_subscribers' ); ?>" type="checkbox"<?php echo checked( $enable_subscribers ); ?> />Enable Subscribers to post</label><br />
		<small>Allow any registered member to post</small>
		</p>
		
		<?php 
	}
}

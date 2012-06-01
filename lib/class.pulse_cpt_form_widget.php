<?php 


class Pulse_CPT_Form_Widget extends WP_Widget {

	public function __construct() {
		parent::__construct(
	 		'foo_widget', // Base ID
			'Pulse Form', // Name
			array( 'description' => __( 'A way to simply add new pulses', 'text_domain' ), ) // Args
		);
	}
	
	function widget( $args, $instance ) {
		global $current_user;
		Pulse_CPT::$add_form_script = true;
		// wp_enqueue_script('pulse-form', '')
		extract( $args, EXTR_SKIP);
	
		echo $before_widget; ?>
		<div class="postbox">
			<form action="" method="post" name="new-post" class="pulse-form">
				<textarea cols="60" rows="4" tabindex="1" class="pulse-form-textarea" name="posttext" placeholder="What is on your mind?"></textarea>
				<div class="pulse-tags-shell"></div>
				<div class="pulse-author-shell"></div>
				<div class="pulse-file-shell"></div>
				
				<div class="pulse-tabs">
					
					<div id="tabs-1">
						<textarea placeholder="Seperate tags by commas"></textarea>
					</div>
					<div id="tabs-2">
						<textarea placeholder="People you are posting with"></textarea>
					</div>
					<div id="tabs-3">
						file upload
					</div>
					<ul>
						<li><a href="#tabs-1">tags</a></li>
						<li><a href="#tabs-2">author</a></li>
						<li><a href="#tabs-3">file</a></li>
					</ul>
				</div>
				
				<div class="pulse-form-submit-wrap">
					<span class="pulse-form-counter">140</span>
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
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['entry_title'] = strip_tags($new_instance['entry_title']);
		$instance['comments_title'] = strip_tags($new_instance['comments_title']);
		return $instance;
	}
	
	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'entry_title' => '', 'comments_title' => '' ) );
		$title = strip_tags($instance['title']);
		
		$entry_title = strip_tags($instance['entry_title']);
		$comments_title = strip_tags($instance['comments_title']);
		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id('entry_title'); ?>">Title for entry feed: <input class="widefat" id="<?php echo $this->get_field_id('entry_title'); ?>" name="<?php echo $this->get_field_name('entry_title'); ?>" type="text" value="<?php echo attribute_escape($entry_title); ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id('comments_title'); ?>">Title for comments feed: <input class="widefat" id="<?php echo $this->get_field_id('comments_title'); ?>" name="<?php echo $this->get_field_name('comments_title'); ?>" type="text" value="<?php echo attribute_escape($comments_title); ?>" /></label></p>
		<?php
	}
}

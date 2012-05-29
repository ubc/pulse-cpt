<?php 


class Pulse_CPT_Widget extends WP_Widget {

	function Pulse_CPT_Widget() {
		$widget_ops = array('classname' => 'widget_rss_links', 'description' => 'A list with your feeds links' );
		$this->WP_Widget('rss_links', 'Feed links', $widget_ops);
	}
	
	function widget($args, $instance) {
		extract($args, EXTR_SKIP);
		echo $before_widget;
		$title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
		$entry_title = empty($instance['entry_title']) ? ' ' : apply_filters('widget_entry_title', $instance['entry_title']);
		$comments_title = empty($instance['comments_title']) ? ' ' : apply_filters('widget_comments_title', $instance['comments_title']);
		if ( !empty( $title ) ) { echo $before_title . $title . $after_title; };
		echo '<ul id="rss">';
		echo ' <li><a href=" ' . get_bloginfo('rss2_url') . '" rel="nofollow" title=" ' . $entry_title . ' ">' . $entry_title . '</a></li>';
		echo ' <li><a href=" ' . get_bloginfo('comments_rss2_url') . '" rel="nofollow" title=" '. $comments_title . ' ">' . $comments_title . '</a></li>';
		echo '</ul>';
		echo $after_widget;
	}
	
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['entry_title'] = strip_tags($new_instance['entry_title']);
		$instance['comments_title'] = strip_tags($new_instance['comments_title']);
		return $instance;
	}
	
	function form($instance) {
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
register_widget( 'Pulse_CPT_Widget' );
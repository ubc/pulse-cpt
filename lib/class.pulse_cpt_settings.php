<?php

/* Settings Screen for the Pulse Custo Post Type */

class Pulse_CPT_Settings {
  public static function admin_menu() {

    $page = add_submenu_page(
            'edit.php?post_type=pulse-cpt', 'Settings', 'Settings', 'manage_options', __FILE__, array('Pulse_CPT_Settings', 'admin_page'));
  }

  public static function admin_page() {

    echo "admin page";
  }

}
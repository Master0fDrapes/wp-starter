<?php
  /**
  * The header for theme
  *
  * @package CandyPress
  */
  $t_options = get_option('tp_opt');
  global $tempDir;
  global $siteUrl;
?>
  <?php //bloginfo('name'); ?>  <?php //is_front_page() ? bloginfo('description') : wp_title(''); ?> <!-- For Page Title -->
  <?php wp_head(); ?> <!--  pulling in all the css files and js files (for js file ref: /inc/enqueue.php) -->
  <?php //body_class(); ?> <!-- Adding Body Class -->
  <?php
    // wp_nav_menu( array(
    // 'theme_location' => 'head_menu',
    // 'container' => 'nav',
    // 'menu_class' => 'menu-item-ul',
  ?> <!-- for Navigation (for nav custom Setting ref:/inc/menu.php) -->


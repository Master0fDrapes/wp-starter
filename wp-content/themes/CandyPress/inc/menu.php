<?php 
/**
**********************
*
  =Menu
*
**********************
*/
function menu_reg() {
  register_nav_menus(
    array(
      'head_menu' => __( 'Header Menu' ),
      'footer_menu' => __( 'Footer Menu' ),
      // 'social_media' => __( 'Social Media Menu' ),
    )
  );
}
add_action( 'init', 'menu_reg' );
/**
****************************
*
  =Add-class-to-menu-link-Main Menu
*
****************************
*/
function add_menu_link_class($atts, $item, $args)
{
    $atts['class'] = 'main-menu__link';
    return $atts;
}
add_filter('nav_menu_link_attributes', 'add_menu_link_class', 1, 3);

/**
*********************************
*
  =Add Class to-<li>-Main Menu
*
*********************************
*/
function head_menu_classes($classes, $item, $args) {
  if($args->theme_location == 'head_menu') {
    $classes[] = 'main-menu__item';
  }
  return $classes;
}
add_filter('nav_menu_css_class', 'head_menu_classes', 1, 3);

/**
****************************************
*
  =Adds Active Class For Main Menu
*
****************************************
*/

function special_nav_class ($classes, $item) {
  if (in_array('current-menu-item', $classes) ){
    $classes[] = 'main-menu__item--active';
  }
  return $classes;
}
add_filter('nav_menu_css_class' , 'special_nav_class' , 10 , 2);

/**
**********************
*
  =WPNav Walker
  =Used for footer(Only)
*
**********************
*/
class Custom_Walker_Nav_Menu_top extends Walker_Nav_Menu
{
  function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
      $is_current_item = '';
      if(array_search('current-menu-item', $item->classes) != 0)
      {
          $is_current_item = 'footer-menu__item--active';
      }
      echo '<li class="'.$is_current_item.' footer-menu__item"><a href="'.$item->url.'" class="footer-menu__link">'.$item->title;
  }

  function end_el( &$output, $item, $depth = 0, $args = array() ) {
      echo '</a></li>';
  }
}

/**
**********************************
*
  =Add-Class-to-<li>-Footer Menu
*
**********************************
*/
function footer_menu_classes($classes, $item, $args) {
  if($args->theme_location == 'footer_menu') {
    $classes[] = 'footer-menu__item';
  }
  return $classes;
}
add_filter('nav_menu_css_class', 'footer_menu_classes', 1, 3);

/**
**********************************
*
  =Add-Class-to-<a>-with a php tag
  'add_a_class' => 'box-link text-dark',
*
**********************************
*/

function add_additional_class_on_a($classes, $item, $args)
{
  if (isset($args->add_a_class)) {
    $classes['class'] = $args->add_a_class;
  }
  return $classes;
}

add_filter('nav_menu_link_attributes', 'add_additional_class_on_a', 1, 3);
?>
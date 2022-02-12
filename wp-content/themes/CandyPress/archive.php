<?php
  /**
  * The template for displaying archive pages
  *
  * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
  *
  * @package CandyPress
  */

  get_header();
  $t_options = get_option('tp_opt');
  global $tempDir;
  global $siteUrl;
?>

<?php require_once 'template-parts/content.php'; ?>

<?php
get_footer();

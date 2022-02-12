<?php
  /**
  * The template for displaying all single posts
  *
  * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
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

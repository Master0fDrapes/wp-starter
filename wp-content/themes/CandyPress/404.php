<?php
  /**
  * The template for displaying 404 pages (not found)
  *
  * @link https://codex.wordpress.org/Creating_an_Error_404_Page
  *
  * @package CandyPress
  */

  get_header();
  $t_options = get_option('tp_opt');
  global $tempDir;
  global $siteUrl;
?>

404

<?php
get_footer();

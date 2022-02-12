<?php
  /**
  * The main template file
  *
  * This is the most generic template file in a WordPress theme
  * and one of the two required files for a theme (the other being style.css).
  * It is used to display a page when nothing more specific matches a query.
  * E.g., it puts together the home page when no home.php file exists.
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

<?php //require_once 'template-parts/content.php'; ?>
<?php //require_once 'template-parts/froms.php'; ?>

<?php
get_footer();

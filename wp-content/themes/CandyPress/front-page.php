<?php
	/**
	 * The template for displaying Home Page
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

<?php
	get_footer();

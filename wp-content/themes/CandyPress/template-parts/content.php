<?php
/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package theme_name
 */

?>

<?php if (have_posts()) : while ( have_posts() ) : the_post();?>

<?php the_content(); ?>

<?php endwhile; else : ?>
	<p>Sorry no posts matched your criteria.</p>
<?php endif; ?>
<?php
/**
 * Template Name: Blank Canvas (zero chrome)
 *
 * M1O v2 — the closest thing to a hand-coded static page, inside WordPress.
 * NO site header or footer: you own the entire <body>. Write a full document
 * (markup + <style> + <script>) in the editor and it renders verbatim. You
 * still get a WordPress URL, the CMS, SEO, and wp_head()/wp_footer() so plugins
 * and the admin bar keep working. Ideal for experiments, campaign pages, and
 * one-off art-directed layouts that shouldn't inherit the theme shell.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'mk-blank' ); ?>>
<?php
while ( have_posts() ) : the_post();
	remove_filter( 'the_content', 'wpautop' );
	remove_filter( 'the_content', 'shortcode_unautop' );
	the_content();
endwhile;
wp_footer();
?>
</body>
</html>

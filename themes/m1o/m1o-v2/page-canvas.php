<?php
/**
 * Template Name: Canvas (free build)
 *
 * M1O v2 — a free-build page. Write raw HTML / CSS / <script> straight in the
 * editor (a Custom HTML block, or the Code editor) and it renders VERBATIM,
 * full-bleed, wrapped only in the site header + footer chrome. No new PHP file
 * per idea, no deploy — the freedom of a hand-coded page, with a WordPress URL
 * and the CMS behind it. For zero chrome (you own the whole <body>), use the
 * "Blank Canvas" template instead.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$nr_current = 'canvas';
get_header();
?>
<main id="content" class="mk-canvas" role="main">
	<?php
	while ( have_posts() ) : the_post();
		// Render the content raw — strip wpautop so pasted markup is untouched.
		remove_filter( 'the_content', 'wpautop' );
		remove_filter( 'the_content', 'shortcode_unautop' );
		the_content();
	endwhile;
	?>
</main>
<?php get_footer();

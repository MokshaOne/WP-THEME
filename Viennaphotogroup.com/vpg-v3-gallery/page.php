<?php
/** VPG v3 · page.php · default single-page template (Gallery). */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>
<main id="vpg-main">
  <?php while ( have_posts() ) : the_post(); ?>

    <section class="g-phero">
      <div class="g-wrap">
        <p class="g-kicker" style="margin-bottom:16px">● <?php bloginfo( 'name' ); ?></p>
        <h1 class="g-display g-phero__title"><?php the_title(); ?></h1>
      </div>
    </section>

    <?php if ( has_post_thumbnail() ) : ?>
      <figure class="g-wrap" style="margin:clamp(24px,4vw,48px) auto">
        <div class="g-fig g-fig--3x2"><?php the_post_thumbnail( 'large', [ 'alt' => esc_attr( get_the_title() ) ] ); ?></div>
      </figure>
    <?php endif; ?>

    <section class="g-section" style="padding-top:clamp(32px,4vw,56px)">
      <div class="g-wrap">
        <div class="g-prose" style="margin:0 auto"><?php the_content(); wp_link_pages(); ?></div>
      </div>
    </section>

  <?php endwhile; ?>
</main>
<?php get_footer();

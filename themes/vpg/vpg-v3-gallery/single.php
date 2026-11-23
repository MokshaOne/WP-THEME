<?php
/** VPG v3 · single.php · default single-post / article template (Gallery). */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>
<main id="vpg-main">
  <?php while ( have_posts() ) : the_post(); ?>

    <section class="g-phero">
      <div class="g-wrap">
        <p class="g-kicker" style="margin-bottom:16px">
          <?php
          $fmt = function_exists( 'vpg_post_format_label' ) ? vpg_post_format_label( get_the_ID() ) : '';
          $cat = get_the_category();
          echo esc_html( $fmt ?: ( $cat ? $cat[0]->name : get_post_type_object( get_post_type() )->labels->singular_name ) );
          ?>
        </p>
        <h1 class="g-display g-phero__title" style="max-width:20ch"><?php the_title(); ?></h1>
        <div class="g-byline" style="margin-top:20px">
          <span><?php echo esc_html( get_the_author() ); ?></span><span>·</span>
          <?php $vpg_bx = function_exists( 'vpg_post_byline_extra' ) ? vpg_post_byline_extra( get_the_ID() ) : '';
          if ( $vpg_bx ) : ?><span><?php echo esc_html( $vpg_bx ); ?></span><span>·</span><?php endif; ?>
          <span><?php echo esc_html( get_the_date( 'd M Y' ) ); ?></span><span>·</span>
          <span><?php echo esc_html( function_exists( 'vpg_reading_time' ) ? vpg_reading_time( get_the_content() ) . ' ' . __( 'min read', 'vpg-v2' ) : '' ); ?></span>
        </div>
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

    <?php if ( comments_open() || get_comments_number() ) : ?>
      <section class="g-section g-section--alt">
        <div class="g-wrap"><div class="g-prose" style="margin:0 auto"><?php comments_template(); ?></div></div>
      </section>
    <?php endif; ?>

  <?php endwhile; ?>
</main>
<?php get_footer();

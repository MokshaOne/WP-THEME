<?php
/**
 * search.php — Search results (uses index list look).
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header(); ?>

<section class="opening" style="padding-bottom: 3rem;">
  <div class="opening-meta" style="grid-template-columns: 1fr 1fr;">
    <div><?php esc_html_e( 'Search', 'on1-agency' ); ?><b><?php echo esc_html( get_search_query() ); ?></b></div>
    <div class="last"><?php esc_html_e( 'Results', 'on1-agency' ); ?><b><?php echo (int) $GLOBALS['wp_query']->found_posts; ?></b></div>
  </div>
  <h1 class="opening-statement"><?php esc_html_e( 'Results for', 'on1-agency' ); ?> <em><?php echo esc_html( get_search_query() ); ?>.</em></h1>
</section>

<section class="services">
  <?php if ( have_posts() ) : ?>
    <div class="services-rows">
      <?php $i = 1; while ( have_posts() ) : the_post(); ?>
        <a href="<?php the_permalink(); ?>" class="svc-row">
          <div class="svc-no"><?php echo esc_html( str_pad( $i, 2, '0', STR_PAD_LEFT ) ); ?></div>
          <div class="svc-name"><?php the_title(); ?></div>
          <div class="svc-desc"><?php echo wp_kses_post( get_the_excerpt() ); ?></div>
          <div class="svc-tags">
            <span class="tag"><?php echo esc_html( strtoupper( get_post_type() ) ); ?></span>
          </div>
          <div class="svc-arrow">↗</div>
        </a>
      <?php $i++; endwhile; ?>
    </div>
  <?php else : ?>
    <div style="padding: 4rem var(--pad); font-family: var(--ff-mono); color: var(--muted);">
      <?php esc_html_e( 'Nothing matched.', 'on1-agency' ); ?>
    </div>
  <?php endif; ?>
</section>

<?php get_footer();

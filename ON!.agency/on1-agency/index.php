<?php
/**
 * index.php — Fallback / Journal index
 * on1.agency theme
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header(); ?>

<section class="opening" style="padding-bottom: 3rem;">
  <div class="opening-meta">
    <div><?php esc_html_e('Section', 'on1-agency'); ?><b><?php esc_html_e('Journal', 'on1-agency'); ?></b></div>
    <div><?php esc_html_e('Edition', 'on1-agency'); ?><b><?php echo esc_html( on1_edition_label() ); ?></b></div>
  </div>
  <h1 class="opening-statement">
    <?php
    if ( is_search() ) {
      printf( esc_html__( 'Search · %s', 'on1-agency' ), '<em>' . esc_html( get_search_query() ) . '</em>' );
    } elseif ( is_category() || is_tag() ) {
      echo wp_kses_post( single_term_title( '', false ) );
    } else {
      esc_html_e( 'Journal', 'on1-agency' );
    }
    ?>
  </h1>
</section>

<section class="services">
  <?php if ( have_posts() ) : ?>
    <div class="services-rows">
      <?php $i = 1; while ( have_posts() ) : the_post(); ?>
        <a href="<?php the_permalink(); ?>" class="svc-row" style="text-decoration: none;">
          <div class="svc-no"><?php echo esc_html( str_pad( $i, 2, '0', STR_PAD_LEFT ) ); ?></div>
          <div class="svc-name"><?php the_title(); ?></div>
          <div class="svc-desc"><?php echo wp_kses_post( get_the_excerpt() ); ?></div>
          <div class="svc-tags">
            <span class="tag mono"><?php echo esc_html( strtoupper( get_the_date('d M Y') ) ); ?></span>
          </div>
          <div class="svc-arrow">↗</div>
        </a>
      <?php $i++; endwhile; ?>
    </div>
    <div style="padding: 2rem var(--pad); border-top: 1px solid var(--rule);">
      <?php the_posts_pagination( array( 'prev_text' => '←', 'next_text' => '→' ) ); ?>
    </div>
  <?php else : ?>
    <p style="padding: 4rem var(--pad); font-family: var(--ff-mono); color: var(--muted);">
      <?php esc_html_e( 'Nothing in the journal yet.', 'on1-agency' ); ?>
    </p>
  <?php endif; ?>
</section>

<?php get_footer();

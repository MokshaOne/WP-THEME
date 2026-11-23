<?php
/** VPG v3 · single · Collection (0576) — a curated path through the site. */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>
<main id="vpg-main">
<?php while ( have_posts() ) : the_post();
    $ids = function_exists( 'vpg_curated_ids' ) ? vpg_curated_ids( get_the_ID() ) : [];
?>
  <section class="g-phero"><div class="g-wrap">
    <p class="g-kicker" style="margin-bottom:16px">● <?php esc_html_e( 'Collection', 'vpg-v2' ); ?></p>
    <h1 class="g-display g-phero__title"><?php the_title(); ?></h1>
    <?php if ( get_the_content() ) : ?><div class="g-prose" style="margin-top:16px"><?php the_content(); ?></div><?php endif; ?>
  </div></section>
  <section class="g-section"><div class="g-wrap">
    <div style="display:grid;gap:0">
      <?php $ci = 1; foreach ( $ids as $cid ) : if ( get_post_status( $cid ) !== 'publish' ) continue; ?>
        <a href="<?php echo esc_url( get_permalink( $cid ) ); ?>" style="display:grid;grid-template-columns:44px minmax(0,1fr) auto;gap:20px;align-items:center;padding:18px 0;border-top:1px solid var(--g-line)">
          <span class="g-display" style="font-size:22px;color:var(--g-red)"><?php printf( '%02d', $ci++ ); ?></span>
          <span>
            <span class="g-cat"><?php echo esc_html( get_post_type_object( get_post_type( $cid ) )->labels->singular_name ); ?></span>
            <strong style="display:block;font-size:18px"><?php echo esc_html( get_the_title( $cid ) ); ?></strong>
          </span>
          <?php if ( has_post_thumbnail( $cid ) ) : ?><span style="width:90px"><?php echo get_the_post_thumbnail( $cid, 'thumbnail', [ 'style' => 'width:90px;height:64px;object-fit:cover;display:block' ] ); ?></span><?php endif; ?>
        </a>
      <?php endforeach; ?>
    </div>
  </div></section>
<?php endwhile; ?>
</main>
<?php get_footer();

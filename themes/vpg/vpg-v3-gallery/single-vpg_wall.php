<?php
/** VPG v3 · single · Gallery wall (0286) — a curated set of frames. */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>
<main id="vpg-main">
<?php while ( have_posts() ) : the_post();
    $ids = function_exists( 'vpg_curated_ids' ) ? vpg_curated_ids( get_the_ID() ) : [];
?>
  <section class="g-section--dark" style="padding:clamp(40px,6vw,80px) 0"><div class="g-wrap">
    <p class="g-kicker" style="margin-bottom:16px">● <?php esc_html_e( 'Gallery wall', 'vpg-v2' ); ?></p>
    <h1 class="g-display" style="font-size:clamp(40px,7vw,88px)"><?php the_title(); ?><span style="color:var(--g-red)">.</span></h1>
    <?php if ( get_the_content() ) : ?><div class="g-prose" style="margin-top:18px;color:rgba(255,255,255,.8)"><?php the_content(); ?></div><?php endif; ?>
    <?php $why = get_post_meta( get_the_ID(), '_vpg_curation_why', true );
    if ( $why ) : ?><p style="margin-top:16px;max-width:60ch;color:rgba(255,255,255,.65);font-size:14px;border-left:3px solid var(--g-red);padding-left:14px">● <?php esc_html_e( 'Why these hang', 'vpg-v2' ); ?> — <?php echo esc_html( $why ); ?></p><?php endif; ?>
  </div></section>
  <section class="g-section"><div class="g-wrap">
    <div data-vpg-gallery data-vpg-grid style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:28px">
      <?php foreach ( $ids as $aid ) :
          $img = wp_get_attachment_image_url( $aid, 'large' );
          if ( ! $img ) continue;
          $full = wp_get_attachment_image_url( $aid, 'full' );
          $exifl = function_exists( 'vpg_photo_exif_label' ) ? vpg_photo_exif_label( $aid ) : ''; ?>
        <figure style="margin:0">
          <a href="<?php echo esc_url( get_attachment_link( $aid ) ); ?>" data-full="<?php echo esc_url( $full ); ?>" data-exif="<?php echo esc_attr( $exifl ); ?>" style="display:block;background:var(--g-bg-2)">
            <img src="<?php echo esc_url( $img ); ?>" data-lqip alt="<?php echo esc_attr( get_post_meta( $aid, '_wp_attachment_image_alt', true ) ?: get_the_title( $aid ) ); ?>" loading="lazy" style="width:100%;height:auto;display:block">
          </a>
          <figcaption class="g-meta" style="margin-top:8px"><?php echo esc_html( get_the_title( $aid ) ); ?> — <?php echo esc_html( get_the_author_meta( 'display_name', (int) get_post_field( 'post_author', $aid ) ) ); ?></figcaption>
        </figure>
      <?php endforeach; ?>
    </div>
  </div></section>
<?php endwhile; ?>
</main>
<?php get_footer();

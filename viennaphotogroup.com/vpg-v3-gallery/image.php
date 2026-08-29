<?php
/** VPG v3 · attachment page (0288) — every photo gets a wall of its own. */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>
<main id="vpg-main">
<?php while ( have_posts() ) : the_post();
    $parent = get_post_parent();
    $exif   = function_exists( 'vpg_photo_exif_label' ) ? vpg_photo_exif_label( get_the_ID() ) : '';
    $author = get_the_author();
?>
  <section class="g-section--dark" style="padding:clamp(32px,5vw,64px) 0">
    <div class="g-wrap" style="text-align:center">
      <?php echo wp_get_attachment_image( get_the_ID(), 'full', false, [ 'style' => 'max-width:100%;max-height:80vh;width:auto;height:auto;margin:0 auto;display:block' ] ); ?>
    </div>
  </section>

  <section class="g-section--tight g-section">
    <div class="g-wrap" style="display:flex;gap:32px;flex-wrap:wrap;align-items:baseline">
      <div style="flex:1;min-width:260px">
        <span class="g-kicker"><?php esc_html_e( 'Photograph', 'vpg-v2' ); ?></span>
        <h1 style="font-size:clamp(22px,3vw,34px);margin-top:10px"><?php the_title(); ?></h1>
        <p class="g-meta" style="margin-top:10px"><?php echo esc_html( $author ); ?> · <?php echo esc_html( get_the_date() ); ?><?php if ( $exif ) echo ' · ' . esc_html( $exif ); ?></p>
        <?php if ( has_excerpt() ) : ?><p class="g-lede" style="margin-top:14px;font-size:16px"><?php echo esc_html( get_the_excerpt() ); ?></p><?php endif; ?>
      </div>
      <dl class="g-phero__aside" style="min-width:200px">
        <?php if ( $parent && $parent->post_status === 'publish' ) : ?>
          <dt><?php esc_html_e( 'Belongs to', 'vpg-v2' ); ?></dt><dd><a href="<?php echo esc_url( get_permalink( $parent ) ); ?>"><?php echo esc_html( get_the_title( $parent ) ); ?></a></dd>
        <?php endif; ?>
        <dt><?php esc_html_e( 'Credit', 'vpg-v2' ); ?></dt><dd><?php echo esc_html( $author ); ?> · Vienna Photo Group</dd>
      </dl>
    </div>
  </section>

  <?php if ( function_exists( 'vpg_gallery_image_extras' ) ) vpg_gallery_image_extras( get_the_ID() ); ?>

  <?php if ( function_exists( 'vpg_similar_images' ) ) :
      $similar = vpg_similar_images( get_the_ID() );
      if ( count( $similar ) >= 2 ) : ?>
  <section class="g-section--alt g-section--tight">
    <div class="g-wrap">
      <span class="g-kicker"><?php esc_html_e( 'Visually related', 'vpg-v2' ); ?></span>
      <div data-vpg-gallery data-vpg-grid style="display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:12px;margin-top:14px">
        <?php foreach ( $similar as $sid ) :
            $img = wp_get_attachment_image_url( $sid, 'medium' );
            if ( ! $img ) continue; ?>
          <a href="<?php echo esc_url( get_attachment_link( $sid ) ); ?>" style="display:block;aspect-ratio:1;overflow:hidden;background:var(--g-bg)">
            <img src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( get_the_title( $sid ) ); ?>" loading="lazy" style="width:100%;height:100%;object-fit:cover">
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; endif; ?>

<?php endwhile; ?>
</main>
<?php get_footer();
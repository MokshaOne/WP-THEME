<?php
/** VPG v2 · single · Shop */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>

<main id="vpg-main">
<?php while ( have_posts() ) : the_post();
    $meta = 'shop_district' ? vpg_field( 'shop_district' ) : '';
?>

    <header class="vpg-page-hero">
        <span class="vpg-chip vpg-chip--shop"><span class="vpg-chip__dot"></span> Shop</span>
        <h1><?php the_title(); ?></h1>
        <?php if ( get_the_excerpt() ) : ?><p class="vpg-lede"><?php echo esc_html( get_the_excerpt() ); ?></p><?php endif; ?>
        <?php if ( $meta ) : ?>
            <p class="vpg-caps" style="margin-top:1.5rem">District · <?php echo esc_html( $meta ); ?></p>
        <?php endif; ?>

    </header>

    <?php if ( has_post_thumbnail() ) : ?>
        <figure style="max-width:var(--vpg-max-magazine);margin:2rem auto;padding:0 var(--vpg-sp-5)">
            <?php the_post_thumbnail( 'vpg-hero', [ 'style' => 'width:100%;border-radius:var(--vpg-radius-lg)' ] ); ?>
        </figure>
    <?php endif; ?>

    <section class="vpg-section vpg-section--tight">
        <div class="vpg-wrap--prose">
            <div class="vpg-prose"><?php the_content(); ?></div>
        </div>
    </section>

        <?php
        $coords = vpg_get_coords( get_the_ID() );
        if ( $coords ) :
            $pin = [ [ 'lat' => $coords[0], 'lng' => $coords[1], 'title' => get_the_title(), 'url' => '', 'lede' => '' ] ];
        ?>
            <section class="vpg-section vpg-section--tight">
                <div class="vpg-wrap">
                    <div class="vpg-section-head"><div><p class="vpg-caps">— <?php esc_html_e( 'Where', 'vpg-v2' ); ?></p><h2><?php esc_html_e( 'On the', 'vpg-v2' ); ?> <em><?php esc_html_e( 'map', 'vpg-v2' ); ?></em></h2></div></div>
                    <div id="vpg-map" class="vpg-map" data-pins="<?php echo esc_attr( wp_json_encode( $pin ) ); ?>" style="height:420px"></div>
                </div>
            </section>
        <?php endif; ?>

    <section class="vpg-section vpg-section--surface vpg-section--tight" style="text-align:center">
        <div class="vpg-wrap--narrow">
            <a class="vpg-btn vpg-btn--ghost" href="<?php echo esc_url( get_post_type_archive_link( 'vpg_shop' ) ); ?>"><?php esc_html_e( 'All shops', 'vpg-v2' ); ?> →</a>
        </div>
    </section>

<?php endwhile; ?>
</main>

<?php get_footer();

<?php
/** VPG v2 · single · Event */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>

<main id="vpg-main">
<?php while ( have_posts() ) : the_post();
    $meta = '_vpg_event_date' ? vpg_field( '_vpg_event_date' ) : '';
?>

    <header class="vpg-page-hero">
        <span class="vpg-chip vpg-chip--event"><span class="vpg-chip__dot"></span> Event</span>
        <h1><?php the_title(); ?></h1>
        <?php if ( get_the_excerpt() ) : ?><p class="vpg-lede"><?php echo esc_html( get_the_excerpt() ); ?></p><?php endif; ?>
        <?php if ( $meta ) : ?>
            <p class="vpg-caps" style="margin-top:1.5rem">Date · <?php echo esc_html( $meta ); ?></p>
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



    <section class="vpg-section vpg-section--surface vpg-section--tight" style="text-align:center">
        <div class="vpg-wrap--narrow">
            <a class="vpg-btn vpg-btn--ghost" href="<?php echo esc_url( get_post_type_archive_link( 'vpg_event' ) ); ?>"><?php esc_html_e( 'All events', 'vpg-v2' ); ?> →</a>
        </div>
    </section>

<?php endwhile; ?>
</main>

<?php get_footer();

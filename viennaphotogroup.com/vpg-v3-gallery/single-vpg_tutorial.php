<?php
/** VPG v2 · single · Tutorial (Gallery) */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>

<main id="vpg-main">
<?php while ( have_posts() ) : the_post();
    $meta = 'tutorial_minutes' ? vpg_field( 'tutorial_minutes' ) : '';
?>

    <section class="g-phero">
      <div class="g-wrap">
        <div class="g-phero__grid">
          <div>
            <p class="g-kicker" style="margin-bottom:16px">Tutorial</p>
            <h1 class="g-display g-phero__title" style="max-width:20ch"><?php the_title(); ?></h1>
            <?php if ( get_the_excerpt() ) : ?>
              <p class="g-lede g-phero__lede"><?php echo esc_html( get_the_excerpt() ); ?></p>
            <?php endif; ?>
            <div class="g-byline" style="margin-top:20px">
              <span><?php echo esc_html( get_the_author() ); ?></span><span>·</span>
              <span><?php echo esc_html( get_the_date( 'd M Y' ) ); ?></span>
            </div>
          </div>
          <?php if ( $meta ) : ?>
            <dl class="g-phero__aside">
              <dt>Minutes</dt><dd><?php echo esc_html( $meta ); ?></dd>
            </dl>
          <?php endif; ?>
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
        <div class="g-prose" style="margin:0 auto"><?php the_content(); ?></div>
      </div>
    </section>

    <section class="g-section g-section--dark" style="text-align:center">
      <div class="g-wrap">
        <a class="g-btn g-btn--red g-btn--lg" href="<?php echo esc_url( get_post_type_archive_link( 'vpg_tutorial' ) ); ?>"><?php esc_html_e( 'All tutorials', 'vpg-v2' ); ?> <span class="a">&rarr;</span></a>
      </div>
    </section>

<?php comments_template(); ?>
<?php endwhile; ?>
</main>

<?php get_footer();

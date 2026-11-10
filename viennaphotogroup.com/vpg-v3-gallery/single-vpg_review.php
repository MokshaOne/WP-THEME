<?php
/** VPG v2 · single · Review (Gallery) */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>

<main id="vpg-main">
<?php while ( have_posts() ) : the_post();
    $meta = '' ? vpg_field( '' ) : '';
?>

    <section class="g-phero">
      <div class="g-wrap">
        <p class="g-kicker" style="margin-bottom:16px">Review</p>
        <h1 class="g-display g-phero__title" style="max-width:20ch"><?php the_title(); ?></h1>
        <?php if ( get_the_excerpt() ) : ?>
          <p class="g-lede g-phero__lede"><?php echo esc_html( get_the_excerpt() ); ?></p>
        <?php endif; ?>
        <div class="g-byline" style="margin-top:20px">
          <span><?php echo esc_html( get_the_author() ); ?></span><span>·</span>
          <span><?php echo esc_html( get_the_date( 'd M Y' ) ); ?></span>
          <?php if ( $meta ) : ?><span>·</span><span><?php echo esc_html( $meta ); ?></span><?php endif; ?>
        </div>
        <?php
        $s1 = (float) vpg_field( 'score_design' );
        $s2 = (float) vpg_field( 'score_performance' );
        $s3 = (float) vpg_field( 'score_price' );
        $score = ( $s1 + $s2 + $s3 ) ? round( ( $s1 + $s2 + $s3 ) / 3, 1 ) : 0;
        ?>
        <?php if ( $score ) : ?>
          <div class="g-stats" style="margin-top:clamp(28px,4vw,44px)">
            <div class="g-stat"><div class="g-stat__n"><em><?php echo esc_html( $score ); ?></em><sup>/10</sup></div><div class="g-stat__l"><?php echo esc_html__( 'Overall', 'vpg-v2' ); ?></div></div>
            <div class="g-stat"><div class="g-stat__n"><?php echo esc_html( $s1 ); ?></div><div class="g-stat__l"><?php echo esc_html__( 'Design', 'vpg-v2' ); ?></div></div>
            <div class="g-stat"><div class="g-stat__n"><?php echo esc_html( $s2 ); ?></div><div class="g-stat__l"><?php echo esc_html__( 'Performance', 'vpg-v2' ); ?></div></div>
            <div class="g-stat"><div class="g-stat__n"><?php echo esc_html( $s3 ); ?></div><div class="g-stat__l"><?php echo esc_html__( 'Price', 'vpg-v2' ); ?></div></div>
          </div>
        <?php endif; ?>
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
        <a class="g-btn g-btn--red g-btn--lg" href="<?php echo esc_url( get_post_type_archive_link( 'vpg_review' ) ); ?>"><?php esc_html_e( 'All reviews', 'vpg-v2' ); ?> <span class="a">&rarr;</span></a>
      </div>
    </section>

<?php comments_template(); ?>
<?php endwhile; ?>
</main>

<?php get_footer();

<?php
/** VPG v3 · archive · Project rooms (1001) — who is building what, together. */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

$paged = max( 1, get_query_var( 'paged' ) );
$q = new WP_Query( [ 'post_type' => 'vpg_project', 'posts_per_page' => 12, 'post_status' => 'publish', 'paged' => $paged, 'orderby' => 'modified', 'order' => 'DESC' ] );
?>
<main id="vpg-main">

  <section class="g-phero"><div class="g-wrap"><div class="g-phero__grid">
    <div>
      <p class="g-kicker" style="margin-bottom:18px">● <?php esc_html_e( 'Project rooms', 'vpg-v2' ); ?></p>
      <h1 class="g-display g-phero__title"><?php echo wp_kses_post( __( 'Built <em>together</em>.', 'vpg-v2' ) ); ?></h1>
      <p class="g-lede g-phero__lede"><?php esc_html_e( 'Shared series in progress — a Documentarian opens a room, members join and hang their own published works. Finished rooms become spreads in the magazine.', 'vpg-v2' ); ?></p>
    </div>
    <dl class="g-phero__aside">
      <dt><?php esc_html_e( 'Open rooms', 'vpg-v2' ); ?></dt><dd><?php echo (int) $q->found_posts; ?></dd>
      <dt><?php esc_html_e( 'Who creates', 'vpg-v2' ); ?></dt><dd><?php esc_html_e( 'Documentarian and up', 'vpg-v2' ); ?></dd>
      <dt><?php esc_html_e( 'Who joins', 'vpg-v2' ); ?></dt><dd><?php esc_html_e( 'Every member', 'vpg-v2' ); ?></dd>
    </dl>
  </div></div></section>

  <section class="g-section g-section--alt"><div class="g-wrap">
    <?php if ( $q->have_posts() ) : ?>
      <div class="g-grid3">
        <?php while ( $q->have_posts() ) : $q->the_post();
            $members = function_exists( 'vpg_project_members' ) ? vpg_project_members( get_the_ID() ) : [];
            $works   = array_filter( array_map( 'intval', (array) get_post_meta( get_the_ID(), '_vpg_project_works', true ) ) );
            $done    = get_post_meta( get_the_ID(), '_vpg_project_done', true );
        ?>
          <a class="g-card" href="<?php the_permalink(); ?>">
            <?php if ( has_post_thumbnail() ) : ?><div class="g-fig g-fig--3x2"><?php the_post_thumbnail( 'medium_large' ); ?></div><?php endif; ?>
            <span class="g-cat"><?php echo $done ? esc_html__( 'Finished · magazine-ready', 'vpg-v2' ) : esc_html__( 'In progress', 'vpg-v2' ); ?></span>
            <h3 class="g-card__title"><?php the_title(); ?></h3>
            <div class="g-byline">
              <span><?php printf( esc_html( _n( '%d member', '%d members', count( $members ), 'vpg-v2' ) ), count( $members ) ); ?> · <?php printf( esc_html( _n( '%d work', '%d works', count( $works ), 'vpg-v2' ) ), count( $works ) ); ?></span>
            </div>
          </a>
        <?php endwhile; ?>
      </div>
      <div style="text-align:center;margin-top:clamp(36px,5vw,56px)">
        <?php the_posts_pagination( [ 'prev_text' => '← Previous', 'next_text' => 'Next →', 'mid_size' => 2 ] ); ?>
      </div>
    <?php else : ?>
      <p class="g-lede"><?php esc_html_e( 'No rooms yet — the first Documentarian to open one writes collective history.', 'vpg-v2' ); ?></p>
    <?php endif; ?>
    <p style="margin-top:28px"><a class="g-link" href="<?php echo esc_url( home_url( '/dashboard/#projects' ) ); ?>"><?php esc_html_e( 'Open a room from your dashboard', 'vpg-v2' ); ?> →</a></p>
  </div></section>

</main>
<?php wp_reset_postdata(); get_footer();

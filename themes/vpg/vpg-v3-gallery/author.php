<?php
/** VPG v3 · author.php · 0278 — all texts by one person, cleanly gathered. */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
$author = get_queried_object();
$posts  = get_posts( [ 'post_type' => 'post', 'author' => $author->ID, 'posts_per_page' => 60, 'post_status' => 'publish' ] );
// also texts where this member is the credited co-author
$co = get_posts( [ 'post_type' => 'post', 'posts_per_page' => 60, 'post_status' => 'publish', 'meta_key' => '_vpg_coauthor', 'meta_value' => $author->ID ] );
$all = [];
foreach ( array_merge( $posts, $co ) as $p ) $all[ $p->ID ] = $p;
$all = array_values( $all );
usort( $all, fn( $a, $b ) => strcmp( $b->post_date, $a->post_date ) );
?>
<main id="vpg-main">
  <section class="g-phero"><div class="g-wrap">
    <p class="g-kicker" style="margin-bottom:16px">● <?php esc_html_e( 'Author', 'vpg-v2' ); ?></p>
    <h1 class="g-display g-phero__title"><?php echo esc_html( $author->display_name ); ?><span style="color:var(--g-red)">.</span></h1>
    <?php if ( $author->description ) : ?><p class="g-lede g-phero__lede"><?php echo esc_html( $author->description ); ?></p><?php endif; ?>
    <div class="g-byline" style="margin-top:16px"><span><?php printf( esc_html( _n( '%d text', '%d texts', count( $all ), 'vpg-v2' ) ), count( $all ) ); ?></span>
      <?php if ( function_exists( 'vpg_member_url' ) ) : ?><span>·</span><span><a href="<?php echo esc_url( home_url( '/member/' . $author->user_nicename . '/' ) ); ?>"><?php esc_html_e( 'Portfolio', 'vpg-v2' ); ?> →</a></span><?php endif; ?>
    </div>
  </div></section>

  <section class="g-section"><div class="g-wrap">
    <?php if ( $all ) : ?>
    <div class="g-list">
      <?php foreach ( $all as $p ) :
        $fmt = function_exists( 'vpg_post_format_label' ) ? vpg_post_format_label( $p->ID ) : ''; ?>
        <a class="g-row" href="<?php echo esc_url( get_permalink( $p ) ); ?>">
          <span style="font-weight:700;color:var(--g-mid);min-width:96px"><?php echo esc_html( get_the_date( 'd M Y', $p ) ); ?></span>
          <div>
            <?php if ( $fmt ) : ?><span class="g-cat"><?php echo esc_html( $fmt ); ?></span><?php endif; ?>
            <h3 class="g-row__title" style="margin:0"><?php echo esc_html( get_the_title( $p ) ); ?></h3>
          </div>
          <span class="g-row__when"><?php echo esc_html( function_exists( 'vpg_reading_time' ) ? vpg_reading_time( $p->post_content ) . 'm' : '' ); ?></span>
        </a>
      <?php endforeach; ?>
    </div>
    <?php else : ?>
      <p class="g-lede" style="color:var(--g-mid)"><?php esc_html_e( 'No published texts yet.', 'vpg-v2' ); ?></p>
    <?php endif; ?>
  </div></section>
</main>
<?php get_footer();

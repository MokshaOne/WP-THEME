<?php
/** Template Name: Review Desk
 * 0842 · the review queue in the frontend — editors never need wp-admin. */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

if ( ! current_user_can( 'edit_others_posts' ) ) : ?>
<main id="vpg-main"><section class="g-section"><div class="g-wrap" style="text-align:center">
  <h1 class="g-display" style="font-size:clamp(32px,6vw,64px)"><?php echo wp_kses_post( __( 'Editors <em>only</em>.', 'vpg-v2' ) ); ?></h1>
  <p class="g-lede" style="margin:16px auto 0"><?php esc_html_e( 'The review desk is where editorial works. Your submissions live in your dashboard.', 'vpg-v2' ); ?></p>
</div></section></main>
<?php get_footer(); return; endif;

$types   = function_exists( 'vpg_submittable_types' ) ? vpg_submittable_types() : [ 'post' ];
$want    = sanitize_key( $_GET['type'] ?? '' );                        // 1014 · one type at a time
$q_types = ( $want && in_array( $want, $types, true ) ) ? [ $want ] : $types;
$pending = new WP_Query( [ 'post_type' => $q_types, 'post_status' => 'pending', 'posts_per_page' => 30, 'orderby' => 'date', 'order' => 'ASC' ] );
?>
<main id="vpg-main">
  <section class="g-phero"><div class="g-wrap"><div class="g-phero__grid">
    <div>
      <p class="g-kicker" style="margin-bottom:16px">● <?php esc_html_e( 'Review desk', 'vpg-v2' ); ?></p>
      <h1 class="g-display g-phero__title"><?php echo (int) $pending->found_posts; ?> <?php echo wp_kses_post( __( '<em>waiting</em>.', 'vpg-v2' ) ); ?></h1>
      <p class="g-lede g-phero__lede"><?php esc_html_e( 'Oldest first — the 72-hour promise starts when a member hits submit. Approve publishes and mails; reject always carries feedback.', 'vpg-v2' ); ?></p>
    </div>
    <dl class="g-phero__aside">
      <dt><?php esc_html_e( 'Promise', 'vpg-v2' ); ?></dt><dd><?php esc_html_e( '72h turnaround', 'vpg-v2' ); ?></dd>
      <dt><?php esc_html_e( 'Backstage', 'vpg-v2' ); ?></dt><dd><a href="<?php echo esc_url( admin_url( 'admin.php?page=vpg-hub' ) ); ?>"><?php esc_html_e( 'Open the hub', 'vpg-v2' ); ?></a></dd>
    </dl>
  </div></div></section>

  <section class="g-section"><div class="g-wrap">
    <p style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:22px">
      <a href="<?php echo esc_url( get_permalink() ); ?>" class="g-btn <?php echo $want ? 'g-btn--ghost' : ''; ?>" style="font-size:11px;padding:8px 14px"><?php esc_html_e( 'All', 'vpg-v2' ); ?></a>
      <?php foreach ( $types as $tt ) : $to = get_post_type_object( $tt ); if ( ! $to ) continue; ?>
        <a href="<?php echo esc_url( add_query_arg( 'type', $tt, get_permalink() ) ); ?>" class="g-btn <?php echo $want === $tt ? '' : 'g-btn--ghost'; ?>" style="font-size:11px;padding:8px 14px"><?php echo esc_html( $to->labels->singular_name ); ?></a>
      <?php endforeach; ?>
    </p>
    <?php if ( ! $pending->have_posts() ) : ?>
      <p style="border:1px solid var(--g-line);padding:48px;text-align:center;font-weight:700">⁂ <?php esc_html_e( 'Inbox zero. Nothing waiting for editorial.', 'vpg-v2' ); ?></p>
    <?php else : ?>
      <div style="display:grid;gap:0">
      <?php while ( $pending->have_posts() ) : $pending->the_post();
          $age_h = ( time() - get_post_time( 'U', true ) ) / HOUR_IN_SECONDS;
          $sla   = $age_h >= 72 ? 'var(--g-red)' : ( $age_h >= 48 ? '#996800' : 'var(--g-mid)' );
      ?>
        <article style="border-top:1px solid var(--g-line);padding:24px 0;display:grid;grid-template-columns:minmax(0,1fr) 260px;gap:28px">
          <div>
            <p class="g-meta" style="margin-bottom:6px">
              <?php echo esc_html( get_post_type_object( get_post_type() )->labels->singular_name ); ?>
              · <?php the_author(); ?>
              · <span style="color:<?php echo esc_attr( $sla ); ?>;font-weight:800"><?php printf( esc_html__( '%dh in queue', 'vpg-v2' ), (int) $age_h ); ?></span>
            </p>
            <h2 style="font-size:22px"><?php the_title(); ?></h2>
            <?php if ( has_post_thumbnail() ) : ?><div style="max-width:340px;margin:12px 0"><?php the_post_thumbnail( 'medium_large', [ 'style' => 'width:100%;height:auto;display:block' ] ); ?></div><?php endif; ?>
            <p style="color:var(--g-mid);font-size:14px;line-height:1.6"><?php echo esc_html( wp_trim_words( get_the_content(), 60 ) ); ?></p>
            <p style="margin-top:8px"><a class="g-link" style="font-size:11px" href="<?php echo esc_url( function_exists( 'vpg_preview_url' ) ? vpg_preview_url( get_the_ID() ) : get_preview_post_link() ); ?>" target="_blank"><?php esc_html_e( 'Full preview', 'vpg-v2' ); ?> ↗</a></p>
          </div>
          <div style="display:grid;gap:10px;align-content:start">
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
              <?php wp_nonce_field( 'vpg_front_review' ); ?>
              <input type="hidden" name="action" value="vpg_front_review">
              <input type="hidden" name="post" value="<?php echo (int) get_the_ID(); ?>">
              <input type="hidden" name="act" value="approve">
              <button class="g-btn g-btn--red" type="submit" style="width:100%">✓ <?php esc_html_e( 'Publish', 'vpg-v2' ); ?></button>
            </form>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:grid;gap:8px">
              <?php wp_nonce_field( 'vpg_front_review' ); ?>
              <input type="hidden" name="action" value="vpg_front_review">
              <input type="hidden" name="post" value="<?php echo (int) get_the_ID(); ?>">
              <input type="hidden" name="act" value="reject">
              <textarea class="g-textarea" name="reason" rows="2" placeholder="<?php esc_attr_e( 'Feedback for the member — required in spirit.', 'vpg-v2' ); ?>"></textarea>
              <button class="g-btn g-btn--ghost" type="submit">✕ <?php esc_html_e( 'Return with feedback', 'vpg-v2' ); ?></button>
            </form>
          </div>
        </article>
      <?php endwhile; wp_reset_postdata(); ?>
      </div>
    <?php endif; ?>
  </div></section>
</main>
<?php get_footer();

<?php
/** Template Name: The Ladder (Ranks FAQ)
 *
 * 0377 · The rank system, explained on one page — rendered straight
 * from vpg_rank_ladder() and vpg_rank_privileges(), so the page can
 * never drift out of sync with the code that enforces it.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

$ladder = function_exists( 'vpg_rank_ladder' ) ? vpg_rank_ladder() : [];
$rank   = is_user_logged_in() && function_exists( 'vpg_member_rank' ) ? vpg_member_rank( get_current_user_id() ) : null;
?>
<main id="vpg-main">

  <section class="g-phero">
    <div class="g-wrap">
      <div class="g-phero__grid">
        <div>
          <p class="g-kicker" style="margin-bottom:18px">● <?php esc_html_e( 'The ladder', 'vpg-v2' ); ?></p>
          <h1 class="g-display g-phero__title"><?php echo wp_kses_post( __( 'Earned, never <em>bought</em>.', 'vpg-v2' ) ); ?></h1>
          <p class="g-lede g-phero__lede"><?php esc_html_e( 'Four ranks, one direction: the map comes first. Every rank is earned in the formats of the stage before it — and brings real privileges, never obligations.', 'vpg-v2' ); ?></p>
        </div>
        <dl class="g-phero__aside">
          <?php if ( $rank ) : ?>
            <dt><?php esc_html_e( 'Your rank', 'vpg-v2' ); ?></dt><dd><?php echo esc_html( $rank['label'] ); ?></dd>
            <?php if ( $rank['next'] ) : ?>
              <dt><?php esc_html_e( 'Next', 'vpg-v2' ); ?></dt><dd><?php printf( esc_html__( '%1$d of %2$d %3$s', 'vpg-v2' ), (int) $rank['next_have'], (int) $rank['next_need'], esc_html( $rank['next_goal'] ) ); ?></dd>
            <?php endif; ?>
          <?php else : ?>
            <dt><?php esc_html_e( 'Entry', 'vpg-v2' ); ?></dt><dd><a href="<?php echo esc_url( home_url( '/join/' ) ); ?>"><?php esc_html_e( 'Join free', 'vpg-v2' ); ?></a></dd>
          <?php endif; ?>
          <dt><?php esc_html_e( 'Cost', 'vpg-v2' ); ?></dt><dd><?php esc_html_e( 'Free · always', 'vpg-v2' ); ?></dd>
        </dl>
      </div>
    </div>
  </section>

  <section class="g-section">
    <div class="g-wrap">
      <div class="g-list" style="display:grid;gap:0">

        <div style="display:grid;grid-template-columns:56px 1fr;gap:24px;padding:28px 0;border-top:2px solid var(--g-ink)">
          <span class="g-display" style="font-size:28px;color:var(--g-red)">0</span>
          <div>
            <h2 style="font-size:20px;font-weight:800;text-transform:uppercase;letter-spacing:.02em"><?php esc_html_e( 'Member', 'vpg-v2' ); ?></h2>
            <p class="g-row__lede" style="margin-top:6px"><?php esc_html_e( 'The door. You join free, confirm your email, and start feeding the map: locations, studios, shops — everything through the review desk within 72 hours.', 'vpg-v2' ); ?></p>
          </div>
        </div>

        <?php
        $stage_notes = [
            __( 'Unlocks gear reviews, tutorial pitches and journal stories. Your map entries now publish instantly — you earned the map’s trust on the map.', 'vpg-v2' ),
            __( 'Unlocks photowalk proposals and photo trails — curating routes and leading people needs deep map knowledge. Everything but journal stories publishes instantly, and you can edit your own live pieces.', 'vpg-v2' ),
            __( 'The backbone. Everything you submit goes live instantly, journal included, and your photos carry a VIP mark in the magazine’s photo picker — editorial reaches for your work first.', 'vpg-v2' ),
        ];
        foreach ( $ladder as $i => $stage ) : ?>
        <div style="display:grid;grid-template-columns:56px 1fr;gap:24px;padding:28px 0;border-top:1px solid var(--g-line)">
          <span class="g-display" style="font-size:28px;color:var(--g-red)"><?php echo (int) ( $i + 1 ); ?></span>
          <div>
            <h2 style="font-size:20px;font-weight:800;text-transform:uppercase;letter-spacing:.02em"><?php echo esc_html( $stage['label'] ); ?></h2>
            <p style="margin-top:4px;font-size:12px;font-weight:800;letter-spacing:.14em;text-transform:uppercase;color:var(--g-red)"><?php
                printf( esc_html__( '+ %1$d published %2$s', 'vpg-v2' ), (int) $stage['need'], esc_html( $stage['goal'] ) );
            ?></p>
            <p class="g-row__lede" style="margin-top:8px"><?php echo esc_html( $stage_notes[ $i ] ?? '' ); ?></p>
          </div>
        </div>
        <?php endforeach; ?>

      </div>

      <div class="g-prose" style="margin-top:48px;max-width:68ch">
        <h2 style="font-size:16px;font-weight:800;text-transform:uppercase;letter-spacing:.1em;margin-bottom:12px">● <?php esc_html_e( 'The fine print — short and honest', 'vpg-v2' ); ?></h2>
        <p><?php esc_html_e( 'Stages are strictly sequential: sixty journal drafts don’t count until the locations exist. Instant publishing requires a confirmed email and a clean record — open reports pause every privilege until resolved, whatever your rank. You never lose a rank for taking a break. Rule changes never apply retroactively. And nothing here is for sale.', 'vpg-v2' ); ?></p>
      </div>
    </div>
  </section>

  <?php if ( ! is_user_logged_in() ) : ?>
  <section class="g-section--dark g-section">
    <div class="g-wrap" style="text-align:center">
      <span class="g-kicker"><?php esc_html_e( 'Step one', 'vpg-v2' ); ?></span>
      <h2 class="g-display g-cta__title" style="margin:18px auto 22px;max-width:16ch"><?php echo wp_kses_post( __( 'The map is <em>waiting</em>.', 'vpg-v2' ) ); ?></h2>
      <div><a class="g-btn g-btn--lg g-btn--red" href="<?php echo esc_url( home_url( '/join/' ) ); ?>"><?php esc_html_e( 'Join free', 'vpg-v2' ); ?> <span class="a">→</span></a></div>
    </div>
  </section>
  <?php endif; ?>

</main>
<?php get_footer();
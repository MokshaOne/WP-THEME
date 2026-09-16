<?php
/** Template Name: Editorial team */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>
<main id="vpg-main">

  <section class="g-phero">
    <div class="g-wrap">
      <div class="g-phero__grid">
        <div>
          <p class="g-kicker" style="margin-bottom:18px">● <?php esc_html_e( 'Team', 'vpg-v2' ); ?></p>
          <h1 class="g-display g-phero__title"><?php echo wp_kses_post( __( 'Who runs the <em>magazine</em>.', 'vpg-v2' ) ); ?></h1>
          <p class="g-lede g-phero__lede"><?php esc_html_e( 'A small editorial circle. Member-run, no hierarchy beyond the four roles required to ship an issue every month.', 'vpg-v2' ); ?></p>
        </div>
        <dl class="g-phero__aside">
          <dt><?php esc_html_e( 'Structure', 'vpg-v2' ); ?></dt><dd><?php esc_html_e( 'Member-run · no hierarchy', 'vpg-v2' ); ?></dd>
          <dt><?php esc_html_e( 'Cycle', 'vpg-v2' ); ?></dt><dd><?php esc_html_e( 'Monthly, four chairs', 'vpg-v2' ); ?></dd>
          <dt><?php esc_html_e( 'Produced by', 'vpg-v2' ); ?></dt><dd><?php esc_html_e( 'Raveenthiran × on1.agency', 'vpg-v2' ); ?></dd>
        </dl>
      </div>
    </div>
  </section>

  <section class="g-section">
    <div class="g-wrap">
      <div class="g-head">
        <div>
          <span class="g-kicker"><?php esc_html_e( 'Founding', 'vpg-v2' ); ?></span>
          <h2 class="g-head__t"><?php echo wp_kses_post( __( 'The <em>desk</em>.', 'vpg-v2' ) ); ?></h2>
        </div>
        <a class="g-link" href="<?php echo esc_url( home_url('/join/') ); ?>"><?php esc_html_e( 'Join them', 'vpg-v2' ); ?> <span class="a">→</span></a>
      </div>
      <div class="g-team">
        <div class="g-member">
          <div class="g-member__n">Nishuthan Raveenthiran</div>
          <div class="g-member__r"><?php esc_html_e( 'Founding · Photography', 'vpg-v2' ); ?></div>
          <p style="margin-top:10px"><a class="g-link" href="https://raveenthiran.com" target="_blank" rel="noopener">raveenthiran.com <span class="a">→</span></a></p>
        </div>
        <div class="g-member">
          <div class="g-member__n">on1.agency</div>
          <div class="g-member__r"><?php esc_html_e( 'Founding · Platform &amp; editorial systems', 'vpg-v2' ); ?></div>
          <p style="margin-top:10px"><a class="g-link" href="https://on1.agency" target="_blank" rel="noopener">on1.agency <span class="a">→</span></a></p>
        </div>
      </div>
    </div>
  </section>

  <section class="g-section g-section--alt">
    <div class="g-wrap">
      <div class="g-head">
        <div>
          <span class="g-kicker"><?php esc_html_e( 'Roles', 'vpg-v2' ); ?></span>
          <h2 class="g-head__t"><?php echo wp_kses_post( __( 'Four <em>chairs</em>, rotating.', 'vpg-v2' ) ); ?></h2>
        </div>
        <div class="g-meta"><?php esc_html_e( 'Monthly cycle', 'vpg-v2' ); ?></div>
      </div>
      <div class="g-team">
        <div class="g-member">
          <div class="g-member__n"><?php esc_html_e( 'Editor', 'vpg-v2' ); ?></div>
          <div class="g-member__r"><?php esc_html_e( 'Curates the monthly issue, commissions pitches, writes the editor\'s letter.', 'vpg-v2' ); ?></div>
        </div>
        <div class="g-member">
          <div class="g-member__n"><?php esc_html_e( 'Cartographer', 'vpg-v2' ); ?></div>
          <div class="g-member__r"><?php esc_html_e( 'Reviews and approves location submissions, verifies coordinates, maintains the index.', 'vpg-v2' ); ?></div>
        </div>
        <div class="g-member">
          <div class="g-member__n"><?php esc_html_e( 'Reviewer', 'vpg-v2' ); ?></div>
          <div class="g-member__r"><?php esc_html_e( 'Tests gear, writes shop and studio reviews, scores entries on Design / Performance / Price.', 'vpg-v2' ); ?></div>
        </div>
        <div class="g-member">
          <div class="g-member__n"><?php esc_html_e( 'Producer', 'vpg-v2' ); ?></div>
          <div class="g-member__r"><?php esc_html_e( 'Layouts, PDF, print coordination, member communications. The boring-but-essential chair.', 'vpg-v2' ); ?></div>
        </div>
      </div>
    </div>
  </section>

  <section class="g-section">
    <div class="g-wrap">
      <div class="g-twocol">
        <div></div>
        <aside>
          <p class="g-pull"><?php echo wp_kses_post( __( 'The editorial circle stays small on purpose — so every issue still has a person responsible for every <em>line</em>.', 'vpg-v2' ) ); ?><span class="g-pull__cite"><?php esc_html_e( '— Editorial principle', 'vpg-v2' ); ?></span></p>
        </aside>
      </div>
    </div>
  </section>

</main>
<?php get_footer();

<?php
/** Template Name: Glossary
 * 0485 · photography terms A–Z, managed under the VPG hub. */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
$terms = function_exists( 'vpg_glossary_terms' ) ? vpg_glossary_terms() : [];
?>
<main id="vpg-main">
  <section class="g-phero"><div class="g-wrap"><div class="g-phero__grid">
    <div>
      <p class="g-kicker" style="margin-bottom:16px">● <?php esc_html_e( 'Glossary', 'vpg-v2' ); ?></p>
      <h1 class="g-display g-phero__title"><?php echo wp_kses_post( __( 'The <em>words</em>.', 'vpg-v2' ) ); ?></h1>
      <p class="g-lede g-phero__lede"><?php esc_html_e( 'Photography vocabulary, explained in one honest sentence each — the companion to every tutorial.', 'vpg-v2' ); ?></p>
    </div>
    <dl class="g-phero__aside">
      <dt><?php esc_html_e( 'Terms', 'vpg-v2' ); ?></dt><dd><?php echo count( $terms ); ?></dd>
      <dt><?php esc_html_e( 'Missing one?', 'vpg-v2' ); ?></dt><dd><a href="<?php echo esc_url( home_url( '/dashboard/' ) ); ?>"><?php esc_html_e( 'Idea box', 'vpg-v2' ); ?></a></dd>
    </dl>
  </div></div></section>
  <section class="g-section"><div class="g-wrap">
    <?php if ( ! $terms ) : ?>
      <p class="g-lede"><?php esc_html_e( 'The glossary is being written — first terms land soon.', 'vpg-v2' ); ?></p>
    <?php else :
      $letter = '';
      foreach ( $terms as $term => $def ) :
          $first = mb_strtoupper( mb_substr( $term, 0, 1 ) );
          if ( $first !== $letter ) : $letter = $first; ?>
            <h2 class="g-display" style="font-size:34px;color:var(--g-red);margin:36px 0 8px"><?php echo esc_html( $letter ); ?></h2>
          <?php endif; ?>
          <div style="display:grid;grid-template-columns:minmax(120px,220px) 1fr;gap:20px;padding:10px 0;border-top:1px solid var(--g-line)">
            <strong id="<?php echo esc_attr( sanitize_title( $term ) ); ?>"><?php echo esc_html( $term ); ?></strong>
            <span style="color:var(--g-mid);font-size:14.5px;line-height:1.6"><?php echo esc_html( $def ); ?></span>
          </div>
      <?php endforeach; endif; ?>
      <?php // 0276 · members grow the Vienna lexicon
      if ( shortcode_exists( 'vpg_lexicon_suggest' ) ) : ?>
        <div style="border-top:1px solid var(--g-line,#E6E5E1);margin-top:32px;padding-top:20px">
          <p class="g-kicker" style="margin-bottom:8px">● <?php esc_html_e( 'Missing a term?', 'vpg-v2' ); ?></p>
          <?php echo do_shortcode( '[vpg_lexicon_suggest]' ); ?>
        </div>
      <?php endif; ?>
  </div></section>
</main>
<?php get_footer();

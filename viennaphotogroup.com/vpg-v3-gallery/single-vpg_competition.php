<?php
/** VPG v3 · single · Competition — entries wall, member submissions, one winner. */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>
<main id="vpg-main">
<?php while ( have_posts() ) : the_post();
    $entries = function_exists( 'vpg_competition_entries' ) ? vpg_competition_entries( get_the_ID() ) : [];
    $winner  = (int) get_post_meta( get_the_ID(), '_vpg_comp_winner', true );
    $closed  = get_post_meta( get_the_ID(), '_vpg_comp_closed', true ) === '1';
?>

    <section class="g-phero">
      <div class="g-wrap">
        <div class="g-phero__grid">
          <div>
            <p class="g-kicker" style="margin-bottom:16px">● <?php esc_html_e( 'Competition', 'vpg-v2' ); ?><?php if ( $closed ) echo ' · ' . esc_html__( 'closed', 'vpg-v2' ); ?></p>
            <h1 class="g-display g-phero__title"><?php the_title(); ?></h1>
            <?php if ( get_the_excerpt() ) : ?><p class="g-lede g-phero__lede"><?php echo esc_html( get_the_excerpt() ); ?></p><?php endif; ?>
          </div>
          <dl class="g-phero__aside">
            <dt><?php esc_html_e( 'Entries', 'vpg-v2' ); ?></dt><dd><?php echo count( $entries ); ?></dd>
            <dt><?php esc_html_e( 'Status', 'vpg-v2' ); ?></dt><dd><?php echo $closed ? esc_html__( 'Winner picked', 'vpg-v2' ) : esc_html__( 'Open for entries', 'vpg-v2' ); ?></dd>
            <dt><?php esc_html_e( 'Who', 'vpg-v2' ); ?></dt><dd><?php esc_html_e( 'Members · free', 'vpg-v2' ); ?></dd>
          </dl>
        </div>
      </div>
    </section>

    <?php if ( get_the_content() ) : ?>
    <section class="g-section g-section--tight">
      <div class="g-wrap"><div class="g-prose" style="margin:0 auto"><?php the_content(); ?></div></div>
    </section>
    <?php endif; ?>

    <?php if ( $winner && wp_attachment_is_image( $winner ) ) : ?>
    <!-- The winner · hung on its own wall -->
    <section class="g-section g-section--dark">
      <div class="g-wrap" style="text-align:center">
        <span class="g-kicker"><?php esc_html_e( 'The winner', 'vpg-v2' ); ?></span>
        <figure style="margin:28px auto 0;max-width:820px">
          <img src="<?php echo esc_url( wp_get_attachment_image_url( $winner, 'large' ) ); ?>" alt="" style="width:100%;display:block">
          <?php $jury = get_post_meta( get_the_ID(), '_vpg_comp_reason', true ); if ( $jury ) : ?>
            <blockquote style="margin:18px 0 0;padding:14px 20px;border-left:3px solid var(--g-red,#E5341F);font-size:15px;line-height:1.6"><?php echo esc_html( $jury ); ?><br><cite style="font-style:normal;font-size:11px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:var(--g-mid,#6A6A6A)">— <?php esc_html_e( 'The jury', 'vpg-v2' ); ?></cite></blockquote>
          <?php endif; ?>
          <?php if ( current_user_can( 'edit_others_posts' ) ) : ?>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:14px">
              <?php wp_nonce_field( 'vpg_winner_reason' ); ?>
              <input type="hidden" name="action" value="vpg_winner_reason">
              <input type="hidden" name="competition" value="<?php echo (int) get_the_ID(); ?>">
              <textarea name="reason" rows="2" style="width:100%;font:inherit;padding:8px" placeholder="<?php esc_attr_e( 'Why this picture won — two sentences from the jury.', 'vpg-v2' ); ?>"><?php echo esc_textarea( $jury ); ?></textarea>
              <button class="g-btn" type="submit" style="margin-top:8px"><?php esc_html_e( 'Save jury note', 'vpg-v2' ); ?></button>
            </form>
          <?php endif; ?>
          <figcaption class="g-meta" style="margin-top:12px;color:rgba(255,255,255,.7)">
            <?php echo esc_html( get_the_author_meta( 'display_name', (int) get_post_field( 'post_author', $winner ) ) ); ?>
          </figcaption>
        </figure>
      </div>
    </section>
    <?php endif; ?>

    <!-- Enter -->
    <?php if ( ! $closed ) : ?>
    <section class="g-section g-section--alt g-section--tight">
      <div class="g-wrap" style="max-width:720px">
        <?php if ( is_user_logged_in() ) : ?>
          <span class="g-kicker"><?php esc_html_e( 'Your entry', 'vpg-v2' ); ?></span>
          <h2 class="g-head__t" style="margin:14px 0 22px"><?php echo wp_kses_post( __( 'Hang your <em>frame</em>.', 'vpg-v2' ) ); ?></h2>
          <form class="g-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data" style="max-width:none">
            <?php wp_nonce_field( 'vpg_competition_enter' ); ?>
            <input type="hidden" name="action" value="vpg_competition_enter">
            <input type="hidden" name="competition" value="<?php echo (int) get_the_ID(); ?>">
            <div class="g-field">
              <label for="entry"><?php esc_html_e( 'Photo · JPG/PNG/WebP, max 8 MB', 'vpg-v2' ); ?></label>
              <input class="g-input" id="entry" type="file" name="entry" required accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
            </div>
            <button class="g-btn g-btn--lg g-btn--red" type="submit"><?php esc_html_e( 'Submit entry', 'vpg-v2' ); ?> <span class="a">→</span></button>
            <p class="g-form__note"><?php esc_html_e( 'Your photo stays yours · credited by name · one winner goes into the next issue.', 'vpg-v2' ); ?></p>
          </form>
        <?php else : ?>
          <div style="text-align:center">
            <p class="g-lede" style="margin:0 auto 20px;text-align:center"><?php esc_html_e( 'Entering is a member thing — membership is free.', 'vpg-v2' ); ?></p>
            <a class="g-btn g-btn--red" href="<?php echo esc_url( home_url( '/join/' ) ); ?>"><?php esc_html_e( 'Join free', 'vpg-v2' ); ?> →</a>
          </div>
        <?php endif; ?>
      </div>
    </section>
    <?php endif; ?>

    <!-- Entries wall -->
    <section class="g-section" id="entries">
      <div class="g-wrap">
        <div class="g-head">
          <div>
            <span class="g-kicker"><?php esc_html_e( 'Entries', 'vpg-v2' ); ?></span>
            <h2 class="g-head__t"><?php echo wp_kses_post( __( 'The <em>wall</em>.', 'vpg-v2' ) ); ?></h2>
          </div>
        </div>
        <?php if ( $entries ) : ?>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:24px">
          <?php foreach ( $entries as $e ) :
              $img = wp_get_attachment_image_url( $e->ID, 'medium_large' );
              if ( ! $img ) continue;
          ?>
          <figure style="margin:0">
            <div style="aspect-ratio:4/5;overflow:hidden;background:var(--g-bg-2)<?php echo $winner === (int) $e->ID ? ';outline:3px solid var(--g-red)' : ''; ?>">
              <img src="<?php echo esc_url( $img ); ?>" alt="" loading="lazy" style="width:100%;height:100%;object-fit:cover">
            </div>
            <figcaption style="display:flex;justify-content:space-between;gap:10px;margin-top:8px">
              <span class="g-meta"><?php echo esc_html( get_the_author_meta( 'display_name', (int) $e->post_author ) ); ?><?php if ( $winner === (int) $e->ID ) : ?> · <strong style="color:var(--g-red)"><?php esc_html_e( 'Winner', 'vpg-v2' ); ?></strong><?php endif; ?></span>
              <?php if ( ! $closed && current_user_can( 'edit_others_posts' ) ) : ?>
                <a class="g-meta" style="color:var(--g-red)" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=vpg_competition_winner&competition=' . get_the_ID() . '&entry=' . $e->ID ), 'vpg_competition_winner' ) ); ?>" onclick="return confirm('<?php echo esc_js( __( 'Pick this entry as the winner and close the competition?', 'vpg-v2' ) ); ?>')"><?php esc_html_e( 'Pick winner', 'vpg-v2' ); ?></a>
              <?php endif; ?>
            </figcaption>
          </figure>
          <?php endforeach; ?>
        </div>
        <?php else : ?>
          <p class="g-lede" style="color:var(--g-mid)"><?php esc_html_e( 'No entries yet — the wall is waiting for the first frame.', 'vpg-v2' ); ?></p>
        <?php endif; ?>
      </div>
    </section>

<?php endwhile; ?>
</main>
<?php get_footer();

<?php
/** VPG v3 · single · Project room (0401) — a shared series in progress. */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>
<main id="vpg-main">
<?php while ( have_posts() ) : the_post();
    $members  = function_exists( 'vpg_project_members' ) ? vpg_project_members( get_the_ID() ) : [];
    $works    = array_filter( array_map( 'intval', (array) get_post_meta( get_the_ID(), '_vpg_project_works', true ) ) );
    $me       = get_current_user_id();
    $is_in    = $me && in_array( $me, $members, true );
    $founder  = (int) get_post_field( 'post_author', get_the_ID() );
    $is_fndr  = $me && ( $me === $founder || current_user_can( 'edit_others_posts' ) );
    $is_done  = (bool) get_post_meta( get_the_ID(), '_vpg_project_done', true );
    $is_circ  = get_post_meta( get_the_ID(), '_vpg_circle', true ) === '1';
?>
  <section class="g-phero"><div class="g-wrap"><div class="g-phero__grid">
    <div>
      <p class="g-kicker" style="margin-bottom:16px">● <?php echo $is_done ? esc_html__( 'Project · finished — magazine-ready', 'vpg-v2' ) : ( $is_circ ? esc_html__( 'Critique circle · six chairs, honest eyes', 'vpg-v2' ) : esc_html__( 'Project room', 'vpg-v2' ) ); ?></p>
      <h1 class="g-display g-phero__title"><?php the_title(); ?></h1>
      <div class="g-prose" style="margin-top:16px"><?php the_content(); ?></div>
    </div>
    <dl class="g-phero__aside">
      <dt><?php esc_html_e( 'Founded by', 'vpg-v2' ); ?></dt><dd><?php the_author(); ?></dd>
      <dt><?php esc_html_e( 'Members', 'vpg-v2' ); ?></dt><dd><?php echo count( $members ); ?></dd>
      <dt><?php esc_html_e( 'Works hung', 'vpg-v2' ); ?></dt><dd><?php echo count( $works ); ?></dd>
    </dl>
  </div></div></section>

  <section class="g-section--alt g-section--tight"><div class="g-wrap" style="display:flex;gap:20px;align-items:center;flex-wrap:wrap">
    <div style="display:flex;gap:12px;flex-wrap:wrap;flex:1">
      <?php foreach ( array_slice( $members, 0, 16 ) as $mid ) : $mu = get_userdata( $mid ); if ( ! $mu ) continue; ?>
        <a href="<?php echo esc_url( home_url( '/members/' . $mu->user_nicename . '/' ) ); ?>" style="display:flex;gap:8px;align-items:center;text-decoration:none">
          <?php echo get_avatar( $mid, 32, '', esc_attr( $mu->display_name ), [ 'style' => 'display:block;width:32px;height:32px;object-fit:cover' ] ); ?>
          <span style="font-size:12px;font-weight:700"><?php echo esc_html( $mu->display_name ); ?></span>
        </a>
      <?php endforeach; ?>
    </div>
    <?php if ( $is_fndr ) : ?>
      <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin:0">
        <?php wp_nonce_field( 'vpg_project_finish' ); ?>
        <input type="hidden" name="action" value="vpg_project_finish">
        <input type="hidden" name="project" value="<?php echo (int) get_the_ID(); ?>">
        <button class="g-btn g-btn--ghost" type="submit"><?php echo $is_done ? esc_html__( 'Reopen the room', 'vpg-v2' ) : esc_html__( '⁂ Mark finished', 'vpg-v2' ); ?></button>
      </form>
    <?php endif; ?>
    <?php if ( is_user_logged_in() ) : ?>
      <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin:0">
        <?php wp_nonce_field( 'vpg_project_join' ); ?>
        <input type="hidden" name="action" value="vpg_project_join">
        <input type="hidden" name="project" value="<?php echo (int) get_the_ID(); ?>">
        <button class="g-btn <?php echo $is_in ? 'g-btn--ghost' : 'g-btn--red'; ?>" type="submit"><?php echo $is_in ? esc_html__( '✓ In · leave', 'vpg-v2' ) : esc_html__( 'Join the project', 'vpg-v2' ); ?></button>
      </form>
    <?php endif; ?>
  </div></section>

  <?php if ( $works ) : ?>
  <section class="g-section"><div class="g-wrap">
    <div class="g-head"><div><span class="g-kicker"><?php esc_html_e( 'The wall so far', 'vpg-v2' ); ?></span>
      <h2 class="g-head__t"><?php echo count( $works ); ?> <em><?php esc_html_e( 'works', 'vpg-v2' ); ?></em></h2></div></div>
    <div class="g-grid3">
      <?php foreach ( $works as $wid ) : if ( get_post_status( $wid ) !== 'publish' ) continue; ?>
        <div>
        <a class="g-card" href="<?php echo esc_url( get_permalink( $wid ) ); ?>">
          <?php if ( has_post_thumbnail( $wid ) ) : ?><div class="g-fig g-fig--3x2"><?php echo get_the_post_thumbnail( $wid, 'medium_large' ); ?></div><?php endif; ?>
          <span class="g-cat"><?php echo esc_html( get_the_author_meta( 'display_name', (int) get_post_field( 'post_author', $wid ) ) ); ?></span>
          <h3 class="g-card__title"><?php echo esc_html( get_the_title( $wid ) ); ?></h3>
        </a>
        <?php if ( $me && ( $me === (int) get_post_field( 'post_author', $wid ) || $is_fndr ) ) : ?>
          <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin:6px 0 0">
            <?php wp_nonce_field( 'vpg_project_unhang' ); ?>
            <input type="hidden" name="action" value="vpg_project_unhang">
            <input type="hidden" name="project" value="<?php echo (int) get_the_ID(); ?>">
            <input type="hidden" name="work" value="<?php echo (int) $wid; ?>">
            <button type="submit" style="background:none;border:0;padding:0;cursor:pointer;font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--g-mid)">✕ <?php esc_html_e( 'Take down', 'vpg-v2' ); ?></button>
          </form>
        <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div></section>
  <?php endif; ?>

  <?php if ( $is_in ) :
      $mine = get_posts( [ 'author' => $me, 'post_type' => function_exists( 'vpg_submittable_types' ) ? vpg_submittable_types() : [ 'post' ], 'post_status' => 'publish', 'posts_per_page' => 40, 'exclude' => $works ] );
      if ( $mine ) : ?>
  <section class="g-section--alt g-section--tight"><div class="g-wrap">
    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:flex;gap:12px;align-items:end;flex-wrap:wrap">
      <?php wp_nonce_field( 'vpg_project_hang' ); ?>
      <input type="hidden" name="action" value="vpg_project_hang">
      <input type="hidden" name="project" value="<?php echo (int) get_the_ID(); ?>">
      <div style="flex:1;min-width:260px">
        <label class="g-label" for="proj-work" style="font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--g-mid)"><?php esc_html_e( 'Hang one of your published works', 'vpg-v2' ); ?></label>
        <select class="g-select" id="proj-work" name="work" style="width:100%">
          <?php foreach ( $mine as $mw ) : ?><option value="<?php echo (int) $mw->ID; ?>"><?php echo esc_html( $mw->post_title ); ?></option><?php endforeach; ?>
        </select>
      </div>
      <button class="g-btn" type="submit"><?php esc_html_e( 'Hang it', 'vpg-v2' ); ?></button>
    </form>
  </div></section>
  <?php endif; endif; ?>

  <?php // 0402 · circles live from feedback — the thread is members-only to write
  if ( $is_circ && ( $is_in || current_user_can( 'edit_others_posts' ) ) ) : ?>
  <section class="g-section"><div class="g-wrap" style="max-width:760px">
    <div class="g-head"><div><span class="g-kicker"><?php esc_html_e( 'The critique thread', 'vpg-v2' ); ?></span>
      <h2 class="g-head__t"><?php echo wp_kses_post( __( 'Say it <em>kindly, clearly</em>.', 'vpg-v2' ) ); ?></h2></div></div>
    <?php comments_template(); ?>
  </div></section>
  <?php endif; ?>

<?php endwhile; ?>
</main>
<?php get_footer();

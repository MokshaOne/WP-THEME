<?php
/**
 * archive-project.php — All projects.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();
?>

<section class="opening" style="padding-bottom: 3rem;">
  <div class="opening-meta" style="grid-template-columns: 1fr 1fr;">
    <div><?php esc_html_e( 'Section', 'on1-agency' ); ?><b><?php esc_html_e( 'Selected Work', 'on1-agency' ); ?></b></div>
    <div class="last"><?php esc_html_e( 'Edition', 'on1-agency' ); ?><b><?php echo esc_html( on1_edition_label() ); ?></b></div>
  </div>
  <h1 class="opening-statement"><?php esc_html_e( 'Selected work, in', 'on1-agency' ); ?> <em><?php esc_html_e( 'long form.', 'on1-agency' ); ?></em></h1>
</section>

<div class="work-archive">
  <?php if ( have_posts() ) : while ( have_posts() ) : the_post();
    $no   = get_field( 'project_no' )   ?: '';
    $cat  = get_field( 'project_cat' )  ?: '';
    $year = get_field( 'project_year' ) ?: '';
  ?>
    <a class="work-card" href="<?php the_permalink(); ?>">
      <?php if ( has_post_thumbnail() ) : ?>
        <div class="work-img"><?php the_post_thumbnail( 'on1-card' ); ?></div>
      <?php else : ?>
        <div class="work-img" style="display: flex; align-items: center; justify-content: center; background-image: linear-gradient(135deg, transparent 49.5%, var(--rule-2) 49.5%, var(--rule-2) 50.5%, transparent 50.5%); background-size: 14px 14px;">
          <span style="font-family: var(--ff-display); font-style: italic; font-size: 1.5rem; color: var(--muted);"><?php echo esc_html( get_the_title() ); ?></span>
        </div>
      <?php endif; ?>
      <h3><?php the_title(); ?></h3>
      <div class="work-meta">
        <span><?php echo esc_html( trim( $no . ' · ' . $cat, ' ·' ) ); ?></span>
        <span><?php echo esc_html( $year ); ?></span>
      </div>
    </a>
  <?php endwhile; else : ?>
    <div style="padding: 4rem var(--pad); font-family: var(--ff-mono); color: var(--muted); grid-column: 1/-1;">
      <?php esc_html_e( 'No projects yet.', 'on1-agency' ); ?>
    </div>
  <?php endif; ?>
</div>

<?php
if ( $GLOBALS['wp_query']->max_num_pages > 1 ) {
    echo '<div style="padding: 2rem var(--pad); text-align: center;">';
    the_posts_pagination( [ 'prev_text' => '←', 'next_text' => '→' ] );
    echo '</div>';
}
get_footer();

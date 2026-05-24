<?php
/**
 * single-project.php — Case study detail page (long form).
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();
while ( have_posts() ) : the_post();
    $pid     = get_the_ID();
    $no      = get_field( 'project_no' )      ?: 'N°01';
    $cat     = get_field( 'project_cat' )     ?: '';
    $year    = get_field( 'project_year' )    ?: '';
    $title   = get_field( 'project_title' )   ?: get_the_title();
    $lede    = get_field( 'project_lede' )    ?: '';
    $blocks  = get_field( 'project_blocks' )  ?: [];
    $metrics = get_field( 'project_metrics' ) ?: [];
    $tags    = get_field( 'project_tags' )    ?: '';
    $url     = get_field( 'project_url' )     ?: '';
?>

<section class="opening opening--project" data-screen-label="project hero">
  <div class="opening-meta">
    <div><?php esc_html_e( 'Project', 'on1-agency' ); ?><b><?php echo esc_html( $no ); ?></b></div>
    <?php if ( $cat )  : ?><div><?php esc_html_e( 'Category', 'on1-agency' ); ?><b><?php echo esc_html( $cat ); ?></b></div><?php endif; ?>
    <?php if ( $year ) : ?><div><?php esc_html_e( 'Year', 'on1-agency' ); ?><b><?php echo esc_html( $year ); ?></b></div><?php endif; ?>
    <?php if ( $url )  : ?><div class="last"><?php esc_html_e( 'Live', 'on1-agency' ); ?><b><a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( parse_url( $url, PHP_URL_HOST ) ); ?> ↗</a></b></div><?php endif; ?>
  </div>

  <h1 class="opening-statement"><?php on1_em( $title ); ?></h1>
  <?php if ( $lede ) : ?>
    <p class="case-lede project-lede"><?php echo wp_kses_post( $lede ); ?></p>
  <?php endif; ?>
</section>

<?php if ( has_post_thumbnail() ) : ?>
  <div class="project-thumbnail">
    <?php the_post_thumbnail( 'on1-hero', [ 'class' => 'project-thumbnail__img' ] ); ?>
  </div>
<?php endif; ?>

<section class="services project-detail">
  <div class="section-head">
    <div class="ix"><?php esc_html_e( 'Detail', 'on1-agency' ); ?></div>
    <div class="nm"><?php esc_html_e( 'Story · Metrics · Stack', 'on1-agency' ); ?></div>
    <?php if ( $year ) : ?><div class="yr"><?php echo esc_html( $year ); ?></div><?php endif; ?>
  </div>

  <div class="project-detail-grid">
    <div class="project-detail-left">
      <?php if ( $blocks ) : foreach ( $blocks as $b ) : ?>
        <h3 class="project-block-title"><?php echo esc_html( $b['label'] ); ?></h3>
        <p class="project-block-body"><?php echo wp_kses_post( $b['body'] ); ?></p>
      <?php endforeach; endif; ?>

      <?php if ( get_the_content() ) : ?>
        <div class="prose">
          <?php the_content(); ?>
        </div>
      <?php endif; ?>
    </div>

    <div class="project-detail-right">
      <?php if ( $metrics ) : ?>
        <h3 class="project-sidebar-heading"><?php esc_html_e( 'Measurable results', 'on1-agency' ); ?></h3>
        <div class="project-metrics-grid">
          <?php foreach ( $metrics as $m ) : ?>
            <div class="metric">
              <span class="lbl"><?php echo esc_html( $m['label'] ); ?></span>
              <span class="val"><?php echo wp_kses( $m['value'], [ 'em' => [], 'span' => [ 'class' => true ] ] ); ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <?php if ( $tags ) : ?>
        <h3 class="project-sidebar-heading"><?php esc_html_e( 'Stack', 'on1-agency' ); ?></h3>
        <div class="project-tags">
          <?php foreach ( array_map( 'trim', explode( ',', $tags ) ) as $t ) : if ( $t === '' ) continue; ?>
            <span class="tag"><?php echo esc_html( $t ); ?></span>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <?php if ( $url ) : ?>
        <a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener" class="case-link project-live-link">
          <?php esc_html_e( 'Visit live site', 'on1-agency' ); ?> →
        </a>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="project-back">
  <a href="<?php echo esc_url( get_post_type_archive_link( 'project' ) ); ?>" class="project-back-link">
    ← <?php esc_html_e( 'All projects', 'on1-agency' ); ?>
  </a>
</section>

<?php endwhile;
get_footer();

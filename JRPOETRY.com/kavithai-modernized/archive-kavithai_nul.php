<?php
/**
 * Books archive — v2.1 Relief.
 * Grouped by decade with the editorial books grid.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

$books = new WP_Query( [
    'post_type'      => 'kavithai_nul',
    'posts_per_page' => -1,
    'orderby'        => 'meta_value_num',
    'meta_key'       => '_kv_year',
    'order'          => 'ASC',
    'no_found_rows'  => true,
] );

// Group by decade.
$by_decade = [];
if ( $books->have_posts() ) {
    while ( $books->have_posts() ) : $books->the_post();
        $y = (int) get_post_meta( get_the_ID(), '_kv_year', true );
        $decade = $y ? (int) ( floor( $y / 10 ) * 10 ) : 0;
        if ( ! isset( $by_decade[ $decade ] ) ) $by_decade[ $decade ] = [];
        $by_decade[ $decade ][] = get_the_ID();
    endwhile;
    wp_reset_postdata();
}
ksort( $by_decade );

$total = (int) ( wp_count_posts( 'kavithai_nul' )->publish ?? 0 );
$ta_nums = [ '௧','௨','௩','௪','௫','௬','௭','௮','௯','௧௦','௧௧','௧௨','௧௩','௧௪','௧௫','௧௬','௧௭','௧௮','௧௯','௨௦' ];
$book_idx = 0;
?>

<header class="page-head">
    <?php echo kv_relief( 'நூல்', 'archive' ); ?>
    <div class="page-head__in">
        <p class="page-head__crumb">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>">முகப்பு</a>
            <span class="sep">·</span>நூல்கள்
        </p>
        <h1 class="page-head__title">நூல்கள்</h1>
        <p class="page-head__sub">Published Works <span style="color:var(--ochre);margin:0 0.5em;">·</span> <span class="la"><?php echo $total; ?> volumes</span></p>
    </div>
</header>

<section class="books">
    <?php if ( empty( $by_decade ) ) : ?>
        <p style="font-family:var(--serif-ta); font-style:italic; color:var(--ink-mute); text-align:center; padding:4rem 0;">இங்கே இன்னும் நூல்கள் இல்லை.</p>
    <?php else : foreach ( $by_decade as $decade => $ids ) :
        $decade_label = $decade ? $decade . ' — ' . ( $decade + 9 ) : 'Other';
        ?>

        <div style="margin-bottom: 4rem;">
            <p class="smallcaps" style="text-align:center; color:var(--ochre); margin-bottom:1rem;">தசாப்தம் — Decade</p>
            <h2 style="font-family:var(--serif-ta); font-weight:300; font-size:2rem; text-align:center; color:var(--ink); margin-bottom: 3rem;">
                <span class="la"><?php echo esc_html( $decade_label ); ?></span>
            </h2>
        </div>

        <div class="books__grid">
            <?php foreach ( $ids as $bid ) :
                $b_year = get_post_meta( $bid, '_kv_year', true );
                $b_type = get_post_meta( $bid, '_kv_type', true );
                $type_labels = [ 'kavithai' => 'Poetry', 'kathai' => 'Stories', 'novel' => 'Novel', 'katture' => 'Essays', 'other' => '' ];
                $type_label = $type_labels[ $b_type ] ?? '';
                $has_thumb  = has_post_thumbnail( $bid );
                ?>
                <a class="book" href="<?php echo esc_url( get_permalink( $bid ) ); ?>">
                    <?php if ( $book_idx < count( $ta_nums ) ) echo '<span class="book__num">' . esc_html( $ta_nums[ $book_idx ] ) . '</span>'; ?>
                    <div class="book__cover <?php echo $has_thumb ? 'book__cover--photo' : 'book__cover--plain'; ?>">
                        <?php if ( $has_thumb ) echo get_the_post_thumbnail( $bid, 'book-cover', [ 'style' => kv_focal_style( $bid ) ] ); ?>
                        <span class="book__cover-title"><?php echo esc_html( get_the_title( $bid ) ); ?></span>
                    </div>
                    <h3 class="book__title"><?php echo esc_html( get_the_title( $bid ) ); ?></h3>
                    <p class="book__meta">
                        <?php if ( $b_year ) echo '<span class="la">' . esc_html( $b_year ) . '</span>'; ?>
                        <?php if ( $b_year && $type_label ) echo '<span class="dot">·</span>'; ?>
                        <?php if ( $type_label ) echo esc_html( $type_label ); ?>
                    </p>
                </a>
                <?php $book_idx++; endforeach; ?>
        </div>

        <?php echo kv_asterism( 'break' ); ?>

    <?php endforeach; endif; ?>
</section>

<?php get_footer(); ?>

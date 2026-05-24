<?php
/**
 * Poem archive — v2.1 Relief.
 * Sidebar filters (search, categories, decade) + TOC-style list.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

$current_cat = isset( $_GET['vagai'] ) ? sanitize_text_field( wp_unslash( $_GET['vagai'] ) ) : '';
$current_decade = isset( $_GET['decade'] ) ? absint( $_GET['decade'] ) : 0;
$search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';

$args = [
    'post_type'      => 'kavithai_gedicht',
    'posts_per_page' => 20,
    'paged'          => max( 1, (int) get_query_var( 'paged', 1 ) ),
    'orderby'        => 'date',
    'order'          => 'DESC',
];
if ( $current_cat ) {
    $args['tax_query'] = [ [ 'taxonomy' => 'kavithai_cat', 'field' => 'slug', 'terms' => $current_cat ] ];
}
if ( $current_decade ) {
    $args['date_query'] = [ [
        'after'     => $current_decade . '-01-01',
        'before'    => ( $current_decade + 9 ) . '-12-31',
        'inclusive' => true,
    ] ];
}
if ( $search ) $args['s'] = $search;

$q = new WP_Query( $args );
$cats = get_terms( [ 'taxonomy' => 'kavithai_cat', 'hide_empty' => true, 'orderby' => 'name' ] );

$total = (int) ( wp_count_posts( 'kavithai_gedicht' )->publish ?? 0 );

// Decade buckets — count published poems per decade.
$decade_counts = [];
$all_poems = get_posts( [
    'post_type'      => 'kavithai_gedicht',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
    'fields'         => 'ids',
    'no_found_rows'  => true,
] );
foreach ( $all_poems as $apid ) {
    $py = get_post_meta( $apid, '_kv_year', true );
    if ( ! $py ) $py = get_the_date( 'Y', $apid );
    $decade = (int) ( floor( ( (int) $py ) / 10 ) * 10 );
    if ( $decade ) {
        if ( ! isset( $decade_counts[ $decade ] ) ) $decade_counts[ $decade ] = 0;
        $decade_counts[ $decade ]++;
    }
}
ksort( $decade_counts );
?>

<header class="page-head">
    <?php echo kv_relief( 'கவிதை', 'archive' ); ?>
    <div class="page-head__in">
        <p class="page-head__crumb">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>">முகப்பு</a>
            <span class="sep">·</span>கவிதைகள்
        </p>
        <h1 class="page-head__title">கவிதைகள்</h1>
        <p class="page-head__sub">Poems <span style="color:var(--ochre);margin:0 0.5em;">·</span> <span class="la"><?php echo $total; ?> published</span></p>
    </div>
</header>

<div class="kv-archive">
    <aside class="archive__side">
        <form class="archive__search" method="GET">
            <input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="ஒரு வார்த்தை, ஒரு வரி...">
        </form>

        <p class="archive__filter-head">வகைகள் · Categories</p>
        <ul class="archive__filter">
            <li><a href="<?php echo esc_url( get_post_type_archive_link( 'kavithai_gedicht' ) ); ?>" class="<?php echo $current_cat === '' ? 'is-current' : ''; ?>">அனைத்தும் <span class="count"><?php echo $total; ?></span></a></li>
            <?php foreach ( $cats as $c ) : ?>
                <li><a href="<?php echo esc_url( add_query_arg( 'vagai', $c->slug, get_post_type_archive_link( 'kavithai_gedicht' ) ) ); ?>" class="<?php echo $current_cat === $c->slug ? 'is-current' : ''; ?>">
                    <?php echo esc_html( $c->name ); ?> <span class="count"><?php echo (int) $c->count; ?></span>
                </a></li>
            <?php endforeach; ?>
        </ul>

        <?php if ( count( $decade_counts ) > 1 ) : ?>
        <p class="archive__filter-head">தசாப்தம் · Decade</p>
        <ul class="archive__filter">
            <?php foreach ( $decade_counts as $d => $c ) : ?>
                <li><a href="<?php echo esc_url( add_query_arg( 'decade', $d, get_post_type_archive_link( 'kavithai_gedicht' ) ) ); ?>" class="<?php echo $current_decade === $d ? 'is-current' : ''; ?>">
                    <span class="la"><?php echo $d; ?>s</span> <span class="count"><?php echo $c; ?></span>
                </a></li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </aside>

    <main>
        <?php if ( $q->have_posts() ) : ?>
            <ul class="toc" style="margin-top: 0;">
                <?php
                $ta_nums = [ '௧','௨','௩','௪','௫','௬','௭','௮','௯','௧௦','௧௧','௧௨','௧௩','௧௪','௧௫','௧௬','௧௭','௧௮','௧௯','௨௦' ];
                $i = 0;
                while ( $q->have_posts() ) : $q->the_post();
                    $w_year = get_post_meta( get_the_ID(), '_kv_year', true );
                    $w_note = get_post_meta( get_the_ID(), '_kv_note', true );
                    ?>
                    <li class="toc__row">
                        <span class="toc__num"><?php echo esc_html( $ta_nums[ $i ] ?? ($i + 1) ); ?></span>
                        <span class="toc__year"><?php echo esc_html( $w_year ?: get_the_date( 'Y' ) ); ?></span>
                        <span class="toc__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></span>
                        <span class="toc__first"><?php echo esc_html( $w_note ?: wp_trim_words( wp_strip_all_tags( get_the_excerpt() ?: get_the_content() ), 12, '…' ) ); ?></span>
                    </li>
                    <?php $i++; endwhile; wp_reset_postdata(); ?>
            </ul>

            <?php
            $links = paginate_links( [
                'total'     => $q->max_num_pages,
                'current'   => max( 1, (int) get_query_var( 'paged', 1 ) ),
                'prev_text' => '←',
                'next_text' => '→',
            ] );
            if ( $links ) echo '<nav class="pagination" aria-label="Page navigation">' . $links . '</nav>';
            ?>
        <?php else : ?>
            <p style="font-family:var(--serif-ta); font-style:italic; color:var(--ink-mute); text-align:center; padding:4rem 0;">இங்கே இன்னும் கவிதைகள் இல்லை.</p>
        <?php endif; ?>
    </main>
</div>

<?php get_footer(); ?>

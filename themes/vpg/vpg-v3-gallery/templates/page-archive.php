<?php
/** Template Name: Full archive */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>
<main id="vpg-main">

<section class="g-phero">
    <div class="g-wrap">
        <p class="g-kicker" style="margin-bottom:16px">● Archive</p>
        <h1 class="g-display g-phero__title">Everything we have <em>published</em>.</h1>
        <p class="g-lede g-phero__lede">Every magazine issue, every location, every review · the full index on one page.</p>
    </div>
</section>

<?php
$sections = [
    [ 'vpg_magazine', 'Magazine issues', 'mag',     'Monthly editorial · cover to colophon' ],
    [ 'vpg_event',    'Events',          'event',   'Workshops, walks, openings, meetups' ],
    [ 'vpg_location', 'Locations',       'loc',     'The map · curated by members' ],
    [ 'vpg_studio',   'Studios',         'studio',  'Rental studios + share directory' ],
    [ 'vpg_shop',     'Shops',           'shop',    'Camera shops, labs, suppliers' ],
    [ 'vpg_review',   'Buying guide',    'review',  'Gear reviews · scored /10' ],
    [ 'vpg_tutorial', 'Tutorials',       'tut',     'Member-written, skill-tagged' ],
];

foreach ( $sections as $i => $sec ) :
    list( $type, $label, $cls, $desc ) = $sec;
    $items = get_posts( [ 'post_type' => $type, 'posts_per_page' => -1, 'orderby' => 'date', 'order' => 'DESC' ] );
    if ( ! $items ) continue;
?>
<section class="g-section <?php echo ( $i % 2 ) ? 'g-section--alt' : 'g-section--tight'; ?>">
    <div class="g-wrap">
        <div class="g-head">
            <div>
                <span class="g-kicker"><?php echo esc_html( $label ); ?></span>
                <h2 class="g-head__t"><?php printf( esc_html( _n( '%d entry', '%d entries', count( $items ), 'vpg-v2' ) ), count( $items ) ); ?></h2>
                <p class="g-lede" style="margin-top:.6rem"><?php echo esc_html( $desc ); ?></p>
            </div>
            <a class="g-link" href="<?php echo esc_url( get_post_type_archive_link( $type ) ); ?>">View archive <span class="a">→</span></a>
        </div>

        <div class="g-list">
            <?php foreach ( $items as $it ) : ?>
                <a class="g-row" href="<?php echo esc_url( get_permalink( $it ) ); ?>">
                    <span class="g-row__when"><?php echo esc_html( get_the_date( 'Y', $it ) ); ?></span>
                    <h3 class="g-row__title"><?php echo esc_html( get_the_title( $it ) ); ?></h3>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endforeach; ?>

</main>
<?php get_footer();

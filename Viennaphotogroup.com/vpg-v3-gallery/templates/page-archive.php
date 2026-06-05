<?php
/** Template Name: Full archive */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>
<main id="vpg-main">

<header class="vpg-page-hero">
    <span class="vpg-chip"><span class="vpg-chip__dot"></span> Archive</span>
    <h1>Everything we have <em>published</em>.</h1>
    <p class="vpg-lede">Every magazine issue, every location, every review · the full index on one page.</p>
</header>

<span class="vpg-asterism vpg-asterism--mark"></span>

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

foreach ( $sections as $sec ) :
    list( $type, $label, $cls, $desc ) = $sec;
    $items = get_posts( [ 'post_type' => $type, 'posts_per_page' => -1, 'orderby' => 'date', 'order' => 'DESC' ] );
    if ( ! $items ) continue;
?>
<section class="vpg-section vpg-section--tight">
    <div class="vpg-wrap">
        <div class="vpg-section-head">
            <div>
                <p class="vpg-caps">— <span class="vpg-chip vpg-chip--<?php echo esc_attr( $cls ); ?>" style="margin-left:.4rem"><span class="vpg-chip__dot"></span> <?php echo esc_html( $label ); ?></span></p>
                <h2 style="margin-top:.4rem"><?php printf( esc_html( _n( '%d entry', '%d entries', count( $items ), 'vpg-v2' ) ), count( $items ) ); ?></h2>
                <p class="vpg-card__lede" style="margin-top:.6rem"><?php echo esc_html( $desc ); ?></p>
            </div>
            <div class="vpg-section-head__meta"><a href="<?php echo esc_url( get_post_type_archive_link( $type ) ); ?>">View archive →</a></div>
        </div>

        <ul style="list-style:none;padding:0;margin:0;display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:.4rem">
            <?php foreach ( $items as $it ) : ?>
                <li><a style="display:grid;grid-template-columns:auto 1fr;gap:.8rem;padding:.5rem 0;border-bottom:1px solid var(--vpg-rule);text-decoration:none;align-items:baseline" href="<?php echo esc_url( get_permalink( $it ) ); ?>">
                    <span style="font-family:var(--vpg-font-mono);font-size:var(--vpg-fs-xs);letter-spacing:.12em;color:var(--vpg-muted)"><?php echo esc_html( get_the_date( 'Y', $it ) ); ?></span>
                    <span><?php echo esc_html( get_the_title( $it ) ); ?></span>
                </a></li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
<span class="vpg-asterism vpg-asterism--break"></span>
<?php endforeach; ?>

</main>
<?php get_footer();

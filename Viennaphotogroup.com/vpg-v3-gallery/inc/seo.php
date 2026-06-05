<?php
/**
 * VPG v2 — SEO / sharing meta.
 *
 *   - Open Graph + Twitter Card meta tags on every page
 *   - Magazine RSS feed at /feed/magazine
 *   - Styled sitemap at /sitemap-vpg/ (HTML, human-readable)
 *
 * Yoast / RankMath users can disable this by defining `VPG_DISABLE_SEO` true.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ─── Open Graph + Twitter Card ────────────────────────────────── */
add_action( 'wp_head', function () {
    if ( defined( 'VPG_DISABLE_SEO' ) && VPG_DISABLE_SEO ) return;

    $title  = wp_get_document_title();
    $desc   = '';
    $image  = '';
    $type   = 'website';
    $url    = home_url( add_query_arg( null, null ) );

    if ( is_singular() ) {
        global $post;
        $desc  = get_the_excerpt( $post ) ?: wp_strip_all_tags( get_post_field( 'post_content', $post ), true );
        $desc  = wp_trim_words( $desc, 32 );
        if ( has_post_thumbnail( $post ) ) $image = get_the_post_thumbnail_url( $post, 'vpg-hero' );
        $type  = ( $post->post_type === 'vpg_magazine' ) ? 'article' : 'article';
    } elseif ( is_front_page() ) {
        $desc  = get_bloginfo( 'description' );
        $type  = 'website';
    } elseif ( is_post_type_archive() ) {
        $desc  = post_type_archive_title( '', false );
    }

    if ( ! $image ) {
        // Fall back to the most recent magazine cover, then the custom-logo
        $latest = get_posts( [ 'post_type' => 'vpg_magazine', 'numberposts' => 1, 'orderby' => 'date' ] );
        if ( $latest && has_post_thumbnail( $latest[0] ) ) {
            $image = get_the_post_thumbnail_url( $latest[0], 'vpg-hero' );
        } else {
            $logo = get_theme_mod( 'custom_logo' );
            if ( $logo ) $image = wp_get_attachment_image_url( $logo, 'full' );
        }
    }

    $site  = get_bloginfo( 'name' );
    $tw    = get_theme_mod( 'vpg_social_x', '' );
    $tw_handle = $tw ? '@' . ltrim( basename( parse_url( $tw, PHP_URL_PATH ) ?: '' ), '/' ) : '';

    echo "\n<!-- VPG · sharing meta -->\n";
    printf( '<meta property="og:type" content="%s" />' . "\n",        esc_attr( $type ) );
    printf( '<meta property="og:site_name" content="%s" />' . "\n",   esc_attr( $site ) );
    printf( '<meta property="og:title" content="%s" />' . "\n",       esc_attr( $title ) );
    printf( '<meta property="og:description" content="%s" />' . "\n", esc_attr( $desc ) );
    printf( '<meta property="og:url" content="%s" />' . "\n",         esc_url( $url ) );
    if ( $image ) {
        printf( '<meta property="og:image" content="%s" />' . "\n",   esc_url( $image ) );
        printf( '<meta property="og:image:width"  content="1920" />' . "\n" );
        printf( '<meta property="og:image:height" content="1080" />' . "\n" );
    }
    echo  '<meta name="twitter:card" content="summary_large_image" />' . "\n";
    if ( $tw_handle ) printf( '<meta name="twitter:site" content="%s" />' . "\n", esc_attr( $tw_handle ) );
    printf( '<meta name="twitter:title" content="%s" />' . "\n",       esc_attr( $title ) );
    printf( '<meta name="twitter:description" content="%s" />' . "\n", esc_attr( $desc ) );
    if ( $image ) printf( '<meta name="twitter:image" content="%s" />' . "\n", esc_url( $image ) );

    if ( is_singular( 'vpg_magazine' ) ) {
        $issue_no = get_post_meta( get_the_ID(), '_vpg_issue_number', true );
        if ( $issue_no ) printf( '<meta property="article:section" content="%s" />' . "\n", esc_attr( $issue_no ) );
        printf( '<meta property="article:published_time" content="%s" />' . "\n", esc_attr( get_the_date( 'c' ) ) );
    }
    echo "<!-- /VPG sharing -->\n\n";
}, 1 );

/* ─── Custom RSS feed for the magazine ────────────────────────── */
add_action( 'init', function () {
    add_feed( 'magazine', 'vpg_render_magazine_feed' );
} );
function vpg_render_magazine_feed() {
    $issues = get_posts( [ 'post_type' => 'vpg_magazine', 'posts_per_page' => 20, 'orderby' => 'date', 'order' => 'DESC' ] );
    header( 'Content-Type: application/rss+xml; charset=' . get_option( 'blog_charset' ), true );
    echo '<?xml version="1.0" encoding="' . esc_attr( get_option( 'blog_charset' ) ) . '"?>' . "\n";
    ?>
<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
    <title><?php bloginfo_rss( 'name' ); ?> · Magazine</title>
    <link><?php echo esc_url( get_post_type_archive_link( 'vpg_magazine' ) ); ?></link>
    <atom:link href="<?php echo esc_url( get_feed_link( 'magazine' ) ); ?>" rel="self" type="application/rss+xml" />
    <description><?php bloginfo_rss( 'description' ); ?> · monthly magazine</description>
    <language><?php bloginfo_rss( 'language' ); ?></language>
    <lastBuildDate><?php echo esc_html( mysql2date( 'D, d M Y H:i:s +0000', current_time( 'mysql', 1 ), false ) ); ?></lastBuildDate>
    <?php foreach ( $issues as $p ) :
        $cover = has_post_thumbnail( $p ) ? get_the_post_thumbnail_url( $p, 'vpg-hero' ) : '';
        $pdf   = get_post_meta( $p->ID, '_vpg_pdf_url', true );
    ?>
    <item>
        <title><?php echo esc_html( $p->post_title ); ?></title>
        <link><?php echo esc_url( get_permalink( $p ) ); ?></link>
        <guid isPermaLink="false">vpg-issue-<?php echo (int) $p->ID; ?></guid>
        <pubDate><?php echo esc_html( mysql2date( 'D, d M Y H:i:s +0000', $p->post_date_gmt, false ) ); ?></pubDate>
        <description><?php echo esc_html( $p->post_excerpt ); ?></description>
        <?php if ( $cover ) : ?><enclosure url="<?php echo esc_url( $cover ); ?>" type="image/jpeg" /><?php endif; ?>
        <?php if ( $pdf )   : ?><enclosure url="<?php echo esc_url( $pdf ); ?>" type="application/pdf" /><?php endif; ?>
        <content:encoded><![CDATA[<?php echo $p->post_excerpt; ?>]]></content:encoded>
    </item>
    <?php endforeach; ?>
</channel>
</rss>
    <?php
}

/* ─── HTML sitemap ──────────────────────────────────────────── */
add_action( 'init', function () {
    add_rewrite_rule( '^sitemap-vpg/?$', 'index.php?vpg_sitemap=1', 'top' );
} );
add_filter( 'query_vars', function ( $v ) { $v[] = 'vpg_sitemap'; return $v; } );
add_action( 'template_redirect', function () {
    if ( ! get_query_var( 'vpg_sitemap' ) ) return;

    get_header();
    ?>
    <main id="vpg-main">
        <header class="vpg-page-hero">
            <span class="vpg-caps">— <?php esc_html_e( 'Sitemap', 'vpg-v2' ); ?></span>
            <h1><?php esc_html_e( 'Everything,', 'vpg-v2' ); ?> <em><?php esc_html_e( 'in one place', 'vpg-v2' ); ?></em>.</h1>
        </header>

        <section class="vpg-section vpg-section--tight">
            <div class="vpg-wrap vpg-grid vpg-grid--3">
                <?php
                $sections = [
                    'vpg_magazine' => 'Magazine issues',
                    'vpg_event'    => 'Events',
                    'vpg_location' => 'Locations',
                    'vpg_studio'   => 'Studios',
                    'vpg_shop'     => 'Shops',
                    'vpg_review'   => 'Reviews',
                    'vpg_tutorial' => 'Tutorials',
                    'page'         => 'Pages',
                    'post'         => 'Journal',
                ];
                foreach ( $sections as $type => $label ) :
                    $items = get_posts( [ 'post_type' => $type, 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC' ] );
                    if ( ! $items ) continue;
                ?>
                    <section class="vpg-card">
                        <h3><?php echo esc_html( $label ); ?> <small style="font-family:var(--vpg-font-mono);font-size:11px;color:var(--vpg-muted);letter-spacing:.18em;text-transform:uppercase">· <?php echo count( $items ); ?></small></h3>
                        <ul style="list-style:none;padding:0;margin:1rem 0 0;display:flex;flex-direction:column;gap:.4rem">
                            <?php foreach ( $items as $it ) : ?>
                                <li><a href="<?php echo esc_url( get_permalink( $it ) ); ?>" style="text-decoration:none"><?php echo esc_html( get_the_title( $it ) ); ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </section>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
    <?php
    get_footer();
    exit;
} );

<?php
/**
 * SEO module
 * - Extended schema.org (LocalBusiness with address, opening hours, geo).
 * - FAQPage schema on /faq, Article schema on journal posts,
 *   VisualArtwork on individual nr_project posts.
 * - /sitemap.xml route and custom robots.txt.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'wp_head', 'nr_schema_markup_extended', 6 );

function nr_schema_markup_extended() {
    try {
        nr_schema_markup_extended_render();
    } catch ( \Throwable $e ) {
        error_log( '[NR seo] schema markup failed: ' . $e->getMessage() );
    }
}

function nr_schema_markup_extended_render() {
    global $post;

    $name         = 'Nishuthan Raveenthiran';
    $site_url     = get_site_url();
    $location     = get_field( 'base_location', 'option' ) ?: 'Wien, Austria';
    $instagram    = get_field( 'instagram_url', 'option' );
    $phone        = get_field( 'seo_phone', 'option' );
    $street       = get_field( 'seo_street', 'option' );
    $postal       = get_field( 'seo_postal_code', 'option' ) ?: '1010';
    $city         = get_field( 'seo_city', 'option' ) ?: 'Wien';
    $lat          = get_field( 'seo_lat', 'option' ) ?: '48.2082';
    $lng          = get_field( 'seo_lng', 'option' ) ?: '16.3738';
    $hours_rows   = get_field( 'seo_opening_hours', 'option' );

    // LocalBusiness + Person auf Homepage und statischen Seiten
    if ( is_front_page() || is_page() ) {
        $schema = [
            '@context' => 'https://schema.org',
            '@graph'   => [
                [
                    '@type'       => [ 'Person', 'LocalBusiness', 'ProfessionalService' ],
                    '@id'         => $site_url . '/#person',
                    'name'        => $name,
                    'jobTitle'    => 'Photographer',
                    'description' => get_field( 'site_tagline', 'option' ) ?: 'Editorial & Commercial Photographer based in Vienna',
                    'url'         => $site_url,
                    'address'     => [
                        '@type'           => 'PostalAddress',
                        'streetAddress'   => $street ?: '',
                        'postalCode'      => $postal,
                        'addressLocality' => $city,
                        'addressCountry'  => 'AT',
                    ],
                    'geo' => [
                        '@type'     => 'GeoCoordinates',
                        'latitude'  => $lat,
                        'longitude' => $lng,
                    ],
                    'areaServed'          => [ 'Wien', 'Niederösterreich', 'Europa' ],
                    'priceRange'          => '€€',
                ],
            ],
        ];

        // Telefon optional
        if ( $phone ) $schema['@graph'][0]['telephone'] = $phone;

        // Opening Hours
        if ( is_array( $hours_rows ) && ! empty( $hours_rows ) ) {
            $oh = [];
            foreach ( $hours_rows as $row ) {
                if ( is_array( $row ) && isset( $row['days'], $row['hours'] ) ) {
                    $oh[] = $row['days'] . ' ' . $row['hours'];
                }
            }
            if ( $oh ) $schema['@graph'][0]['openingHours'] = $oh;
        }

        // Instagram sameAs
        if ( $instagram && is_string( $instagram ) ) $schema['@graph'][0]['sameAs'] = [ $instagram ];

        // Hero-Bild
        $hero = get_field( 'hero_image', 'option' );
        if ( is_array( $hero ) && ! empty( $hero['url'] ) ) {
            $schema['@graph'][0]['image'] = $hero['url'];
        } elseif ( is_numeric( $hero ) ) {
            $url = wp_get_attachment_image_url( (int) $hero, 'large' );
            if ( $url ) $schema['@graph'][0]['image'] = $url;
        } elseif ( is_string( $hero ) && $hero !== '' ) {
            $schema['@graph'][0]['image'] = $hero;
        }

        echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
    }

    // VisualArtwork auf einzelnen Projekten
    if ( is_singular( 'nr_project' ) && $post ) {
        $cover   = get_field( 'project_cover', $post->ID );
        $gallery = get_field( 'project_gallery', $post->ID );
        $images  = [];
        if ( is_array( $cover ) && ! empty( $cover['url'] ) ) {
            $images[] = $cover['url'];
        } elseif ( is_numeric( $cover ) ) {
            $u = wp_get_attachment_image_url( (int) $cover, 'large' );
            if ( $u ) $images[] = $u;
        }
        if ( is_array( $gallery ) ) {
            foreach ( $gallery as $img ) {
                if ( is_array( $img ) && ! empty( $img['url'] ) ) {
                    $images[] = $img['url'];
                } elseif ( is_numeric( $img ) ) {
                    $u = wp_get_attachment_image_url( (int) $img, 'large' );
                    if ( $u ) $images[] = $u;
                }
            }
        }

        $schema = [
            '@context'        => 'https://schema.org',
            '@type'           => 'VisualArtwork',
            'name'            => get_the_title(),
            'creator'         => [ '@type' => 'Person', 'name' => $name, 'url' => $site_url ],
            'url'             => get_permalink(),
            'artMedium'       => 'Photography',
            'locationCreated' => get_field( 'project_location', $post->ID ) ?: $city,
            'dateCreated'     => get_field( 'project_year', $post->ID ) ?: get_the_date( 'Y' ),
        ];
        if ( ! empty( $images ) ) $schema['image'] = $images;
        if ( $client = get_field( 'project_client', $post->ID ) ) {
            $schema['contributor'] = [ '@type' => 'Organization', 'name' => $client ];
        }

        echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
    }

    // FAQPage Schema — FAQ now lives on the merged Enquire page.
    $is_enquire = ( is_page() && get_page_template_slug( get_queried_object_id() ) === 'page-enquire.php' ) || is_page( 'enquire' );
    if ( $is_enquire && function_exists( 'nr_faq_items' ) ) {
        $entities = [];
        foreach ( nr_faq_items() as $item ) {
            if ( ! empty( $item['q'] ) && ! empty( $item['a'] ) ) {
                $entities[] = [
                    '@type'          => 'Question',
                    'name'           => $item['q'],
                    'acceptedAnswer' => [ '@type' => 'Answer', 'text' => $item['a'] ],
                ];
            }
        }
        if ( $entities ) {
            $schema = [ '@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $entities ];
            echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
        }
    }

}

/* =============================================================================
   FEATURE 16 — Sitemap.xml
   ============================================================================= */

add_action( 'init', 'nr_register_sitemap_route' );

function nr_register_sitemap_route() {
    add_rewrite_rule( '^sitemap\.xml$', 'index.php?nr_sitemap=1', 'top' );
    add_rewrite_tag( '%nr_sitemap%', '1' );
}

add_action( 'template_redirect', 'nr_serve_sitemap' );

function nr_serve_sitemap() {
    if ( ! get_query_var( 'nr_sitemap' ) ) return;

    header( 'Content-Type: application/xml; charset=UTF-8' );

    $urls = [];

    // Statische Seiten
    $static = [
        [ 'loc' => home_url( '/' ),          'priority' => '1.0', 'changefreq' => 'weekly' ],
        [ 'loc' => home_url( '/portfolio' ),  'priority' => '0.9', 'changefreq' => 'weekly' ],
        [ 'loc' => home_url( '/about' ),      'priority' => '0.7', 'changefreq' => 'monthly' ],
        [ 'loc' => home_url( '/booking' ),    'priority' => '0.9', 'changefreq' => 'monthly' ],
        [ 'loc' => home_url( '/contact' ),    'priority' => '0.7', 'changefreq' => 'monthly' ],
        [ 'loc' => home_url( '/faq' ),        'priority' => '0.6', 'changefreq' => 'monthly' ],
    ];
    $urls = array_merge( $urls, $static );

    // Projekte
    $projects = get_posts( [ 'post_type' => 'nr_project', 'posts_per_page' => -1, 'fields' => 'ids' ] );
    foreach ( $projects as $id ) {
        $urls[] = [
            'loc'        => get_permalink( $id ),
            'lastmod'    => get_the_modified_date( 'Y-m-d', $id ),
            'priority'   => '0.8',
            'changefreq' => 'monthly',
        ];
    }

    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    foreach ( $urls as $url ) {
        echo "  <url>\n";
        echo '    <loc>' . esc_url( $url['loc'] ) . "</loc>\n";
        if ( ! empty( $url['lastmod'] ) ) echo '    <lastmod>' . esc_html( $url['lastmod'] ) . "</lastmod>\n";
        if ( ! empty( $url['changefreq'] ) ) echo '    <changefreq>' . esc_html( $url['changefreq'] ) . "</changefreq>\n";
        if ( ! empty( $url['priority'] ) ) echo '    <priority>' . esc_html( $url['priority'] ) . "</priority>\n";
        echo "  </url>\n";
    }
    echo '</urlset>';
    exit;
}

// robots.txt
add_filter( 'robots_txt', 'nr_custom_robots_txt', 10, 2 );

function nr_custom_robots_txt( $output, $public ) {
    $site_url = get_site_url();
    return "User-agent: *\n"
         . "Disallow: /wp-admin/\n"
         . "Disallow: /wp-includes/\n"
         . "Disallow: /wp-login.php\n"
         . "Disallow: /xmlrpc.php\n"
         . "Disallow: /?s=\n"
         . "Disallow: /wp-json/\n"
         . "Allow: /wp-admin/admin-ajax.php\n\n"
         . "Sitemap: {$site_url}/sitemap.xml\n";
}



/* =============================================================================
   Social meta — Open Graph + Twitter card + canonical + verification + analytics
   Appended in v3.6 — sits next to the existing schema.org markup above.
   ============================================================================= */

add_action( 'wp_head', 'nr_social_meta', 7 );

function nr_social_meta() {
	$title    = wp_get_document_title();
	$site_url = home_url( '/' );
	$canonical = is_singular() ? get_permalink() : ( is_post_type_archive() ? get_post_type_archive_link( get_post_type() ) : $site_url );
	$desc     = nr_opt( 'nr_tagline', __( 'Editorial, architectural, and portrait photography. Vienna.', 'raveenthiran' ) );
	if ( is_singular() && has_excerpt() ) {
		$desc = wp_strip_all_tags( get_the_excerpt() );
	} elseif ( is_singular() && ! is_front_page() ) {
		$post_content = trim( wp_strip_all_tags( get_post()->post_content ?? '' ) );
		if ( $post_content ) $desc = mb_substr( $post_content, 0, 200 );
	}

	$image = '';
	if ( is_singular() && has_post_thumbnail() ) {
		$image = get_the_post_thumbnail_url( get_the_ID(), 'nr-hero' );
	}
	if ( ! $image ) {
		// Fallback to first featured project's hero, or site icon
		$q = new WP_Query( [
			'post_type'      => 'nr_project',
			'posts_per_page' => 1,
			'meta_key'       => 'featured_on_homepage',
			'meta_value'     => '1',
			'fields'         => 'ids',
		] );
		if ( $q->have_posts() ) {
			$image = get_the_post_thumbnail_url( $q->posts[0], 'nr-hero' );
		}
		wp_reset_postdata();
	}
	if ( ! $image && function_exists( 'get_site_icon_url' ) ) {
		$image = get_site_icon_url( 512 );
	}

	$locale = get_locale() ?: 'en_US';
	$type   = is_singular( 'nr_project' ) ? 'article' : ( is_singular() ? 'article' : 'website' );

	echo "\n<!-- nr social meta -->\n";
	printf( '<link rel="canonical" href="%s">' . "\n", esc_url( $canonical ) );

	// Open Graph
	printf( '<meta property="og:type" content="%s">' . "\n", esc_attr( $type ) );
	printf( '<meta property="og:site_name" content="%s">' . "\n", esc_attr( get_bloginfo( 'name' ) ) );
	printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( $title ) );
	printf( '<meta property="og:description" content="%s">' . "\n", esc_attr( $desc ) );
	printf( '<meta property="og:url" content="%s">' . "\n", esc_url( $canonical ) );
	printf( '<meta property="og:locale" content="%s">' . "\n", esc_attr( $locale ) );
	if ( $image ) {
		printf( '<meta property="og:image" content="%s">' . "\n", esc_url( $image ) );
		echo '<meta property="og:image:width" content="2400">' . "\n";
		echo '<meta property="og:image:height" content="1600">' . "\n";
	}

	// Twitter
	echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
	$tw_handle = nr_opt( 'nr_twitter_handle', '' );
	if ( $tw_handle ) printf( '<meta name="twitter:site" content="@%s">' . "\n", esc_attr( ltrim( $tw_handle, '@' ) ) );
	printf( '<meta name="twitter:title" content="%s">' . "\n", esc_attr( $title ) );
	printf( '<meta name="twitter:description" content="%s">' . "\n", esc_attr( $desc ) );
	if ( $image ) printf( '<meta name="twitter:image" content="%s">' . "\n", esc_url( $image ) );

	// Search Console / Bing / Yandex verification
	foreach ( [
		'google'   => 'nr_verify_google',
		'msvalidate.01' => 'nr_verify_bing',
		'yandex-verification' => 'nr_verify_yandex',
	] as $name => $key ) {
		$v = trim( (string) nr_opt( $key, '' ) );
		if ( $v ) printf( '<meta name="%s" content="%s">' . "\n", esc_attr( $name ), esc_attr( $v ) );
	}
}

/* BreadcrumbList JSON-LD — helps Google show breadcrumbs in SERPs */
add_action( 'wp_head', 'nr_breadcrumb_schema', 8 );

function nr_breadcrumb_schema() {
	if ( is_front_page() ) return;

	$items = [];
	$home  = home_url( '/' );
	$items[] = [ 'name' => __( 'Home', 'raveenthiran' ), 'item' => $home ];

	if ( is_singular( 'nr_project' ) ) {
		$archive = get_post_type_archive_link( 'nr_project' );
		if ( $archive ) $items[] = [ 'name' => __( 'Work', 'raveenthiran' ), 'item' => $archive ];
		$items[] = [ 'name' => get_the_title(), 'item' => get_permalink() ];
	} elseif ( is_post_type_archive( 'nr_project' ) ) {
		$items[] = [ 'name' => __( 'Work', 'raveenthiran' ), 'item' => get_post_type_archive_link( 'nr_project' ) ];
	} elseif ( is_page() ) {
		$items[] = [ 'name' => get_the_title(), 'item' => get_permalink() ];
	} else {
		return;
	}

	$list = [];
	foreach ( $items as $i => $it ) {
		$list[] = [
			'@type'    => 'ListItem',
			'position' => $i + 1,
			'name'     => $it['name'],
			'item'     => $it['item'],
		];
	}
	$schema = [ '@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $list ];
	echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}

/* Analytics / custom tracking script — injected into <head> from settings */
add_action( 'wp_head', 'nr_inject_tracking_script', 100 );

function nr_inject_tracking_script() {
	if ( is_admin() || is_user_logged_in() ) return; // don't track admins
	$script = (string) get_option( 'nr_tracking_script', '' );
	if ( trim( $script ) === '' ) return;
	echo "\n<!-- nr tracking -->\n" . $script . "\n";
}

/* =============================================================================
   FEATURE 23 — WP REST API deaktivieren für nicht eingeloggte Besucher
   ============================================================================= */
add_filter( 'rest_authentication_errors', function( $result ) {
    if ( ! empty( $result ) ) return $result;
    if ( ! is_user_logged_in() ) {
        return new WP_Error(
            'rest_not_logged_in',
            'REST API access requires authentication.',
            [ 'status' => 401 ]
        );
    }
    return $result;
} );

// User-Enumeration via REST API verhindern
add_filter( 'rest_endpoints', function( $endpoints ) {
    if ( isset( $endpoints['/wp/v2/users'] ) ) unset( $endpoints['/wp/v2/users'] );
    if ( isset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] ) ) unset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
    return $endpoints;
} );

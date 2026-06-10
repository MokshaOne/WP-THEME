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

        // #18 — sameAs from every configured social profile.
        $sameas = array_values( array_filter( [
            is_string( $instagram ) ? $instagram : '',
            (string) nr_opt( 'nr_behance', '' ),
            (string) nr_opt( 'nr_vimeo', '' ),
            (string) nr_opt( 'nr_linkedin', '' ),
        ] ) );
        if ( $sameas ) $schema['@graph'][0]['sameAs'] = $sameas;

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
        if ( ! empty( $images ) ) {
            // Richer ImageObject entries (caption + creator) instead of bare URLs.
            $schema['image'] = array_map( function ( $u ) use ( $name, $site_url ) {
                return [
                    '@type'      => 'ImageObject',
                    'contentUrl' => $u,
                    'caption'    => get_the_title(),
                    'creator'    => [ '@type' => 'Person', 'name' => $name, 'url' => $site_url ],
                ];
            }, $images );
        }
        if ( $client = get_field( 'project_client', $post->ID ) ) {
            $schema['contributor'] = [ '@type' => 'Organization', 'name' => $client ];
        }

        echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
    }

    // Article schema on journal posts (headline/date/author/image).
    if ( is_singular( 'nr_journal' ) && $post ) {
        $schema = [
            '@context'      => 'https://schema.org',
            '@type'         => 'Article',
            'headline'      => get_the_title(),
            'url'           => get_permalink(),
            'datePublished' => get_the_date( 'c' ),
            'dateModified'  => get_the_modified_date( 'c' ),
            'author'        => [ '@type' => 'Person', 'name' => $name, 'url' => $site_url ],
            'publisher'     => [ '@type' => 'Person', 'name' => $name, 'url' => $site_url ],
            'mainEntityOfPage' => get_permalink(),
        ];
        if ( has_excerpt( $post ) ) $schema['description'] = wp_strip_all_tags( get_the_excerpt() );
        if ( has_post_thumbnail( $post ) ) {
            $u = get_the_post_thumbnail_url( $post, 'nr-hero' );
            if ( $u ) $schema['image'] = $u;
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
        [ 'loc' => home_url( '/' ),          'priority' => '1.0', 'changefreq' => 'weekly', 'lastmod' => date_i18n( 'Y-m-d' ) ],
        [ 'loc' => home_url( '/portfolio' ),  'priority' => '0.9', 'changefreq' => 'weekly' ],
        [ 'loc' => home_url( '/about' ),      'priority' => '0.7', 'changefreq' => 'monthly' ],
        [ 'loc' => home_url( '/enquire' ),    'priority' => '0.9', 'changefreq' => 'monthly' ],
        [ 'loc' => home_url( '/journal' ),    'priority' => '0.7', 'changefreq' => 'weekly' ],
    ];
    $urls = array_merge( $urls, $static );

    // Projekte
    $projects = get_posts( [ 'post_type' => 'nr_project', 'posts_per_page' => -1, 'fields' => 'ids' ] );
    foreach ( $projects as $id ) {
        $imgs = [];
        if ( has_post_thumbnail( $id ) ) {
            $u = get_the_post_thumbnail_url( $id, 'nr-hero' );
            if ( $u ) $imgs[] = $u;
        }
        $gallery = function_exists( 'nr_field' ) ? nr_field( 'project_gallery', $id ) : [];
        if ( is_array( $gallery ) ) {
            foreach ( $gallery as $g ) {
                if ( is_array( $g ) && ! empty( $g['url'] ) ) {
                    $imgs[] = $g['url'];
                } elseif ( is_numeric( $g ) ) {
                    $gu = wp_get_attachment_image_url( (int) $g, 'nr-hero' );
                    if ( $gu ) $imgs[] = $gu;
                }
            }
        }
        $urls[] = [
            'loc'        => get_permalink( $id ),
            'lastmod'    => get_the_modified_date( 'Y-m-d', $id ),
            'priority'   => '0.8',
            'changefreq' => 'monthly',
            'images'     => array_slice( array_values( array_unique( $imgs ) ), 0, 1000 ),
        ];
    }

    // Journal entries (text content — important for SEO posts/guides)
    $journal = get_posts( [ 'post_type' => 'nr_journal', 'posts_per_page' => -1, 'fields' => 'ids' ] );
    foreach ( $journal as $id ) {
        $imgs = [];
        if ( has_post_thumbnail( $id ) ) {
            $u = get_the_post_thumbnail_url( $id, 'nr-hero' );
            if ( $u ) $imgs[] = $u;
        }
        $urls[] = [
            'loc'        => get_permalink( $id ),
            'lastmod'    => get_the_modified_date( 'Y-m-d', $id ),
            'priority'   => '0.6',
            'changefreq' => 'monthly',
            'images'     => $imgs,
        ];
    }

    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";
    foreach ( $urls as $url ) {
        echo "  <url>\n";
        echo '    <loc>' . esc_url( $url['loc'] ) . "</loc>\n";
        if ( ! empty( $url['lastmod'] ) ) echo '    <lastmod>' . esc_html( $url['lastmod'] ) . "</lastmod>\n";
        if ( ! empty( $url['changefreq'] ) ) echo '    <changefreq>' . esc_html( $url['changefreq'] ) . "</changefreq>\n";
        if ( ! empty( $url['priority'] ) ) echo '    <priority>' . esc_html( $url['priority'] ) . "</priority>\n";
        if ( ! empty( $url['images'] ) ) {
            foreach ( $url['images'] as $img ) {
                echo '    <image:image><image:loc>' . esc_url( $img ) . "</image:loc></image:image>\n";
            }
        }
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
	printf( '<meta name="description" content="%s">' . "\n", esc_attr( $desc ) ); // #12
	if ( is_paged() ) echo '<meta name="robots" content="noindex,follow">' . "\n"; // #19

	// Open Graph
	printf( '<meta property="og:type" content="%s">' . "\n", esc_attr( $type ) );
	printf( '<meta property="og:site_name" content="%s">' . "\n", esc_attr( get_bloginfo( 'name' ) ) );
	printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( $title ) );
	printf( '<meta property="og:description" content="%s">' . "\n", esc_attr( $desc ) );
	printf( '<meta property="og:url" content="%s">' . "\n", esc_url( $canonical ) );
	printf( '<meta property="og:locale" content="%s">' . "\n", esc_attr( $locale ) );
	// #69 — prefer a composited 1200x630 share card for single projects.
	$og_w = 2400; $og_h = 1600;
	if ( is_singular( 'nr_project' ) && function_exists( 'nr_og_card_url' ) ) {
		$card = nr_og_card_url( get_queried_object_id() );
		if ( $card ) { $image = $card; $og_w = 1200; $og_h = 630; }
	}
	if ( $image ) {
		printf( '<meta property="og:image" content="%s">' . "\n", esc_url( $image ) );
		printf( '<meta property="og:image:width" content="%d">' . "\n", $og_w );
		printf( '<meta property="og:image:height" content="%d">' . "\n", $og_h );
		printf( '<meta property="og:image:alt" content="%s">' . "\n", esc_attr( $title ) ); // #15
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
	} elseif ( is_singular( 'nr_journal' ) ) {
		$jurl = function_exists( 'nr_journal_url' ) ? nr_journal_url() : get_post_type_archive_link( 'nr_journal' );
		if ( $jurl ) $items[] = [ 'name' => __( 'Journal', 'raveenthiran' ), 'item' => $jurl ];
		$items[] = [ 'name' => get_the_title(), 'item' => get_permalink() ];
	} elseif ( is_post_type_archive( 'nr_journal' ) ) {
		$items[] = [ 'name' => __( 'Journal', 'raveenthiran' ), 'item' => get_post_type_archive_link( 'nr_journal' ) ];
	} elseif ( is_tax( [ 'nr_project_cat', 'nr_project_tag', 'nr_project_series' ] ) ) {
		$items[] = [ 'name' => __( 'Work', 'raveenthiran' ), 'item' => get_post_type_archive_link( 'nr_project' ) ];
		$t = get_queried_object();
		if ( $t ) $items[] = [ 'name' => $t->name, 'item' => get_term_link( $t ) ];
	} elseif ( is_tax( 'nr_journal_cat' ) ) {
		$jurl = function_exists( 'nr_journal_url' ) ? nr_journal_url() : get_post_type_archive_link( 'nr_journal' );
		if ( $jurl ) $items[] = [ 'name' => __( 'Journal', 'raveenthiran' ), 'item' => $jurl ];
		$t = get_queried_object();
		if ( $t ) $items[] = [ 'name' => $t->name, 'item' => get_term_link( $t ) ];
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
	if ( ( $_COOKIE['nr_consent'] ?? '' ) !== 'accepted' ) return; // #75 consent-gated
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

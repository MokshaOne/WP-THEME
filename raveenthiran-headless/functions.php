<?php
/**
 * Raveenthiran Headless — content model + normalized REST contract.
 *
 * This theme renders no front end (see index.php). It registers the Work CPT,
 * the Album (work_category) and Service (work_service) taxonomies, and exposes
 * `project`, `gallery` and `seo` REST fields that read ACF first and fall back
 * to legacy meta / attached media. The ACF field group auto-loads from the
 * theme's acf-json/ folder.
 *
 * @package RaveenthiranHeadless
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/* ── Post type + taxonomies ─────────────────────────────────────────────── */
add_action( 'init', function () {

	if ( ! post_type_exists( 'work' ) ) {
		register_post_type( 'work', array(
			'labels' => array(
				'name'          => __( 'Work', 'rvn' ),
				'singular_name' => __( 'Project', 'rvn' ),
				'add_new_item'  => __( 'Add New Project', 'rvn' ),
				'edit_item'     => __( 'Edit Project', 'rvn' ),
				'all_items'     => __( 'All Work', 'rvn' ),
				'menu_name'     => __( 'Work', 'rvn' ),
			),
			'public'        => true,
			'has_archive'   => true,
			'menu_icon'     => 'dashicons-camera-alt',
			'menu_position' => 5,
			'rewrite'       => array( 'slug' => 'work', 'with_front' => false ),
			'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ),
			'show_in_rest'  => true,
		) );
	}

	if ( ! taxonomy_exists( 'work_category' ) ) {
		register_taxonomy( 'work_category', 'work', array(
			'labels'            => array( 'name' => __( 'Albums', 'rvn' ), 'singular_name' => __( 'Album', 'rvn' ) ),
			'public'            => true,
			'hierarchical'      => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'album', 'with_front' => false ),
		) );
	}

	if ( ! taxonomy_exists( 'work_service' ) ) {
		register_taxonomy( 'work_service', 'work', array(
			'labels'            => array( 'name' => __( 'Services', 'rvn' ), 'singular_name' => __( 'Service', 'rvn' ) ),
			'public'            => true,
			'hierarchical'      => false,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'service', 'with_front' => false ),
		) );
	}
} );

/* Flush rewrite rules once when the theme is activated (so /work resolves). */
add_action( 'after_switch_theme', function () { flush_rewrite_rules(); } );

/* ── Helpers ────────────────────────────────────────────────────────────── */

/** Read an ACF field if ACF is active, else the legacy `_still_<key>` meta. */
function rvn_field( $post_id, $acf_key, $legacy_key = '' ) {
	if ( function_exists( 'get_field' ) ) {
		$v = get_field( $acf_key, $post_id );
		if ( $v !== null && $v !== '' && $v !== false ) { return $v; }
	}
	if ( $legacy_key ) {
		$v = get_post_meta( $post_id, $legacy_key, true );
		if ( $v !== '' ) { return $v; }
	}
	return '';
}

/** Normalize credits into [{role,name,url}]. Accepts an ACF repeater (array of
 *  rows) OR a textarea with one "Role — Name" per line (ACF-free friendly). */
function rvn_credits( $post_id ) {
	$raw = function_exists( 'get_field' ) ? get_field( 'credits', $post_id ) : '';
	$out = array();
	if ( is_array( $raw ) ) {
		foreach ( $raw as $row ) {
			$role = isset( $row['role'] ) ? trim( (string) $row['role'] ) : '';
			$name = isset( $row['name'] ) ? trim( (string) $row['name'] ) : '';
			$url  = isset( $row['url'] ) ? trim( (string) $row['url'] ) : '';
			if ( $role || $name ) { $out[] = array( 'role' => $role, 'name' => $name, 'url' => $url ); }
		}
	} elseif ( is_string( $raw ) && $raw !== '' ) {
		foreach ( preg_split( '/\r\n|\r|\n/', $raw ) as $line ) {
			$line = trim( $line );
			if ( $line === '' ) { continue; }
			$parts = preg_split( '/\s*(?:—|–|-|:)\s*/u', $line, 2 );
			$out[] = array( 'role' => trim( $parts[0] ), 'name' => isset( $parts[1] ) ? trim( $parts[1] ) : '', 'url' => '' );
		}
	}
	return $out;
}

/** Build a responsive image record from an attachment id. */
function rvn_image( $id, $size = 'large' ) {
	$src = wp_get_attachment_image_src( $id, $size );
	if ( ! $src ) { return null; }
	return array(
		'src'    => $src[0],
		'w'      => (int) $src[1],
		'h'      => (int) $src[2],
		'srcset' => (string) wp_get_attachment_image_srcset( $id, $size ),
		'alt'    => (string) get_post_meta( $id, '_wp_attachment_image_alt', true ),
	);
}

/* ── REST contract: project / gallery / seo ─────────────────────────────── */
add_action( 'rest_api_init', function () {

	register_rest_field( 'work', 'project', array(
		'get_callback' => function ( $post ) {
			$id = $post['id'];
			$services = array();
			$terms = wp_get_post_terms( $id, 'work_service', array( 'fields' => 'names' ) );
			if ( ! is_wp_error( $terms ) ) { $services = array_values( $terms ); }
			return array(
				'client'        => (string) rvn_field( $id, 'client',   '_still_client' ),
				'year'          => (string) rvn_field( $id, 'year',     '_still_year' ),
				'location'      => (string) rvn_field( $id, 'location', '_still_location' ),
				'website'       => (string) rvn_field( $id, 'website',  '_still_website' ),
				'services'      => $services,
				'credits'       => rvn_credits( $id ),
				'featured_home' => (bool) ( function_exists( 'get_field' ) ? get_field( 'featured_home', $id ) : false ),
			);
		},
		'schema' => array( 'type' => 'object', 'context' => array( 'view', 'edit', 'embed' ) ),
	) );

	register_rest_field( 'work', 'gallery', array(
		'get_callback' => function ( $post ) {
			$out = array();

			// 1) ACF Gallery field (curated + ordered). return_format = id.
			if ( function_exists( 'get_field' ) ) {
				$g = get_field( 'gallery', $post['id'] );
				if ( is_array( $g ) ) {
					foreach ( $g as $item ) {
						$aid = is_array( $item ) ? (int) ( $item['ID'] ?? $item['id'] ?? 0 ) : (int) $item;
						$rec = $aid ? rvn_image( $aid, 'large' ) : null;
						if ( $rec ) { $out[] = $rec; }
					}
				}
			}

			// 2) Fallback: images attached to the post (excluding the featured one).
			if ( empty( $out ) ) {
				$featured = (int) get_post_thumbnail_id( $post['id'] );
				foreach ( get_attached_media( 'image', $post['id'] ) as $img ) {
					if ( (int) $img->ID === $featured ) { continue; }
					$rec = rvn_image( $img->ID, 'large' );
					if ( $rec ) { $out[] = $rec; }
				}
			}

			return $out;
		},
		'schema' => array( 'type' => 'array', 'context' => array( 'view', 'edit', 'embed' ) ),
	) );

	register_rest_field( 'work', 'seo', array(
		'get_callback' => function ( $post ) {
			$desc = (string) rvn_field( $post['id'], 'seo_description' );
			$ogid = function_exists( 'get_field' ) ? get_field( 'og_image', $post['id'] ) : 0;
			if ( is_array( $ogid ) && isset( $ogid['id'] ) ) { $ogid = $ogid['id']; }
			$og = '';
			if ( $ogid ) { $rec = rvn_image( (int) $ogid, 'large' ); $og = $rec ? $rec['src'] : ''; }
			return array( 'description' => $desc, 'og_image' => $og );
		},
		'schema' => array( 'type' => 'object', 'context' => array( 'view', 'edit', 'embed' ) ),
	) );
} );

/* ── Tidy admin: no theme customizer/front-end noise for a headless install ── */
add_action( 'after_setup_theme', function () {
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'title-tag' );
} );

/* ── Site settings: ACF options page (edit the Studio bio, contact, price
   calculator and FAQ from WordPress) + a REST endpoint the Astro build reads. ── */
add_action( 'acf/init', function () {
	if ( function_exists( 'acf_add_options_page' ) ) {
		acf_add_options_page( array(
			'page_title' => 'Site settings',
			'menu_title' => 'Site settings',
			'menu_slug'  => 'rvn-site',
			'capability' => 'edit_posts',
			'redirect'   => false,
			'icon_url'   => 'dashicons-admin-customizer',
			'position'   => 3,
		) );
	}
} );

add_action( 'rest_api_init', function () {
	register_rest_route( 'rvn/v1', '/site', array(
		'methods'             => 'GET',
		'permission_callback' => '__return_true',
		'callback'            => 'rvn_site_payload',
	) );
} );

/** Read an ACF options value with a fallback. */
function rvn_site_opt( $key, $default = '' ) {
	if ( ! function_exists( 'get_field' ) ) { return $default; }
	$v = get_field( $key, 'option' );
	return ( $v === null || $v === false || $v === '' ) ? $default : $v;
}

/** Normalized site payload for the headless frontend. */
function rvn_site_payload() {
	$rows_kv = function ( $key, $a = 'label', $b = 'value' ) {
		$out = array();
		foreach ( (array) rvn_site_opt( $key, array() ) as $r ) {
			$la = isset( $r[ $a ] ) ? trim( (string) $r[ $a ] ) : '';
			$lb = isset( $r[ $b ] ) ? $r[ $b ] : '';
			if ( $la !== '' ) { $out[] = array( $a => $la, $b => $lb ); }
		}
		return $out;
	};
	$lines = function ( $key ) {
		$out = array();
		$raw = rvn_site_opt( $key, '' );
		if ( is_string( $raw ) && $raw !== '' ) {
			foreach ( preg_split( '/\r\n|\r|\n/', $raw ) as $l ) { $l = trim( $l ); if ( $l !== '' ) { $out[] = $l; } }
		}
		return $out;
	};

	$types = array();
	foreach ( $rows_kv( 'project_types', 'label', 'base' ) as $r ) { $types[] = array( 'label' => $r['label'], 'base' => (float) $r['base'] ); }
	$addons = array();
	foreach ( $rows_kv( 'addons', 'label', 'price' ) as $r ) { $addons[] = array( 'label' => $r['label'], 'price' => (float) $r['price'] ); }
	$stats = array();
	foreach ( $rows_kv( 'studio_stats', 'label', 'value' ) as $r ) { $stats[] = array( 'label' => $r['label'], 'value' => (string) $r['value'] ); }
	$faq = array();
	foreach ( (array) rvn_site_opt( 'faq', array() ) as $r ) {
		$q = isset( $r['question'] ) ? trim( (string) $r['question'] ) : '';
		if ( $q === '' ) { continue; }
		$faq[] = array( 'q' => $q, 'a' => isset( $r['answer'] ) ? (string) $r['answer'] : '' );
	}
	$portrait = rvn_site_opt( 'studio_portrait', '' );
	if ( is_array( $portrait ) ) { $portrait = $portrait['url'] ?? ''; }

	return array(
		'studio'  => array(
			'lede'     => (string) rvn_site_opt( 'studio_lede', '' ),
			'bio'      => (string) rvn_site_opt( 'studio_bio', '' ),
			'portrait' => (string) $portrait,
			'stats'    => $stats,
			'clients'  => $lines( 'studio_clients' ),
		),
		'contact' => array(
			'email'     => (string) rvn_site_opt( 'contact_email', '' ),
			'location'  => (string) rvn_site_opt( 'contact_location', '' ),
			'response'  => (string) rvn_site_opt( 'contact_response', '' ),
			'instagram' => (string) rvn_site_opt( 'contact_instagram', '' ),
		),
		'pricing' => array(
			'currency' => (string) rvn_site_opt( 'currency', '€' ),
			'types'    => $types,
			'addons'   => $addons,
			'licence'  => (float) rvn_site_opt( 'licence_price', 0 ),
			'per_km'   => (float) rvn_site_opt( 'per_km', 0 ),
		),
		'faq'     => $faq,
	);
}

/* ══════════════════════════════════════════════════════════════════════
   Enquiry endpoint — receives the frontend enquiry form, stores it as a
   private CPT, and emails the studio + an auto-reply to the client.
   ══════════════════════════════════════════════════════════════════════ */

add_action( 'init', function () {
	register_post_type( 'rvn_enquiry', array(
		'labels'       => array( 'name' => 'Enquiries', 'singular_name' => 'Enquiry' ),
		'public'       => false,
		'show_ui'      => true,
		'show_in_menu' => true,
		'menu_icon'    => 'dashicons-email-alt',
		'menu_position'=> 26,
		'supports'     => array( 'title', 'editor' ),
		'capability_type' => 'post',
	) );
} );

/* CORS for the rvn/v1 namespace so the static frontend (another origin) can POST. */
add_action( 'rest_api_init', function () {
	remove_filter( 'rest_pre_serve_request', 'rest_send_cors_headers' );
	add_filter( 'rest_pre_serve_request', function ( $value ) {
		$origin  = get_http_origin();
		$allowed = array(
			'https://raveenthiran.com',
			'https://www.raveenthiran.com',
			'http://localhost:4321',
			'http://localhost:4322',
		);
		if ( $origin && in_array( $origin, $allowed, true ) ) {
			header( 'Access-Control-Allow-Origin: ' . $origin );
			header( 'Access-Control-Allow-Methods: GET, POST, OPTIONS' );
			header( 'Access-Control-Allow-Headers: Content-Type' );
			header( 'Vary: Origin' );
		}
		return $value;
	} );
}, 15 );

add_action( 'rest_api_init', function () {
	register_rest_route( 'rvn/v1', '/enquiry', array(
		'methods'             => 'POST',
		'permission_callback' => '__return_true',
		'callback'            => 'rvn_enquiry_submit',
	) );
} );

function rvn_enquiry_submit( WP_REST_Request $request ) {
	$p = $request->get_json_params();
	if ( ! is_array( $p ) ) { $p = $request->get_params(); }

	// Honeypot — bots fill hidden "company"; drop silently as success.
	if ( ! empty( $p['company'] ) ) { return new WP_REST_Response( array( 'ok' => true ), 200 ); }

	$name  = sanitize_text_field( $p['name'] ?? '' );
	$email = sanitize_email( $p['email'] ?? '' );
	if ( $name === '' || ! is_email( $email ) ) {
		return new WP_REST_Response( array( 'ok' => false, 'error' => 'Please add your name and a valid email.' ), 400 );
	}

	$type     = sanitize_text_field( $p['project_type'] ?? '' );
	$addons   = sanitize_text_field( $p['addons'] ?? '' );
	$estimate = sanitize_text_field( $p['estimate'] ?? '' );
	$date     = sanitize_text_field( $p['preferred_date'] ?? '' );
	$notes    = sanitize_textarea_field( $p['notes'] ?? '' );

	$body  = "Name: {$name}\nEmail: {$email}\nProject type: {$type}\nAdd-ons: {$addons}\n";
	$body .= "Estimate: {$estimate}\nPreferred date: {$date}\n\n{$notes}";

	wp_insert_post( array(
		'post_type'    => 'rvn_enquiry',
		'post_status'  => 'private',
		'post_title'   => trim( $name . ' — ' . ( $type ?: 'Enquiry' ) . ( $estimate ? ' (' . $estimate . ')' : '' ) ),
		'post_content' => $body,
	) );

	$studio  = rvn_site_opt( 'contact_email', get_option( 'admin_email' ) );
	wp_mail( $studio, 'New enquiry — ' . $name, $body, array(
		'Content-Type: text/plain; charset=UTF-8',
		'Reply-To: ' . $name . ' <' . $email . '>',
	) );

	$reply  = "Hi {$name},\n\nThank you for your enquiry — I've received it and will reply within 24 hours ";
	$reply .= "with availability and a firm quote.\n\nHere's what you sent:\n\n{$body}\n\n";
	$reply .= "— Nishuthan Raveenthiran\nraveenthiran.com";
	wp_mail( $email, 'Thanks for your enquiry — Raveenthiran', $reply, array(
		'Content-Type: text/plain; charset=UTF-8',
		'Reply-To: ' . $studio,
	) );

	return new WP_REST_Response( array( 'ok' => true ), 200 );
}

/* ══════════════════════════════════════════════════════════════════════
   SMTP delivery — routes wp_mail() through an authenticated SMTP server so
   enquiries + auto-replies actually arrive. Adapted from the Obscura theme.
   Configured under Site settings → Mail (SMTP). The password may instead be
   defined as RVN_SMTP_PASS in wp-config.php (kept out of the database).
   ══════════════════════════════════════════════════════════════════════ */

function rvn_smtp_password() {
	if ( defined( 'RVN_SMTP_PASS' ) && RVN_SMTP_PASS ) { return RVN_SMTP_PASS; }
	return (string) rvn_site_opt( 'smtp_pass', '' );
}

add_action( 'phpmailer_init', function ( $phpmailer ) {
	if ( (int) rvn_site_opt( 'smtp_enable', 0 ) !== 1 ) { return; }

	$host = trim( (string) rvn_site_opt( 'smtp_host', '' ) );
	$user = trim( (string) rvn_site_opt( 'smtp_user', '' ) );
	$pass = rvn_smtp_password();
	if ( $host === '' || $user === '' || $pass === '' ) { return; } // not configured → leave default mail()

	$phpmailer->isSMTP();
	$phpmailer->Host       = $host;
	$phpmailer->Port       = (int) ( rvn_site_opt( 'smtp_port', 587 ) ?: 587 );
	$phpmailer->SMTPAuth   = true;
	$phpmailer->Username   = $user;
	$phpmailer->Password   = $pass;
	$phpmailer->SMTPSecure = rvn_site_opt( 'smtp_secure', 'tls' ) === 'ssl' ? 'ssl' : 'tls';

	$from = trim( (string) rvn_site_opt( 'smtp_from', '' ) );
	if ( $from === '' || ! is_email( $from ) ) { $from = $user; }
	$name = (string) ( rvn_site_opt( 'smtp_fromname', '' ) ?: get_bloginfo( 'name' ) );
	try {
		$phpmailer->setFrom( $from, $name, false );
		$phpmailer->Sender = $from; // envelope-from for SPF alignment
	} catch ( \Exception $e ) {
		// keep whatever From WP already set
	}
}, 20 );

add_filter( 'wp_mail_from', function ( $email ) {
	if ( (int) rvn_site_opt( 'smtp_enable', 0 ) !== 1 ) { return $email; }
	$from = trim( (string) rvn_site_opt( 'smtp_from', '' ) );
	return ( $from && is_email( $from ) ) ? $from : $email;
}, 20 );

add_filter( 'wp_mail_from_name', function ( $name ) {
	if ( (int) rvn_site_opt( 'smtp_enable', 0 ) !== 1 ) { return $name; }
	$n = trim( (string) rvn_site_opt( 'smtp_fromname', '' ) );
	return $n ?: $name;
}, 20 );

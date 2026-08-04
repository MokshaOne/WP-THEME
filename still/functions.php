<?php
/**
 * Still — theme functions.
 *
 * Still is the cinematic DESIGN layer. The ENGINE (content types, ACF, Theme
 * Settings, SMTP, PWA, WebP, OG cards, PDF, map, SEO, security, shortcodes …)
 * is cloned from moksha1one and loaded from inc/engine.php. It registers the
 * nr_* content model (nr_project = Work, nr_journal = Journal, nr_testimonial,
 * nr_enquiry). Still's templates render that content in the Still look; we
 * dequeue moksha's own skin so this design owns the front end.
 *
 * @package Still
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'STILL_VER', '1.0.0' );

/* ── The moksha1one engine (CPTs, ACF, features, Theme Settings) ────── */
require_once get_template_directory() . '/inc/engine.php';

/* ── Still design setup (additive to the engine's supports) ─────────── */
add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	add_image_size( 'still-card', 900, 1125, true );
	add_image_size( 'still-hero', 2000, 1333, false );
	register_nav_menus( array( 'primary' => __( 'Primary (dock)', 'still' ) ) );
}, 20 );

/* ── Still design assets ────────────────────────────────────────────── */
add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'still', get_template_directory_uri() . '/assets/css/main.css', array(), STILL_VER );
	wp_enqueue_style( 'still-base', get_stylesheet_uri(), array( 'still' ), STILL_VER );
	wp_enqueue_script( 'still', get_template_directory_uri() . '/assets/js/main.js', array(), STILL_VER, true );
}, 20 );

/* ── Dequeue moksha1one's VISUAL layer — keep its features, drop its skin ── */
add_action( 'wp_enqueue_scripts', function () {
	foreach ( array( 'nr-theme', 'void-skin', 'mk-skin', 'nr-fonts', 'nr-features', 'nr-fixes' ) as $h ) {
		wp_dequeue_style( $h );
	}
	foreach ( array( 'nr-theme', 'void-skin', 'mk-scroll', 'nr-studio', 'nr-magic', 'nr-webgl-hero', 'nr-features' ) as $h ) {
		wp_dequeue_script( $h );
	}
}, 100 );

/* Flush rewrites once after switching to Still. */
add_action( 'after_switch_theme', function () {
	if ( ! get_option( 'still_flushed' ) ) {
		flush_rewrite_rules();
		update_option( 'still_flushed', '1' );
	}
} );
add_action( 'switch_theme', function () { delete_option( 'still_flushed' ); } );

/* ── URL helpers → point at the engine's content model ─────────────── */
function still_work_url() {
	return get_post_type_archive_link( 'nr_project' ) ?: home_url( '/project' );
}
function still_journal_url() {
	return get_post_type_archive_link( 'nr_journal' ) ?: home_url( '/journal' );
}
function still_page_url( $slug, $fallback = '' ) {
	$page = get_page_by_path( $slug );
	return $page ? get_permalink( $page ) : home_url( '/' . ( $fallback ?: $slug ) );
}
function still_pad( $n ) {
	return str_pad( (string) (int) $n, 2, '0', STR_PAD_LEFT );
}

/** Minimal pagination markup matching the theme's .pagination styles. */
function still_pagination( $query = null ) {
	$args = array( 'mid_size' => 1, 'prev_text' => '←', 'next_text' => '→', 'type' => 'plain' );
	if ( $query instanceof WP_Query ) {
		$args['total']   = (int) $query->max_num_pages;
		$args['current'] = max( 1, (int) get_query_var( 'paged' ) );
	}
	$total = $args['total'] ?? ( $GLOBALS['wp_query']->max_num_pages ?? 1 );
	if ( $total < 2 ) {
		return;
	}
	$links = paginate_links( $args );
	if ( $links ) {
		echo '<nav class="pagination" aria-label="' . esc_attr__( 'Pagination', 'still' ) . '">' . wp_kses_post( $links ) . '</nav>';
	}
}

/**
 * Navigation model — drives the dock and the home teaser panels.
 * Each panel links OUT to a real page; it never replaces it.
 */
function still_nav_items() {
	$items = array(
		array( 'key' => 'work',    'label' => __( 'Work', 'still' ),    'url' => still_work_url(),                       'desc' => __( 'Portraiture, architecture and the quiet spaces between. Selected projects — new work added continually.', 'still' ) ),
		array( 'key' => 'studio',  'label' => __( 'Studio', 'still' ),  'url' => still_page_url( 'about', 'about' ),     'desc' => __( 'A practice of looking slowly — and keeping only what lasts. Based in Vienna, working across Europe.', 'still' ) ),
		array( 'key' => 'enquire', 'label' => __( 'Enquire', 'still' ), 'url' => still_page_url( 'enquire', 'enquire' ), 'desc' => __( 'Commissions and editorial — studio details, a price estimate, and the form. A reply within 24 hours.', 'still' ) ),
	);
	return apply_filters( 'still_nav_items', $items );
}

/**
 * Render a project/journal cover using the engine's smart image helper when
 * present (handles featured image, ACF cover and gallery fallbacks), else the
 * plain featured image.
 */
function still_cover( $id, $size = 'still-card' ) {
	if ( function_exists( 'nr_image_or_placeholder' ) ) {
		nr_image_or_placeholder( $id, $size, get_the_title( $id ) );
	} elseif ( has_post_thumbnail( $id ) ) {
		echo get_the_post_thumbnail( $id, $size, array( 'alt' => esc_attr( get_the_title( $id ) ) ) );
	} else {
		echo '<span class="ph">' . esc_html__( 'Photo', 'still' ) . '</span>';
	}
}

/* ═══════════════════════════════════════════════════════════════════
 * Refinements — remove Journal, trim ACF, black-and-white backend
 * ═══════════════════════════════════════════════════════════════════ */

/* Keep nr_project (Work) alive; retire the Journal content type + taxonomy. */
add_action( 'init', function () {
	if ( post_type_exists( 'nr_journal' ) )       { unregister_post_type( 'nr_journal' ); }
	if ( taxonomy_exists( 'nr_journal_cat' ) )     { unregister_taxonomy( 'nr_journal_cat' ); }
}, 30 );

/* Drop the sci-fi "VOID — Specimen Controls" ACF group; keep the useful ones. */
add_action( 'acf/init', function () {
	if ( function_exists( 'acf_remove_local_field_group' ) ) {
		acf_remove_local_field_group( 'group_void_specimen' );
	}
}, 20 );

/* Black-and-white backend: a grayscale admin colour scheme, made the default,
 * plus overrides that neutralise the engine's gold accents in wp-admin. */
add_action( 'admin_init', function () {
	wp_admin_css_color(
		'still',
		__( 'Still · B&W', 'still' ),
		get_template_directory_uri() . '/assets/css/admin-scheme.css',
		array( '#0f0f10', '#17171a', '#000000', '#f6f6f7' )
	);
} );
add_filter( 'get_user_option_admin_color', function () { return 'still'; } );
add_action( 'admin_enqueue_scripts', function () {
	wp_enqueue_style( 'still-admin', get_template_directory_uri() . '/assets/css/admin-still.css', array(), STILL_VER );
}, 100 );
add_action( 'login_enqueue_scripts', function () {
	wp_enqueue_style( 'still-admin', get_template_directory_uri() . '/assets/css/admin-still.css', array(), STILL_VER );
} );

/* ── Legal pages: auto-create Impressum / AGB / Datenschutz with demo data ──
 * Austrian photographer boilerplate (DEMO — must be reviewed before publishing).
 * Created once on theme activation if the slug doesn't already exist. */
function still_legal_content( $slug ) {
	switch ( $slug ) {
		case 'impressum':
			return <<<'HTML'
<p><em>Hinweis: Diese Seite ist eine unverbindliche Muster-Vorlage (Demo) und ersetzt keine Rechtsberatung. Bitte vor Veröffentlichung prüfen lassen.</em></p>
<h2>Impressum</h2>
<p>Offenlegung gemäß § 5 E-Commerce-Gesetz (ECG) und § 25 Mediengesetz.</p>
<p><strong>Nishuthan Raveenthiran</strong><br>Fotograf<br>[Straße und Hausnummer]<br>[PLZ] Wien<br>Österreich</p>
<p>E-Mail: office@raveenthiran.com<br>Telefon: [+43 …]<br>Web: raveenthiran.com</p>
<p>UID-Nummer: [ATU… – sofern vorhanden]<br>Berufsbezeichnung: Fotograf (verliehen in Österreich)<br>Mitgliedschaft: Wirtschaftskammer Wien</p>
<p>Es gelten die gewerberechtlichen Vorschriften (GewO), abrufbar unter www.ris.bka.gv.at.</p>
<h3>Verbraucherstreitbeilegung</h3>
<p>Die Europäische Kommission stellt eine Plattform zur Online-Streitbeilegung (OS) bereit: https://ec.europa.eu/consumers/odr. Wir sind nicht verpflichtet, an einem Streitbeilegungsverfahren vor einer Verbraucherschlichtungsstelle teilzunehmen.</p>
<h3>Urheberrecht</h3>
<p>Alle Inhalte und Fotografien dieser Website sind urheberrechtlich geschützt (© Nishuthan Raveenthiran). Jede Verwertung außerhalb der Grenzen des Urheberrechts bedarf der schriftlichen Zustimmung.</p>
HTML;
		case 'agb':
			return <<<'HTML'
<p><em>Hinweis: Muster-AGB (Demo). Vor Verwendung anwaltlich prüfen lassen.</em></p>
<h2>Allgemeine Geschäftsbedingungen</h2>
<h3>1. Geltungsbereich</h3>
<p>Diese AGB gelten für alle Aufträge und Verträge zwischen Nishuthan Raveenthiran („Fotograf") und dem Auftraggeber über fotografische Leistungen.</p>
<h3>2. Vertragsabschluss</h3>
<p>Angebote sind freibleibend. Ein Vertrag kommt mit schriftlicher Auftragsbestätigung oder mit Beginn der Leistungserbringung zustande. Über das Enquire-Formular errechnete Preise sind unverbindliche Schätzungen.</p>
<h3>3. Preise und Zahlung</h3>
<p>Es gelten die vereinbarten Preise zzgl. gesetzlicher Umsatzsteuer. Reisekosten werden gesondert verrechnet. Rechnungen sind innerhalb von 14 Tagen ohne Abzug zahlbar.</p>
<h3>4. Nutzungsrechte und Urheberrecht</h3>
<p>Der Fotograf bleibt Urheber aller Aufnahmen. Nutzungsrechte werden im vereinbarten Umfang (Zweck, Dauer, Gebiet) eingeräumt und gehen erst nach vollständiger Bezahlung über. Bearbeitung oder Weitergabe an Dritte bedürfen der Zustimmung.</p>
<h3>5. Termine, Storno und Wetter</h3>
<p>Termine können bis 7 Tage vor dem Shooting kostenfrei verschoben werden. Bei Absage innerhalb von 48 Stunden wird das volle Honorar fällig. Outdoor-Termine können wetterbedingt einvernehmlich verlegt werden.</p>
<h3>6. Haftung</h3>
<p>Der Fotograf haftet nur für Vorsatz und grobe Fahrlässigkeit. Für technische Ausfälle oder Datenverlust wird im gesetzlich zulässigen Rahmen keine Haftung übernommen.</p>
<h3>7. Gerichtsstand und Recht</h3>
<p>Es gilt österreichisches Recht. Gerichtsstand ist Wien.</p>
HTML;
		case 'datenschutz':
			return <<<'HTML'
<p><em>Hinweis: Muster-Datenschutzerklärung (Demo). Vor Veröffentlichung prüfen lassen.</em></p>
<h2>Datenschutzerklärung</h2>
<h3>Verantwortlicher</h3>
<p>Nishuthan Raveenthiran, [Adresse], Wien. E-Mail: office@raveenthiran.com.</p>
<h3>Erhebung und Verarbeitung</h3>
<p>Wir verarbeiten personenbezogene Daten nur, soweit dies zur Bereitstellung der Website und unserer Leistungen erforderlich ist. Bei Nutzung des Enquire-/Kontaktformulars werden Name, E-Mail-Adresse und Ihre Nachricht zur Bearbeitung der Anfrage gespeichert (Art. 6 Abs. 1 lit. b DSGVO).</p>
<h3>Cookies</h3>
<p>Diese Website verwendet technisch notwendige Cookies. Ein Hinweisbanner erfragt Ihre Zustimmung; Ihre Auswahl wird lokal in Ihrem Browser gespeichert. Es werden keine Tracking- oder Werbe-Cookies ohne Einwilligung gesetzt.</p>
<h3>Speicherdauer</h3>
<p>Anfragen werden gelöscht, sobald sie nicht mehr benötigt werden und keine gesetzlichen Aufbewahrungspflichten entgegenstehen.</p>
<h3>Ihre Rechte</h3>
<p>Sie haben das Recht auf Auskunft, Berichtigung, Löschung, Einschränkung, Datenübertragbarkeit und Widerspruch sowie ein Beschwerderecht bei der Österreichischen Datenschutzbehörde (www.dsb.gv.at).</p>
<h3>Kontakt</h3>
<p>Für Anliegen zum Datenschutz: office@raveenthiran.com.</p>
HTML;
	}
	return '';
}

add_action( 'after_switch_theme', function () {
	if ( get_option( 'still_legal_seeded' ) ) {
		return;
	}
	$pages = array(
		'impressum'   => __( 'Impressum', 'still' ),
		'agb'         => __( 'AGB', 'still' ),
		'datenschutz' => __( 'Datenschutz', 'still' ),
	);
	foreach ( $pages as $slug => $title ) {
		if ( get_page_by_path( $slug ) ) {
			continue;
		}
		wp_insert_post( array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_name'    => $slug,
			'post_title'   => $title,
			'post_content' => still_legal_content( $slug ),
		) );
	}
	update_option( 'still_legal_seeded', '1' );
} );

/* Horizontal Work archive: show more per page so the filmstrip is worthwhile. */
add_action( 'pre_get_posts', function ( $q ) {
	if ( ! is_admin() && $q->is_main_query() && $q->is_post_type_archive( 'nr_project' ) ) {
		$q->set( 'posts_per_page', 24 );
	}
} );

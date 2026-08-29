<?php
/**
 * VPG v2 — functions.php · bootstrap.
 * Co-production of Raveenthiran × on1.agency.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

define( 'VPG_V2_VERSION', '2.0.0' );
define( 'VPG_V2_DIR',     get_template_directory() );
define( 'VPG_V2_URI',     get_template_directory_uri() );

// Composer autoload · loaded only if `composer install` has been run
if ( file_exists( VPG_V2_DIR . '/vendor/autoload.php' ) ) {
    require_once VPG_V2_DIR . '/vendor/autoload.php';
}

// ── ACF polyfill · MUST load first so get_field() exists everywhere ──
require_once VPG_V2_DIR . '/inc/acf-polyfill.php';

// ── Core infrastructure ─────────────────────────────────────
require_once VPG_V2_DIR . '/inc/theme-setup.php';
require_once VPG_V2_DIR . '/inc/enqueue.php';
require_once VPG_V2_DIR . '/inc/performance.php';     // WebP · lazy media · emoji removal
require_once VPG_V2_DIR . '/inc/helpers.php';
require_once VPG_V2_DIR . '/inc/cpts.php';
require_once VPG_V2_DIR . '/inc/acf-fields.php';      // CPT field groups (ACF + native fallback)
require_once VPG_V2_DIR . '/inc/customizer.php';
require_once VPG_V2_DIR . '/inc/admin-panel.php';     // "Vienna Photo Group" admin hub
require_once VPG_V2_DIR . '/inc/security.php';

// ── Magazine workflow ───────────────────────────────────────
require_once VPG_V2_DIR . '/inc/magazine-editor.php';
require_once VPG_V2_DIR . '/inc/pdf-generator.php';

// ── One-click setup wizard (pages + menus + rewrites) ──────
require_once VPG_V2_DIR . '/inc/setup-wizard.php';

// ── Phase-2 extensions ─────────────────────────────────────
require_once VPG_V2_DIR . '/inc/submission-handler.php';   // /contact /join /submit POST handlers
require_once VPG_V2_DIR . '/inc/submission-queue.php';     // ✉ Submissions admin
require_once VPG_V2_DIR . '/inc/seo.php';                  // OG + Twitter + RSS + sitemap
require_once VPG_V2_DIR . '/inc/cover-generator.php';      // GD-based auto-cover fallback
require_once VPG_V2_DIR . '/inc/members.php';              // profile pages · bookmarks · cross-CPT search
require_once VPG_V2_DIR . '/inc/account.php';              // frontend profile editor · local avatars · magic login · deletion
require_once VPG_V2_DIR . '/inc/community.php';            // RSVP · notifications · digest · photo of the week · trails · competitions
require_once VPG_V2_DIR . '/inc/newsletter.php';           // double-opt-in newsletter list + CSV export
require_once VPG_V2_DIR . '/inc/mail.php';                 // SMTP transport + delivery log
require_once VPG_V2_DIR . '/inc/platform.php';             // analytics · JSON-LD · embeds · REST hardening · locale · WP-CLI
require_once VPG_V2_DIR . '/inc/advanced.php';             // check-in · photo fingerprints · trust levels · bilingual search · AI alt text
require_once VPG_V2_DIR . '/inc/location-meta.php';        // map meta box · pin picker for location/studio/shop
require_once VPG_V2_DIR . '/inc/gating.php';               // [vpg-members] / [vpg-public] shortcodes + helpers
require_once VPG_V2_DIR . '/inc/cpt-gating.php';           // Magazine, Buying guide, Tutorials, Events · logged-in only during beta
require_once VPG_V2_DIR . '/inc/district-migration.php';   // Nominatim reverse-geocode · auto-fill district for legacy entries

// ── Optional · member submission flow ───────────────────────
$submission_handler = VPG_V2_DIR . '/inc/submission-handler.php';
if ( file_exists( $submission_handler ) ) require_once $submission_handler;

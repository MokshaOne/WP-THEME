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
require_once VPG_V2_DIR . '/inc/admin-columns.php';       // list columns · filters · at-a-glance
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
require_once VPG_V2_DIR . '/inc/interviews.php';          // featured-artist interviews · questions, dashboard form, invites
require_once VPG_V2_DIR . '/inc/newsletter.php';           // double-opt-in newsletter list + CSV export
require_once VPG_V2_DIR . '/inc/mail.php';                 // SMTP transport + delivery log
require_once VPG_V2_DIR . '/inc/platform.php';             // analytics · JSON-LD · embeds · REST hardening · locale · WP-CLI
require_once VPG_V2_DIR . '/inc/advanced.php';
require_once VPG_V2_DIR . '/inc/quickwins.php';          // Q2 · spot check · SOTW · heartbeat · maintenance             // check-in · photo fingerprints · trust levels · bilingual search · AI alt text
require_once VPG_V2_DIR . '/inc/discovery.php';
require_once VPG_V2_DIR . '/inc/power.php';             // Q4 · palette · series · districts · sitemap · GPX
require_once VPG_V2_DIR . '/inc/editorial.php';         // Q5 · review desk · hub tiles · glossary
require_once VPG_V2_DIR . '/inc/projects.php';
require_once VPG_V2_DIR . '/inc/followups.php';
require_once VPG_V2_DIR . '/inc/security.php';        // Q7 · TOTP 2FA · passkeys (WebAuthn)
require_once VPG_V2_DIR . '/inc/push.php';            // Q7 · self-hosted web push (VAPID + aes128gcm)
require_once VPG_V2_DIR . '/inc/api.php';             // Q7 · public read-only JSON API v1
require_once VPG_V2_DIR . '/inc/i18n.php';            // Q7 · DE/EN switch + hreflang
require_once VPG_V2_DIR . '/inc/mobile.php';          // Q7 · quick pin · offline drafts
require_once VPG_V2_DIR . '/inc/followups2.php';      // Q8 · circle rounds · xmlrpc harden · translation watch
require_once VPG_V2_DIR . '/inc/imagesearch.php';     // Q9 · similar/colour search · anonymise · vision hooks
require_once VPG_V2_DIR . '/inc/formats.php';         // Q9 · EPUB · zine · annual · listen
require_once VPG_V2_DIR . '/inc/federation.php';      // Q9 · ActivityPub · webmentions
require_once VPG_V2_DIR . '/inc/map-attributes.php';  // Cluster 01 · curated spot attributes
require_once VPG_V2_DIR . '/inc/map-tools.php';       // Cluster 01 · poster/QR · legend · embed · mini-map
require_once VPG_V2_DIR . '/inc/spot-quality.php';    // Cluster 02 · spot data · quality desk · districts
require_once VPG_V2_DIR . '/inc/trails.php';          // Cluster 03 · trail format · print · live · curation
require_once VPG_V2_DIR . '/inc/events.php';          // Cluster 04 · host desk · chat · live · programme
require_once VPG_V2_DIR . '/inc/magazine.php';        // Cluster 05 · issue craft · cover vote · desk · metrics
require_once VPG_V2_DIR . '/inc/print.php';           // Cluster 06 · print studio · generators · knowledge · lists
require_once VPG_V2_DIR . '/inc/journal.php';         // Cluster 07 · journal formats · before/after · desk · filter
require_once VPG_V2_DIR . '/inc/gallery.php';         // Cluster 08 · lightbox · palette · embed · exposure
require_once VPG_V2_DIR . '/inc/profile.php';         // Cluster 09 · artist page · CV · card · export · guestbook
require_once VPG_V2_DIR . '/inc/recognition.php';     // Cluster 10 · ranks · badges · certificates · principles
require_once VPG_V2_DIR . '/inc/community-plus.php';  // Cluster 11 · boards · Q&A · polls · handbook · status
require_once VPG_V2_DIR . '/inc/competitions.php';    // Cluster 12 · challenge setup · voting · hall · rules
require_once VPG_V2_DIR . '/inc/learning.php';        // Cluster 13 · tutorials · tools · paths · bounties · desk
require_once VPG_V2_DIR . '/inc/onboarding.php';      // Cluster 14 · onboarding mails · referral · growth desk       // Q6 · gallery moderation · district index · glossary autolinks          // Q5 · project rooms · event galleries · walls · collections         // Q3 · missing districts · coffee · idea box · views · stats
require_once VPG_V2_DIR . '/inc/search.php';           // Cluster 15 · smart search · discovery · search desk
require_once VPG_V2_DIR . '/inc/mobile-plus.php';     // Cluster 16 · PWA behaviours · thumb bar · GPS choice
require_once VPG_V2_DIR . '/inc/perf.php';            // Cluster 17 · speculation · view transitions · flags · tech desk
require_once VPG_V2_DIR . '/inc/location-meta.php';        // map meta box · pin picker for location/studio/shop
require_once VPG_V2_DIR . '/inc/gating.php';               // [vpg-members] / [vpg-public] shortcodes + helpers
require_once VPG_V2_DIR . '/inc/cpt-gating.php';           // Magazine, Buying guide, Tutorials, Events · logged-in only during beta
require_once VPG_V2_DIR . '/inc/district-migration.php';   // Nominatim reverse-geocode · auto-fill district for legacy entries

// ── Optional · member submission flow ───────────────────────
$submission_handler = VPG_V2_DIR . '/inc/submission-handler.php';
if ( file_exists( $submission_handler ) ) require_once $submission_handler;

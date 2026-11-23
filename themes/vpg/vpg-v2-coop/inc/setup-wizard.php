<?php
/**
 * VPG v2 — Setup Wizard.
 *
 * Runs once on theme activation. Idempotent — re-running just fills gaps,
 * never duplicates. Also exposes a manual trigger under
 * Tools → "Reset VPG default content".
 *
 * What it does:
 *   1. Creates the 15 page templates as actual Pages (slug + template + sample content)
 *   2. Creates 5 nav menus and populates them with the right items
 *   3. Assigns each menu to its theme location
 *   4. Sets Settings → Reading → "Show on front: latest posts" stays default
 *      (front-page.php handles the homepage automatically)
 *   5. Flushes rewrite rules so CPT URLs work immediately
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ─── Run on activation ─────────────────────────────────────────── */
add_action( 'after_switch_theme', 'vpg_run_setup_wizard' );

/* ─── Manual trigger · admin-post handler ───────────────────────── */
add_action( 'admin_post_vpg_reset_setup', function () {
    if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden' );
    check_admin_referer( 'vpg_reset_setup' );
    vpg_run_setup_wizard();
    wp_safe_redirect( admin_url( 'tools.php?vpg_setup=done' ) );
    exit;
} );

/* ─── Admin notice when not yet seeded ──────────────────────────── */
add_action( 'admin_notices', function () {
    if ( ! current_user_can( 'manage_options' ) ) return;
    if ( get_option( 'vpg_setup_complete' ) ) return;
    ?>
    <div class="notice notice-info">
        <p><strong>VPG v2</strong> · <?php esc_html_e( 'Default pages and menus are not set up yet. Run the wizard to create everything in one click.', 'vpg-v2' ); ?></p>
        <p>
            <a class="button button-primary" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=vpg_reset_setup' ), 'vpg_reset_setup' ) ); ?>">
                <?php esc_html_e( 'Run setup now', 'vpg-v2' ); ?>
            </a>
        </p>
    </div>
    <?php
} );

/* ─── Tools page UI · re-run + status ──────────────────────────── */
add_action( 'admin_menu', function () {
    add_management_page(
        'VPG · Setup',
        'VPG · Setup',
        'manage_options',
        'vpg-setup',
        'vpg_render_setup_page'
    );
} );

function vpg_render_setup_page() {
    $complete = (bool) get_option( 'vpg_setup_complete' );
    ?>
    <div class="wrap">
        <h1>VPG v2 · <?php esc_html_e( 'Setup wizard', 'vpg-v2' ); ?></h1>
        <?php if ( ! empty( $_GET['vpg_setup'] ) && $_GET['vpg_setup'] === 'done' ) : ?>
            <div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Setup complete · 15 pages + 5 menus created.', 'vpg-v2' ); ?></p></div>
        <?php endif; ?>

        <p style="max-width:720px;line-height:1.6"><?php esc_html_e( 'This creates the 15 site pages (About, Contact, Join, etc.) with the correct page templates assigned, plus 5 nav menus (Primary + 4 footer columns) wired to their theme locations.', 'vpg-v2' ); ?></p>

        <p style="max-width:720px;line-height:1.6"><strong><?php esc_html_e( 'Idempotent · safe to re-run.', 'vpg-v2' ); ?></strong> <?php esc_html_e( 'Existing pages with the same slug are left in place — only missing ones are created. Menus are wiped and rebuilt from the canonical list.', 'vpg-v2' ); ?></p>

        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <?php wp_nonce_field( 'vpg_reset_setup' ); ?>
            <input type="hidden" name="action" value="vpg_reset_setup">
            <p>
                <button type="submit" class="button button-primary button-large">
                    <?php echo $complete ? esc_html__( 'Re-run setup', 'vpg-v2' ) : esc_html__( 'Run setup now', 'vpg-v2' ); ?>
                </button>
            </p>
        </form>

        <hr style="margin:2.5rem 0">

        <h2><?php esc_html_e( 'Migrate v1 coordinates', 'vpg-v2' ); ?></h2>
        <p style="max-width:720px;line-height:1.6"><?php esc_html_e( 'If you imported from VPG v1, locations/studios/shops store their lat/lng in a single ACF field (e.g. "coordinates", "studio_coordinates"). Run this to split them into the v2 location_lat / studio_lat / shop_lat meta keys so the new map can read them. Tries seven candidate field-name variants per CPT · safe to re-run · skips entries already migrated.', 'vpg-v2' ); ?></p>
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <?php wp_nonce_field( 'vpg_migrate_coords' ); ?>
            <input type="hidden" name="action" value="vpg_migrate_coords">
            <p><button type="submit" class="button button-primary"><?php esc_html_e( 'Migrate coordinates now', 'vpg-v2' ); ?></button></p>
        </form>

        <hr style="margin:2.5rem 0">

        <h2><?php esc_html_e( 'Diagnose · what is actually in the database', 'vpg-v2' ); ?></h2>
        <p style="max-width:720px;line-height:1.6"><?php esc_html_e( 'Scans up to 5 sample posts per CPT, finds every meta key whose value looks like "lat, lng". Use this if the migration says it migrated 0 but the front-end still shows 0 pins · the report tells you the actual field name to look for.', 'vpg-v2' ); ?></p>
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <?php wp_nonce_field( 'vpg_diagnose_coords' ); ?>
            <input type="hidden" name="action" value="vpg_diagnose_coords">
            <p><button type="submit" class="button"><?php esc_html_e( 'Run diagnostic scan', 'vpg-v2' ); ?></button></p>
        </form>

        <?php $diag = get_transient( 'vpg_coord_diagnose' ); if ( $diag ) : ?>
            <h3 style="margin-top:2rem"><?php esc_html_e( 'Diagnostic report', 'vpg-v2' ); ?> <small style="opacity:.55;font-weight:normal">— refreshed once per 5 min</small></h3>
            <table class="widefat striped" style="margin-top:1rem">
                <thead><tr><th>CPT</th><th>Total</th><th>Sample w/ coords</th><th>Meta keys found</th></tr></thead>
                <tbody>
                    <?php foreach ( $diag as $cpt => $r ) :
                        $unique_keys = [];
                        foreach ( $r['sample_meta'] as $entry ) $unique_keys = array_merge( $unique_keys, array_keys( $entry['keys'] ) );
                        $unique_keys = array_unique( $unique_keys );
                    ?>
                    <tr>
                        <td><code><?php echo esc_html( $cpt ); ?></code></td>
                        <td><?php echo (int) $r['total']; ?></td>
                        <td><?php echo (int) $r['with_coords']; ?> / 5</td>
                        <td>
                            <?php if ( $unique_keys ) : ?>
                                <?php foreach ( $unique_keys as $k ) : ?>
                                    <code style="margin-right:.4rem;background:#fff;padding:2px 6px;border:1px solid #ccd0d4"><?php echo esc_html( $k ); ?></code>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <em style="color:#b32d2e">none found · neither v1 ACF nor v2 split fields are set</em>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p style="margin-top:1rem;font-size:13px;color:#646970"><?php esc_html_e( 'If a non-standard meta key shows up that the migration does not know about, paste the key name here and I will add it to the candidate list.', 'vpg-v2' ); ?></p>
        <?php endif; ?>

        <hr style="margin:2.5rem 0">

        <h2 id="vpg-derive"><?php esc_html_e( 'Derive districts from coordinates', 'vpg-v2' ); ?></h2>
        <p style="max-width:720px;line-height:1.6"><?php esc_html_e( 'For every location, studio, or shop that has lat/lng but no district set, this calls OSM Nominatim to reverse-geocode the pin and extract the 4-digit Wien postcode (e.g. "1010"). The district filter on the unified /locations/ map then has data to filter on. Nominatim is rate-limited to 1 req/sec so this batches 20 entries per click — repeat until 0 remain.', 'vpg-v2' ); ?></p>

        <?php $status = vpg_district_status(); $todo_total = array_sum( wp_list_pluck( $status, 'todo' ) ); ?>

        <table class="widefat striped" style="max-width:720px;margin-top:1rem">
            <thead><tr><th><?php esc_html_e( 'CPT', 'vpg-v2' ); ?></th><th><?php esc_html_e( 'District set', 'vpg-v2' ); ?></th><th><?php esc_html_e( 'Needs district', 'vpg-v2' ); ?></th></tr></thead>
            <tbody>
                <?php foreach ( $status as $cpt => $row ) : ?>
                    <tr>
                        <td><?php echo esc_html( $row['label'] ); ?> <code style="opacity:.6"><?php echo esc_html( $cpt ); ?></code></td>
                        <td><?php echo (int) $row['done']; ?></td>
                        <td><strong><?php echo (int) $row['todo']; ?></strong></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ( ! empty( $_GET['vpg_derive'] ) && $_GET['vpg_derive'] === 'done' ) : ?>
            <?php $last = get_transient( 'vpg_district_last_run' ); if ( $last ) : ?>
                <div class="notice notice-<?php echo $last['failed'] > 0 ? 'warning' : 'success'; ?>" style="margin-top:1.5rem">
                    <p><strong><?php
                        printf(
                            esc_html__( 'Batch done · %1$d filled · %2$d skipped · %3$d failed.', 'vpg-v2' ),
                            (int) $last['filled'], (int) $last['skipped'], (int) $last['failed']
                        );
                    ?></strong></p>
                    <?php if ( ! empty( $last['log'] ) ) : ?>
                        <details style="margin:.5rem 0 1rem">
                            <summary style="cursor:pointer"><?php esc_html_e( 'Show entries processed', 'vpg-v2' ); ?></summary>
                            <pre style="background:#fff;padding:1rem;border:1px solid #ccd0d4;max-height:260px;overflow:auto;font-size:12px;line-height:1.5"><?php
                                echo esc_html( implode( "\n", $last['log'] ) );
                            ?></pre>
                        </details>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:1.5rem">
            <?php wp_nonce_field( 'vpg_derive_districts' ); ?>
            <input type="hidden" name="action" value="vpg_derive_districts">
            <p>
                <button type="submit" class="button button-primary" <?php disabled( $todo_total, 0 ); ?>>
                    <?php
                    if ( $todo_total === 0 ) {
                        esc_html_e( 'All entries have a district ✓', 'vpg-v2' );
                    } else {
                        printf( esc_html__( 'Derive next %1$d · %2$d still to go', 'vpg-v2' ),
                            min( VPG_DISTRICT_BATCH_SIZE * 3, $todo_total ),
                            $todo_total
                        );
                    }
                    ?>
                </button>
                <span style="margin-left:1rem;color:#646970;font-size:13px"><?php esc_html_e( 'Runs ~22 s · stays well under PHP max_execution_time.', 'vpg-v2' ); ?></span>
            </p>
        </form>

        <?php
        // Detect legacy buggy format · early-version sprintf('1%03d') wrote 1001-1023 instead of 1010-1230.
        global $wpdb;
        $buggy_count = (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->postmeta}
             WHERE meta_key IN ('location_district', 'shop_district')
             AND meta_value REGEXP '^100[1-9]$|^101[1-9]$|^102[0-3]$'"
        );
        ?>
        <?php if ( $buggy_count > 0 ) : ?>
            <div class="notice notice-warning inline" style="margin-top:1.5rem;max-width:720px">
                <p style="margin:.6rem 0"><strong><?php esc_html_e( 'Buggy district format detected', 'vpg-v2' ); ?></strong> · <?php
                    printf( esc_html__( '%d entries have postcodes in the form 1001–1023 (wrong) instead of 1010–1230 (correct Wien format).', 'vpg-v2' ), $buggy_count );
                ?></p>
                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin:.6rem 0 1rem">
                    <?php wp_nonce_field( 'vpg_repair_districts' ); ?>
                    <input type="hidden" name="action" value="vpg_repair_districts">
                    <button type="submit" class="button"><?php esc_html_e( 'Repair district format', 'vpg-v2' ); ?></button>
                    <span style="margin-left:1rem;color:#646970;font-size:13px"><?php esc_html_e( 'Re-derives from coords · uses Nominatim cache · runs in seconds.', 'vpg-v2' ); ?></span>
                </form>
            </div>
        <?php endif; ?>

        <?php if ( ! empty( $_GET['vpg_repair'] ) && $_GET['vpg_repair'] === 'done' ) : ?>
            <?php $rep = get_transient( 'vpg_district_last_repair' ); ?>
            <div class="notice notice-success" style="margin-top:1rem"><p><?php
                printf( esc_html__( 'Format repaired · %d entries re-derived from coordinates.', 'vpg-v2' ), (int) $rep );
            ?></p></div>
        <?php endif; ?>

        <?php if ( $complete ) : ?>
            <h2 style="margin-top:2rem"><?php esc_html_e( 'Status', 'vpg-v2' ); ?></h2>
            <ul style="line-height:1.8">
                <li>✓ <?php esc_html_e( 'Pages created with templates assigned', 'vpg-v2' ); ?></li>
                <li>✓ <?php esc_html_e( 'Primary menu + 4 footer menus wired to theme locations', 'vpg-v2' ); ?></li>
                <li>✓ <?php esc_html_e( 'Rewrite rules flushed', 'vpg-v2' ); ?></li>
            </ul>
        <?php endif; ?>
    </div>
    <?php
}

/* ─── The actual wizard ────────────────────────────────────────── */
function vpg_run_setup_wizard() {

    // 1 · pages
    $pages = vpg_seed_pages();

    // 2 · menus (always wipe + rebuild for clean state)
    vpg_seed_menus( $pages );

    // 3 · flush rewrites so CPT archives work
    flush_rewrite_rules( false );

    update_option( 'vpg_setup_complete', time() );
}

/* ─── Page seeder ──────────────────────────────────────────────── */
function vpg_seed_pages() {

    $defs = [
        'about'      => [ 'About',         'templates/page-about.php',      'A quiet magazine for photographers.' ],
        'contact'    => [ 'Contact',       'templates/page-contact.php',    'Send a message — editorial enquiries, submissions, member support.' ],
        'join'       => [ 'Join VPG',      'templates/page-join.php',       'Become a member · €60 per year.' ],
        'login'      => [ 'Login',         'templates/page-login.php',      'Member login.' ],
        'dashboard'  => [ 'Dashboard',     'templates/page-dashboard.php',  'Your VPG dashboard.' ],
        'submit'     => [ 'Submit',        'templates/page-submit.php',     'Members can submit a location, studio, shop or review.' ],
        'imprint'    => [ 'Imprint',       'templates/page-imprint.php',    'Imprint per § 5 ECG / § 24 MedienG.' ],
        'privacy'    => [ 'Privacy',       'templates/page-privacy.php',    'How VPG handles personal data and submissions.' ],
        'terms'      => [ 'Terms',         'templates/page-terms.php',      'Membership terms, content rights, editorial policy.' ],
        'membership' => [ 'Membership',    'templates/page-membership.php', 'Reader · Member · Sustaining member.' ],
        'faq'        => [ 'FAQ',           'templates/page-faq.php',        'Frequently asked questions.' ],
        'team'       => [ 'Team',          'templates/page-team.php',       'Editorial team.' ],
        'newsletter' => [ 'Newsletter',    'templates/page-newsletter.php', 'One-line email when a new issue ships.' ],
        'archive'    => [ 'Archive',       'templates/page-archive.php',    'Full archive of everything published.' ],
        'map'        => [ 'Map',           'templates/page-map-guide.php',  'The full Leaflet location map.' ],
    ];

    $created = [];

    foreach ( $defs as $slug => $row ) {
        list( $title, $template, $sample ) = $row;

        $existing = get_page_by_path( $slug );

        if ( $existing ) {
            // Make sure the template is set (in case theme was reactivated)
            update_post_meta( $existing->ID, '_wp_page_template', $template );
            $created[ $slug ] = $existing->ID;
            continue;
        }

        $id = wp_insert_post( [
            'post_type'    => 'page',
            'post_title'   => $title,
            'post_name'    => $slug,
            'post_status'  => 'publish',
            'post_content' => '<!-- wp:paragraph --><p>' . esc_html( $sample ) . '</p><!-- /wp:paragraph -->',
            'meta_input'   => [ '_wp_page_template' => $template ],
        ] );

        if ( $id && ! is_wp_error( $id ) ) {
            $created[ $slug ] = $id;
        }
    }

    return $created;
}

/* ─── Menu seeder ──────────────────────────────────────────────── */
function vpg_seed_menus( $pages ) {

    // CPT archive URLs · Studios + Shops are filter-views of the unified /locations/ map
    $loc_base = get_post_type_archive_link( 'vpg_location' ) ?: home_url('/locations/');
    $cpt_urls = [
        'magazine'     => [ 'Magazine',     get_post_type_archive_link( 'vpg_magazine' )  ?: home_url('/magazine/')  ],
        'events'       => [ 'Events',       get_post_type_archive_link( 'vpg_event' )     ?: home_url('/events/')    ],
        'locations'    => [ 'The Map',      $loc_base ],
        'studios'      => [ 'Studios',      add_query_arg( 'type', 'studio', $loc_base ) ],
        'shops'        => [ 'Shops',        add_query_arg( 'type', 'shop',   $loc_base ) ],
        'buying-guide' => [ 'Buying guide', get_post_type_archive_link( 'vpg_review' )    ?: home_url('/buying-guide/') ],
        'tutorials'    => [ 'Tutorials',    get_post_type_archive_link( 'vpg_tutorial' )  ?: home_url('/tutorials/') ],
    ];

    $menus = [
        'vpg-primary' => [
            'name'     => 'VPG · Primary',
            'location' => 'primary',
            // Studios + Shops live as filter views of "The Map" — keep primary nav lean.
            'items'    => [
                [ 'custom', 'Home', home_url('/') ],
                [ 'cpt',    'Magazine',     'magazine' ],
                [ 'cpt',    'Events',       'events' ],
                [ 'cpt',    'The Map',      'locations' ],
                [ 'cpt',    'Buying guide', 'buying-guide' ],
                [ 'page',   'About',        'about' ],
            ],
        ],
        'vpg-explore' => [
            'name'     => 'VPG · Footer Explore',
            'location' => 'footer-col-1',
            'items'    => [
                [ 'cpt', 'Magazine',     'magazine' ],
                [ 'cpt', 'Events',       'events' ],
                [ 'cpt', 'Locations',    'locations' ],
                [ 'cpt', 'Studios',      'studios' ],
                [ 'cpt', 'Shops',        'shops' ],
                [ 'cpt', 'Buying guide', 'buying-guide' ],
                [ 'cpt', 'Tutorials',    'tutorials' ],
            ],
        ],
        'vpg-members' => [
            'name'     => 'VPG · Footer Members',
            'location' => 'footer-col-2',
            'items'    => [
                [ 'page', 'Join VPG',   'join' ],
                [ 'page', 'Login',      'login' ],
                [ 'page', 'Dashboard',  'dashboard' ],
                [ 'page', 'Submit',     'submit' ],
                [ 'page', 'Newsletter', 'newsletter' ],
            ],
        ],
        'vpg-community' => [
            'name'     => 'VPG · Footer Community',
            'location' => 'footer-col-3',
            'items'    => [
                [ 'page', 'About',      'about' ],
                [ 'page', 'Team',       'team' ],
                [ 'page', 'Contact',    'contact' ],
                [ 'page', 'FAQ',        'faq' ],
                [ 'page', 'Membership', 'membership' ],
            ],
        ],
        'vpg-legal' => [
            'name'     => 'VPG · Legal',
            'location' => 'legal',
            'items'    => [
                [ 'page', 'Imprint', 'imprint' ],
                [ 'page', 'Privacy', 'privacy' ],
                [ 'page', 'Terms',   'terms' ],
            ],
        ],
    ];

    $locations = get_theme_mod( 'nav_menu_locations', [] );
    if ( ! is_array( $locations ) ) $locations = [];

    foreach ( $menus as $slug => $cfg ) {

        // Wipe any prior menu of this slug for a clean rebuild
        $existing = wp_get_nav_menu_object( $slug );
        if ( $existing ) wp_delete_nav_menu( $existing->term_id );

        $menu_id = wp_create_nav_menu( $cfg['name'] );
        if ( is_wp_error( $menu_id ) || ! $menu_id ) continue;

        // Some hosts return the menu by name not slug; ensure we got an int ID
        if ( is_object( $menu_id ) ) $menu_id = $menu_id->term_id;

        // Force the slug so the next wipe finds it
        wp_update_term( $menu_id, 'nav_menu', [ 'slug' => $slug ] );

        // Populate items
        $position = 0;
        foreach ( $cfg['items'] as $item ) {
            list( $kind, $label, $key ) = $item;
            $position++;

            $args = [
                'menu-item-status'   => 'publish',
                'menu-item-position' => $position,
                'menu-item-title'    => $label,
            ];

            if ( $kind === 'page' ) {
                $page_id = isset( $pages[ $key ] ) ? (int) $pages[ $key ] : 0;
                if ( ! $page_id ) {
                    $p = get_page_by_path( $key );
                    if ( $p ) $page_id = $p->ID;
                }
                if ( ! $page_id ) continue;
                $args['menu-item-type']      = 'post_type';
                $args['menu-item-object']    = 'page';
                $args['menu-item-object-id'] = $page_id;
            } elseif ( $kind === 'cpt' ) {
                $url = $cpt_urls[ $key ][1] ?? '';
                if ( ! $url ) continue;
                $args['menu-item-type'] = 'custom';
                $args['menu-item-url']  = $url;
            } else {
                $args['menu-item-type'] = 'custom';
                $args['menu-item-url']  = $key;
            }

            wp_update_nav_menu_item( $menu_id, 0, $args );
        }

        // Assign to theme location
        $locations[ $cfg['location'] ] = $menu_id;
    }

    set_theme_mod( 'nav_menu_locations', $locations );
}

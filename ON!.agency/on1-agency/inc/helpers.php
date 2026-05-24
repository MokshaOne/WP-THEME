<?php
/**
 * on1.agency · v4 · helpers.php
 *
 * Data accessors. All theme settings live in wp_options:
 *   on1_identity (array)        : name, tagline, location, status, email, ...
 *   on1_hero (array)            : manifesto block, headline words, tag line
 *   on1_stats (array of rows)   : [{num, label, accent_in_num}]
 *   on1_services (array of rows): [{name, name_em, desc, price, url}]
 *   on1_pricing (array of rows) : [{name, em, price, period, features[], featured, url}]
 *   on1_quotes (array of rows)  : [{text, author, role, initials}]
 *   on1_faq (array of rows)     : [{q, a}]
 *   on1_brief (array)           : lede, sub, primary_url, secondary_url
 *   on1_footer (array)          : about, contact[], sister[], legal[], colophon
 *   on1_show (array of toggles) : show_stats, show_pricing, show_quotes, show_faq, ...
 *
 * Repeaters store as serialized array. Static settings store individually.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }


/* ─── Generic option accessor with default fallback ──────── */
function on1_opt( $key, $default = '' ) {
    return get_option( 'on1_' . $key, $default );
}

/* ─── Echoes a string with <em>…</em> allowed (and only that) */
function on1_em( $str ) {
    return wp_kses( $str, [ 'em' => [], 'br' => [] ] );
}


/* ═══════════════════════════════════════════════════════════
   IDENTITY
   ═══════════════════════════════════════════════════════════ */
function on1_get_identity() {
    $defaults = [
        'name'     => 'on1.agency',
        'tagline'  => 'A one-person digital atelier in Wien.',
        'location' => 'Wien, Austria',
        'status'   => 'Booking Q3',
        'email'    => 'hello@on1.agency',
    ];
    $saved = get_option( 'on1_identity', [] );
    return wp_parse_args( is_array( $saved ) ? $saved : [], $defaults );
}


/* ═══════════════════════════════════════════════════════════
   HERO
   ═══════════════════════════════════════════════════════════ */
function on1_get_hero() {
    $defaults = [
        'manifesto_lead'    => '" At on1.agency, we believe quiet design is the most generous kind of work.',
        'manifesto_body'    => 'A one-person digital atelier in Wien — building editorial WordPress sites and design systems for studios who want to look like themselves, not like a template. Strategy, design, build, and the senior pair of eyes you need at the end."',
        'signoff_name'      => 'Nishuthan Raveenthiran',
        'signoff_role'      => 'Founder &amp; Sole Practitioner',
        'headline_line_1'   => 'where <em>craft</em>',
        'headline_line_2'   => 'meets code<em>.</em>',
        'tag_line'          => 'Booking <em>Q3</em> — slots open',
        'cta_label'         => 'Start a project',
        'cta_url'           => '#brief',
    ];
    return wp_parse_args( get_option( 'on1_hero', [] ) ?: [], $defaults );
}


/* ═══════════════════════════════════════════════════════════
   STATS
   ═══════════════════════════════════════════════════════════ */
function on1_get_stats() {
    $saved = get_option( 'on1_stats', null );
    if ( is_array( $saved ) && ! empty( $saved ) ) return $saved;
    return [
        [ 'num' => '<span class="accent">14</span>+', 'label' => "Sites shipped\nsince 2018" ],
        [ 'num' => '3<small> mo</small>',             'label' => "Avg. engagement\nlength" ],
        [ 'num' => '<span class="accent">72</span>h', 'label' => "Reply\nwindow" ],
        [ 'num' => '1',                                'label' => "Person\nresponsible" ],
    ];
}


/* ═══════════════════════════════════════════════════════════
   SERVICES (practice list)
   ═══════════════════════════════════════════════════════════ */
function on1_get_services() {
    $saved = get_option( 'on1_services', null );
    if ( is_array( $saved ) && ! empty( $saved ) ) return $saved;
    return [
        [
            'name' => 'Editorial <em>WordPress</em> themes',
            'desc' => 'Built like a printed monograph. ACF Pro-driven, type-led, custom CPTs. No page builders, no plugin sprawl. From <strong>€ 6.800</strong>.',
            'url'  => '#brief',
        ],
        [
            'name' => 'Design <em>systems</em> &amp; tokens',
            'desc' => 'Palette, type, components, documentation. For when the next site, deck and pitch should look like the same studio. From <strong>€ 3.200</strong>.',
            'url'  => '#brief',
        ],
        [
            'name' => 'Identity <em>refresh</em> for studios',
            'desc' => 'Wordmark, voice, site, email. Quiet identity work for studios who want to look more like themselves and less like everyone else. From <strong>€ 4.500</strong>.',
            'url'  => '#brief',
        ],
        [
            'name' => '<em>Audits</em> &amp; second opinions',
            'desc' => 'Senior eyes on a site that\'s almost-but-not-quite right. Two weeks, written report, no bullshit. Often becomes the bigger engagement. From <strong>€ 1.800</strong>.',
            'url'  => '#brief',
        ],
    ];
}


/* ═══════════════════════════════════════════════════════════
   PRICING TIERS
   ═══════════════════════════════════════════════════════════ */
function on1_get_pricing() {
    $saved = get_option( 'on1_pricing', null );
    if ( is_array( $saved ) && ! empty( $saved ) ) return $saved;
    return [
        [
            'name'     => 'Audit <em>plan</em>',
            'price'    => '€1.8K',
            'period'   => '/ 2 wks',
            'features' => [ 'Code review', 'A11y &amp; perf pass', 'Written report', '1 follow-up call' ],
            'featured' => '0',
            'cta'      => 'Choose',
            'url'      => '#brief',
        ],
        [
            'name'     => 'Premium <em>plan</em>',
            'price'    => '€6.8K',
            'period'   => '/ project',
            'features' => [ 'Custom WordPress build', 'ACF Pro field design', '3 CPTs · 1 admin panel', '2 rounds of refinement', 'Launch &amp; handoff' ],
            'featured' => '1',
            'cta'      => 'Choose',
            'url'      => '#brief',
        ],
        [
            'name'     => 'Identity <em>refresh</em>',
            'price'    => '€4.5K',
            'period'   => '/ project',
            'features' => [ 'Wordmark + variants', 'Voice &amp; tone', 'Light site refresh', 'Email signature' ],
            'featured' => '0',
            'cta'      => 'Choose',
            'url'      => '#brief',
        ],
        [
            'name'     => 'Custom <em>plan</em>',
            'price'    => '€<em>—</em>',
            'period'   => '/ depends',
            'features' => [ 'Design system + tokens', 'Multi-site build', 'Long engagements (3—6 mo)', 'Retainer available' ],
            'featured' => '0',
            'cta'      => 'Get quote',
            'url'      => '#brief',
        ],
    ];
}


/* ═══════════════════════════════════════════════════════════
   TESTIMONIALS / QUOTES
   ═══════════════════════════════════════════════════════════ */
function on1_get_quotes() {
    $saved = get_option( 'on1_quotes', null );
    if ( is_array( $saved ) && ! empty( $saved ) ) return $saved;
    return [
        [ 'text' => 'Nishuthan reads a brief like an editor — finds the sentence we didn\'t write but meant to. Then builds exactly that.', 'author' => 'Maria R.', 'role' => 'Director · Studio Wien', 'initials' => 'MR' ],
        [ 'text' => 'The kind of designer who pushes back when you ask for the wrong thing. Saved us from ourselves twice in one project.', 'author' => 'Thomas K.', 'role' => 'Founder · Atelier Neun', 'initials' => 'TK' ],
        [ 'text' => 'Our site went from "a WordPress site" to "the studio\'s site". Three years later, we still haven\'t touched it.', 'author' => 'Julia F.', 'role' => 'Photographer · VPG', 'initials' => 'JF' ],
    ];
}


/* ═══════════════════════════════════════════════════════════
   FAQ
   ═══════════════════════════════════════════════════════════ */
function on1_get_faq() {
    $saved = get_option( 'on1_faq', null );
    if ( is_array( $saved ) && ! empty( $saved ) ) return $saved;
    return [
        [ 'q' => 'How long does a typical project take?',                'a' => 'Most full WordPress builds take 6–10 weeks. An audit is 2 weeks. An identity refresh is 3–5. The biggest variable is your decision speed — I move as fast as the slowest reviewer.' ],
        [ 'q' => 'Do you work with page builders like Elementor or Divi?', 'a' => 'No. Every site is custom WordPress with ACF Pro and hand-written templates. Page builders are great for the use case they were designed for; my work isn\'t that use case.' ],
        [ 'q' => 'Will my team be able to update the site?',              'a' => 'Yes. The admin is custom-designed to match the front-end. Plain language, sensible defaults, no plugin spaghetti. I write the admin like I\'m the one who\'ll use it.' ],
        [ 'q' => 'What if I already have a designer?',                   'a' => 'Even better. I build from your designs, or pair with your designer through the process. Bring me a Figma file and a brief; I\'ll write back with what\'s possible.' ],
        [ 'q' => 'Where are you based, and do you travel?',               'a' => 'Vienna. Most work is remote. I\'ll come to your studio for the kickoff and the launch if it\'s in Europe and useful — usually it is.' ],
        [ 'q' => 'Do you offer maintenance after launch?',                'a' => 'A 90-day soft-warranty is included on every build (bug fixes, small adjustments). After that, I offer a monthly retainer or pay-as-you-go.' ],
    ];
}


/* ═══════════════════════════════════════════════════════════
   BRIEF CTA
   ═══════════════════════════════════════════════════════════ */
function on1_get_brief() {
    $defaults = [
        'eyebrow'         => 'Send a signal',
        'lede_line_1'     => 'tell us what you\'re',
        'lede_line_2'     => '<em>actually</em> doing.',
        'sub'             => 'Five questions. One reply within 72 hours. No automated funnels, no discovery call before we\'ve read your email. Just a conversation.',
        'primary_label'   => 'Send brief',
        'primary_url'     => 'mailto:hello@on1.agency',
        'secondary_label' => 'Book a call',
        'secondary_url'   => '#',
    ];
    return wp_parse_args( get_option( 'on1_brief', [] ) ?: [], $defaults );
}


/* ═══════════════════════════════════════════════════════════
   FOOTER
   ═══════════════════════════════════════════════════════════ */
function on1_get_footer() {
    $defaults = [
        'brand_html'   => 'on<em>1</em>.agency <sup>®</sup>',
        'about_html'   => 'A one-person digital atelier.<br>Wien, Austria.<br><em>Working slowly, on purpose.</em>',
        'colophon'     => 'Set in <em>Instrument Serif</em> &amp; Inter',
        'copyright'    => '© MMXXVI · on1.agency',
        'build_note'   => 'Built in Wien · v4',
    ];
    return wp_parse_args( get_option( 'on1_footer', [] ) ?: [], $defaults );
}


/* ═══════════════════════════════════════════════════════════
   DISPLAY TOGGLES
   ═══════════════════════════════════════════════════════════ */
function on1_show( $key ) {
    $toggles = get_option( 'on1_show', [] );
    if ( ! is_array( $toggles ) ) $toggles = [];
    return ! isset( $toggles[ $key ] ) ? true : (bool) $toggles[ $key ];
}


/* ═══════════════════════════════════════════════════════════
   SITES I MANAGE (the multi-site dashboard)
   ═══════════════════════════════════════════════════════════ */
function on1_get_sites() {
    $saved = get_option( 'on1_sites', null );
    if ( is_array( $saved ) && ! empty( $saved ) ) return $saved;
    return [
        [ 'name' => 'on1.agency',           'url' => 'https://on1.agency',           'admin' => 'https://on1.agency/wp-admin/',           'note' => 'This site' ],
        [ 'name' => 'Kavithai (jrpoetry)',  'url' => 'https://jrpoetry.com',         'admin' => 'https://jrpoetry.com/wp-admin/',         'note' => 'Tamil poetry · v2.1 Relief' ],
        [ 'name' => 'M1O Hub',              'url' => 'https://m1o.at',               'admin' => 'https://m1o.at/wp-admin/',               'note' => 'Link hub · v2.0' ],
        [ 'name' => 'Raveenthiran',         'url' => 'https://raveenthiran.com',     'admin' => 'https://raveenthiran.com/wp-admin/',     'note' => 'Photography portfolio' ],
        [ 'name' => 'Vienna Photo Group',   'url' => 'https://viennaphotogroup.com', 'admin' => 'https://viennaphotogroup.com/wp-admin/', 'note' => 'Photographer community' ],
    ];
}


/* ═══════════════════════════════════════════════════════════
   CASE STUDIES (CPT-backed)
   ═══════════════════════════════════════════════════════════ */
function on1_get_featured_project() {
    $q = new WP_Query( [
        'post_type'      => 'project',
        'posts_per_page' => 1,
        'meta_query'     => [ [ 'key' => '_on1_featured', 'value' => '1' ] ],
        'no_found_rows'  => true,
    ] );
    if ( $q->have_posts() ) return $q->posts[0];
    // Fallback to latest project.
    $q = new WP_Query( [ 'post_type' => 'project', 'posts_per_page' => 1, 'no_found_rows' => true ] );
    return $q->have_posts() ? $q->posts[0] : null;
}

function on1_get_other_projects( $exclude_id = 0, $limit = 3 ) {
    $args = [
        'post_type'      => 'project',
        'posts_per_page' => $limit,
        'no_found_rows'  => true,
    ];
    if ( $exclude_id ) $args['post__not_in'] = [ (int) $exclude_id ];
    $q = new WP_Query( $args );
    return $q->posts;
}

function on1_project_meta( $post_id, $key, $default = '' ) {
    $v = get_post_meta( $post_id, '_on1_' . $key, true );
    return $v !== '' ? $v : $default;
}

<?php
/**
 * VPG v2 — Content gating.
 *
 * Three ways to gate content by login state · pick what fits the context:
 *
 *  1. SHORTCODES (Gutenberg / Classic editor)
 *     [vpg-members]…content…[/vpg-members]   · logged-in only
 *     [vpg-public]…content…[/vpg-public]     · logged-out only · inverse
 *     Attributes:
 *       cta="Open the map"           · button label inside the placeholder
 *       message="Members see this."  · explainer line
 *       blur="1"                     · show blurred preview instead of placeholder
 *
 *  2. BLOCK SHORTHAND (raw HTML in a Custom HTML block)
 *     <div class="vpg-gate vpg-gate--members">…</div>
 *     <div class="vpg-gate vpg-gate--public">…</div>
 *     CSS hides the right side depending on body.logged-in / body.logged-out.
 *
 *  3. PHP HELPERS (templates)
 *     vpg_gate( 'members', function () { ... } )   · runs callback if logged-in
 *     vpg_gate_text( $html, $opts )                · returns gated HTML
 *
 * Future-proof · `tier="member"` / `tier="sustaining"` attributes are accepted
 * but currently treated the same as plain logged-in (no real tier system yet).
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ════════════════════════════════════════════════════════════════ */
/*  Shortcodes                                                       */
/* ════════════════════════════════════════════════════════════════ */
add_shortcode( 'vpg-members', function ( $atts, $content = '' ) {
    return vpg_gate_render( 'members', $content, (array) $atts );
} );
add_shortcode( 'vpg-public', function ( $atts, $content = '' ) {
    return vpg_gate_render( 'public', $content, (array) $atts );
} );

/* ════════════════════════════════════════════════════════════════ */
/*  Core render                                                      */
/* ════════════════════════════════════════════════════════════════ */
function vpg_gate_render( $mode, $content, $atts = [] ) {
    $defaults = [
        'cta'     => __( 'Log in', 'vpg-v2' ),
        'cta_url' => '',
        'message' => '',
        'blur'    => '0',
        'tier'    => '',  // reserved · ignored for now
    ];
    $a = shortcode_atts( $defaults, $atts );

    $logged_in = is_user_logged_in();
    $show_real = ( $mode === 'members' ) ? $logged_in : ! $logged_in;

    // tier="supporter|sustaining" narrows a members gate further
    if ( $mode === 'members' && $show_real && ! empty( $a['tier'] ) ) {
        $show_real = (bool) apply_filters( 'vpg_gate_tier_check', true, $a['tier'] );
        if ( ! $show_real && ! $a['message'] ) {
            $a['message'] = __( 'A supporter-tier extra · supporter tiers open later.', 'vpg-v2' );
        }
    }

    if ( $show_real ) {
        return do_shortcode( $content );
    }

    // Members-mode + logged-out → show placeholder.
    if ( $mode === 'members' ) {
        $message = $a['message'] ?: __( 'Members only · log in or join to read this.', 'vpg-v2' );
        $cta_url = $a['cta_url'] ?: wp_login_url( get_permalink() );
        $blur    = ! empty( $a['blur'] ) && $a['blur'] !== '0';

        ob_start();
        ?>
        <div class="vpg-gate vpg-gate--blocked<?php echo $blur ? ' vpg-gate--blur' : ''; ?>">
            <?php if ( $blur ) : ?>
                <div class="vpg-gate__blurred" aria-hidden="true"><?php echo do_shortcode( $content ); ?></div>
            <?php endif; ?>
            <div class="vpg-gate__overlay">
                <p class="vpg-caps">— <?php esc_html_e( 'Members only', 'vpg-v2' ); ?></p>
                <p class="vpg-gate__msg"><?php echo esc_html( $message ); ?></p>
                <p class="vpg-gate__actions">
                    <a class="vpg-btn vpg-btn--sm" href="<?php echo esc_url( $cta_url ); ?>"><?php echo esc_html( $a['cta'] ); ?> →</a>
                    <a class="vpg-btn vpg-btn--ghost vpg-btn--sm" href="<?php echo esc_url( home_url('/join/') ); ?>"><?php esc_html_e( 'Join free', 'vpg-v2' ); ?></a>
                </p>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    // Public-mode + logged-in → just hide silently.
    return '';
}

/* ════════════════════════════════════════════════════════════════ */
/*  Template helpers                                                 */
/* ════════════════════════════════════════════════════════════════ */
/** Run callback when the gate is satisfied · echoes its output. */
function vpg_gate( $mode, callable $cb ) {
    $logged_in = is_user_logged_in();
    $ok = ( $mode === 'members' ) ? $logged_in : ! $logged_in;
    if ( $ok ) $cb();
}

/** Return gated HTML · same logic as the shortcode, callable from templates. */
function vpg_gate_text( $mode, $html, $opts = [] ) {
    return vpg_gate_render( $mode, $html, $opts );
}

/* ════════════════════════════════════════════════════════════════ */
/*  body_class · simple CSS-only gating via .vpg-gate--members /     */
/*  .vpg-gate--public divs (handled by base.css rule below)          */
/* ════════════════════════════════════════════════════════════════ */
add_filter( 'body_class', function ( $c ) {
    if ( is_user_logged_in() ) $c[] = 'vpg-logged-in';
    else                       $c[] = 'vpg-logged-out';
    return $c;
} );

/* ════════════════════════════════════════════════════════════════ */
/*  Inline styles · keeps gating self-contained in one file          */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'wp_head', function () {
    ?>
    <style id="vpg-gating">
        /* DOM-level shortcuts · for users who prefer raw HTML over shortcodes */
        body.vpg-logged-out .vpg-gate--members:not(.vpg-gate--blocked) { display: none; }
        body.vpg-logged-in  .vpg-gate--public                          { display: none; }

        /* Placeholder UI shown when a [vpg-members] block is blocked */
        .vpg-gate--blocked {
            position: relative;
            border: 1px dashed var(--vpg-rule-strong);
            border-radius: var(--vpg-radius);
            padding: var(--vpg-sp-7) var(--vpg-sp-6);
            background: var(--vpg-card-deep);
            overflow: hidden;
            min-height: 220px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .vpg-gate__blurred {
            position: absolute; inset: 0;
            filter: blur(14px) saturate(0.85) brightness(0.95);
            pointer-events: none;
            user-select: none;
            opacity: 0.55;
        }
        .vpg-gate__overlay {
            position: relative; z-index: 1;
            text-align: center;
            max-width: 36ch;
        }
        .vpg-gate__msg {
            font-family: var(--vpg-font-italic);
            font-style: italic;
            font-size: var(--vpg-fs-lg);
            color: var(--vpg-ink-mid);
            margin: var(--vpg-sp-3) 0 var(--vpg-sp-5);
        }
        .vpg-gate__actions { display: flex; gap: var(--vpg-sp-3); justify-content: center; flex-wrap: wrap; }
    </style>
    <?php
} );

/* ════════════════════════════════════════════════════════════════ */
/*  Supporter tiers · feature flags, ready before payment exists     */
/*                                                                   */
/*  A member's tier lives in `_vpg_tier` (member|supporter|          */
/*  sustaining) — free members are 'member'. Which tier unlocks      */
/*  which feature is one option map; nothing free ever moves behind  */
/*  a flag (enforced: 'member' always passes features mapped to it). */
/* ════════════════════════════════════════════════════════════════ */
function vpg_member_tier( $uid = null ) {
    $uid = $uid ?: get_current_user_id();
    if ( ! $uid ) return '';
    $tier = get_user_meta( $uid, '_vpg_tier', true );
    return in_array( $tier, [ 'member', 'supporter', 'sustaining' ], true ) ? $tier : 'member';
}

function vpg_tier_features() {
    $defaults = [
        // feature        => minimum tier
        'submissions'     => 'member',
        'pdf_download'    => 'member',
        'portfolio'       => 'member',
        'print_run'       => 'supporter',
        'early_access'    => 'supporter',
        'colophon_credit' => 'sustaining',
    ];
    $stored = get_option( 'vpg_tier_features', [] );
    return array_merge( $defaults, is_array( $stored ) ? $stored : [] );
}

function vpg_tier_can( $feature, $uid = null ) {
    $rank = [ 'member' => 1, 'supporter' => 2, 'sustaining' => 3 ];
    $map  = vpg_tier_features();
    $need = $map[ $feature ] ?? 'member';
    if ( ! is_user_logged_in() && $uid === null ) return false;
    return ( $rank[ vpg_member_tier( $uid ) ] ?? 0 ) >= ( $rank[ $need ] ?? 1 );
}

/* The [vpg-members tier="supporter"] attribute now really gates */
add_filter( 'vpg_gate_tier_check', function ( $ok, $tier ) {
    if ( ! $tier || $tier === 'member' ) return $ok;
    $rank = [ 'member' => 1, 'supporter' => 2, 'sustaining' => 3 ];
    return ( $rank[ vpg_member_tier() ] ?? 0 ) >= ( $rank[ $tier ] ?? 1 );
}, 10, 2 );

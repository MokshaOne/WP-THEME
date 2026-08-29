<?php
/** Template Name: Privacy */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
$id = vpg_identity();
?>
<main id="vpg-main">

<section class="g-phero">
    <div class="g-wrap">
        <p class="g-kicker" style="margin-bottom:16px">● Legal</p>
        <h1 class="g-display g-phero__title">Privacy <em>policy</em>.</h1>
        <p class="g-lede g-phero__lede">How VPG handles personal data, cookies, submissions and analytics · written plainly · GDPR-aligned.</p>
    </div>
</section>

<section class="g-section g-section--tight">
    <div class="g-wrap">
        <div class="g-prose">

            <p style="font-style:italic;color:var(--g-mid)">Last updated: <?php echo esc_html( wp_date( 'F Y' ) ); ?> · plain-language summary, then the detail below.</p>

            <h2>The short version</h2>
            <ul>
                <li>We collect only what we need to run the platform · your name, email, and submission content if you become a member.</li>
                <li>We do not sell, rent, or trade your data. Ever.</li>
                <li>No tracking pixels, no Google Analytics, no Facebook Pixel, no advertising cookies.</li>
                <li>You can request a copy of your data, or its deletion, at any time · email <a href="mailto:<?php echo esc_attr( $id['email'] ); ?>"><?php echo esc_html( $id['email'] ); ?></a>.</li>
            </ul>

            <h2>What we collect</h2>
            <h3>Visitors (no signup)</h3>
            <p>Standard server logs only · IP address, browser, page requested, timestamp · retained 14 days for security analysis, then deleted. No analytics cookies are set.</p>

            <h3>Members</h3>
            <p>When you create a member account we store · your name, email, hashed password (bcrypt), tier (Reader / Member / Sustaining), payment status, member-since date, and any content you submit. Stored in our self-hosted WordPress database · not shared with third parties.</p>

            <h3>Submissions</h3>
            <p>Locations, studios, shops and reviews you submit include the metadata you provide (title, description, coordinates, photos). This becomes part of the public index once approved. You retain copyright and can request removal at any time.</p>

            <h2>Cookies</h2>
            <p>Two cookies, both first-party · WordPress session cookie when logged in · a small preference cookie (e.g. last-viewed magazine issue, dark-mode preference). No advertising, retargeting, or tracking cookies are set.</p>

            <h2>Newsletter</h2>
            <p>If you subscribe to the issue-alert newsletter, we store your email until you unsubscribe. One-click unsubscribe in every email. We never send marketing, promotions, partner offers or surveys.</p>

            <h2>Map &amp; location data</h2>
            <p>The Leaflet map loads tiles from <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a>. Address search uses <a href="https://nominatim.openstreetmap.org/" target="_blank" rel="noopener">OpenStreetMap Nominatim</a>. These third parties may log map-tile and search requests per their own privacy terms. We do not pass user-identifying data to them.</p>

            <h2>Third-party services</h2>
            <ul>
                <li><strong>Google Fonts</strong> · self-hosted for privacy. No requests to fonts.google.com.</li>
                <li><strong>Payment processing</strong> · membership payments via SEPA / PayPal. We see the transaction record only · no card data ever touches our servers.</li>
            </ul>

            <h2>Your rights (GDPR)</h2>
            <p>You have the right to · access your data · correct it · delete it · export it · object to processing · lodge a complaint with the Austrian Data Protection Authority (<a href="https://www.dsb.gv.at/" target="_blank" rel="noopener">dsb.gv.at</a>). All requests via email · we respond within 30 days.</p>

            <h2>Contact</h2>
            <p>Data protection enquiries: <a href="mailto:<?php echo esc_attr( $id['email'] ); ?>"><?php echo esc_html( $id['email'] ); ?></a></p>

        </div>
    </div>
</section>

</main>
<?php get_footer();

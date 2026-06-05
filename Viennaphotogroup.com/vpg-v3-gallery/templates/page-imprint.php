<?php
/** Template Name: Imprint */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
$id = vpg_identity();
?>
<main id="vpg-main">

<section class="g-phero">
    <div class="g-wrap">
        <p class="g-kicker" style="margin-bottom:16px">● Legal</p>
        <h1 class="g-display g-phero__title"><em>Imprint</em>.</h1>
        <p class="g-lede g-phero__lede">Information per § 5 ECG &amp; § 24 MedienG (Austria).</p>
    </div>
</section>

<section class="g-section g-section--tight">
    <div class="g-wrap">
        <div class="g-prose">

            <h2>Diensteanbieter / Media owner</h2>
            <p>
                <strong>Vienna Photo Group</strong><br>
                A co-production of Raveenthiran &times; on1.agency<br>
                <?php echo esc_html( $id['location'] ); ?><br>
                Austria
            </p>

            <h2>Contact</h2>
            <p>
                Email: <a href="mailto:<?php echo esc_attr( $id['email'] ); ?>"><?php echo esc_html( $id['email'] ); ?></a><br>
                Web: <a href="<?php echo esc_url( home_url('/') ); ?>"><?php echo esc_html( str_replace( [ 'https://', 'http://' ], '', home_url('/') ) ); ?></a>
            </p>

            <h2>Editorial responsibility</h2>
            <p>
                Editorial direction and content responsibility per § 25 MedienG lies with the editorial circle of Vienna Photo Group. Members rotate through the four editorial chairs · see <a href="<?php echo esc_url( home_url('/team/') ); ?>">/team/</a>.
            </p>

            <h2>Purpose of publication</h2>
            <p>
                Vienna Photo Group is a decentralised, data-driven reference hub for photographers shooting Wien. The platform indexes shooting locations, studios and shops · publishes a monthly editorial magazine · and operates without advertising, sponsors or commercial profit. Membership fees cover hosting and editorial honorariums.
            </p>

            <h2>Disclaimer</h2>
            <p>
                External links are checked at the time of submission. We accept no liability for the content of linked third-party websites · their operators are solely responsible. Location data is submitted by members in good faith but is not verified against current property access or legal status · photograph at your own discretion and respect private property.
            </p>

            <h2>Copyright</h2>
            <p>
                All editorial content, layouts, code and original imagery on this site is copyright Vienna Photo Group and its members. Submitted content remains the copyright of the submitter · published under a non-exclusive licence to VPG. Reproduction requires written permission · contact the editorial circle.
            </p>

            <h2>Online dispute resolution</h2>
            <p>
                The European Commission provides an online dispute-resolution platform: <a href="https://ec.europa.eu/consumers/odr/" target="_blank" rel="noopener">ec.europa.eu/consumers/odr/</a>. We are not obliged and not willing to participate in dispute settlement proceedings before a consumer arbitration board.
            </p>

        </div>
    </div>
</section>

</main>
<?php get_footer();

<?php
/**
 * on1.agency · v4 · footer.php
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$f  = on1_get_footer();
$id = on1_get_identity();
?>

<footer class="footer">
    <div class="footer__in">
        <h2 class="footer__brand"><?php echo on1_em( $f['brand_html'] ); ?></h2>

        <div class="footer__grid">
            <div class="footer__col">
                <h6>Studio</h6>
                <p><?php echo wp_kses( $f['about_html'], [ 'br' => [], 'em' => [], 'strong' => [], 'a' => [ 'href' => [], 'target' => [], 'rel' => [] ] ] ); ?></p>
            </div>
            <div class="footer__col">
                <h6>Contact</h6>
                <ul role="list">
                    <?php if ( $id['email'] ) : ?>
                        <li><a href="mailto:<?php echo esc_attr( $id['email'] ); ?>"><?php echo esc_html( $id['email'] ); ?></a></li>
                    <?php endif; ?>
                    <li><a href="#brief">Send a brief</a></li>
                    <li><a href="#faq">FAQ</a></li>
                </ul>
            </div>
            <div class="footer__col">
                <h6>Sister projects</h6>
                <ul role="list">
                    <li><a href="https://m1o.at">m1o.at</a></li>
                    <li><a href="https://raveenthiran.com">raveenthiran.com</a></li>
                    <li><a href="https://viennaphotogroup.com">VPG</a></li>
                </ul>
            </div>
            <div class="footer__col">
                <h6>Legal</h6>
                <ul role="list">
                    <?php
                    if ( has_nav_menu( 'legal' ) ) {
                        wp_nav_menu( [ 'theme_location' => 'legal', 'container' => false, 'items_wrap' => '%3$s' ] );
                    } else {
                        echo '<li><a href="#">Imprint</a></li><li><a href="#">Privacy</a></li><li><a href="#">Terms</a></li>';
                    }
                    ?>
                </ul>
            </div>
        </div>

        <div class="footer__final">
            <span><?php echo on1_em( $f['colophon'] ); ?></span>
            <span class="center"><?php echo esc_html( $f['copyright'] ); ?></span>
            <span class="right"><?php echo on1_em( $f['build_note'] ); ?></span>
        </div>
    </div>
</footer>

<?php if ( on1_show( 'picker' ) ) : ?>
<!-- Design preset picker · sticky bottom bar. Toggle in On1 Hub → Display. -->
<div id="on1-design-picker" class="design-picker" aria-label="Design preset picker">
    <div class="design-picker__inner">
        <span class="design-picker__label">Site Style</span>
        <div class="design-picker__list" role="group" aria-label="Choose a design preset"></div>
    </div>
</div>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>

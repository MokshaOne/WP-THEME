<?php
/**
 * M1O Hub · footer / colophon
 */
if ( ! defined( 'ABSPATH' ) ) exit; ?>

<footer class="footer" role="contentinfo">
    <div class="footer__in">
        <div class="footer__copy">
            <span class="signal">●</span> M1O.HUB · v<?php echo esc_html( M1O_VERSION ); ?> · BUILT IN VIENNA · © MMXXVI<br>
            <span style="color: var(--faint); letter-spacing: 0.18em; text-transform: uppercase; margin-top: 0.3rem; display: inline-block;">
                Created by
                <a href="https://on1.agency" target="_blank" rel="noopener" style="color: var(--signal); border-bottom: 1px solid var(--rule); padding-bottom: 1px;">on1.agency</a>
            </span>
        </div>
        <div class="footer__end">
            End of transmission <span class="blink">_</span>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>

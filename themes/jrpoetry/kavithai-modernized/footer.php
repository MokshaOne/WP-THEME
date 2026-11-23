<?php
/**
 * Footer — colophon with typeface credits, copyright, agency line.
 */
if ( ! defined( 'ABSPATH' ) ) exit; ?>

</main>

<footer class="footer">
    <?php echo kv_relief( 'க', 'footer' ); ?>

    <div class="footer__in">
        <p class="footer__tagline"><?php echo esc_html( kv_tagline() ); ?></p>

        <div class="footer__seal"><?php echo kv_seal( 'lg' ); ?></div>

        <ul class="footer__links" role="list">
            <?php
            wp_nav_menu( [
                'theme_location' => 'footer',
                'container'      => false,
                'items_wrap'     => '%3$s',
                'fallback_cb'    => function() {
                    $links = [
                        home_url( '/' )                                  => 'முகப்பு',
                        get_post_type_archive_link( 'kavithai_gedicht' ) => 'கவிதைகள்',
                        get_post_type_archive_link( 'kavithai_nul' )     => 'நூல்கள்',
                        home_url( '/ennai-parri/' )                      => 'என்னை பற்றி',
                        home_url( '/thodarbu/' )                         => 'தொடர்பு',
                    ];
                    foreach ( $links as $url => $label ) {
                        echo '<li><a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a></li>';
                    }
                },
            ] );
            ?>
        </ul>

        <div class="colophon">
            <div class="colophon__block">
                <strong>Set in</strong>
                <em>Noto Serif Tamil</em>, drawn by Pria Ravichandran<br>
                for Google Fonts, &amp; <em>EB Garamond</em>,<br>
                drawn by Georg Duffner after Robert Granjon's<br>
                sixteenth-century specimens of MDLXXXII.
            </div>
            <div class="colophon__block colophon__center">
                <span class="roman">MMXXVI</span>
                © <span class="ta"><?php echo esc_html( kv_name() ); ?></span><br>
                <em>All rights reserved.</em>
            </div>
            <div class="colophon__block colophon__credit">
                <strong>Designed for the screen</strong>
                <a href="https://on1.agency" target="_blank" rel="noopener">
                    On1 <span class="ampersand">&amp;</span> Agency
                </a>
                <div style="margin-top:0.7rem; font-size:0.7rem; letter-spacing:0.04em; font-style:italic; color:var(--ink-faint);">
                    A small studio in pursuit of quiet things.
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Site strip — fixed bottom band, Latin-caps quick nav -->
<div class="site-strip" role="contentinfo">
    <span class="site-strip__brand">Pavalarmani <span style="color:var(--ink-mute); margin:0 0.3em;">·</span> Vetrichelvi</span>
    <span class="site-strip__sep">·</span>
    <ul class="site-strip__nav">
        <li><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a></li>
        <li><a href="<?php echo esc_url( home_url( '/kalavarisai/' ) ); ?>">Poems</a></li>
        <li><a href="<?php echo esc_url( get_post_type_archive_link( 'kavithai_nul' ) ); ?>">Books</a></li>
        <li><a href="<?php echo esc_url( home_url( '/ennai-parri/' ) ); ?>">About</a></li>
        <li><a href="<?php echo esc_url( home_url( '/thodarbu/' ) ); ?>">Contact</a></li>
    </ul>
</div>

<?php wp_footer(); ?>
</body>
</html>

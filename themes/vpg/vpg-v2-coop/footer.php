<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<footer class="vpg-footer" role="contentinfo">
    <div class="vpg-footer__in">

        <h2 class="vpg-footer__brand">
            Vienna Photo <em>Group</em><span style="color:var(--vpg-accent)">.</span>
        </h2>

        <div class="vpg-footer__cols">

            <div class="vpg-footer__col">
                <h6><?php esc_html_e( 'Explore', 'vpg-v2' ); ?></h6>
                <?php
                if ( has_nav_menu( 'footer-col-1' ) ) {
                    wp_nav_menu( [ 'theme_location' => 'footer-col-1', 'container' => false, 'items_wrap' => '<ul>%3$s</ul>', 'depth' => 1 ] );
                } else {
                    echo '<ul>';
                    foreach ( [
                        __( 'Magazine',      'vpg-v2' ) => get_post_type_archive_link( 'vpg_magazine' ),
                        __( 'Events',        'vpg-v2' ) => get_post_type_archive_link( 'vpg_event' ),
                        __( 'Location map',  'vpg-v2' ) => get_post_type_archive_link( 'vpg_location' ),
                        __( 'Studios',       'vpg-v2' ) => get_post_type_archive_link( 'vpg_studio' ),
                        __( 'Shops',         'vpg-v2' ) => get_post_type_archive_link( 'vpg_shop' ),
                        __( 'Buying guide',  'vpg-v2' ) => get_post_type_archive_link( 'vpg_review' ),
                        __( 'Tutorials',     'vpg-v2' ) => get_post_type_archive_link( 'vpg_tutorial' ),
                    ] as $label => $url ) {
                        if ( $url ) echo '<li><a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a></li>';
                    }
                    echo '</ul>';
                }
                ?>
            </div>

            <div class="vpg-footer__col">
                <h6><?php esc_html_e( 'Members', 'vpg-v2' ); ?></h6>
                <?php
                if ( has_nav_menu( 'footer-col-2' ) ) {
                    wp_nav_menu( [ 'theme_location' => 'footer-col-2', 'container' => false, 'items_wrap' => '<ul>%3$s</ul>', 'depth' => 1 ] );
                } else {
                    echo '<ul>';
                    echo '<li><a href="' . esc_url( home_url('/join/') )      . '">' . esc_html__( 'Join VPG',  'vpg-v2' ) . '</a></li>';
                    echo '<li><a href="' . esc_url( wp_login_url() )           . '">' . esc_html__( 'Login',     'vpg-v2' ) . '</a></li>';
                    echo '<li><a href="' . esc_url( home_url('/dashboard/') ) . '">' . esc_html__( 'Dashboard', 'vpg-v2' ) . '</a></li>';
                    echo '<li><a href="' . esc_url( home_url('/submit/') )    . '">' . esc_html__( 'Submit',    'vpg-v2' ) . '</a></li>';
                    echo '</ul>';
                }
                ?>
            </div>

            <div class="vpg-footer__col">
                <h6><?php esc_html_e( 'Community', 'vpg-v2' ); ?></h6>
                <?php
                if ( has_nav_menu( 'footer-col-3' ) ) {
                    wp_nav_menu( [ 'theme_location' => 'footer-col-3', 'container' => false, 'items_wrap' => '<ul>%3$s</ul>', 'depth' => 1 ] );
                } else {
                    echo '<ul>';
                    echo '<li><a href="' . esc_url( home_url('/about/') )   . '">' . esc_html__( 'About VPG',  'vpg-v2' ) . '</a></li>';
                    echo '<li><a href="' . esc_url( home_url('/team/') )    . '">' . esc_html__( 'Editorial',  'vpg-v2' ) . '</a></li>';
                    echo '<li><a href="' . esc_url( home_url('/contact/') ) . '">' . esc_html__( 'Contact',    'vpg-v2' ) . '</a></li>';
                    echo '<li><a href="' . esc_url( home_url('/faq/') )     . '">' . esc_html__( 'FAQ',        'vpg-v2' ) . '</a></li>';
                    echo '</ul>';
                }
                ?>
            </div>

            <div class="vpg-footer__col">
                <h6><?php esc_html_e( 'Legal', 'vpg-v2' ); ?></h6>
                <?php
                if ( has_nav_menu( 'legal' ) ) {
                    wp_nav_menu( [ 'theme_location' => 'legal', 'container' => false, 'items_wrap' => '<ul>%3$s</ul>' ] );
                } else {
                    echo '<ul>';
                    echo '<li><a href="' . esc_url( home_url('/imprint/') ) . '">' . esc_html__( 'Imprint', 'vpg-v2' ) . '</a></li>';
                    echo '<li><a href="' . esc_url( home_url('/privacy/') ) . '">' . esc_html__( 'Privacy', 'vpg-v2' ) . '</a></li>';
                    echo '<li><a href="' . esc_url( home_url('/terms/') )   . '">' . esc_html__( 'Terms',   'vpg-v2' ) . '</a></li>';
                    echo '</ul>';
                }
                ?>
            </div>
        </div>

        <div class="vpg-footer__legal">
            <span>© <?php echo esc_html( wp_date( 'Y' ) ); ?> Vienna Photo Group · v2.0</span>
            <span><?php esc_html_e( 'A co-production · Raveenthiran × on1.agency', 'vpg-v2' ); ?></span>
            <span><a href="https://on1.agency" target="_blank" rel="noopener">on1.agency</a></span>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>

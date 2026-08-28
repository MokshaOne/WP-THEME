<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<footer class="g-foot" role="contentinfo">
    <div class="g-wrap">

        <div class="g-foot__top">
            <p class="g-foot__brand"><?php bloginfo( 'name' ); ?><b>.</b></p>
            <div style="text-align:right">
                <p class="g-kicker g-kicker--faint" style="margin-bottom:14px;color:rgba(255,255,255,.5)"><?php esc_html_e( 'A co-production · Raveenthiran × on1.agency', 'vpg-v2' ); ?></p>
                <a class="g-btn g-btn--on-dark" href="<?php echo esc_url( home_url( '/join/' ) ); ?>"><?php esc_html_e( 'Join VPG', 'vpg-v2' ); ?> <span class="a">→</span></a>
            </div>
        </div>

        <div class="g-foot__cols">

            <div>
                <h6><?php esc_html_e( 'Explore', 'vpg-v2' ); ?></h6>
                <?php
                if ( has_nav_menu( 'footer-col-1' ) ) {
                    wp_nav_menu( [ 'theme_location' => 'footer-col-1', 'container' => false, 'items_wrap' => '<ul>%3$s</ul>', 'depth' => 1 ] );
                } else {
                    echo '<ul>';
                    foreach ( [
                        __( 'Magazine',      'vpg-v2' ) => get_post_type_archive_link( 'vpg_magazine' ),
                        __( 'The Map',       'vpg-v2' ) => get_post_type_archive_link( 'vpg_location' ),
                        __( 'Events',        'vpg-v2' ) => get_post_type_archive_link( 'vpg_event' ),
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

            <div>
                <h6><?php esc_html_e( 'Members', 'vpg-v2' ); ?></h6>
                <?php
                if ( has_nav_menu( 'footer-col-2' ) ) {
                    wp_nav_menu( [ 'theme_location' => 'footer-col-2', 'container' => false, 'items_wrap' => '<ul>%3$s</ul>', 'depth' => 1 ] );
                } else {
                    echo '<ul>';
                    echo '<li><a href="' . esc_url( home_url( '/join/' ) )      . '">' . esc_html__( 'Join VPG',  'vpg-v2' ) . '</a></li>';
                    echo '<li><a href="' . esc_url( wp_login_url() )            . '">' . esc_html__( 'Login',     'vpg-v2' ) . '</a></li>';
                    echo '<li><a href="' . esc_url( home_url( '/dashboard/' ) ) . '">' . esc_html__( 'Dashboard', 'vpg-v2' ) . '</a></li>';
                    echo '<li><a href="' . esc_url( home_url( '/submit/' ) )    . '">' . esc_html__( 'Submit',    'vpg-v2' ) . '</a></li>';
                    echo '</ul>';
                }
                ?>
            </div>

            <div>
                <h6><?php esc_html_e( 'Community', 'vpg-v2' ); ?></h6>
                <?php
                if ( has_nav_menu( 'footer-col-3' ) ) {
                    wp_nav_menu( [ 'theme_location' => 'footer-col-3', 'container' => false, 'items_wrap' => '<ul>%3$s</ul>', 'depth' => 1 ] );
                } else {
                    echo '<ul>';
                    echo '<li><a href="' . esc_url( home_url( '/about/' ) )   . '">' . esc_html__( 'About',   'vpg-v2' ) . '</a></li>';
                    echo '<li><a href="' . esc_url( home_url( '/team/' ) )    . '">' . esc_html__( 'Team',    'vpg-v2' ) . '</a></li>';
                    echo '<li><a href="' . esc_url( home_url( '/contact/' ) ) . '">' . esc_html__( 'Contact', 'vpg-v2' ) . '</a></li>';
                    echo '<li><a href="' . esc_url( home_url( '/faq/' ) )     . '">' . esc_html__( 'FAQ',     'vpg-v2' ) . '</a></li>';
                    echo '</ul>';
                }
                ?>
            </div>

            <div>
                <h6><?php esc_html_e( 'Legal', 'vpg-v2' ); ?></h6>
                <?php
                if ( has_nav_menu( 'legal' ) ) {
                    wp_nav_menu( [ 'theme_location' => 'legal', 'container' => false, 'items_wrap' => '<ul>%3$s</ul>' ] );
                } else {
                    echo '<ul>';
                    echo '<li><a href="' . esc_url( home_url( '/imprint/' ) ) . '">' . esc_html__( 'Imprint', 'vpg-v2' ) . '</a></li>';
                    echo '<li><a href="' . esc_url( home_url( '/privacy/' ) ) . '">' . esc_html__( 'Privacy', 'vpg-v2' ) . '</a></li>';
                    echo '<li><a href="' . esc_url( home_url( '/terms/' ) )   . '">' . esc_html__( 'Terms',   'vpg-v2' ) . '</a></li>';
                    echo '</ul>';
                }
                ?>
            </div>
        </div>

        <div class="g-foot__legal">
            <span>© <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?> · v3.0</span>
            <span><?php esc_html_e( 'A member-run photography magazine · Wien', 'vpg-v2' ); ?></span>
            <span><a href="https://on1.agency" target="_blank" rel="noopener">on1.agency</a></span>
        </div>
    </div>
</footer>

<?php if ( ! is_user_logged_in() && ! is_page( [ 'join', 'login' ] ) ) : ?>
<!-- Mobile sticky join bar · logged-out visitors only -->
<div class="g-joinbar" role="complementary" aria-label="<?php esc_attr_e( 'Membership', 'vpg-v2' ); ?>">
    <span class="g-joinbar__t"><?php esc_html_e( 'Free membership · two minutes', 'vpg-v2' ); ?></span>
    <a class="g-joinbar__btn" href="<?php echo esc_url( home_url( '/join/' ) ); ?>"><?php esc_html_e( 'Join free', 'vpg-v2' ); ?> &#8594;</a>
</div>
<style>
    .g-joinbar { display: none; }
    @media (max-width: 760px) {
        .g-joinbar {
            position: fixed; left: 0; right: 0; bottom: 0; z-index: 60;
            display: flex; align-items: center; justify-content: space-between; gap: 12px;
            background: var(--g-ink, #0B0B0B); color: #fff;
            padding: 12px 16px calc(12px + env(safe-area-inset-bottom, 0px));
            border-top: 1px solid rgba(255,255,255,.14);
        }
        .g-joinbar__t { font-size: 11px; font-weight: 600; letter-spacing: .12em; text-transform: uppercase; color: rgba(255,255,255,.75); }
        .g-joinbar__btn {
            flex: none; background: var(--g-red, #E5341F); color: #fff;
            font-size: 12px; font-weight: 800; letter-spacing: .14em; text-transform: uppercase;
            padding: 11px 18px; text-decoration: none;
        }
        body { padding-bottom: 64px; }
    }
</style>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>

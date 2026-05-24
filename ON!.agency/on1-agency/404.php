<?php
/**
 * 404.php
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header(); ?>
<main class="on1-404" id="main-content">
    <div class="on1-container">
        <div class="on1-404__inner">
            <p class="on1-404__num" aria-hidden="true" data-text="404">404</p>
            <h1 class="on1-404__title">Page not found</h1>
            <p class="on1-404__sub">The page you're looking for doesn't exist or has moved.</p>
            <a href="<?php echo esc_url(home_url('/')); ?>" class="on1-magnetic">← Back to Home</a>
        </div>
    </div>
</main>
<?php get_footer();

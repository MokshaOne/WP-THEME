<?php
/**
 * 404 — Not Found
 */
get_header(); ?>

<section style="display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 100vh; text-align: center; padding: var(--re-pad);">
    <div class="re-eyebrow" style="margin-bottom: 16px;">404</div>
    <h1 class="re-display--lg re-text-3d re-reveal" style="margin-bottom: 16px;">
        <?php echo re_emphasize_title(__('Page Not Found', 'r-e')); ?>
    </h1>
    <p class="re-prose" style="margin-bottom: 32px; text-align: center;">
        <?php esc_html_e('The page you are looking for does not exist or has been moved.', 'r-e'); ?>
    </p>
    <div style="display: flex; gap: 12px;">
        <div class="re-magnetic">
            <a href="<?php echo esc_url(home_url('/')); ?>" class="re-btn re-btn--primary"><?php esc_html_e('Back Home', 'r-e'); ?></a>
        </div>
        <div class="re-magnetic">
            <a href="<?php echo esc_url(home_url('/contact')); ?>" class="re-btn"><?php esc_html_e('Contact', 'r-e'); ?></a>
        </div>
    </div>
</section>

<?php get_footer();

</main><!-- /.re-main -->

<!-- Booking Modal (Elementor Pro Popup fallback) -->
<div id="re-booking" class="re-modal" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e('Book a session', 'r-e'); ?>">
    <div class="re-modal__backdrop"></div>
    <div class="re-modal__body">
        <button data-modal-close aria-label="<?php esc_attr_e('Close', 'r-e'); ?>" style="position: absolute; top: 16px; right: 16px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M18 6L6 18M6 6l12 12"/></svg>
        </button>
        <div class="re-eyebrow" style="margin-bottom: 16px;"><?php esc_html_e('Booking', 'r-e'); ?></div>
        <h3 class="re-display--sm" style="margin-bottom: 24px;"><?php echo esc_html(re_opt('re_book_label', __('Book a Session', 'r-e'))); ?></h3>

        <?php
        $shortcode = re_opt('re_latepoint_shortcode');
        if ($shortcode && shortcode_exists('latepoint_book_form')) {
            echo do_shortcode($shortcode);
        } else { ?>
            <form class="re-form" data-ajax="re_contact">
                <input type="text" name="name" class="re-input" placeholder="<?php esc_attr_e('Your name', 'r-e'); ?>" required>
                <input type="email" name="email" class="re-input" placeholder="<?php esc_attr_e('Email', 'r-e'); ?>" required>
                <textarea name="message" class="re-input" placeholder="<?php esc_attr_e('Tell us about your project…', 'r-e'); ?>" required></textarea>
                <button type="submit" class="re-btn re-btn--primary re-btn--block"><?php esc_html_e('Send Inquiry', 'r-e'); ?></button>
            </form>
        <?php } ?>
    </div>
</div>

<!-- Inquiry FAB Modal -->
<div id="re-inquiry" class="re-modal" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e('Quick inquiry', 'r-e'); ?>">
    <div class="re-modal__backdrop"></div>
    <div class="re-modal__body">
        <button data-modal-close aria-label="<?php esc_attr_e('Close', 'r-e'); ?>" style="position: absolute; top: 16px; right: 16px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M18 6L6 18M6 6l12 12"/></svg>
        </button>
        <div class="re-eyebrow" style="margin-bottom: 16px;"><?php esc_html_e('Quick Inquiry', 'r-e'); ?></div>
        <form class="re-form" data-ajax="re_contact">
            <input type="text" name="name" class="re-input" placeholder="<?php esc_attr_e('Name', 'r-e'); ?>" required>
            <input type="email" name="email" class="re-input" placeholder="<?php esc_attr_e('Email', 'r-e'); ?>" required>
            <textarea name="message" class="re-input" placeholder="<?php esc_attr_e('Brief description…', 'r-e'); ?>" rows="3" required></textarea>
            <div style="position: absolute; left: -9999px;"><input type="text" name="re_hp" tabindex="-1" autocomplete="off"></div>
            <button type="submit" class="re-btn re-btn--primary re-btn--block"><?php esc_html_e('Send', 'r-e'); ?></button>
        </form>
    </div>
</div>

<!-- Lightbox -->
<div id="re-lightbox" class="re-lightbox" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e('Image viewer', 'r-e'); ?>">
    <div class="re-lightbox__backdrop"></div>
    <div class="re-reg re-reg--tl"></div>
    <div class="re-reg re-reg--tr"></div>
    <div class="re-reg re-reg--bl"></div>
    <div class="re-reg re-reg--br"></div>
    <img class="re-lightbox__image" src="" alt="">
    <div class="re-lightbox__meta"></div>
    <button data-lightbox-close aria-label="<?php esc_attr_e('Close', 'r-e'); ?>" style="position: absolute; top: 20px; right: 20px;">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M18 6L6 18M6 6l12 12"/></svg>
    </button>
    <button data-lightbox-prev aria-label="<?php esc_attr_e('Previous', 'r-e'); ?>" style="position: absolute; left: 20px; top: 50%; transform: translateY(-50%);">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M15 19l-7-7 7-7"/></svg>
    </button>
    <button data-lightbox-next aria-label="<?php esc_attr_e('Next', 'r-e'); ?>" style="position: absolute; right: 20px; top: 50%; transform: translateY(-50%);">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 5l7 7-7 7"/></svg>
    </button>
</div>

<!-- Cookie Notice -->
<?php if (!is_admin()): ?>
<div class="re-cookie" role="alert">
    <div class="re-cookie__text">
        <?php printf(
            esc_html__('We use cookies for a better experience. %s', 'r-e'),
            '<a href="' . esc_url(home_url('/datenschutz')) . '">' . esc_html__('Privacy Policy', 'r-e') . '</a>'
        ); ?>
    </div>
    <div class="re-cookie__actions">
        <button class="re-btn" data-cookie-accept="1"><?php esc_html_e('Accept', 'r-e'); ?></button>
        <button class="re-btn re-btn--ghost" data-cookie-decline="1"><?php esc_html_e('Decline', 'r-e'); ?></button>
    </div>
</div>
<?php endif; ?>

<!-- Mobile Tab Bar -->
<nav class="re-tabbar" role="navigation" aria-label="<?php esc_attr_e('Quick navigation', 'r-e'); ?>">
    <a href="<?php echo esc_url(home_url('/')); ?>" class="re-tabbar__item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1h-2z"/></svg>
        <span>Home</span>
    </a>
    <a href="<?php echo esc_url(home_url('/portfolio')); ?>" class="re-tabbar__item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        <span>Work</span>
    </a>
    <a href="#" data-modal="re-booking" class="re-tabbar__item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        <span>Book</span>
    </a>
    <a href="<?php echo esc_url(home_url('/contact')); ?>" class="re-tabbar__item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
        <span>Hello</span>
    </a>
</nav>

<?php wp_footer(); ?>
</body>
</html>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="<?php echo esc_attr(re_opt('re_color_bg', '#0B0C10')); ?>">
    <?php
    $mode = re_opt('re_color_mode', 'dark');
    $bg   = re_opt('re_color_bg', '#0B0C10');
    $ink  = re_opt('re_color_ink', '#F2EFE9');
    $accent = re_opt('re_accent', '#F2A03D');

    // Convert hex to RGB
    $bg_rgb  = implode(',', sscanf($bg, "#%02x%02x%02x"));
    $ink_rgb = implode(',', sscanf($ink, "#%02x%02x%02x"));
    $acc_rgb = implode(',', sscanf($accent, "#%02x%02x%02x"));
    ?>
    <style>
        :root {
            --re-bg: <?php echo esc_attr($bg); ?>;
            --re-ink: <?php echo esc_attr($ink); ?>;
            --re-ink-rgb: <?php echo esc_attr($ink_rgb); ?>;
            --re-amber: <?php echo esc_attr($accent); ?>;
            --re-amber-rgb: <?php echo esc_attr($acc_rgb); ?>;
        }
    </style>
    <?php wp_head(); ?>
</head>
<body <?php body_class('re-mode-' . esc_attr($mode)); ?>>
<?php wp_body_open(); ?>

<a class="skip-link" href="#main"><?php esc_html_e('Skip to content', 'r-e'); ?></a>

<!-- WebGL Particle Canvas -->
<canvas id="re-particles" aria-hidden="true"></canvas>

<!-- Page Transition Wipe -->
<div class="re-wipe" aria-hidden="true"></div>

<!-- Film Grain -->
<?php if (re_opt('re_fx_grain', true)): ?>
    <div class="re-grain" aria-hidden="true"></div>
<?php endif; ?>

<!-- Topbar -->
<header class="re-topbar" role="banner">
    <a href="<?php echo esc_url(home_url('/')); ?>" class="re-logo" aria-label="<?php bloginfo('name'); ?>">
        <?php echo esc_html(re_opt('re_logo_text', get_bloginfo('name'))); ?>
        <?php if ($sub = re_opt('re_logo_sub')): ?>
            <span class="re-logo__sub"><?php echo esc_html($sub); ?></span>
        <?php endif; ?>
    </a>

    <?php if (has_nav_menu('primary')): ?>
        <nav class="re-nav" role="navigation" aria-label="<?php esc_attr_e('Primary', 'r-e'); ?>">
            <?php
            wp_nav_menu([
                'theme_location' => 'primary',
                'container'      => false,
                'items_wrap'     => '%3$s',
                'depth'          => 1,
            ]);
            ?>
        </nav>
    <?php endif; ?>

    <div style="display: flex; align-items: center; gap: 16px;">
        <?php if ($avail = re_opt('re_availability')): ?>
            <div class="re-avail">
                <span class="re-avail__dot"></span>
                <?php echo esc_html($avail); ?>
            </div>
        <?php endif; ?>

        <div class="re-magnetic">
            <a href="<?php echo esc_url(home_url('/booking')); ?>" class="re-btn re-btn--primary">
                <?php echo esc_html(re_opt('re_book_label', __('Book a Session', 'r-e'))); ?>
            </a>
        </div>

        <!-- Mobile hamburger -->
        <button class="re-icon-btn" data-sidebar-open aria-label="<?php esc_attr_e('Menu', 'r-e'); ?>" style="display: none;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M3 12h18M3 6h18M3 18h18"/>
            </svg>
        </button>
    </div>
</header>

<!-- Mobile Sidebar -->
<div class="re-sidebar-backdrop" aria-hidden="true"></div>
<aside class="re-sidebar" role="navigation" aria-label="<?php esc_attr_e('Mobile menu', 'r-e'); ?>">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px;">
        <span class="re-logo"><?php echo esc_html(re_opt('re_logo_text', get_bloginfo('name'))); ?></span>
        <button class="re-icon-btn" data-sidebar-close aria-label="<?php esc_attr_e('Close', 'r-e'); ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M18 6L6 18M6 6l12 12"/>
            </svg>
        </button>
    </div>
    <?php if (has_nav_menu('mobile')): ?>
        <?php wp_nav_menu([
            'theme_location' => 'mobile',
            'container'      => false,
            'menu_class'     => 're-sidebar-nav',
            'depth'          => 1,
        ]); ?>
    <?php elseif (has_nav_menu('primary')): ?>
        <?php wp_nav_menu([
            'theme_location' => 'primary',
            'container'      => false,
            'menu_class'     => 're-sidebar-nav',
            'depth'          => 1,
        ]); ?>
    <?php endif; ?>

    <div style="margin-top: auto; padding-top: 32px;">
        <?php if ($email = re_opt('re_email')): ?>
            <a href="mailto:<?php echo esc_attr($email); ?>" class="re-chrome" style="display: block; margin-bottom: 8px;"><?php echo esc_html($email); ?></a>
        <?php endif; ?>
        <div style="display: flex; gap: 12px; margin-top: 16px;">
            <?php if ($ig = re_opt('re_instagram')): ?>
                <a href="<?php echo esc_url($ig); ?>" class="re-icon-btn re-icon-btn--fill" target="_blank" rel="noopener" aria-label="Instagram">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                </a>
            <?php endif; ?>
            <?php if ($be = re_opt('re_behance')): ?>
                <a href="<?php echo esc_url($be); ?>" class="re-icon-btn re-icon-btn--fill" target="_blank" rel="noopener" aria-label="Behance">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M22 7h-7V5h7v2zm1.726 10c-.442 1.297-2.029 3-5.101 3-3.074 0-5.564-1.729-5.564-5.675 0-3.91 2.325-5.92 5.466-5.92 3.082 0 4.964 1.782 5.375 4.426.078.506.109 1.188.095 2.14H15.97c.13 1.211.994 2.062 2.442 2.062.86 0 1.579-.332 2.038-.987h3.276zM15.97 12.594h5.09c-.073-1.199-.879-1.991-2.494-1.991-1.508 0-2.39.86-2.596 1.991zM9.104 4H.5v16h8.745c2.773 0 4.582-1.648 4.582-4.127 0-1.861-.916-3.104-2.693-3.611 1.335-.588 2.198-1.752 2.198-3.26C13.332 5.751 11.594 4 9.104 4zM4.5 7.375h3.633c1.199 0 2.023.677 2.023 1.773 0 1.165-.936 1.852-2.148 1.852H4.5V7.375zm4.07 9.25H4.5v-4h4.108c1.416 0 2.312.771 2.312 2.007 0 1.26-.924 1.993-2.35 1.993z"/></svg>
                </a>
            <?php endif; ?>
        </div>
    </div>
</aside>

<main id="main" class="re-main" role="main">

<?php
/**
 * Elementor Canvas Template
 * Full-width template for Elementor-built pages
 */

if (!defined('ABSPATH')) exit;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <?php
    $bg = re_opt('re_color_bg', '#0B0C10');
    $ink = re_opt('re_color_ink', '#F2EFE9');
    $accent = re_opt('re_accent', '#F2A03D');
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
<body <?php body_class('re-mode-' . esc_attr(re_opt('re_color_mode', 'dark'))); ?>>
<?php wp_body_open(); ?>

<canvas id="re-particles" aria-hidden="true"></canvas>
<div class="re-wipe" aria-hidden="true"></div>
<?php if (re_opt('re_fx_grain', true)): ?>
    <div class="re-grain" aria-hidden="true"></div>
<?php endif; ?>

<?php
while (have_posts()) {
    the_post();
    the_content();
}
?>

<?php wp_footer(); ?>
</body>
</html>

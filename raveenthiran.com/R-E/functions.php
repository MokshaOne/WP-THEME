<?php
/**
 * R-E — Raveenthiran Elementor Edition
 *
 * Elementor-powered reimagining of Catalogue Noir with 3D elements,
 * WebGL effects, and advanced Elementor integration.
 */

defined('ABSPATH') || exit;

define('RE_VERSION', '1.0.0');
define('RE_DIR', get_template_directory());
define('RE_URI', get_template_directory_uri());

/* ─── Theme Setup ─────────────────────────────────────────── */

add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo', [
        'height'      => 60,
        'width'       => 200,
        'flex-width'  => true,
        'flex-height' => true,
    ]);
    add_theme_support('html5', ['search-form', 'comment-form', 'gallery', 'caption', 'style', 'script']);
    add_theme_support('editor-styles');
    add_theme_support('wp-block-styles');
    add_theme_support('responsive-embeds');
    add_theme_support('align-wide');

    register_nav_menus([
        'primary'  => __('Primary Menu', 'r-e'),
        'footer'   => __('Footer Menu', 'r-e'),
        'mobile'   => __('Mobile Tab Bar', 'r-e'),
    ]);

    add_image_size('re-hero', 2400, 1600, true);
    add_image_size('re-hero-2x', 3600, 2400, true);
    add_image_size('re-card', 1200, 1600, true);
    add_image_size('re-card-2x', 1800, 2400, true);
    add_image_size('re-thumb', 600, 800, true);
    add_image_size('re-og', 1200, 630, true);
});

/* ─── Elementor Support ───────────────────────────────────── */

add_action('after_setup_theme', function () {
    add_theme_support('elementor');
    add_theme_support('elementor-default-template');
});

add_action('elementor/theme/register_locations', function ($manager) {
    $manager->register_all_core_locations();
});

/* ─── Enqueue Assets ──────────────────────────────────────── */

add_action('wp_enqueue_scripts', function () {
    // Fonts
    wp_enqueue_style('re-fonts-preconnect', 'https://fonts.googleapis.com', [], null);
    wp_enqueue_style('re-fonts', 'https://fonts.googleapis.com/css2?family=Inter+Tight:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,500;1,600;1,700&family=JetBrains+Mono:wght@400;500&display=swap', [], null);

    // Theme stylesheet
    wp_enqueue_style('re-theme', RE_URI . '/assets/css/theme.css', [], RE_VERSION);

    // 3D & Special Effects stylesheet
    wp_enqueue_style('re-3d-effects', RE_URI . '/assets/css/3d-effects.css', ['re-theme'], RE_VERSION);

    // Elementor overrides
    if (did_action('elementor/loaded')) {
        wp_enqueue_style('re-elementor', RE_URI . '/assets/css/elementor-overrides.css', ['re-theme'], RE_VERSION);
    }

    // Three.js for WebGL particle effects
    wp_enqueue_script('three-js', 'https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js', [], 'r128', true);

    // GSAP for advanced animations
    wp_enqueue_script('gsap', 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js', [], '3.12.5', true);
    wp_enqueue_script('gsap-scroll', 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js', ['gsap'], '3.12.5', true);

    // Vanilla Tilt for 3D card effects
    wp_enqueue_script('vanilla-tilt', 'https://cdnjs.cloudflare.com/ajax/libs/vanilla-tilt/1.8.1/vanilla-tilt.min.js', [], '1.8.1', true);

    // Theme scripts
    wp_enqueue_script('re-3d', RE_URI . '/assets/js/3d-effects.js', ['three-js'], RE_VERSION, true);
    wp_enqueue_script('re-theme', RE_URI . '/assets/js/theme.js', ['gsap', 'gsap-scroll', 'vanilla-tilt'], RE_VERSION, true);

    wp_localize_script('re-theme', 'reData', [
        'ajaxUrl'  => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('re_nonce'),
        'themeUri' => RE_URI,
    ]);
});

/* ─── Font Preloading ─────────────────────────────────────── */

add_action('wp_head', function () {
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
}, 1);

/* ─── Custom Post Types ───────────────────────────────────── */

add_action('init', function () {
    register_post_type('re_project', [
        'labels' => [
            'name'          => __('Projects', 'r-e'),
            'singular_name' => __('Project', 'r-e'),
            'add_new_item'  => __('Add New Project', 'r-e'),
            'edit_item'     => __('Edit Project', 'r-e'),
        ],
        'public'       => true,
        'has_archive'  => 'portfolio',
        'rewrite'      => ['slug' => 'project', 'with_front' => false],
        'supports'     => ['title', 'editor', 'thumbnail', 'excerpt'],
        'menu_icon'    => 'dashicons-camera',
        'show_in_rest' => true,
    ]);

    register_taxonomy('re_project_cat', 're_project', [
        'labels' => [
            'name'          => __('Project Categories', 'r-e'),
            'singular_name' => __('Category', 'r-e'),
        ],
        'hierarchical' => true,
        'public'       => true,
        'rewrite'      => ['slug' => 'category'],
        'show_in_rest' => true,
    ]);

    register_post_type('re_testimonial', [
        'labels' => [
            'name'          => __('Testimonials', 'r-e'),
            'singular_name' => __('Testimonial', 'r-e'),
        ],
        'public'       => false,
        'show_ui'      => true,
        'supports'     => ['title', 'editor', 'thumbnail'],
        'menu_icon'    => 'dashicons-format-quote',
        'show_in_rest' => true,
    ]);
});

/* ─── Elementor Custom Widgets ────────────────────────────── */

add_action('elementor/widgets/register', function ($widgets_manager) {
    $widget_files = glob(RE_DIR . '/elementor-widgets/*.php');
    foreach ($widget_files as $file) {
        require_once $file;
    }

    if (class_exists('RE_Hero_3D_Widget')) {
        $widgets_manager->register(new RE_Hero_3D_Widget());
    }
    if (class_exists('RE_Portfolio_Grid_Widget')) {
        $widgets_manager->register(new RE_Portfolio_Grid_Widget());
    }
    if (class_exists('RE_Gallery_Rail_Widget')) {
        $widgets_manager->register(new RE_Gallery_Rail_Widget());
    }
    if (class_exists('RE_Stats_Counter_Widget')) {
        $widgets_manager->register(new RE_Stats_Counter_Widget());
    }
    if (class_exists('RE_Testimonial_3D_Widget')) {
        $widgets_manager->register(new RE_Testimonial_3D_Widget());
    }
    if (class_exists('RE_Marquee_Widget')) {
        $widgets_manager->register(new RE_Marquee_Widget());
    }
});

add_action('elementor/elements/categories_registered', function ($elements_manager) {
    $elements_manager->add_category('r-e-theme', [
        'title' => __('R-E Theme', 'r-e'),
        'icon'  => 'fa fa-camera',
    ]);
});

/* ─── Elementor Global Colors & Fonts ─────────────────────── */

add_action('elementor/editor/after_enqueue_scripts', function () {
    wp_enqueue_style('re-editor', RE_URI . '/assets/css/elementor-editor.css', [], RE_VERSION);
});

/* ─── Theme Settings ──────────────────────────────────────── */

require_once RE_DIR . '/inc/theme-settings.php';
require_once RE_DIR . '/inc/helpers.php';

/* ─── AJAX Handlers ───────────────────────────────────────── */

add_action('wp_ajax_re_contact', 're_handle_contact');
add_action('wp_ajax_nopriv_re_contact', 're_handle_contact');

function re_handle_contact() {
    check_ajax_referer('re_nonce', 'nonce');

    $name    = sanitize_text_field($_POST['name'] ?? '');
    $email   = sanitize_email($_POST['email'] ?? '');
    $message = sanitize_textarea_field($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($message)) {
        wp_send_json_error(['message' => __('All fields required.', 'r-e')]);
    }

    $to      = re_opt('re_email', get_option('admin_email'));
    $subject = sprintf('[R-E] New inquiry from %s', $name);
    $body    = sprintf("Name: %s\nEmail: %s\n\n%s", $name, $email, $message);
    $headers = ['Reply-To: ' . $name . ' <' . $email . '>', 'Content-Type: text/plain; charset=UTF-8'];

    $sent = wp_mail($to, $subject, $body, $headers);
    $sent ? wp_send_json_success(['message' => __('Message sent.', 'r-e')]) : wp_send_json_error(['message' => __('Send failed.', 'r-e')]);
}

/* ─── Body Classes ────────────────────────────────────────── */

add_filter('body_class', function ($classes) {
    $classes[] = 'r-e';
    if (re_opt('re_fx_cursor', true)) $classes[] = 're-cursor-active';
    if (re_opt('re_fx_grain', true))  $classes[] = 're-grain-active';
    if (re_opt('re_fx_wipe', true))   $classes[] = 're-wipe-active';
    if (re_opt('re_fx_3d', true))     $classes[] = 're-3d-active';
    if (re_opt('re_fx_particles', true)) $classes[] = 're-particles-active';
    return $classes;
});

/* ─── Elementor Page Template Blank Override ───────────────── */

add_filter('template_include', function ($template) {
    if (is_singular() && did_action('elementor/loaded') && re_is_elementor_page()) {
        $canvas = RE_DIR . '/template-parts/canvas.php';
        if (file_exists($canvas)) return $canvas;
    }
    return $template;
});

function re_is_elementor_page() {
    if (!did_action('elementor/loaded')) return false;
    if (!class_exists('\Elementor\Plugin')) return false;
    $post_id = get_the_ID();
    if (!$post_id) return false;
    $document = \Elementor\Plugin::$instance->documents->get($post_id);
    if (!$document) return false;
    return $document->is_built_with_elementor();
}

/* ─── Disable Emoji for Performance ───────────────────────── */

remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');

/* ─── Security Headers ────────────────────────────────────── */

add_action('send_headers', function () {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
});

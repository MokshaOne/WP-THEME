<?php
/**
 * R-E Hero 3D Slider — Elementor Widget
 * Fullscreen hero with 3D slide transitions
 */

defined('ABSPATH') || exit;

class RE_Hero_3D_Widget extends \Elementor\Widget_Base {

    public function get_name() { return 're-hero-3d'; }
    public function get_title() { return __('R-E Hero 3D Slider', 'r-e'); }
    public function get_icon() { return 'eicon-slider-push'; }
    public function get_categories() { return ['r-e-theme']; }
    public function get_keywords() { return ['hero', 'slider', '3d', 'fullscreen', 'photography']; }

    protected function register_controls() {
        $this->start_controls_section('content_section', [
            'label' => __('Slides', 'r-e'),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ]);

        $repeater = new \Elementor\Repeater();

        $repeater->add_control('image', [
            'label'   => __('Image', 'r-e'),
            'type'    => \Elementor\Controls_Manager::MEDIA,
            'default' => ['url' => \Elementor\Utils::get_placeholder_image_src()],
        ]);

        $repeater->add_control('title', [
            'label'   => __('Title', 'r-e'),
            'type'    => \Elementor\Controls_Manager::TEXT,
            'default' => __('Project Title', 'r-e'),
        ]);

        $repeater->add_control('category', [
            'label' => __('Category', 'r-e'),
            'type'  => \Elementor\Controls_Manager::TEXT,
        ]);

        $repeater->add_control('year', [
            'label'   => __('Year', 'r-e'),
            'type'    => \Elementor\Controls_Manager::TEXT,
            'default' => date('Y'),
        ]);

        $repeater->add_control('location', [
            'label' => __('Location', 'r-e'),
            'type'  => \Elementor\Controls_Manager::TEXT,
        ]);

        $repeater->add_control('link', [
            'label' => __('Link', 'r-e'),
            'type'  => \Elementor\Controls_Manager::URL,
        ]);

        $this->add_control('slides', [
            'label'   => __('Slides', 'r-e'),
            'type'    => \Elementor\Controls_Manager::REPEATER,
            'fields'  => $repeater->get_controls(),
            'default' => [
                ['title' => 'Timeless Portraits', 'category' => 'Portrait', 'year' => '2025', 'location' => 'Vienna'],
                ['title' => 'Urban Architecture', 'category' => 'Architecture', 'year' => '2025', 'location' => 'Berlin'],
                ['title' => 'Editorial Noir', 'category' => 'Editorial', 'year' => '2024', 'location' => 'Paris'],
            ],
            'title_field' => '{{{ title }}}',
        ]);

        $this->end_controls_section();

        $this->start_controls_section('settings_section', [
            'label' => __('Settings', 'r-e'),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control('autoplay', [
            'label'        => __('Autoplay', 'r-e'),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'default'      => 'yes',
        ]);

        $this->add_control('interval', [
            'label'   => __('Interval (ms)', 'r-e'),
            'type'    => \Elementor\Controls_Manager::NUMBER,
            'default' => 9000,
            'min'     => 2000,
            'max'     => 30000,
            'step'    => 500,
            'condition' => ['autoplay' => 'yes'],
        ]);

        $this->add_control('enable_3d', [
            'label'        => __('3D Transitions', 'r-e'),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'default'      => 'yes',
        ]);

        $this->add_control('ken_burns', [
            'label'        => __('Ken Burns Effect', 'r-e'),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'default'      => 'yes',
        ]);

        $this->add_control('show_dots', [
            'label'        => __('Show Thumbnail Dots', 'r-e'),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'default'      => 'yes',
        ]);

        $this->add_control('show_counter', [
            'label'        => __('Show Counter', 'r-e'),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'default'      => 'yes',
        ]);

        $this->add_control('eyebrow', [
            'label'   => __('Eyebrow Text', 'r-e'),
            'type'    => \Elementor\Controls_Manager::TEXT,
            'default' => __('Selected Work', 'r-e'),
        ]);

        $this->end_controls_section();

        // Style tab
        $this->start_controls_section('style_section', [
            'label' => __('Style', 'r-e'),
            'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
        ]);

        $this->add_control('overlay_gradient', [
            'label'   => __('Overlay Gradient', 'r-e'),
            'type'    => \Elementor\Controls_Manager::SWITCHER,
            'default' => 'yes',
        ]);

        $this->add_control('show_reg_marks', [
            'label'        => __('Registration Marks', 'r-e'),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'default'      => 'yes',
        ]);

        $this->end_controls_section();
    }

    protected function render() {
        $s = $this->get_settings_for_display();
        $slides = $s['slides'];
        if (empty($slides)) return;

        $classes = 're-hero';
        if ($s['ken_burns'] === 'yes') $classes .= ' re-fx-ken';
        ?>
        <div class="<?php echo esc_attr($classes); ?>"
             data-autoplay="<?php echo $s['autoplay'] === 'yes' ? 'true' : 'false'; ?>"
             data-interval="<?php echo esc_attr($s['interval']); ?>">

            <?php if ($s['show_reg_marks'] === 'yes'): ?>
                <div class="re-reg re-reg--tl"></div>
                <div class="re-reg re-reg--tr"></div>
                <div class="re-reg re-reg--bl"></div>
                <div class="re-reg re-reg--br"></div>
            <?php endif; ?>

            <?php foreach ($slides as $i => $slide): ?>
                <div class="re-hero__slide<?php echo $i === 0 ? ' is-active' : ''; ?>">
                    <?php if (!empty($slide['image']['url'])): ?>
                        <img class="re-hero__image"
                             src="<?php echo esc_url($slide['image']['url']); ?>"
                             alt="<?php echo esc_attr($slide['title']); ?>"
                             loading="<?php echo $i === 0 ? 'eager' : 'lazy'; ?>"
                             decoding="async"
                             <?php if ($i === 0): ?>fetchpriority="high"<?php endif; ?>>
                    <?php endif; ?>

                    <div class="re-hero__content">
                        <?php if ($s['eyebrow']): ?>
                            <div class="re-eyebrow"><?php echo esc_html($s['eyebrow']); ?></div>
                        <?php endif; ?>

                        <h2 class="re-hero__title re-text-3d">
                            <?php echo re_emphasize_title($slide['title']); ?>
                        </h2>

                        <div class="re-hero__meta">
                            <?php if ($slide['category']): ?><span><?php echo esc_html($slide['category']); ?></span><?php endif; ?>
                            <?php if ($slide['location']): ?><span><?php echo esc_html($slide['location']); ?></span><?php endif; ?>
                            <?php if ($slide['year']): ?><span><?php echo esc_html($slide['year']); ?></span><?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Navigation -->
            <button class="re-hero__arrow re-hero__arrow--prev" aria-label="<?php esc_attr_e('Previous', 'r-e'); ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M15 19l-7-7 7-7"/></svg>
            </button>
            <button class="re-hero__arrow re-hero__arrow--next" aria-label="<?php esc_attr_e('Next', 'r-e'); ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 5l7 7-7 7"/></svg>
            </button>

            <?php if ($s['show_counter'] === 'yes'): ?>
                <div class="re-hero__counter">01 of <?php echo str_pad(count($slides), 2, '0', STR_PAD_LEFT); ?></div>
            <?php endif; ?>

            <?php if ($s['show_dots'] === 'yes'): ?>
                <div class="re-hero__dots">
                    <?php foreach ($slides as $i => $slide): ?>
                        <button class="re-hero__dot<?php echo $i === 0 ? ' is-active' : ''; ?>" aria-label="Slide <?php echo $i + 1; ?>">
                            <?php if (!empty($slide['image']['url'])): ?>
                                <img src="<?php echo esc_url($slide['image']['url']); ?>" alt="" loading="lazy" decoding="async">
                            <?php endif; ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
}

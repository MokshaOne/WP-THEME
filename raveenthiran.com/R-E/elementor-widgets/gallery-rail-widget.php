<?php
/**
 * R-E Gallery Rail — Elementor Widget
 * Horizontal scrolling gallery with 3D depth and lightbox
 */

defined('ABSPATH') || exit;

class RE_Gallery_Rail_Widget extends \Elementor\Widget_Base {

    public function get_name() { return 're-gallery-rail'; }
    public function get_title() { return __('R-E Gallery Rail', 'r-e'); }
    public function get_icon() { return 'eicon-slider-album'; }
    public function get_categories() { return ['r-e-theme']; }
    public function get_keywords() { return ['gallery', 'rail', 'horizontal', 'scroll', 'lightbox']; }

    protected function register_controls() {
        $this->start_controls_section('content_section', [
            'label' => __('Gallery', 'r-e'),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control('gallery', [
            'label'   => __('Images', 'r-e'),
            'type'    => \Elementor\Controls_Manager::GALLERY,
            'default' => [],
        ]);

        $this->add_control('layout', [
            'label'   => __('Layout', 'r-e'),
            'type'    => \Elementor\Controls_Manager::SELECT,
            'options' => [
                'rail'   => __('Horizontal Rail', 'r-e'),
                'spread' => __('3D Spread', 'r-e'),
            ],
            'default' => 'rail',
        ]);

        $this->add_control('enable_lightbox', [
            'label'   => __('Lightbox', 'r-e'),
            'type'    => \Elementor\Controls_Manager::SWITCHER,
            'default' => 'yes',
        ]);

        $this->add_control('show_counter', [
            'label'   => __('Plate Counter', 'r-e'),
            'type'    => \Elementor\Controls_Manager::SWITCHER,
            'default' => 'yes',
        ]);

        $this->add_control('height', [
            'label'      => __('Rail Height', 'r-e'),
            'type'       => \Elementor\Controls_Manager::SLIDER,
            'size_units' => ['vh', 'px'],
            'range'      => ['vh' => ['min' => 30, 'max' => 100], 'px' => ['min' => 200, 'max' => 1000]],
            'default'    => ['unit' => 'vh', 'size' => 80],
        ]);

        $this->end_controls_section();
    }

    protected function render() {
        $s = $this->get_settings_for_display();
        $images = $s['gallery'];
        if (empty($images)) return;

        $is_spread = $s['layout'] === 'spread';
        $class = $is_spread ? 're-gallery-3d re-gallery-3d--spread' : 're-rail';
        $height = $s['height']['size'] . $s['height']['unit'];
        ?>
        <div class="<?php echo esc_attr($class); ?>"
             data-h-rail
             data-lightbox-group
             style="height: <?php echo esc_attr($height); ?>;">

            <?php foreach ($images as $i => $img): ?>
                <div class="re-plate<?php echo $is_spread ? ' re-gallery-3d__item' : ''; ?>">
                    <img src="<?php echo esc_url($img['url']); ?>"
                         alt="<?php echo esc_attr($img['alt'] ?? ''); ?>"
                         loading="<?php echo $i < 3 ? 'eager' : 'lazy'; ?>"
                         decoding="async">
                    <?php if ($s['enable_lightbox'] === 'yes'): ?>
                        <button class="re-plate__lightbox"
                                data-lightbox-src="<?php echo esc_url($img['url']); ?>"
                                data-caption="<?php echo esc_attr($img['alt'] ?? ''); ?>"
                                aria-label="<?php printf(esc_attr__('View image %d', 'r-e'), $i + 1); ?>"
                                style="position:absolute;inset:0;background:none;border:none;cursor:pointer;"></button>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($s['show_counter'] === 'yes'): ?>
            <div class="re-chrome" data-plate-counter style="margin-top: 12px;">
                01 / <?php echo str_pad(count($images), 2, '0', STR_PAD_LEFT); ?>
            </div>
        <?php endif; ?>
        <?php
    }
}

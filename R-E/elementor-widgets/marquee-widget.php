<?php
/**
 * R-E Marquee — Elementor Widget
 * Auto-scrolling text marquee for clients / awards
 */

defined('ABSPATH') || exit;

class RE_Marquee_Widget extends \Elementor\Widget_Base {

    public function get_name() { return 're-marquee'; }
    public function get_title() { return __('R-E Marquee', 'r-e'); }
    public function get_icon() { return 'eicon-scroll'; }
    public function get_categories() { return ['r-e-theme']; }

    protected function register_controls() {
        $this->start_controls_section('content_section', [
            'label' => __('Content', 'r-e'),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control('items', [
            'label'       => __('Items (one per line)', 'r-e'),
            'type'        => \Elementor\Controls_Manager::TEXTAREA,
            'default'     => "Red Bull\nVogue\nPorsche\nSwarovski\nWiener Staatsoper\nAdidas\nSony Music",
            'placeholder' => __('One item per line', 'r-e'),
        ]);

        $this->add_control('speed', [
            'label'   => __('Duration (seconds)', 'r-e'),
            'type'    => \Elementor\Controls_Manager::NUMBER,
            'default' => 30,
            'min'     => 5,
            'max'     => 120,
        ]);

        $this->add_control('separator', [
            'label'   => __('Separator', 'r-e'),
            'type'    => \Elementor\Controls_Manager::TEXT,
            'default' => '·',
        ]);

        $this->add_control('direction', [
            'label'   => __('Direction', 'r-e'),
            'type'    => \Elementor\Controls_Manager::SELECT,
            'options' => [
                'left'  => __('Left', 'r-e'),
                'right' => __('Right', 'r-e'),
            ],
            'default' => 'left',
        ]);

        $this->end_controls_section();
    }

    protected function render() {
        $s = $this->get_settings_for_display();
        $items = array_filter(array_map('trim', explode("\n", $s['items'])));
        if (empty($items)) return;

        $dir = $s['direction'] === 'right' ? 'reverse' : 'normal';
        $duration = intval($s['speed']) . 's';
        $sep = esc_html($s['separator']);
        ?>
        <div class="re-marquee">
            <div class="re-marquee__track" style="animation-duration: <?php echo esc_attr($duration); ?>; animation-direction: <?php echo esc_attr($dir); ?>;">
                <?php for ($rep = 0; $rep < 2; $rep++): ?>
                    <?php foreach ($items as $i => $item): ?>
                        <span class="re-marquee__item"><?php echo esc_html($item); ?></span>
                        <?php if ($i < count($items) - 1 || $rep === 0): ?>
                            <span class="re-marquee__item" aria-hidden="true"><?php echo $sep; ?></span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endfor; ?>
            </div>
        </div>
        <?php
    }
}

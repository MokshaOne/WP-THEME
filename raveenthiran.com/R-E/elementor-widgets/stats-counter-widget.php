<?php
/**
 * R-E Stats Counter — Elementor Widget
 * Animated number counters with 3D hover depth
 */

defined('ABSPATH') || exit;

class RE_Stats_Counter_Widget extends \Elementor\Widget_Base {

    public function get_name() { return 're-stats-counter'; }
    public function get_title() { return __('R-E Stats Counter', 'r-e'); }
    public function get_icon() { return 'eicon-counter'; }
    public function get_categories() { return ['r-e-theme']; }

    protected function register_controls() {
        $this->start_controls_section('content_section', [
            'label' => __('Stats', 'r-e'),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ]);

        $repeater = new \Elementor\Repeater();

        $repeater->add_control('number', [
            'label'   => __('Number', 'r-e'),
            'type'    => \Elementor\Controls_Manager::TEXT,
            'default' => '150',
        ]);

        $repeater->add_control('suffix', [
            'label' => __('Suffix', 'r-e'),
            'type'  => \Elementor\Controls_Manager::TEXT,
            'default' => '+',
        ]);

        $repeater->add_control('label', [
            'label'   => __('Label', 'r-e'),
            'type'    => \Elementor\Controls_Manager::TEXT,
            'default' => __('Projects', 'r-e'),
        ]);

        $this->add_control('stats', [
            'label'   => __('Stats', 'r-e'),
            'type'    => \Elementor\Controls_Manager::REPEATER,
            'fields'  => $repeater->get_controls(),
            'default' => [
                ['number' => '150', 'suffix' => '+', 'label' => 'Projects'],
                ['number' => '8', 'suffix' => '', 'label' => 'Years'],
                ['number' => '50', 'suffix' => 'K', 'label' => 'Frames'],
                ['number' => '12', 'suffix' => '', 'label' => 'Awards'],
            ],
            'title_field' => '{{{ label }}}',
        ]);

        $this->add_control('animate', [
            'label'   => __('Count-up Animation', 'r-e'),
            'type'    => \Elementor\Controls_Manager::SWITCHER,
            'default' => 'yes',
        ]);

        $this->end_controls_section();
    }

    protected function render() {
        $s = $this->get_settings_for_display();
        ?>
        <div class="re-stats re-stagger">
            <?php foreach ($s['stats'] as $stat): ?>
                <div class="re-stat re-glow-border" data-tilt>
                    <div class="re-stat__number"
                         <?php if ($s['animate'] === 'yes'): ?>
                         data-count-to="<?php echo esc_attr($stat['number']); ?>"
                         <?php endif; ?>>
                        <?php echo esc_html($stat['number'] . $stat['suffix']); ?>
                    </div>
                    <div class="re-stat__label"><?php echo esc_html($stat['label']); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
    }
}

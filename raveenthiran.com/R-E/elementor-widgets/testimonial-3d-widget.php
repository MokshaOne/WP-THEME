<?php
/**
 * R-E Testimonial 3D — Elementor Widget
 * Flip-card testimonials with 3D rotate effect
 */

defined('ABSPATH') || exit;

class RE_Testimonial_3D_Widget extends \Elementor\Widget_Base {

    public function get_name() { return 're-testimonial-3d'; }
    public function get_title() { return __('R-E Testimonial 3D', 'r-e'); }
    public function get_icon() { return 'eicon-testimonial'; }
    public function get_categories() { return ['r-e-theme']; }

    protected function register_controls() {
        $this->start_controls_section('content_section', [
            'label' => __('Testimonials', 'r-e'),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ]);

        $repeater = new \Elementor\Repeater();

        $repeater->add_control('quote', [
            'label'   => __('Quote', 'r-e'),
            'type'    => \Elementor\Controls_Manager::TEXTAREA,
            'default' => __('Working with Raveenthiran was an extraordinary experience.', 'r-e'),
        ]);

        $repeater->add_control('author', [
            'label'   => __('Author', 'r-e'),
            'type'    => \Elementor\Controls_Manager::TEXT,
            'default' => __('Client Name', 'r-e'),
        ]);

        $repeater->add_control('role', [
            'label' => __('Role / Company', 'r-e'),
            'type'  => \Elementor\Controls_Manager::TEXT,
        ]);

        $repeater->add_control('project', [
            'label' => __('Project Name', 'r-e'),
            'type'  => \Elementor\Controls_Manager::TEXT,
        ]);

        $repeater->add_control('rating', [
            'label'   => __('Rating', 'r-e'),
            'type'    => \Elementor\Controls_Manager::NUMBER,
            'default' => 5,
            'min'     => 1,
            'max'     => 5,
        ]);

        $this->add_control('testimonials', [
            'label'  => __('Testimonials', 'r-e'),
            'type'   => \Elementor\Controls_Manager::REPEATER,
            'fields' => $repeater->get_controls(),
            'default' => [
                ['quote' => 'An extraordinary eye for light and composition.', 'author' => 'Marie Laurent', 'role' => 'Creative Director'],
                ['quote' => 'Exceeded every expectation with refined elegance.', 'author' => 'Thomas Varga', 'role' => 'Brand Manager'],
            ],
            'title_field' => '{{{ author }}}',
        ]);

        $this->add_control('columns', [
            'label'   => __('Columns', 'r-e'),
            'type'    => \Elementor\Controls_Manager::SELECT,
            'options' => ['1' => '1', '2' => '2', '3' => '3'],
            'default' => '2',
        ]);

        $this->end_controls_section();
    }

    protected function render() {
        $s = $this->get_settings_for_display();
        ?>
        <div style="display: grid; grid-template-columns: repeat(<?php echo esc_attr($s['columns']); ?>, 1fr); gap: 20px;" class="re-stagger">
            <?php foreach ($s['testimonials'] as $t): ?>
                <div class="re-testimonial">
                    <div class="re-testimonial__inner">
                        <div class="re-testimonial__front">
                            <div class="re-testimonial__quote">&ldquo;<?php echo esc_html($t['quote']); ?>&rdquo;</div>
                            <div class="re-testimonial__author">
                                <?php echo esc_html($t['author']); ?>
                                <?php if ($t['role']): ?>
                                    <span style="color: var(--re-ink-3);"> · <?php echo esc_html($t['role']); ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if ($t['rating']): ?>
                                <div style="margin-top: 12px; color: var(--re-amber);">
                                    <?php echo str_repeat('★', (int)$t['rating']); echo str_repeat('☆', 5 - (int)$t['rating']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="re-testimonial__back">
                            <?php if ($t['project']): ?>
                                <div class="re-eyebrow" style="margin-bottom: 12px;">Project</div>
                                <div class="re-display--sm"><?php echo esc_html($t['project']); ?></div>
                            <?php endif; ?>
                            <div class="re-chrome" style="margin-top: auto;"><?php echo esc_html($t['author']); ?></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
    }
}

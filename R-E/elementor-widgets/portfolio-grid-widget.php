<?php
/**
 * R-E Portfolio Grid — Elementor Widget
 * Dynamic portfolio grid with 3D tilt cards and category filtering
 */

defined('ABSPATH') || exit;

class RE_Portfolio_Grid_Widget extends \Elementor\Widget_Base {

    public function get_name() { return 're-portfolio-grid'; }
    public function get_title() { return __('R-E Portfolio Grid', 'r-e'); }
    public function get_icon() { return 'eicon-gallery-grid'; }
    public function get_categories() { return ['r-e-theme']; }
    public function get_keywords() { return ['portfolio', 'gallery', 'grid', 'projects', 'filter']; }

    protected function register_controls() {
        $this->start_controls_section('content_section', [
            'label' => __('Query', 'r-e'),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control('posts_per_page', [
            'label'   => __('Projects per Page', 'r-e'),
            'type'    => \Elementor\Controls_Manager::NUMBER,
            'default' => 12,
            'min'     => 1,
            'max'     => 48,
        ]);

        $this->add_control('show_filter', [
            'label'   => __('Show Filter Chips', 'r-e'),
            'type'    => \Elementor\Controls_Manager::SWITCHER,
            'default' => 'yes',
        ]);

        $this->add_control('enable_tilt', [
            'label'   => __('3D Tilt on Cards', 'r-e'),
            'type'    => \Elementor\Controls_Manager::SWITCHER,
            'default' => 'yes',
        ]);

        $this->add_control('card_style', [
            'label'   => __('Card Style', 'r-e'),
            'type'    => \Elementor\Controls_Manager::SELECT,
            'options' => [
                'overlay'   => __('Overlay on Hover', 'r-e'),
                'below'     => __('Meta Below Image', 'r-e'),
                'minimal'   => __('Minimal (No Meta)', 'r-e'),
            ],
            'default' => 'overlay',
        ]);

        $this->add_control('columns', [
            'label'   => __('Columns', 'r-e'),
            'type'    => \Elementor\Controls_Manager::SELECT,
            'options' => [
                '2' => '2', '3' => '3', '4' => '4',
            ],
            'default' => '3',
        ]);

        $this->add_control('eyebrow', [
            'label'   => __('Section Eyebrow', 'r-e'),
            'type'    => \Elementor\Controls_Manager::TEXT,
            'default' => __('Portfolio', 'r-e'),
        ]);

        $this->add_control('title', [
            'label'   => __('Section Title', 'r-e'),
            'type'    => \Elementor\Controls_Manager::TEXT,
            'default' => __('Selected Work', 'r-e'),
        ]);

        $this->end_controls_section();
    }

    protected function render() {
        $s = $this->get_settings_for_display();

        $query = new WP_Query([
            'post_type'      => 're_project',
            'posts_per_page' => $s['posts_per_page'],
            'orderby'        => 'date',
            'order'          => 'DESC',
        ]);

        $categories = get_terms(['taxonomy' => 're_project_cat', 'hide_empty' => true]);
        $tilt = $s['enable_tilt'] === 'yes' ? ' data-tilt' : '';
        ?>
        <section class="re-portfolio re-reveal">
            <?php if ($s['eyebrow']): ?>
                <div class="re-eyebrow" style="margin-bottom: 12px;"><?php echo esc_html($s['eyebrow']); ?></div>
            <?php endif; ?>
            <?php if ($s['title']): ?>
                <h2 class="re-display--md" style="margin-bottom: 32px;" data-gsap-fade-up><?php echo re_emphasize_title($s['title']); ?></h2>
            <?php endif; ?>

            <?php if ($s['show_filter'] === 'yes' && !empty($categories) && !is_wp_error($categories)): ?>
                <div class="re-filter re-stagger">
                    <button class="re-chip is-on" data-filter="all">All</button>
                    <?php foreach ($categories as $cat): ?>
                        <button class="re-chip" data-filter="<?php echo esc_attr($cat->slug); ?>"><?php echo esc_html($cat->name); ?></button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="re-grid" style="grid-template-columns: repeat(<?php echo esc_attr($s['columns']); ?>, 1fr);">
                <?php while ($query->have_posts()): $query->the_post();
                    $meta = re_project_meta();
                    $cats = wp_list_pluck(get_the_terms(get_the_ID(), 're_project_cat') ?: [], 'slug');
                    $img = re_image_or_placeholder(get_the_ID());
                    ?>
                    <a href="<?php the_permalink(); ?>" class="re-card re-distort" data-cats="<?php echo esc_attr(implode(' ', $cats)); ?>"<?php echo $tilt; ?> data-gsap-scale-in>
                        <img class="re-card__image" src="<?php echo esc_url($img); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" decoding="async">
                        <?php if ($meta['n']): ?>
                            <span class="re-card__badge"><?php echo esc_html($meta['n']); ?></span>
                        <?php endif; ?>
                        <?php if ($s['card_style'] === 'overlay'): ?>
                            <div class="re-card__overlay">
                                <h3 class="re-card__title"><?php echo re_emphasize_title(get_the_title()); ?></h3>
                                <div class="re-card__meta">
                                    <?php echo esc_html(implode(' · ', array_filter([$meta['cat'], $meta['yr']]))); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </a>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
        </section>
        <?php
    }
}

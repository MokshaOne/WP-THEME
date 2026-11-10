<?php
/**
 * Front Page — Hero Slider (fallback when not built with Elementor)
 */
get_header();

$projects = new WP_Query([
    'post_type'      => 're_project',
    'posts_per_page' => intval(re_opt('re_hero_max', 6)),
    'meta_key'       => 'featured_on_homepage',
    'meta_value'     => '1',
    'orderby'        => 'menu_order date',
    'order'          => 'DESC',
]);

if (!$projects->have_posts()) {
    $projects = new WP_Query([
        'post_type'      => 're_project',
        'posts_per_page' => intval(re_opt('re_hero_max', 6)),
        'orderby'        => 'date',
        'order'          => 'DESC',
    ]);
}

$slides = [];
while ($projects->have_posts()) {
    $projects->the_post();
    $meta = re_project_meta();
    $slides[] = [
        'title'    => get_the_title(),
        'image'    => re_image_or_placeholder(get_the_ID(), 're-hero', 'Hero'),
        'link'     => get_permalink(),
        'cat'      => $meta['cat'],
        'yr'       => $meta['yr'],
        'loc'      => $meta['loc'],
        'client'   => $meta['client'],
    ];
}
wp_reset_postdata();

$use_3d = re_opt('re_fx_3d', true);
$auto   = re_opt('re_hero_auto', true);
$interval = re_opt('re_hero_interval', 9000);
?>

<div class="re-hero<?php echo re_opt('re_fx_ken', true) ? ' re-fx-ken' : ''; ?>"
     data-autoplay="<?php echo $auto ? 'true' : 'false'; ?>"
     data-interval="<?php echo esc_attr($interval); ?>">

    <div class="re-reg re-reg--tl"></div>
    <div class="re-reg re-reg--tr"></div>
    <div class="re-reg re-reg--bl"></div>
    <div class="re-reg re-reg--br"></div>

    <?php foreach ($slides as $i => $slide): ?>
        <div class="re-hero__slide<?php echo $i === 0 ? ' is-active' : ''; ?>">
            <img class="re-hero__image"
                 src="<?php echo esc_url($slide['image']); ?>"
                 alt="<?php echo esc_attr($slide['title']); ?>"
                 loading="<?php echo $i === 0 ? 'eager' : 'lazy'; ?>"
                 decoding="async"
                 <?php if ($i === 0): ?>fetchpriority="high"<?php endif; ?>>

            <div class="re-hero__content">
                <div class="re-eyebrow"><?php echo esc_html(re_opt('re_hero_eyebrow', 'Selected Work')); ?></div>
                <h2 class="re-hero__title re-text-3d">
                    <a href="<?php echo esc_url($slide['link']); ?>"><?php echo re_emphasize_title($slide['title']); ?></a>
                </h2>
                <div class="re-hero__meta">
                    <?php if ($slide['cat']): ?><span><?php echo esc_html($slide['cat']); ?></span><?php endif; ?>
                    <?php if ($slide['loc']): ?><span><?php echo esc_html($slide['loc']); ?></span><?php endif; ?>
                    <?php if ($slide['yr']): ?><span><?php echo esc_html($slide['yr']); ?></span><?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <button class="re-hero__arrow re-hero__arrow--prev" aria-label="<?php esc_attr_e('Previous', 'r-e'); ?>">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M15 19l-7-7 7-7"/></svg>
    </button>
    <button class="re-hero__arrow re-hero__arrow--next" aria-label="<?php esc_attr_e('Next', 'r-e'); ?>">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 5l7 7-7 7"/></svg>
    </button>

    <div class="re-hero__counter">01 of <?php echo str_pad(count($slides), 2, '0', STR_PAD_LEFT); ?></div>

    <div class="re-hero__dots">
        <?php foreach ($slides as $i => $slide): ?>
            <button class="re-hero__dot<?php echo $i === 0 ? ' is-active' : ''; ?>" aria-label="Slide <?php echo $i + 1; ?>">
                <img src="<?php echo esc_url($slide['image']); ?>" alt="" loading="lazy" decoding="async">
            </button>
        <?php endforeach; ?>
    </div>
</div>

<?php get_footer();

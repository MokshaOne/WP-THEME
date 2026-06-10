<?php
/**
 * Portfolio Archive — Grid with category filter
 */
get_header();

$categories = get_terms(['taxonomy' => 're_project_cat', 'hide_empty' => true]);
?>

<section class="re-portfolio">
    <div class="re-eyebrow" style="margin-bottom: 12px;"><?php echo esc_html(re_opt('re_portfolio_title', 'Portfolio')); ?></div>
    <h1 class="re-display--md re-reveal" style="margin-bottom: 32px;" data-gsap-fade-up>
        <?php echo re_emphasize_title(re_opt('re_portfolio_title', __('Selected Work', 'r-e'))); ?>
    </h1>

    <?php if (!empty($categories) && !is_wp_error($categories)): ?>
        <div class="re-filter re-stagger">
            <button class="re-chip is-on" data-filter="all"><?php esc_html_e('All', 'r-e'); ?></button>
            <?php foreach ($categories as $cat): ?>
                <button class="re-chip" data-filter="<?php echo esc_attr($cat->slug); ?>"><?php echo esc_html($cat->name); ?></button>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (have_posts()): ?>
        <div class="re-grid">
            <?php while (have_posts()): the_post();
                $meta = re_project_meta();
                $cats = wp_list_pluck(get_the_terms(get_the_ID(), 're_project_cat') ?: [], 'slug');
                $img  = re_image_or_placeholder(get_the_ID());
                ?>
                <a href="<?php the_permalink(); ?>" class="re-card re-distort" data-cats="<?php echo esc_attr(implode(' ', $cats)); ?>" data-tilt data-gsap-scale-in>
                    <img class="re-card__image" src="<?php echo esc_url($img); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" decoding="async">
                    <?php if ($meta['n']): ?>
                        <span class="re-card__badge"><?php echo esc_html($meta['n']); ?></span>
                    <?php endif; ?>
                    <div class="re-card__overlay">
                        <h2 class="re-card__title"><?php echo re_emphasize_title(get_the_title()); ?></h2>
                        <div class="re-card__meta">
                            <?php echo esc_html(implode(' · ', array_filter([$meta['cat'], $meta['yr']]))); ?>
                        </div>
                    </div>
                </a>
            <?php endwhile; ?>
        </div>
        <?php the_posts_pagination(['class' => 're-chrome', 'prev_text' => '←', 'next_text' => '→']); ?>
    <?php else: ?>
        <p class="re-prose"><?php esc_html_e('No projects yet.', 'r-e'); ?></p>
    <?php endif; ?>
</section>

<?php get_footer();

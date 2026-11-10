<?php
/**
 * Search Results
 */
get_header(); ?>

<section class="re-portfolio">
    <div class="re-eyebrow" style="margin-bottom: 12px;"><?php esc_html_e('Search', 'r-e'); ?></div>
    <h1 class="re-display--md re-reveal" style="margin-bottom: 32px;">
        <?php printf(esc_html__('Results for &ldquo;%s&rdquo;', 'r-e'), get_search_query()); ?>
    </h1>

    <?php if (have_posts()): ?>
        <div class="re-grid">
            <?php while (have_posts()): the_post(); ?>
                <a href="<?php the_permalink(); ?>" class="re-card" data-tilt data-gsap-scale-in>
                    <?php if (has_post_thumbnail()): ?>
                        <img class="re-card__image" src="<?php echo esc_url(get_the_post_thumbnail_url(null, 're-card')); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" decoding="async">
                    <?php endif; ?>
                    <div class="re-card__overlay">
                        <h2 class="re-card__title"><?php echo re_emphasize_title(get_the_title()); ?></h2>
                        <div class="re-card__meta"><?php echo esc_html(get_the_date('Y')); ?></div>
                    </div>
                </a>
            <?php endwhile; ?>
        </div>
        <?php the_posts_pagination(['class' => 're-chrome', 'prev_text' => '←', 'next_text' => '→']); ?>
    <?php else: ?>
        <p class="re-prose"><?php esc_html_e('No results found.', 'r-e'); ?></p>
    <?php endif; ?>
</section>

<?php get_footer();

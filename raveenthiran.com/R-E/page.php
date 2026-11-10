<?php
/**
 * Generic Page Template
 */
get_header(); ?>

<section style="padding: calc(var(--re-bar-h) + var(--re-pad)) var(--re-pad) var(--re-pad); max-width: 800px; margin: 0 auto;">
    <?php while (have_posts()): the_post(); ?>
        <div class="re-eyebrow" style="margin-bottom: 12px;"><?php echo esc_html(get_post_type_object(get_post_type())->labels->singular_name ?? 'Page'); ?></div>
        <h1 class="re-display--lg re-reveal" style="margin-bottom: 32px;"><?php echo re_emphasize_title(get_the_title()); ?></h1>
        <div class="re-prose re-reveal">
            <?php the_content(); ?>
        </div>
    <?php endwhile; ?>
</section>

<?php get_footer();

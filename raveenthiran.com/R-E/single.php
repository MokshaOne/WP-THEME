<?php
/**
 * Single Post / Page
 */
get_header(); ?>

<article style="padding: calc(var(--re-bar-h) + var(--re-pad)) var(--re-pad) var(--re-pad); max-width: 800px; margin: 0 auto;">
    <?php while (have_posts()): the_post(); ?>
        <div class="re-eyebrow" style="margin-bottom: 12px;">
            <?php echo esc_html(get_the_date('d M Y')); ?>
        </div>
        <h1 class="re-display--lg re-reveal" style="margin-bottom: 32px;">
            <?php echo re_emphasize_title(get_the_title()); ?>
        </h1>
        <?php if (has_post_thumbnail()): ?>
            <div class="re-reveal" style="margin-bottom: 32px; border-radius: 4px; overflow: hidden;">
                <?php the_post_thumbnail('large', ['loading' => 'eager', 'decoding' => 'async', 'fetchpriority' => 'high']); ?>
            </div>
        <?php endif; ?>
        <div class="re-prose re-reveal">
            <?php the_content(); ?>
        </div>
    <?php endwhile; ?>
</article>

<?php get_footer();

<?php
/**
 * Generic page template — v2.1 Relief.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header(); the_post(); ?>

<header class="page-head">
    <div class="page-head__in">
        <h1 class="page-head__title"><?php the_title(); ?></h1>
    </div>
</header>

<article style="max-width:720px; margin:0 auto; padding: var(--pad-section) var(--pad-x);">
    <?php if ( has_post_thumbnail() ) : ?>
        <div style="margin-bottom: 3rem; position: relative; aspect-ratio: 16/9; overflow: hidden; border: 1px solid var(--rule);">
            <?php the_post_thumbnail( 'poem-hero', [
                'style' => 'width:100%; height:100%; object-fit:cover; ' . kv_focal_style( get_the_ID() ),
            ] ); ?>
        </div>
    <?php endif; ?>

    <div style="font-family:var(--serif-ta); font-size:1.15rem; color:var(--ink-soft); line-height:2;">
        <?php the_content(); ?>
    </div>
</article>

<?php get_footer(); ?>

<?php
/**
 * Single Project — Split layout with horizontal plate rail
 */
get_header();
the_post();

$meta   = re_project_meta();
$gallery = re_field('project_gallery') ?: [];
$cover   = get_the_post_thumbnail_url(null, 're-hero') ?: re_placeholder('Cover', true, '3:2');
?>

<div class="re-project">
    <!-- Sidebar -->
    <div class="re-project__sidebar">
        <div>
            <div class="re-eyebrow" style="margin-bottom: 16px;">
                <?php echo esc_html($meta['cat'] ?: __('Project', 'r-e')); ?>
            </div>

            <h1 class="re-display--md re-text-3d re-reveal" style="margin-bottom: 24px;">
                <?php echo re_emphasize_title(get_the_title()); ?>
            </h1>

            <?php if (has_excerpt()): ?>
                <p class="re-prose re-reveal" style="margin-bottom: 24px;"><?php the_excerpt(); ?></p>
            <?php endif; ?>

            <!-- Meta grid -->
            <div class="re-stagger" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <?php
                $meta_items = [
                    [__('Number', 'r-e'), $meta['n']],
                    [__('Year', 'r-e'), $meta['yr']],
                    [__('Client', 'r-e'), $meta['client']],
                    [__('Location', 'r-e'), $meta['loc']],
                    [__('Frames', 'r-e'), $meta['frames']],
                    [__('Format', 'r-e'), $meta['format']],
                ];
                foreach ($meta_items as [$label, $value]):
                    if (!$value) continue;
                ?>
                    <div>
                        <div class="re-chrome" style="margin-bottom: 4px;"><?php echo esc_html($label); ?></div>
                        <div style="font-weight: var(--re-fw-medium);"><?php echo esc_html($value); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 12px; margin-top: 32px;">
            <div class="re-magnetic">
                <a href="#" data-modal="re-booking" data-service="<?php echo esc_attr($meta['cat']); ?>" class="re-btn re-btn--primary re-btn--block">
                    <?php esc_html_e('Commission Similar', 'r-e'); ?>
                </a>
            </div>
            <a href="<?php echo esc_url(home_url('/portfolio')); ?>" class="re-btn re-btn--ghost">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M15 19l-7-7 7-7"/></svg>
                <?php esc_html_e('Back to Work', 'r-e'); ?>
            </a>
            <div class="re-chrome" data-plate-counter>01 / <?php echo str_pad(count($gallery) ?: 1, 2, '0', STR_PAD_LEFT); ?></div>
        </div>
    </div>

    <!-- Plate Rail -->
    <div class="re-project__rail" data-h-rail data-lightbox-group>
        <?php if (!empty($gallery)):
            foreach ($gallery as $i => $img):
                $url = is_array($img) ? ($img['url'] ?? '') : wp_get_attachment_url($img);
                if (!$url) continue;
                ?>
                <div class="re-plate">
                    <img src="<?php echo esc_url($url); ?>"
                         alt="<?php echo esc_attr(is_array($img) ? ($img['alt'] ?? '') : get_post_meta($img, '_wp_attachment_image_alt', true)); ?>"
                         loading="<?php echo $i < 2 ? 'eager' : 'lazy'; ?>"
                         decoding="async">
                    <button data-lightbox-src="<?php echo esc_url($url); ?>"
                            aria-label="<?php printf(esc_attr__('View plate %d', 'r-e'), $i + 1); ?>"
                            style="position:absolute;inset:0;background:none;border:none;cursor:pointer;"></button>
                </div>
            <?php endforeach;
        else: ?>
            <div class="re-plate">
                <img src="<?php echo esc_url($cover); ?>" alt="<?php the_title_attribute(); ?>">
            </div>
        <?php endif; ?>
    </div>
</div>

<?php get_footer();

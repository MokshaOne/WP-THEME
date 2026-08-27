<?php
/**
 * Title: Featured work grid
 * Slug: latent/featured-work
 * Categories: latent
 * Description: A grid of the latest Work posts (featured image + title + category), pulled live via a Query Loop.
 */
?>
<!-- wp:group {"tagName":"section","align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50"}}},"layout":{"type":"constrained","wideSize":"1280px"}} -->
<section class="wp-block-group alignfull" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50)"><!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|20","margin":{"bottom":"var:preset|spacing|40"}}},"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"bottom"}} -->
<div class="wp-block-group" style="margin-bottom:var(--wp--preset--spacing--40)"><!-- wp:group {"style":{"spacing":{"blockGap":"0.6rem"}},"layout":{"type":"constrained","justifyContent":"left"}} -->
<div class="wp-block-group"><!-- wp:paragraph {"style":{"typography":{"fontSize":"0.72rem","letterSpacing":"0.26em","textTransform":"uppercase"},"color":{"text":"var:preset|color|accent-deep"}},"fontFamily":"mono"} -->
<p class="has-text-color has-mono-font-family" style="color:var(--wp--preset--color--accent-deep);font-size:0.72rem;letter-spacing:0.26em;text-transform:uppercase">— Selected work</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading">Recent frames, 2024—2026</h2>
<!-- /wp:heading --></div>
<!-- /wp:group -->

<!-- wp:paragraph {"style":{"typography":{"fontSize":"0.72rem","letterSpacing":"0.14em","textTransform":"uppercase"}},"fontFamily":"mono"} -->
<p class="has-mono-font-family" style="font-size:0.72rem;letter-spacing:0.14em;text-transform:uppercase"><a href="/work">Full archive →</a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:query {"queryId":0,"query":{"perPage":6,"pages":0,"offset":0,"postType":"work","order":"desc","orderBy":"date","inherit":false},"align":"wide"} -->
<div class="wp-block-query alignwide"><!-- wp:post-template {"style":{"spacing":{"blockGap":"var:preset|spacing|30"}},"layout":{"type":"grid","columnCount":3}} -->
<!-- wp:group {"className":"latent-card","style":{"spacing":{"blockGap":"0.9rem"},"border":{"color":"var:preset|color|line","width":"1px"},"spacing":{"padding":{"top":"0.9rem","right":"0.9rem","bottom":"0.9rem","left":"0.9rem"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group latent-card has-border-color" style="border-color:var(--wp--preset--color--line);border-width:1px;padding-top:0.9rem;padding-right:0.9rem;padding-bottom:0.9rem;padding-left:0.9rem"><!-- wp:post-featured-image {"isLink":true,"aspectRatio":"4/5","style":{"border":{"radius":"2px"}}} /-->

<!-- wp:group {"style":{"spacing":{"blockGap":"0.5rem"}},"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"center"}} -->
<div class="wp-block-group"><!-- wp:post-title {"isLink":true,"fontSize":"large","style":{"typography":{"fontWeight":"500"}}} /-->

<!-- wp:post-terms {"term":"work_category","style":{"typography":{"fontSize":"0.68rem","letterSpacing":"0.12em","textTransform":"uppercase"},"color":{"text":"var:preset|color|contrast-2"}},"fontFamily":"mono"} /--></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
<!-- /wp:post-template -->

<!-- wp:query-no-results -->
<!-- wp:paragraph {"style":{"color":{"text":"var:preset|color|contrast-2"}}} -->
<p class="has-text-color" style="color:var(--wp--preset--color--contrast-2)">No work yet. Add your first piece under <strong>Work → Add New</strong> — set a featured image and a category, and it appears here automatically.</p>
<!-- /wp:paragraph -->
<!-- /wp:query-no-results --></div>
<!-- /wp:query -->
</section>
<!-- /wp:group -->

<?php
/**
 * Fallback index — v2.1 Relief.
 * Used when no more specific template matches. Same TOC pattern
 * as the poem archive so search/category/tag queries land here gracefully.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header(); ?>

<header class="page-head">
    <div class="page-head__in">
        <h1 class="page-head__title">கவிதைகள்</h1>
        <p class="page-head__sub">Poems</p>
    </div>
</header>

<div style="max-width: 900px; margin: 0 auto; padding: var(--pad-section) var(--pad-x);">
    <?php if ( have_posts() ) : ?>
        <ul class="toc" style="margin-top: 0;">
            <?php
            $ta_nums = [ '௧','௨','௩','௪','௫','௬','௭','௮','௯','௧௦','௧௧','௧௨','௧௩','௧௪','௧௫','௧௬','௧௭','௧௮','௧௯','௨௦' ];
            $i = 0;
            while ( have_posts() ) : the_post();
                $w_year = get_post_meta( get_the_ID(), '_kv_year', true );
                $w_note = get_post_meta( get_the_ID(), '_kv_note', true );
                ?>
                <li class="toc__row">
                    <span class="toc__num"><?php echo esc_html( $ta_nums[ $i ] ?? ($i + 1) ); ?></span>
                    <span class="toc__year"><?php echo esc_html( $w_year ?: get_the_date( 'Y' ) ); ?></span>
                    <span class="toc__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></span>
                    <span class="toc__first"><?php echo esc_html( $w_note ?: wp_trim_words( wp_strip_all_tags( get_the_excerpt() ?: get_the_content() ), 12, '…' ) ); ?></span>
                </li>
            <?php $i++; endwhile; ?>
        </ul>

        <?php
        $links = paginate_links( [
            'prev_text' => '←',
            'next_text' => '→',
        ] );
        if ( $links ) echo '<nav class="pagination" aria-label="Page navigation">' . $links . '</nav>';
        ?>
    <?php else : ?>
        <p style="font-family:var(--serif-ta); font-style:italic; color:var(--ink-mute); text-align:center; padding:4rem 0;">இங்கே இன்னும் கவிதைகள் இல்லை.</p>
    <?php endif; ?>
</div>

<?php get_footer(); ?>

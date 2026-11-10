<?php
/**
 * Fallback template — single posts, archives, search results.
 * The homepage uses front-page.php (the link list).
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header(); ?>

<main id="main" class="page">

    <?php if ( have_posts() ) : ?>

        <?php if ( is_home() && ! is_front_page() ) : ?>
            <h1 style="font-family: var(--display); font-size: clamp(2.4rem, 6vw, 4rem); font-weight: 700; letter-spacing: -0.03em; line-height: 0.95; color: var(--fg); margin-bottom: 2.5rem;">
                <?php single_post_title(); ?>
            </h1>
        <?php elseif ( is_archive() ) : ?>
            <h1 style="font-family: var(--display); font-size: clamp(2.4rem, 6vw, 4rem); font-weight: 700; letter-spacing: -0.03em; line-height: 0.95; color: var(--fg); margin-bottom: 2.5rem;">
                <?php the_archive_title(); ?>
            </h1>
        <?php elseif ( is_search() ) : ?>
            <h1 style="font-family: var(--display); font-size: clamp(2.4rem, 6vw, 4rem); font-weight: 700; letter-spacing: -0.03em; line-height: 0.95; color: var(--fg); margin-bottom: 2.5rem;">
                Search: <span class="signal"><?php echo esc_html( get_search_query() ); ?></span>
            </h1>
        <?php endif; ?>

        <?php while ( have_posts() ) : the_post(); ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>
                     style="padding: 2rem 0; border-bottom: 1px solid var(--rule);">
                <p style="font-family: var(--mono); font-size: 10px; letter-spacing: 0.22em; text-transform: uppercase; color: var(--mute); margin-bottom: 0.6rem;">
                    <?php echo esc_html( get_the_date() ); ?>
                </p>
                <h2 style="font-family: var(--display); font-size: 1.6rem; font-weight: 500; letter-spacing: -0.01em; margin-bottom: 0.8rem;">
                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                </h2>
                <div style="font-family: var(--mono); font-size: 14px; line-height: 1.7; color: var(--fg); opacity: 0.85;">
                    <?php
                    if ( is_singular() ) {
                        the_content();
                    } else {
                        the_excerpt();
                    }
                    ?>
                </div>
                <?php if ( ! is_singular() ) : ?>
                    <a href="<?php the_permalink(); ?>"
                       style="display: inline-block; margin-top: 1.5rem; font-family: var(--mono); font-size: 11px; letter-spacing: 0.22em; text-transform: uppercase; color: var(--signal); border-bottom: 1px solid var(--signal); padding-bottom: 2px;">
                        Read ↗
                    </a>
                <?php endif; ?>
            </article>
        <?php endwhile; ?>

        <nav style="display: flex; justify-content: space-between; align-items: center; margin-top: 3rem; font-family: var(--mono); font-size: 11px; letter-spacing: 0.22em; text-transform: uppercase;">
            <?php
            echo paginate_links( [
                'prev_text' => '← Prev',
                'next_text' => 'Next →',
            ] );
            ?>
        </nav>

    <?php else : ?>

        <div style="text-align: center; padding: 6rem 0;">
            <h1 style="font-family: var(--display); font-size: clamp(3rem, 7vw, 5rem); font-weight: 700; letter-spacing: -0.04em; margin-bottom: 1.5rem;">
                Void<span style="color: var(--signal);">.</span>
            </h1>
            <p style="font-family: var(--mono); font-size: 12px; letter-spacing: 0.22em; text-transform: uppercase; color: var(--mute); margin-bottom: 2.5rem;">
                No content found at this signal.
            </p>
            <a href="<?php echo esc_url( home_url() ); ?>"
               style="font-family: var(--mono); font-size: 11px; letter-spacing: 0.28em; text-transform: uppercase; color: var(--signal); border: 1px solid var(--signal); padding: 1rem 1.5rem; display: inline-block;">
                ▶ Return to hub
            </a>
        </div>

    <?php endif; ?>

</main>

<?php get_footer(); ?>

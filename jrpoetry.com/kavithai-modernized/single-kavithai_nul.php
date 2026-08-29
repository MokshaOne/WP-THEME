<?php
/**
 * Single book — v2.1 Relief.
 * NOTE: PDF download / individual-poem TOC features arrive when
 *       the importer change lands. For now, the book post is shown
 *       with its description and cover.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header(); the_post();

$pid    = get_the_ID();
$year   = get_post_meta( $pid, '_kv_year', true );
$pub    = get_post_meta( $pid, '_kv_publisher', true );
$type   = get_post_meta( $pid, '_kv_type', true );
// CP4: per-book PDF + original .docx download.
$docx_id  = get_post_meta( $pid, '_kv_book_docx_id', true );
$docx_url = $docx_id ? wp_get_attachment_url( (int) $docx_id ) : '';
$pdf_url  = add_query_arg( 'kv_book_pdf', $pid, home_url( '/' ) );
$type_labels = [
    'kavithai' => 'கவிதை தொகுப்பு',
    'kathai'   => 'சிறுகதைகள்',
    'novel'    => 'நாவல்',
    'katture'  => 'கட்டுரைகள்',
    'other'    => 'மற்றவை',
];
$type_label = $type_labels[ $type ] ?? '';
$has_thumb  = has_post_thumbnail();
?>

<header class="page-head" style="padding-bottom: 1rem;">
    <div class="page-head__in">
        <p class="page-head__crumb">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>">முகப்பு</a>
            <span class="sep">·</span>
            <a href="<?php echo esc_url( get_post_type_archive_link( 'kavithai_nul' ) ); ?>">நூல்கள்</a>
            <span class="sep">·</span>
            <?php the_title(); ?>
        </p>
    </div>
</header>

<section style="max-width: var(--max-page); margin: 0 auto; padding: var(--pad-section) var(--pad-x); display: grid; grid-template-columns: 1fr 1.4fr; gap: clamp(2rem, 6vw, 6rem); align-items: start;">
    <div>
        <div class="book__cover <?php echo $has_thumb ? 'book__cover--photo' : 'book__cover--plain'; ?>" style="aspect-ratio: 2/3; position: relative;">
            <?php if ( $has_thumb ) {
                the_post_thumbnail( 'book-cover', [
                    'style' => 'position:absolute;inset:0;width:100%;height:100%;object-fit:cover; ' . kv_focal_style( $pid ),
                ] );
            } ?>
            <span class="book__cover-title"><?php the_title(); ?></span>
        </div>
    </div>

    <div>
        <p class="hero__honorific" style="margin-bottom: 0.8rem;">
            <?php if ( $type_label ) echo esc_html( $type_label ); ?>
            <?php if ( $type_label && $pub ) echo ' <span class="sep">·</span> '; ?>
            <?php if ( $pub ) echo esc_html( $pub ); ?>
        </p>
        <h1 style="font-family: var(--serif-ta); font-size: clamp(2.8rem, 5.6vw, 4.4rem); font-weight: 300; line-height: 1.05; color: var(--ink); letter-spacing: -0.015em; margin-bottom: 1.6rem;">
            <?php the_title(); ?>
        </h1>

        <?php if ( $year ) : ?>
        <div style="display: grid; grid-template-columns: repeat(2, auto); gap: 2rem; padding: 1.4rem 0; margin-bottom: 2rem; border-top: 1px solid var(--rule); border-bottom: 1px solid var(--rule); font-family: var(--serif-la); font-size: 0.72rem; letter-spacing: 0.28em; text-transform: uppercase; color: var(--ink-mute); font-weight: 500;">
            <div>Published<span style="display:block; font-family:var(--serif-ta); font-size:1.1rem; letter-spacing:0; text-transform:none; color:var(--ink); margin-top:0.3rem; font-feature-settings:'lnum','tnum';"><?php echo esc_html( $year ); ?></span></div>
            <?php if ( $type_label ) : ?>
                <div>Type<span style="display:block; font-family:var(--serif-ta); font-size:1.05rem; letter-spacing:0; text-transform:none; color:var(--ink); margin-top:0.3rem; font-style:italic;"><?php echo esc_html( $type_label ); ?></span></div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div style="font-family: var(--serif-ta); font-size: 1.15rem; line-height: 1.9; color: var(--ink-soft); margin-bottom: 2rem;">
            <?php the_content(); ?>
        </div>

        <!-- CP4: per-book PDF + original .docx -->
        <div class="book-actions">
            <a class="book-action book-action--primary" href="<?php echo esc_url( $pdf_url ); ?>" target="_blank" rel="noopener">
                <span class="icon">⬇</span> Download as PDF
            </a>
            <a class="book-action" href="<?php echo esc_url( $pdf_url ); ?>" target="_blank" rel="noopener">
                <span class="icon">⊙</span> View as PDF
            </a>
            <?php if ( $docx_url ) : ?>
                <a class="book-action" href="<?php echo esc_url( $docx_url ); ?>" download>
                    <span class="icon">⊞</span> Original .docx
                </a>
            <?php endif; ?>
        </div>
        <p style="font-family: var(--serif-la); font-style: italic; font-size: 0.85rem; color: var(--ink-mute); margin-top: 1.2rem;">
            The full book is downloadable as PDF; individual poems are also linked below.
        </p>
    </div>
</section>

<style>
.book-actions {
    display: flex; gap: 0.8rem; flex-wrap: wrap;
    margin-top: 1.5rem;
}
.book-action {
    display: inline-flex; align-items: center; gap: 0.8rem;
    padding: 1rem 1.6rem;
    border: 1px solid var(--ink);
    font-family: var(--serif-la);
    font-size: 0.74rem; letter-spacing: 0.32em;
    text-transform: uppercase; color: var(--ink);
    font-weight: 500;
    background: var(--paper);
    transition: background .25s, color .25s;
}
.book-action:hover { background: var(--ink); color: var(--paper); }
.book-action--primary {
    background: var(--oxblood); color: var(--paper);
    border-color: var(--oxblood);
}
.book-action--primary:hover { background: var(--oxblood-deep); border-color: var(--oxblood-deep); }
.book-action .icon {
    font-family: var(--serif-ta);
    font-size: 1.2rem; letter-spacing: 0; text-transform: none;
}
</style>

<?php
// Poems from this book (if any)
$book_title = get_the_title();
$poems = new WP_Query( [
    'post_type'      => 'kavithai_gedicht',
    'posts_per_page' => -1,
    'meta_key'       => '_kv_book_title',
    'meta_value'     => $book_title,
    'orderby'        => 'meta_value_num',
    'order'          => 'ASC',
    'no_found_rows'  => true,
] );
?>

<?php if ( $poems->have_posts() ) : echo kv_asterism( 'break' ); ?>
<section class="toc-section">
    <?php echo kv_eyebrow_bi( 'உள்ளடக்கம்', 'Table of Contents' ); ?>
    <ul class="toc">
        <?php
        $ta_nums = [ '௧', '௨', '௩', '௪', '௫', '௬', '௭', '௮', '௯', '௧௦', '௧௧', '௧௨', '௧௩', '௧௪', '௧௫', '௧௬', '௧௭', '௧௮', '௧௯', '௨௦', '௨௧', '௨௨', '௨௩', '௨௪', '௨௫', '௨௬', '௨௭', '௨௮', '௨௯', '௩௦' ];
        $i = 0;
        while ( $poems->have_posts() ) : $poems->the_post();
            $b_num  = get_post_meta( get_the_ID(), '_kv_book_num', true );
            $b_note = get_post_meta( get_the_ID(), '_kv_note', true );
            ?>
            <li class="toc__row">
                <span class="toc__num"><?php echo esc_html( $ta_nums[ $i ] ?? ($i + 1) ); ?></span>
                <span class="toc__year"><?php echo esc_html( $b_num ?: '—' ); ?></span>
                <span class="toc__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></span>
                <span class="toc__first"><?php echo esc_html( $b_note ?: wp_trim_words( wp_strip_all_tags( get_the_content() ), 10, '…' ) ); ?></span>
            </li>
        <?php $i++; endwhile; wp_reset_postdata(); ?>
    </ul>
</section>
<?php endif; ?>

<?php get_footer(); ?>

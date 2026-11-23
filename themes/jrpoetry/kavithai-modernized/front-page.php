<?php
/**
 * Front page — South Asian Editorial direction (v2.1).
 *
 * Sections (toggleable via Customizer):
 *   1. Hero (with relief backdrop + portrait + journey)
 *   2. Featured poem (with marginalia, drop cap, asterism breaks)
 *   3. Selected works (TOC style)
 *   4. Books grid
 *   5. Awards line list
 *   6. Pull quote
 *   7. About (4-section bilingual)
 *   8. Contact
 */
if ( ! defined( 'ABSPATH' ) ) exit;

get_header();

$poem_count_obj = wp_count_posts( 'kavithai_gedicht' );
$book_count_obj = wp_count_posts( 'kavithai_nul' );
$award_count_obj = wp_count_posts( 'kavithai_virudu' );
$poem_count  = (int) ( $poem_count_obj->publish ?? 0 );
$book_count  = (int) ( $book_count_obj->publish ?? 0 );
$award_count = (int) ( $award_count_obj->publish ?? 0 );

// Featured poem — prefer the explicitly marked one, fall back to most recent.
$q_featured = new WP_Query( [
    'post_type'      => 'kavithai_gedicht',
    'posts_per_page' => 1,
    'post_status'    => 'publish',
    'meta_query'     => [ [ 'key' => '_kv_featured', 'value' => '1' ] ],
    'no_found_rows'  => true,
] );
if ( ! $q_featured->have_posts() ) {
    $q_featured = new WP_Query( [
        'post_type'      => 'kavithai_gedicht',
        'posts_per_page' => 1,
        'post_status'    => 'publish',
        'no_found_rows'  => true,
    ] );
}

$q_works  = new WP_Query( [ 'post_type' => 'kavithai_gedicht', 'posts_per_page' => 6, 'offset' => 1, 'post_status' => 'publish', 'no_found_rows' => true ] );
$q_books  = new WP_Query( [ 'post_type' => 'kavithai_nul', 'posts_per_page' => 6, 'orderby' => 'meta_value_num', 'meta_key' => '_kv_year', 'order' => 'ASC', 'no_found_rows' => true ] );
$q_awards = new WP_Query( [ 'post_type' => 'kavithai_virudu', 'posts_per_page' => 5, 'orderby' => 'meta_value_num', 'meta_key' => '_kv_year', 'order' => 'DESC', 'no_found_rows' => true ] );

$portrait_url = kv_portrait_url( 'poet-portrait' );
?>

<!-- ═════ HERO ═════ -->
<?php if ( get_option( 'kv_show_hero', '1' ) !== '0' ) : ?>
<header class="hero">
    <?php echo kv_relief( 'வெற்றிச்செல்வி', 'hero' ); ?>

    <div class="hero__text">
        <div class="hero__above">
            <p class="hero__honorific">
                <?php if ( $award_count > 0 ) : ?>
                    கவிஞர் <span class="sep">·</span> <span class="ta">பாவலர்மணி</span> <span class="sep">·</span> Tamil Poet
                <?php else : ?>
                    கவிஞர் <span class="sep">·</span> Tamil Poet
                <?php endif; ?>
            </p>
        </div>

        <h1 class="hero__name"><?php echo wp_kses( nl2br( esc_html( kv_name() ) ), [ 'br' => [] ] ); ?></h1>

        <?php $subname = kv_subname(); if ( $subname ) : ?>
            <p class="hero__birth"><?php echo esc_html( $subname ); ?></p>
        <?php endif; ?>

        <span class="hero__rule"></span>

        <?php $verse = kv_opt( 'kv_hero_verse', '' ); if ( $verse ) : ?>
            <p class="hero__intro"><?php echo nl2br( esc_html( $verse ) ); ?></p>
        <?php else : ?>
            <p class="hero__intro">
                ஈழத்தின் வேர்களைச் சுமந்து,<br>
                அகவல்களில் தமிழ் மரபை வாழ்விக்கிறேன்.
            </p>
        <?php endif; ?>

        <div class="hero__journey">
            <span class="label">Born</span>
            <span class="place"><?php echo esc_html( kv_opt( 'kv_birthplace', 'அச்சுவேலி, யாழ்ப்பாணம்' ) ); ?></span>
            <span class="label">Now</span>
            <span class="place"><?php echo esc_html( kv_location() ); ?></span>
            <span class="label">Form</span>
            <span class="place">அகவல் · classical akaval verse</span>
        </div>
    </div>

    <div class="hero__portrait">
        <?php if ( $portrait_url ) : ?>
            <img src="<?php echo esc_url( $portrait_url ); ?>" alt="<?php echo esc_attr( kv_name() ); ?>" style="object-position: var(--focal-x, 50%) var(--focal-y, 35%);">
        <?php else : ?>
            <div class="hero__portrait-empty"><span class="label">Portrait · 3:5</span></div>
        <?php endif; ?>
        <span class="hero__portrait-caption">
            <span><?php echo esc_html( kv_location() ); ?></span>
            <span class="la">MMXXIV</span>
        </span>
    </div>
</header>
<?php endif; ?>

<!-- ═════ FEATURED POEM ═════ -->
<?php if ( get_option( 'kv_show_daily', '1' ) !== '0' && $q_featured->have_posts() ) : $q_featured->the_post();
    echo kv_asterism( 'break' );
    $pid       = get_the_ID();
    $year      = get_post_meta( $pid, '_kv_year', true );
    $loc       = get_post_meta( $pid, '_kv_origin_loc', true );
    $ded       = get_post_meta( $pid, '_kv_dedication', true );
    $title_en  = get_post_meta( $pid, '_kv_title_en', true );

    // Drop cap: first Tamil grapheme cluster (consonant + dependent vowel).
    $content_raw = wp_strip_all_tags( get_the_content() );
    $content_raw = trim( preg_replace( '/^\s+/', '', $content_raw ) );
    $dropcap     = mb_substr( $content_raw, 0, 2 );
    $rest        = mb_substr( $content_raw, 2 );

    // Split stanzas on blank lines, then lines via <br>.
    $stanzas = preg_split( '/\n\s*\n+/u', $rest );
?>
<section class="featured" id="poem">
    <?php echo kv_relief( 'க', 'featured' ); ?>

    <div class="featured__in">
        <?php echo kv_eyebrow_bi( 'இன்றைய வரிகள்', "Today's Lines" ); ?>

        <h2 class="featured__title"><?php the_title(); ?></h2>
        <?php if ( $title_en ) : ?>
            <p class="featured__subtitle"><?php echo esc_html( $title_en ); ?></p>
        <?php endif; ?>

        <div class="poem-grid">
            <aside class="poem-margin-left">
                <?php if ( $year ) : ?>
                    <span class="line"><span class="label">Written</span><span class="value"><?php echo esc_html( $year ); ?></span></span>
                <?php endif; ?>
                <?php if ( $loc ) : ?>
                    <span class="line"><span class="label">Place</span><span class="value"><?php echo esc_html( $loc ); ?></span></span>
                <?php endif; ?>
                <?php if ( $ded ) : ?>
                    <span class="line"><span class="label">For</span><span class="value"><?php echo esc_html( $ded ); ?></span></span>
                <?php endif; ?>
            </aside>

            <div class="poem-body">
                <?php
                $first = true;
                foreach ( $stanzas as $i => $stanza ) :
                    $stanza = trim( $stanza );
                    if ( $stanza === '' ) continue;
                    $lines = explode( "\n", $stanza );
                    ?>
                    <p>
                        <?php
                        $line_idx = 0;
                        foreach ( $lines as $line ) :
                            $line = trim( $line );
                            if ( $line === '' ) continue;
                            if ( $first && $line_idx === 0 ) {
                                // Inject drop cap on first line of first stanza.
                                $line_head = mb_substr( $line, 0, 2 );
                                $line_tail = mb_substr( $line, 2 );
                                echo '<span class="dropcap">' . esc_html( $line_head ) . '</span>' . esc_html( $line_tail );
                                $first = false;
                            } else {
                                echo esc_html( $line );
                            }
                            if ( $line_idx < count( $lines ) - 1 ) echo '<br>';
                            $line_idx++;
                        endforeach;
                        ?>
                    </p>
                    <?php if ( $i < count( $stanzas ) - 1 ) echo kv_asterism( 'stanza' ); ?>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="poem-end"><?php echo kv_seal( 'sm' ); ?></div>
        <div class="poem-footer">
            <p class="poem-footer__cite"><span class="ta"><?php echo esc_html( kv_name() ); ?></span><?php if ( $year ) echo '<span class="la">, ' . esc_html( $year ) . '</span>'; ?></p>
        </div>
        <div class="poem-more">
            <a href="<?php the_permalink(); ?>">முழுமையாக படிக்க <span class="la">→</span></a>
        </div>
    </div>
</section>
<?php wp_reset_postdata(); endif; ?>

<!-- ═════ SELECTED WORKS (TOC) ═════ -->
<?php if ( get_option( 'kv_show_recent', '1' ) !== '0' && $q_works->have_posts() ) : echo kv_asterism( 'break' ); ?>
<section class="toc-section" id="works">
    <?php echo kv_eyebrow_bi( 'தேர்ந்த படைப்புகள்', 'Selected Works' ); ?>

    <ul class="toc">
        <?php
        $ta_nums = [ '௧', '௨', '௩', '௪', '௫', '௬', '௭', '௮', '௯', '௧௦' ];
        $i = 0;
        while ( $q_works->have_posts() ) : $q_works->the_post();
            $w_year  = get_post_meta( get_the_ID(), '_kv_year', true );
            $w_note  = get_post_meta( get_the_ID(), '_kv_note', true );
            ?>
            <li class="toc__row">
                <span class="toc__num"><?php echo esc_html( $ta_nums[ $i ] ?? ($i + 1) ); ?></span>
                <span class="toc__year"><?php echo esc_html( $w_year ?: get_the_date( 'Y' ) ); ?></span>
                <span class="toc__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></span>
                <span class="toc__first"><?php echo esc_html( $w_note ?: wp_trim_words( wp_strip_all_tags( get_the_excerpt() ?: get_the_content() ), 10, '…' ) ); ?></span>
            </li>
            <?php $i++;
        endwhile; wp_reset_postdata();
        ?>
    </ul>

    <p style="text-align:center; margin-top:2.5rem;">
        <a href="<?php echo esc_url( get_post_type_archive_link( 'kavithai_gedicht' ) ); ?>" class="smallcaps" style="border-bottom:1px solid var(--rule); padding-bottom:4px;">அனைத்து கவிதைகள் →</a>
    </p>
</section>
<?php endif; ?>

<!-- ═════ BOOKS ═════ -->
<?php if ( $q_books->have_posts() ) : echo kv_asterism( 'break' ); ?>
<section class="books" id="books">
    <?php echo kv_eyebrow_bi( 'நூல்கள்', 'Published Works' ); ?>

    <div class="books__grid">
        <?php
        $i = 0;
        while ( $q_books->have_posts() ) : $q_books->the_post();
            $b_year = get_post_meta( get_the_ID(), '_kv_year', true );
            $b_type = get_post_meta( get_the_ID(), '_kv_type', true );
            $type_labels = [
                'kavithai' => 'Poetry', 'kathai' => 'Stories',
                'novel' => 'Novel', 'katture' => 'Essays', 'other' => '',
            ];
            $type_label = $type_labels[ $b_type ] ?? '';
            $has_thumb  = has_post_thumbnail();
            ?>
            <a class="book" href="<?php the_permalink(); ?>">
                <?php if ( $i < 10 ) echo '<span class="book__num">' . esc_html( $ta_nums[ $i ] ?? ($i + 1) ) . '</span>'; ?>

                <div class="book__cover <?php echo $has_thumb ? 'book__cover--photo' : 'book__cover--plain'; ?>">
                    <?php if ( $has_thumb ) {
                        the_post_thumbnail( 'book-cover', [ 'style' => kv_focal_style( get_the_ID() ) ] );
                    } ?>
                    <span class="book__cover-title"><?php the_title(); ?></span>
                </div>

                <h3 class="book__title"><?php the_title(); ?></h3>
                <p class="book__meta">
                    <?php if ( $b_year ) echo '<span class="la">' . esc_html( $b_year ) . '</span>'; ?>
                    <?php if ( $b_year && $type_label ) echo '<span class="dot">·</span>'; ?>
                    <?php if ( $type_label ) echo esc_html( $type_label ); ?>
                </p>
            </a>
        <?php $i++; endwhile; wp_reset_postdata(); ?>
    </div>
</section>
<?php endif; ?>

<!-- ═════ AWARDS ═════ -->
<?php if ( $q_awards->have_posts() ) : echo kv_asterism( 'break' ); ?>
<section class="awards" id="awards">
    <?php echo kv_eyebrow_bi( 'விருதுகள்', 'Honours & Recognition' ); ?>

    <ul class="awards__list">
        <?php while ( $q_awards->have_posts() ) : $q_awards->the_post();
            $a_year = get_post_meta( get_the_ID(), '_kv_year', true );
            $a_org  = get_post_meta( get_the_ID(), '_kv_org', true );
            ?>
            <li class="award">
                <span class="award__year"><?php echo esc_html( $a_year ); ?></span>
                <span class="award__name"><?php the_title(); ?></span>
                <?php if ( $a_org ) : ?>
                    <span class="award__org"><?php echo esc_html( $a_org ); ?></span>
                <?php else : ?>
                    <span></span>
                <?php endif; ?>
            </li>
        <?php endwhile; wp_reset_postdata(); ?>
    </ul>
</section>
<?php endif; ?>

<!-- ═════ PULL QUOTE ═════ -->
<?php echo kv_asterism( 'break' ); $quote = kv_opt( 'kv_diaspora_quote', '' ); ?>
<section class="pull">
    <?php echo kv_relief( 'அகவல்', 'pull' ); ?>
    <div class="pull__in">
        <span class="pull__mark">❦</span>
        <blockquote>
            <?php if ( $quote ) {
                echo nl2br( esc_html( $quote ) );
            } else { ?>
                ஒவ்வொரு சொல்லும்<br>
                என் வேர்களுக்கான<br>
                சமர்ப்பணம்.
            <?php } ?>
        </blockquote>
        <p class="pull__cite"><span class="ta"><?php echo esc_html( kv_name() ); ?></span></p>
    </div>
</section>

<!-- ═════ ABOUT (4-section bilingual) ═════ -->
<?php if ( get_option( 'kv_show_about', '1' ) !== '0' ) : echo kv_asterism( 'break' ); ?>
<section class="about" id="about">
    <?php echo kv_relief( 'வே', 'about' ); ?>

    <div class="about__in">
        <div class="about__title">
            <h2 class="about__title-ta">ஒரு வாழ்க்கை, வார்த்தைகளில்</h2>
            <p class="about__title-la">A Life · In Words</p>
        </div>

        <?php
        // Pull the 4 about sections from Customizer; fall back to defaults.
        $about_sections = [
            [ 'ta' => 'அடித்தளம்',           'la' => 'Foundation',           'key' => 'kv_about_foundation', 'default' => 'இறையருளும் குருவருளும் துணையாக நிற்க, தமிழ்த்தாயை வணங்கி என் சிந்தனைகளை எழுத்தாக்குகிறேன். ஒவ்வொரு சொல்லும் என் வேர்களுக்கான சமர்ப்பணம்.' ],
            [ 'ta' => 'பூர்வீகமும் பயணமும்', 'la' => 'Origin & Journey',     'key' => 'kv_about_journey',    'default' => 'ஈழத்தின் அச்சுவேலியில் (யாழ்ப்பாணம்) பிறந்து, ஆசிரியராகப் பணியாற்றிய நான், போர்ச்சூழல் காரணமாக குடும்பத்துடன் இடம்பெயர்ந்து ஆஸ்திரியாவை எனது புதிய வாழ்விடமாகக் கொண்டேன்.' ],
            [ 'ta' => 'இலக்கியப் பயணம்',     'la' => 'Literary Path',        'key' => 'kv_about_literary',   'default' => 'ஆரம்பத்தில் சிறுகதைகள் மற்றும் புதுக்கவிதைகள் ஊடாக எனது எண்ணங்களைப் பதிவு செய்தேன். எழுத்து எனக்கு ஒரு வடிகாலாகவும், அடையாளமாகவும் அமைந்தது.' ],
            [ 'ta' => 'மரபை நோக்கிய நகர்வு', 'la' => 'Return to the Tradition', 'key' => 'kv_about_tradition', 'default' => 'மரபுக்கவிதை மீதான தாகத்தால், பாட்டரசர் கி. பாரதிதாசன் ஐயாவின் வழிகாட்டலில் யாப்பிலக்கணம் கற்றேன். இன்று பாரம்பரிய அகவல் பாக்களைப் படைத்து, தமிழ் மரபின் வேர்களை என் கவிதைகளில் நிலைநிறுத்துகிறேன்.' ],
        ];
        foreach ( $about_sections as $sec ) : $body = kv_opt( $sec['key'], $sec['default'] ); ?>
            <div class="about__section">
                <div class="about__sec-head">
                    <h3 class="about__sec-ta"><?php echo esc_html( $sec['ta'] ); ?></h3>
                    <p class="about__sec-la"><?php echo esc_html( $sec['la'] ); ?></p>
                </div>
                <div class="about__sec-body"><?php echo nl2br( esc_html( $body ) ); ?></div>
            </div>
        <?php endforeach; ?>

        <?php $about_page = get_page_by_path( 'ennai-parri' ); if ( $about_page ) : ?>
            <p style="text-align:center; margin-top:3rem;">
                <a href="<?php echo esc_url( get_permalink( $about_page->ID ) ); ?>" class="smallcaps" style="border-bottom:1px solid var(--rule); padding-bottom:4px;">முழுமையாக படிக்க →</a>
            </p>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<!-- ═════ CONTACT ═════ -->
<?php if ( get_option( 'kv_show_contact', '1' ) !== '0' ) : ?>
<section style="text-align:center; padding: var(--pad-section) var(--pad-x);">
    <?php echo kv_eyebrow_bi( 'தொடர்பு', 'Get in Touch' ); ?>
    <p style="font-family:var(--serif-ta); font-size:1.18rem; color:var(--ink-soft); margin-bottom:2.5rem; line-height:1.8;">
        ஒரு கேள்வி, ஒரு நன்றி, அல்லது ஒரு கவிதை —<br>எல்லாமே வரவேற்கிறேன்.
    </p>
    <?php $e = kv_email(); if ( $e ) : ?>
        <a href="mailto:<?php echo esc_attr( $e ); ?>" style="font-family:var(--serif-la); font-style:italic; font-size:clamp(1.5rem, 3vw, 2.2rem); color:var(--oxblood); border-bottom:1px solid var(--ochre); padding-bottom:6px; display:inline-block;">
            <?php echo esc_html( $e ); ?>
        </a>
    <?php else : ?>
        <a href="<?php echo esc_url( home_url( '/thodarbu/' ) ); ?>" class="smallcaps" style="border-bottom:1px solid var(--rule); padding-bottom:4px;">தொடர்பு படிவம் →</a>
    <?php endif; ?>
</section>
<?php endif; ?>

<?php get_footer(); ?>

<?php
/**
 * Template Name: என்னை பற்றி
 * About page — v2.1 Relief. Full 4-section bio + timeline + influences + awards.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header(); the_post();

$awards = new WP_Query( [
    'post_type'      => 'kavithai_virudu',
    'posts_per_page' => -1,
    'orderby'        => 'meta_value_num',
    'meta_key'       => '_kv_year',
    'order'          => 'DESC',
    'no_found_rows'  => true,
] );

$portrait_url = has_post_thumbnail() ? get_the_post_thumbnail_url( null, 'poet-portrait' ) : kv_portrait_url( 'poet-portrait' );
?>

<header class="page-head">
    <?php echo kv_relief( 'வாழ்வு', 'featured' ); ?>
    <div class="page-head__in">
        <p class="page-head__crumb">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>">முகப்பு</a>
            <span class="sep">·</span>என்னை பற்றி
        </p>
        <h1 class="page-head__title">ஒரு வாழ்க்கை,<br>வார்த்தைகளில்</h1>
        <p class="page-head__sub">A Life in Words <span style="color:var(--ochre);margin:0 0.5em;">·</span> <span class="la"><?php echo esc_html( kv_name() ); ?></span></p>
    </div>
</header>

<!-- 4-section bilingual bio -->
<section class="about">
    <?php echo kv_relief( 'வே', 'about' ); ?>
    <div class="about__in">
        <?php
        // Pull bio sections from settings; fall back to defaults.
        $about_sections = [
            [ 'ta' => 'அடித்தளம்',           'la' => 'Foundation',              'key' => 'kv_about_foundation', 'default' => 'இறையருளும் குருவருளும் துணையாக நிற்க, தமிழ்த்தாயை வணங்கி என் சிந்தனைகளை எழுத்தாக்குகிறேன். ஒவ்வொரு சொல்லும் என் வேர்களுக்கான சமர்ப்பணம்.' ],
            [ 'ta' => 'பூர்வீகமும் பயணமும்', 'la' => 'Origin & Journey',        'key' => 'kv_about_journey',    'default' => 'ஈழத்தின் அச்சுவேலியில் (யாழ்ப்பாணம்) பிறந்து, ஆசிரியராகப் பணியாற்றிய நான், போர்ச்சூழல் காரணமாக குடும்பத்துடன் இடம்பெயர்ந்து ஆஸ்திரியாவை எனது புதிய வாழ்விடமாகக் கொண்டேன்.' ],
            [ 'ta' => 'இலக்கியப் பயணம்',     'la' => 'Literary Path',           'key' => 'kv_about_literary',   'default' => 'ஆரம்பத்தில் சிறுகதைகள் மற்றும் புதுக்கவிதைகள் ஊடாக எனது எண்ணங்களைப் பதிவு செய்தேன். எழுத்து எனக்கு ஒரு வடிகாலாகவும், அடையாளமாகவும் அமைந்தது.' ],
            [ 'ta' => 'மரபை நோக்கிய நகர்வு', 'la' => 'Return to the Tradition', 'key' => 'kv_about_tradition', 'default' => 'மரபுக்கவிதை மீதான தாகத்தால், பாட்டரசர் கி. பாரதிதாசன் ஐயாவின் வழிகாட்டலில் யாப்பிலக்கணம் கற்றேன். இன்று பாரம்பரிய அகவல் பாக்களைப் படைத்து, தமிழ் மரபின் வேர்களை என் கவிதைகளில் நிலைநிறுத்துகிறேன்.' ],
        ];
        foreach ( $about_sections as $sec ) :
            $body = kv_opt( $sec['key'], $sec['default'] );
            ?>
            <div class="about__section">
                <div class="about__sec-head">
                    <h3 class="about__sec-ta"><?php echo esc_html( $sec['ta'] ); ?></h3>
                    <p class="about__sec-la"><?php echo esc_html( $sec['la'] ); ?></p>
                </div>
                <div class="about__sec-body"><?php echo nl2br( esc_html( $body ) ); ?></div>
            </div>
        <?php endforeach; ?>

        <?php $page_content = get_the_content();
        if ( trim( wp_strip_all_tags( $page_content ) ) !== '' ) : ?>
            <div class="about__section">
                <div class="about__sec-head">
                    <h3 class="about__sec-ta">மேலும்</h3>
                    <p class="about__sec-la">More</p>
                </div>
                <div class="about__sec-body"><?php the_content(); ?></div>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Pull quote -->
<?php echo kv_asterism( 'break' ); ?>
<section class="pull">
    <?php echo kv_relief( 'மரபு', 'pull' ); ?>
    <div class="pull__in">
        <span class="pull__mark">❦</span>
        <?php $quote = kv_opt( 'kv_diaspora_quote', '' ); ?>
        <blockquote>
            <?php if ( $quote ) {
                echo nl2br( esc_html( $quote ) );
            } else { ?>
                எழுத்து எனக்கு<br>
                ஒரு வடிகாலாகவும்,<br>
                அடையாளமாகவும் அமைந்தது.
            <?php } ?>
        </blockquote>
        <p class="pull__cite"><span class="ta"><?php echo esc_html( kv_name() ); ?></span></p>
    </div>
</section>

<!-- Awards (full list, line style) -->
<?php if ( $awards->have_posts() ) : echo kv_asterism( 'break' ); ?>
<section class="awards">
    <?php echo kv_eyebrow_bi( 'விருதுகள்', 'Honours & Recognition' ); ?>
    <ul class="awards__list">
        <?php while ( $awards->have_posts() ) : $awards->the_post();
            $yr  = get_post_meta( get_the_ID(), '_kv_year', true );
            $org = get_post_meta( get_the_ID(), '_kv_org', true );
            ?>
            <li class="award">
                <span class="award__year"><?php echo esc_html( $yr ); ?></span>
                <span class="award__name"><?php the_title(); ?></span>
                <?php if ( $org ) : ?>
                    <span class="award__org"><?php echo esc_html( $org ); ?></span>
                <?php else : ?>
                    <span></span>
                <?php endif; ?>
            </li>
        <?php endwhile; wp_reset_postdata(); ?>
    </ul>
</section>
<?php endif; ?>

<?php get_footer(); ?>

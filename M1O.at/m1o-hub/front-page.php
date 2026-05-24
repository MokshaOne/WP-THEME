<?php
/**
 * M1O Hub · front page · link-list direction
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

$id          = m1o_get_identity();
$channels    = m1o_get_channels();
$group_lbls  = m1o_group_labels();
$status_lbls = m1o_status_labels();
$show_groups = m1o_opt( 'm1o_show_groups', '1' ) !== '0';
$show_cta    = m1o_opt( 'm1o_show_cta',    '1' ) !== '0';

// Group channels by their group key, preserving order within each group.
$grouped = [];
foreach ( array_keys( $group_lbls ) as $g ) $grouped[ $g ] = [];
foreach ( $channels as $i => $ch ) {
    $g = $ch['group'] ?? 'work';
    if ( ! isset( $grouped[ $g ] ) ) $grouped[ $g ] = [];
    $ch['__num'] = $i + 1;
    $grouped[ $g ][] = $ch;
}

// Status class mapping.
$status_classes = [
    'active'  => '',
    'idle'    => 'link__status--mute',
    'live'    => 'link__status--live',
    'current' => 'link__status--current',
];
?>

<main id="main" class="page">

    <!-- IDENTITY -->
    <header class="id">
        <h1 class="id__name"><?php echo wp_kses_post( str_replace( "\n", '<br>', preg_replace( '/(\.|·)/u', '<span class="period">$1</span>', esc_html( $id['name'] ) ) ) ); ?></h1>
        <p class="id__tagline"><?php echo nl2br( esc_html( $id['tagline'] ) ); ?></p>
        <div class="id__meta">
            <span class="led"><?php echo esc_html( $id['status'] ); ?></span>
            <span><?php echo esc_html( $id['location'] ); ?></span>
            <?php if ( $id['est'] ) : ?>
                <span>est. <?php echo esc_html( $id['est'] ); ?></span>
            <?php endif; ?>
        </div>
    </header>

    <!-- CHANNELS, grouped -->
    <?php $ch_global_num = 0; foreach ( $group_lbls as $g_key => $g_label ) :
        if ( empty( $grouped[ $g_key ] ) ) continue;
        $count = count( $grouped[ $g_key ] );
    ?>
        <section class="group">
            <?php if ( $show_groups ) : ?>
                <div class="group__head">
                    <span class="label">// <?php echo esc_html( $g_label ); ?></span>
                    <span class="rule"></span>
                    <span class="count"><?php echo sprintf( '%02d', $count ); ?></span>
                </div>
            <?php endif; ?>

            <?php foreach ( $grouped[ $g_key ] as $ch ) :
                $ch_global_num++;
                $status_key   = $ch['status'] ?? 'active';
                $status_class = $status_classes[ $status_key ] ?? '';
                $status_text  = $status_lbls[ $status_key ] ?? $status_key;
            ?>
                <a class="link"
                   href="<?php echo esc_url( $ch['url'] ?? '#' ); ?>"
                   target="_blank"
                   rel="noopener">
                    <span class="link__num">CH <?php echo sprintf( '%02d', $ch_global_num ); ?></span>
                    <div class="link__main">
                        <span class="link__title"><?php echo esc_html( $ch['title'] ?? '' ); ?></span>
                        <span class="link__host"><?php echo esc_html( $ch['host'] ?? '' ); ?></span>
                    </div>
                    <span class="link__status <?php echo esc_attr( $status_class ); ?>">
                        <?php echo esc_html( trim( $status_text, '●○▶ ' ) ); ?>
                    </span>
                    <span class="link__arrow">↗</span>
                </a>
            <?php endforeach; ?>
        </section>
    <?php endforeach; ?>

    <?php if ( $show_cta && $id['email'] ) : ?>
        <a class="cta" href="mailto:<?php echo esc_attr( $id['email'] ); ?>">
            <div>
                <span class="cta__label">▶ <?php echo esc_html( $id['cta'] ); ?></span>
                <span class="cta__main"><?php echo esc_html( $id['email'] ); ?></span>
            </div>
            <span class="cta__arrow">→</span>
        </a>
    <?php endif; ?>

</main>

<?php get_footer(); ?>

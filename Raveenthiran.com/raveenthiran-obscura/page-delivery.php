<?php
/**
 * Template Name: Delivery
 * #60 — client delivery page: protect it with WordPress's native page password
 * (Edit page → Visibility → Password protected). Lists the delivery gallery with
 * per-file download links; hides itself after the expiry date.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$nr_current = 'portfolio';
get_header();
the_post();

$expired = false;
$exp = function_exists( 'nr_field' ) ? trim( (string) nr_field( 'delivery_expiry' ) ) : '';
if ( $exp && ( $ts = strtotime( $exp . ' 23:59:59' ) ) && time() > $ts ) $expired = true;
?>
<section class="nr-page nr-delivery">
	<span class="nr-eyebrow"><?php esc_html_e( 'Delivery', 'raveenthiran' ); ?></span>
	<h1 class="nr-display nr-display--md"><?php the_title(); ?></h1>

	<?php if ( post_password_required() ) : ?>
		<div class="nr-delivery__gate"><?php echo get_the_password_form(); ?></div>
	<?php elseif ( $expired ) : ?>
		<p class="nr-delivery__expired"><?php esc_html_e( 'This delivery has expired. Email me if you need the files again.', 'raveenthiran' ); ?></p>
	<?php else :
		$note = function_exists( 'nr_field' ) ? trim( (string) nr_field( 'delivery_note' ) ) : '';
		if ( $note ) echo '<p class="nr-delivery__note">' . esc_html( $note ) . '</p>';
		if ( $exp ) printf( '<p class="nr-delivery__until">%s</p>', esc_html( sprintf( __( 'Available until %s', 'raveenthiran' ), $exp ) ) );
		$g = function_exists( 'nr_field' ) ? nr_field( 'delivery_gallery' ) : [];
		if ( is_array( $g ) && $g ) : ?>
			<div class="nr-delivery__grid">
				<?php foreach ( $g as $item ) :
					$aid = is_array( $item ) ? (int) ( $item['ID'] ?? $item['id'] ?? 0 ) : ( is_numeric( $item ) ? (int) $item : 0 );
					if ( ! $aid ) continue;
					$full = wp_get_attachment_url( $aid ); if ( ! $full ) continue; ?>
					<figure class="nr-delivery__item">
						<?php echo wp_get_attachment_image( $aid, 'nr-thumb', false, [ 'loading' => 'lazy', 'decoding' => 'async' ] ); ?>
						<a class="nr-btn nr-btn--ghost" href="<?php echo esc_url( $full ); ?>" download><span><?php esc_html_e( 'Download', 'raveenthiran' ); ?></span> <span>↓</span></a>
					</figure>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<p><?php esc_html_e( 'No files attached yet.', 'raveenthiran' ); ?></p>
		<?php endif;
	endif; ?>
</section>
<?php get_footer(); ?>

<?php
/**
 * Footer — Studio: closing CTA band + editorial footer + global parts.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$nr_enquire   = function_exists( 'nr_enquire_url' ) ? nr_enquire_url() : home_url( '/enquire' );
$nr_email     = nr_opt( 'nr_email', '' );
$nr_location  = nr_opt( 'nr_location', 'Wien' );
$nr_logo_text = nr_opt( 'nr_logo_text', 'Raveenthiran' );

$nr_socials = array_filter( [
	'Instagram' => nr_opt( 'nr_instagram' ),
	'Behance'   => nr_opt( 'nr_behance' ),
	'Vimeo'     => nr_opt( 'nr_vimeo' ),
	'LinkedIn'  => nr_opt( 'nr_linkedin' ),
] );

$nr_legal = array_filter( [
	'datenschutz' => nr_opt( 'nr_footer_legal_dz', 'Datenschutz' ),
	'agb'         => nr_opt( 'nr_footer_legal_ag', 'AGB' ),
	'impressum'   => nr_opt( 'nr_footer_legal_im', 'Impressum' ),
] );

$nr_cta_eyebrow = nr_opt( 'nr_footer_cta_eyebrow', __( 'Commissions · 2026', 'raveenthiran' ) );
$nr_cta_title   = nr_opt( 'nr_footer_cta_title', __( "Let's make something<br>worth keeping.", 'raveenthiran' ) );
?>
</main><?php /* /main */ ?>

<?php /* ── closing call-to-action band ─────────────────────────── */ ?>
<section class="st-cta" aria-label="<?php esc_attr_e( 'Start a project', 'raveenthiran' ); ?>" data-reveal>
	<div class="st-wrap">
		<span class="st-eyebrow"><?php echo esc_html( $nr_cta_eyebrow ); ?></span>
		<a class="st-cta__title" href="<?php echo esc_url( $nr_enquire ); ?>">
			<?php echo wp_kses( $nr_cta_title, [ 'br' => [], 'em' => [] ] ); ?>
			<span class="st-cta__arrow" aria-hidden="true">→</span>
		</a>
		<div class="st-cta__row">
			<a class="st-btn st-btn--primary" href="<?php echo esc_url( $nr_enquire ); ?>"><?php esc_html_e( 'Start an enquiry', 'raveenthiran' ); ?></a>
			<?php if ( $nr_email ) : ?>
				<button type="button" class="st-btn st-btn--ghost" data-copy="<?php echo esc_attr( $nr_email ); ?>"><?php echo esc_html( $nr_email ); ?></button>
			<?php endif; ?>
		</div>
	</div>
</section>

<footer class="st-foot" role="contentinfo">
	<div class="st-wrap st-foot__grid">
		<div class="st-foot__brand">
			<span class="st-foot__mark"><?php echo esc_html( $nr_logo_text ); ?></span>
			<p class="st-foot__note"><?php echo esc_html( nr_opt( 'nr_footer_text', sprintf( __( 'Photography studio based in %s. Editorial, portrait & architecture, worldwide.', 'raveenthiran' ), $nr_location ) ) ); ?></p>
		</div>

		<nav class="st-foot__col" aria-label="<?php esc_attr_e( 'Pages', 'raveenthiran' ); ?>">
			<h4><?php esc_html_e( 'Menu', 'raveenthiran' ); ?></h4>
			<a href="<?php echo esc_url( get_post_type_archive_link( 'nr_project' ) ?: home_url( '/portfolio' ) ); ?>"><?php esc_html_e( 'Work', 'raveenthiran' ); ?></a>
			<a href="<?php echo esc_url( function_exists( 'nr_template_page_url' ) ? nr_template_page_url( 'page-about.php', 'about' ) : home_url( '/about' ) ); ?>"><?php esc_html_e( 'Studio', 'raveenthiran' ); ?></a>
			<a href="<?php echo esc_url( function_exists( 'nr_journal_url' ) ? nr_journal_url() : home_url( '/journal' ) ); ?>"><?php esc_html_e( 'Journal', 'raveenthiran' ); ?></a>
			<a href="<?php echo esc_url( $nr_enquire ); ?>"><?php esc_html_e( 'Enquire', 'raveenthiran' ); ?></a>
		</nav>

		<?php if ( $nr_socials ) : ?>
		<nav class="st-foot__col" aria-label="<?php esc_attr_e( 'Elsewhere', 'raveenthiran' ); ?>">
			<h4><?php esc_html_e( 'Elsewhere', 'raveenthiran' ); ?></h4>
			<?php foreach ( $nr_socials as $label => $url ) printf( '<a href="%s" target="_blank" rel="noopener">%s ↗</a>', esc_url( $url ), esc_html( $label ) ); ?>
		</nav>
		<?php endif; ?>

		<div class="st-foot__col st-foot__contact">
			<h4><?php esc_html_e( 'Contact', 'raveenthiran' ); ?></h4>
			<?php if ( $nr_email ) : ?><button type="button" class="st-foot__email" data-copy="<?php echo esc_attr( $nr_email ); ?>"><?php echo esc_html( $nr_email ); ?></button><?php endif; ?>
			<span class="st-foot__place"><?php echo esc_html( nr_opt( 'nr_studio', $nr_location ) ); ?></span>
			<?php $nr_pk = nr_opt( 'nr_presskit_url', '' ); if ( $nr_pk ) : ?><a href="<?php echo esc_url( $nr_pk ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Press kit ↗', 'raveenthiran' ); ?></a><?php endif; ?>
		</div>
	</div>

	<div class="st-wrap st-foot__base">
		<span>© <?php echo esc_html( date_i18n( 'Y' ) ); ?> <?php echo esc_html( $nr_logo_text ); ?></span>
		<nav class="st-foot__legal" aria-label="<?php esc_attr_e( 'Legal', 'raveenthiran' ); ?>">
			<?php if ( ! empty( $nr_legal['datenschutz'] ) ) : ?><a href="<?php echo esc_url( home_url( '/datenschutz' ) ); ?>"><?php echo esc_html( $nr_legal['datenschutz'] ); ?></a><?php endif; ?>
			<?php if ( ! empty( $nr_legal['agb'] ) ) : ?><a href="<?php echo esc_url( home_url( '/agb' ) ); ?>"><?php echo esc_html( $nr_legal['agb'] ); ?></a><?php endif; ?>
			<?php if ( ! empty( $nr_legal['impressum'] ) ) : ?><a href="<?php echo esc_url( home_url( '/impressum' ) ); ?>"><?php echo esc_html( $nr_legal['impressum'] ); ?></a><?php endif; ?>
			<button type="button" class="st-foot__top" data-scroll-top><?php esc_html_e( 'Top ↑', 'raveenthiran' ); ?></button>
		</nav>
	</div>
</footer>

<?php /* ── global feature parts (functional — kept) ───────────── */ ?>
<?php get_template_part( 'parts/cookie-notice' ); ?>
<?php get_template_part( 'parts/inquiry-modal' ); ?>
<?php get_template_part( 'parts/quote-popover' ); ?>

<?php
/* form submission status banner — set by admin_post_nr_contact_send redirect */
if ( isset( $_GET['nr_sent'] ) ) :
	$ok    = $_GET['nr_sent'] === '1';
	$class = $ok ? 'nr-status--ok' : 'nr-status--error';
	$msg   = $ok
		? __( 'Sent — I’ll reply within 24 hours.', 'raveenthiran' )
		: __( 'Sending failed. Email me directly.', 'raveenthiran' );
	?>
	<div class="nr-status <?php echo esc_attr( $class ); ?>" role="status" aria-live="polite"><?php echo esc_html( $msg ); ?></div>
	<script>setTimeout(function(){var s=document.querySelector('.nr-status');if(s)s.remove();},5000);</script>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>

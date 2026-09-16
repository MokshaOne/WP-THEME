<?php
/**
 * GDPR cookie notice. Visible after page load on first visit; consent
 * persists via localStorage. Styled via .nr-cookie in assets/css/theme.css.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
if ( nr_opt( 'nr_show_cookie_notice', '1' ) !== '1' ) return;

$policy_url = home_url( '/datenschutz' );
?>
<div id="nr-cookie" class="nr-cookie" role="region" aria-label="<?php esc_attr_e( 'Cookie notice', 'raveenthiran' ); ?>">
	<p style="margin:0">
		<?php esc_html_e( 'This site uses cookies to improve your experience.', 'raveenthiran' ); ?>
		<a href="<?php echo esc_url( $policy_url ); ?>"><?php esc_html_e( 'Privacy policy', 'raveenthiran' ); ?></a>
	</p>
	<button id="nr-cookie-decline" type="button"><?php esc_html_e( 'Decline', 'raveenthiran' ); ?></button>
	<button id="nr-cookie-accept" type="button" class="is-primary"><?php esc_html_e( 'Accept', 'raveenthiran' ); ?></button>
</div>
<script>
(function(){
	if (localStorage.getItem('nr_cookie_consent')) return;
	var el = document.getElementById('nr-cookie');
	if (!el) return;
	// reveal after the page has settled
	requestAnimationFrame(function(){ setTimeout(function(){ el.classList.add('is-on'); }, 400); });
	document.getElementById('nr-cookie-accept').addEventListener('click', function(){
		localStorage.setItem('nr_cookie_consent','accepted'); document.cookie='nr_consent=accepted;path=/;max-age=15552000;samesite=lax'; el.classList.remove('is-on');
		setTimeout(function(){ el.remove(); }, 500);
	});
	document.getElementById('nr-cookie-decline').addEventListener('click', function(){
		localStorage.setItem('nr_cookie_consent','declined'); document.cookie='nr_consent=declined;path=/;max-age=15552000;samesite=lax'; el.classList.remove('is-on');
		setTimeout(function(){ el.remove(); }, 500);
	});
})();
</script>

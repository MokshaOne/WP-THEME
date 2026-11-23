<?php
/* Floating inquiry FAB + booking modal. Toggled by nr_show_inquiry_fab. */
if ( ! defined( 'ABSPATH' ) ) exit;
if ( get_option( 'nr_show_inquiry_fab', '1' ) !== '1' ) return;
$fab_label = get_option( 'nr_fab_label', __( 'Book me', 'raveenthiran' ) );
?>

<button id="nr-fab" type="button" aria-label="<?php esc_attr_e( 'Open booking enquiry', 'raveenthiran' ); ?>" aria-expanded="false" aria-controls="nr-modal">
    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true">
        <path d="M17 13.5c0 .4-.1.8-.3 1.2-.2.4-.4.7-.7 1-.5.5-1 .7-1.6.7-.4 0-.8-.1-1.3-.3a13 13 0 01-1.3-.7 22 22 0 01-1.3-1 21 21 0 01-1-1.3 13 13 0 01-.7-1.3c-.2-.4-.3-.9-.3-1.2 0-.4.1-.8.3-1.1.2-.4.5-.7.9-.9.4-.2.9-.2 1.3 0 .2.1.3.2.4.4l1.3 1.8c.1.1.2.3.2.4 0 .2-.1.3-.2.4l-.5.5c0 .1 0 .1.1.2l.5.6.6.5h.2l.5-.5c.1-.1.3-.2.4-.2.2 0 .3.1.4.2l1.8 1.3c.2.1.3.2.4.4z" stroke="currentColor" stroke-width="1.4"/>
    </svg>
    <span><?php echo esc_html( $fab_label ); ?></span>
</button>

<div id="nr-modal" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Booking enquiry', 'raveenthiran' ); ?>" aria-hidden="true">
    <div id="nr-modal-bg"></div>
    <div id="nr-modal-box">
        <button id="nr-modal-x" type="button" aria-label="<?php esc_attr_e( 'Close', 'raveenthiran' ); ?>">×</button>
        <p class="nr-modal-eyebrow"><?php esc_html_e( 'Quick enquiry', 'raveenthiran' ); ?></p>
        <h2 class="nr-modal-title"><?php esc_html_e( "Let's work", 'raveenthiran' ); ?> <em><?php esc_html_e( 'together', 'raveenthiran' ); ?></em></h2>

        <form id="nr-modal-form" novalidate>
            <div id="nr-modal-msg" role="alert" aria-live="polite"></div>
            <div class="nr-modal-honey" aria-hidden="true"><input type="text" name="nr_website" tabindex="-1" autocomplete="off"></div>

            <div class="nr-modal-grid">
                <label class="nr-modal-field">
                    <span class="nr-modal-field__label"><?php esc_html_e( 'Name', 'raveenthiran' ); ?></span>
                    <input type="text" name="nr_name" autocomplete="name" required>
                </label>
                <label class="nr-modal-field">
                    <span class="nr-modal-field__label"><?php esc_html_e( 'Email', 'raveenthiran' ); ?></span>
                    <input type="email" name="nr_email" autocomplete="email" inputmode="email" spellcheck="false" required>
                </label>
            </div>
            <label class="nr-modal-field">
                <span class="nr-modal-field__label"><?php esc_html_e( 'Message', 'raveenthiran' ); ?></span>
                <textarea name="nr_message" rows="3" autocomplete="off" required></textarea>
            </label>
            <button type="submit" class="nr-btn nr-btn--primary"><?php esc_html_e( 'Send enquiry', 'raveenthiran' ); ?> →</button>
        </form>
    </div>
</div>

<style>
#nr-fab {
    position:fixed; bottom:calc(28px + 16px); right:28px; z-index:9000;
    display:inline-flex; align-items:center; gap:8px;
    background:var(--nr-accent, #BF882E); border:none; border-radius:999px;
    padding:12px 20px;
    color:var(--nr-bg, #0B0907);
    font-family:var(--font-mono, monospace); font-size:10px;
    letter-spacing:.15em; text-transform:uppercase; cursor:pointer;
    box-shadow:0 8px 32px rgba(0,0,0,.5);
    transition:transform .2s, box-shadow .2s, background .2s;
}
#nr-fab:hover { transform:translateY(-3px); box-shadow:0 12px 40px rgba(0,0,0,.6); }

#nr-modal {
    position:fixed; inset:0; z-index:10000;
    display:flex; align-items:center; justify-content:center;
    visibility:hidden; pointer-events:none;
}
#nr-modal.open { visibility:visible; pointer-events:auto; }
#nr-modal-bg {
    position:absolute; inset:0;
    background:rgba(11,9,7,.85); backdrop-filter:blur(8px);
    opacity:0; transition:opacity .3s;
}
#nr-modal.open #nr-modal-bg { opacity:1; }
#nr-modal-box {
    position:relative; z-index:1;
    background:var(--nr-bg, #0B0907); border:1px solid var(--nr-border, #2A2522);
    padding:clamp(24px, 4vw, 40px);
    width:min(560px, calc(100vw - 32px));
    transform:translateY(24px); opacity:0;
    transition:transform .3s, opacity .3s;
}
#nr-modal.open #nr-modal-box { transform:translateY(0); opacity:1; }
#nr-modal-x {
    position:absolute; top:16px; right:16px;
    background:none; border:none; padding:4px 8px;
    font-size:22px; color:var(--nr-muted, #8A8780); cursor:pointer; line-height:1;
}
#nr-modal-x:hover { color:var(--nr-ink, #EDE8DF); }

.nr-modal-eyebrow {
    font-family:var(--font-mono, monospace); font-size:10px;
    letter-spacing:.2em; text-transform:uppercase;
    color:var(--nr-accent, #BF882E); margin:0 0 12px;
}
.nr-modal-title {
    font-family:var(--font-display, serif);
    font-size:clamp(24px, 4vw, 40px); line-height:1;
    letter-spacing:-.03em; font-weight:400;
    margin:0 0 24px; color:var(--nr-ink, #EDE8DF);
}
.nr-modal-title em { font-style:italic; color:var(--nr-accent, #BF882E); }

#nr-modal-form { display:flex; flex-direction:column; gap:14px; }
#nr-modal-msg {
    display:none; padding:12px 16px;
    font-family:var(--font-mono, monospace); font-size:11px;
    border:1px solid var(--nr-border, #2A2522);
}
.nr-modal-honey { position:absolute; left:-9999px; opacity:0; }
.nr-modal-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
.nr-modal-field { position:relative; display:block; }
.nr-modal-field__label {
    position:absolute; left:14px; top:10px;
    font-family:var(--font-mono, monospace); font-size:9px;
    letter-spacing:.12em; text-transform:uppercase;
    color:var(--nr-muted, #8A8780); pointer-events:none;
}
.nr-modal-field input,
.nr-modal-field textarea {
    width:100%;
    background:var(--nr-surface, #1A1614);
    border:1px solid var(--nr-border, #2A2522);
    padding:24px 14px 10px;
    font-family:var(--font-body, system-ui); font-size:14px;
    color:var(--nr-ink, #EDE8DF); outline:none;
    transition:border-color .2s;
    resize:none;
}
.nr-modal-field input:focus,
.nr-modal-field textarea:focus { border-color:var(--nr-accent, #BF882E); }

@media (max-width:480px) {
    #nr-fab { padding:14px; border-radius:50%; }
    #nr-fab span { display:none; }
    .nr-modal-grid { grid-template-columns:1fr; }
}
@media (max-width:900px) {
    #nr-fab { bottom:calc(var(--tab-h, 64px) + 16px); }
}
</style>

<script>
(function(){
    var fab   = document.getElementById('nr-fab');
    var modal = document.getElementById('nr-modal');
    var bg    = document.getElementById('nr-modal-bg');
    var x     = document.getElementById('nr-modal-x');
    if (!modal) return;

    function open()  { modal.classList.add('open'); if (fab) fab.setAttribute('aria-expanded','true'); modal.removeAttribute('aria-hidden'); document.body.style.overflow='hidden'; }
    function close() { modal.classList.remove('open'); if (fab) fab.setAttribute('aria-expanded','false'); modal.setAttribute('aria-hidden','true'); document.body.style.overflow=''; }

    if (fab) fab.addEventListener('click', open);
    document.querySelectorAll('.nr-contact-trigger').forEach(function(el){ el.addEventListener('click', open); });
    x.addEventListener('click', close);
    bg.addEventListener('click', close);
    document.addEventListener('keydown', function(e){ if (e.key === 'Escape') close(); });

    var form = document.getElementById('nr-modal-form');
    var msg  = document.getElementById('nr-modal-msg');
    form.addEventListener('submit', function(e){
        e.preventDefault();
        if (form.querySelector('[name="nr_website"]').value) return;
        var btn = form.querySelector('button[type="submit"]');
        var orig = btn.innerHTML;
        btn.disabled = true; btn.innerHTML = (typeof NR !== 'undefined' && NR.i18n && NR.i18n.sending) || 'Sending…';

        var data = new FormData(form);
        data.append('action', 'nr_contact_form');
        if (typeof NR !== 'undefined' && NR.nonce) data.append('nonce', NR.nonce);
        var url = (typeof NR !== 'undefined' && NR.ajaxUrl) ? NR.ajaxUrl : '/wp-admin/admin-ajax.php';

        fetch(url, { method:'POST', credentials:'same-origin', body:data })
            .then(function(r){ return r.json(); })
            .then(function(r){
                msg.style.display = 'block';
                if (r.success) {
                    msg.style.borderColor = 'var(--nr-accent, #BF882E)';
                    msg.style.color = 'var(--nr-ink, #EDE8DF)';
                    msg.textContent = r.data || 'Sent!';
                    form.reset();
                    setTimeout(close, 2000);
                } else {
                    msg.style.borderColor = '#c0392b';
                    msg.style.color = '#e74c3c';
                    msg.textContent = r.data || 'Error.';
                }
            })
            .catch(function(){
                msg.style.display = 'block';
                msg.textContent = 'Network error.';
            })
            .finally(function(){ btn.disabled = false; btn.innerHTML = orig; });
    });
})();
</script>

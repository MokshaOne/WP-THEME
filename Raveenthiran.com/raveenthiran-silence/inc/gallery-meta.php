<?php
/**
 * Silence — native project meta boxes (no-ACF fallback).
 * Writes the exact same keys as Obscura / the ACF group: project_gallery
 * (ID array), project_client, project_year, project_location,
 * featured_on_homepage. When ACF Pro is active, its "Project Details"
 * group (inc/acf-fields.php) renders instead and these boxes step aside.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/** True when real ACF is active (the polyfill doesn't count). */
function nr_sil_has_acf() {
	return class_exists( 'ACF' );
}

add_action( 'add_meta_boxes', function () {
	if ( nr_sil_has_acf() ) return; // ACF renders the same fields itself
	add_meta_box( 'nr_gallery', __( 'Gallery — plates', 'raveenthiran-silence' ), 'nr_sil_gallery_box', 'nr_project', 'normal', 'high' );
	add_meta_box( 'nr_details', __( 'Project details', 'raveenthiran-silence' ), 'nr_sil_details_box', 'nr_project', 'side' );
} );

function nr_sil_gallery_box( $post ) {
	wp_nonce_field( 'nr_sil_meta_save', 'nr_sil_meta_nonce' );
	$ids = array_filter( array_map( 'absint', (array) get_post_meta( $post->ID, 'project_gallery', true ) ) );
	echo '<div id="nr-gallery-grid" style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:10px">';
	foreach ( $ids as $id ) {
		$src = wp_get_attachment_image_url( $id, 'thumbnail' );
		if ( ! $src && wp_attachment_is( 'video', $id ) ) $src = get_the_post_thumbnail_url( $id, 'thumbnail' );
		if ( $src ) {
			printf(
				'<span class="nr-g-item" data-id="%1$d" title="%2$s" style="position:relative;cursor:pointer"><img src="%3$s" width="90" height="90" style="object-fit:cover;display:block;border:1px solid #ccc"></span>',
				$id, esc_attr__( 'Click to remove', 'raveenthiran-silence' ), esc_url( $src )
			);
		}
	}
	echo '</div>';
	printf( '<input type="hidden" id="nr-gallery-ids" name="nr_gallery" value="%s">', esc_attr( implode( ',', $ids ) ) );
	printf( '<button type="button" class="button" id="nr-gallery-add">%s</button> ', esc_html__( 'Add / reorder images', 'raveenthiran-silence' ) );
	echo '<p class="description">' . esc_html__( 'Selection order = display order. Click a thumbnail to remove it. The featured image is always shown as plate 01. Same field Obscura uses — the two themes share this gallery.', 'raveenthiran-silence' ) . '</p>';
}

function nr_sil_details_box( $post ) {
	$fields = [
		'project_client'   => __( 'Client', 'raveenthiran-silence' ),
		'project_year'     => __( 'Year', 'raveenthiran-silence' ),
		'project_location' => __( 'Location', 'raveenthiran-silence' ),
	];
	foreach ( $fields as $key => $label ) {
		printf(
			'<p><label style="display:block;margin-bottom:2px"><strong>%s</strong></label><input type="text" class="widefat" name="%s" value="%s"></p>',
			esc_html( $label ), esc_attr( $key ), esc_attr( get_post_meta( $post->ID, $key, true ) )
		);
	}
	echo '<p><label><input type="checkbox" name="featured_on_homepage" value="1" ' . checked( get_post_meta( $post->ID, 'featured_on_homepage', true ), '1', false ) . '> '
		. esc_html__( 'Featured on homepage', 'raveenthiran-silence' ) . '</label></p>';
}

add_action( 'save_post_nr_project', function ( $post_id ) {
	if ( ! isset( $_POST['nr_sil_meta_nonce'] ) || ! wp_verify_nonce( $_POST['nr_sil_meta_nonce'], 'nr_sil_meta_save' ) ) return;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;

	$ids = isset( $_POST['nr_gallery'] ) ? array_filter( array_map( 'absint', explode( ',', (string) $_POST['nr_gallery'] ) ) ) : [];
	update_post_meta( $post_id, 'project_gallery', $ids );

	foreach ( [ 'project_client', 'project_year', 'project_location' ] as $key ) {
		update_post_meta( $post_id, $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ?? '' ) ) );
	}
	update_post_meta( $post_id, 'featured_on_homepage', isset( $_POST['featured_on_homepage'] ) ? '1' : '0' );
} );

// wp.media picker for the gallery box (project edit screen only, no ACF).
add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( nr_sil_has_acf() ) return;
	if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) return;
	if ( get_current_screen()->post_type !== 'nr_project' ) return;
	wp_enqueue_media();
} );

add_action( 'admin_footer', function () {
	if ( nr_sil_has_acf() ) return;
	$screen = get_current_screen();
	if ( ! $screen || $screen->post_type !== 'nr_project' || $screen->base !== 'post' ) return;
	?>
	<script>
	(function(){
		var addBtn = document.getElementById('nr-gallery-add');
		var grid   = document.getElementById('nr-gallery-grid');
		var input  = document.getElementById('nr-gallery-ids');
		if (!addBtn || !grid || !input || !window.wp || !wp.media) return;

		function render(items){
			grid.innerHTML = '';
			input.value = items.map(function(i){ return i.id; }).join(',');
			items.forEach(function(i){
				var span = document.createElement('span');
				span.className = 'nr-g-item';
				span.dataset.id = i.id;
				span.title = '<?php echo esc_js( __( 'Click to remove', 'raveenthiran-silence' ) ); ?>';
				span.style.cssText = 'position:relative;cursor:pointer';
				span.innerHTML = '<img src="'+i.src+'" width="90" height="90" style="object-fit:cover;display:block;border:1px solid #ccc">';
				grid.appendChild(span);
			});
		}
		function current(){
			return Array.prototype.map.call(grid.querySelectorAll('.nr-g-item'), function(el){
				return { id: el.dataset.id, src: el.querySelector('img').src };
			});
		}

		grid.addEventListener('click', function(e){
			var item = e.target.closest('.nr-g-item');
			if (!item) return;
			item.remove();
			render(current());
		});

		addBtn.addEventListener('click', function(){
			var frame = wp.media({ title: '<?php echo esc_js( __( 'Project gallery', 'raveenthiran-silence' ) ); ?>', multiple: 'add', library: { type: ['image','video'] } });
			frame.on('open', function(){
				var sel = frame.state().get('selection');
				input.value.split(',').filter(Boolean).forEach(function(id){
					var att = wp.media.attachment(id); att.fetch(); sel.add(att);
				});
			});
			frame.on('select', function(){
				render(frame.state().get('selection').map(function(att){
					var d = att.toJSON();
					var src = d.sizes && d.sizes.thumbnail ? d.sizes.thumbnail.url
						: (d.image && d.image.src ? d.image.src : (d.icon || d.url)); // videos: poster frame or dashicon
					return { id: d.id, src: src };
				}));
			});
			frame.open();
		});
	})();
	</script>
	<?php
} );

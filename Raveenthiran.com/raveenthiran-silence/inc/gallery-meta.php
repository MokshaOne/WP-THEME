<?php
/**
 * Silence — native project meta boxes (no ACF, no plugins).
 * Gallery: wp.media multi-select stored as an ID array in _sl_gallery.
 * Details: client / year / location text fields.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'add_meta_boxes', function () {
	add_meta_box( 'sl_gallery', __( 'Gallery — plates', 'raveenthiran-silence' ), 'sl_gallery_box', 'sl_project', 'normal', 'high' );
	add_meta_box( 'sl_details', __( 'Project details', 'raveenthiran-silence' ), 'sl_details_box', 'sl_project', 'side' );
} );

function sl_gallery_box( $post ) {
	wp_nonce_field( 'sl_meta_save', 'sl_meta_nonce' );
	$ids = array_filter( array_map( 'absint', (array) get_post_meta( $post->ID, '_sl_gallery', true ) ) );
	echo '<div id="sl-gallery-grid" style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:10px">';
	foreach ( $ids as $id ) {
		$src = wp_get_attachment_image_url( $id, 'thumbnail' );
		if ( $src ) {
			printf(
				'<span class="sl-g-item" data-id="%1$d" title="%2$s" style="position:relative;cursor:pointer"><img src="%3$s" width="90" height="90" style="object-fit:cover;display:block;border:1px solid #ccc"></span>',
				$id, esc_attr__( 'Click to remove', 'raveenthiran-silence' ), esc_url( $src )
			);
		}
	}
	echo '</div>';
	printf( '<input type="hidden" id="sl-gallery-ids" name="sl_gallery" value="%s">', esc_attr( implode( ',', $ids ) ) );
	printf( '<button type="button" class="button" id="sl-gallery-add">%s</button> ', esc_html__( 'Add / reorder images', 'raveenthiran-silence' ) );
	echo '<p class="description">' . esc_html__( 'Selection order = display order. Click a thumbnail to remove it. The featured image is always shown as plate 01.', 'raveenthiran-silence' ) . '</p>';
}

function sl_details_box( $post ) {
	$fields = [
		'_sl_client'   => __( 'Client', 'raveenthiran-silence' ),
		'_sl_year'     => __( 'Year', 'raveenthiran-silence' ),
		'_sl_location' => __( 'Location', 'raveenthiran-silence' ),
	];
	foreach ( $fields as $key => $label ) {
		printf(
			'<p><label style="display:block;margin-bottom:2px"><strong>%s</strong></label><input type="text" class="widefat" name="%s" value="%s"></p>',
			esc_html( $label ), esc_attr( ltrim( $key, '_' ) ), esc_attr( get_post_meta( $post->ID, $key, true ) )
		);
	}
	echo '<p><label><input type="checkbox" name="sl_featured" value="1" ' . checked( get_post_meta( $post->ID, '_sl_featured', true ), '1', false ) . '> '
		. esc_html__( 'Featured on homepage', 'raveenthiran-silence' ) . '</label></p>';
}

add_action( 'save_post_sl_project', function ( $post_id ) {
	if ( ! isset( $_POST['sl_meta_nonce'] ) || ! wp_verify_nonce( $_POST['sl_meta_nonce'], 'sl_meta_save' ) ) return;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;

	$ids = isset( $_POST['sl_gallery'] ) ? array_filter( array_map( 'absint', explode( ',', (string) $_POST['sl_gallery'] ) ) ) : [];
	update_post_meta( $post_id, '_sl_gallery', $ids );

	foreach ( [ 'sl_client' => '_sl_client', 'sl_year' => '_sl_year', 'sl_location' => '_sl_location' ] as $post_key => $meta_key ) {
		update_post_meta( $post_id, $meta_key, sanitize_text_field( wp_unslash( $_POST[ $post_key ] ?? '' ) ) );
	}
	update_post_meta( $post_id, '_sl_featured', isset( $_POST['sl_featured'] ) ? '1' : '0' );
} );

// wp.media picker for the gallery box (project edit screen only).
add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) return;
	if ( get_current_screen()->post_type !== 'sl_project' ) return;
	wp_enqueue_media();
} );

add_action( 'admin_footer', function () {
	$screen = get_current_screen();
	if ( ! $screen || $screen->post_type !== 'sl_project' || $screen->base !== 'post' ) return;
	?>
	<script>
	(function(){
		var addBtn = document.getElementById('sl-gallery-add');
		var grid   = document.getElementById('sl-gallery-grid');
		var input  = document.getElementById('sl-gallery-ids');
		if (!addBtn || !grid || !input || !window.wp || !wp.media) return;

		function render(items){
			grid.innerHTML = '';
			input.value = items.map(function(i){ return i.id; }).join(',');
			items.forEach(function(i){
				var span = document.createElement('span');
				span.className = 'sl-g-item';
				span.dataset.id = i.id;
				span.title = '<?php echo esc_js( __( 'Click to remove', 'raveenthiran-silence' ) ); ?>';
				span.style.cssText = 'position:relative;cursor:pointer';
				span.innerHTML = '<img src="'+i.src+'" width="90" height="90" style="object-fit:cover;display:block;border:1px solid #ccc">';
				grid.appendChild(span);
			});
		}
		function current(){
			return Array.prototype.map.call(grid.querySelectorAll('.sl-g-item'), function(el){
				return { id: el.dataset.id, src: el.querySelector('img').src };
			});
		}

		grid.addEventListener('click', function(e){
			var item = e.target.closest('.sl-g-item');
			if (!item) return;
			item.remove();
			render(current());
		});

		addBtn.addEventListener('click', function(){
			var frame = wp.media({ title: '<?php echo esc_js( __( 'Project gallery', 'raveenthiran-silence' ) ); ?>', multiple: 'add', library: { type: 'image' } });
			frame.on('open', function(){
				var sel = frame.state().get('selection');
				input.value.split(',').filter(Boolean).forEach(function(id){
					var att = wp.media.attachment(id); att.fetch(); sel.add(att);
				});
			});
			frame.on('select', function(){
				render(frame.state().get('selection').map(function(att){
					var d = att.toJSON();
					return { id: d.id, src: (d.sizes && d.sizes.thumbnail ? d.sizes.thumbnail.url : d.url) };
				}));
			});
			frame.open();
		});
	})();
	</script>
	<?php
} );

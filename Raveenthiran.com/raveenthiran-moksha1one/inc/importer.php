<?php
/**
 * #41 Bulk project importer.
 *
 * Projects → Import: upload a .zip where each top-level folder is one project.
 *   • folder name        → project title
 *   • first image        → featured image (cover)
 *   • all images (sorted) → the gallery (project_gallery), in filename order
 *   • EXIF date          → project_year (via the add_attachment hook in tier2.php)
 *
 * Replaces an Immich / Media-Picker workflow: photos are imported straight into
 * the WordPress media library. Process modest zips (~5–10 projects) at a time
 * to stay within shared-host upload/time limits; run it as often as needed.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'admin_menu', function () {
	add_submenu_page(
		'edit.php?post_type=nr_project',
		__( 'Import Projects', 'raveenthiran' ),
		__( 'Import', 'raveenthiran' ),
		'edit_posts',
		'nr-import',
		'nr_import_render_page'
	);
} );

function nr_import_render_page() {
	$done = isset( $_GET['nr_imported'] ) ? (int) $_GET['nr_imported'] : -1;
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Import Projects', 'raveenthiran' ); ?></h1>

		<?php if ( $done >= 0 ) :
			$imgs = isset( $_GET['nr_images'] ) ? (int) $_GET['nr_images'] : 0;
			$skip = isset( $_GET['nr_skipped'] ) ? (int) $_GET['nr_skipped'] : 0;
			$err  = isset( $_GET['nr_err'] ) ? sanitize_text_field( wp_unslash( $_GET['nr_err'] ) ) : '';
			$class = ( $done === 0 && ! $imgs ) ? 'notice-error' : 'notice-success'; ?>
			<div class="notice <?php echo esc_attr( $class ); ?> is-dismissible"><p>
				<?php
				printf(
					/* translators: 1: projects, 2: images, 3: skipped */
					esc_html__( 'Imported %1$d project(s) · %2$d image(s) · %3$d skipped (already existed).', 'raveenthiran' ),
					$done, $imgs, $skip
				);
				if ( $err ) echo ' — ' . esc_html( $err );
				?>
			</p></div>
		<?php endif; ?>

		<p style="max-width:720px">
			<?php esc_html_e( 'Upload a .zip where each top-level folder is one project. The folder name becomes the title, the first image becomes the cover, and every image becomes the gallery (in filename order). The year auto-fills from photo EXIF.', 'raveenthiran' ); ?>
		</p>
		<p style="max-width:720px;color:#666">
			<strong><?php esc_html_e( 'Tip:', 'raveenthiran' ); ?></strong>
			<?php esc_html_e( 'on shared hosting keep each zip to roughly 5–10 projects so it fits your upload size / time limits. You can run the importer as many times as you like.', 'raveenthiran' ); ?>
		</p>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
			<input type="hidden" name="action" value="nr_import_projects">
			<?php wp_nonce_field( 'nr_import', '_nr_import_nonce' ); ?>
			<p><input type="file" name="nr_zip" accept=".zip,application/zip" required></p>
			<p><label><input type="checkbox" name="nr_skip_existing" value="1" checked>
				<?php esc_html_e( 'Skip projects whose title already exists (safe to re-run)', 'raveenthiran' ); ?></label></p>
			<?php submit_button( __( 'Import projects', 'raveenthiran' ) ); ?>
		</form>
	</div>
	<?php
}

add_action( 'admin_post_nr_import_projects', 'nr_handle_import' );
function nr_handle_import() {
	if ( ! current_user_can( 'edit_posts' ) || ! check_admin_referer( 'nr_import', '_nr_import_nonce' ) ) {
		wp_die( esc_html__( 'Unauthorized.', 'raveenthiran' ) );
	}
	$back = admin_url( 'edit.php?post_type=nr_project&page=nr-import' );
	$fail = function ( $msg ) use ( $back ) {
		wp_safe_redirect( add_query_arg( [ 'nr_imported' => 0, 'nr_images' => 0, 'nr_skipped' => 0, 'nr_err' => rawurlencode( $msg ) ], $back ) );
		exit;
	};

	if ( empty( $_FILES['nr_zip']['tmp_name'] ) ) $fail( __( 'No file uploaded.', 'raveenthiran' ) );
	$ft = wp_check_filetype( $_FILES['nr_zip']['name'] );
	if ( ( $ft['ext'] ?? '' ) !== 'zip' ) $fail( __( 'Please upload a .zip file.', 'raveenthiran' ) );

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	WP_Filesystem();
	@set_time_limit( 0 );
	@ini_set( 'memory_limit', '512M' );

	$up   = wp_upload_dir();
	$work = trailingslashit( $up['basedir'] ) . 'nr-import-' . wp_generate_password( 8, false );
	wp_mkdir_p( $work );

	$unzip = unzip_file( $_FILES['nr_zip']['tmp_name'], $work );
	if ( is_wp_error( $unzip ) ) { nr_import_rrmdir( $work ); $fail( __( 'Could not unzip the file (filesystem permissions?).', 'raveenthiran' ) ); }

	$skip_existing = ! empty( $_POST['nr_skip_existing'] );
	$exts = [ 'jpg', 'jpeg', 'png', 'webp', 'avif', 'gif' ];
	$projects = 0; $images = 0; $skipped = 0;

	foreach ( nr_import_project_dirs( $work ) as $dir ) {
		$raw = basename( $dir );
		if ( $raw === '' || $raw[0] === '.' || strtoupper( $raw ) === '__MACOSX' ) continue;
		$title = ucwords( trim( str_replace( [ '-', '_' ], ' ', $raw ) ) );

		if ( $skip_existing && nr_import_project_exists( $title ) ) { $skipped++; continue; }

		$files = glob( trailingslashit( $dir ) . '*' ) ?: [];
		natcasesort( $files );
		$imgs = array_values( array_filter( $files, function ( $f ) use ( $exts ) {
			return is_file( $f ) && in_array( strtolower( pathinfo( $f, PATHINFO_EXTENSION ) ), $exts, true );
		} ) );
		if ( ! $imgs ) continue;

		$pid = wp_insert_post( [ 'post_type' => 'nr_project', 'post_status' => 'publish', 'post_title' => $title ] );
		if ( ! $pid || is_wp_error( $pid ) ) continue;
		$projects++;

		$gallery = [];
		foreach ( $imgs as $i => $f ) {
			$att = nr_import_sideload( $f, $pid );
			if ( ! $att || is_wp_error( $att ) ) continue;
			$images++;
			if ( $i === 0 ) set_post_thumbnail( $pid, $att );
			$gallery[] = (int) $att;
		}
		if ( $gallery ) {
			if ( function_exists( 'update_field' ) ) update_field( 'project_gallery', $gallery, $pid );
			else update_post_meta( $pid, 'project_gallery', $gallery );
		}
	}

	nr_import_rrmdir( $work );
	wp_safe_redirect( add_query_arg( [ 'nr_imported' => $projects, 'nr_images' => $images, 'nr_skipped' => $skipped ], $back ) );
	exit;
}

/* If the zip wraps everything in one root folder, descend into it. */
function nr_import_project_dirs( $work ) {
	$dirs = glob( trailingslashit( $work ) . '*', GLOB_ONLYDIR ) ?: [];
	$dirs = array_values( array_filter( $dirs, function ( $d ) { return strtoupper( basename( $d ) ) !== '__MACOSX'; } ) );
	if ( count( $dirs ) === 1 ) {
		$sub = glob( trailingslashit( $dirs[0] ) . '*', GLOB_ONLYDIR ) ?: [];
		if ( $sub ) return $sub;
	}
	return $dirs;
}

function nr_import_project_exists( $title ) {
	$q = get_posts( [ 'post_type' => 'nr_project', 'title' => $title, 'posts_per_page' => 1, 'fields' => 'ids', 'post_status' => 'any', 'no_found_rows' => true ] );
	return ! empty( $q );
}

function nr_import_sideload( $file, $parent ) {
	$tmp = wp_tempnam( basename( $file ) );
	if ( ! $tmp ) return false;
	if ( ! @copy( $file, $tmp ) ) { @unlink( $tmp ); return false; }
	$id = media_handle_sideload( [ 'name' => basename( $file ), 'tmp_name' => $tmp ], (int) $parent );
	if ( is_wp_error( $id ) ) { @unlink( $tmp ); return $id; }
	return $id;
}

function nr_import_rrmdir( $dir ) {
	if ( ! is_dir( $dir ) ) return;
	foreach ( array_diff( scandir( $dir ), [ '.', '..' ] ) as $f ) {
		$p = $dir . '/' . $f;
		is_dir( $p ) ? nr_import_rrmdir( $p ) : @unlink( $p );
	}
	@rmdir( $dir );
}

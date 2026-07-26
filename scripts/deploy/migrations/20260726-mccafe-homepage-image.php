<?php
/**
 * Add the McCafé prices infographic to the homepage as an editable WordPress
 * Media Library attachment, then refresh the managed homepage pattern once.
 *
 * Run from the WordPress root:
 * MCPRICES_MCCAFE_IMAGE_APPROVAL=APPLY_20260726_MCCAFE_IMAGE \
 * php scripts/deploy/migrations/20260726-mccafe-homepage-image.php
 */

if ( 'cli' !== PHP_SAPI ) {
	fwrite( STDERR, "This migration may only run from the command line.\n" );
	exit( 2 );
}

$wordpress_root = dirname( __DIR__, 3 );
require $wordpress_root . '/wp-load.php';

global $wpdb;

$approval       = (string) getenv( 'MCPRICES_MCCAFE_IMAGE_APPROVAL' );
$apply          = 'APPLY_20260726_MCCAFE_IMAGE' === $approval;
$home_id        = (int) get_option( 'page_on_front' );
$home_id        = $home_id > 0 ? $home_id : 11;
$filename       = 'mccafe-coffee-prices.avif';
$relative_file  = '2026/07/' . $filename;
$alt_text       = 'McCafé coffee prices and calories for hot coffee, iced coffee, frappes, and hot chocolate';
$asset_directory = get_theme_file_path( '/assets/images/mcprices/homepage' );
$upload          = wp_get_upload_dir();
$upload_directory = trailingslashit( $upload['basedir'] ) . '2026/07';
$source_files     = array(
	$filename                              => array( 1672, 941 ),
	'mccafe-coffee-prices-400x225.avif'     => array( 400, 225 ),
	'mccafe-coffee-prices-800x450.avif'     => array( 800, 450 ),
	'mccafe-coffee-prices-1200x675.avif'    => array( 1200, 675 ),
);

$home = get_post( $home_id );

if ( ! $home instanceof WP_Post || 'page' !== $home->post_type ) {
	fwrite( STDERR, "The configured homepage could not be found.\n" );
	exit( 1 );
}

foreach ( $source_files as $source_filename => $dimensions ) {
	$source_path = trailingslashit( $asset_directory ) . $source_filename;

	if ( ! is_file( $source_path ) ) {
		fwrite( STDERR, 'Required source image is missing: ' . $source_path . "\n" );
		exit( 1 );
	}
}

$attachment_id = (int) $wpdb->get_var(
	"SELECT post_id
	FROM {$wpdb->postmeta}
	WHERE meta_key = '_mcprices_mccafe_prices_image'
		AND meta_value = '1'
	ORDER BY post_id DESC
	LIMIT 1"
);

if ( ! $attachment_id ) {
	$attachment_id = (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT post_id
			FROM {$wpdb->postmeta}
			WHERE meta_key = '_wp_attached_file'
				AND meta_value = %s
			ORDER BY post_id DESC
			LIMIT 1",
			$relative_file
		)
	);
}

echo 'MODE=' . ( $apply ? 'apply' : 'dry-run' ) . "\n";
echo 'HOME_ID=' . $home_id . "\n";
echo 'EXISTING_ATTACHMENT_ID=' . $attachment_id . "\n";
echo 'SOURCE_FILES=' . count( $source_files ) . "\n";

if ( ! $apply ) {
	echo "No changes applied. Set MCPRICES_MCCAFE_IMAGE_APPROVAL=APPLY_20260726_MCCAFE_IMAGE to continue.\n";
	exit( 0 );
}

$backup_directory = WP_CONTENT_DIR . '/uploads/mcprices-deploy-backups';

if ( ! wp_mkdir_p( $backup_directory ) ) {
	throw new RuntimeException( 'Could not create the migration backup directory.' );
}

$backup_file = trailingslashit( $backup_directory ) . '20260726-mccafe-homepage-image-' . gmdate( 'Ymd-His' ) . '.json';
$backup      = array(
	'created_at'          => gmdate( 'c' ),
	'home_id'             => $home_id,
	'home_content'        => (string) $home->post_content,
	'home_modified'       => (string) $home->post_modified,
	'home_modified_gmt'   => (string) $home->post_modified_gmt,
	'attachment_id'       => $attachment_id,
	'attachment_post'     => $attachment_id ? get_post( $attachment_id, ARRAY_A ) : null,
	'attached_file'       => $attachment_id ? get_post_meta( $attachment_id, '_wp_attached_file', true ) : null,
	'attachment_metadata' => $attachment_id ? get_post_meta( $attachment_id, '_wp_attachment_metadata', true ) : null,
	'attachment_alt'      => $attachment_id ? get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) : null,
);

if ( false === file_put_contents( $backup_file, wp_json_encode( $backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) ) ) {
	throw new RuntimeException( 'Could not write the migration backup.' );
}

if ( ! wp_mkdir_p( $upload_directory ) ) {
	throw new RuntimeException( 'Could not create the target uploads directory.' );
}

foreach ( $source_files as $source_filename => $dimensions ) {
	$source_path      = trailingslashit( $asset_directory ) . $source_filename;
	$destination_path = trailingslashit( $upload_directory ) . $source_filename;

	if ( ! is_file( $destination_path ) || hash_file( 'sha256', $source_path ) !== hash_file( 'sha256', $destination_path ) ) {
		if ( ! copy( $source_path, $destination_path ) ) {
			throw new RuntimeException( 'Could not copy ' . $source_filename . ' into the Media Library uploads directory.' );
		}
	}
}

$attachment_url = trailingslashit( $upload['baseurl'] ) . $relative_file;
$attachment     = array(
	'post_title'     => 'McCafé Coffee Prices and Calories',
	'post_excerpt'   => 'McCafé coffee price and calorie summary covering hot, iced, frozen, and chocolate drinks.',
	'post_content'   => '',
	'post_status'    => 'inherit',
	'post_mime_type' => 'image/avif',
	'post_parent'    => $home_id,
	'guid'           => $attachment_url,
);

if ( $attachment_id ) {
	$attachment['ID'] = $attachment_id;
	$result           = wp_update_post( $attachment, true );
} else {
	$result = wp_insert_attachment(
		$attachment,
		trailingslashit( $upload_directory ) . $filename,
		$home_id,
		true
	);
}

if ( is_wp_error( $result ) || ! $result ) {
	throw new RuntimeException( is_wp_error( $result ) ? $result->get_error_message() : 'Could not create the attachment.' );
}

$attachment_id = (int) $result;
$metadata      = array(
	'width'     => 1672,
	'height'    => 941,
	'file'      => $relative_file,
	'filesize'  => filesize( trailingslashit( $upload_directory ) . $filename ),
	'sizes'     => array(
		'mcprices-400'  => array(
			'file'      => 'mccafe-coffee-prices-400x225.avif',
			'width'     => 400,
			'height'    => 225,
			'mime-type' => 'image/avif',
			'filesize'  => filesize( trailingslashit( $upload_directory ) . 'mccafe-coffee-prices-400x225.avif' ),
		),
		'mcprices-800'  => array(
			'file'      => 'mccafe-coffee-prices-800x450.avif',
			'width'     => 800,
			'height'    => 450,
			'mime-type' => 'image/avif',
			'filesize'  => filesize( trailingslashit( $upload_directory ) . 'mccafe-coffee-prices-800x450.avif' ),
		),
		'mcprices-1200' => array(
			'file'      => 'mccafe-coffee-prices-1200x675.avif',
			'width'     => 1200,
			'height'    => 675,
			'mime-type' => 'image/avif',
			'filesize'  => filesize( trailingslashit( $upload_directory ) . 'mccafe-coffee-prices-1200x675.avif' ),
		),
	),
	'image_meta' => array(),
);

update_post_meta( $attachment_id, '_wp_attached_file', $relative_file );
update_post_meta( $attachment_id, '_wp_attachment_metadata', $metadata );
update_post_meta( $attachment_id, '_wp_attachment_image_alt', $alt_text );
update_post_meta( $attachment_id, '_mcprices_mccafe_prices_image', '1' );
clean_post_cache( $attachment_id );

$image_figure_marker = '<figure class="mcprices-meal-prices-image mcprices-mccafe-prices-image">';
$image_html          = wp_get_attachment_image(
	$attachment_id,
	'full',
	false,
	array(
		'class'    => 'mcprices-mccafe-prices-image__media',
		'alt'      => $alt_text,
		'loading'  => 'lazy',
		'decoding' => 'async',
		'sizes'    => '(max-width: 782px) calc(100vw - 32px), (max-width: 1200px) calc(100vw - 48px), 1170px',
	)
);

if ( ! is_string( $image_html ) || '' === trim( $image_html ) ) {
	throw new RuntimeException( 'WordPress could not generate the McCafé prices image markup.' );
}

$figure_markup = $image_figure_marker . "\n\t\t\t\t\t" . $image_html . "\n\t\t\t\t</figure>";
$home_content  = (string) $home->post_content;
$updated_content = $home_content;

if ( false !== strpos( $home_content, $image_figure_marker ) ) {
	$updated_content = (string) preg_replace(
		'#<figure class="mcprices-meal-prices-image mcprices-mccafe-prices-image">.*?</figure>#s',
		$figure_markup,
		$home_content,
		1
	);
} else {
	$section_position = strpos( $home_content, '<div class="menu-section" id="mccafe"' );
	$heading_position = false !== $section_position ? strpos( $home_content, '<div class="menu-section-head">', $section_position ) : false;
	$heading_end      = false !== $heading_position ? strpos( $home_content, '</div>', $heading_position ) : false;

	if ( false === $section_position || false === $heading_position || false === $heading_end ) {
		throw new RuntimeException( 'The McCafé homepage section heading could not be located.' );
	}

	$insert_position = $heading_end + strlen( '</div>' );
	$updated_content = substr( $home_content, 0, $insert_position )
		. "\n\t\t\t\t"
		. $figure_markup
		. substr( $home_content, $insert_position );
}

if ( 1 !== substr_count( $updated_content, $image_figure_marker ) ) {
	throw new RuntimeException( 'The targeted homepage update did not produce exactly one McCafé prices image.' );
}

if ( $home_content !== $updated_content ) {
	$updated = wp_update_post(
		array(
			'ID'           => $home_id,
			'post_content' => $updated_content,
		),
		true
	);

	if ( is_wp_error( $updated ) ) {
		throw new RuntimeException( $updated->get_error_message() );
	}
}

$homepage_pattern = require get_theme_file_path( '/inc/mcprices/pattern-homepage.php' );

if ( ! is_string( $homepage_pattern ) || '' === trim( $homepage_pattern ) ) {
	throw new RuntimeException( 'The managed homepage pattern could not be generated.' );
}

update_option( 'mcprices_homepage_pattern_signature', hash( 'sha256', $homepage_pattern ), false );
clean_post_cache( $home_id );
do_action( 'litespeed_purge_url', home_url( '/' ) );

$stored_home = get_post( $home_id );
$stored_alt  = (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );

if (
	! $stored_home instanceof WP_Post
	|| 1 !== substr_count( (string) $stored_home->post_content, $image_figure_marker )
	|| $alt_text !== $stored_alt
	|| ! is_file( trailingslashit( $upload_directory ) . $filename )
) {
	throw new RuntimeException( 'Post-migration verification failed.' );
}

echo 'BACKUP_FILE=' . $backup_file . "\n";
echo 'ATTACHMENT_ID=' . $attachment_id . "\n";
echo 'ALT_TEXT=' . $stored_alt . "\n";
echo 'HOMEPAGE_IMAGE_COUNT=1' . "\n";
echo "MIGRATION_STATUS=success\n";

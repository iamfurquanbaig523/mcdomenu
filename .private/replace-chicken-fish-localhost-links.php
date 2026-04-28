<?php
require 'C:/xampp/htdocs/wordpress/wp-load.php';

$page = get_page_by_path( 'chicken-fish-menu', OBJECT, 'page' );

if ( ! $page instanceof WP_Post ) {
	fwrite( STDERR, "page-not-found\n" );
	exit( 1 );
}

$old_content = (string) $page->post_content;
$new_content = str_replace( 'http://localhost/wordpress', 'https://mcdomenuusa.com', $old_content );

if ( $new_content !== $old_content ) {
	$result = wp_update_post(
		array(
			'ID'           => (int) $page->ID,
			'post_content' => $new_content,
		),
		true
	);

	if ( is_wp_error( $result ) ) {
		fwrite( STDERR, $result->get_error_message() . "\n" );
		exit( 1 );
	}
}

echo 'page_id=' . (int) $page->ID . PHP_EOL;
echo 'localhost_remaining=' . substr_count( $new_content, 'http://localhost/wordpress' ) . PHP_EOL;
echo 'live_links=' . substr_count( $new_content, 'https://mcdomenuusa.com' ) . PHP_EOL;

<?php
require dirname(__DIR__) . '/wp-load.php';

$pages = get_posts(
	[
		'post_type'      => 'page',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'ID',
		'order'          => 'ASC',
	]
);

$rows = [];

foreach ( $pages as $page ) {
	$page_id     = (int) $page->ID;
	$support_key = (string) get_post_meta( $page_id, '_mcprices_support_page', true );
	$managed_key = (string) get_post_meta( $page_id, '_mcprices_managed_key', true );

	$rows[] = [
		'id'          => $page_id,
		'slug'        => (string) $page->post_name,
		'title'       => html_entity_decode( wp_strip_all_tags( $page->post_title ), ENT_QUOTES ),
		'parent_id'   => (int) $page->post_parent,
		'uri'         => (string) get_page_uri( $page_id ),
		'permalink'   => (string) get_permalink( $page_id ),
		'edit_url'    => (string) admin_url( 'post.php?post=' . $page_id . '&action=edit' ),
		'support_key' => $support_key,
		'managed_key' => $managed_key,
	];
}

echo wp_json_encode(
	[
		'home_url' => home_url( '/' ),
		'pages'    => $rows,
	],
	JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
);

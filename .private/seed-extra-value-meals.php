<?php
require 'C:/xampp/htdocs/wordpress/wp-load.php';
$page = get_page_by_path( 'extra-value-meals', OBJECT, 'page' );
if ( $page instanceof WP_Post ) {
    delete_post_meta( (int) $page->ID, '_mcprices_allow_custom_content' );
}
$integration = \Kadence\McPrices_Integration::get_instance();
$ref = new ReflectionClass( $integration );
$method = $ref->getMethod( 'maybe_seed_support_pages' );
$method->setAccessible( true );
$method->invoke( $integration );
$page = get_page_by_path( 'extra-value-meals', OBJECT, 'page' );
if ( $page instanceof WP_Post ) {
    echo 'page=' . $page->ID . "\n";
    echo 'title=' . $page->post_title . "\n";
    echo 'localhost=' . substr_count( (string) $page->post_content, 'http://localhost/wordpress' ) . "\n";
    echo 'live=' . substr_count( (string) $page->post_content, 'https://mcdomenuusa.com' ) . "\n";
}
?>

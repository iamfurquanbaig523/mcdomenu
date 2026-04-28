<?php
require 'C:/xampp/htdocs/wordpress/wp-load.php';
foreach ( array( 'breakfast-menu', 'chicken-fish-menu' ) as $slug ) {
    $page = get_page_by_path( $slug, OBJECT, 'page' );
    if ( ! $page instanceof WP_Post ) {
        continue;
    }
    $content = (string) $page->post_content;
    echo $slug . "\t" . $page->ID . "\tlocalhost=" . substr_count( $content, 'http://localhost/wordpress' ) . "\tlive=" . substr_count( $content, 'https://mcdomenuusa.com' ) . "\tmeta=" . (string) get_post_meta( $page->ID, '_mcprices_allow_custom_content', true ) . "\n";
}
?>

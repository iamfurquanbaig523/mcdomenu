<?php
require 'C:/xampp/htdocs/wordpress/wp-load.php';
foreach ( array( 'breakfast-menu', 'chicken-fish-menu' ) as $slug ) {
    $page = get_page_by_path( $slug, OBJECT, 'page' );
    if ( $page instanceof WP_Post ) {
        delete_post_meta( (int) $page->ID, '_mcprices_allow_custom_content' );
        echo $slug . "\t" . $page->ID . "\tmeta-cleared\n";
    }
}
?>

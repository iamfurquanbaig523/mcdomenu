<?php
require 'C:/xampp/htdocs/wordpress/wp-load.php';
foreach ( array( 'menu/fries-sides', 'fries-sides' ) as $path ) {
    $page = get_page_by_path( $path, OBJECT, 'page' );
    if ( $page instanceof WP_Post ) {
        echo $path . "\t" . $page->ID . "\t" . $page->post_title . "\t" . $page->post_name . "\t" . $page->post_status . "\n";
    }
}
?>

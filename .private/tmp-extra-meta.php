<?php
require 'C:/xampp/htdocs/wordpress/wp-load.php';
$page = get_page_by_path( 'extra-value-meals', OBJECT, 'page' );
if ( $page instanceof WP_Post ) {
    echo "meta=" . get_post_meta( $page->ID, '_mcprices_support_page', true ) . "\n";
}
?>

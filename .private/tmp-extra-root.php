<?php
require 'C:/xampp/htdocs/wordpress/wp-load.php';
$page = get_page_by_path( 'extra-value-meals', OBJECT, 'page' );
if ( $page instanceof WP_Post ) {
    echo "id\t{$page->ID}\n";
    echo "meta\t" . get_post_meta( $page->ID, '_mcprices_allow_custom_content', true ) . "\n";
    echo substr( (string) $page->post_content, 0, 500 );
}
?>

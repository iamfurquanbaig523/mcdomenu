<?php
require 'C:/xampp/htdocs/wordpress/wp-load.php';
$integration = \Kadence\McPrices_Integration::get_instance();
$ref = new ReflectionClass($integration);
$method = $ref->getMethod('maybe_seed_support_pages');
$method->setAccessible(true);
$changed = $method->invoke($integration);
echo 'changed=' . ($changed ? '1' : '0') . "\n";
foreach ( array( 'breakfast-menu', 'chicken-fish-menu' ) as $slug ) {
    $page = get_page_by_path( $slug, OBJECT, 'page' );
    if ( $page instanceof WP_Post ) {
        $content = (string) $page->post_content;
        echo $slug . "\tlocalhost=" . substr_count( $content, 'http://localhost/wordpress' ) . "\tlive=" . substr_count( $content, 'https://mcdomenuusa.com' ) . "\n";
    }
}
?>

<?php
require 'C:/xampp/htdocs/wordpress/wp-load.php';
$integration = \Kadence\McPrices_Integration::get_instance();
$ref = new ReflectionClass($integration);
$method = $ref->getMethod('get_seeded_file_content');
$method->setAccessible(true);
$content = (string) $method->invoke($integration, '/inc/mcprices/data/chicken-fish-menu-seeded-content.html');
echo 'localhost=' . substr_count($content, 'http://localhost/wordpress') . "\n";
echo 'live=' . substr_count($content, 'https://mcdomenuusa.com') . "\n";
?>

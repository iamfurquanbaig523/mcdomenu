<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require __DIR__ . '/../wp-load.php';
require_once __DIR__ . '/../wp-content/themes/kadence/inc/mcprices/class-mcprices-integration.php';
echo "loaded\n";
$integration = Kadence\McPrices_Integration::get_instance();
echo get_class($integration) . "\n";
$ref = new ReflectionClass($integration);
$method = $ref->getMethod('get_menu_directory_category_data');
$method->setAccessible(true);
$category = $method->invoke($integration, 'mccafe');
var_dump(is_array($category), count($category['items'] ?? []));
foreach (($category['items'] ?? []) as $item) {
    echo ($item['slug'] ?? '') . '|' . ($item['name'] ?? '') . '|' . ($item['price'] ?? '') . '|' . ($item['calories'] ?? '') . "\n";
}

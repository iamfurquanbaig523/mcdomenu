<?php
require 'wp-load.php';
$integration = null;
foreach ($GLOBALS['wp_filter']['init']->callbacks ?? [] as $priority => $callbacks) {
  foreach ($callbacks as $cb) {
    if (is_array($cb['function']) && is_object($cb['function'][0]) && method_exists($cb['function'][0], 'get_menu_directory_categories')) {
      $integration = $cb['function'][0];
      break 2;
    }
  }
}
if (!$integration) { echo "no integration\n"; exit; }
$ref = new ReflectionClass($integration);
$m = $ref->getMethod('get_menu_directory_categories');
$m->setAccessible(true);
$cats = $m->invoke($integration);
foreach ($cats as $id => $cat) {
  echo $id, "\t", $cat['slug'], "\t", $cat['title'], "\n";
}

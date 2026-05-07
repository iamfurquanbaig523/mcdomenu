<?php
require 'wp-load.php';
$integration = null;
foreach (($GLOBALS['wp_filter']['init']->callbacks ?? []) as $callbacks) {
  foreach ($callbacks as $cb) {
    if (is_array($cb['function']) && is_object($cb['function'][0]) && method_exists($cb['function'][0], 'get_menu_directory_categories')) {
      $integration = $cb['function'][0];
      break 2;
    }
  }
}
if (!$integration) { echo "integration not found\n"; exit(1); }
$ref = new ReflectionClass($integration);
$method = $ref->getMethod('get_menu_directory_categories');
$method->setAccessible(true);
$cats = $method->invoke($integration);

function count_main_words($url) {
  $html = @file_get_contents($url);
  if ($html === false) { return ['words' => -1, 'error' => 'fetch']; }
  libxml_use_internal_errors(true);
  $dom = new DOMDocument();
  $dom->loadHTML($html);
  $xpath = new DOMXPath($dom);
  $nodes = $xpath->query('//main');
  $text = '';
  if ($nodes && $nodes->length > 0) {
    $text = $nodes->item(0)->textContent;
  } else {
    $text = strip_tags($html);
  }
  $words = str_word_count(preg_replace('/\s+/', ' ', trim($text)));
  return ['words' => $words];
}

$category_under = [];
$item_under = [];
$total_items = 0;
foreach ($cats as $category_id => $category) {
  $cat_url = home_url('/menu/' . $category['slug'] . '/');
  $cat = count_main_words($cat_url);
  if ($cat['words'] < 400) {
    $category_under[] = [$category['slug'], $cat['words']];
  }
  foreach ($category['items'] as $item) {
    $total_items++;
    $url = home_url('/menu/' . $category['slug'] . '/' . $item['slug'] . '/');
    $res = count_main_words($url);
    if ($res['words'] < 400) {
      $item_under[] = [$category['slug'] . '/' . $item['slug'], $res['words']];
    }
  }
}

echo "CATEGORY_UNDER_400=" . count($category_under) . "\n";
foreach ($category_under as $row) { echo implode("\t", $row), "\n"; }
echo "ITEM_UNDER_400=" . count($item_under) . " of $total_items\n";
foreach (array_slice($item_under, 0, 30) as $row) { echo implode("\t", $row), "\n"; }

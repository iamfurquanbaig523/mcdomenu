<?php
require 'wp-load.php';
$slugs = ['mcdvoice','mango-pineapple-smoothie-large-2','mcdonalds-deals-mcvalue-guide-2','mcdonalds-nutrition-calories-allergens-2','mcdonalds-prices-by-state-2','sample-page'];
$out = [];
foreach ($slugs as $slug) {
  $page = get_page_by_path($slug, OBJECT, 'page');
  if ($page) {
    $out[] = [
      'slug' => $slug,
      'id' => $page->ID,
      'status' => $page->post_status,
      'title' => $page->post_title,
      'parent' => $page->post_parent,
      'menu_order' => $page->menu_order,
    ];
  } else {
    $out[] = ['slug' => $slug, 'missing' => true];
  }
}
echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

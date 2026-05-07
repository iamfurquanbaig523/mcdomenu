<?php
require 'wp-load.php';
$targets = [
  'mcdonalds-deals-mcvalue-guide',
  'mcdonalds-deals-mcvalue-guide-2',
  'mcdonalds-nutrition-calories-allergens',
  'mcdonalds-nutrition-calories-allergens-2',
  'mcdonalds-prices-by-state',
  'mcdonalds-prices-by-state-2',
  'sample-page',
  'mcdvoice'
];
$out = [];
foreach ($targets as $slug) {
  $page = get_page_by_path($slug, OBJECT, 'page');
  if (! $page) {
    $out[] = ['slug' => $slug, 'missing' => true];
    continue;
  }
  $out[] = [
    'slug' => $slug,
    'id' => $page->ID,
    'title' => $page->post_title,
    'allow_custom' => get_post_meta($page->ID, '_mcprices_allow_custom_content', true),
    'rank_math_robots' => get_post_meta($page->ID, 'rank_math_robots', true),
    'rank_math_title' => get_post_meta($page->ID, 'rank_math_title', true),
    'rank_math_desc_length' => strlen((string) get_post_meta($page->ID, 'rank_math_description', true)),
  ];
}
echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

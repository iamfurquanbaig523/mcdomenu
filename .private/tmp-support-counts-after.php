<?php
require 'wp-load.php';
$slugs = ['big-mac-price-usa','big-mac-price-uk','mcdvoice','mcdonalds-app-deals','calorie-counter','breakfast-hours','breakfast-times','allergen-guide','vegan-options','price-history','delivery-guide','mcdelivery-guide','rewards-guide','limited-time-menu','snack-wrap','dollar-menu','shareables-bundles'];
foreach ($slugs as $slug) {
  $page = get_page_by_path($slug, OBJECT, 'page');
  if (!$page) { echo "MISS\t$slug\n"; continue; }
  $rendered = do_blocks($page->post_content);
  $words = str_word_count(wp_strip_all_tags($rendered));
  $meta = get_post_meta($page->ID,'_mcprices_support_page',true);
  echo $slug, "\t", $page->ID, "\t", $words, "\t", $meta, "\n";
}

<?php
require 'wp-load.php';
$targets = ['big-mac-price-uk','breakfast-hours','breakfast-times','calorie-counter','delivery-guide','limited-time-menu','mcdelivery-guide','mcdonalds-app-deals','price-history','rewards-guide','vegan-options','allergen-guide','shareables-bundles','snack-wrap','dollar-menu'];
foreach ($targets as $slug) {
  $page = get_page_by_path($slug, OBJECT, 'page');
  if (!$page) { echo "MISS\t$slug\n"; continue; }
  $words = str_word_count(wp_strip_all_tags(do_blocks($page->post_content)));
  echo $slug, "\t", $page->ID, "\t", $words, "\n";
}

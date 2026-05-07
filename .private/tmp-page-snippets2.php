<?php
require 'wp-load.php';
$slugs = ['big-mac-price-usa','big-mac-price-uk','breakfast-hours','breakfast-times','delivery-guide','mcdelivery-guide','mcdonalds-app-deals','rewards-guide','limited-time-menu','shareables-bundles','snack-wrap','vegan-options','allergen-guide'];
foreach ($slugs as $slug) {
  $page = get_page_by_path($slug, OBJECT, 'page');
  echo "===== $slug =====\n";
  if (!$page) { echo "missing\n"; continue; }
  echo substr($page->post_content,0,1200), "\n\n";
}

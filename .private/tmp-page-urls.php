<?php
require_once 'c:/xampp/htdocs/wordpress/wp-load.php';
$targets = ['big-mac-price-uk','breakfast-hours','breakfast-times','calorie-counter','delivery-guide','limited-time-menu','mcdelivery-guide','mcdonalds-app-deals','price-history','rewards-guide','vegan-options','allergen-guide','shareables-bundles','snack-wrap','dollar-menu'];
foreach ($targets as $slug) {
  $page = get_page_by_path($slug, OBJECT, 'page');
  if (!$page) continue;
  echo "===== $slug =====\n";
  echo get_permalink($page), "\n";
}
?>

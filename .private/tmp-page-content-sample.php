<?php
require_once 'c:/xampp/htdocs/wordpress/wp-load.php';
$pages = ['big-mac-price-uk','breakfast-hours','breakfast-times','calorie-counter','delivery-guide','limited-time-menu','mcdelivery-guide','mcdonalds-app-deals','price-history','rewards-guide','vegan-options','allergen-guide','shareables-bundles','snack-wrap','dollar-menu'];
foreach ($pages as $slug) {
  $page = get_page_by_path($slug, OBJECT, 'page');
  if (!$page) continue;
  echo "===== $slug =====\n";
  echo substr($page->post_content, 0, 1000), "\n\n";
}
?>

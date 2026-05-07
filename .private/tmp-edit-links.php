<?php
require_once 'c:/xampp/htdocs/wordpress/wp-load.php';
$targets = ['breakfast-hours','breakfast-times','big-mac-price-usa','big-mac-price-uk','mcdonalds-app-deals','rewards-guide','delivery-guide','mcdelivery-guide','limited-time-menu','price-history','vegan-options','allergen-guide','shareables-bundles','snack-wrap','dollar-menu','calorie-counter'];
foreach ($targets as $slug) {
  $page = get_page_by_path($slug, OBJECT, 'page');
  if (!$page) continue;
  $edit = admin_url('post.php?post=' . $page->ID . '&action=edit');
  echo $slug, "\t", $page->ID, "\t", $edit, "\n";
}
?>

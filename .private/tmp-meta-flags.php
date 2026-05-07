<?php
require_once 'c:/xampp/htdocs/wordpress/wp-load.php';
$slugs = ['big-mac-price-uk','breakfast-hours','breakfast-times','calorie-counter','delivery-guide','limited-time-menu','mcdelivery-guide','mcdonalds-app-deals','price-history','rewards-guide','vegan-options','allergen-guide','shareables-bundles','snack-wrap','dollar-menu'];
foreach ($slugs as $slug) {
  $page = get_page_by_path($slug, OBJECT, 'page');
  if (!$page) continue;
  $custom = get_post_meta($page->ID, '_mcprices_allow_custom_content', true);
  $support = get_post_meta($page->ID, '_mcprices_support_page', true);
  echo $slug, "\tcustom=", ($custom ?: '0'), "\tsupport=", ($support ?: '0'), "\n";
}
?>

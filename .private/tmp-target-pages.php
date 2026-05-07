<?php
require_once 'c:/xampp/htdocs/wordpress/wp-load.php';
$targets = [
 'big-mac-price-uk','breakfast-hours','breakfast-times','calorie-counter','delivery-guide','limited-time-menu','mcdelivery-guide','mcdonalds-app-deals','price-history','rewards-guide','vegan-options','allergen-guide','about','contact','privacy-policy','cookie-policy','disclaimer','ad-disclosure','sitemap','shareables-bundles','snack-wrap','dollar-menu'
];
foreach ($targets as $slug) {
  $page = get_page_by_path($slug, OBJECT, 'page');
  if (!$page) { echo "MISSING\t$slug\n"; continue; }
  $text = trim(wp_strip_all_tags($page->post_content));
  $words = str_word_count(preg_replace('/\s+/', ' ', html_entity_decode($text)));
  echo $slug, "\t", $page->ID, "\t", $words, "\t", get_permalink($page), "\n";
}
?>

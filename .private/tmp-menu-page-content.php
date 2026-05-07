<?php
require_once 'c:/xampp/htdocs/wordpress/wp-load.php';
$targets = ['breakfast-menu','burgers-menu','chicken-fish','mccafe-coffees','deals-and-offers'];
foreach ($targets as $slug) {
  $page = get_page_by_path('menu/' . $slug, OBJECT, 'page');
  if (!$page) { echo "MISSING\t$slug\n"; continue; }
  echo "===== $slug =====\n";
  echo substr($page->post_content, 0, 1000), "\n\n";
}
?>

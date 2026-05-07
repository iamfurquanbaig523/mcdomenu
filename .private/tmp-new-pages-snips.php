<?php
require 'wp-load.php';
foreach (['mcdvoice','vegan-options','big-mac-price-uk','mcdelivery-guide'] as $slug) {
  $page = get_page_by_path($slug, OBJECT, 'page');
  echo "===== $slug =====\n";
  if (!$page) { echo "missing\n"; continue; }
  echo substr($page->post_content,0,1800), "\n\n";
}

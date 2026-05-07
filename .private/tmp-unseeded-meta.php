<?php
require 'wp-load.php';
foreach (['big-mac-price-uk','breakfast-times','mcdelivery-guide','vegan-options'] as $slug) {
  $page=get_page_by_path($slug, OBJECT, 'page');
  if (!$page) { echo "MISS\t$slug\n"; continue; }
  echo $slug, "\t", get_post_meta($page->ID,'_mcprices_support_page',true), "\t", get_post_meta($page->ID,'_mcprices_allow_custom_content',true), "\n";
}

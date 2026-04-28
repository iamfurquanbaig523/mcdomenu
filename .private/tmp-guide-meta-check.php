<?php
require 'C:/xampp/htdocs/wordpress/wp-load.php';
foreach (array('breakfast-menu','chicken-fish-menu') as $slug) {
  $page = get_page_by_path($slug, OBJECT, 'page');
  if ($page) {
    echo $slug . "\t" . $page->ID . "\t" . get_post_meta($page->ID, '_mcprices_allow_custom_content', true) . "\n";
  }
}
?>

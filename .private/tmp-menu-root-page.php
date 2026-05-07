<?php
require 'wp-load.php';
$page = get_page_by_path('menu');
if ($page) {
  echo json_encode([
    'id' => $page->ID,
    'title' => $page->post_title,
    'managed' => get_post_meta($page->ID, '_mcprices_managed_page', true),
    'support' => get_post_meta($page->ID, '_mcprices_support_page', true),
    'content_snippet' => substr($page->post_content, 0, 5000)
  ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}

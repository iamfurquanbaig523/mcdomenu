<?php
require 'wp-load.php';
$pages = get_posts([
  'post_type' => 'page',
  'post_status' => 'publish',
  'numberposts' => -1,
  'orderby' => 'name',
  'order' => 'ASC',
]);
$out = [];
foreach ($pages as $page) {
  if (preg_match('/-\d+$/', $page->post_name) || in_array($page->post_name, ['sample-page','blog'], true)) {
    $out[] = [
      'id' => $page->ID,
      'slug' => $page->post_name,
      'title' => $page->post_title,
      'permalink' => get_permalink($page),
    ];
  }
}
echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

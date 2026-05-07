<?php
require 'wp-load.php';
$pages = get_posts([
  'post_type' => 'page',
  'post_status' => 'publish',
  'posts_per_page' => -1,
  'orderby' => 'menu_order title',
  'order' => 'ASC',
]);
$rows = [];
foreach ($pages as $page) {
  $rendered = do_blocks($page->post_content);
  $words = str_word_count(wp_strip_all_tags($rendered));
  $rows[] = [
    'id' => $page->ID,
    'slug' => $page->post_name,
    'title' => $page->post_title,
    'words' => $words,
    'support' => get_post_meta($page->ID, '_mcprices_support_page', true),
  ];
}
usort($rows, fn($a,$b) => $a['words'] <=> $b['words']);
foreach ($rows as $row) {
  echo implode("\t", [$row['id'],$row['slug'],$row['words'],$row['support'],$row['title']]), "\n";
}

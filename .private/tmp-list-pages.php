<?php
require_once 'c:/xampp/htdocs/wordpress/wp-load.php';
$pages = get_posts([
  'post_type' => 'page',
  'post_status' => ['publish','draft','private','future','pending'],
  'posts_per_page' => -1,
  'orderby' => 'title',
  'order' => 'ASC',
]);
foreach ($pages as $page) {
  $text = trim(wp_strip_all_tags($page->post_content));
  $words = str_word_count(preg_replace('/\s+/', ' ', html_entity_decode($text)));
  echo $page->ID, "\t", $page->post_name, "\t", $words, "\t", $page->post_status, "\t", $page->post_title, "\n";
}
?>

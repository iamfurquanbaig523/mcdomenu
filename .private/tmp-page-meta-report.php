<?php
require 'wp-load.php';
$pages = get_posts([
  'post_type' => 'page',
  'post_status' => 'publish',
  'posts_per_page' => -1,
  'orderby' => 'ID',
  'order' => 'ASC',
]);
foreach ($pages as $page) {
  $support = get_post_meta($page->ID, '_mcprices_support_page', true);
  $managed = get_post_meta($page->ID, '_mcprices_managed_key', true);
  echo implode("\t", [
    $page->ID,
    $page->post_name,
    str_replace(["\r","\n","\t"], ' ', html_entity_decode($page->post_title, ENT_QUOTES)),
    $support,
    $managed,
    $page->post_parent,
  ]), "\n";
}

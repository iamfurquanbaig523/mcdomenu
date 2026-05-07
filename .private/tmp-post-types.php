<?php
require 'wp-load.php';
$post_types = get_post_types(['public' => true], 'objects');
$out = [];
foreach ($post_types as $type => $obj) {
  $counts = wp_count_posts($type);
  $out[] = [
    'post_type' => $type,
    'label' => $obj->label,
    'publicly_queryable' => $obj->publicly_queryable,
    'rewrite' => $obj->rewrite,
    'publish_count' => isset($counts->publish) ? (int) $counts->publish : 0,
  ];
}
echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

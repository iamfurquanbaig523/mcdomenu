<?php
require 'wp-load.php';
require_once ABSPATH . 'wp-admin/includes/plugin.php';
$summary = [];
$summary['home_url'] = home_url('/');
$summary['public'] = get_option('blog_public');
$summary['permalink_structure'] = get_option('permalink_structure');
$summary['front_page'] = get_option('page_on_front');
$summary['posts_page'] = get_option('page_for_posts');
$summary['site_title'] = get_bloginfo('name');
$summary['site_tagline'] = get_bloginfo('description');
$summary['rank_math_active'] = is_plugin_active('seo-by-rank-math/rank-math.php');
$summary['wp_sitemaps_available'] = function_exists('wp_sitemaps_get_server');
$summary['page_count'] = (int) wp_count_posts('page')->publish;
$summary['post_count'] = (int) wp_count_posts('post')->publish;
$summary['attachment_count'] = (int) wp_count_posts('attachment')->inherit;
$summary['category_count'] = wp_count_terms(['taxonomy' => 'category', 'hide_empty' => false]);
$summary['menu_taxonomy_count'] = taxonomy_exists('category') ? wp_count_terms(['taxonomy' => 'category', 'hide_empty' => false]) : null;
echo json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

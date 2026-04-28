<?php
require 'C:/xampp/htdocs/wordpress/wp-load.php';
$post = get_page_by_path('menu/chicken-fish', OBJECT, 'page');
if ($post) {
    echo "menu_page\t{$post->ID}\t{$post->post_title}\t{$post->post_name}\n";
}
$post2 = get_page_by_path('chicken-fish-menu', OBJECT, 'page');
if ($post2) {
    echo "root_page\t{$post2->ID}\t{$post2->post_title}\t{$post2->post_name}\n";
}
?>

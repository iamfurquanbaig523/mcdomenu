<?php
require 'C:/xampp/htdocs/wordpress/wp-load.php';
$page = get_page_by_path('chicken-fish-menu', OBJECT, 'page');
if ($page) {
    echo "id\t{$page->ID}\n";
    echo "title\t{$page->post_title}\n";
    echo "status\t{$page->post_status}\n";
    echo substr($page->post_content, 0, 500);
}
?>

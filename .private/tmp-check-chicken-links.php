<?php
require 'C:/xampp/htdocs/wordpress/wp-load.php';
$page = get_page_by_path('chicken-fish-menu', OBJECT, 'page');
if ($page) {
    $count = substr_count($page->post_content, 'http://localhost/wordpress');
    echo "page\t{$page->ID}\t{$count}\n";
}
?>

<?php
require 'C:/xampp/htdocs/wordpress/wp-load.php';
$page = get_page_by_path('chicken-fish-menu', OBJECT, 'page');
if (!$page) { echo "no-page\n"; exit(1); }
echo "ok\t{$page->ID}\t{$page->post_title}\n";
?>

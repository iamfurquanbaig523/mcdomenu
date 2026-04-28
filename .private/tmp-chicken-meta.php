<?php
require 'C:/xampp/htdocs/wordpress/wp-load.php';
$meta = get_post_meta(194, '_mcprices_allow_custom_content', true);
echo $meta === '' ? 'empty' : $meta;
?>

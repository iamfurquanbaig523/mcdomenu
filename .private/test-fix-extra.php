<?php
$path = 'C:/xampp/htdocs/wordpress/wp-content/themes/kadence/inc/mcprices/data/extra-value-meals-seeded-content.html';
$content = file_get_contents($path);
if (!is_string($content)) { exit(1); }
$fixed = mb_convert_encoding($content, 'UTF-8', 'Windows-1252');
echo substr($fixed, 0, 500);
?>

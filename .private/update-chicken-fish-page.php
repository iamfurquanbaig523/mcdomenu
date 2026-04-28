<?php
require 'C:/xampp/htdocs/wordpress/wp-load.php';
$html = file_get_contents('C:/xampp/htdocs/wordpress/.private/chicken-fish-article.final.html');
if (false === $html) {
    fwrite(STDERR, "failed-to-read-html\n");
    exit(1);
}
$result = wp_update_post(array(
    'ID' => 194,
    'post_content' => $html,
), true);
if (is_wp_error($result)) {
    fwrite(STDERR, $result->get_error_message() . "\n");
    exit(1);
}
update_post_meta(194, '_mcprices_allow_custom_content', '1');
clean_post_cache(194);
echo "updated\t" . $result . "\n";
echo "meta\t" . get_post_meta(194, '_mcprices_allow_custom_content', true) . "\n";
?>

<?php
require 'wp-load.php';
foreach ([115,118,121,122,1431] as $id) {
  echo "ID $id\n";
  $all = get_post_meta($id);
  foreach ($all as $k => $v) {
    if (false !== strpos($k, 'mcprices')) {
      echo $k, ' => ', json_encode($v), "\n";
    }
  }
  echo "\n";
}

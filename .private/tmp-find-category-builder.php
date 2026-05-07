<?php
$path = 'wp-content/themes/kadence/inc/mcprices/class-mcprices-integration.php';
$lines = file($path);
for ($i=0; $i<count($lines); $i++) {
  if (strpos($lines[$i], 'CATEGORY PAGE') !== false || strpos($lines[$i], 'Tracked items') !== false || strpos($lines[$i], 'mcprices_menu_category') !== false) {
    $start = max(0, $i-30);
    $end = min(count($lines)-1, $i+60);
    for ($j=$start; $j<=$end; $j++) {
      echo ($j+1) . ':' . $lines[$j];
    }
    echo "\n=====\n";
  }
}

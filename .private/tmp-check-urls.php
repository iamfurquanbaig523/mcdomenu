<?php
require_once 'c:/xampp/htdocs/wordpress/wp-load.php';
$urls = [
 'official_menu' => 'https://www.mcdonalds.com/us/en-us/full-menu.html',
 'official_breakfast' => 'https://www.mcdonalds.com/us/en-us/full-menu/breakfast.html',
 'official_nutrition' => 'https://www.mcdonalds.com/us/en-us/about-our-food/nutrition-calculator.html',
 'official_deals' => 'https://www.mcdonalds.com/us/en-us/deals.html',
 'official_app' => 'https://www.mcdonalds.com/us/en-us/download-app.html',
 'official_rewards' => 'https://www.mcdonalds.com/us/en-us/mymcdonalds.html',
 'official_mcdelivery' => 'https://www.mcdonalds.com/us/en-us/faq/mcdelivery.html',
 'official_mcdvoice' => 'https://www.mcdvoice.com/',
 'official_food' => 'https://www.mcdonalds.com/us/en-us/about-our-food.html',
 'official_uk_menu' => 'https://www.mcdonalds.com/gb/en-gb/menu.htm.html',
 'official_uk_burgers' => 'https://www.mcdonalds.com/gb/en-gb/menu/burgers.html'
];
foreach ($urls as $key => $url) {
  $headers = @get_headers($url, true);
  $status = is_array($headers) && isset($headers[0]) ? $headers[0] : 'FAILED';
  echo $key, "\t", $status, "\t", $url, "\n";
}
?>

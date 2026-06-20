<?php
/**
 * Homepage block pattern content for the McPrices USA layout.
 *
 * @package kadence
 */

$current_year = wp_date( 'Y', null, wp_timezone() );

$page_url = static function ( $path ) {
	return home_url( '/' . trim( (string) $path, '/' ) . '/' );
};

$mcprices_integration = class_exists( '\Kadence\McPrices_Integration' ) ? \Kadence\McPrices_Integration::get_instance() : null;

$get_category_page_url = static function ( $category_id ) use ( $mcprices_integration ) {
	if ( $mcprices_integration ) {
		return $mcprices_integration->get_menu_category_page_url( $category_id );
	}

	return '#' . ltrim( (string) $category_id, '#' );
};

$get_item_page_url = static function ( $category_id, $item_identifier ) use ( $mcprices_integration, $get_category_page_url ) {
	if ( $mcprices_integration ) {
		return $mcprices_integration->get_menu_item_page_url( $category_id, $item_identifier );
	}

	return $get_category_page_url( $category_id );
};

$get_category_media_url = static function ( $category_id ) use ( $mcprices_integration ) {
	if ( $mcprices_integration ) {
		return $mcprices_integration->get_menu_category_media_asset_url( $category_id );
	}

	return '';
};

$get_item_media_url = static function ( $item_name ) use ( $mcprices_integration ) {
	if ( $mcprices_integration ) {
		return $mcprices_integration->get_menu_item_media_asset_url( $item_name );
	}

	return '';
};

$get_home_item_media_url = static function ( $item_name, $size = 'full' ) use ( $get_item_media_url ) {
	$item_media_url = $get_item_media_url( $item_name );

	if ( '' === $item_media_url ) {
		return '';
	}

	if ( preg_match( '#/items/([^/]+)\.(?:png|jpe?g)$#i', $item_media_url, $matches ) ) {
		if ( 'full' !== $size ) {
			$homepage_relative_path = '/assets/images/mcprices/homepage/items/' . $matches[1] . '-' . preg_replace( '/[^0-9a-z_-]/i', '', (string) $size ) . '.jpg';

			if ( file_exists( get_theme_file_path( $homepage_relative_path ) ) ) {
				return get_theme_file_uri( $homepage_relative_path );
			}
		}

		$jpg_relative_path = '/assets/images/mcprices/official/items/' . $matches[1] . '.jpg';

		if ( file_exists( get_theme_file_path( $jpg_relative_path ) ) ) {
			return get_theme_file_uri( $jpg_relative_path );
		}
	}

	return $item_media_url;
};

$interactive_tools = array(
	array(
		'slug'        => 'budget-finder',
		'icon'        => '&#128176;',
		'title'       => 'Budget Meal Finder',
		'short_title' => 'Budget Finder',
		'desc'        => 'Find the best meal under $5, $8, or $10',
		'url'         => $page_url( 'budget-finder' ),
		'cta'         => 'Try it here',
	),
	array(
		'slug'        => 'calorie-calculator',
		'icon'        => '&#128290;',
		'title'       => 'Meal Calorie Builder',
		'short_title' => 'Calorie Builder',
		'desc'        => 'Add items and track total calories + cost',
		'url'         => $page_url( 'calorie-calculator' ),
		'cta'         => 'Build a meal',
	),
	array(
		'slug'        => 'compare-items',
		'icon'        => '&#9878;',
		'title'       => 'Compare Menu Items',
		'short_title' => 'Compare Items',
		'desc'        => 'Side-by-side price, calorie &amp; value comparison',
		'url'         => $page_url( 'compare-items' ),
		'cta'         => 'Compare here',
	),
);

$menu_source_path = get_theme_file_path( 'assets/data/mcprices-usa-menu.json' );
$menu_source_json = file_exists( $menu_source_path ) ? file_get_contents( $menu_source_path ) : false;
$menu_source      = is_string( $menu_source_json ) ? json_decode( $menu_source_json, true ) : array();
$menu_source      = is_array( $menu_source ) ? $menu_source : array();

$get_menu_section = static function ( $source_id ) use ( $menu_source ) {
	foreach ( $menu_source as $section ) {
		if ( isset( $section['id'] ) && $source_id === $section['id'] ) {
			return $section;
		}
	}

	return array(
		'id'   => $source_id,
		'title' => '',
		'note' => '',
		'subs' => array(),
	);
};

$count_menu_rows = static function ( array $section ) {
	$count = 0;

	foreach ( $section['subs'] ?? array() as $sub_section ) {
		$count += count( $sub_section['rows'] ?? array() );
	}

	return $count;
};

$format_calories = static function ( $calories ) {
	$calories = trim( (string) $calories );

	if ( '' === $calories ) {
		return '&mdash;';
	}

	if ( false !== stripos( $calories, 'kcal' ) ) {
		return $calories;
	}

	return $calories . ' kcal';
};

$find_menu_row = static function ( $source_id, $needle ) use ( $get_menu_section ) {
	$section = $get_menu_section( $source_id );

	foreach ( $section['subs'] ?? array() as $sub_section ) {
		foreach ( $sub_section['rows'] ?? array() as $row ) {
			$row_name = isset( $row['name'] ) ? (string) $row['name'] : '';

			if ( '' !== $row_name && false !== stripos( $row_name, $needle ) ) {
				return $row;
			}
		}
	}

	return array(
		'name'     => $needle,
		'price'    => '',
		'calories' => '',
	);
};

$flatten_menu_rows = static function ( array $section, $source_id ) use ( $format_calories ) {
	$rows = array();

	foreach ( $section['subs'] ?? array() as $sub_section ) {
		$sub_label = isset( $sub_section['sub'] ) ? (string) $sub_section['sub'] : '';

		foreach ( $sub_section['rows'] ?? array() as $row ) {
			$name     = isset( $row['name'] ) ? (string) $row['name'] : '';
			$price    = isset( $row['price'] ) ? (string) $row['price'] : '&mdash;';
			$calories = isset( $row['calories'] ) ? (string) $row['calories'] : '';
			$tags     = isset( $row['tags'] ) && is_array( $row['tags'] ) ? $row['tags'] : array();
			$status   = 'Item';

			switch ( $source_id ) {
				case 'evm':
					$status = false !== stripos( $sub_label, 'Breakfast' ) ? 'Breakfast meal' : 'Meal';
					break;
				case 'mcvalue':
					if ( false !== stripos( $sub_label, 'Meal Deals' ) ) {
						$status = 'Meal deal';
					} elseif ( false !== stripos( $sub_label, 'Breakfast' ) ) {
						$status = 'Breakfast value';
					} elseif ( false !== stripos( $sub_label, 'Lunch' ) ) {
						$status = 'Any-day value';
					} elseif ( false !== stripos( $sub_label, 'Eats' ) ) {
						$status = 'Value pick';
					} else {
						$status = 'McValue';
					}
					break;
				case 'bfast':
					$status = 'Breakfast';
					break;
				case 'burgers':
					$status = 'Burger';
					break;
				case 'chicken':
					$status = false !== stripos( $name, 'Fish' ) ? 'Fish' : 'Chicken';
					break;
				case 'nuggets':
					if ( false !== stripos( $name, 'Strips' ) ) {
						$status = in_array( 'new', $tags, true ) ? 'New' : 'Strips';
					} elseif ( false !== stripos( $name, '20 pc' ) || false !== stripos( $name, '40 pc' ) ) {
						$status = 'Shareable';
					} else {
						$status = 'Nuggets';
					}
					break;
				case 'wrap':
					$status = 'Wrap';
					break;
				case 'sides':
					$status = false !== stripos( $name, 'Apple' ) ? 'Fruit' : 'Side';
					break;
				case 'happy':
					$status = 'Kids';
					break;
				case 'sweets':
					if ( in_array( 'ltd', $tags, true ) ) {
						$status = 'Limited';
					} elseif ( false !== stripos( $name, 'McFlurry' ) ) {
						$status = 'McFlurry';
					} elseif ( false !== stripos( $name, 'Shake' ) ) {
						$status = 'Shake';
					} elseif ( false !== stripos( $name, 'Pie' ) || false !== stripos( $name, 'Cookie' ) ) {
						$status = 'Baked';
					} else {
						$status = 'Dessert';
					}
					break;
				case 'coffee':
					if ( false !== stripos( $sub_label, 'Frapp' ) ) {
						$status = 'Frozen';
					} elseif ( false !== stripos( $sub_label, 'Iced' ) ) {
						$status = 'Iced';
					} elseif ( false !== stripos( $sub_label, 'Hot Chocolate' ) ) {
						$status = 'Hot chocolate';
					} else {
						$status = 'Coffee';
					}
					break;
				case 'bev':
					if ( false !== stripos( $sub_label, 'Frozen' ) ) {
						$status = 'Frozen';
					} elseif ( false !== stripos( $sub_label, 'Smoothies' ) ) {
						$status = 'Smoothie';
					} elseif ( false !== stripos( $sub_label, 'Tea' ) ) {
						$status = 'Tea';
					} elseif ( false !== stripos( $sub_label, 'Juice' ) ) {
						$status = 'Juice';
					} elseif ( false !== stripos( $sub_label, 'Water' ) ) {
						$status = 'Water';
					} else {
						$status = 'Drink';
					}
					break;
				case 'sauce':
					$status = in_array( $price, array( 'Incl.', 'Free' ), true ) ? 'Included' : 'Sauce';
					break;
			}

			$rows[] = array(
				'name'     => $name,
				'price'    => $price,
				'calories' => $format_calories( $calories ),
				'status'   => $status,
			);
		}
	}

	return $rows;
};

$kpop_section     = $get_menu_section( 'kpop' );
$bigarch_section  = $get_menu_section( 'bigarch' );
$evm_section      = $get_menu_section( 'evm' );
$mcvalue_section  = $get_menu_section( 'mcvalue' );
$breakfast_section = $get_menu_section( 'bfast' );
$burgers_section  = $get_menu_section( 'burgers' );
$chicken_section  = $get_menu_section( 'chicken' );
$nuggets_section  = $get_menu_section( 'nuggets' );
$wrap_section     = $get_menu_section( 'wrap' );
$sides_section    = $get_menu_section( 'sides' );
$happy_section    = $get_menu_section( 'happy' );
$sweets_section   = $get_menu_section( 'sweets' );
$coffee_section   = $get_menu_section( 'coffee' );
$beverages_section = $get_menu_section( 'bev' );
$sauces_section   = $get_menu_section( 'sauce' );

$whats_new_total = $count_menu_rows( $kpop_section ) + $count_menu_rows( $bigarch_section );

$big_mac_row           = $find_menu_row( 'burgers', 'Big Mac' );
$mcgriddles_row        = $find_menu_row( 'bfast', 'Sausage, Egg & Cheese McGriddles' );
$mccrispy_row          = $find_menu_row( 'chicken', 'McCrispy' );
$oreo_mcflurry_row     = $find_menu_row( 'sweets', 'OREO McFlurry' );
$large_fries_row       = $find_menu_row( 'sides', 'World Famous Fries Large' );
$egg_mcmuffin_row      = $find_menu_row( 'bfast', 'Egg McMuffin' );
$nuggets_six_row       = $find_menu_row( 'nuggets', '6 pc Chicken McNuggets' );
$huntrix_row           = $find_menu_row( 'kpop', 'The HUNTRIX Meal' );
$saja_boys_row         = $find_menu_row( 'kpop', 'The Saja Boys Breakfast Meal' );
$ramyeon_fries_row     = $find_menu_row( 'kpop', 'Ramyeon McShaker' );
$big_arch_row          = $find_menu_row( 'bigarch', 'The BIG ARCHTM (' );
$big_arch_meal_row     = $find_menu_row( 'bigarch', 'The BIG ARCHTM Meal' );
$big_mac_meal_row      = $find_menu_row( 'evm', 'Big Mac Meal' );
$nuggets_ten_meal_row  = $find_menu_row( 'evm', '10 pc Chicken McNuggets Meal' );
$sausage_mcmuffin_meal_row = $find_menu_row( 'evm', 'Sausage McMuffin with Egg Meal' );
$sausage_biscuit_meal_row  = $find_menu_row( 'evm', 'Sausage Biscuit with Egg Meal' );
$world_cup_guide_url        = $page_url( 'mcdonalds-fifa-world-cup-meal' );
$format_world_cup_price_range = static function ( array $row, $fallback_min, $fallback_max ) {
	$minimum = isset( $row['price_min'] ) ? (float) $row['price_min'] : (float) $fallback_min;
	$maximum = isset( $row['price_max'] ) ? (float) $row['price_max'] : (float) $fallback_max;

	return '$' . number_format( $minimum, 2 ) . '-$' . number_format( $maximum, 2 ) . ' est.';
};
$world_cup_big_mac_range          = $format_world_cup_price_range( $big_mac_meal_row, 8.95, 11.45 );
$world_cup_nuggets_range          = $format_world_cup_price_range( $nuggets_ten_meal_row, 8.85, 11.35 );
$world_cup_sausage_mcmuffin_range = $format_world_cup_price_range( $sausage_mcmuffin_meal_row, 8.45, 10.95 );
$world_cup_sausage_biscuit_range  = $format_world_cup_price_range( $sausage_biscuit_meal_row, 8.25, 10.75 );
$nuggets_twenty_row    = $find_menu_row( 'nuggets', '20 pc Chicken McNuggets' );
$nuggets_forty_row     = $find_menu_row( 'nuggets', '40 pc Chicken McNuggets' );
$cookie_one_row        = $find_menu_row( 'sweets', 'Chocolate Chip Cookie (1)' );
$chocolate_shake_medium_row = $find_menu_row( 'sweets', 'Chocolate Shake Medium' );
$vanilla_shake_medium_row   = $find_menu_row( 'sweets', 'Vanilla Shake Medium' );
$strawberry_shake_medium_row = $find_menu_row( 'sweets', 'Strawberry Shake Medium' );
$strawberry_banana_smoothie_medium_row = $find_menu_row( 'bev', 'Strawberry Banana Smoothie Medium' );
$mango_pineapple_smoothie_medium_row   = $find_menu_row( 'bev', 'Mango Pineapple Smoothie Medium' );

$featured_items = array(
	array(
		'name'     => 'FIFA World Cup Meal',
		'cal'      => 'Limited-time meal - FIFA 26',
		'price'    => '$8.25-$11.45 est.',
		'emoji'    => '&#127942;',
		'category' => 'meals',
		'url'      => $world_cup_guide_url,
		'media'    => 'Big Mac Meal',
	),
	array(
		'name'     => 'Big Mac',
		'cal'      => $format_calories( $big_mac_row['calories'] ?? '' ) . ' - Burgers',
		'price'    => $big_mac_row['price'] ?? '$5.99',
		'emoji'    => '&#127828;',
		'category' => 'burgers',
	),
	array(
		'name'     => 'Sausage, Egg & Cheese McGriddles',
		'cal'      => $format_calories( $mcgriddles_row['calories'] ?? '' ) . ' - Breakfast',
		'price'    => $mcgriddles_row['price'] ?? '$5.99',
		'emoji'    => '&#129374;',
		'category' => 'breakfast',
	),
	array(
		'name'     => 'McCrispy',
		'cal'      => $format_calories( $mccrispy_row['calories'] ?? '' ) . ' - Chicken',
		'price'    => $mccrispy_row['price'] ?? '$4.99',
		'emoji'    => '&#127831;',
		'category' => 'chickenfish',
	),
	array(
		'name'     => 'OREO McFlurry',
		'cal'      => $format_calories( $oreo_mcflurry_row['calories'] ?? '' ) . ' - Desserts',
		'price'    => $oreo_mcflurry_row['price'] ?? '$5.59',
		'emoji'    => '&#127846;',
		'category' => 'sweets',
	),
	array(
		'name'     => 'World Famous Fries - Large',
		'cal'      => $format_calories( $large_fries_row['calories'] ?? '' ) . ' - Sides',
		'price'    => $large_fries_row['price'] ?? '$4.99',
		'emoji'    => '&#127839;',
		'category' => 'sides',
	),
);

$category_cards = array(
	array( 'id' => 'whats-new', 'class' => 'red', 'emoji' => '&#127381;', 'name' => "What's New", 'count' => $whats_new_total . ' limited-time items', 'url' => $get_category_page_url( 'whats-new' ), 'image' => $get_category_media_url( 'whats-new' ) ),
	array( 'id' => 'meals', 'class' => '', 'emoji' => '&#127859;', 'name' => 'Extra Value Meals', 'count' => $count_menu_rows( $evm_section ) . ' meals', 'url' => $get_category_page_url( 'meals' ), 'image' => $get_category_media_url( 'meals' ) ),
	array( 'id' => 'mcvalue', 'class' => '', 'emoji' => '&#128184;', 'name' => 'McValue', 'count' => $count_menu_rows( $mcvalue_section ) . ' value picks', 'url' => $get_category_page_url( 'mcvalue' ), 'image' => $get_category_media_url( 'mcvalue' ) ),
	array( 'id' => 'breakfast', 'class' => '', 'emoji' => '&#129374;', 'name' => 'Breakfast', 'count' => $count_menu_rows( $breakfast_section ) . ' breakfast items', 'url' => $get_category_page_url( 'breakfast' ), 'image' => $get_category_media_url( 'breakfast' ) ),
	array( 'id' => 'burgers', 'class' => '', 'emoji' => '&#127828;', 'name' => 'Burgers', 'count' => $count_menu_rows( $burgers_section ) . ' burgers', 'url' => $get_category_page_url( 'burgers' ), 'image' => $get_category_media_url( 'burgers' ) ),
	array( 'id' => 'chickenfish', 'class' => '', 'emoji' => '&#127831;', 'name' => 'Chicken & Fish', 'count' => $count_menu_rows( $chicken_section ) . ' sandwiches', 'url' => $get_category_page_url( 'chickenfish' ), 'image' => $get_category_media_url( 'chickenfish' ) ),
	array( 'id' => 'nuggets', 'class' => '', 'emoji' => '&#127831;', 'name' => 'McNuggets & Strips', 'count' => $count_menu_rows( $nuggets_section ) . ' chicken items', 'url' => $get_category_page_url( 'nuggets' ), 'image' => $get_category_media_url( 'nuggets' ) ),
	array( 'id' => 'snackwrap', 'class' => '', 'emoji' => '&#127791;', 'name' => 'Snack Wrap', 'count' => $count_menu_rows( $wrap_section ) . ' wraps', 'url' => $get_category_page_url( 'snackwrap' ), 'image' => $get_category_media_url( 'snackwrap' ) ),
	array( 'id' => 'sides', 'class' => '', 'emoji' => '&#127839;', 'name' => 'Fries & Sides', 'count' => $count_menu_rows( $sides_section ) . ' side items', 'url' => $get_category_page_url( 'sides' ), 'image' => $get_category_media_url( 'sides' ) ),
	array( 'id' => 'happymeal', 'class' => '', 'emoji' => '&#127881;', 'name' => 'Happy Meal', 'count' => $count_menu_rows( $happy_section ) . ' kids meals', 'url' => $get_category_page_url( 'happymeal' ), 'image' => $get_category_media_url( 'happymeal' ) ),
	array( 'id' => 'sweets', 'class' => '', 'emoji' => '&#127846;', 'name' => 'Sweets & Treats', 'count' => $count_menu_rows( $sweets_section ) . ' treats', 'url' => $get_category_page_url( 'sweets' ), 'image' => $get_category_media_url( 'sweets' ) ),
	array( 'id' => 'mccafe', 'class' => '', 'emoji' => '&#9749;', 'name' => 'McCafe Coffees', 'count' => $count_menu_rows( $coffee_section ) . ' coffee drinks', 'url' => $get_category_page_url( 'mccafe' ), 'image' => $get_category_media_url( 'mccafe' ) ),
	array( 'id' => 'beverages', 'class' => '', 'emoji' => '&#127865;', 'name' => 'Beverages', 'count' => $count_menu_rows( $beverages_section ) . ' drinks', 'url' => $get_category_page_url( 'beverages' ), 'image' => $get_category_media_url( 'beverages' ) ),
	array( 'id' => 'sauces', 'class' => '', 'emoji' => '&#129514;', 'name' => 'Sauces & Condiments', 'count' => $count_menu_rows( $sauces_section ) . ' sauce items', 'url' => $get_category_page_url( 'sauces' ), 'image' => $get_category_media_url( 'sauces' ) ),
	array( 'id' => 'deals', 'class' => 'red', 'emoji' => '&#127991;&#65039;', 'name' => 'Deals', 'count' => 'Current McValue offers', 'url' => $get_category_page_url( 'deals' ), 'image' => $get_category_media_url( 'deals' ) ),
);

$whats_new_items = array(
	array( 'name' => "McDonald's Grinch Meal", 'cal' => 'Holiday limited-time meal', 'price' => 'Varies by location', 'status' => 'Holiday LTO', 'emoji' => '&#127876;', 'category' => 'whats-new', 'url' => $page_url( 'mcdonalds-grinch-meal' ) ),
	array( 'name' => 'The HUNTRIX Meal', 'cal' => $format_calories( $huntrix_row['calories'] ?? '' ), 'price' => $huntrix_row['price'] ?? '~$11.99', 'status' => 'New', 'emoji' => '&#127909;', 'category' => 'whats-new' ),
	array( 'name' => 'The Saja Boys Breakfast Meal', 'cal' => $format_calories( $saja_boys_row['calories'] ?? '' ), 'price' => $saja_boys_row['price'] ?? '~$10.49', 'status' => 'Limited Time', 'emoji' => '&#127860;', 'category' => 'whats-new' ),
	array( 'name' => 'Ramyeon McShaker Fries', 'cal' => $format_calories( $ramyeon_fries_row['calories'] ?? '' ), 'price' => $ramyeon_fries_row['price'] ?? '~$4.49', 'status' => 'Limited Time', 'emoji' => '&#127839;', 'category' => 'whats-new' ),
	array( 'name' => 'The BIG ARCH', 'cal' => $format_calories( $big_arch_row['calories'] ?? '' ), 'price' => $big_arch_row['price'] ?? '~$8.99', 'status' => 'Limited Time', 'emoji' => '&#127828;', 'category' => 'whats-new' ),
	array( 'name' => 'The BIG ARCH Meal', 'cal' => $format_calories( $big_arch_meal_row['calories'] ?? '' ), 'price' => $big_arch_meal_row['price'] ?? '~$13.49', 'status' => 'Limited Time', 'emoji' => '&#127828;', 'category' => 'whats-new' ),
);

$world_cup_meal_items = array(
	array(
		'name'   => 'FIFA World Cup Meal - Big Mac',
		'media'  => 'Big Mac Meal',
		'cal'    => '1,170 kcal',
		'price'  => $world_cup_big_mac_range,
		'price_min' => (float) ( $big_mac_meal_row['price_min'] ?? 8.95 ),
		'price_max' => (float) ( $big_mac_meal_row['price_max'] ?? 11.45 ),
		'url'    => $world_cup_guide_url,
		'status' => 'Official LTO',
	),
	array(
		'name'   => 'FIFA World Cup Meal - 10 pc McNuggets',
		'media'  => '10 pc Chicken McNuggets Meal',
		'cal'    => '1,000 kcal',
		'price'  => $world_cup_nuggets_range,
		'price_min' => (float) ( $nuggets_ten_meal_row['price_min'] ?? 8.85 ),
		'price_max' => (float) ( $nuggets_ten_meal_row['price_max'] ?? 11.35 ),
		'url'    => $world_cup_guide_url,
		'status' => 'Official LTO',
	),
	array(
		'name'   => 'FIFA World Cup Meal - Sausage McMuffin with Egg',
		'media'  => 'Sausage McMuffin with Egg Meal',
		'cal'    => '620 kcal',
		'price'  => $world_cup_sausage_mcmuffin_range,
		'price_min' => (float) ( $sausage_mcmuffin_meal_row['price_min'] ?? 8.45 ),
		'price_max' => (float) ( $sausage_mcmuffin_meal_row['price_max'] ?? 10.95 ),
		'url'    => $world_cup_guide_url,
		'status' => 'Breakfast LTO',
	),
	array(
		'name'   => 'FIFA World Cup Meal - Sausage Biscuit with Egg',
		'media'  => 'Sausage Biscuit with Egg Meal',
		'cal'    => '670 kcal',
		'price'  => $world_cup_sausage_biscuit_range,
		'price_min' => (float) ( $sausage_biscuit_meal_row['price_min'] ?? 8.25 ),
		'price_max' => (float) ( $sausage_biscuit_meal_row['price_max'] ?? 10.75 ),
		'url'    => $world_cup_guide_url,
		'status' => 'Breakfast LTO',
	),
);

$meal_shortcut_cards = array(
	array(
		'class' => 'deal-dark',
		'label' => 'Breakfast combos',
		'title' => 'Breakfast Meal Combos',
		'price' => '~$9-$11+',
		'copy'  => 'McMuffin, biscuit, McGriddles, bagel, hotcakes, and burrito meals are easiest to compare inside the Extra Value Meals category.',
		'url'   => $get_category_page_url( 'meals' ),
	),
	array(
		'class' => 'deal-red',
		'label' => 'Lunch & dinner',
		'title' => 'Lunch & Dinner Meals',
		'price' => '~$9-$14+',
		'copy'  => 'Use this path when you want a Big Mac Meal, McNuggets Meal, McCrispy Meal, Filet-O-Fish Meal, or Quarter Pounder Meal.',
		'url'   => $page_url( 'extra-value-meals' ),
	),
	array(
		'class' => 'deal-yellow',
		'label' => 'Add one',
		'title' => 'Buy One, Add One for $1',
		'price' => 'App and location vary',
		'copy'  => 'Compare the breakfast and lunch add-one lists before ordering because the best value changes by daypart and restaurant.',
		'url'   => $get_category_page_url( 'mcvalue' ),
	),
	array(
		'class' => 'deal-dark',
		'label' => 'Cold drinks',
		'title' => 'Shakes & Smoothies',
		'price' => '$3.99-$4.49 med.',
		'copy'  => 'Shakes sit under Sweets & Treats, while smoothies sit under Beverages, so this shortcut connects both parts of the menu.',
		'url'   => $get_category_page_url( 'beverages' ),
	),
);

$menu_cards = array(
	array(
		'badge'       => '&#128293; #1 bestseller',
		'title'       => 'Big Mac',
		'price'       => $big_mac_row['price'] ?? '$5.99',
		'calories'    => $format_calories( $big_mac_row['calories'] ?? '' ),
		'chip'        => 'Burger',
		'detail_one'  => 'Three-bun burger with signature sauce',
		'detail_two'  => $format_calories( $big_mac_row['calories'] ?? '' ),
		'category'    => 'burgers',
	),
	array(
		'badge'       => '&#11088; breakfast favorite',
		'title'       => 'Egg McMuffin',
		'price'       => $egg_mcmuffin_row['price'] ?? '$4.89',
		'calories'    => $format_calories( $egg_mcmuffin_row['calories'] ?? '' ),
		'chip'        => 'Breakfast',
		'detail_one'  => '300-calorie breakfast sandwich staple',
		'detail_two'  => $format_calories( $egg_mcmuffin_row['calories'] ?? '' ),
		'category'    => 'breakfast',
	),
	array(
		'badge'       => '&#9889; value pick',
		'title'       => '6 Piece Chicken McNuggets',
		'price'       => $nuggets_six_row['price'] ?? '$4.39',
		'calories'    => $format_calories( $nuggets_six_row['calories'] ?? '' ),
		'chip'        => 'Popular',
		'detail_one'  => 'McValue-friendly nugget order',
		'detail_two'  => $format_calories( $nuggets_six_row['calories'] ?? '' ),
		'category'    => 'nuggets',
	),
);

$iconic_cards = array(
	array(
		'title' => 'Big Mac',
		'copy'  => 'The Big Mac is the classic burger benchmark for comparing sandwich price, meal price, and value against Quarter Pounder or McDouble options.',
		'url'   => $get_item_page_url( 'burgers', 'Big Mac' ),
	),
	array(
		'title' => 'Chicken McNuggets',
		'copy'  => 'McNuggets are the easiest item to scale from snack size to shareable size, especially when sauces, fries, and app deals matter.',
		'url'   => $get_item_page_url( 'nuggets', '10 pc Chicken McNuggets' ),
	),
	array(
		'title' => 'Egg McMuffin',
		'copy'  => 'The Egg McMuffin is the breakfast anchor most people use to compare McMuffins, biscuits, McGriddles, and breakfast meals.',
		'url'   => $get_item_page_url( 'breakfast', 'Egg McMuffin' ),
	),
);

$shareable_cards = array(
	array(
		'title' => '40 pc Chicken McNuggets',
		'price' => $nuggets_forty_row['price'] ?? '$13.19',
		'cal'   => $format_calories( $nuggets_forty_row['calories'] ?? '' ),
		'copy'  => 'Best starting point when a group wants nuggets first and will add fries, drinks, or sauces separately.',
		'url'   => $get_item_page_url( 'nuggets', '40 pc Chicken McNuggets' ),
	),
	array(
		'title' => '20 pc Chicken McNuggets',
		'price' => $nuggets_twenty_row['price'] ?? '$7.00',
		'cal'   => $format_calories( $nuggets_twenty_row['calories'] ?? '' ),
		'copy'  => 'Good middle-size order for two people or a family add-on when a full 40 pc box is too much.',
		'url'   => $get_item_page_url( 'nuggets', '20 pc Chicken McNuggets' ),
	),
	array(
		'title' => 'Large Fries Pairing',
		'price' => $large_fries_row['price'] ?? '$4.99',
		'cal'   => $format_calories( $large_fries_row['calories'] ?? '' ),
		'copy'  => 'Fries often decide whether a shareable order is cheaper as a bundle or better built from separate items.',
		'url'   => $get_item_page_url( 'sides', 'World Famous Fries Large' ),
	),
	array(
		'title' => 'Cookies & Dessert Add-ons',
		'price' => 'From ' . ( $cookie_one_row['price'] ?? '$1.79' ),
		'cal'   => $format_calories( $cookie_one_row['calories'] ?? '' ),
		'copy'  => 'Use dessert add-ons when the group order needs a sweet finish without turning every order into a full meal.',
		'url'   => $get_category_page_url( 'sweets' ),
	),
);

$shake_smoothie_cards = array(
	array( 'name' => 'Chocolate Shake Medium', 'price' => $chocolate_shake_medium_row['price'] ?? '$3.99', 'cal' => $format_calories( $chocolate_shake_medium_row['calories'] ?? '' ), 'url' => $get_item_page_url( 'sweets', 'Chocolate Shake Medium' ) ),
	array( 'name' => 'Vanilla Shake Medium', 'price' => $vanilla_shake_medium_row['price'] ?? '$3.99', 'cal' => $format_calories( $vanilla_shake_medium_row['calories'] ?? '' ), 'url' => $get_item_page_url( 'sweets', 'Vanilla Shake Medium' ) ),
	array( 'name' => 'Strawberry Shake Medium', 'price' => $strawberry_shake_medium_row['price'] ?? '$3.99', 'cal' => $format_calories( $strawberry_shake_medium_row['calories'] ?? '' ), 'url' => $get_item_page_url( 'sweets', 'Strawberry Shake Medium' ) ),
	array( 'name' => 'Strawberry Banana Smoothie Medium', 'price' => $strawberry_banana_smoothie_medium_row['price'] ?? '$4.49', 'cal' => $format_calories( $strawberry_banana_smoothie_medium_row['calories'] ?? '' ), 'url' => $get_item_page_url( 'beverages', 'Strawberry Banana Smoothie Medium' ) ),
	array( 'name' => 'Mango Pineapple Smoothie Medium', 'price' => $mango_pineapple_smoothie_medium_row['price'] ?? '$4.49', 'cal' => $format_calories( $mango_pineapple_smoothie_medium_row['calories'] ?? '' ), 'url' => $get_item_page_url( 'beverages', 'Mango Pineapple Smoothie Medium' ) ),
);

$menu_section_blueprints = array(
	array(
		'source_id'    => 'evm',
		'id'           => 'meals',
		'title'        => 'Extra Value Meals',
		'heading'      => 'Extra Value Meals Prices',
		'icon'         => '&#127859;',
		'description'  => 'Compare McDonald\'s combo meal prices for breakfast meals, burger meals, chicken meals, McNuggets meals, Filet-O-Fish meals, fries, and drinks.',
	),
	array(
		'source_id'    => 'mcvalue',
		'id'           => 'mcvalue',
		'title'        => 'McValue',
		'heading'      => 'McValue Menu Prices',
		'icon'         => '&#128184;',
		'description'  => 'Find McDonald\'s value menu prices and McValue offers for meal deals, buy-one-add-one items, mini desserts, and lower-cost add-ons.',
	),
	array(
		'source_id'    => 'bfast',
		'id'           => 'breakfast',
		'title'        => 'Breakfast',
		'heading'      => 'Breakfast Menu Prices',
		'icon'         => '&#129374;',
		'description'  => 'Explore McDonald\'s breakfast menu prices for McMuffins, McGriddles, biscuits, hash browns, breakfast meals, and coffee options.',
	),
	array(
		'source_id'    => 'burgers',
		'id'           => 'burgers',
		'title'        => 'Burgers',
		'heading'      => 'Burgers Menu Prices',
		'icon'         => '&#127828;',
		'description'  => 'Check McDonald\'s burger prices for Big Mac, Cheeseburger, Quarter Pounder, Hamburger, McDouble, and other popular burger items.',
	),
	array(
		'source_id'    => 'chicken',
		'id'           => 'chickenfish',
		'title'        => 'Chicken & Fish Sandwiches',
		'heading'      => 'Chicken & Fish Sandwich Prices',
		'icon'         => '&#127831;',
		'description'  => 'Compare McDonald\'s chicken and fish sandwich prices for McCrispy, McChicken, Filet-O-Fish, spicy sandwiches, and related meal options.',
	),
	array(
		'source_id'    => 'nuggets',
		'id'           => 'nuggets',
		'title'        => 'McNuggets & McCrispy Strips',
		'heading'      => 'McNuggets & McCrispy Strips Prices',
		'icon'         => '&#127831;',
		'description'  => 'View McDonald\'s McNuggets prices and McCrispy Strips prices, from smaller snack sizes to larger shareable orders.',
	),
	array(
		'source_id'    => 'wrap',
		'id'           => 'snackwrap',
		'title'        => 'Snack Wrap',
		'heading'      => 'Snack Wrap Prices',
		'icon'         => '&#127791;',
		'description'  => 'Check McDonald\'s Snack Wrap prices for current crispy chicken wrap flavors and related value options.',
	),
	array(
		'source_id'    => 'sides',
		'id'           => 'sides',
		'title'        => 'Fries & Sides',
		'heading'      => 'Fries & Sides Prices',
		'icon'         => '&#127839;',
		'description'  => 'Compare McDonald\'s fries and sides prices, including small fries, medium fries, large fries, hash browns, apple slices, and other sides.',
	),
	array(
		'source_id'    => 'happy',
		'id'           => 'happymeal',
		'title'        => 'Happy Meal',
		'heading'      => 'Happy Meal Prices',
		'icon'         => '&#127881;',
		'description'  => 'See McDonald\'s Happy Meal prices for hamburger, cheeseburger, and McNuggets Happy Meal options.',
	),
	array(
		'source_id'    => 'sweets',
		'id'           => 'sweets',
		'title'        => 'Sweets & Treats',
		'heading'      => 'Desserts Prices',
		'icon'         => '&#127846;',
		'description'  => 'Browse McDonald\'s dessert prices for McFlurry, apple pie, sundaes, cones, shakes, and other sweet treats.',
	),
	array(
		'source_id'    => 'coffee',
		'id'           => 'mccafe',
		'title'        => 'McCafe Coffees',
		'heading'      => 'McCaf&eacute; Coffee Prices',
		'icon'         => '&#9749;',
		'description'  => 'View McDonald\'s McCaf&eacute; prices for hot coffee, iced coffee, frappes, lattes, cappuccinos, and other coffee drinks.',
	),
	array(
		'source_id'    => 'bev',
		'id'           => 'beverages',
		'title'        => 'Beverages',
		'heading'      => 'Drinks Menu Prices',
		'icon'         => '&#127865;',
		'description'  => 'Find McDonald\'s drink prices for Coke, Sprite, Fanta, smoothies, frozen drinks, milk, juice, and other beverages.',
	),
	array(
		'source_id'    => 'sauce',
		'id'           => 'sauces',
		'title'        => 'Sauces & Condiments',
		'heading'      => 'Sauces & Condiments Prices',
		'icon'         => '&#129514;',
		'description'  => 'Review McDonald\'s sauce and condiment prices for dipping sauces, included packets, and low-cost paid extras.',
	),
);

$menu_sections = array();

foreach ( $menu_section_blueprints as $section_blueprint ) {
	$source_section  = $get_menu_section( $section_blueprint['source_id'] );
	$menu_sections[] = array(
		'id'          => $section_blueprint['id'],
		'title'       => $section_blueprint['title'],
		'heading'     => $section_blueprint['heading'],
		'icon'        => $section_blueprint['icon'],
		'description' => $section_blueprint['description'],
		'rows'        => $flatten_menu_rows( $source_section, $section_blueprint['source_id'] ),
	);
}

$menu_item_total = 0;

foreach ( $menu_sections as $menu_section ) {
	$menu_item_total += count( $menu_section['rows'] );
}

$deal_cards = array(
	array(
		'class' => 'deal-red',
		'label' => '$5 Meal Deal',
		'title' => 'McChicken Meal Deal',
		'price' => '$5.00',
		'copy'  => 'McChicken, 4 pc McNuggets, small fries, and a small drink from the current McValue lineup.',
		'url'   => $get_item_page_url( 'deals', 'McChicken Meal Deal' ),
	),
	array(
		'class' => 'deal-dark',
		'label' => '$5 Meal Deal',
		'title' => 'McDouble Meal Deal',
		'price' => '$5.00',
		'copy'  => 'McDouble, 4 pc McNuggets, small fries, and a small drink from the same current McValue offer set.',
		'url'   => $get_item_page_url( 'deals', 'McDouble Meal Deal' ),
	),
	array(
		'class' => 'deal-dark',
		'label' => '$6 Meal Deal',
		'title' => 'Daily Double Meal Deal',
		'price' => '~$6.00',
		'copy'  => 'The Daily Double Meal Deal is listed as the higher-entry limited-time McValue meal deal option.',
		'url'   => $get_item_page_url( 'deals', 'Daily Double Meal Deal' ),
	),
	array(
		'class' => 'deal-yellow',
		'label' => 'Breakfast BOGO',
		'title' => 'Buy 1, Add 1 for $1',
		'price' => '$2.99 base items',
		'copy'  => 'The breakfast offer applies to Sausage Biscuit, Sausage McMuffin, Sausage Burrito, and Hash Browns.',
		'url'   => $get_item_page_url( 'deals', 'Breakfast Buy 1 Add 1 for $1' ),
	),
	array(
		'class' => 'deal-dark',
		'label' => 'Lunch BOGO',
		'title' => 'Buy 1, Add 1 for $1',
		'price' => '$2.89-$4.39 items',
		'copy'  => 'Current lunch and dinner McValue add-on picks include Double Cheeseburger, McChicken, 6 pc McNuggets, and Small World Famous Fries.',
		'url'   => $get_item_page_url( 'deals', 'Lunch Buy 1 Add 1 for $1' ),
	),
	array(
		'class' => 'deal-dark',
		'label' => 'Mini dessert',
		'title' => 'McValue Mini McFlurry Picks',
		'price' => '$3.19',
		'copy'  => 'Mini M&M\'s and OREO McFlurry cups are listed in the current McValue dessert section as low-entry sweet options.',
		'url'   => $get_item_page_url( 'deals', 'McValue Mini McFlurry Picks' ),
	),
);

$guide_cards = array(
	array( 'href' => $page_url( 'menu' ), 'title' => 'Full Menu USA', 'text' => 'The main McDonald\'s USA menu pillar for comparing categories, prices, calories, deals, and the best next guide to open.' ),
	array( 'href' => $page_url( 'breakfast-menu' ), 'title' => 'Breakfast Menu Prices USA', 'text' => 'A deeper breakfast guide covering McMuffins, biscuits, McGriddles, hotcakes, Hash Browns, and breakfast value comparisons.' ),
	array( 'href' => $page_url( 'burgers-menu' ), 'title' => 'Burgers Menu Prices USA', 'text' => 'Compare Big Mac, Quarter Pounder builds, McDouble, cheeseburgers, hamburgers, and burger price ladders in one guide.' ),
	array( 'href' => $page_url( 'chicken-fish-menu' ), 'title' => 'Chicken & Fish Menu Prices USA', 'text' => 'McCrispy, spicy builds, McChicken, Filet-O-Fish, and chicken-versus-fish value comparisons for USA ordering.' ),
	array( 'href' => $page_url( 'nuggets-and-strips' ), 'title' => 'McNuggets & Strips Prices USA', 'text' => 'Piece counts, share boxes, McCrispy Strips, sauces, and family-order value context from the current USA menu.' ),
	array( 'href' => $page_url( 'fries-sides' ), 'title' => 'Fries & Sides Prices USA', 'text' => 'See how fries sizes, Apple Slices, and side-upgrade choices change the final McDonald\'s USA total.' ),
	array( 'href' => $page_url( 'happy-meal-menu' ), 'title' => 'Happy Meal Prices USA', 'text' => 'A family-order guide to Hamburger and McNuggets Happy Meals, kids-meal pricing, and related add-on choices.' ),
	array( 'href' => $page_url( 'sweets-treats' ), 'title' => 'Desserts Menu Prices USA', 'text' => 'McFlurries, mini treats, cones, sundaes, shakes, pies, and dessert value comparisons in one sweets guide.' ),
	array( 'href' => $get_category_page_url( 'mccafe' ), 'title' => 'McCafe Menu Prices USA', 'text' => 'Hot coffee, iced coffee, espresso drinks, frappes, and McCafe size-led pricing comparisons for the USA menu.' ),
	array( 'href' => $page_url( 'beverage-menu' ), 'title' => 'Drinks Menu Prices USA', 'text' => 'Soft drinks, tea, lemonade, juice, smoothies, frozen drinks, and beverage pricing across the USA menu.' ),
	array( 'href' => $page_url( 'sauces-condiments' ), 'title' => 'Sauces & Condiments Prices USA', 'text' => 'Included dips, paid sauces, packet condiments, and the add-on choices that change nuggets, fries, and shareable orders.' ),
	array( 'href' => $page_url( 'mcdonalds-deals-mcvalue-guide' ), 'title' => 'Deals & McValue Guide USA', 'text' => 'The main value pillar for $5-style meals, McValue ordering, app-led savings, and low-entry menu strategies.' ),
	array( 'href' => $page_url( 'mcdonalds-fifa-world-cup-meal' ), 'title' => 'FIFA World Cup Meal Guide', 'text' => 'Limited-time meal options, calories, official availability notes, and category links for the 2026 FIFA World Cup promotion.' ),
	array( 'href' => $page_url( 'mcdonalds-secret-menu' ), 'title' => 'Secret Menu & Custom Orders', 'text' => 'A safe custom-order guide explaining unofficial combinations, app limitations, and the official items behind each idea.' ),
	array( 'href' => $page_url( 'mcdonalds-nutrition-calories-allergens' ), 'title' => 'Nutrition, Calories & Allergens Guide', 'text' => 'A broader McDonald\'s USA guide for calories, ingredients, allergen checks, and lighter-versus-heavier menu decisions.' ),
	array( 'href' => $page_url( 'mcdonalds-prices-by-state' ), 'title' => 'McDonald\'s Prices by State', 'text' => 'The regional pricing pillar for comparing state and city variation, local value differences, and market-level menu shifts.' ),
);

$faq_items = array(
	array(
		'question' => 'Are McDonald\'s prices the same at every location?',
		'answer'   => 'No. McDonald\'s prices can vary by city, franchise, taxes, app offers, delivery markups, and restaurant format. A Big Mac, breakfast meal, or McValue deal can cost more in airports, downtown stores, or delivery apps than at a standard local restaurant.',
	),
	array(
		'question' => 'Where can I find McDonald\'s menu prices in the USA?',
		'answer'   => 'This homepage works as a McDonald\'s menu with prices for the USA. Use the category sections for breakfast, burgers, McCafe, Happy Meals, fries, desserts, drinks, sauces, combo meals, and deals, then open the matching guide or item page when you want a closer comparison.',
	),
	array(
		'question' => 'Why are McDonald\'s delivery prices sometimes higher?',
		'answer'   => 'McDonald\'s delivery prices are often higher because the final total can include delivery menu pricing, service fees, small-order fees, taxes, and platform markups. If you are comparing value, check the in-store price, pickup price, and delivery checkout before you order.',
	),
	array(
		'question' => 'What time does McDonald\'s stop serving breakfast?',
		'answer'   => 'Most McDonald\'s locations stop breakfast around 10:30 AM on weekdays and around 11:00 AM on weekends, but that is not universal. Local breakfast cut-off times, item availability, and 24-hour store rules can differ, so the McDonald\'s app or your restaurant is the best final check.',
	),
	array(
		'question' => 'Does McDonald\'s have different prices for breakfast, lunch, and dinner?',
		'answer'   => 'Yes. Breakfast, lunch, and dinner menus use different items, meal bundles, and availability windows, so prices naturally differ across dayparts. Egg McMuffins, McGriddles, burgers, McNuggets, and combo meals are priced within their own menu sections rather than one flat all-day price list.',
	),
	array(
		'question' => 'How can I get McDonald\'s deals or coupons?',
		'answer'   => 'The McDonald\'s app is usually the best source for current deals, coupons, and rewards because many offers are app-exclusive or account-specific. A $5 meal deal, buy-one-add-one offer, free fries promotion, or points reward may be available at one restaurant but not another.',
	),
	array(
		'question' => 'What is McValue at McDonald\'s?',
		'answer'   => 'McValue is McDonald\'s value platform for lower-cost ordering, combining meal deals, entry-price items, add-on offers, and app savings. It is useful when you want to compare whether a McChicken, McDouble, small fries, nuggets, breakfast item, or mini dessert gives the best value for the money.',
	),
	array(
		'question' => 'How often are McDonald\'s menu prices updated on this site?',
		'answer'   => 'This site reviews McDonald\'s menu prices when menu data, app offers, seasonal products, limited-time meals, or category changes are added. Because restaurant prices can change faster than a public guide, use these prices for planning and then confirm the live total before checkout.',
	),
	array(
		'question' => 'Where can I find McDonald\'s calories, ingredients, and allergen information?',
		'answer'   => 'You can use this site for a quick calorie reference, but the official McDonald\'s nutrition tools remain the best place to verify calories, ingredients, allergens, and customization effects. That matters most for dairy, egg, wheat, soy, fish, peanut, tree nut, or other allergy-related decisions.',
	),
	array(
		'question' => 'Why do McDonald\'s prices vary by location?',
		'answer'   => 'McDonald\'s prices vary by location because franchise operators, labor costs, rent, taxes, local competition, delivery fees, and store format all affect pricing. Prices at airport, mall, highway, and urban restaurants can look different from suburban drive-thru restaurants.',
	),
	array(
		'question' => 'Why is my McDonald\'s checkout total different from the menu price?',
		'answer'   => 'The menu price is only the starting point. Your final McDonald\'s total can change when taxes, size upgrades, sauces, meal swaps, extra toppings, delivery fees, service charges, and app discounts are applied, which is why the checkout screen is the final number to trust.',
	),
	array(
		'question' => 'Are McDonald\'s app prices different from restaurant prices?',
		'answer'   => 'Sometimes, yes. McDonald\'s app prices can differ from in-store, drive-thru, pickup, or delivery pricing because app-only promotions, rewards offers, bundled deals, and local restaurant settings may change the final amount you see before checkout.',
	),
	array(
		'question' => 'Does McDonald\'s serve breakfast all day?',
		'answer'   => 'No, most McDonald\'s restaurants in the USA do not serve a full all-day breakfast menu. Breakfast usually ends in the late morning, and once the menu switches, items like Egg McMuffins, hotcakes, biscuits, and McGriddles may no longer be available until the next day.',
	),
	array(
		'question' => 'How much is a Big Mac meal at McDonald\'s?',
		'answer'   => 'A Big Mac meal price varies by location, drink size, fries size, taxes, and local restaurant pricing. Use the burger and combo meal sections on this page to compare the current McDonald\'s menu prices around a Big Mac, standalone burger, and full meal option before you order.',
	),
	array(
		'question' => 'Are McDonald\'s combo meals cheaper than ordering items separately?',
		'answer'   => 'Often, yes. A McDonald\'s combo meal can offer better value than buying the sandwich, fries, and drink one by one, especially when a local meal bundle or app deal is active. The best choice depends on portion size, add-ons, and whether a current McValue or coupon offer beats the standard combo price.',
	),
);

$timeline_items = array(
	array( 'year' => '1955', 'title' => 'McDonald\'s National Expansion Begins', 'text' => 'Ray Kroc opened the first McDonald\'s restaurant for the modern system in Des Plaines, Illinois, launching the chain\'s coast-to-coast expansion.' ),
	array( 'year' => '1979', 'title' => 'Happy Meal Debuts', 'text' => 'The Happy Meal became a defining kids\' format and remains one of the most recognizable quick-service meal bundles in the US.' ),
	array( 'year' => '1983', 'title' => 'Chicken McNuggets Go Mainstream', 'text' => 'McNuggets became a lasting part of the US menu and eventually grew into one of the brand\'s biggest group-order and snack categories.' ),
	array( 'year' => '2000s', 'title' => 'McCafe Growth Accelerates', 'text' => 'McCafe helped McDonald\'s expand from burgers and fries into coffeehouse-style drinks, iced coffees, lattes, and frappes.' ),
	array( 'year' => '2026', 'title' => 'McValue, BIG ARCH and Limited-Time Meals', 'text' => 'The current USA menu file highlights McValue savings, BIG ARCH, Snack Wraps, seasonal promo meals, and limited-time desserts as major traffic drivers.' ),
);

$footer_disclaimer = '&#9888;&#65039; <strong>Disclaimer:</strong> McDonald&#8217;s prices may vary by location, restaurant, delivery app, and current promotions. This website is an independent menu price guide and is not affiliated with McDonald&#8217;s.';

$get_item_image_alt = static function ( array $row ) {
	$name = trim( wp_strip_all_tags( (string) ( $row['name'] ?? '' ) ) );

	if ( '' === $name ) {
		return "McDonald's menu item";
	}

	return "McDonald's " . $name . ' menu item';
};

$get_named_item_image_alt = static function ( $item_name ) {
	$name = trim( wp_strip_all_tags( (string) $item_name ) );

	if ( '' === $name ) {
		return "McDonald's menu item";
	}

	return "McDonald's " . $name . ' menu item';
};

$get_category_image_alt = static function ( array $card ) {
	$name = trim( wp_strip_all_tags( (string) ( $card['name'] ?? '' ) ) );

	if ( '' === $name ) {
		return "McDonald's menu category";
	}

	return "McDonald's " . $name . ' menu category';
};

$render_rows = static function ( array $rows, $category_id ) use ( $get_item_page_url, $get_home_item_media_url, $get_item_image_alt ) {
	foreach ( $rows as $row ) :
		$item_url   = $get_item_page_url( $category_id, $row['name'] ?? '' );
		$item_media = $get_home_item_media_url( $row['name'] ?? '', '48' );
		?>
		<tr>
			<td>
				<div class="td-name">
					<a class="mcprices-inline-media" href="<?php echo esc_url( $item_url ); ?>">
						<?php if ( $item_media ) : ?>
							<img class="mcprices-inline-media__thumb" src="<?php echo esc_url( $item_media ); ?>" alt="<?php echo esc_attr( $get_item_image_alt( $row ) ); ?>" width="48" height="48" loading="lazy" decoding="async">
						<?php endif; ?>
						<span class="mcprices-inline-media__label"><?php echo esc_html( $row['name'] ); ?></span>
					</a>
				</div>
			</td>
			<td class="td-price"><?php echo esc_html( $row['price'] ); ?></td>
			<td class="td-cal"><?php echo esc_html( $row['calories'] ); ?></td>
			<td><?php echo esc_html( $row['status'] ); ?></td>
		</tr>
		<?php
	endforeach;
};

ob_start();
?>
<!-- wp:group {"className":"mcprices-page mcprices-managed-homepage","layout":{"type":"default"}} -->
<div class="wp-block-group mcprices-page mcprices-managed-homepage" data-mcprices-pattern-version="3.3.8">
<!-- wp:html -->
<section class="hero">
	<div class="hero-bg"></div>
	<div class="hero-pattern"></div>
	<div class="hero-content">
		<div class="hero-left animate-fadeup">
			<div class="hero-eyebrow">Updated April 2026</div>
			<h1 class="hero-title">
				McDonald's Menu Prices<br>
				<span class="highlight">USA</span>
			</h1>
			<p class="hero-sub">Find the latest McDonald&#8217;s prices in the USA, including McDonald&#8217;s menu prices for burgers, breakfast, McCaf&eacute; drinks, Happy Meals, fries, desserts, combo meals, and value menu items. Prices may vary by location, but this McDonald&#8217;s price list gives you a helpful overview across the United States.</p>
			<p class="hero-sub">Thousands of readers trust this page to compare accurate McDonald&rsquo;s menu prices, calories, and value deals before they order.</p>
			<div class="mcprices-hero-search-placeholder" data-mcprices-hero-search-placeholder="1"></div>
			<div class="hero-btns">
				<a href="#full-menu" class="btn-primary">&#127828; View Full Menu</a>
				<a href="#deals" class="btn-outline">&#127991;&#65039; Value Deals</a>
				<a href="<?php echo esc_url( $page_url( 'budget-finder' ) ); ?>" class="btn-outline" data-home-tool-trigger="budget-finder" data-home-tool-scroll="1">&#128736;&#65039; Interactive Tools</a>
			</div>
			<div class="mcprices-hero-tool-strip" aria-label="Homepage interactive tools">
				<?php foreach ( $interactive_tools as $index => $tool ) : ?>
					<a
						class="mcprices-hero-tool-strip__link<?php echo 0 === $index ? ' is-active' : ''; ?>"
						href="<?php echo esc_url( $tool['url'] ); ?>"
						data-home-tool-trigger="<?php echo esc_attr( $tool['slug'] ); ?>"
						data-home-tool-tab="<?php echo esc_attr( $tool['slug'] ); ?>"
						data-home-tool-scroll="1"
					>
						<span class="mcprices-hero-tool-strip__icon" aria-hidden="true"><?php echo $tool['icon']; ?></span>
						<span><?php echo esc_html( $tool['short_title'] ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
		<div class="hero-right animate-fadeup delay-2">
			<div class="hero-badge-float">
				&#128293; Most<br><span>Popular</span>
			</div>
			<div class="hero-card-main">
				<div class="hero-card-title">&#11088; Top Menu Items</div>
				<?php foreach ( $featured_items as $index => $item ) : ?>
					<?php
					$featured_item_media_name = $item['media'] ?? $item['name'];
					$featured_item_media      = $get_home_item_media_url( $featured_item_media_name, '120' );
					$featured_item_url        = $item['url'] ?? $get_item_page_url( $item['category'], $item['name'] );
					?>
					<div class="featured-item">
						<div class="item-emoji">
							<?php if ( $featured_item_media ) : ?>
								<img class="mcprices-media-icon mcprices-media-icon--item" src="<?php echo esc_url( $featured_item_media ); ?>" alt="<?php echo esc_attr( $get_named_item_image_alt( $item['name'] ) ); ?>" width="120" height="120" loading="<?php echo 0 === $index ? 'eager' : 'lazy'; ?>" decoding="async"<?php echo 0 === $index ? ' fetchpriority="high"' : ''; ?>>
							<?php else : ?>
								<?php echo $item['emoji']; ?>
							<?php endif; ?>
						</div>
						<div class="item-info">
							<div class="item-name"><a href="<?php echo esc_url( $featured_item_url ); ?>"><?php echo esc_html( $item['name'] ); ?></a></div>
							<div class="item-cal"><?php echo esc_html( $item['cal'] ); ?></div>
						</div>
						<div class="item-price"><?php echo esc_html( $item['price'] ); ?></div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>

<div class="breadcrumbs">
	<div class="container">
		<div class="breadcrumb-inner">
			<a href="#">Home</a>
			<span class="breadcrumb-sep">&#8250;</span>
			<span class="breadcrumb-current">McDonald's Menu Prices USA <?php echo esc_html( $current_year ); ?></span>
		</div>
	</div>
</div>

<section class="full-menu" id="full-menu">
	<div class="container">
		<div class="section-header">
			<div class="section-label">Every category, every price</div>
			<h2 class="section-title">McDonald's Menu Prices by Category</h2>
			<p class="section-sub">Use this McDonald&#8217;s menu prices USA guide to compare the McDonald&#8217;s menu with prices across <?php echo esc_html( (string) $menu_item_total ); ?> current items in 15 categories. Browse burgers, breakfast, McCaf&eacute; coffees, McFlurry desserts, Happy Meals, fries, sauces, combo meals, and McValue deals with prices and calories updated for <?php echo esc_html( $current_year ); ?>.</p>
		</div>
		<div class="size-key">
			<strong>Format guide:</strong> Small-to-large price ranges are grouped where multiple sizes exist. All prices are shown in US dollars and can vary by location.
		</div>
		<div class="menu-tabs">
			<button class="menu-tab" type="button" data-menu-filter="all" aria-pressed="false">&#9776; All</button>
			<button class="menu-tab" type="button" data-menu-filter="whats-new" aria-pressed="false">&#127381; What's New</button>
			<a class="menu-tab menu-tab--anchor" href="#fifa-world-cup-meal" data-menu-filter="fifa-world-cup-meal" aria-pressed="false">&#127942; FIFA World Cup Meal</a>
			<?php foreach ( $menu_sections as $section ) : ?>
				<button class="menu-tab<?php echo 'breakfast' === $section['id'] ? ' active' : ''; ?>" type="button" data-menu-filter="<?php echo esc_attr( $section['id'] ); ?>" aria-pressed="<?php echo 'breakfast' === $section['id'] ? 'true' : 'false'; ?>"><?php echo $section['icon']; ?> <?php echo esc_html( wp_strip_all_tags( $section['title'] ) ); ?></button>
			<?php endforeach; ?>
		</div>
		<?php foreach ( $menu_sections as $section ) : ?>
			<div class="menu-section" id="<?php echo esc_attr( $section['id'] ); ?>" data-menu-category="<?php echo esc_attr( $section['id'] ); ?>">
				<?php $section_media = $get_category_media_url( $section['id'] ); ?>
				<div class="menu-section-head">
					<span class="menu-section-icon">
						<?php if ( $section_media ) : ?>
							<img class="mcprices-media-icon mcprices-media-icon--section" src="<?php echo esc_url( $section_media ); ?>" alt="<?php echo esc_attr( wp_strip_all_tags( $section['title'] ) ); ?>" width="160" height="160" loading="lazy" decoding="async">
						<?php else : ?>
							<?php echo $section['icon']; ?>
						<?php endif; ?>
					</span>
					<h2 class="menu-section-title"><?php echo wp_kses_post( $section['heading'] ); ?></h2>
					<span class="menu-section-count"><?php echo esc_html( (string) count( $section['rows'] ) ); ?> items</span>
				</div>
				<p class="menu-section-desc"><?php echo esc_html( html_entity_decode( $section['description'], ENT_QUOTES, 'UTF-8' ) ); ?></p>
				<div class="menu-table-wrap">
					<table class="menu-table">
						<thead>
							<tr>
								<th>Item</th>
								<th>Price</th>
								<th>Calories</th>
								<th>Status</th>
							</tr>
						</thead>
						<tbody>
							<?php $render_rows( $section['rows'], $section['id'] ); ?>
						</tbody>
					</table>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</section>

<section class="categories">
	<div class="container">
			<div class="section-header">
				<div class="section-label">Browse by Category</div>
			<h2 class="section-title">Browse McDonald's Menu Prices by Category</h2>
			<p class="section-sub">Jump straight to the US menu section you need &mdash; pricing, calories, and value-focused picks included.</p>
			<p class="section-sub section-sub--links">Popular category links: <a href="<?php echo esc_url( $get_category_page_url( 'breakfast' ) ); ?>">McDonald&#8217;s breakfast menu prices</a>, <a href="<?php echo esc_url( $get_category_page_url( 'burgers' ) ); ?>">McDonald&#8217;s burger menu prices</a>, <a href="<?php echo esc_url( $get_category_page_url( 'mccafe' ) ); ?>">McDonald&#8217;s McCaf&eacute; prices</a>, <a href="<?php echo esc_url( $get_category_page_url( 'happymeal' ) ); ?>">McDonald&#8217;s Happy Meal prices</a>, <a href="<?php echo esc_url( $get_category_page_url( 'beverages' ) ); ?>">McDonald&#8217;s drinks menu prices</a>, <a href="<?php echo esc_url( $get_category_page_url( 'sides' ) ); ?>">McDonald&#8217;s fries prices</a>, <a href="<?php echo esc_url( $get_category_page_url( 'sweets' ) ); ?>">McDonald&#8217;s desserts prices</a>, <a href="<?php echo esc_url( $get_category_page_url( 'deals' ) ); ?>">McDonald&#8217;s deals and offers</a>, and <a href="<?php echo esc_url( $page_url( 'menu' ) ); ?>">the full menu hub</a>.</p>
		</div>
		<div class="cat-grid">
			<?php foreach ( $category_cards as $card ) : ?>
				<a href="<?php echo esc_url( $card['url'] ); ?>" class="cat-card<?php echo $card['class'] ? ' ' . esc_attr( $card['class'] ) : ''; ?>" data-category-id="<?php echo esc_attr( $card['id'] ); ?>">
					<span class="cat-emoji">
						<?php if ( ! empty( $card['image'] ) ) : ?>
							<img class="mcprices-media-icon mcprices-media-icon--category" src="<?php echo esc_url( $card['image'] ); ?>" alt="<?php echo esc_attr( $get_category_image_alt( $card ) ); ?>" width="160" height="160" loading="lazy" decoding="async">
						<?php else : ?>
							<?php echo $card['emoji']; ?>
						<?php endif; ?>
					</span>
					<div class="cat-name"><?php echo $card['name']; ?></div>
					<div class="cat-count"><?php echo esc_html( $card['count'] ); ?></div>
					<div class="cat-arrow">&rarr;</div>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="whats-new" id="whats-new" data-menu-category="whats-new">
	<div class="container">
		<div class="section-header">
			<div class="section-label">Limited-Time Menu</div>
			<h2 class="section-title">What's New at McDonald's USA <?php echo esc_html( $current_year ); ?></h2>
			<p class="section-sub">Current limited-time and featured items showing up across the latest USA menu update.</p>
		</div>
		<div class="mcprices-whats-new-intro">
			<p class="mcprices-whats-new-intro-text">The current US menu update highlights <strong class="mcprices-whats-new-date">The BIG ARCH</strong>, a limited-time burger stacked with two quarter-pound beef patties and special sauce, <strong>The HUNTRIX Meal</strong>, which bundles 10-piece McNuggets with fries and a drink, <strong>Ramyeon McShaker Fries</strong>, and the holiday <a href="<?php echo esc_url( $page_url( 'mcdonalds-grinch-meal' ) ); ?>">McDonald&#8217;s Grinch Meal</a> guide for seasonal McShaker Fries, socks, and bundle context.</p>
		</div>
		<div class="new-items-grid">
			<?php foreach ( $whats_new_items as $item ) : ?>
				<?php $status_class = false !== stripos( $item['status'], 'new' ) ? 'avail-new' : 'avail-limited'; ?>
				<?php $item_url = isset( $item['url'] ) ? (string) $item['url'] : $get_item_page_url( $item['category'], $item['name'] ); ?>
				<?php $new_item_media = $get_home_item_media_url( $item['name'], '120' ); ?>
				<a href="<?php echo esc_url( $item_url ); ?>" class="new-item-card">
					<div class="new-item-top">
						<div class="new-item-emoji">
							<?php if ( $new_item_media ) : ?>
								<img class="mcprices-media-icon mcprices-media-icon--new" src="<?php echo esc_url( $new_item_media ); ?>" alt="<?php echo esc_attr( $get_named_item_image_alt( $item['name'] ) ); ?>" width="120" height="120" loading="lazy" decoding="async">
							<?php else : ?>
								<?php echo $item['emoji']; ?>
							<?php endif; ?>
						</div>
						<span class="new-item-avail avail-badge <?php echo esc_attr( $status_class ); ?>"><?php echo esc_html( $item['status'] ); ?></span>
					</div>
					<div class="new-item-body">
						<div class="new-item-name"><?php echo esc_html( $item['name'] ); ?></div>
						<div class="new-item-cal"><?php echo esc_html( $item['cal'] ); ?></div>
						<div class="new-item-price"><?php echo esc_html( $item['price'] ); ?></div>
					</div>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="whats-new" id="fifa-world-cup-meal">
	<div class="container">
		<div class="section-header">
			<div class="section-label">FIFA World Cup Meal</div>
			<h2 class="section-title">McDonald&#8217;s FIFA World Cup Meal <?php echo esc_html( $current_year ); ?></h2>
			<p class="section-sub">The FIFA World Cup Meal is a limited-time McDonald&#8217;s promotion with Big Mac, 10 pc McNuggets, Sausage McMuffin with Egg, and Sausage Biscuit with Egg meal options at participating U.S. restaurants. The estimated ranges below use the tracked standard meal prices as a planning baseline; confirm the final local total in the app.</p>
			<p class="section-sub section-sub--links">Planning match day too? Follow <a href="https://www.livesoccer24.com/" target="_blank" rel="noopener noreferrer">live FIFA World Cup scores and fixtures on LiveSoccer24</a> while you compare the meal options below.</p>
		</div>
		<div class="new-items-grid">
			<?php foreach ( $world_cup_meal_items as $item ) : ?>
				<?php $world_cup_media = $get_home_item_media_url( $item['media'], '120' ); ?>
				<a href="<?php echo esc_url( $item['url'] ); ?>" class="new-item-card">
					<div class="new-item-top">
						<div class="new-item-emoji">
							<?php if ( $world_cup_media ) : ?>
								<img class="mcprices-media-icon mcprices-media-icon--new" src="<?php echo esc_url( $world_cup_media ); ?>" alt="<?php echo esc_attr( $get_named_item_image_alt( $item['name'] ) ); ?>" width="120" height="120" loading="lazy" decoding="async">
							<?php else : ?>
								&#9917;
							<?php endif; ?>
						</div>
						<span class="new-item-avail avail-badge avail-limited"><?php echo esc_html( $item['status'] ); ?></span>
					</div>
					<div class="new-item-body">
						<div class="new-item-name"><?php echo esc_html( $item['name'] ); ?></div>
						<div class="new-item-cal"><?php echo esc_html( $item['cal'] ); ?></div>
						<div class="new-item-price"><?php echo esc_html( $item['price'] ); ?></div>
					</div>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="featured-menu" id="meal-shortcuts">
	<div class="container">
		<div class="section-header center">
			<div class="section-label">Meal shortcuts</div>
			<h2 class="section-title">Breakfast, Lunch, Dinner and Value Meal Shortcuts</h2>
			<p class="section-sub">Use these shortcuts when you already know the type of order you want. They connect the homepage to the right category or guide page without forcing you to scroll through every menu table.</p>
		</div>
		<div class="deals-grid">
			<?php foreach ( $meal_shortcut_cards as $card ) : ?>
				<a href="<?php echo esc_url( $card['url'] ); ?>" class="deal-card <?php echo esc_attr( $card['class'] ); ?>">
					<div class="deal-badge-top"><?php echo esc_html( $card['label'] ); ?></div>
					<div class="deal-title"><?php echo esc_html( $card['title'] ); ?></div>
					<div class="deal-price"><?php echo esc_html( $card['price'] ); ?></div>
					<div class="deal-sub"><?php echo esc_html( $card['copy'] ); ?></div>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="interactive-tools-home" id="interactive-tools">
	<div class="container">
		<div class="section-header center">
			<div class="section-label">Quick Tools</div>
			<h2 class="section-title">Interactive planning tools for price, calories, and value</h2>
			<p class="section-sub">Open a tool only when you need it, use it on the homepage, then close it and keep browsing the live menu.</p>
		</div>
		<div class="mcprices-tool-hub-grid">
			<?php foreach ( $interactive_tools as $index => $tool ) : ?>
				<div class="mcprices-tool-hub-card">
					<div class="mcprices-tool-hub-card__icon" aria-hidden="true"><?php echo $tool['icon']; ?></div>
					<h3 class="mcprices-tool-hub-card__title"><?php echo esc_html( $tool['title'] ); ?></h3>
					<p class="mcprices-tool-hub-card__desc"><?php echo wp_kses_post( $tool['desc'] ); ?></p>
					<a
						class="btn-card mcprices-tool-hub-card__button"
						href="<?php echo esc_url( $tool['url'] ); ?>"
						data-home-tool-trigger="<?php echo esc_attr( $tool['slug'] ); ?>"
						data-home-tool-scroll="1"
					><?php echo esc_html( $tool['cta'] ); ?></a>
				</div>
			<?php endforeach; ?>
		</div>
		<div class="mcprices-home-tool-stage is-collapsed" id="interactive-tools-panel" data-home-tool-stage data-home-tool-default="budget-finder" data-home-tool-open="0">
			<div class="mcprices-home-tool-stage__header">
				<div class="mcprices-home-tool-stage__tabs" role="tablist" aria-label="Choose an interactive tool">
					<?php foreach ( $interactive_tools as $index => $tool ) : ?>
						<button
							class="mcprices-home-tool-stage__tab"
							type="button"
							data-home-tool-trigger="<?php echo esc_attr( $tool['slug'] ); ?>"
							data-home-tool-tab="<?php echo esc_attr( $tool['slug'] ); ?>"
							aria-pressed="false"
						>
							<span class="mcprices-home-tool-stage__tab-icon" aria-hidden="true"><?php echo $tool['icon']; ?></span>
							<span><?php echo esc_html( $tool['title'] ); ?></span>
						</button>
					<?php endforeach; ?>
				</div>
				<button class="mcprices-home-tool-stage__close" type="button" data-home-tool-close aria-label="Close interactive tool">
					<span aria-hidden="true">&times;</span>
					<span>Close tool</span>
				</button>
			</div>
			<div class="mcprices-home-tool-stage__panels">
				<?php foreach ( $interactive_tools as $tool ) : ?>
					<div
						class="mcprices-home-tool-panel"
						data-home-tool-panel="<?php echo esc_attr( $tool['slug'] ); ?>"
						hidden
					>
						<div class="mcprices-tool-placeholder" data-mcprices-tool-placeholder="<?php echo esc_attr( $tool['slug'] ); ?>"></div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>

<section class="featured-menu">
	<div class="container">
		<div class="section-header center">
			<div class="section-label">Featured Favorites</div>
			<h2 class="section-title">Popular McDonald's Menu Picks</h2>
			<p class="section-sub">High-interest menu favorites that people compare most often: burgers, breakfast, chicken, fries, and dessert picks from the current USA file.</p>
		</div>
		<div class="menu-cards-grid featured-grid">
			<?php foreach ( $menu_cards as $card ) : ?>
				<?php $card_media = $get_home_item_media_url( $card['title'], '240' ); ?>
				<div class="menu-card">
					<div class="card-badge-top"><?php echo wp_kses_post( $card['badge'] ); ?></div>
					<div class="card-img">
						<?php if ( $card_media ) : ?>
							<img class="mcprices-card-media" src="<?php echo esc_url( $card_media ); ?>" alt="<?php echo esc_attr( $get_named_item_image_alt( $card['title'] ) ); ?>" width="240" height="240" loading="lazy" decoding="async">
						<?php endif; ?>
					</div>
					<div class="card-body">
						<div class="card-title-row">
							<div>
								<div class="card-name"><?php echo esc_html( $card['title'] ); ?></div>
								<div class="card-calories"><?php echo esc_html( $card['calories'] ); ?></div>
							</div>
							<div class="card-price"><?php echo esc_html( $card['price'] ); ?></div>
						</div>
						<div class="card-chip"><?php echo esc_html( $card['chip'] ); ?></div>
						<div class="card-details">
							<div class="card-detail-pill"><?php echo esc_html( $card['detail_one'] ); ?></div>
							<div class="card-detail-pill"><?php echo esc_html( $card['detail_two'] ); ?></div>
						</div>
					</div>
					<div class="card-footer">
						<a class="btn-card" href="<?php echo esc_url( $get_item_page_url( $card['category'], $card['title'] ) ); ?>">Read more</a>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="quality quality-section" id="iconic-items">
	<div class="container">
		<div class="section-header center">
			<div class="section-label">Iconic items</div>
			<h2 class="section-title">Top 3 Most Iconic McDonald&#8217;s Items</h2>
			<p class="section-sub">If you are new to the menu, start with these three anchors. They are useful comparison points for burger prices, nugget sizes, and breakfast value.</p>
		</div>
		<div class="quality-grid">
			<?php foreach ( $iconic_cards as $card ) : ?>
				<a class="quality-card" href="<?php echo esc_url( $card['url'] ); ?>">
					<div class="quality-card-icon">&#11088;</div>
					<div class="quality-card-title"><?php echo esc_html( $card['title'] ); ?></div>
					<div class="quality-card-text"><?php echo esc_html( $card['copy'] ); ?></div>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="deals" id="deals">
	<div class="container">
		<div class="section-header center">
			<div class="section-label" style="background: rgba(255,199,44,0.15); color: var(--mc-yellow);">Live Deals</div>
			<h2 class="section-title" style="color: white;">Deals and Offers</h2>
			<p class="section-sub" style="color: rgba(255,255,255,0.65);">Find McDonald&#8217;s deals and offers, including meal deals, app offers, combo deals, and limited-time value options.</p>
		</div>
		<div class="deals-grid">
			<?php foreach ( $deal_cards as $card ) : ?>
				<a href="<?php echo esc_url( $card['url'] ); ?>" class="deal-card <?php echo esc_attr( $card['class'] ); ?>">
					<div class="deal-badge-top"><?php echo esc_html( $card['label'] ); ?></div>
					<div class="deal-title"><?php echo esc_html( $card['title'] ); ?></div>
					<div class="deal-price"><?php echo esc_html( $card['price'] ); ?></div>
					<div class="deal-sub"><?php echo esc_html( $card['copy'] ); ?></div>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="quality quality-section" id="shareables-bundles">
	<div class="container">
		<div class="section-header center">
			<div class="section-label">Group orders</div>
			<h2 class="section-title">Shareables &amp; Bundles</h2>
			<p class="section-sub">Shareables are best when you are feeding more than one person. Compare nuggets, fries, drinks, and dessert add-ons together before deciding whether a bundle or separate items gives better value.</p>
		</div>
		<div class="quality-grid">
			<?php foreach ( $shareable_cards as $card ) : ?>
				<a class="quality-card" href="<?php echo esc_url( $card['url'] ); ?>">
					<div class="quality-card-icon">&#127831;</div>
					<div class="quality-card-title"><?php echo esc_html( $card['title'] ); ?></div>
					<div class="quality-card-text"><strong><?php echo esc_html( $card['price'] ); ?></strong> &middot; <?php echo esc_html( $card['cal'] ); ?></div>
					<div class="quality-card-text"><?php echo esc_html( $card['copy'] ); ?></div>
				</a>
			<?php endforeach; ?>
		</div>
		<p class="section-sub section-sub--links">For a fuller group-order explanation, open the <a href="<?php echo esc_url( $page_url( 'shareables-bundles' ) ); ?>">Shareables &amp; Bundles guide</a>, then compare <a href="<?php echo esc_url( $get_category_page_url( 'nuggets' ) ); ?>">McNuggets</a>, <a href="<?php echo esc_url( $get_category_page_url( 'sides' ) ); ?>">fries and sides</a>, and <a href="<?php echo esc_url( $get_category_page_url( 'sweets' ) ); ?>">desserts</a>.</p>
	</div>
</section>

<section class="full-menu" id="shakes-smoothies">
	<div class="container">
		<div class="section-header">
			<div class="section-label">Shakes &amp; Smoothies</div>
			<h2 class="section-title">McDonald&#8217;s Shakes &amp; Smoothies Prices</h2>
			<p class="section-sub">Shakes are tracked under Sweets &amp; Treats, while smoothies are tracked under Beverages. This quick table brings both together so readers can compare cold sweet drinks without leaving the homepage.</p>
		</div>
		<div class="menu-table-wrap">
			<table class="menu-table">
				<thead>
					<tr>
						<th>Item</th>
						<th>Tracked price</th>
						<th>Calories</th>
						<th>Menu path</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $shake_smoothie_cards as $card ) : ?>
						<tr>
							<td><a href="<?php echo esc_url( $card['url'] ); ?>"><?php echo esc_html( $card['name'] ); ?></a></td>
							<td class="td-price"><?php echo esc_html( $card['price'] ); ?></td>
							<td class="td-cal"><?php echo esc_html( $card['cal'] ); ?></td>
							<td><?php echo false !== stripos( $card['name'], 'Smoothie' ) ? 'Beverages' : 'Sweets & Treats'; ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<p class="section-sub section-sub--links">Open <a href="<?php echo esc_url( $get_category_page_url( 'sweets' ) ); ?>">Sweets &amp; Treats</a> for shakes and McFlurry options, or <a href="<?php echo esc_url( $get_category_page_url( 'beverages' ) ); ?>">Beverages</a> for smoothies, soft drinks, teas, and frozen drinks.</p>
	</div>
</section>

<section class="quality quality-section" id="secret-menu">
	<div class="container">
		<div class="section-header center">
			<div class="section-label">Custom order ideas</div>
			<h2 class="section-title">McDonald&#8217;s Secret Menu Guide</h2>
			<p class="section-sub">Secret menu items are custom combinations, not official McDonald&#8217;s menu items. Order by describing the ingredients or modifications clearly, and accept that some restaurants may not be able to make every custom build.</p>
		</div>
		<div class="quality-grid">
			<a class="quality-card" href="<?php echo esc_url( $page_url( 'mcdonalds-secret-menu' ) ); ?>">
				<div class="quality-card-icon">&#128161;</div>
				<div class="quality-card-title">Poor Man&#8217;s Big Mac idea</div>
				<div class="quality-card-text">A McDouble-style value build with Big Mac-inspired toppings can be cheaper than a full Big Mac when the restaurant allows customization.</div>
			</a>
			<a class="quality-card" href="<?php echo esc_url( $page_url( 'mcdonalds-secret-menu' ) ); ?>">
				<div class="quality-card-icon">&#127828;</div>
				<div class="quality-card-title">Land, Sea &amp; Air-style stack</div>
				<div class="quality-card-text">A custom stack using burger, chicken, and Filet-O-Fish pieces is a novelty order, not a standard listed item.</div>
			</a>
			<a class="quality-card" href="<?php echo esc_url( $page_url( 'mcdonalds-secret-menu' ) ); ?>">
				<div class="quality-card-icon">&#127846;</div>
				<div class="quality-card-title">Apple Pie McFlurry idea</div>
				<div class="quality-card-text">Dessert customizations are easier to understand when you compare the Apple Pie and McFlurry pages first.</div>
			</a>
		</div>
	</div>
</section>

<section class="ordering ordering-section">
	<div class="container">
		<div class="section-header center">
			<div class="section-label">Ways to order</div>
			<h2 class="section-title">How to Order McDonald's in the USA</h2>
			<p class="section-sub">Ordering habits in the US menu ecosystem are heavily shaped by the app, drive-thru speed, McDelivery and value-led combo shopping.</p>
		</div>
		<div class="order-grid ordering-grid">
			<div class="order-card">
				<div class="order-icon">&#128241;</div>
				<div class="order-title">McDonald&rsquo;s App</div>
				<div class="order-desc">Use the app for Mobile Order &amp; Pay, MyMcDonald&rsquo;s Rewards, limited-time deals and restaurant-specific availability before you check out.</div>
			</div>
			<div class="order-card">
				<div class="order-icon">&#128663;</div>
				<div class="order-title">Drive-Thru</div>
				<div class="order-desc">Drive-thru remains the fastest path for breakfast and value meals, especially when paired with app pickup or location-aware offers.</div>
			</div>
			<div class="order-card">
				<div class="order-icon">&#128666;</div>
				<div class="order-title">McDelivery</div>
				<div class="order-desc">Delivery menu pricing and service fees can differ from in-store pricing, so the cheapest value deal is not always the cheapest delivered deal.</div>
			</div>
			<div class="order-card">
				<div class="order-icon">&#127869;</div>
				<div class="order-title">Counter &amp; Kiosk</div>
				<div class="order-desc">In-store ordering is still useful when you want customizations, reward redemption help or a fast check of breakfast cut-off timing.</div>
			</div>
		</div>
	</div>
</section>

<section class="delivery-rewards delivery-section">
	<div class="container">
		<div class="section-header">
			<div class="section-label">Delivery &amp; rewards</div>
			<h2 class="section-title">McDelivery, App Deals &amp; Rewards</h2>
			<p class="section-sub">The McDonald&rsquo;s app remains the best place to confirm live availability, local pricing, and rotating rewards.</p>
		</div>
		<div class="delivery-grid">
			<div class="delivery-card">
				<div class="delivery-card-icon">&#128666;</div>
				<div class="delivery-card-content">
					<div class="delivery-card-title">McDelivery</div>
					<div class="delivery-card-text">McDelivery is one of the easiest ways to compare the listed menu price against the real delivered total. Delivery platform fees, bundled promotions and menu availability can all change the final value equation.</div>
					<div class="delivery-card-chips">
						<span class="platform-chip">Delivery pricing varies</span>
						<span class="platform-chip">Partner fees apply</span>
					</div>
				</div>
			</div>
			<div class="delivery-card">
				<div class="delivery-card-icon">&#127873;</div>
				<div class="delivery-card-content">
					<div class="delivery-card-title">MyMcDonald&rsquo;s Rewards</div>
					<div class="delivery-card-text">Rewards points, free-item redemptions, and member-only offers can change which McDonald&rsquo;s order gives the best value at checkout.</div>
					<div class="delivery-card-chips">
						<span class="platform-chip">$5 meal deals</span>
						<span class="platform-chip">App-exclusive offers</span>
						<span class="platform-chip">Rewards points</span>
					</div>
				</div>
			</div>
			<div class="delivery-card">
				<div class="delivery-card-icon">&#128241;</div>
				<div class="delivery-card-content">
					<div class="delivery-card-title">McDonald&rsquo;s App Deals</div>
					<div class="delivery-card-text">The app is usually the fastest place to check rotating coupons, app-only discounts, local restaurant participation, and pickup versus delivery price differences.</div>
					<div class="delivery-card-chips">
						<a class="platform-chip" href="<?php echo esc_url( $page_url( 'mcdonalds-app-deals' ) ); ?>">App deals guide</a>
						<span class="platform-chip">Location-aware</span>
					</div>
				</div>
			</div>
			<div class="delivery-card">
				<div class="delivery-card-icon">&#127881;</div>
				<div class="delivery-card-content">
					<div class="delivery-card-title">Collector&rsquo;s &amp; Limited-Time Meals</div>
					<div class="delivery-card-text">Promotional meals can be worth checking when packaging, collectibles, or seasonal items matter, but availability and price should always be verified locally.</div>
					<div class="delivery-card-chips">
						<a class="platform-chip" href="<?php echo esc_url( $page_url( 'limited-time-menu' ) ); ?>">Limited-time menu</a>
						<a class="platform-chip" href="<?php echo esc_url( $page_url( 'mcdonalds-fifa-world-cup-meal' ) ); ?>">FIFA Meal guide</a>
					</div>
				</div>
			</div>
			<div class="delivery-card">
				<div class="delivery-card-icon">&#128184;</div>
				<div class="delivery-card-content">
					<div class="delivery-card-title">McValue Deals</div>
					<div class="delivery-card-text">McValue is the value hub for meal deals, buy-one-add-one offers, mini desserts, and lower-cost ordering paths across breakfast, lunch and dinner.</div>
					<div class="delivery-card-chips">
						<a class="platform-chip" href="<?php echo esc_url( $page_url( 'mcdonalds-deals-mcvalue-guide' ) ); ?>">McValue guide</a>
						<a class="platform-chip" href="<?php echo esc_url( $get_category_page_url( 'mcvalue' ) ); ?>">McValue prices</a>
					</div>
				</div>
			</div>
			<div class="delivery-card">
				<div class="delivery-card-icon">&#128279;</div>
				<div class="delivery-card-content">
					<div class="delivery-card-title">Official McDonald's Resources</div>
					<div class="delivery-card-text">Use these official McDonald&rsquo;s links when you want the branded delivery FAQ, the official receipt survey, or the main app download page before you compare prices and deals on this site.</div>
					<div class="delivery-card-chips">
						<a class="platform-chip" href="https://www.mcdonalds.com/us/en-us/faq/mcdelivery.html" target="_blank" rel="noopener noreferrer">Official McDelivery FAQ</a>
						<a class="platform-chip" href="https://www.mcdvoice.com/" target="_blank" rel="noopener noreferrer">Official McDVoice Survey</a>
						<a class="platform-chip" href="https://www.mcdonalds.com/us/en-us/download-app.html" target="_blank" rel="noopener noreferrer">Official McDonald's App</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<section class="calories calories-section" id="calories">
	<div class="container">
		<div class="section-header center">
			<div class="section-label">Calorie guide</div>
			<h2 class="section-title">McDonald's USA Calorie Guide <?php echo esc_html( $current_year ); ?></h2>
			<p class="section-sub">Quick calorie anchors based on the current USA menu data tracked on this site.</p>
		</div>
		<div class="cal-grid">
			<div class="cal-card">
				<div class="cal-value">250</div>
				<div class="cal-name">Lowest Calorie Burger</div>
				<div class="cal-label">Hamburger</div>
			</div>
			<div class="cal-card">
				<div class="cal-value">300</div>
				<div class="cal-name">Classic Breakfast Pick</div>
				<div class="cal-label">Egg McMuffin</div>
			</div>
			<div class="cal-card">
				<div class="cal-value">0</div>
				<div class="cal-name">Lowest Calorie Drink</div>
				<div class="cal-label">Diet Coke / DASANI Water</div>
			</div>
			<div class="cal-card">
				<div class="cal-value">200</div>
				<div class="cal-name">Light Dessert</div>
				<div class="cal-label">Vanilla Cone</div>
			</div>
			<div class="cal-card">
				<div class="cal-value">480</div>
				<div class="cal-name">Large Fries</div>
				<div class="cal-label">World Famous Fries</div>
			</div>
			<div class="cal-card">
				<div class="cal-value">1650</div>
				<div class="cal-name">Largest Shareable</div>
				<div class="cal-label">40 Piece Chicken McNuggets</div>
			</div>
		</div>
	</div>
</section>

<section class="content-sidebar seo-section">
	<div class="container content-sidebar-grid seo-layout">
		<div class="content-main seo-main">
			<h2>McDonald's Prices USA <?php echo esc_html( $current_year ); ?> &mdash; Menu Price Guide</h2>
			<p>Use this McDonald&#8217;s prices USA guide to compare current McDonald&#8217;s menu prices by category before you order. It works as a practical McDonald&#8217;s menu with prices for burgers, breakfast, McCaf&eacute; drinks, Happy Meals, fries, desserts, combo meals, McValue items, and limited-time deals.</p>
			<p><strong>About this guide:</strong> McDonald&#8217;s Menu Prices USA is an independent U.S. McDonald&#8217;s menu price guide created by <strong>Sarah Jenkins</strong>, Lead Menu Analyst and Senior Editor based in the United States. Sarah tracks McDonald&#8217;s prices, deals, and menu changes to help US consumers compare costs before they order. This site is not affiliated with McDonald&#8217;s Corporation. For the highest-priority pages, start with <a href="<?php echo esc_url( $page_url( 'happy-meal-menu' ) ); ?>">Happy Meal prices</a>, the <a href="<?php echo esc_url( $get_item_page_url( 'beverages', 'Soft Drink Small' ) ); ?>">small drink price page</a>, and <a href="<?php echo esc_url( $page_url( 'breakfast-menu' ) ); ?>">breakfast menu prices</a>.</p>
			<p>The McDonald&#8217;s price list on this page is organized for quick planning, with menu item names, sample prices, calories, and category links in one place. You can start with breakfast menu prices, burger prices, McCaf&eacute; prices, Happy Meal prices, drinks menu prices, fries prices, desserts prices, or deals and offers depending on what you want to compare.</p>
			<p>McDonald&#8217;s prices are not always the same at every restaurant. Local franchise pricing, city costs, taxes, delivery apps, app-exclusive offers, and current promotions can change the final checkout total, so this guide should be used as a helpful overview of McDonald&#8217;s menu prices USA rather than a guaranteed national receipt.</p>
			<p>For value-focused ordering, compare combo meal prices, McValue meal deals, buy-one-add-one offers, app deals, and side or dessert add-ons before choosing a meal. This helps you see whether a standalone item, full combo meal, or current McDonald&#8217;s deal gives better value at your location.</p>
			<h3>How to use this McDonald&#8217;s price guide</h3>
			<p>Start with the category buttons above if you want a fast McDonald&#8217;s menu with prices, or use the quick links to jump directly to breakfast, burgers, McCaf&eacute;, Happy Meals, drinks, fries, desserts, sauces, or deals. Each section keeps the item name, sample price, and calorie context together so you can compare choices without opening several pages.</p>
			<h3>What to compare before ordering</h3>
			<p>When checking McDonald&#8217;s prices USA, compare the standalone item price with the full meal price, especially for Big Mac meals, Quarter Pounder meals, McNuggets meals, breakfast meals, and McCrispy options. A combo can be better value when you already want fries and a drink, while a single sandwich or side can be cheaper when you are only adding one item.</p>
			<h3>Why local prices may be different</h3>
			<p>This McDonald&#8217;s price list is meant for planning, not as a fixed national menu board. Restaurants in different cities, airports, malls, delivery zones, and app markets may show different McDonald&#8217;s menu prices because operating costs, taxes, franchise decisions, and promotions are not identical everywhere.</p>
			<p><strong>Important:</strong> This is an independent, unofficial menu price guide and is not affiliated with McDonald&#8217;s. Always confirm your final local price in the official McDonald&#8217;s app, restaurant menu board, delivery app, or checkout screen before buying.</p>
		</div>
		<div class="content-sidebar-panel seo-sidebar">
			<div class="sidebar-card">
				<div class="sidebar-card-title">Quick Links</div>
				<div class="sidebar-links">
					<a href="#whats-new" class="sidebar-link">&#127381; Limited-Time Menu</a>
					<a href="#meals" class="sidebar-link">&#127859; Extra Value Meals</a>
					<a href="#mcvalue" class="sidebar-link">&#128184; McValue</a>
					<a href="#breakfast" class="sidebar-link">&#129374; Breakfast Prices</a>
					<a href="#burgers" class="sidebar-link">&#127828; Burger Prices</a>
					<a href="#chickenfish" class="sidebar-link">&#127831; Chicken &amp; Fish</a>
					<a href="#nuggets" class="sidebar-link">&#127831; McNuggets &amp; Strips</a>
					<a href="#snackwrap" class="sidebar-link">&#127791; Snack Wrap</a>
					<a href="#happymeal" class="sidebar-link">&#127881; Happy Meal</a>
					<a href="#sweets" class="sidebar-link">&#127846; Sweets &amp; Treats</a>
					<a href="#mccafe" class="sidebar-link">&#9749; McCafe Coffees</a>
					<a href="#beverages" class="sidebar-link">&#127865; Beverage Prices</a>
					<a href="#sauces" class="sidebar-link">&#129514; Sauces &amp; Condiments</a>
				</div>
			</div>
			<div class="sidebar-card">
				<div class="sidebar-card-title">Editorial &amp; Trust</div>
				<div class="sidebar-links">
					<a href="<?php echo esc_url( home_url( '/pricing-methodology/' ) ); ?>" class="sidebar-link">&#128221; How We Track Prices</a>
					<a href="<?php echo esc_url( home_url( '/about/' ) ); ?>" class="sidebar-link">&#8505; About Us</a>
					<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="sidebar-link">&#9993; Contact</a>
					<a href="<?php echo esc_url( home_url( '/disclaimer/' ) ); ?>" class="sidebar-link">&#9888; Disclaimer</a>
				</div>
			</div>
			<div class="sidebar-card">
				<div class="sidebar-card-title">About the writer</div>
				<p>Sarah Jenkins reviews McDonald&#8217;s USA menu prices, calories, value deals, and limited-time updates so readers can compare items before ordering.</p>
				<div class="sidebar-links">
					<a href="<?php echo esc_url( home_url( '/editorial-policy/' ) ); ?>" class="sidebar-link">&#128221; Editorial Policy</a>
					<a href="<?php echo esc_url( home_url( '/pricing-methodology/' ) ); ?>" class="sidebar-link">&#128269; Source Methodology</a>
				</div>
			</div>
		</div>
	</div>
</section>

<section class="holiday-hours hours-section">
	<div class="container hours-grid hours-wrapper">
		<div class="hours-main">
			<div class="section-label">Typical hours</div>
			<h2 class="section-title">McDonald's Hours (USA)</h2>
			<p class="section-sub">Typical USA breakfast hours generally start around 4:00 AM and end just before 10:30 or 11:00 AM depending on the day, with lunch and dinner taking over after that.</p>
			<div class="hours-table-wrap">
				<table class="hours-table">
					<thead>
						<tr>
							<th>Day</th>
							<th>Breakfast</th>
							<th>Lunch</th>
							<th>Dinner</th>
						</tr>
					</thead>
					<tbody>
						<tr><td>Sunday</td><td>4:00 AM &ndash; 10:59 AM</td><td>11:00 AM &ndash; 4:59 PM</td><td>5:00 PM &ndash; 3:59 AM</td></tr>
						<tr><td>Monday &ndash; Friday</td><td>4:00 AM &ndash; 10:29 AM</td><td>10:30 AM &ndash; 4:59 PM</td><td>5:00 PM &ndash; 3:59 AM</td></tr>
						<tr><td>Saturday</td><td>4:00 AM &ndash; 10:59 AM</td><td>11:00 AM &ndash; 4:59 PM</td><td>5:00 PM &ndash; 3:59 AM</td></tr>
					</tbody>
				</table>
			</div>
			<p style="font-size:13px;color:var(--mc-grey-500);margin-top:14px;font-style:italic;">&#9888;&#65039; Hours can vary by franchise and 24-hour status. Always verify your exact restaurant hours in the official McDonald&rsquo;s app before ordering.</p>
		</div>
		<div class="hours-sidebar">
			<div class="hours-tip-card hours-tip-box">
				<div class="hours-tip-title">&#128204; Quick Tips</div>
				<div class="hours-tip-item"><span class="hours-tip-em">&#128241;</span> Use the <strong>McDonald&rsquo;s app</strong> to confirm live breakfast cut-off times at your local restaurant.</div>
				<div class="hours-tip-item"><span class="hours-tip-em">&#128663;</span> Drive-thru restaurants often have the most predictable breakfast-to-lunch transition flow.</div>
				<div class="hours-tip-item"><span class="hours-tip-em">&#9200;</span> Monday through Friday breakfast typically ends earlier than on Saturday or Sunday.</div>
				<div class="hours-tip-item"><span class="hours-tip-em">&#127860;</span> Bagels, platters, and hash browns are breakfast-only in the current USA menu coverage and disappear after the breakfast window.</div>
				<div class="hours-tip-item"><span class="hours-tip-em">&#127839;</span> Lunch and dinner menu availability is usually strongest once the breakfast menu clears, especially for burgers and fries.</div>
			</div>
		</div>
	</div>
</section>

<section class="quality quality-section">
	<div class="container">
		<div class="section-header center">
			<div class="section-label">Ingredient notes</div>
			<h2 class="section-title">McDonald's USA Quality &amp; Ingredient Highlights</h2>
			<p class="section-sub">A quick high-level guide to the ingredient and menu facts readers most often want alongside pricing.</p>
		</div>
		<div class="quality-grid">
			<div class="quality-card"><div class="quality-card-icon">&#129385;</div><div class="quality-card-title">100% Beef Burgers</div><div class="quality-card-text">Core burgers like the Big Mac and Quarter Pounder are marketed around 100% beef patties, which is one of the most searched trust points on any McDonald&rsquo;s menu guide.</div></div>
			<div class="quality-card"><div class="quality-card-icon">&#127831;</div><div class="quality-card-title">Chicken Focus</div><div class="quality-card-text">McNuggets, McCrispy sandwiches and McCrispy Strips are central to the current US chicken lineup and are heavily featured in both value deals and combo meals.</div></div>
			<div class="quality-card"><div class="quality-card-icon">&#129370;</div><div class="quality-card-title">Breakfast Core</div><div class="quality-card-text">Egg McMuffins, bagels, biscuits and McGriddles remain key breakfast pillars, with breakfast times still mattering more in the US than many readers expect.</div></div>
			<div class="quality-card"><div class="quality-card-icon">&#9749;</div><div class="quality-card-title">McCaf&#233; Expansion</div><div class="quality-card-text">Coffee, espresso drinks, iced coffees and frappés make McCaf&#233; a major part of the menu, not just a small add-on category.</div></div>
			<div class="quality-card"><div class="quality-card-icon">&#127839;</div><div class="quality-card-title">Fries &amp; Sauces</div><div class="quality-card-text">World Famous Fries plus the sauce lineup still drive side-order behavior, especially inside combo meals and shareables.</div></div>
			<div class="quality-card"><div class="quality-card-icon">&#128184;</div><div class="quality-card-title">Value-Led Ordering</div><div class="quality-card-text">App offers, McValue, meal deals and bundle packs define the current US savings story much more than a single flat “cheap menu” ever could.</div></div>
		</div>
	</div>
</section>

<section class="history history-section">
	<div class="container history-grid">
		<div class="history-main">
			<div class="section-label">McDonald's in America</div>
			<h2 class="section-title">McDonald's USA History &amp; Locations</h2>
			<div class="timeline">
				<?php foreach ( $timeline_items as $item ) : ?>
					<div class="timeline-item">
						<div class="timeline-year"><?php echo esc_html( $item['year'] ); ?></div>
						<div class="timeline-content">
							<div class="timeline-title"><?php echo esc_html( $item['title'] ); ?></div>
							<div class="timeline-text"><?php echo esc_html( $item['text'] ); ?></div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<div class="history-sidebar">
			<div class="stats-panel">
				<div class="stats-panel-title">&#128202; McDonald's USA in Numbers</div>
				<div class="stat-row"><span class="stat-row-label">US Restaurants</span><span class="stat-row-value">13,500+</span></div>
				<div class="stat-row"><span class="stat-row-label">Core Menu Buckets</span><span class="stat-row-value">15</span></div>
				<div class="stat-row"><span class="stat-row-label">Breakfast Window</span><span class="stat-row-value">4 AM &ndash; 10:30/11 AM</span></div>
				<div class="stat-row"><span class="stat-row-label">Value Platform</span><span class="stat-row-value">McValue</span></div>
				<div class="stat-row"><span class="stat-row-label">Current Price Currency</span><span class="stat-row-value">USD</span></div>
				<div class="stat-row"><span class="stat-row-label">Current Focus</span><span class="stat-row-value">McValue + limited-time items</span></div>
			</div>
		</div>
	</div>
</section>

<section class="internal-links" id="guides">
	<div class="container">
		<div class="section-header center">
			<div class="section-label">Deep-dive guides</div>
			<h2 class="section-title">McDonald's Menu Price Guides</h2>
			<p class="section-sub">Direct links to the main USA price guides readers use most often for menu planning, value comparisons, nutrition checks, and regional price research.</p>
		</div>
		<div class="link-grid links-grid">
			<?php foreach ( $guide_cards as $card ) : ?>
				<a href="<?php echo esc_url( $card['href'] ); ?>" class="link-card">
					<div class="link-card-icon">&#8594;</div>
					<div class="link-card-title"><?php echo esc_html( $card['title'] ); ?></div>
					<div class="link-card-text"><?php echo esc_html( $card['text'] ); ?></div>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="faq-section" id="faq">
	<div class="container">
		<h2 class="section-title">McDonald's Prices FAQs</h2>
		<div class="faq-list">
			<?php foreach ( $faq_items as $faq ) : ?>
				<details class="faq-item">
					<summary class="faq-q"><?php echo esc_html( $faq['question'] ); ?><span class="faq-icon">+</span></summary>
					<div class="faq-a" style="max-height:none;overflow:visible;padding:0 20px 20px;"><p><?php echo esc_html( $faq['answer'] ); ?></p></div>
				</details>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<div class="container">
	<p><?php echo wp_kses_post( $footer_disclaimer ); ?></p>
</div>
<!-- /wp:html -->
</div>
<!-- /wp:group -->
<?php

return trim( ob_get_clean() );

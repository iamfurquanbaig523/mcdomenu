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

$featured_items = array(
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
	array( 'name' => 'The HUNTRIX Meal', 'cal' => $format_calories( $huntrix_row['calories'] ?? '' ), 'price' => $huntrix_row['price'] ?? '~$11.99', 'status' => 'New', 'emoji' => '&#127909;', 'category' => 'whats-new' ),
	array( 'name' => 'The Saja Boys Breakfast Meal', 'cal' => $format_calories( $saja_boys_row['calories'] ?? '' ), 'price' => $saja_boys_row['price'] ?? '~$10.49', 'status' => 'Limited Time', 'emoji' => '&#127860;', 'category' => 'whats-new' ),
	array( 'name' => 'Ramyeon McShaker Fries', 'cal' => $format_calories( $ramyeon_fries_row['calories'] ?? '' ), 'price' => $ramyeon_fries_row['price'] ?? '~$4.49', 'status' => 'Limited Time', 'emoji' => '&#127839;', 'category' => 'whats-new' ),
	array( 'name' => 'The BIG ARCH', 'cal' => $format_calories( $big_arch_row['calories'] ?? '' ), 'price' => $big_arch_row['price'] ?? '~$8.99', 'status' => 'Limited Time', 'emoji' => '&#127828;', 'category' => 'whats-new' ),
	array( 'name' => 'The BIG ARCH Meal', 'cal' => $format_calories( $big_arch_meal_row['calories'] ?? '' ), 'price' => $big_arch_meal_row['price'] ?? '~$13.49', 'status' => 'Limited Time', 'emoji' => '&#127828;', 'category' => 'whats-new' ),
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

$menu_section_blueprints = array(
	array(
		'source_id'    => 'evm',
		'id'           => 'meals',
		'title'        => 'Extra Value Meals',
		'icon'         => '&#127859;',
		'description'  => 'Breakfast, lunch, and dinner combo meals pulled from the attached USA menu file, including breakfast sandwiches, burgers, McCrispy meals, McNuggets meals, snack wrap meals, and Filet-O-Fish meals.',
	),
	array(
		'source_id'    => 'mcvalue',
		'id'           => 'mcvalue',
		'title'        => 'McValue',
		'icon'         => '&#128184;',
		'description'  => 'The current McValue lineup from the attached USA menu file, including $5 meal deals, buy one add one for $1 offers, and lower-cost dessert and snack picks.',
	),
	array(
		'source_id'    => 'bfast',
		'id'           => 'breakfast',
		'title'        => 'Breakfast',
		'icon'         => '&#129374;',
		'description'  => 'Breakfast sandwiches, McGriddles, bagels, platters, oatmeal, hash browns, and hotcakes from the latest USA menu source.',
	),
	array(
		'source_id'    => 'burgers',
		'id'           => 'burgers',
		'title'        => 'Burgers',
		'icon'         => '&#127828;',
		'description'  => 'Current USA burger prices for Big Mac, Quarter Pounder builds, McDouble, Daily Double, cheeseburgers, and hamburgers.',
	),
	array(
		'source_id'    => 'chicken',
		'id'           => 'chickenfish',
		'title'        => 'Chicken & Fish Sandwiches',
		'icon'         => '&#127831;',
		'description'  => 'Chicken and fish sandwich pricing for McCrispy builds, Filet-O-Fish, and McChicken from the attached USA menu file.',
	),
	array(
		'source_id'    => 'nuggets',
		'id'           => 'nuggets',
		'title'        => 'McNuggets & McCrispy Strips',
		'icon'         => '&#127831;',
		'description'  => 'Chicken McNuggets and McCrispy Strips prices, from snack sizes up to 40-piece shareables.',
	),
	array(
		'source_id'    => 'wrap',
		'id'           => 'snackwrap',
		'title'        => 'Snack Wrap',
		'icon'         => '&#127791;',
		'description'  => 'Both current Snack Wrap flavors from the attached USA menu source.',
	),
	array(
		'source_id'    => 'sides',
		'id'           => 'sides',
		'title'        => 'Fries & Sides',
		'icon'         => '&#127839;',
		'description'  => 'World Famous Fries in each listed size plus Apple Slices from the latest USA side menu data.',
	),
	array(
		'source_id'    => 'happy',
		'id'           => 'happymeal',
		'title'        => 'Happy Meal',
		'icon'         => '&#127881;',
		'description'  => 'Current Happy Meal prices for hamburger and McNuggets builds from the attached USA menu file.',
	),
	array(
		'source_id'    => 'sweets',
		'id'           => 'sweets',
		'title'        => 'Sweets & Treats',
		'icon'         => '&#127846;',
		'description'  => 'McFlurries, cones, sundaes, shakes, pies, and cookies from the current USA sweets section.',
	),
	array(
		'source_id'    => 'coffee',
		'id'           => 'mccafe',
		'title'        => 'McCafe Coffees',
		'icon'         => '&#9749;',
		'description'  => 'Full McCafe coffee and espresso pricing from the attached USA menu file, including hot drinks, iced drinks, frappes, and hot chocolate.',
	),
	array(
		'source_id'    => 'bev',
		'id'           => 'beverages',
		'title'        => 'Beverages',
		'icon'         => '&#127865;',
		'description'  => 'Soft drinks, frozen drinks, smoothies, lemonade, tea, juice, milk, and bottled water from the current USA beverage section.',
	),
	array(
		'source_id'    => 'sauce',
		'id'           => 'sauces',
		'title'        => 'Sauces & Condiments',
		'icon'         => '&#129514;',
		'description'  => 'Current dipping sauces and condiments, including included sauces and low-cost paid extras.',
	),
);

$menu_sections = array();

foreach ( $menu_section_blueprints as $section_blueprint ) {
	$source_section  = $get_menu_section( $section_blueprint['source_id'] );
	$menu_sections[] = array(
		'id'          => $section_blueprint['id'],
		'title'       => $section_blueprint['title'],
		'icon'        => $section_blueprint['icon'],
		'description' => $section_blueprint['description'],
		'rows'        => $flatten_menu_rows( $source_section, $section_blueprint['source_id'] ),
	);
}

$deal_cards = array(
	array(
		'class' => 'deal-red',
		'label' => '$5 Meal Deal',
		'title' => 'McChicken Meal Deal',
		'price' => '$5.00',
		'copy'  => 'McChicken, 4 pc McNuggets, small fries, and a small drink from the attached McValue lineup.',
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
		'copy'  => 'The Daily Double Meal Deal appears in the attached McValue data as the higher-entry limited-time meal deal option.',
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
		'copy'  => 'Mini M&M\'s and OREO McFlurry cups appear in the attached McValue Eats section as low-entry dessert options.',
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
	array( 'href' => $page_url( 'mccafe-menu' ), 'title' => 'McCafe Menu Prices USA', 'text' => 'Hot coffee, iced coffee, espresso drinks, frappes, and McCafe size-led pricing comparisons for the USA menu.' ),
	array( 'href' => $page_url( 'beverage-menu' ), 'title' => 'Drinks Menu Prices USA', 'text' => 'Soft drinks, tea, lemonade, juice, smoothies, frozen drinks, and beverage pricing across the USA menu.' ),
	array( 'href' => $page_url( 'sauces-condiments' ), 'title' => 'Sauces & Condiments Prices USA', 'text' => 'Included dips, paid sauces, packet condiments, and the add-on choices that change nuggets, fries, and shareable orders.' ),
	array( 'href' => $page_url( 'mcdonalds-deals-mcvalue-guide' ), 'title' => 'Deals & McValue Guide USA', 'text' => 'The main value pillar for $5-style meals, McValue ordering, app-led savings, and low-entry menu strategies.' ),
	array( 'href' => $page_url( 'mcdonalds-nutrition-calories-allergens' ), 'title' => 'Nutrition, Calories & Allergens Guide', 'text' => 'A broader McDonald\'s USA guide for calories, ingredients, allergen checks, and lighter-versus-heavier menu decisions.' ),
	array( 'href' => $page_url( 'mcdonalds-prices-by-state' ), 'title' => 'McDonald\'s Prices by State', 'text' => 'The regional pricing pillar for comparing state and city variation, local value differences, and market-level menu shifts.' ),
);

$faq_items = array(
	array(
		'question' => 'How much is a Big Mac in the USA?',
		'answer'   => 'The attached USA menu data lists the Big Mac at $5.99 on the core burger menu. Local restaurant, app, tax, and delivery pricing can still change the final total.',
	),
	array(
		'question' => 'What is on the McValue menu right now?',
		'answer'   => 'The current McValue lineup includes $5 McChicken and McDouble meal deals, an about-$6 Daily Double meal deal, breakfast buy one add one for $1 items like Sausage Biscuit and Hash Browns, lunch and dinner add-on picks like McChicken, Double Cheeseburger, 6 pc McNuggets, Small Fries, plus mini McFlurry treats.',
	),
	array(
		'question' => 'How much is a Happy Meal in the USA?',
		'answer'   => 'This USA update lists Hamburger Happy Meal at about $5.89, 4 pc McNuggets Happy Meal at about $6.19, and 6 pc McNuggets Happy Meal at about $7.29.',
	),
	array(
		'question' => 'What time does McDonald\'s serve breakfast in the USA?',
		'answer'   => 'The attached breakfast data notes that breakfast is typically served until 10:30 AM on weekdays and 11:00 AM on weekends, although exact cutoffs can vary by restaurant.',
	),
	array(
		'question' => 'How many calories are in McDonald\'s large fries?',
		'answer'   => 'Large World Famous Fries are listed at 480 calories in the current USA menu data used on this site.',
	),
	array(
		'question' => 'Does McDonald\'s USA have a vegan burger?',
		'answer'   => 'The attached USA menu file does not show a national vegan burger. The current lineup is focused on burgers, chicken, breakfast, fries, desserts, drinks, and coffee.',
	),
	array(
		'question' => 'How much is a McFlurry in the USA?',
		'answer'   => 'The current USA sweets data lists a regular OREO McFlurry at $5.59, a regular M&M\'s McFlurry at $5.59, and mini McFlurry options at $3.19 in the McValue section.',
	),
	array(
		'question' => 'What is the cheapest item on the McDonald\'s USA menu?',
		'answer'   => 'The lowest paid items in the attached USA menu data are Vanilla Cone at $1.29 and the Honest Kids Appley Ever After juice box at $1.29, followed by several $1.69 drink options.',
	),
);

$timeline_items = array(
	array( 'year' => '1955', 'title' => 'McDonald\'s National Expansion Begins', 'text' => 'Ray Kroc opened the first McDonald\'s restaurant for the modern system in Des Plaines, Illinois, launching the chain\'s coast-to-coast expansion.' ),
	array( 'year' => '1979', 'title' => 'Happy Meal Debuts', 'text' => 'The Happy Meal became a defining kids\' format and remains one of the most recognizable quick-service meal bundles in the US.' ),
	array( 'year' => '1983', 'title' => 'Chicken McNuggets Go Mainstream', 'text' => 'McNuggets became a lasting part of the US menu and eventually grew into one of the brand\'s biggest group-order and snack categories.' ),
	array( 'year' => '2000s', 'title' => 'McCafe Growth Accelerates', 'text' => 'McCafe helped McDonald\'s expand from burgers and fries into coffeehouse-style drinks, iced coffees, lattes, and frappes.' ),
	array( 'year' => '2026', 'title' => 'McValue, BIG ARCH and Limited-Time Meals', 'text' => 'The current USA menu file highlights McValue savings, BIG ARCH, Snack Wraps, seasonal promo meals, and limited-time desserts as major traffic drivers.' ),
);

$footer_disclaimer = '&#9888;&#65039; <strong>Disclaimer:</strong> This is an independent, unofficial website. McDonald&#8217;s Menu Prices USA is not affiliated with, endorsed by, or connected to McDonald&#8217;s Corporation in any way. All prices are sourced from publicly available menus and may vary by location, franchise, taxes, app offer, and date.';

$render_rows = static function ( array $rows, $category_id ) use ( $get_item_page_url, $get_item_media_url ) {
	foreach ( $rows as $row ) :
		$item_url   = $get_item_page_url( $category_id, $row['name'] ?? '' );
		$item_media = $get_item_media_url( $row['name'] ?? '' );
		?>
		<tr>
			<td>
				<div class="td-name">
					<a class="mcprices-inline-media" href="<?php echo esc_url( $item_url ); ?>">
						<?php if ( $item_media ) : ?>
							<img class="mcprices-inline-media__thumb" src="<?php echo esc_url( $item_media ); ?>" alt="<?php echo esc_attr( 'McDonald\'s ' . $row['name'] . ' price USA 2026' ); ?>" loading="lazy" decoding="async">
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
<div class="wp-block-group mcprices-page mcprices-managed-homepage" data-mcprices-pattern-version="3.2.1">
<!-- wp:html -->
<section class="hero">
	<div class="hero-bg"></div>
	<div class="hero-pattern"></div>
	<div class="hero-content">
		<div class="hero-left animate-fadeup">
			<div class="hero-eyebrow">Updated April 2026</div>
			<h1 class="hero-title">
				McDonald's Menu<br>
				Prices <span class="highlight">USA <?php echo esc_html( $current_year ); ?></span>
			</h1>
			<p class="hero-sub">The most complete and up-to-date McDonald's USA price list. Compare current dollar prices, calories, combo meals, McValue savings, breakfast picks, McCafe drinks, sauces, desserts, and limited-time menu items in one place.</p>
			<p class="hero-sub">Thousands of readers trust this page to compare accurate McDonald&rsquo;s menu prices, calories, and value deals before they order.</p>
			[mcprices_hero_search]
			<div class="hero-btns">
				<a href="#full-menu" class="btn-primary">&#127828; View Full Menu</a>
				<a href="#deals" class="btn-outline">&#127991;&#65039; Value Deals</a>
			</div>
			<div class="hero-stats">
				<div class="stat-item">
					<div class="stat-num">212+</div>
					<div class="stat-label">Menu Items</div>
				</div>
				<div class="stat-item">
					<div class="stat-num">15</div>
					<div class="stat-label">Categories</div>
				</div>
				<div class="stat-item">
					<div class="stat-num">April</div>
					<div class="stat-label">Last Updated</div>
				</div>
				<div class="stat-item">
					<div class="stat-num">13,500+</div>
					<div class="stat-label">US Locations</div>
				</div>
			</div>
		</div>
		<div class="hero-right animate-fadeup delay-2">
			<div class="hero-badge-float">
				&#128293; Most<br><span>Popular</span>
			</div>
			<div class="hero-card-main">
				<div class="hero-card-title">&#11088; Top Menu Items</div>
				<?php foreach ( $featured_items as $item ) : ?>
					<div class="featured-item">
						<div class="item-emoji"><?php echo $item['emoji']; ?></div>
						<div class="item-info">
							<div class="item-name"><a href="<?php echo esc_url( $get_item_page_url( $item['category'], $item['name'] ) ); ?>"><?php echo esc_html( $item['name'] ); ?></a></div>
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

<section class="categories">
	<div class="container">
		<div class="section-header">
			<div class="section-label">Browse by Category</div>
			<h2 class="section-title">What Are You Looking For&rarr;</h2>
			<p class="section-sub">Jump straight to the US menu section you need &mdash; pricing, calories, and value-focused picks included.</p>
		</div>
		<div class="cat-grid">
			<?php foreach ( $category_cards as $card ) : ?>
				<a href="<?php echo esc_url( $card['url'] ); ?>" class="cat-card<?php echo $card['class'] ? ' ' . esc_attr( $card['class'] ) : ''; ?>" data-category-id="<?php echo esc_attr( $card['id'] ); ?>">
					<span class="cat-emoji">
						<?php if ( ! empty( $card['image'] ) ) : ?>
							<img class="mcprices-media-icon mcprices-media-icon--category" src="<?php echo esc_url( $card['image'] ); ?>" alt="<?php echo esc_attr( 'McDonald\'s ' . $card['name'] . ' menu USA' ); ?>" loading="lazy" decoding="async">
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
			<p class="section-sub">Current limited-time and featured items showing up across the attached USA menu snapshot.</p>
		</div>
		<div class="mcprices-whats-new-intro">
			<p class="mcprices-whats-new-intro-text">The current US snapshot highlights <strong class="mcprices-whats-new-date">The BIG ARCH</strong>, a limited-time burger stacked with two quarter-pound beef patties and special sauce, <strong>The HUNTRIX Meal</strong>, which bundles 10-piece McNuggets with fries and a drink, and <strong>Ramyeon McShaker Fries</strong>, a limited-time seasoned fries variation not found on the standard sides lineup.</p>
		</div>
		<div class="new-items-grid">
			<?php foreach ( $whats_new_items as $item ) : ?>
				<?php $status_class = false !== stripos( $item['status'], 'new' ) ? 'avail-new' : 'avail-limited'; ?>
				<a href="<?php echo esc_url( $get_item_page_url( $item['category'], $item['name'] ) ); ?>" class="new-item-card">
					<div class="new-item-top">
						<div class="new-item-emoji"><?php echo $item['emoji']; ?></div>
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

<section class="featured-menu">
	<div class="container">
		<div class="section-header center">
			<div class="section-label">Featured Favorites</div>
			<h2 class="section-title">Popular McDonald's USA Picks</h2>
			<p class="section-sub">High-interest menu favorites that people compare most often: burgers, breakfast, chicken, fries, and dessert picks from the current USA file.</p>
		</div>
		<div class="menu-cards-grid featured-grid">
			<?php foreach ( $menu_cards as $card ) : ?>
				<div class="menu-card">
					<div class="card-badge-top"><?php echo wp_kses_post( $card['badge'] ); ?></div>
					<div class="card-img"></div>
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

<section class="full-menu" id="full-menu">
	<div class="container">
		<div class="section-header">
			<div class="section-label">Every category, every price</div>
			<h2 class="section-title">Complete McDonald's USA Menu <?php echo esc_html( $current_year ); ?></h2>
			<p class="section-sub">This original McDonald&#8217;s Menu Prices USA build now reflects 212 current item rows across 15 US-focused sections from the attached source file, while keeping the design native to Kadence.</p>
			<p class="section-sub">From Big Mac, Quarter Pounder, McChicken, and McNuggets prices to Egg McMuffin breakfast items, McCafe coffees, McFlurry desserts, Happy Meal options, fries, sauces, and McValue deals, this homepage keeps the core McDonald's USA menu categories, price points, and calorie references together in one place.</p>
			<p class="section-sub">If you want McDonald's menu prices USA in dollars, McDonald's breakfast menu prices, McDonald's burger prices, McDonald's Happy Meal prices, McDonald's McCafe prices, McDonald's dessert prices, or the latest McValue menu prices, this page is built to answer those exact searches fast.</p>
		</div>
		<div class="size-key">
			<strong>Format guide:</strong> Small-to-large price ranges are grouped where multiple sizes exist. All prices are shown in US dollars and can vary by location.
		</div>
		<div class="menu-tabs">
			<button class="menu-tab active" type="button" data-menu-filter="all" aria-pressed="true">&#9776; All</button>
			<button class="menu-tab" type="button" data-menu-filter="whats-new">&#127381; What's New</button>
			<?php foreach ( $menu_sections as $section ) : ?>
				<button class="menu-tab" type="button" data-menu-filter="<?php echo esc_attr( $section['id'] ); ?>"><?php echo $section['icon']; ?> <?php echo esc_html( wp_strip_all_tags( $section['title'] ) ); ?></button>
			<?php endforeach; ?>
		</div>
		<?php foreach ( $menu_sections as $section ) : ?>
			<div class="menu-section" id="<?php echo esc_attr( $section['id'] ); ?>" data-menu-category="<?php echo esc_attr( $section['id'] ); ?>">
				<div class="menu-section-head">
					<span class="menu-section-icon"><?php echo $section['icon']; ?></span>
					<span class="menu-section-title"><?php echo $section['title']; ?></span>
					<span class="menu-section-count"><?php echo esc_html( (string) count( $section['rows'] ) ); ?> items</span>
				</div>
				<p class="menu-section-desc"><?php echo esc_html( html_entity_decode( $section['description'], ENT_QUOTES, 'UTF-8' ) ); ?></p>
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
		<?php endforeach; ?>
	</div>
</section>

<section class="deals" id="deals">
	<div class="container">
		<div class="section-header center">
			<div class="section-label" style="background: rgba(255,199,44,0.15); color: var(--mc-yellow);">Live Deals</div>
			<h2 class="section-title" style="color: white;">McDonald's USA Value Deals <?php echo esc_html( $current_year ); ?></h2>
			<p class="section-sub" style="color: rgba(255,255,255,0.65);">Current deal structures, app savings, and McValue-led offers surfaced across the attached USA menu file.</p>
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

<section class="ordering ordering-section">
	<div class="container">
		<div class="section-header center">
			<div class="section-label">Ways to order</div>
			<h2 class="section-title">Ways to Order at McDonald's USA</h2>
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
				<div class="delivery-card-text">McDelivery is one of the easiest ways to compare the “menu price” against the real delivered total. Delivery platform fees, bundled promotions and menu availability can all change the final value equation.</div>
					<div class="delivery-card-chips">
						<span class="platform-chip">Delivery pricing varies</span>
						<span class="platform-chip">Partner fees apply</span>
					</div>
				</div>
			</div>
			<div class="delivery-card">
				<div class="delivery-card-icon">&#127873;</div>
				<div class="delivery-card-content">
					<div class="delivery-card-title">MyMcDonald&rsquo;s Rewards &amp; App Offers</div>
				<div class="delivery-card-text">App-led rewards and exclusive coupons are a major part of McDonald&rsquo;s USA value positioning right now. This is especially important for combo meals, fries promotions and limited-time promo tie-ins.</div>
					<div class="delivery-card-chips">
						<span class="platform-chip">$5 meal deals</span>
						<span class="platform-chip">App-exclusive offers</span>
						<span class="platform-chip">Rewards points</span>
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
			<h2>McDonald's USA Menu Prices <?php echo esc_html( $current_year ); ?> &mdash; Everything You Need to Know</h2>
			<p>This McDonald&#8217;s Menu Prices USA build keeps the native Kadence parent-theme integration in place while focusing the content entirely on the United States menu ecosystem. The homepage now tracks Extra Value Meals, McValue, breakfast sandwiches, burgers, chicken and fish sandwiches, McNuggets, Snack Wraps, fries and sides, Happy Meals, sweets, McCafe coffees, beverages, and sauces from the attached menu source.</p>
			<p>US pricing can vary heavily by market. The same item can shift between locations, app offers, delivery channels, and tax settings. That is why the site emphasizes current sample pricing, price ranges, and location variability rather than pretending there is one fixed national menu board.</p>
			<p>US value positioning is also different. Current savings revolve around McValue, buy-one-add-one offers, app-exclusive coupons, combo-style meal deals, and low-entry dessert add-ons. On this version of the site, those offers are surfaced both inside the main menu section and again in the dedicated deals block so readers can compare individual items with full combo options faster.</p>
			<p>Most visitors land here looking for fast answers about McDonald's breakfast prices, burger prices, combo meal prices, Happy Meal costs, McCafe drink prices, dessert prices, and the latest McValue offers. Adding those details directly into the homepage helps the page act as a stronger menu hub instead of a thin overview, while still keeping the structure focused on current McDonald's USA menu prices and calories.</p>
			<p>That means the homepage now targets direct search intent around McDonald's menu with prices, McDonald's full menu prices USA, McDonald's calories, McDonald's breakfast hours, Big Mac price, Quarter Pounder price, Egg McMuffin price, McNuggets price, fries price, McFlurry price, and McDonald's deals. Instead of relying only on tabs and cards, the page states those topics clearly in crawlable text so search engines and AI engines can understand the page as a complete McDonald's USA price guide.</p>
			<p>It also gives stronger context around the exact commercial topics people compare before ordering: breakfast menu prices, lunch and dinner combo meal prices, McValue meal deal prices, McDelivery pricing differences, app-exclusive McDonald's deals, and location-based price variation across the United States. That extra context improves topical depth for McDonald's USA menu prices without changing the design or moving users away from the main price tables.</p>
			<p><strong>Important:</strong> This is an independent, unofficial website. Prices may vary by franchise, city, delivery platform, app promotion and date. Always confirm your final local total in the official McDonald&rsquo;s app or on the order screen before buying.</p>
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
		</div>
	</div>
</section>

<section class="holiday-hours hours-section">
	<div class="container hours-grid hours-wrapper">
		<div class="hours-main">
			<div class="section-label">Typical hours</div>
			<h2 class="section-title">McDonald's Hours (USA)</h2>
			<p class="section-sub">Typical USA breakfast hours generally start around 4:00 AM and end just before 10:30 or 11:00 AM depending on the day, with lunch and dinner taking over after that.</p>
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
			<h2 class="section-title">In-Depth Price Guides</h2>
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
		<h2 class="section-title">McDonald's USA Menu Prices - Frequently Asked Questions</h2>
		<div class="faq-list">
			<?php foreach ( $faq_items as $faq ) : ?>
				<details class="faq-item">
					<summary class="faq-q"><?php echo esc_html( $faq['question'] ); ?><span class="faq-icon">+</span></summary>
					<div class="faq-a"><p><?php echo esc_html( $faq['answer'] ); ?></p></div>
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

<?php
/**
 * JSON-LD Structured Data for mcdomenuusa.com
 *
 * Outputs: BreadcrumbList (all inner pages), FAQPage (priority pages),
 * WebSite + Organization (homepage supplement — fixes knowledgegraph to Organization type).
 *
 * Rank Math handles Article/WebPage schema per-page.
 * This file adds the schema types Rank Math does not cover with current settings.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main entry: hook schema output into wp_head before Rank Math (priority 4).
 */
add_action( 'wp_head', 'kadence_mcprices_output_json_ld_schema', 4 );

function kadence_mcprices_output_json_ld_schema() {
	if ( is_admin() ) {
		return;
	}

	$blocks = array();

	// 1. WebSite + Organization on homepage only.
	if ( is_front_page() ) {
		$blocks[] = kadence_mcprices_schema_website();
		$blocks[] = kadence_mcprices_schema_organization();
		$blocks[] = kadence_mcprices_schema_person();
	}

	// 2. BreadcrumbList on every non-homepage page.
	if ( ! is_front_page() ) {
		$breadcrumb = kadence_mcprices_schema_breadcrumb();
		if ( $breadcrumb ) {
			$blocks[] = $breadcrumb;
		}
	}

	// 3. FAQPage on known priority pages.
	$faq = kadence_mcprices_schema_faqpage();
	if ( $faq ) {
		$blocks[] = $faq;
	}

	if ( empty( $blocks ) ) {
		return;
	}

	foreach ( $blocks as $schema ) {
		echo '<script type="application/ld+json">'
			. wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
			. "</script>\n";
	}
}

/* -----------------------------------------------------------------------
 * WEBSITE SCHEMA
 * --------------------------------------------------------------------- */

function kadence_mcprices_schema_website() {
	return array(
		'@context'        => 'https://schema.org',
		'@type'           => 'WebSite',
		'@id'             => 'https://mcdomenuusa.com/#website',
		'url'             => 'https://mcdomenuusa.com/',
		'name'            => "McDonald's Menu Prices USA",
		'description'     => "Independent reference for U.S. McDonald's menu prices, calories, deals, and ordering guides.",
		'publisher'       => array( '@id' => 'https://mcdomenuusa.com/#organization' ),
		'potentialAction' => array(
			'@type'       => 'SearchAction',
			'target'      => array(
				'@type'       => 'EntryPoint',
				'urlTemplate' => 'https://mcdomenuusa.com/?s={search_term_string}',
			),
			'query-input' => 'required name=search_term_string',
		),
		'inLanguage'      => 'en-US',
	);
}

/* -----------------------------------------------------------------------
 * ORGANIZATION SCHEMA
 * --------------------------------------------------------------------- */

function kadence_mcprices_schema_organization() {
	$logo_url = mcprices_site_url( 'assets/images/mcprices/mcprices-logo-schema.svg' );

	return array(
		'@context'    => 'https://schema.org',
		'@type'       => 'Organization',
		'@id'         => 'https://mcdomenuusa.com/#organization',
		'name'        => "McDonald's Menu Prices USA",
		'url'         => 'https://mcdomenuusa.com/',
		'logo'        => array(
			'@type'  => 'ImageObject',
			'@id'    => 'https://mcdomenuusa.com/#logo',
			'url'    => esc_url( $logo_url ),
			'width'  => 512,
			'height' => 512,
			'caption' => "McDonald's Menu Prices USA",
		),
		'description' => "McDonald's Menu Prices USA is an independent, country-specific reference for current U.S. McDonald's menu prices, calories, nutritional information, deals, and ordering guides. Not affiliated with McDonald's Corporation.",
		'founder'     => array( '@id' => 'https://mcdomenuusa.com/#author' ),
		'sameAs'      => array(
			'https://mcdomenuusa.com/',
		),
	);
}

/* -----------------------------------------------------------------------
 * PERSON SCHEMA — Editorial Team
 * --------------------------------------------------------------------- */

function kadence_mcprices_schema_person() {
	$profile = array(
		'name'        => get_bloginfo( 'name' ),
		'url'         => home_url( '/editorial-policy/' ),
		'description' => "The McDonald's Menu Prices USA editorial team reviews menu prices, calories, availability notes, and official source links so readers can compare before ordering.",
		'title'       => 'Editorial team',
	);

	if ( class_exists( '\Kadence\McPrices_Integration' ) ) {
		$front_page_id = (int) get_option( 'page_on_front' );
		$front_page    = $front_page_id ? get_post( $front_page_id ) : null;
		$profile       = \Kadence\McPrices_Integration::get_instance()->get_public_author_profile( $front_page );
	}

	return array(
		'@context'    => 'https://schema.org',
		'@type'       => 'Person',
		'@id'         => 'https://mcdomenuusa.com/#author',
		'name'        => $profile['name'],
		'url'         => $profile['url'],
		'description' => $profile['description'],
		'jobTitle'    => $profile['title'],
		'knowsAbout'  => array( "McDonald's menu prices", 'fast food pricing USA', 'McDonald\'s deals', 'fast food nutrition', 'consumer price tracking' ),
		'worksFor'    => array( '@id' => 'https://mcdomenuusa.com/#organization' ),
	);
}

/* -----------------------------------------------------------------------
 * BREADCRUMBLIST SCHEMA
 * --------------------------------------------------------------------- */

function kadence_mcprices_schema_breadcrumb() {
	if ( ! is_singular() && ! is_page() ) {
		return null;
	}

	$post_id = get_queried_object_id();

	if ( ! $post_id ) {
		return null;
	}

	$items   = array();
	$items[] = array(
		'@type'    => 'ListItem',
		'position' => 1,
		'name'     => "McDonald's Menu Prices USA",
		'item'     => 'https://mcdomenuusa.com/',
	);

	$ancestors = array_reverse( get_ancestors( $post_id, 'page' ) );

	$position = 2;
	foreach ( $ancestors as $ancestor_id ) {
		$ancestor_title = get_the_title( $ancestor_id );
		$ancestor_url   = get_permalink( $ancestor_id );

		if ( ! $ancestor_title || ! $ancestor_url ) {
			continue;
		}

		$ancestor_url = kadence_mcprices_normalize_runtime_url( $ancestor_url );

		$items[] = array(
			'@type'    => 'ListItem',
			'position' => $position,
			'name'     => html_entity_decode( $ancestor_title, ENT_QUOTES, 'UTF-8' ),
			'item'     => $ancestor_url,
		);
		$position++;
	}

	$current_title = get_the_title( $post_id );
	$current_url   = get_permalink( $post_id );

	if ( ! $current_title || ! $current_url ) {
		return null;
	}

	$current_url = kadence_mcprices_normalize_runtime_url( $current_url );

	$priority_title = kadence_mcprices_get_priority_page_title_for_path(
		kadence_mcprices_get_relative_page_path_for_post_id( $post_id )
	);

	$display_title = $priority_title ? $priority_title : $current_title;
	$display_title = html_entity_decode(
		preg_replace( '/\s*\|.*$/', '', $display_title ),
		ENT_QUOTES,
		'UTF-8'
	);

	$items[] = array(
		'@type'    => 'ListItem',
		'position' => $position,
		'name'     => $display_title,
		'item'     => $current_url,
	);

	return array(
		'@context'        => 'https://schema.org',
		'@type'           => 'BreadcrumbList',
		'itemListElement' => $items,
	);
}

/* -----------------------------------------------------------------------
 * FAQPAGE SCHEMA — priority pages
 * --------------------------------------------------------------------- */

function kadence_mcprices_schema_faqpage() {
	$path = kadence_mcprices_get_priority_request_path();

	// Also catch sub-pages by checking the actual post slug when path is empty.
	if ( '' === $path && is_singular( 'page' ) ) {
		$post = get_queried_object();
		if ( $post instanceof WP_Post ) {
			$path = $post->post_name;
		}
	}

	$faqs = kadence_mcprices_get_faqs_for_path( $path );

	if ( empty( $faqs ) ) {
		return null;
	}

	$entities = array();
	foreach ( $faqs as $faq ) {
		$entities[] = array(
			'@type'          => 'Question',
			'name'           => $faq['q'],
			'acceptedAnswer' => array(
				'@type' => 'Answer',
				'text'  => $faq['a'],
			),
		);
	}

	return array(
		'@context'   => 'https://schema.org',
		'@type'      => 'FAQPage',
		'mainEntity' => $entities,
	);
}

/**
 * Return FAQ question/answer pairs for a given page path.
 *
 * @param string $path Site-relative page path.
 * @return array<int, array{q: string, a: string}>
 */
function kadence_mcprices_get_faqs_for_path( $path ) {
	$path = trim( (string) $path, '/' );

	switch ( $path ) {

		case '':
		case 'home':
		case 'homepage':
			return array(
				array(
					'q' => "What are McDonald's current menu prices in the USA?",
					'a' => "McDonald's menu prices in the USA vary by location and franchise. This site tracks 207+ current items across 15 categories including burgers, breakfast, McCafé, Happy Meals, drinks, and desserts. Prices typically range from under \$2 for individual items to over \$10 for large meals.",
				),
				array(
					'q' => "How much is a Happy Meal at McDonald's?",
					'a' => "A McDonald's Happy Meal ranges from approximately \$5.89 for a Hamburger Happy Meal to \$7.29 for a 6-pc Chicken McNuggets Happy Meal. All options include a side (Apple Slices or kids fries), a drink, and a toy.",
				),
				array(
					'q' => "What time does McDonald's stop serving breakfast?",
					'a' => "McDonald's breakfast service typically ends at 10:30 AM on weekdays and 11:00 AM on weekends at most U.S. locations. Times vary by franchise, so checking the McDonald's app for your local location is the most reliable method.",
				),
				array(
					'q' => "Does McDonald's have a value menu in 2026?",
					'a' => "Yes. McDonald's offers the McValue menu which includes items such as the Sausage McMuffin, 4-pc Chicken McNuggets, small fries, and other options. The McValue menu is designed to offer lower-cost choices across breakfast, lunch, and dinner.",
				),
				array(
					'q' => "How much is a small drink at McDonald's?",
					'a' => "A McDonald's small soft drink is tracked at approximately \$1.69 in current USA menu data. Prices vary by location and drink type. Sweet tea and unsweetened iced tea are priced lower at around \$1.00 for small.",
				),
				array(
					'q' => "How much does a Big Mac cost in 2026?",
					'a' => "The Big Mac price at McDonald's USA is approximately \$5.29 to \$5.99 depending on the location, state, and franchise pricing. Urban and airport locations typically charge more than suburban or rural locations.",
				),
			);

		case 'happy-meal-menu':
			return array(
				array(
					'q' => "What is on the McDonald's Happy Meal menu in the USA?",
					'a' => "The McDonald's Happy Meal menu in the USA has three options: Hamburger Happy Meal, 4-pc Chicken McNuggets Happy Meal, and 6-pc Chicken McNuggets Happy Meal. Each includes a choice of Apple Slices or kid-sized fries, a drink (milk, chocolate milk, water, or juice box), and a toy.",
				),
				array(
					'q' => "How much is a McDonald's Happy Meal?",
					'a' => "A McDonald's Happy Meal costs approximately \$5.89 for the Hamburger option, \$6.19 for the 4-pc McNuggets option, and \$7.29 for the 6-pc McNuggets option. Prices vary by location and may differ in Hawaii, Alaska, and high-cost metro areas.",
				),
				array(
					'q' => "How many calories are in a McDonald's Happy Meal?",
					'a' => "McDonald's Happy Meal calorie counts on the standard build (Apple Slices and 1% Low Fat Milk) are approximately 475 kcal for the Hamburger Happy Meal, 420 kcal for the 4-pc McNuggets Happy Meal, and 510 kcal for the 6-pc McNuggets Happy Meal. All stay under the 600-calorie cap McDonald's enforces.",
				),
				array(
					'q' => "Can you get Apple Slices instead of fries in a Happy Meal?",
					'a' => "Yes. Every McDonald's Happy Meal in the USA lets you choose between Apple Slices or a kid-sized portion of World Famous Fries as the side. Apple Slices are the lower-calorie option.",
				),
				array(
					'q' => "Does every McDonald's Happy Meal come with a toy?",
					'a' => "Yes, every McDonald's Happy Meal in the USA includes one Happy Meal toy. The toy changes based on the current promotional theme, which typically rotates every few weeks.",
				),
				array(
					'q' => "What drinks come with a McDonald's Happy Meal?",
					'a' => "McDonald's Happy Meals come with a choice of 1% Low Fat Milk Jug, Reduced Sugar Low Fat Chocolate Milk Jug, DASANI Water Bottle, or an Honest Kids Appley Ever After Juice Box. Soft drinks are not included in the standard Happy Meal drink options.",
				),
				array(
					'q' => "Is the Happy Meal available at breakfast?",
					'a' => "No. McDonald's Happy Meals are not part of the breakfast menu in the USA. Happy Meals become available after breakfast service ends, typically after 10:30 AM on weekdays and 11:00 AM on weekends.",
				),
				array(
					'q' => "Which McDonald's Happy Meal is the best value?",
					'a' => "The 4-pc McNuggets Happy Meal at approximately \$6.19 offers the best calorie efficiency at around 420 kcal. The Hamburger Happy Meal at \$5.89 is the cheapest option. For the biggest serving, the 6-pc McNuggets Happy Meal provides more food at \$7.29.",
				),
				array(
					'q' => "Are McDonald's Happy Meal prices the same everywhere in the USA?",
					'a' => "No. McDonald's franchise pricing means Happy Meal prices vary by state, city, and individual location. Prices tracked on this site are based on national averages. Higher-cost cities, airports, and tourist areas typically charge more.",
				),
				array(
					'q' => "What is the difference between the 4-pc and 6-pc McNuggets Happy Meal?",
					'a' => "The 4-pc McNuggets Happy Meal (approximately \$6.19, ~420 kcal) is the lighter and cheaper option suited for younger or smaller children. The 6-pc McNuggets Happy Meal (approximately \$7.29, ~510 kcal) provides two additional nuggets and suits bigger appetites.",
				),
			);

		case 'breakfast-menu':
			return array(
				array(
					'q' => "What time is McDonald's breakfast in the USA?",
					'a' => "McDonald's breakfast in the USA is served from approximately 5:00 AM to 10:30 AM on weekdays and until 11:00 AM on weekends at most locations. Hours vary by franchise and location — check the McDonald's app for exact local times.",
				),
				array(
					'q' => "How much is an Egg McMuffin at McDonald's?",
					'a' => "An Egg McMuffin at McDonald's is tracked at approximately \$4.79 in current USA menu data. It is one of the most popular breakfast items and serves as the core benchmark for breakfast sandwich pricing.",
				),
				array(
					'q' => "How much is a Sausage McMuffin at McDonald's?",
					'a' => "A Sausage McMuffin at McDonald's is tracked at approximately \$2.49. It is the lowest-entry McMuffin-style breakfast sandwich on the menu and a popular McValue option.",
				),
				array(
					'q' => "Does McDonald's still serve all-day breakfast in 2026?",
					'a' => "No. McDonald's discontinued its all-day breakfast in most U.S. locations. Breakfast is now a morning-only menu segment, typically available until 10:30 AM on weekdays and 11:00 AM on weekends.",
				),
				array(
					'q' => "What items are on the McDonald's breakfast menu?",
					'a' => "The McDonald's breakfast menu includes McMuffins (Egg McMuffin, Sausage McMuffin), Biscuit Sandwiches (Bacon Egg & Cheese Biscuit, Sausage Biscuit), McGriddles, Bagel Sandwiches, Hash Browns, Hotcakes, Big Breakfast platters, Fruit & Maple Oatmeal, and Sausage Burritos.",
				),
				array(
					'q' => "How many calories are in McDonald's breakfast items?",
					'a' => "McDonald's breakfast calorie counts range from 150 calories (Hash Browns) to around 1,340 calories (Big Breakfast with Hotcakes). The Egg McMuffin is approximately 310 calories and the Sausage McMuffin is about 400 calories on the standard build.",
				),
				array(
					'q' => "What is the cheapest McDonald's breakfast item?",
					'a' => "The cheapest items on the McDonald's breakfast menu are typically the Sausage McMuffin (approximately \$2.49) and Hash Browns (approximately \$2.69). McValue breakfast options like the Sausage Biscuit may also be available at lower price points.",
				),
				array(
					'q' => "How much is a Big Breakfast with Hotcakes?",
					'a' => "A Big Breakfast with Hotcakes at McDonald's is tracked at approximately \$7.89 in current USA menu data. It is the highest-priced standard breakfast item and includes scrambled eggs, sausage patty, hash browns, a biscuit, and hotcakes.",
				),
				array(
					'q' => "Can you order McDonald's breakfast on delivery apps?",
					'a' => "Yes. McDonald's breakfast is available on DoorDash, Uber Eats, and the McDonald's app for delivery during breakfast hours. Delivery orders typically carry a markup on item prices plus delivery and service fees.",
				),
				array(
					'q' => "Are McDonald's breakfast prices different at different locations?",
					'a' => "Yes. McDonald's franchise pricing means breakfast item prices vary by location, state, and franchisee. Prices on this page are based on tracked national averages. Urban locations and airports typically price higher than suburban or rural ones.",
				),
			);

		case 'burgers-menu':
			return array(
				array(
					'q' => "How much does a Big Mac cost at McDonald's in 2026?",
					'a' => "A Big Mac at McDonald's is tracked at approximately \$5.29 to \$5.99 in current USA menu data. Prices vary by location, state, and franchise. Urban and high-cost areas typically price higher.",
				),
				array(
					'q' => "What burgers are on the McDonald's menu in the USA?",
					'a' => "The McDonald's burgers menu in the USA includes the Big Mac, Quarter Pounder with Cheese, Double Quarter Pounder with Cheese, McDouble, Double Cheeseburger, Cheeseburger, Hamburger, Daily Double, and the Bacon Quarter Pounder with Cheese.",
				),
				array(
					'q' => "How many calories are in a McDonald's Big Mac?",
					'a' => "A McDonald's Big Mac contains approximately 590 calories on the standard build with two beef patties, special sauce, lettuce, cheese, pickles, and onions on a sesame seed bun.",
				),
				array(
					'q' => "What is the cheapest burger at McDonald's?",
					'a' => "The cheapest burgers at McDonald's are the Hamburger and the Cheeseburger. The Hamburger is typically priced around \$1.99 to \$2.49 and the Cheeseburger around \$2.19 to \$2.99 depending on location.",
				),
				array(
					'q' => "How much is a Quarter Pounder with Cheese at McDonald's?",
					'a' => "A Quarter Pounder with Cheese at McDonald's is tracked at approximately \$5.79 in current USA menu data. The Double Quarter Pounder with Cheese is approximately \$7.89.",
				),
				array(
					'q' => "What is the difference between the McDouble and Double Cheeseburger?",
					'a' => "The McDouble has two beef patties and one slice of cheese, while the Double Cheeseburger has two beef patties and two slices of cheese. The Double Cheeseburger is typically priced slightly higher than the McDouble.",
				),
				array(
					'q' => "Can you get McDonald's burgers as a meal deal?",
					'a' => "Yes. Most McDonald's burgers are available as Extra Value Meals that include medium fries and a medium drink. The Big Mac Meal, Quarter Pounder Meal, and McDouble Meal Deal are among the most popular combo options.",
				),
			);

		case 'breakfast-hours':
			return array(
				array(
					'q' => "What time does McDonald's breakfast start in the USA?",
					'a' => "McDonald's breakfast typically starts at 5:00 AM at most U.S. locations. Some 24-hour locations may serve breakfast slightly earlier. The exact start time varies by franchise location.",
				),
				array(
					'q' => "What time does McDonald's breakfast end?",
					'a' => "McDonald's breakfast ends at 10:30 AM on weekdays and 11:00 AM on weekends at most U.S. locations. Some locations may cut off at 10:30 AM on all days. Check the McDonald's app for the exact cutoff at your nearest location.",
				),
				array(
					'q' => "Does McDonald's serve breakfast all day in 2026?",
					'a' => "No. McDonald's discontinued all-day breakfast in the USA. Breakfast is a morning-only window — typically until 10:30 AM on weekdays and 11:00 AM on weekends. After breakfast ends, the lunch and dinner menu replaces it.",
				),
				array(
					'q' => "What are McDonald's breakfast hours on weekends?",
					'a' => "On weekends (Saturday and Sunday), McDonald's typically extends breakfast service until 11:00 AM instead of 10:30 AM. Exact weekend hours vary by location — checking the McDonald's app for your specific location gives the most accurate times.",
				),
				array(
					'q' => "How do I find McDonald's breakfast hours near me?",
					'a' => "The most reliable way to find McDonald's breakfast hours near you is to use the McDonald's app or visit the McDonald's website store locator. Hours vary by individual franchise, so the app gives real-time, location-specific information.",
				),
				array(
					'q' => "Can different McDonald's locations have different breakfast hours?",
					'a' => "Yes. Because McDonald's restaurants are independently franchised, breakfast cutoff times can vary from one location to another even in the same city. Most follow the 10:30 AM or 11:00 AM pattern, but individual franchisees can set their own hours.",
				),
			);

		case 'menu/beverages-drinks':
		case 'beverages-drinks':
			return array(
				array(
					'q' => "How much is a small drink at McDonald's?",
					'a' => "A McDonald's small soft drink is tracked at approximately \$1.69 in current USA menu data. Sweet tea and unsweetened iced tea are available for around \$1.00 for a small size.",
				),
				array(
					'q' => "What drinks does McDonald's have on the menu?",
					'a' => "McDonald's drink menu includes soft drinks (Coca-Cola, Sprite, Dr Pepper, Fanta, Diet Coke, Hi-C), lemonade, sweet tea, unsweetened iced tea, smoothies (Strawberry Banana, Mango Pineapple), frozen drinks (Frozen Fanta, Frozen Coca-Cola), hot tea, orange juice, milk, and water.",
				),
				array(
					'q' => "How much is a McDonald's large soft drink?",
					'a' => "A McDonald's large soft drink is tracked at approximately \$2.29 in current USA menu data. Large sizes are available for most fountain drink options.",
				),
				array(
					'q' => "Does McDonald's still have \$1 drinks?",
					'a' => "McDonald's no longer offers a nationwide \$1 any-size drink deal as a permanent promotion. Individual items like Sweet Tea and Unsweetened Iced Tea are tracked around \$1.00. Check the McDonald's app for current drink promotions.",
				),
				array(
					'q' => "How much is a McDonald's smoothie?",
					'a' => "McDonald's smoothies are tracked at approximately \$3.99 for small, \$4.59 for medium, and \$5.29 for large. Flavors include Strawberry Banana and Mango Pineapple.",
				),
			);

		case 'menu/mccafe-coffees':
		case 'mccafe-menu':
			return array(
				array(
					'q' => "How much is a McDonald's McCafé coffee?",
					'a' => "McDonald's McCafé Premium Roast Coffee is tracked at approximately \$1.49 for small, \$1.79 for medium, and \$2.09 for large. Espresso drinks like lattes and cappuccinos range from \$3.49 to \$4.49 depending on size.",
				),
				array(
					'q' => "What coffee drinks does McDonald's McCafé have?",
					'a' => "McDonald's McCafé menu includes Premium Roast Coffee, Americano, Latte, Caramel Latte, French Vanilla Latte, Cappuccino, Mocha Latte, Caramel Macchiato, Hot Chocolate, Iced Coffee, Iced Lattes, Caramel Frappes, and Mocha Frappes.",
				),
				array(
					'q' => "How much is a McDonald's Caramel Frappe?",
					'a' => "A McDonald's Caramel Frappe is tracked at approximately \$3.79 for small, \$4.29 for medium, and \$4.99 for large in current USA menu data.",
				),
				array(
					'q' => "Does McDonald's McCafé have iced coffee?",
					'a' => "Yes. McDonald's McCafé iced coffee options include Iced Coffee, Iced Caramel Coffee, Iced French Vanilla Coffee, Iced Latte, Iced Caramel Latte, Iced French Vanilla Latte, Iced Caramel Macchiato, and Iced Mocha Latte.",
				),
				array(
					'q' => "How much is a McDonald's latte?",
					'a' => "A McDonald's McCafé Latte is tracked at approximately \$3.49 for small, \$3.99 for medium, and \$4.49 for large. Flavored lattes (Caramel, French Vanilla, Mocha) are priced approximately \$0.20 to \$0.50 higher.",
				),
			);

		default:
			return array();
	}
}

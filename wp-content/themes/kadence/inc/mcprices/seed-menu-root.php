<?php
/**
 * Menu root seeding and managed-page cleanup for McPrices.
 *
 * @package kadence
 */

namespace Kadence;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Keeps the native /menu/ page tree clean and admin-visible.
 */
class McPrices_Menu_Root_Seed {
	/**
	 * Root page slug/path.
	 */
	const ROOT_PATH = 'menu';

	/**
	 * Root page title.
	 */
	const ROOT_TITLE = 'McDonald\'s Full Menu USA';

	/**
	 * Root page excerpt.
	 */
	const ROOT_EXCERPT = 'Browse the full McDonald\'s USA menu, compare major categories, and jump into the dedicated breakfast, burgers, drinks, dessert, deals, nutrition, and regional pricing guides.';

	/**
	 * Ensure the menu root page exists and remove duplicate managed pages.
	 *
	 * @return void
	 */
	public static function maybe_seed() {
		$root_page_id = self::ensure_root_page();

		if ( ! $root_page_id ) {
			return;
		}

		self::normalize_managed_hierarchy( $root_page_id );
		self::cleanup_duplicate_managed_pages( $root_page_id );
		self::normalize_managed_hierarchy( $root_page_id );
	}

	/**
	 * Return the rich full-menu root page content.
	 *
	 * @return string
	 */
	protected static function get_root_content() {
		$now                = new \DateTimeImmutable( 'now', wp_timezone() );
		$current_year       = $now->format( 'Y' );
		$updated_date       = $now->format( 'F j, Y' );
		$links              = array(
			array( 'label' => 'Breakfast Menu Prices USA', 'url' => self::build_site_url( 'breakfast-menu/' ), 'description' => 'Use the breakfast pillar for McMuffins, biscuits, McGriddles, Hash Browns, and breakfast combo decisions.' ),
			array( 'label' => 'Burgers Menu Prices USA', 'url' => self::build_site_url( 'burgers-menu/' ), 'description' => 'Compare Big Mac, Quarter Pounder, McDouble, Hamburger, meal pricing, and premium-versus-value burger choices.' ),
			array( 'label' => 'Chicken & Fish Menu Prices USA', 'url' => self::build_site_url( 'chicken-fish-menu/' ), 'description' => 'Check McCrispy, McChicken, Filet-O-Fish, spicy builds, and sandwich-meal context.' ),
			array( 'label' => 'McNuggets & Strips Menu Prices USA', 'url' => self::build_site_url( 'nuggets-and-strips/' ), 'description' => 'Compare nugget piece counts, McCrispy Strips, share boxes, and sauce-driven ordering choices.' ),
			array( 'label' => 'Fries & Sides Prices USA', 'url' => self::build_site_url( 'fries-sides/' ), 'description' => 'See how fries sizes and side add-ons change the final total of a typical order.' ),
			array( 'label' => 'McDonald\'s Deals & McValue Guide USA', 'url' => self::build_site_url( 'mcdonalds-deals-mcvalue-guide/' ), 'description' => 'Open the value pillar for current meal deals, McValue offers, app-led savings, and bundle logic.' ),
			array( 'label' => 'McDonald\'s Nutrition, Calories & Allergens Guide', 'url' => self::build_site_url( 'mcdonalds-nutrition-calories-allergens/' ), 'description' => 'Use the broader nutrition pillar when the menu decision depends on calories, ingredients, or allergen checks.' ),
			array( 'label' => 'McDonald\'s Prices by State', 'url' => self::build_site_url( 'mcdonalds-prices-by-state/' ), 'description' => 'Understand why national menu prices and local checkout totals can differ from market to market.' ),
		);
		$category_rows      = array(
			array( self::build_site_link( 'menu/burgers/', 'Burgers' ), 'Big Mac, Quarter Pounder, McDouble, Cheeseburger, Daily Double', '$2.99' ),
			array( self::build_site_link( 'menu/breakfast/', 'Breakfast' ), 'Egg McMuffin, McGriddles, Hotcakes, Sausage Biscuit, Big Breakfast', '$1.99' ),
			array( self::build_site_link( 'menu/chickenfish/', 'Chicken &amp; Fish' ), 'McCrispy, Spicy McCrispy, McChicken, Filet-O-Fish', '$4.69' ),
			array( self::build_site_link( 'menu/nuggets/', 'Nuggets &amp; Strips' ), '4, 6, 10, 20, and 40-piece McNuggets plus McCrispy Strips', '$3.19' ),
			array( self::build_site_link( 'menu/meals/', 'Meals' ), 'Combo meals for burgers, chicken, and breakfast items', '$8.19' ),
			array( self::build_site_link( 'menu/mcvalue/', 'McValue' ), 'Under $3 items, meal deals from $4, and Buy 1 Add 1 for $1 offers', '$1.50' ),
			array( self::build_site_link( 'menu/deals/', 'Deals' ), 'McChicken Meal Deal, McDouble Meal Deal, Daily Double Deal', '$5.00' ),
			array( self::build_site_link( 'menu/mccafe/', 'McCafe' ), 'Lattes, frappes, cappuccinos, iced coffee, and hot chocolate', '$2.49' ),
			array( self::build_site_link( 'menu/beverages/', 'Beverages' ), 'Soft drinks, sweet tea, lemonade, smoothies, and milk', '$1.00' ),
			array( self::build_site_link( 'menu/sides/', 'Sides' ), 'World Famous Fries in three sizes plus Apple Slices', '$1.39' ),
			array( self::build_site_link( 'menu/happymeal/', 'Happy Meal' ), 'Hamburger and McNuggets Happy Meals with kid-friendly bundles', '$4.99' ),
			array( self::build_site_link( 'menu/sweets/', 'Sweets &amp; Treats' ), 'McFlurry, shakes, sundaes, Apple Pie, cookies, and Vanilla Cone', '$1.00' ),
			array( self::build_site_link( 'menu/sauces/', 'Sauces' ), 'Sweet N Sour, Tangy BBQ, Honey Mustard, Creamy Ranch, and more', '$0.25' ),
			array( self::build_site_link( 'menu/snackwrap/', 'Snack Wraps' ), 'Ranch Snack Wrap and Spicy Snack Wrap', '$2.99' ),
			array( self::build_site_link( 'menu/whats-new/', 'What&#8217;s New' ), 'The Big Arch, Ramyeon McShaker Fries, and the Saja Boys Breakfast Meal', '$7.99' ),
		);
		$burger_rows        = array(
			array( self::build_site_link( 'menu/burgers/big-mac/', 'Big Mac' ), '$5.99', '590 cal' ),
			array( self::build_site_link( 'menu/burgers/quarter-pounder-with-cheese/', 'Quarter Pounder with Cheese' ), '$6.39', '520 cal' ),
			array( self::build_site_link( 'menu/burgers/double-quarter-pounder-with-cheese/', 'Double Quarter Pounder with Cheese' ), '$6.99', '740 cal' ),
			array( self::build_site_link( 'menu/burgers/quarter-pounder-with-cheese-deluxe/', 'Quarter Pounder with Cheese Deluxe' ), '$7.49', '630 cal' ),
			array( self::build_site_link( 'menu/burgers/bacon-quarter-pounder-with-cheese/', 'Bacon Quarter Pounder with Cheese' ), '$8.99', '630 cal' ),
			array( self::build_site_link( 'menu/burgers/mcdouble/', 'McDouble' ), '$4.59', '390 cal' ),
			array( self::build_site_link( 'menu/burgers/daily-double/', 'Daily Double' ), '$4.39', '490 cal' ),
			array( self::build_site_link( 'menu/burgers/double-cheeseburger/', 'Double Cheeseburger' ), '$3.99', '450 cal' ),
			array( self::build_site_link( 'menu/burgers/cheeseburger/', 'Cheeseburger' ), '$2.99', '300 cal' ),
			array( self::build_site_link( 'menu/burgers/hamburger/', 'Hamburger' ), '$2.49', '250 cal' ),
		);
		$breakfast_rows     = array(
			array( self::build_site_link( 'menu/breakfast/egg-mcmuffin/', 'Egg McMuffin' ), '$4.29', '310 cal' ),
			array( self::build_site_link( 'menu/breakfast/sausage-mcmuffin-with-egg/', 'Sausage McMuffin with Egg' ), '$4.79', '480 cal' ),
			array( self::build_site_link( 'menu/breakfast/sausage-mcmuffin/', 'Sausage McMuffin' ), '$2.99', '400 cal' ),
			array( self::build_site_link( 'menu/breakfast/bacon-egg-cheese-mcgriddles/', 'Bacon, Egg &amp; Cheese McGriddles' ), '$5.19', '430 cal' ),
			array( self::build_site_link( 'menu/breakfast/sausage-egg-cheese-mcgriddles/', 'Sausage, Egg &amp; Cheese McGriddles' ), '$5.19', '550 cal' ),
			array( self::build_site_link( 'menu/breakfast/bacon-egg-cheese-bagel/', 'Bacon, Egg &amp; Cheese Bagel' ), '$5.59', '620 cal' ),
			array( self::build_site_link( 'menu/breakfast/sausage-biscuit-with-egg/', 'Sausage Biscuit with Egg' ), '$4.49', '530 cal' ),
			array( self::build_site_link( 'menu/breakfast/sausage-burrito/', 'Sausage Burrito' ), '$2.99', '310 cal' ),
			array( self::build_site_link( 'menu/breakfast/big-breakfast/', 'Big Breakfast' ), '$7.59', '760 cal' ),
			array( self::build_site_link( 'menu/breakfast/big-breakfast-with-hotcakes/', 'Big Breakfast with Hotcakes' ), '$8.29', '1,340 cal' ),
		);
		$chicken_rows       = array(
			array( self::build_site_link( 'menu/chickenfish/mccrispy/', 'McCrispy' ), '$4.99', '470 cal' ),
			array( self::build_site_link( 'menu/chickenfish/spicy-mccrispy/', 'Spicy McCrispy' ), '$4.99', '530 cal' ),
			array( self::build_site_link( 'menu/chickenfish/deluxe-mccrispy/', 'Deluxe McCrispy' ), '$5.99', '530 cal' ),
			array( self::build_site_link( 'menu/chickenfish/spicy-deluxe-mccrispy/', 'Spicy Deluxe McCrispy' ), '$5.99', '580 cal' ),
			array( self::build_site_link( 'menu/chickenfish/mcchicken/', 'McChicken' ), '$3.10', '400 cal' ),
			array( self::build_site_link( 'menu/chickenfish/filet-o-fish/', 'Filet-O-Fish' ), '$5.69', '390 cal' ),
		);
		$nugget_rows        = array(
			array( self::build_site_link( 'menu/nuggets/4-pc-chicken-mcnuggets/', '4-Piece Chicken McNuggets' ), '$3.19', '170 cal' ),
			array( self::build_site_link( 'menu/nuggets/6-pc-chicken-mcnuggets/', '6-Piece Chicken McNuggets' ), '$4.39', '250 cal' ),
			array( self::build_site_link( 'menu/nuggets/10-pc-chicken-mcnuggets/', '10-Piece Chicken McNuggets' ), '$5.79', '410 cal' ),
			array( self::build_site_link( 'menu/nuggets/20-pc-chicken-mcnuggets/', '20-Piece Chicken McNuggets' ), '$9.00', '830 cal' ),
			array( self::build_site_link( 'menu/nuggets/40-pc-chicken-mcnuggets/', '40-Piece Chicken McNuggets' ), '$13.19', '1,650 cal' ),
			array( self::build_site_link( 'menu/nuggets/mccrispy-strips/', 'McCrispy Strips' ), '$5.49', '400 cal' ),
		);
		$mccafe_hot_rows    = array(
			array( self::build_site_link( 'menu/mccafe/premium-roast-coffee/', 'Premium Roast Coffee' ), '$1.49', '$1.79', '$2.09' ),
			array( self::build_site_link( 'menu/mccafe/americano-small/', 'Americano' ), '$2.49', '$2.99', '$3.49' ),
			array( self::build_site_link( 'menu/mccafe/mccafe-latte-small/', 'Latte' ), '$3.49', '$3.99', '$4.49' ),
			array( self::build_site_link( 'menu/mccafe/mccafe-caramel-latte-small/', 'Caramel Latte' ), '$3.69', '$4.19', '$4.69' ),
			array( self::build_site_link( 'menu/mccafe/mccafe-french-vanilla-latte-small/', 'French Vanilla Latte' ), '$3.69', '$4.19', '$4.69' ),
			array( self::build_site_link( 'menu/mccafe/mccafe-cappuccino-small/', 'Cappuccino' ), '$3.49', '$3.99', '$4.49' ),
			array( self::build_site_link( 'menu/mccafe/mccafe-hot-chocolate-small/', 'Hot Chocolate' ), '$2.99', '$3.49', '$3.99' ),
		);
		$mccafe_iced_rows   = array(
			array( self::build_site_link( 'menu/mccafe/mccafe-iced-coffee-small/', 'Iced Coffee' ), '$3.19', '$3.59', '$4.19' ),
			array( self::build_site_link( 'menu/mccafe/mccafe-iced-caramel-latte-small/', 'Iced Caramel Latte' ), '$3.69', '$4.19', '&mdash;' ),
			array( self::build_site_link( 'menu/mccafe/mccafe-iced-mocha-latte-small/', 'Iced Mocha Latte' ), '$3.79', '$4.29', '&mdash;' ),
			array( self::build_site_link( 'menu/mccafe/mccafe-caramel-frappe-small/', 'Caramel Frappe' ), '$3.79', '$4.29', '$4.99' ),
			array( self::build_site_link( 'menu/mccafe/mccafe-mocha-frappe-small/', 'Mocha Frappe' ), '$3.79', '$4.29', '$4.99' ),
			array( self::build_site_link( 'menu/mccafe/mccafe-caramel-macchiato-small/', 'Caramel Macchiato' ), '$3.79', '$4.29', '$4.79' ),
		);
		$mcvalue_rows       = array(
			array( self::build_site_link( 'menu/mcvalue/sausage-mcmuffin/', 'Sausage McMuffin' ), '$2.99 (or $1.50 promo)' ),
			array( self::build_site_link( 'menu/mcvalue/the-mcdouble-meal-deal/', 'McDouble' ), '$2.99 (or $2.50 promo)' ),
			array( self::build_site_link( 'menu/mcvalue/mcchicken/', 'McChicken' ), '$2.99' ),
			array( self::build_site_link( 'menu/mcvalue/4-pc-chicken-mcnuggets/', '4-Piece Chicken McNuggets' ), '$2.99' ),
			array( self::build_site_link( 'menu/mcvalue/sausage-biscuit/', 'Sausage Biscuit' ), '$2.49' ),
			array( self::build_site_link( 'menu/mcvalue/sausage-burrito/', 'Sausage Burrito' ), '$2.49' ),
			array( self::build_site_link( 'menu/mcvalue/hash-browns/', 'Hash Browns' ), '$2.69' ),
			array( self::build_site_link( 'menu/mcvalue/small-world-famous-fries/', 'Small World Famous Fries' ), '$2.89' ),
			array( self::build_site_link( 'menu/mcvalue/6-pc-chicken-mcnuggets/', '6-Piece Chicken McNuggets' ), '$2.99' ),
			array( 'Medium Soft Drink', '$1.00' ),
		);
		$sides_rows         = array(
			array( self::build_site_link( 'menu/sides/world-famous-fries-small/', 'Small World Famous Fries' ), '$2.89', '230 cal' ),
			array( self::build_site_link( 'menu/sides/world-famous-fries-medium/', 'Medium World Famous Fries' ), '$3.99', '320 cal' ),
			array( self::build_site_link( 'menu/sides/world-famous-fries-large/', 'Large World Famous Fries' ), '$4.79', '490 cal' ),
			array( self::build_site_link( 'menu/sides/apple-slices/', 'Apple Slices' ), '$1.39', '15 cal' ),
		);
		$dessert_rows       = array(
			array( self::build_site_link( 'menu/sweets/vanilla-cone/', 'Vanilla Cone' ), '$1.00', '200 cal' ),
			array( self::build_site_link( 'menu/sweets/baked-apple-pie/', 'Baked Apple Pie' ), '$1.89', '230 cal' ),
			array( self::build_site_link( 'menu/sweets/chocolate-chip-cookie/', 'Chocolate Chip Cookie' ), '$1.79', '170 cal' ),
			array( self::build_site_link( 'menu/sweets/hot-fudge-sundae/', 'Hot Fudge Sundae' ), '$3.65', '330 cal' ),
			array( self::build_site_link( 'menu/sweets/hot-caramel-sundae/', 'Hot Caramel Sundae' ), '$3.65', '331 cal' ),
			array( self::build_site_link( 'menu/sweets/oreo-mcflurry/', 'Oreo McFlurry' ), '$3.99', '510 cal' ),
			array( self::build_site_link( 'menu/sweets/mms-mcflurry/', 'M&amp;M&#8217;s McFlurry' ), '$3.99', '630 cal' ),
			array( self::build_site_link( 'menu/sweets/chocolate-shake-medium/', 'Chocolate Shake (Medium)' ), '$4.29', '680 cal' ),
			array( self::build_site_link( 'menu/sweets/strawberry-shake-medium/', 'Strawberry Shake (Medium)' ), '$4.29', '580 cal' ),
			array( self::build_site_link( 'menu/sweets/vanilla-shake-medium/', 'Vanilla Shake (Medium)' ), '$4.29', '530 cal' ),
		);
		$happy_meal_rows    = array(
			array( self::build_site_link( 'menu/happymeal/hamburger-happy-meal/', 'Hamburger Happy Meal' ), '$4.99', '475 cal' ),
			array( self::build_site_link( 'menu/happymeal/4-pc-mcnuggets-happy-meal/', '4-Piece McNuggets Happy Meal' ), '$5.99', '395 cal' ),
			array( self::build_site_link( 'menu/happymeal/6-pc-mcnuggets-happy-meal/', '6-Piece McNuggets Happy Meal' ), '$6.99', '475 cal' ),
		);
		$combo_rows         = array(
			array( self::build_site_link( 'menu/meals/big-mac-meal/', 'Big Mac Meal' ), '$10.19', 'Big Mac + Medium Fries + Medium Drink' ),
			array( self::build_site_link( 'menu/meals/quarter-pounder-with-cheese-meal/', 'Quarter Pounder with Cheese Meal' ), '$10.69', 'Quarter Pounder with Cheese + Medium Fries + Medium Drink' ),
			array( self::build_site_link( 'menu/meals/double-quarter-pounder-with-cheese-meal/', 'Double Quarter Pounder Meal' ), '$11.49', 'Double Quarter Pounder with Cheese + Medium Fries + Medium Drink' ),
			array( self::build_site_link( 'menu/meals/mccrispy-meal/', 'McCrispy Meal' ), '$8.79', 'McCrispy + Medium Fries + Medium Drink' ),
			array( self::build_site_link( 'menu/meals/deluxe-mccrispy-meal/', 'Deluxe McCrispy Meal' ), '$9.49', 'Deluxe McCrispy + Medium Fries + Medium Drink' ),
			array( self::build_site_link( 'menu/meals/filet-o-fish-meal/', 'Filet-O-Fish Meal' ), '$9.19', 'Filet-O-Fish + Medium Fries + Medium Drink' ),
			array( self::build_site_link( 'menu/meals/10-pc-chicken-mcnuggets-meal/', '10-Piece McNuggets Meal' ), '$10.09', '10-Piece McNuggets + Medium Fries + Medium Drink' ),
			array( self::build_site_link( 'menu/meals/egg-mcmuffin-meal/', 'Egg McMuffin Meal' ), '$8.19', 'Egg McMuffin + Hash Browns + Medium Drink' ),
		);
		$nutrition_rows     = array(
			array( 'Burgers', 'Hamburger', '250', 'Double Quarter Pounder with Cheese', '740' ),
			array( 'Breakfast', 'Fruit &amp; Maple Oatmeal', '320', 'Big Breakfast with Hotcakes', '1,340' ),
			array( 'Chicken', '4-Piece McNuggets', '170', '40-Piece McNuggets', '1,650' ),
			array( 'Sides', 'Apple Slices', '15', 'Large Fries', '490' ),
			array( 'Sweets', 'Chocolate Chip Cookie', '170', 'M&amp;M&#8217;s McFlurry', '630' ),
			array( 'Beverages', 'Unsweetened Iced Tea', '0', 'Large Mango Pineapple Smoothie', '340' ),
		);
		$ordering_rows      = array(
			array( 'McDonald&#8217;s App', 'Exclusive deals, mobile ordering, and reward tracking', 'Up to 50% off select items' ),
			array( 'Drive-Thru', 'Fast pickup and standard in-store pricing', 'Current live offers only' ),
			array( 'In-Store Kiosk', 'Customisation without checkout pressure', 'Same baseline pricing as the counter' ),
			array( 'McDelivery', 'Convenience and large family ordering', 'Usually higher menu prices plus delivery fees' ),
		);
		$quick_nav_rows     = array(
			array( self::build_site_link( 'menu/burgers/', 'Burgers' ), self::build_site_link( 'menu/breakfast/', 'Breakfast' ), self::build_site_link( 'menu/chickenfish/', 'Chicken &amp; Fish' ) ),
			array( self::build_site_link( 'menu/nuggets/', 'McNuggets &amp; Strips' ), self::build_site_link( 'menu/meals/', 'Meals' ), self::build_site_link( 'menu/mcvalue/', 'McValue' ) ),
			array( self::build_site_link( 'menu/deals/', 'Deals' ), self::build_site_link( 'menu/mccafe/', 'McCafe' ), self::build_site_link( 'menu/beverages/', 'Beverages' ) ),
			array( self::build_site_link( 'menu/sides/', 'Sides' ), self::build_site_link( 'menu/happymeal/', 'Happy Meal' ), self::build_site_link( 'menu/sweets/', 'Sweets &amp; Treats' ) ),
			array( self::build_site_link( 'menu/sauces/', 'Sauces' ), self::build_site_link( 'menu/snackwrap/', 'Snack Wraps' ), self::build_site_link( 'menu/whats-new/', 'What&#8217;s New' ) ),
		);
		$faq_entries        = array(
			array(
				'question' => 'What is the cheapest item on the McDonald&#8217;s menu?',
				'answer'   => 'The cheapest items on the McDonald&#8217;s menu are the Vanilla Cone at $1.00 and Apple Slices at $1.39. Among hot food items, the Sausage McMuffin on the ' . self::build_site_link( 'menu/mcvalue/', 'McValue Under $3 Menu' ) . ' is the lowest-priced sandwich during the active promo window.',
			),
			array(
				'question' => 'What time does McDonald&#8217;s stop serving breakfast?',
				'answer'   => 'McDonald&#8217;s breakfast ends at 10:30 AM at most locations. Some 24-hour stores vary slightly on weekends, so the best next check is the ' . self::build_site_link( 'breakfast-hours/', 'McDonald&#8217;s breakfast hours guide' ) . '.',
			),
			array(
				'question' => 'Does McDonald&#8217;s have a dollar menu in ' . esc_html( $current_year ) . '?',
				'answer'   => 'Yes. The current equivalent is the ' . self::build_site_link( 'menu/mcvalue/', 'McValue Under $3 Menu' ) . ', which replaced the old $1 $2 $3 Dollar Menu structure. It includes national low-entry items priced under $3, with some items periodically dropping to $1.50 to $2.50.',
			),
			array(
				'question' => 'How much is a Big Mac in the USA?',
				'answer'   => 'The Big Mac is tracked here at $5.99 nationally, although high-cost markets can push the final total much higher. The fastest next reference is the ' . self::build_site_link( 'big-mac-price-usa/', 'Big Mac Price USA guide' ) . '.',
			),
			array(
				'question' => 'What are the McDonald&#8217;s limited-time items right now?',
				'answer'   => 'Current limited-time highlights include ' . self::build_site_link( 'menu/whats-new/the-big-arch/', 'The Big Arch' ) . ', ' . self::build_site_link( 'menu/whats-new/ramyeon-mcshaker-fries/', 'Ramyeon McShaker Fries' ) . ', and ' . self::build_site_link( 'menu/whats-new/the-saja-boys-breakfast-meal/', 'The Saja Boys Breakfast Meal' ) . '. For the full rotation, open the ' . self::build_site_link( 'limited-time-menu/', 'limited-time menu guide' ) . '.',
			),
			array(
				'question' => 'Does McDonald&#8217;s have allergen information available?',
				'answer'   => 'Yes. Full allergen details are available for wheat, dairy, egg, soy, sesame, and fish-sensitive menu items through the ' . self::build_site_link( 'allergen-guide/', 'McDonald&#8217;s allergen guide' ) . '.',
			),
			array(
				'question' => 'How do I find McDonald&#8217;s prices near me?',
				'answer'   => 'This site tracks current national averages, but exact totals vary by state, city, franchise, and channel. For live local pricing, use the McDonald&#8217;s app after choosing your nearest store.',
			),
		);

		$content  = '<!-- wp:group {"className":"mcprices-page mcprices-menu-root-page"} --><div class="wp-block-group mcprices-page mcprices-menu-root-page">';
		$content .= self::build_html_block(
			'<div class="section-header section-header-left"><div class="section-label">Full Menu Guide</div><h2 class="section-title">McDonald&#8217;s Menu Prices USA ' . esc_html( $current_year ) . ' &ndash; Full Menu with Calories &amp; Current Deals</h2><p class="section-sub">This is the complete McDonald&#8217;s USA menu guide for burgers, breakfast, chicken, McCafe drinks, sides, sweets, Happy Meals, sauces, combo meals, calories, and current McValue offers, all linked to the right category and item pages inside the site.</p></div>'
		);
		$content .= self::build_block_paragraph( 'McDonald&#8217;s is the most visited fast food chain in the United States, serving over 69 million customers daily across more than 13,500 locations. Whether you are planning a drive-thru run, ordering through the app, or checking prices before you head out, knowing what things cost saves time and reduces surprises at the register.' );
		$content .= self::build_block_paragraph( 'This page is your complete guide to the <strong>McDonald&#8217;s menu with prices for ' . esc_html( $current_year ) . '</strong>, updated to reflect the latest changes including the expanded ' . self::build_site_link( 'menu/mcvalue/', 'McValue menu' ) . ', the return of the Snack Wrap, and current limited-time items. Every major branch is covered here: burgers, breakfast, chicken, McCafe, sides, sweets, deals, and more.' );
		$content .= self::build_block_paragraph( '<strong>Key fact:</strong> McDonald&#8217;s prices vary by location, franchise, and state. The prices listed throughout this site reflect current national averages. Always verify your exact local total in the McDonald&#8217;s app before ordering.' );

		$content .= self::build_block_heading( 'What&#8217;s New on the McDonald&#8217;s Menu in ' . $current_year, 2 );
		$content .= self::build_block_paragraph( 'McDonald&#8217;s made significant changes to its value lineup in early ' . esc_html( $current_year ) . '. During the current menu update cycle, the chain expanded the ' . self::build_site_link( 'menu/mcvalue/', 'McValue menu' ) . ' with new low-entry picks, stronger breakfast value, and more visible cross-category savings.' );
		$content .= self::build_block_list(
			array(
				'<strong>Under $3 Menu:</strong> At least 10 items available throughout the day, including the Sausage McMuffin, McDouble, 4-Piece Chicken McNuggets, small Fries, and a medium Soft Drink.',
				'<strong>$4 Breakfast Meal Deal:</strong> A Sausage McMuffin or Sausage Biscuit, Hash Browns, and a small McCafe Premium Roast Coffee for $4.',
				'<strong>Snack Wrap returns:</strong> Both the ' . self::build_site_link( 'menu/snackwrap/ranch-snack-wrap/', 'Ranch Snack Wrap' ) . ' and ' . self::build_site_link( 'menu/snackwrap/spicy-snack-wrap/', 'Spicy Snack Wrap' ) . ' are back on the menu, starting at $2.99.',
				'<strong>Premium burger launch:</strong> ' . self::build_site_link( 'menu/whats-new/the-big-arch/', 'The Big Arch' ) . ' joins the line-up as a premium double-patty burger built for higher-intent burger orders.',
			)
		);
		$content .= self::build_block_paragraph( 'For the full rundown of fresh releases and seasonal items, visit the ' . self::build_site_link( 'menu/whats-new/', 'What&#8217;s New menu' ) . '.' );

		$content .= self::build_block_heading( 'McDonald&#8217;s Menu Categories &ndash; Browse by Section', 2 );
		$content .= self::build_block_paragraph( 'The full McDonald&#8217;s USA menu spans 15 core categories. Use the table below to jump directly to the section you need, with a quick overview of what each contains and the typical starting price you can expect.' );
		$content .= self::build_table_block(
			array( 'Category', 'What&#8217;s Inside', 'Starting Price' ),
			$category_rows
		);
		$content .= self::build_block_paragraph( '<strong>Not sure where to start?</strong> The most searched items on the McDonald&#8217;s menu are the ' . self::build_site_link( 'menu/burgers/big-mac/', 'Big Mac' ) . ' ($5.99), the ' . self::build_site_link( 'menu/breakfast/egg-mcmuffin/', 'Egg McMuffin' ) . ' ($4.29), and the ' . self::build_site_link( 'menu/nuggets/10-pc-chicken-mcnuggets/', '10-Piece Chicken McNuggets' ) . ' ($5.79). For the best everyday value, the ' . self::build_site_link( 'menu/mcvalue/', 'McValue menu' ) . ' is still the strongest first stop.' );

		$content .= self::build_block_heading( 'McDonald&#8217;s Burgers Menu Prices', 2 );
		$content .= self::build_block_paragraph( 'The ' . self::build_site_link( 'menu/burgers/', 'McDonald&#8217;s burgers menu' ) . ' is the core of the line-up, anchored by the Big Mac and the Quarter Pounder family. Prices range from $2.49 for the Hamburger up to $8.99 for the Bacon Quarter Pounder with Cheese, with value-oriented doubles sitting between the cheapest singles and the signature premium burgers.' );
		$content .= self::build_block_heading( 'Most Popular Burgers and Prices', 3 );
		$content .= self::build_table_block(
			array( 'Burger', 'Price', 'Calories' ),
			$burger_rows
		);
		$content .= self::build_block_paragraph( '<strong>Best value pick:</strong> The ' . self::build_site_link( 'menu/burgers/mcdouble/', 'McDouble' ) . ' gives you two beef patties, American cheese, pickles, onions, ketchup, and mustard for $4.59. It is also available inside the ' . self::build_site_link( 'menu/deals/mcdouble-meal-deal/', 'McDouble Meal Deal' ) . ', which is one of the most efficient ways to turn a burger order into a full meal.' );
		$content .= self::build_block_paragraph( 'For a full burger-by-burger comparison including meal upgrades and calorie ranges, open the ' . self::build_site_link( 'burgers-menu/', 'Burgers Menu Prices USA guide' ) . '.' );

		$content .= self::build_block_heading( 'McDonald&#8217;s Breakfast Menu Prices', 2 );
		$content .= self::build_block_paragraph( 'McDonald&#8217;s serves breakfast every day during ' . self::build_site_link( 'breakfast-hours/', 'morning hours' ) . ', with the menu available from opening until 10:30 AM at most locations. The ' . self::build_site_link( 'menu/breakfast/', 'breakfast menu' ) . ' covers everything from quick handheld sandwiches to heavier plate meals.' );
		$content .= self::build_block_heading( 'Breakfast Sandwiches and Combos', 3 );
		$content .= self::build_table_block(
			array( 'Item', 'Price', 'Calories' ),
			$breakfast_rows
		);
		$content .= self::build_block_heading( 'Breakfast Sides and Light Options', 3 );
		$content .= self::build_block_list(
			array(
				self::build_site_link( 'menu/breakfast/hash-browns/', 'Hash Browns' ) . ' &ndash; $2.69 | 140 cal',
				self::build_site_link( 'menu/breakfast/hotcakes/', 'Hotcakes' ) . ' &ndash; $5.69 | 580 cal',
				self::build_site_link( 'menu/breakfast/hotcakes-and-sausage/', 'Hotcakes and Sausage' ) . ' &ndash; $5.89 | 770 cal',
				self::build_site_link( 'menu/breakfast/fruit-maple-oatmeal/', 'Fruit &amp; Maple Oatmeal' ) . ' &ndash; $1.99 | 320 cal',
			)
		);
		$content .= self::build_block_paragraph( '<strong>The $4 Breakfast Meal Deal</strong> combines a Sausage McMuffin or Sausage Biscuit, Hash Browns, and a small McCafe Premium Roast Coffee. It is one of the best per-item value bundles on the entire breakfast menu.' );
		$content .= self::build_block_paragraph( 'For full breakfast pricing including combo upgrades and nutrition context, see the ' . self::build_site_link( 'breakfast-menu/', 'McDonald&#8217;s breakfast menu guide' ) . '.' );

		$content .= self::build_block_heading( 'McDonald&#8217;s Chicken, Fish &amp; McNuggets Prices', 2 );
		$content .= self::build_block_paragraph( 'Chicken and fish options make up one of the most diverse parts of the McDonald&#8217;s menu, covering crispy sandwiches, classic nuggets, fish sandwiches, and the returning Snack Wrap format. The ' . self::build_site_link( 'menu/chickenfish/', 'Chicken &amp; Fish menu' ) . ' and ' . self::build_site_link( 'menu/nuggets/', 'Nuggets &amp; Strips menu' ) . ' together cover nearly every chicken-led ordering path.' );
		$content .= self::build_block_heading( 'Chicken Sandwiches', 3 );
		$content .= self::build_table_block(
			array( 'Item', 'Price', 'Calories' ),
			$chicken_rows
		);
		$content .= self::build_block_heading( 'Chicken McNuggets Prices', 3 );
		$content .= self::build_table_block(
			array( 'Size', 'Price', 'Calories' ),
			$nugget_rows
		);
		$content .= self::build_block_heading( 'Snack Wraps Back in ' . $current_year, 3 );
		$content .= self::build_block_list(
			array(
				self::build_site_link( 'menu/snackwrap/ranch-snack-wrap/', 'Ranch Snack Wrap' ) . ' &ndash; $2.99 | 390 cal',
				self::build_site_link( 'menu/snackwrap/spicy-snack-wrap/', 'Spicy Snack Wrap' ) . ' &ndash; $2.99 | 400 cal',
			)
		);
		$content .= self::build_block_paragraph( 'For the complete chicken and fish picture, move next to the ' . self::build_site_link( 'chicken-fish-menu/', 'Chicken &amp; Fish Menu Prices USA guide' ) . ' and the ' . self::build_site_link( 'nuggets-and-strips/', 'McNuggets &amp; Strips guide' ) . '.' );

		$content .= self::build_block_heading( 'McDonald&#8217;s McCafe Menu Prices', 2 );
		$content .= self::build_block_paragraph( 'McCafe is McDonald&#8217;s in-house coffee and specialty drink platform, covering espresso drinks, iced coffees, frappes, and hot beverages at prices that consistently undercut most traditional coffee chains. The ' . self::build_site_link( 'menu/mccafe/', 'full McCafe menu' ) . ' spans hot, iced, and frozen drink types.' );
		$content .= self::build_block_heading( 'Hot Coffee and Espresso Drinks', 3 );
		$content .= self::build_table_block(
			array( 'Item', 'Small', 'Medium', 'Large' ),
			$mccafe_hot_rows
		);
		$content .= self::build_block_heading( 'Iced and Frozen Drinks', 3 );
		$content .= self::build_table_block(
			array( 'Item', 'Small', 'Medium', 'Large' ),
			$mccafe_iced_rows
		);
		$content .= self::build_block_paragraph( '<strong>Worth knowing:</strong> A medium McCafe Premium Roast Coffee is included in the $4 Breakfast Meal Deal. It is also one of the lowest-entry hot drink options on the menu. For full size and flavor combinations, visit the ' . self::build_site_link( 'mccafe-menu/', 'McCafe menu page' ) . '.' );

		$content .= self::build_block_heading( 'McDonald&#8217;s McValue Menu and Current Deals', 2 );
		$content .= self::build_block_paragraph( 'Value is the biggest story on the McDonald&#8217;s menu in ' . esc_html( $current_year ) . '. The expanded ' . self::build_site_link( 'menu/mcvalue/', 'McValue menu' ) . ' now operates in three distinct tiers, giving customers more flexibility than the old one-size-fits-all value approach. This is also where many of the strongest current deals are concentrated.' );
		$content .= self::build_block_heading( 'Tier 1: Under $3 Menu', 3 );
		$content .= self::build_table_block(
			array( 'Item', 'Price' ),
			$mcvalue_rows
		);
		$content .= self::build_block_heading( 'Tier 2: Meal Deals', 3 );
		$content .= self::build_block_list(
			array(
				self::build_site_link( 'menu/deals/mcchicken-meal-deal/', 'McChicken Meal Deal' ) . ' &ndash; $5',
				self::build_site_link( 'menu/deals/mcdouble-meal-deal/', 'McDouble Meal Deal' ) . ' &ndash; $6',
				self::build_site_link( 'menu/deals/daily-double-meal-deal/', 'Daily Double Meal Deal' ) . ' &ndash; $6',
			)
		);
		$content .= self::build_block_heading( 'Tier 3: Buy One, Add One for $1', 3 );
		$content .= self::build_block_paragraph( 'Buy one qualifying item at full price and add a second for just $1. That offer appears across both ' . self::build_site_link( 'menu/deals/breakfast-buy-1-add-1-for-1/', 'breakfast deals' ) . ' and ' . self::build_site_link( 'menu/deals/lunch-buy-1-add-1-for-1/', 'lunch and dinner deals' ) . '.' );
		$content .= self::build_block_paragraph( '<strong>The real value calculation:</strong> The McDouble Meal Deal at $6 includes a McDouble, 4-Piece McNuggets, small Fries, and a small Soft Drink. Buying those items separately would cost far more, which is why this bundle consistently ranks as one of the best-value national offers.' );
		$content .= self::build_block_paragraph( 'For app-only offers and loyalty savings, open the ' . self::build_site_link( 'mcdonalds-app-deals/', 'McDonald&#8217;s App Deals guide' ) . ', the ' . self::build_site_link( 'rewards-guide/', 'Rewards guide' ) . ', and the full ' . self::build_site_link( 'mcdonalds-deals-mcvalue-guide/', 'McDonald&#8217;s Deals &amp; McValue guide' ) . '.' );

		$content .= self::build_block_heading( 'McDonald&#8217;s Sides, Beverages &amp; Sweets Prices', 2 );
		$content .= self::build_block_heading( 'Sides', 3 );
		$content .= self::build_table_block(
			array( 'Item', 'Price', 'Calories' ),
			$sides_rows
		);
		$content .= self::build_block_paragraph( 'For a complete breakdown of fries pricing by size, see the ' . self::build_site_link( 'fries-sides/', 'Fries &amp; Sides guide' ) . '.' );
		$content .= self::build_block_heading( 'Beverages', 3 );
		$content .= self::build_block_list(
			array(
				'Soft Drinks (small, medium, large): $1.00 / $1.29 / $1.49',
				self::build_site_link( 'menu/beverages/sweet-tea-small/', 'Sweet Tea' ) . ' &ndash; $1.00 to $1.49 depending on size',
				self::build_site_link( 'menu/beverages/mango-pineapple-smoothie-small/', 'Mango Pineapple Smoothie' ) . ' (small) &ndash; $3.99',
				self::build_site_link( 'menu/beverages/strawberry-banana-smoothie-small/', 'Strawberry Banana Smoothie' ) . ' (small) &ndash; $3.99',
				self::build_site_link( 'menu/beverages/lemonade-small/', 'Lemonade' ) . ' (small) &ndash; $2.49',
			)
		);
		$content .= self::build_block_paragraph( 'For the complete drinks range including milk, juice, and frozen beverages, open the ' . self::build_site_link( 'beverage-menu/', 'full beverage menu' ) . '.' );
		$content .= self::build_block_heading( 'Sweets and Desserts', 3 );
		$content .= self::build_table_block(
			array( 'Item', 'Price', 'Calories' ),
			$dessert_rows
		);
		$content .= self::build_block_paragraph( '<strong>Best cheap dessert:</strong> The ' . self::build_site_link( 'menu/sweets/vanilla-cone/', 'Vanilla Cone' ) . ' at $1.00 is the lowest-priced dessert on the menu. For a full sweets reference, see the ' . self::build_site_link( 'sweets-treats/', 'Sweets &amp; Treats guide' ) . '.' );

		$content .= self::build_block_heading( 'McDonald&#8217;s Happy Meal Prices', 2 );
		$content .= self::build_block_paragraph( 'The ' . self::build_site_link( 'menu/happymeal/', 'McDonald&#8217;s Happy Meal' ) . ' is built for kids and includes a main item, fries or Apple Slices, a drink, and a toy. Three core options are currently available.' );
		$content .= self::build_table_block(
			array( 'Happy Meal Option', 'Price', 'Calories (approx.)' ),
			$happy_meal_rows
		);
		$content .= self::build_block_paragraph( '<strong>Nutrition note:</strong> Swapping fries for Apple Slices and milk for a soft drink can reduce the calorie count significantly. For allergen checks across all Happy Meal components, use the ' . self::build_site_link( 'allergen-guide/', 'allergen guide' ) . '. For the full kid-meal breakdown, see the ' . self::build_site_link( 'happy-meal-menu/', 'Happy Meal menu page' ) . '.' );

		$content .= self::build_block_heading( 'McDonald&#8217;s Sauces and Condiments', 2 );
		$content .= self::build_block_paragraph( 'McDonald&#8217;s offers a wide range of dipping sauces and condiment packets, most priced between $0.25 and $0.50 when purchased separately. McNuggets orders usually include sauces at no extra charge, but extra dips and packets can still shift the total on larger nugget and strip orders.' );
		$content .= self::build_block_heading( 'Available Dipping Sauces', 3 );
		$content .= self::build_block_list(
			array(
				self::build_site_link( 'menu/sauces/sweet-n-sour-sauce/', 'Sweet N Sour Sauce' ) . ' &ndash; $0.25',
				self::build_site_link( 'menu/sauces/tangy-barbecue-sauce/', 'Tangy Barbecue Sauce' ) . ' &ndash; $0.25',
				self::build_site_link( 'menu/sauces/honey-mustard-sauce/', 'Honey Mustard Sauce' ) . ' &ndash; $0.25',
				self::build_site_link( 'menu/sauces/creamy-ranch-sauce/', 'Creamy Ranch Sauce' ) . ' &ndash; $0.25',
				self::build_site_link( 'menu/sauces/spicy-buffalo-sauce/', 'Spicy Buffalo Sauce' ) . ' &ndash; $0.25',
				self::build_site_link( 'menu/sauces/honey/', 'Honey' ) . ' &ndash; $0.25',
				self::build_site_link( 'menu/sauces/creamy-chili-mccrispy-strip-dip/', 'Creamy Chili McCrispy Strip Dip' ) . ' &ndash; $0.40',
				self::build_site_link( 'menu/sauces/hunter-sauce/', 'Hunter Sauce' ) . ' &ndash; $0.40',
				self::build_site_link( 'menu/sauces/demon-sauce/', 'Demon Sauce' ) . ' &ndash; $0.40',
			)
		);
		$content .= self::build_block_heading( 'Condiment Packets', 3 );
		$content .= self::build_block_list(
			array(
				self::build_site_link( 'menu/sauces/ketchup-packet/', 'Ketchup Packet' ) . ' &ndash; Free',
				self::build_site_link( 'menu/sauces/mustard-packet/', 'Mustard Packet' ) . ' &ndash; Free',
				self::build_site_link( 'menu/sauces/mayonnaise-packet/', 'Mayonnaise Packet' ) . ' &ndash; $0.25',
			)
		);
		$content .= self::build_block_paragraph( 'For ingredients, calories, and per-sauce detail, go to the ' . self::build_site_link( 'sauces-condiments/', 'Sauces &amp; Condiments page' ) . '.' );

		$content .= self::build_block_heading( 'McDonald&#8217;s Extra Value Meals and Combo Pricing', 2 );
		$content .= self::build_block_paragraph( 'Combo meals bundle a main item with medium fries and a medium drink, usually saving money compared with ordering every part separately. The ' . self::build_site_link( 'extra-value-meals/', 'Extra Value Meals guide' ) . ' covers the broader combo landscape, while the live ' . self::build_site_link( 'menu/meals/', 'Meals category' ) . ' lists every meal page currently available.' );
		$content .= self::build_table_block(
			array( 'Meal', 'Price', 'What&#8217;s Included' ),
			$combo_rows
		);
		$content .= self::build_block_paragraph( 'All combo meals can be upgraded from medium to large for roughly another dollar. For larger group orders, the ' . self::build_site_link( 'shareables-bundles/', 'Shareables &amp; Bundles page' ) . ' is the best next stop.' );

		$content .= self::build_block_heading( 'McDonald&#8217;s Nutrition, Allergens &amp; Calories', 2 );
		$content .= self::build_block_paragraph( 'McDonald&#8217;s publishes nutritional information for every menu item, including calories, fat, sodium, protein, and carbohydrates. For anyone managing dietary needs, the nutrition picture matters just as much as the menu price.' );
		$content .= self::build_block_heading( 'Quick Calorie Reference by Category', 3 );
		$content .= self::build_table_block(
			array( 'Category', 'Lowest Calorie Option', 'Calories', 'Highest Calorie Option', 'Calories' ),
			$nutrition_rows
		);
		$content .= self::build_block_heading( 'Allergy and Dietary Resources', 3 );
		$content .= self::build_block_list(
			array(
				'<strong>Full allergen information by item:</strong> ' . self::build_site_link( 'allergen-guide/', 'McDonald&#8217;s Allergen Guide' ),
				'<strong>Calorie tracking tool:</strong> ' . self::build_site_link( 'calorie-counter/', 'McDonald&#8217;s Calorie Counter' ),
				'<strong>Price movement context:</strong> ' . self::build_site_link( 'price-history/', 'McDonald&#8217;s Price History' ),
			)
		);
		$content .= self::build_block_paragraph( '<strong>Key dietary note:</strong> The ' . self::build_site_link( 'menu/breakfast/fruit-maple-oatmeal/', 'Fruit &amp; Maple Oatmeal' ) . ' is the lowest-calorie hot food on the menu, while ' . self::build_site_link( 'menu/beverages/unsweetened-iced-tea-any-size/', 'Unsweetened Iced Tea' ) . ' is the zero-calorie drink option most readers check first.' );

		$content .= self::build_block_heading( 'How to Order McDonald&#8217;s and Save Money in ' . $current_year, 2 );
		$content .= self::build_block_paragraph( 'McDonald&#8217;s has more ways to order than ever, and the channel you choose has a direct impact on what you pay. Delivery convenience, app promotions, and in-store combo behaviour can all change the final order total.' );
		$content .= self::build_block_heading( 'Ordering Channels Compared', 3 );
		$content .= self::build_table_block(
			array( 'Method', 'Best For', 'Potential Savings' ),
			$ordering_rows
		);
		$content .= self::build_block_paragraph( '<strong>The app is the single biggest lever for saving money at McDonald&#8217;s.</strong> App-exclusive deals regularly include free items with purchase, discounted meals, and rotating bonus offers. The ' . self::build_site_link( 'mcdonalds-app-deals/', 'McDonald&#8217;s App Deals guide' ) . ' covers the strongest current examples.' );
		$content .= self::build_block_paragraph( 'MyMcDonald&#8217;s Rewards earns 100 points per $1 spent, with free-item redemptions beginning at low thresholds. The ' . self::build_site_link( 'rewards-guide/', 'Rewards guide' ) . ' explains where the best redemption value usually sits.' );
		$content .= self::build_block_paragraph( 'Ordering through delivery apps usually adds fees and often carries higher menu pricing. The ' . self::build_site_link( 'delivery-guide/', 'Delivery guide' ) . ' breaks down that pricing difference in more detail.' );

		$content .= self::build_block_heading( 'Frequently Asked Questions About the McDonald&#8217;s Menu', 2 );
		foreach ( $faq_entries as $faq_entry ) {
			$content .= self::build_block_heading( $faq_entry['question'], 3 );
			$content .= self::build_block_paragraph( $faq_entry['answer'] );
		}

		$content .= self::build_block_heading( 'About This McDonald&#8217;s Menu Price Guide', 2 );
		$content .= self::build_block_paragraph( 'This guide is maintained as a regularly updated reference for McDonald&#8217;s USA menu prices, calories, and deals. Prices are sourced from current national-average menu data and updated when McDonald&#8217;s announces menu or pricing changes. Because McDonald&#8217;s operates on a franchise model across thousands of US locations, individual restaurant prices may differ from the figures shown here.' );
		$content .= self::build_block_paragraph( 'Last updated: <strong>' . esc_html( $updated_date ) . '</strong>.' );
		$content .= self::build_block_heading( 'Quick Navigation &ndash; All Menu Categories', 3 );
		$content .= self::build_table_block(
			array( 'Column 1', 'Column 2', 'Column 3' ),
			$quick_nav_rows
		);
		$content .= self::build_block_heading( 'Related Guides on This Site', 3 );
		$content .= self::build_block_list( self::build_related_link_items( $links ) );
		$content .= self::build_block_paragraph( '<em>Prices shown are national averages and may vary by location. Last updated: ' . esc_html( $updated_date ) . '.</em>' );

		$content .= '</div><!-- /wp:group -->';
		$content .= self::build_block_separator();
		$content .= self::build_block_shortcode( '[mcprices_menu_directory]' );

		return $content;
	}

	/**
	 * Legacy root content builder kept temporarily for rollback safety.
	 *
	 * @return string
	 */
	protected static function get_root_content_legacy() {
		$menu_url    = home_url( '/menu/' );
		$links       = array(
			array( 'label' => 'Breakfast Menu Prices USA', 'url' => home_url( '/breakfast-menu/' ), 'description' => 'Use the breakfast pillar for McMuffins, biscuits, McGriddles, Hash Browns, and breakfast combo decisions.' ),
			array( 'label' => 'Burgers Menu Prices USA', 'url' => home_url( '/burgers-menu/' ), 'description' => 'Compare Big Mac, Quarter Pounder, McDouble, Hamburger, meal pricing, and premium-versus-value burger choices.' ),
			array( 'label' => 'Chicken & Fish Menu Prices USA', 'url' => home_url( '/chicken-fish-menu/' ), 'description' => 'Check McCrispy, McChicken, Filet-O-Fish, spicy builds, and sandwich-meal context.' ),
			array( 'label' => 'McNuggets & Strips Menu Prices USA', 'url' => home_url( '/nuggets-and-strips/' ), 'description' => 'Compare nugget piece counts, McCrispy Strips, share boxes, and sauce-driven ordering choices.' ),
			array( 'label' => 'Fries & Sides Prices USA', 'url' => home_url( '/fries-sides/' ), 'description' => 'See how fries sizes and side add-ons change the final total of a typical order.' ),
			array( 'label' => 'McDonald\'s Deals & McValue Guide USA', 'url' => home_url( '/mcdonalds-deals-mcvalue-guide/' ), 'description' => 'Open the value pillar for current meal deals, McValue offers, app-led savings, and bundle logic.' ),
			array( 'label' => 'McDonald\'s Nutrition, Calories & Allergens Guide', 'url' => home_url( '/mcdonalds-nutrition-calories-allergens/' ), 'description' => 'Use the broader nutrition pillar when the menu decision depends on calories, ingredients, or allergen checks.' ),
			array( 'label' => 'McDonald\'s Prices by State', 'url' => home_url( '/mcdonalds-prices-by-state/' ), 'description' => 'Understand why national menu prices and local checkout totals can differ from market to market.' ),
		);

		$content  = '';
		$content .= self::build_block_paragraph( 'This McDonald&#8217;s Full Menu USA page is the main directory-style pillar for readers who want the whole menu picture before committing to one category, one deal path, or one exact item page. It works as the broadest navigational guide on the site: a place to understand how breakfast, burgers, chicken, fries, drinks, desserts, sauces, Happy Meals, and current value offers fit together inside the same national menu system.' );
		$content .= self::build_block_paragraph( 'A strong full-menu page should do more than repeat category names. It should show how people actually use the menu when they are deciding what to order. Some readers start with a category in mind. Others start with a budget. Others want calories, family ordering help, or a quick answer on whether breakfast is still available. This page is built to support those real search paths while still keeping the design native to the existing WordPress setup.' );
		$content .= self::build_block_paragraph( 'Every price on this site is shown in dollars and is designed for comparison and planning. The final total can still move because of location, franchise pricing, taxes, app participation, delivery fees, and limited-time offers. That is why this full-menu pillar focuses on helping readers understand the structure of the menu first, then move into the exact page that matches the order they are really trying to compare.' );

		$content .= self::build_block_heading( 'What this full menu guide covers', 2 );
		$content .= self::build_block_paragraph( 'The full-menu view is useful because McDonald&#8217;s USA does not behave like one flat list of products. It behaves like several menu systems connected together: a breakfast system with timing pressure, a burgers system with strong price ladders, a chicken system with meal and sauce choices, a value system shaped by deals and app behavior, and a dessert-and-drinks system that can reshape the final ticket even after the main food choice is already made.' );
		$content .= self::build_block_paragraph( 'That means the fastest way to use a menu-price site is usually to start at the full-menu level, identify the real decision branch, and then move to the focused pillar or item page that answers it. This page is designed to be that first step.' );
		$content .= self::build_block_list(
			array(
				'<strong>Breakfast:</strong> McMuffins, biscuits, McGriddles, bagels, oatmeal, hotcakes, Hash Browns, and breakfast meal pricing.',
				'<strong>Burgers:</strong> Big Mac, Quarter Pounder builds, McDouble, Daily Double, cheeseburgers, hamburgers, and premium burger releases.',
				'<strong>Chicken &amp; Fish:</strong> McCrispy variants, McChicken, Filet-O-Fish, and sandwich-meal comparisons.',
				'<strong>McNuggets &amp; Strips:</strong> Portion sizes, share boxes, dipping logic, and family-order comparison.',
				'<strong>Fries &amp; Sides:</strong> Small, medium, and large fries plus lighter side add-ons and meal-upgrade context.',
				'<strong>Happy Meal:</strong> Kid-focused meal bundles, nugget and burger options, and family-order relevance.',
				'<strong>Desserts:</strong> McFlurries, sundaes, cones, shakes, pie, cookies, and premium-versus-value sweet options.',
				'<strong>McCafe and drinks:</strong> Coffee, espresso drinks, iced coffee, soda, tea, lemonade, juice, smoothies, and frozen beverages.',
				'<strong>Sauces &amp; condiments:</strong> Dipping sauces and packets that affect nuggets, fries, strips, wraps, and final spend.',
				'<strong>Deals &amp; McValue:</strong> Meal deals, buy-one-add-one offers, low-entry add-ons, and app-driven savings behavior.',
			)
		);

		$content .= self::build_block_heading( 'How to use the full menu before you order', 2 );
		$content .= self::build_block_paragraph( 'Readers usually come to a full-menu page for one of five reasons. They may want the current national menu structure. They may want the fastest route to a category such as breakfast or burgers. They may want the cheapest realistic order. They may want to compare calories across categories. Or they may want to understand how much local pricing can vary before they rely on one headline number. A useful full-menu pillar has to support all five without becoming unreadable.' );
		$content .= self::build_block_paragraph( 'The easiest workflow is simple. Start here to choose the right comparison branch. If the main decision is food type, move to the relevant category pillar. If the main decision is savings, move to the deals and McValue guide. If the main decision is calories or allergen sensitivity, move to the nutrition guide. If the main concern is local price movement, move to the prices-by-state pillar. Then return to the live category or item page when you need the exact menu listing.' );
		$content .= self::build_block_paragraph( 'This process is especially helpful because McDonald&#8217;s ordering decisions are rarely single-variable decisions. The customer who thinks they are only looking up a burger price often also ends up comparing fries, drinks, desserts, and meal deals. The full-menu page keeps that wider picture visible instead of forcing every search into a narrow silo too early.' );

		$content .= self::build_block_heading( 'Where McDonald\'s menu prices change fastest', 2 );
		$content .= self::build_block_paragraph( 'The menu areas that usually create the biggest ticket swings are signature burgers, combo meals, breakfast meals, fries and drink upgrades, premium desserts, and app-led bundle offers. That is why a menu overview matters so much for price-focused readers: the main entrée is only one part of the real total. The side strategy, drink size, dessert choice, and local app participation can easily reshape the order.' );
		$content .= self::build_block_paragraph( 'The full-menu view also helps readers see the difference between a low-entry order and a complete meal. A cheap standalone item can stop looking cheap once fries, a drink, and a dessert are added. At the same time, a meal deal can sometimes beat an a la carte order only when it matches the exact appetite and category path the customer already wanted.' );
		$content .= self::build_block_list(
			array(
				'Breakfast changes quickly because timing and combo upgrades matter as much as the sandwich price itself.',
				'Burgers have a strong price ladder, from lower-entry options to premium signature builds and limited-time releases.',
				'Chicken, nuggets, and strips become more complex once sauces, share sizes, and meal builds are part of the comparison.',
				'Fries and drinks are small on their own but often create the biggest hidden jump inside a combo total.',
				'Desserts and sweet drinks can turn a moderate order into a much heavier spend and calorie total in one extra step.',
			)
		);

		$content .= self::build_block_heading( 'Breakfast, burgers, drinks, desserts, and value: the most common reader paths', 2 );
		$content .= self::build_block_paragraph( 'Breakfast remains one of the highest-intent branches on a McDonald&#8217;s menu site because readers are usually under time pressure. They want to know if breakfast is still available, what the current morning lineup includes, and whether a sandwich-only breakfast or a full combo is the better value. That makes the breakfast pillar one of the strongest support pages for readers who arrive here first.' );
		$content .= self::build_block_paragraph( 'Burgers are the clearest price-comparison branch on the menu because they span cheap single sandwiches, value doubles, flagship burgers, quarter-pound builds, and limited-time premium launches. For many readers, a good burger pillar answers the main value question better than a generic deals page, because the real comparison is between burger tiers rather than between unrelated menu items.' );
		$content .= self::build_block_paragraph( 'Drinks and desserts matter because they are often researched late in the process but still change the final order meaningfully. Readers want to know if a McFlurry is worth the jump over a cone, whether a McCafe drink beats a fountain drink for value, and how much a large fries and drink upgrade adds once the main food choice is already fixed. The full-menu pillar keeps those secondary-but-important decisions in the same planning flow.' );

		$content .= self::build_block_heading( 'Why local variation still matters on a national menu guide', 2 );
		$content .= self::build_block_paragraph( 'A national menu page is useful because it gives readers a stable comparison framework. But it is only half of the story. McDonald&#8217;s USA prices can change by state, city, store operator, tax environment, delivery channel, and promotion participation. That is why a trustworthy menu site should be transparent about what national menu data can do and what it cannot do.' );
		$content .= self::build_block_paragraph( 'This page helps with the national structure. The prices-by-state pillar helps with the regional lens. Together they give readers a more honest picture than either one could provide alone. That combined approach is especially useful for app users, travelers, and price-sensitive customers who notice that a familiar order can look different once the market changes.' );

		$content .= self::build_block_heading( 'How readers actually use a full McDonald\'s menu page', 2 );
		$content .= self::build_block_paragraph( 'A full-menu pillar is not just a directory for search engines. It is a real planning page for readers who have not yet decided whether they want breakfast, burgers, chicken, fries, desserts, drinks, or a deal-led order. Some readers arrive because they want the whole menu in one place. Others arrive because they are trying to work out the cheapest satisfying order, the most filling category, or the fastest path to the item page that actually matters.' );
		$content .= self::build_block_paragraph( 'That means this page has to support several user journeys at once. One reader may want a quick route to the breakfast menu before service ends. Another may be comparing Big Mac with a McValue option. Another may want to know whether the dessert and drink add-ons make the order much more expensive than expected. The best version of a full-menu page makes those paths easy to follow without forcing every reader into the same sequence.' );
		$content .= self::build_block_paragraph( 'This broader usefulness is also what helps the page perform well for topical SEO and AI retrieval. A search engine or assistant can pull this page not only for a generic menu query, but also for broader planning questions about value, calories, regional pricing, category structure, and the difference between a quick snack order and a full meal.' );

		$content .= self::build_block_heading( 'What makes a trustworthy menu guide more useful than a thin category list', 2 );
		$content .= self::build_block_paragraph( 'Trust on a menu-price site comes from being clear about scope. Readers need a page that shows the current tracked menu structure, explains how categories fit together, and links naturally into the exact item pages that answer narrower questions. At the same time, the page should be honest about what still needs official confirmation, such as allergen-sensitive decisions, live app-only deals, delivery markups, and exact local totals.' );
		$content .= self::build_block_paragraph( 'That balance is important for EEAT as well. A useful page does not pretend that one unofficial source can replace every official transaction detail. Instead, it helps readers do the planning work first: choose the right category, compare realistic order paths, understand where price jumps usually happen, and then move into official McDonald\'s tools when the final confirmation matters.' );
		$content .= self::build_block_paragraph( 'For SEO, that same trust layer also makes the page more useful across a wider range of search intent. Someone searching for menu prices, someone searching for the full menu with calories, and someone searching for the best route to deals or breakfast can all use the same page as a starting point because it explains the menu structure rather than only listing isolated product names.' );
		$content .= self::build_block_paragraph( 'It also improves the page for returning visitors. Readers who used the site once for burgers may come back later for breakfast, desserts, or regional pricing. A strong full-menu page helps them re-enter the topic quickly because it keeps the whole McDonald\'s USA menu system visible in one place and then routes them into the exact guide they need next.' );
		$content .= self::build_block_paragraph( 'That makes the page valuable as both a first-stop resource and a repeat-use navigation hub. Instead of acting like a one-time landing page, it becomes the place readers can return to whenever they need to switch from one menu branch to another without losing the broader price-and-planning context.' );
		$content .= self::build_block_paragraph( 'In practical terms, that means the page should keep working even when the reader changes intent halfway through the visit. A person who starts with breakfast, then wonders about deals, then ends up checking nutrition or state pricing should still be able to move cleanly through the site from this one full-menu hub.' );
		$content .= self::build_block_paragraph( 'That flexibility is one of the biggest reasons a full-menu pillar earns repeat traffic over time: it continues to help even after the first query changes into a different menu question.' );
		$content .= self::build_block_list(
			array(
				'Use the full-menu page when you still need the broad menu map before opening one exact category or item page.',
				'Use the category pillars when the question has narrowed to breakfast, burgers, nuggets, drinks, desserts, or another major menu branch.',
				'Use the deals, nutrition, and prices-by-state guides when the real decision is about savings, calories, ingredients, or regional variation rather than one single food item.',
				'Use official McDonald\'s channels for final live availability, allergen verification, and the exact checkout total at your chosen location.',
			)
		);

		$content .= self::build_block_heading( 'Common questions readers ask before using the full menu', 2 );
		$content .= self::build_block_heading( 'Is this page meant to replace the category pages?', 3 );
		$content .= self::build_block_paragraph( 'No. The full-menu pillar is the starting point, not the ending point. It helps readers choose the right comparison branch and then move into the category or item page that answers the real order question.' );
		$content .= self::build_block_heading( 'Why is the full menu page still useful if I already know one item I want?', 3 );
		$content .= self::build_block_paragraph( 'Because people often discover that their real decision is wider than one item. A burger buyer may also need fries, a drink, or a dessert. A breakfast buyer may need timing information. A deal buyer may discover that the best value path sits in another branch of the menu.' );
		$content .= self::build_block_heading( 'Do prices on this page match every McDonald\'s location exactly?', 3 );
		$content .= self::build_block_paragraph( 'No. They are planning and comparison prices based on the tracked menu data across the site. The final local checkout can still vary because of location, tax, promotions, app participation, and delivery markups.' );
		$content .= self::build_block_heading( 'What should I open after this page?', 3 );
		$content .= self::build_block_paragraph( 'Open the category or guide that matches your real intent. That may be breakfast, burgers, chicken and fish, nuggets, fries and sides, desserts, deals and McValue, nutrition and allergens, or prices by state.' );

		$content .= self::build_block_heading( 'How we use and verify menu data', 2 );
		$content .= self::build_block_paragraph( 'This page is built as a routing and comparison pillar around the tracked McDonald&#8217;s USA menu data already used across the site. It is written to improve menu understanding, strengthen internal linking, and help readers reach the exact category or item page that matches their order decision.' );
		$content .= self::build_block_paragraph( 'For final local totals, live deal participation, allergen-sensitive decisions, and exact real-time availability, readers should still verify details through official McDonald&#8217;s sources before ordering. That is the most accurate way to move from a useful planning guide to a real transaction.' );

		$content .= self::build_block_heading( 'Best next pages after the full menu', 2 );
		$content .= self::build_block_list( self::build_related_link_items( $links ) );
		$content .= self::build_block_separator();
		$content .= self::build_block_shortcode( '[mcprices_menu_directory]' );

		return $content;
	}

	/**
	 * Return related-link list items for the root page.
	 *
	 * @param array<int, array<string, string>> $links Link data.
	 * @return string[]
	 */
	protected static function build_related_link_items( array $links ) {
		$items = array();

		foreach ( $links as $link ) {
			$label       = trim( (string) ( $link['label'] ?? '' ) );
			$url         = trim( (string) ( $link['url'] ?? '' ) );
			$description = trim( (string) ( $link['description'] ?? '' ) );

			if ( '' === $label || '' === $url ) {
				continue;
			}

			$item = '<a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a>';

			if ( '' !== $description ) {
				$item .= ' &mdash; ' . esc_html( $description );
			}

			$items[] = $item;
		}

		return $items;
	}

	/**
	 * Build one internal site URL.
	 *
	 * @param string $path Relative site path.
	 * @return string
	 */
	protected static function build_site_url( $path ) {
		$path = trim( (string) $path );

		if ( '' === $path ) {
			return home_url( '/' );
		}

		return home_url( '/' . ltrim( $path, '/' ) );
	}

	/**
	 * Build one internal site link.
	 *
	 * @param string $path  Relative site path.
	 * @param string $label Link label.
	 * @return string
	 */
	protected static function build_site_link( $path, $label ) {
		return '<a href="' . esc_url( self::build_site_url( $path ) ) . '">' . wp_kses_post( (string) $label ) . '</a>';
	}

	/**
	 * Wrap trusted HTML in a Gutenberg HTML block.
	 *
	 * @param string $html HTML markup.
	 * @return string
	 */
	protected static function build_html_block( $html ) {
		return '<!-- wp:html -->' . trim( (string) $html ) . '<!-- /wp:html -->';
	}

	/**
	 * Render one theme-native menu table block.
	 *
	 * @param string[]   $headers Table headers.
	 * @param string[][] $rows    Table body rows.
	 * @return string
	 */
	protected static function build_table_block( array $headers, array $rows ) {
		$thead = '';
		$tbody = '';

		foreach ( $headers as $header ) {
			$thead .= '<th>' . wp_kses_post( (string) $header ) . '</th>';
		}

		foreach ( $rows as $row ) {
			$tbody .= '<tr>';

			foreach ( $row as $cell ) {
				$tbody .= '<td>' . wp_kses_post( (string) $cell ) . '</td>';
			}

			$tbody .= '</tr>';
		}

		if ( '' === $thead || '' === $tbody ) {
			return '';
		}

		return self::build_html_block(
			'<div class="menu-table-wrap"><table class="menu-table"><thead><tr>' . $thead . '</tr></thead><tbody>' . $tbody . '</tbody></table></div>'
		);
	}

	/**
	 * Wrap a heading in Gutenberg comment markup.
	 *
	 * @param string $text  Heading text.
	 * @param int    $level Heading level.
	 * @return string
	 */
	protected static function build_block_heading( $text, $level = 2 ) {
		$level = max( 2, min( 4, (int) $level ) );

		return sprintf(
			'<!-- wp:heading {"level":%1$d} --><h%1$d>%2$s</h%1$d><!-- /wp:heading -->',
			$level,
			wp_kses_post( (string) $text )
		);
	}

	/**
	 * Wrap trusted HTML in a paragraph block.
	 *
	 * @param string $html Paragraph HTML.
	 * @return string
	 */
	protected static function build_block_paragraph( $html ) {
		return '<!-- wp:paragraph --><p>' . wp_kses_post( (string) $html ) . '</p><!-- /wp:paragraph -->';
	}

	/**
	 * Wrap list items in a Gutenberg list block.
	 *
	 * @param string[] $items List item HTML strings.
	 * @return string
	 */
	protected static function build_block_list( array $items ) {
		$list_items = '';

		foreach ( $items as $item ) {
			$item = trim( (string) $item );

			if ( '' === $item ) {
				continue;
			}

			$list_items .= '<li>' . wp_kses_post( $item ) . '</li>';
		}

		if ( '' === $list_items ) {
			return '';
		}

		return '<!-- wp:list --><ul>' . $list_items . '</ul><!-- /wp:list -->';
	}

	/**
	 * Return a separator block.
	 *
	 * @return string
	 */
	protected static function build_block_separator() {
		return '<!-- wp:separator --><hr class="wp-block-separator has-alpha-channel-opacity"/><!-- /wp:separator -->';
	}

	/**
	 * Wrap a shortcode in Gutenberg shortcode markup.
	 *
	 * @param string $shortcode Shortcode text.
	 * @return string
	 */
	protected static function build_block_shortcode( $shortcode ) {
		return '<!-- wp:shortcode -->' . trim( (string) $shortcode ) . '<!-- /wp:shortcode -->';
	}

	/**
	 * Create or normalize the canonical /menu/ root page.
	 *
	 * @return int
	 */
	protected static function ensure_root_page() {
		$root_content = self::get_root_content();
		$pages = get_posts(
			array(
				'post_type'      => 'page',
				'post_status'    => array( 'publish', 'draft', 'pending', 'private', 'trash' ),
				'posts_per_page' => -1,
				'orderby'        => array(
					'menu_order' => 'ASC',
					'ID'         => 'ASC',
				),
				'order'          => 'ASC',
			)
		);

		$candidates = array();

		foreach ( $pages as $page ) {
			$page_path   = (string) get_page_uri( $page->ID );
			$managed_key = (string) get_post_meta( $page->ID, '_mcprices_managed_key', true );
			$managed_type = (string) get_post_meta( $page->ID, '_mcprices_managed_page', true );

			if ( self::ROOT_PATH === $page_path || 'menu' === $managed_key || 'menu-root' === $managed_type ) {
				$candidates[] = $page;
			}
		}

		$root_page = self::pick_canonical_page( $candidates );

		if ( ! $root_page instanceof \WP_Post ) {
			$root_page_id = wp_insert_post(
				array(
					'post_title'   => self::ROOT_TITLE,
					'post_name'    => self::ROOT_PATH,
					'post_type'    => 'page',
					'post_status'  => 'publish',
					'post_parent'  => 0,
					'menu_order'   => 0,
					'post_excerpt' => self::ROOT_EXCERPT,
					'post_content' => $root_content,
				),
				true
			);

			if ( is_wp_error( $root_page_id ) || ! $root_page_id ) {
				return 0;
			}

			$root_page = get_post( (int) $root_page_id );
		}

		if ( ! $root_page instanceof \WP_Post ) {
			return 0;
		}

		wp_update_post(
			array(
				'ID'           => (int) $root_page->ID,
				'post_title'   => self::ROOT_TITLE,
				'post_name'    => self::ROOT_PATH,
				'post_status'  => 'publish',
				'post_parent'  => 0,
				'menu_order'   => 0,
				'post_excerpt' => self::ROOT_EXCERPT,
				'post_content' => $root_content,
			)
		);

		update_post_meta( (int) $root_page->ID, '_mcprices_managed_page', 'menu-root' );
		update_post_meta( (int) $root_page->ID, '_mcprices_managed_key', 'menu' );

		return (int) $root_page->ID;
	}

	/**
	 * Remove duplicate managed menu pages while preserving the canonical tree.
	 *
	 * @param int $root_page_id Canonical root page ID.
	 * @return void
	 */
	protected static function cleanup_duplicate_managed_pages( $root_page_id ) {
		$managed_pages = self::get_managed_pages();

		$groups = array();

		foreach ( $managed_pages as $page ) {
			$key = trim( (string) get_post_meta( $page->ID, '_mcprices_managed_key', true ) );

			if ( '' === $key ) {
				$key = 'path::' . (string) get_page_uri( $page->ID );
			}

			$groups[ $key ][] = $page;
		}

		foreach ( $groups as $pages ) {
			if ( count( $pages ) < 2 ) {
				continue;
			}

			$canonical = self::pick_canonical_page( $pages, $root_page_id );

			if ( ! $canonical instanceof \WP_Post ) {
				continue;
			}

			foreach ( $pages as $page ) {
				if ( (int) $page->ID === (int) $canonical->ID ) {
					continue;
				}

				self::delete_duplicate_page( (int) $page->ID, (int) $canonical->ID );
			}
		}

		self::cleanup_duplicate_paths( $root_page_id );
	}

	/**
	 * Remove duplicate pages that still share the exact same path.
	 *
	 * @param int $root_page_id Canonical root page ID.
	 * @return void
	 */
	protected static function cleanup_duplicate_paths( $root_page_id ) {
		$pages = self::get_managed_pages();

		$groups = array();

		foreach ( $pages as $page ) {
			$path = (string) get_page_uri( $page->ID );

			if ( '' === $path ) {
				continue;
			}

			$groups[ $path ][] = $page;
		}

		foreach ( $groups as $pages_with_path ) {
			if ( count( $pages_with_path ) < 2 ) {
				continue;
			}

			$canonical = self::pick_canonical_page( $pages_with_path, $root_page_id );

			if ( ! $canonical instanceof \WP_Post ) {
				continue;
			}

			foreach ( $pages_with_path as $page ) {
				if ( (int) $page->ID === (int) $canonical->ID ) {
					continue;
				}

				self::delete_duplicate_page( (int) $page->ID, (int) $canonical->ID );
			}
		}
	}

	/**
	 * Normalize the managed page hierarchy so categories sit under /menu/ and
	 * item pages sit under their canonical category pages.
	 *
	 * @param int $root_page_id Canonical root page ID.
	 * @return void
	 */
	protected static function normalize_managed_hierarchy( $root_page_id ) {
		$managed_pages = self::get_managed_pages();
		$groups        = array();

		foreach ( $managed_pages as $page ) {
			$key = trim( (string) get_post_meta( $page->ID, '_mcprices_managed_key', true ) );

			if ( '' === $key ) {
				continue;
			}

			$groups[ $key ][] = $page;
		}

		$canonical_pages = array(
			'menu' => get_post( (int) $root_page_id ),
		);

		foreach ( $groups as $key => $pages ) {
			if ( 'menu' === $key ) {
				continue;
			}

			$canonical = self::pick_canonical_page( $pages, $root_page_id );

			if ( $canonical instanceof \WP_Post ) {
				$canonical_pages[ $key ] = $canonical;
			}
		}

		foreach ( $canonical_pages as $key => $page ) {
			if ( ! $page instanceof \WP_Post || 'menu' === $key || false !== strpos( $key, '::' ) ) {
				continue;
			}

			self::normalize_managed_page(
				(int) $page->ID,
				array(
					'post_parent' => (int) $root_page_id,
					'post_name'   => self::get_desired_slug_from_key( $key ),
				)
			);

			$canonical_pages[ $key ] = get_post( (int) $page->ID );
		}

		foreach ( $canonical_pages as $key => $page ) {
			if ( ! $page instanceof \WP_Post || false === strpos( $key, '::' ) ) {
				continue;
			}

			list( $category_key ) = explode( '::', $key, 2 );
			$parent_page = isset( $canonical_pages[ $category_key ] ) && $canonical_pages[ $category_key ] instanceof \WP_Post
				? $canonical_pages[ $category_key ]
				: null;

			self::normalize_managed_page(
				(int) $page->ID,
				array(
					'post_parent' => $parent_page instanceof \WP_Post ? (int) $parent_page->ID : (int) $root_page_id,
					'post_name'   => self::get_desired_slug_from_key( $key ),
				)
			);
		}
	}

	/**
	 * Choose the canonical page from a duplicate group.
	 *
	 * @param array $pages        Candidate pages.
	 * @param int   $root_page_id Canonical root page ID.
	 * @return \WP_Post|null
	 */
	protected static function pick_canonical_page( array $pages, $root_page_id = 0 ) {
		$pages = array_filter(
			$pages,
			static function ( $page ) {
				return $page instanceof \WP_Post;
			}
		);

		if ( empty( $pages ) ) {
			return null;
		}

		usort(
			$pages,
			static function ( $left, $right ) use ( $root_page_id ) {
				$left_score  = self::get_page_preference_score( $left, $root_page_id );
				$right_score = self::get_page_preference_score( $right, $root_page_id );

				if ( $left_score === $right_score ) {
					return (int) $left->ID - (int) $right->ID;
				}

				return $right_score - $left_score;
			}
		);

		return $pages[0];
	}

	/**
	 * Score one page candidate for canonical preference.
	 *
	 * @param \WP_Post $page         Candidate page.
	 * @param int      $root_page_id Canonical root page ID.
	 * @return int
	 */
	protected static function get_page_preference_score( \WP_Post $page, $root_page_id = 0 ) {
		$score     = 0;
		$page_path = (string) get_page_uri( $page->ID );

		if ( 'publish' === $page->post_status ) {
			$score += 1000;
		}

		if ( $root_page_id && (int) $page->ID === (int) $root_page_id ) {
			$score += 500;
		}

		if ( false === strpos( $page_path, '-2' ) ) {
			$score += 150;
		}

		if ( false === strpos( $page_path, '-3' ) ) {
			$score += 50;
		}

		$score -= strlen( $page_path );

		$children = get_children(
			array(
				'post_parent' => (int) $page->ID,
				'post_type'   => 'page',
				'numberposts' => -1,
				'post_status' => 'any',
				'fields'      => 'ids',
			)
		);

		$score += is_array( $children ) ? count( $children ) * 25 : 0;

		return $score;
	}

	/**
	 * Return all managed menu pages, including trashed duplicates.
	 *
	 * @return \WP_Post[]
	 */
	protected static function get_managed_pages() {
		return get_posts(
			array(
				'post_type'      => 'page',
				'post_status'    => array( 'publish', 'draft', 'pending', 'private', 'trash' ),
				'posts_per_page' => -1,
				'meta_query'     => array(
					array(
						'key'     => '_mcprices_managed_page',
						'compare' => 'EXISTS',
					),
				),
			)
		);
	}

	/**
	 * Derive the intended slug from a managed page key.
	 *
	 * @param string $managed_key Managed page key.
	 * @return string
	 */
	protected static function get_desired_slug_from_key( $managed_key ) {
		$managed_key = trim( (string) $managed_key );

		if ( '' === $managed_key ) {
			return '';
		}

		if ( false !== strpos( $managed_key, '::' ) ) {
			list( , $item_slug ) = explode( '::', $managed_key, 2 );

			return sanitize_title( $item_slug );
		}

		return sanitize_title( $managed_key );
	}

	/**
	 * Normalize one managed page record.
	 *
	 * @param int   $page_id  Page ID.
	 * @param array $updates  Post fields to normalize.
	 * @return void
	 */
	protected static function normalize_managed_page( $page_id, array $updates ) {
		$page = get_post( (int) $page_id );

		if ( ! $page instanceof \WP_Post ) {
			return;
		}

		$post_data = array(
			'ID'          => (int) $page->ID,
			'post_status' => 'publish',
		);

		if ( array_key_exists( 'post_parent', $updates ) ) {
			$post_data['post_parent'] = (int) $updates['post_parent'];
		}

		if ( ! empty( $updates['post_name'] ) ) {
			$post_data['post_name'] = (string) $updates['post_name'];
		}

		wp_update_post( $post_data );
	}

	/**
	 * Remove a duplicate managed page after reassigning its children.
	 *
	 * @param int $duplicate_page_id Duplicate page ID.
	 * @param int $canonical_page_id Canonical page ID.
	 * @return void
	 */
	protected static function delete_duplicate_page( $duplicate_page_id, $canonical_page_id ) {
		if ( $duplicate_page_id === $canonical_page_id ) {
			return;
		}

		self::move_child_pages( (int) $duplicate_page_id, (int) $canonical_page_id );
		wp_delete_post( (int) $duplicate_page_id, true );
	}

	/**
	 * Reassign child pages to the canonical page before trashing duplicates.
	 *
	 * @param int $from_page_id Old parent page ID.
	 * @param int $to_page_id   New parent page ID.
	 * @return void
	 */
	protected static function move_child_pages( $from_page_id, $to_page_id ) {
		if ( $from_page_id === $to_page_id ) {
			return;
		}

		$children = get_children(
			array(
				'post_parent' => (int) $from_page_id,
				'post_type'   => 'page',
				'numberposts' => -1,
				'post_status' => 'any',
				'fields'      => 'ids',
			)
		);

		if ( empty( $children ) || ! is_array( $children ) ) {
			return;
		}

		foreach ( $children as $child_id ) {
			wp_update_post(
				array(
					'ID'          => (int) $child_id,
					'post_parent' => (int) $to_page_id,
				)
			);
		}
	}
}

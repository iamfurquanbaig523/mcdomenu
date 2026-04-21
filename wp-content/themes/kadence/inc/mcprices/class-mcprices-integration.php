<?php
/**
 * Native McPrices integration for the Kadence parent theme.
 *
 * @package kadence
 */

namespace Kadence;

use WP_Customize_Control;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds a native Kadence integration layer for the attached McPrices design.
 */
class McPrices_Integration {
	/**
	 * Theme mod used to toggle the global design layer.
	 */
	const ENABLE_SETTING = 'mcprices_enable_design';

	/**
	 * Theme mod used to toggle the footer disclaimer.
	 */
	const DISCLAIMER_ENABLE_SETTING = 'mcprices_footer_disclaimer_enable';

	/**
	 * Theme mod used to store the footer disclaimer content.
	 */
	const DISCLAIMER_TEXT_SETTING = 'mcprices_footer_disclaimer_text';

	/**
	 * Theme mod used to track the one-time native Kadence seeding pass.
	 */
	const SEED_VERSION_SETTING = 'mcprices_native_seed_version';

	/**
	 * Current seeding version for the native design integration.
	 */
	const SEED_VERSION = '2.0.1';

	/**
	 * Option used to track Rank Math SEO seeding for portable databases.
	 */
	const RANK_MATH_SEED_VERSION_OPTION = 'mcprices_rank_math_seed_version';

	/**
	 * Current Rank Math seed version.
	 */
	const RANK_MATH_SEED_VERSION = '2.0.1';

	/**
	 * Option used to track portable DB-backed setup seeding.
	 */
	const PORTABLE_DB_SEED_VERSION_OPTION = 'mcprices_portable_db_seed_version';

	/**
	 * Current portable DB seed version.
	 */
	const PORTABLE_DB_SEED_VERSION = '2.0.0';

	/**
	 * Option used to trigger a one-time rewrite flush after DB seeding.
	 */
	const REWRITE_FLUSH_OPTION = 'mcprices_pending_rewrite_flush';

	/**
	 * Option used to track the currently seeded homepage pattern signature.
	 */
	const HOMEPAGE_PATTERN_SIGNATURE_OPTION = 'mcprices_homepage_pattern_signature';

	/**
	 * Option used to track the currently seeded menu signature.
	 */
	const MENU_SIGNATURE_OPTION = 'mcprices_menu_signature';

	/**
	 * Option used to track the currently seeded footer widget signature.
	 */
	const FOOTER_WIDGET_SIGNATURE_OPTION = 'mcprices_footer_widget_signature';

	/**
	 * Singleton instance.
	 *
	 * @var McPrices_Integration|null
	 */
	protected static $instance = null;

	/**
	 * Get or create the integration instance.
	 *
	 * @return McPrices_Integration
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	protected function __construct() {
		add_filter( 'kadence_theme_options_defaults', array( $this, 'filter_kadence_defaults' ) );
		add_filter( 'kadence_global_palette_defaults', array( $this, 'filter_palette_defaults' ) );
		add_filter( 'kadence_post_layout', array( $this, 'filter_front_page_layout' ) );
		add_filter( 'body_class', array( $this, 'filter_body_classes' ) );
		add_filter( 'wp_resource_hints', array( $this, 'filter_resource_hints' ), 10, 2 );
		add_filter( 'pre_get_document_title', array( $this, 'filter_document_title' ), 20 );
		add_filter( 'the_content', array( $this, 'filter_dynamic_date_content' ), 20 );
		add_filter( 'render_block', array( $this, 'filter_dynamic_date_block_html' ), 20, 2 );
		add_filter( 'theme_mod_header_html_content', array( $this, 'filter_dynamic_update_bar_html' ) );
		add_filter( 'theme_mod_mobile_html_content', array( $this, 'filter_dynamic_update_bar_html' ) );
		add_filter( 'wp_robots', array( $this, 'filter_homepage_robots' ), 20 );
		add_filter( 'robots_txt', array( $this, 'filter_robots_txt' ), 20, 2 );

		add_action( 'after_setup_theme', array( $this, 'maybe_seed_native_design' ), 30 );
		add_action( 'after_setup_theme', array( $this, 'maybe_seed_portable_database_settings' ), 34 );
		add_action( 'after_setup_theme', array( $this, 'maybe_seed_rank_math_settings' ), 35 );
		add_action( 'after_setup_theme', array( $this, 'maybe_sync_managed_site_content' ), 40 );
		add_action( 'customize_register', array( $this, 'register_customizer' ), 100 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ), 30 );
		add_action( 'init', array( $this, 'maybe_flush_pending_rewrite_rules' ), 99 );
		add_action( 'wp_head', array( $this, 'render_homepage_meta_tags' ), 2 );
		add_action( 'wp_head', array( $this, 'render_homepage_schema' ), 30 );
		add_action( 'template_redirect', array( $this, 'maybe_render_sitemap' ), 0 );
		add_action( 'template_redirect', array( $this, 'maybe_render_robots' ), 0 );
		add_action( 'kadence_before_footer', array( $this, 'render_footer_disclaimer' ), 5 );
		add_action( 'kadence_render_mobile_header_column', array( $this, 'render_mobile_header_search_toggle' ), 20, 2 );
		add_action( 'init', array( $this, 'register_shortcodes' ), 15 );
		add_action( 'init', array( $this, 'register_patterns' ), 20 );
		add_action( 'wp_footer', array( $this, 'render_mobile_search_panel' ), 15 );
		add_action( 'wp_footer', array( $this, 'render_mobile_quick_nav' ), 20 );
	}

	/**
	 * Return whether the site-wide McPrices design mode is enabled.
	 *
	 * @return bool
	 */
	protected function design_enabled() {
		return (bool) get_theme_mod( self::ENABLE_SETTING, true );
	}

	/**
	 * Return the default footer disclaimer text.
	 *
	 * @return string
	 */
	protected function get_default_disclaimer_text() {
		return '&#9888;&#65039; <strong>Disclaimer:</strong> This is an independent, unofficial website. McDonald&#8217;s Menu Prices USA is not affiliated with, endorsed by, or connected to McDonald&#8217;s Corporation in any way. All prices are sourced from publicly available menus and may vary by location, market, promotion, and date. This site may contain advertisements.';
	}

	/**
	 * Return the site-home path without a hard-coded host so seeded links remain
	 * portable across local and production domains.
	 *
	 * @return string
	 */
	protected function get_home_path_url() {
		$path = wp_parse_url( home_url( '/' ), PHP_URL_PATH );
		$path = is_string( $path ) ? $path : '/';
		$path = '/' . ltrim( $path, '/' );

		return untrailingslashit( $path ) . '/';
	}

	/**
	 * Return the current site-local date string.
	 *
	 * @param string $format PHP date format.
	 * @return string
	 */
	protected function get_current_site_date( $format = 'd-m-Y' ) {
		if ( function_exists( 'kadence_mcprices_get_current_site_date' ) ) {
			return (string) kadence_mcprices_get_current_site_date( $format );
		}

		return wp_date( $format, null, wp_timezone() );
	}

	/**
	 * Return the current site-local year.
	 *
	 * @return string
	 */
	protected function get_current_site_year() {
		return $this->get_current_site_date( 'Y' );
	}

	/**
	 * Replace managed McPrices date labels with the current date/year at render time.
	 *
	 * @param string $content Raw HTML/text.
	 * @return string
	 */
	protected function replace_dynamic_date_strings( $content ) {
		$current_date = $this->get_current_site_date();
		$current_year = $this->get_current_site_year();

		$replacements = array(
			'Prices last verified: <strong>April 2026</strong>'                               => 'Prices last verified: <strong>' . $current_date . '</strong>',
			'Updated April 2026'                                                              => 'Updated ' . $current_date,
			'updated April 2026'                                                              => 'updated ' . $current_date,
			'updated for April 2026'                                                          => 'updated for ' . $current_date,
			'latest April 2026 update'                                                        => 'latest ' . $current_date . ' update',
			'current April 2026 update'                                                       => 'current ' . $current_date . ' update',
			'Prices were last verified in April 2026.'                                        => 'Prices were last verified on ' . $current_date . '.',
			'What&#8217;s New 2026'                                                            => 'What&#8217;s New ' . $current_year,
			"What's New 2026"                                                                 => "What's New " . $current_year,
			"McDonald's Menu Prices UK 2026"                                                  => "McDonald's Menu Prices USA " . $current_year,
			"McDonald's Menu Prices USA 2026"                                                 => "McDonald's Menu Prices USA " . $current_year,
			"What's New at McDonald's UK 2026"                                                => "What's New at McDonald's USA " . $current_year,
			"What's New at McDonald's USA 2026"                                               => "What's New at McDonald's USA " . $current_year,
			"Complete McDonald's UK Menu 2026"                                                => "Complete McDonald's USA Menu " . $current_year,
			"Complete McDonald's USA Menu 2026"                                               => "Complete McDonald's USA Menu " . $current_year,
			"McDonald's UK Value Picks 2026"                                                  => "McDonald's USA Value Deals " . $current_year,
			"McDonald's USA Value Deals 2026"                                                 => "McDonald's USA Value Deals " . $current_year,
			"McDonald's UK Calorie Guide 2026"                                                => "McDonald's USA Calorie Guide " . $current_year,
			"McDonald's USA Calorie Guide 2026"                                               => "McDonald's USA Calorie Guide " . $current_year,
			"McDonald's UK Holiday Hours 2026"                                                => "McDonald's Hours (USA) " . $current_year,
			"McDonald's UK in Numbers (2026)"                                                 => "McDonald's USA in Numbers (" . $current_year . ')',
			'UK 2026'                                                                         => 'USA ' . $current_year,
			'USA 2026'                                                                        => 'USA ' . $current_year,
			'Date 2026'                                                                       => 'Date ' . $current_year,
			'April 2026'                                                                      => $current_date,
		);

		return strtr( (string) $content, $replacements );
	}

	/**
	 * Return the default update bar HTML used in the builder HTML item.
	 *
	 * @return string
	 */
	protected function get_default_update_bar_html() {
		return $this->get_seeded_update_bar_html();
	}

	/**
	 * Return the most appropriate blog landing URL.
	 *
	 * @return string
	 */
	protected function get_blog_url() {
		$posts_page_id = (int) get_option( 'page_for_posts' );
		if ( $posts_page_id ) {
			$posts_page_url = get_permalink( $posts_page_id );
			if ( $posts_page_url ) {
				return $posts_page_url;
			}
		}

		return home_url( '/blog/' );
	}

	/**
	 * Return the Rank Math titles option values required for the seeded SEO setup.
	 *
	 * @return array<string, mixed>
	 */
	protected function get_rank_math_titles_seed() {
		$current_year = $this->get_current_site_year();
		$current_date = $this->get_current_site_date();

		return array(
			'title_separator'      => '|',
			'website_name'          => "McDonald's Menu Prices USA",
			'knowledgegraph_name'   => "McDonald's Menu Prices USA",
			'knowledgegraph_type'   => 'person',
			'local_business_type'   => 'Organization',
			'homepage_title'        => "McDonald's Menu Prices USA {$current_year} | Full Price List & Calories",
			'homepage_description'  => "Complete McDonald's USA menu prices updated {$current_date}. Find prices for burgers, breakfast, McCafe, drinks, McValue deals, McNuggets, Happy Meals, desserts, and combo meals in dollars.",
			'pt_post_title'         => "%title% | McDonald's Menu Prices USA",
			'pt_post_description'   => '%excerpt%',
			'tax_category_title'    => "%term% Prices USA {$current_year} | McDonald's Menu Prices USA",
			'tax_category_description' => "Browse %term% prices, deals, calories, and McDonald's USA menu updates for {$current_year} on McDonald's Menu Prices USA.",
			'404_title'             => "Page Not Found | McDonald's Menu Prices USA",
		);
	}

	/**
	 * Return the Rank Math general option values required for the seeded SEO setup.
	 *
	 * @return array<string, mixed>
	 */
	protected function get_rank_math_general_seed() {
		return array(
			'setup_mode'                  => 'advanced',
			'attachment_redirect_default' => home_url( '/' ),
		);
	}

	/**
	 * Return the Rank Math sitemap option values required for the seeded SEO setup.
	 *
	 * @return array<string, mixed>
	 */
	protected function get_rank_math_sitemap_seed() {
		return array(
			'items_per_page'     => 200,
			'include_images'     => 'on',
			'html_sitemap'       => 'on',
			'html_sitemap_display' => 'shortcode',
			'pt_post_sitemap'    => 'on',
			'pt_page_sitemap'    => 'on',
			'tax_category_sitemap' => 'on',
		);
	}

	/**
	 * Return the minimum Rank Math modules that should be active for the seeded setup.
	 *
	 * @return string[]
	 */
	protected function get_rank_math_seed_modules() {
		return array(
			'link-counter',
			'analytics',
			'seo-analysis',
			'sitemap',
			'rich-snippet',
			'instant-indexing',
		);
	}

	/**
	 * Return the support and guide pages that should exist in any fresh DB.
	 *
	 * @return array<string, array<string, string>>
	 */
	protected function get_seeded_support_pages() {
		return array(
			'about' => array(
				'title'   => 'About Us',
				'content' => '<!-- wp:paragraph --><p>McDonald&#8217;s Menu Prices USA is an independent guide to McDonald&#8217;s USA menu prices, calories, deals, breakfast hours, drinks, desserts, and combo meals. We update the site regularly so readers can compare prices, understand category coverage, and check new menu rollouts without relying on scattered screenshots.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>We are not affiliated with McDonald&#8217;s. Prices can vary by location, franchise, app offer, taxes, and delivery platform.</p><!-- /wp:paragraph -->',
			),
			'privacy-policy' => array(
				'title'   => 'Privacy Policy',
				'content' => '<!-- wp:paragraph --><p>This Privacy Policy explains how McDonald&#8217;s Menu Prices USA may collect and use limited information such as analytics data, contact submissions, and advertising-related data when you use the site. We only use this information to operate, improve, and protect the website.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>If you contact us directly, we may retain the information you send so we can respond. Third-party services such as analytics, advertising, and embedded tools may also process data according to their own policies.</p><!-- /wp:paragraph -->',
			),
			'cookie-policy' => array(
				'title'   => 'Cookie Policy',
				'content' => '<!-- wp:paragraph --><p>McDonald&#8217;s Menu Prices USA may use cookies and similar technologies to remember preferences, measure traffic, and support advertising or performance tools. Some cookies are essential for the site to work properly, while others help us understand how visitors use the site.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>You can usually control cookies through your browser settings. Disabling some cookies may affect how parts of the site function.</p><!-- /wp:paragraph -->',
			),
			'contact' => array(
				'title'   => 'Contact',
				'content' => '<!-- wp:paragraph --><p>Use this page to contact McDonald&#8217;s Menu Prices USA about price corrections, menu updates, advertising questions, or general feedback. If you spot a menu price that looks outdated, include the item name, restaurant location, and latest observed price so we can review it quickly.</p><!-- /wp:paragraph -->',
			),
			'disclaimer' => array(
				'title'   => 'Disclaimer',
				'content' => '<!-- wp:paragraph --><p>McDonald&#8217;s Menu Prices USA is an independent, unofficial website and is not affiliated with, endorsed by, or connected to McDonald&#8217;s Corporation. Prices, calories, availability, and promotions may vary by location and date.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>Always confirm important details such as allergens, breakfast hours, delivery pricing, and final local totals with the official McDonald&#8217;s app, website, or restaurant before ordering.</p><!-- /wp:paragraph -->',
			),
			'ad-disclosure' => array(
				'title'   => 'Ad Disclosure',
				'content' => '<!-- wp:paragraph --><p>McDonald&#8217;s Menu Prices USA may display advertisements, sponsored placements, or monetised content to support site operations. Advertising relationships do not change our editorial approach: we still aim to provide clear, practical, and regularly updated McDonald&#8217;s USA price information.</p><!-- /wp:paragraph -->',
			),
			'sitemap' => array(
				'title'   => 'Sitemap',
				'content' => '<!-- wp:paragraph --><p>Browse the main areas of McDonald&#8217;s Menu Prices USA below, or use the XML sitemap at <a href="' . esc_url( home_url( '/sitemap.xml' ) ) . '">' . esc_html( home_url( '/sitemap.xml' ) ) . '</a> for the crawler-friendly version.</p><!-- /wp:paragraph --><!-- wp:shortcode -->[rank_math_html_sitemap]<!-- /wp:shortcode -->',
			),
			'big-mac-price-usa' => array(
				'title'   => 'Big Mac Price USA',
				'content' => '<!-- wp:paragraph --><p>This guide focuses on the current Big Mac price in the USA, including sandwich pricing, combo meal pricing, calorie information, and how the Big Mac compares with other burger choices on the McDonald&#8217;s USA menu.</p><!-- /wp:paragraph -->',
			),
			'mcdonalds-app-deals' => array(
				'title'   => 'McDonald&#8217;s App Deals',
				'content' => '<!-- wp:paragraph --><p>Our McDonald&#8217;s App Deals guide tracks the most useful USA app offers, including McValue bundles, buy-one-add-one deals, meal discounts, rewards offers, and limited-time promotions that can lower the cost of popular items.</p><!-- /wp:paragraph -->',
			),
			'calorie-counter' => array(
				'title'   => 'Calorie Counter',
				'content' => '<!-- wp:paragraph --><p>The McDonald&#8217;s Menu Prices USA calorie counter page helps you compare menu items by calories so you can spot lighter burgers, breakfast choices, sides, drinks, and dessert options more easily.</p><!-- /wp:paragraph -->',
			),
			'breakfast-hours' => array(
				'title'   => 'Breakfast Hours',
				'content' => '<!-- wp:paragraph --><p>This page explains typical McDonald&#8217;s breakfast hours in the USA, including when breakfast usually ends and which menu items are normally available in the morning window.</p><!-- /wp:paragraph -->',
			),
			'allergen-guide' => array(
				'title'   => 'Allergen Guide',
				'content' => '<!-- wp:paragraph --><p>Our allergen guide explains how to approach McDonald&#8217;s USA menu choices more carefully, but always use the official McDonald&#8217;s allergen tool and restaurant information for final decisions.</p><!-- /wp:paragraph -->',
			),
			'price-history' => array(
				'title'   => 'Price History',
				'content' => '<!-- wp:paragraph --><p>The McDonald&#8217;s Menu Prices USA price history page tracks how popular menu prices have changed over time, helping readers compare current pricing with previous months and seasonal promotions.</p><!-- /wp:paragraph -->',
			),
			'delivery-guide' => array(
				'title'   => 'Delivery Guide',
				'content' => '<!-- wp:paragraph --><p>This delivery guide explains what to expect when ordering McDonald&#8217;s USA through delivery platforms, including price differences, fees, bundle availability, and app-linked promotions.</p><!-- /wp:paragraph -->',
			),
			'rewards-guide' => array(
				'title'   => 'Rewards Guide',
				'content' => '<!-- wp:paragraph --><p>This guide covers how MyMcDonald&#8217;s Rewards fits into current USA pricing, including points, redemptions, app-only discounts, and how rewards interact with meal deals.</p><!-- /wp:paragraph -->',
			),
			'limited-time-menu' => array(
				'title'   => 'Limited-Time Menu',
				'content' => '<!-- wp:paragraph --><p>This page highlights current limited-time McDonald&#8217;s USA menu items, seasonal sandwiches, desserts, breakfast collaborations, and short-run deal bundles that may not stay on the menu for long.</p><!-- /wp:paragraph -->',
			),
			'breakfast-menu' => array(
				'title'   => 'Breakfast Menu',
				'content' => '<!-- wp:paragraph --><p>This guide covers the McDonald&#8217;s USA breakfast menu, including biscuits, McMuffins, McGriddles, bagels, hotcakes, oatmeal, hash browns, and breakfast meal pricing.</p><!-- /wp:paragraph -->',
			),
			'burgers-menu' => array(
				'title'   => 'Burgers Menu',
				'content' => '<!-- wp:paragraph --><p>This guide covers McDonald&#8217;s USA burger prices, including Big Mac, Quarter Pounder, McDouble, Daily Double, cheeseburgers, hamburgers, and combo meal pricing.</p><!-- /wp:paragraph -->',
			),
			'chicken-fish-menu' => array(
				'title'   => 'Chicken & Fish Menu',
				'content' => '<!-- wp:paragraph --><p>This guide covers McDonald&#8217;s USA chicken and fish sandwiches, including McCrispy, McChicken, Filet-O-Fish, spicy builds, and sandwich meal pricing.</p><!-- /wp:paragraph -->',
			),
			'nuggets-and-strips' => array(
				'title'   => 'McNuggets & Strips',
				'content' => '<!-- wp:paragraph --><p>This guide covers Chicken McNuggets, McCrispy Strips, share boxes, and combo options across the current McDonald&#8217;s USA menu.</p><!-- /wp:paragraph -->',
			),
			'snack-wrap' => array(
				'title'   => 'Snack Wrap',
				'content' => '<!-- wp:paragraph --><p>This guide tracks the current McDonald&#8217;s USA snack wrap lineup, including spicy and ranch builds, pricing, and calories.</p><!-- /wp:paragraph -->',
			),
			'fries-sides' => array(
				'title'   => 'Fries & Sides',
				'content' => '<!-- wp:paragraph --><p>This guide covers World Famous Fries in each size plus side items such as apple slices, hash browns, and other commonly available side add-ons in the USA menu.</p><!-- /wp:paragraph -->',
			),
			'happy-meal-menu' => array(
				'title'   => 'Happy Meal Menu',
				'content' => '<!-- wp:paragraph --><p>This guide covers current McDonald&#8217;s USA Happy Meal options, prices, calories, and the main kids&#8217; meal builds readers compare most often.</p><!-- /wp:paragraph -->',
			),
			'sweets-treats' => array(
				'title'   => 'Sweets & Treats',
				'content' => '<!-- wp:paragraph --><p>This guide covers McFlurry flavors, sundaes, shakes, cones, cookies, and apple pie pricing across the McDonald&#8217;s USA sweets and treats menu.</p><!-- /wp:paragraph -->',
			),
			'mccafe-menu' => array(
				'title'   => 'McCafe Menu',
				'content' => '<!-- wp:paragraph --><p>This guide covers McCafe coffees, espresso drinks, iced coffees, frappes, lattes, cappuccinos, and related price ranges on the McDonald&#8217;s USA menu.</p><!-- /wp:paragraph -->',
			),
			'beverage-menu' => array(
				'title'   => 'Beverage Menu',
				'content' => '<!-- wp:paragraph --><p>This guide covers soft drinks, smoothies, frozen drinks, lemonade, tea, orange juice, water, and other McDonald&#8217;s USA beverages with current pricing and calorie ranges.</p><!-- /wp:paragraph -->',
			),
			'dollar-menu' => array(
				'title'   => '$1 $2 $3 Menu',
				'content' => '<!-- wp:paragraph --><p>This guide covers the budget-focused McDonald&#8217;s USA value lineup, including lower-cost breakfast picks, burgers, nuggets, fries, and other entry-price items.</p><!-- /wp:paragraph -->',
			),
			'extra-value-meals' => array(
				'title'   => 'Extra Value Meals',
				'content' => '<!-- wp:paragraph --><p>This guide covers McDonald&#8217;s USA combo meals for breakfast, lunch, and dinner, including burger meals, chicken meals, fish meals, and wrap meal pricing.</p><!-- /wp:paragraph -->',
			),
			'shareables-bundles' => array(
				'title'   => 'Shareables & Bundles',
				'content' => '<!-- wp:paragraph --><p>This guide covers larger McDonald&#8217;s USA share boxes and bundles, including 40-piece McNuggets, large fries packs, cookie totes, and family-style ordering ideas.</p><!-- /wp:paragraph -->',
			),
			'sauces-condiments' => array(
				'title'   => 'Sauces & Condiments',
				'content' => '<!-- wp:paragraph --><p>This guide covers McDonald&#8217;s USA dipping sauces and condiment packets, including barbecue, ranch, honey mustard, sweet and sour, buffalo, ketchup, mustard, and mayo.</p><!-- /wp:paragraph -->',
			),
		);
	}

	/**
	 * Return whether the Rank Math plugin files are present.
	 *
	 * @return bool
	 */
	protected function is_rank_math_plugin_available() {
		return file_exists( WP_PLUGIN_DIR . '/seo-by-rank-math/rank-math.php' );
	}

	/**
	 * Return whether Rank Math is currently active.
	 *
	 * @return bool
	 */
	protected function is_rank_math_plugin_active() {
		if ( defined( 'RANK_MATH_VERSION' ) || class_exists( '\RankMath\Helper' ) ) {
			return true;
		}

		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		return function_exists( 'is_plugin_active' ) && is_plugin_active( 'seo-by-rank-math/rank-math.php' );
	}

	/**
	 * Return whether the portable DB-backed seed is already current.
	 *
	 * @return bool
	 */
	protected function portable_db_seed_is_current() {
		if ( self::PORTABLE_DB_SEED_VERSION !== get_option( self::PORTABLE_DB_SEED_VERSION_OPTION, '' ) ) {
			return false;
		}

		if ( "McDonald's Menu Prices USA" !== (string) get_option( 'blogname', '' ) ) {
			return false;
		}

		if ( 'Full USA price list, calories, deals, breakfast hours, and menu guides.' !== (string) get_option( 'blogdescription', '' ) ) {
			return false;
		}

		if ( '/%postname%/' !== (string) get_option( 'permalink_structure', '' ) ) {
			return false;
		}

		if ( $this->is_rank_math_plugin_available() && ! $this->is_rank_math_plugin_active() ) {
			return false;
		}

		foreach ( $this->get_seeded_support_pages() as $slug => $page_data ) {
			$page = get_page_by_path( $slug );

			if ( ! $page instanceof \WP_Post || 'publish' !== $page->post_status ) {
				return false;
			}
		}

		$privacy_page = get_page_by_path( 'privacy-policy' );
		if ( $privacy_page instanceof \WP_Post && (int) $privacy_page->ID !== (int) get_option( 'wp_page_for_privacy_policy', 0 ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Ensure pretty permalinks are enabled for the seeded site.
	 *
	 * @return bool
	 */
	protected function maybe_seed_permalink_structure() {
		if ( '/%postname%/' === (string) get_option( 'permalink_structure', '' ) ) {
			return false;
		}

		update_option( 'permalink_structure', '/%postname%/' );

		return true;
	}

	/**
	 * Seed the WordPress site title and tagline for portable deployments.
	 *
	 * @return bool
	 */
	protected function maybe_seed_site_identity() {
		$changed = false;

		if ( "McDonald's Menu Prices USA" !== (string) get_option( 'blogname', '' ) ) {
			update_option( 'blogname', "McDonald's Menu Prices USA" );
			$changed = true;
		}

		if ( 'Full USA price list, calories, deals, breakfast hours, and menu guides.' !== (string) get_option( 'blogdescription', '' ) ) {
			update_option( 'blogdescription', 'Full USA price list, calories, deals, breakfast hours, and menu guides.' );
			$changed = true;
		}

		return $changed;
	}

	/**
	 * Activate Rank Math automatically when its plugin files are already present.
	 *
	 * @return bool
	 */
	protected function maybe_activate_rank_math_plugin() {
		if ( ! $this->is_rank_math_plugin_available() || $this->is_rank_math_plugin_active() ) {
			return false;
		}

		if ( ! function_exists( 'activate_plugin' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if ( function_exists( 'activate_plugin' ) ) {
			$activation = activate_plugin( 'seo-by-rank-math/rank-math.php', '', false, true );

			return ! is_wp_error( $activation );
		}

		return false;
	}

	/**
	 * Ensure the linked support and guide pages exist and are published.
	 *
	 * @return bool
	 */
	protected function maybe_seed_support_pages() {
		$changed = false;

		foreach ( $this->get_seeded_support_pages() as $slug => $page_data ) {
			$page = get_page_by_path( $slug );

			if ( $page instanceof \WP_Post ) {
				$needs_update =
					$page_data['title'] !== (string) $page->post_title ||
					$slug !== (string) $page->post_name ||
					'publish' !== (string) $page->post_status ||
					trim( (string) $page->post_content ) !== trim( (string) $page_data['content'] );

				if ( $needs_update ) {
					wp_update_post(
						array(
							'ID'           => (int) $page->ID,
							'post_title'   => $page_data['title'],
							'post_name'    => $slug,
							'post_status'  => 'publish',
							'post_content' => $page_data['content'],
						)
					);
					$changed = true;
				}

				continue;
			}

			$page_id = wp_insert_post(
				array(
					'post_title'   => $page_data['title'],
					'post_name'    => $slug,
					'post_type'    => 'page',
					'post_status'  => 'publish',
					'post_content' => $page_data['content'],
				),
				true
			);

			if ( ! is_wp_error( $page_id ) && $page_id ) {
				$changed = true;
			}
		}

		return $changed;
	}

	/**
	 * Assign the seeded privacy policy page in WordPress settings.
	 *
	 * @return bool
	 */
	protected function maybe_seed_privacy_policy_page() {
		$privacy_page = get_page_by_path( 'privacy-policy' );

		if ( ! $privacy_page instanceof \WP_Post ) {
			return false;
		}

		if ( (int) $privacy_page->ID === (int) get_option( 'wp_page_for_privacy_policy', 0 ) ) {
			return false;
		}

		update_option( 'wp_page_for_privacy_policy', (int) $privacy_page->ID );

		return true;
	}

	/**
	 * Flag rewrite rules for a one-time flush on init.
	 *
	 * @return void
	 */
	protected function mark_rewrite_flush_pending() {
		update_option( self::REWRITE_FLUSH_OPTION, '1', false );
	}

	/**
	 * Flush rewrite rules when the portable DB seed requested it.
	 *
	 * @return void
	 */
	public function maybe_flush_pending_rewrite_rules() {
		if ( '1' !== (string) get_option( self::REWRITE_FLUSH_OPTION, '' ) ) {
			return;
		}

		flush_rewrite_rules( false );
		delete_option( self::REWRITE_FLUSH_OPTION );
	}

	/**
	 * Seed all remaining DB-backed settings that should survive a fresh database.
	 *
	 * @return void
	 */
	public function maybe_seed_portable_database_settings() {
		if ( $this->portable_db_seed_is_current() ) {
			return;
		}

		$changed = false;

		if ( $this->maybe_seed_site_identity() ) {
			$changed = true;
		}

		if ( $this->maybe_seed_permalink_structure() ) {
			$changed = true;
		}

		if ( $this->maybe_activate_rank_math_plugin() ) {
			$changed = true;
		}

		if ( $this->maybe_seed_support_pages() ) {
			$changed = true;
		}

		if ( $this->maybe_seed_privacy_policy_page() ) {
			$changed = true;
		}

		if ( $changed ) {
			$this->mark_rewrite_flush_pending();
		}

		update_option( self::PORTABLE_DB_SEED_VERSION_OPTION, self::PORTABLE_DB_SEED_VERSION, false );
	}

	/**
	 * Merge a seed array into a stored Rank Math option array.
	 *
	 * @param string               $option_name Option name.
	 * @param array<string, mixed> $seed_values Seeded values.
	 * @return void
	 */
	protected function merge_rank_math_option_array( $option_name, $seed_values ) {
		$current_values = get_option( $option_name, array() );
		$current_values = is_array( $current_values ) ? $current_values : array();
		$merged_values  = array_replace_recursive( $current_values, $seed_values );

		update_option( $option_name, $merged_values, false );
	}

	/**
	 * Determine whether the critical Rank Math SEO seed values already exist.
	 *
	 * @return bool
	 */
	protected function rank_math_seed_is_current() {
		$titles = get_option( 'rank-math-options-titles', array() );
		$titles = is_array( $titles ) ? $titles : array();
		$current_year = $this->get_current_site_year();
		$current_date = $this->get_current_site_date();

		if ( self::RANK_MATH_SEED_VERSION === get_option( self::RANK_MATH_SEED_VERSION_OPTION, '' )
			&& '1' === (string) get_option( 'rank_math_registration_skip', '' )
			&& '1' === (string) get_option( 'rank_math_wizard_completed', '' )
			&& '1' === (string) get_option( 'blog_public', '' )
			&& "McDonald's Menu Prices USA {$current_year} | Full Price List & Calories" === ( $titles['homepage_title'] ?? '' )
			&& "Complete McDonald's USA menu prices updated {$current_date}. Find prices for burgers, breakfast, McCafe, drinks, McValue deals, McNuggets, Happy Meals, desserts, and combo meals in dollars." === ( $titles['homepage_description'] ?? '' )
			&& "%title% | McDonald's Menu Prices USA" === ( $titles['pt_post_title'] ?? '' )
			&& "%term% Prices USA {$current_year} | McDonald's Menu Prices USA" === ( $titles['tax_category_title'] ?? '' )
			&& "Page Not Found | McDonald's Menu Prices USA" === ( $titles['404_title'] ?? '' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Seed Rank Math configuration into the current database so SEO settings are
	 * recreated automatically on fresh local or production installs.
	 *
	 * @return void
	 */
	public function maybe_seed_rank_math_settings() {
		if ( $this->rank_math_seed_is_current() ) {
			return;
		}

		update_option( 'rank_math_registration_skip', '1', false );
		update_option( 'rank_math_wizard_completed', '1', false );
		update_option( 'blog_public', '1', false );

		$current_modules = get_option( 'rank_math_modules', array() );
		$current_modules = is_array( $current_modules ) ? $current_modules : array();
		$seed_modules    = array_values( array_unique( array_merge( $current_modules, $this->get_rank_math_seed_modules() ) ) );

		update_option( 'rank_math_modules', $seed_modules, false );
		$this->merge_rank_math_option_array( 'rank-math-options-titles', $this->get_rank_math_titles_seed() );
		$this->merge_rank_math_option_array( 'rank-math-options-general', $this->get_rank_math_general_seed() );
		$this->merge_rank_math_option_array( 'rank-math-options-sitemap', $this->get_rank_math_sitemap_seed() );

		update_option( self::RANK_MATH_SEED_VERSION_OPTION, self::RANK_MATH_SEED_VERSION, false );
	}

	/**
	 * Keep the managed homepage, menus and footer widgets in sync with the theme
	 * files so uploading newer files updates older databases automatically.
	 *
	 * @return void
	 */
	public function maybe_sync_managed_site_content() {
		if ( ! $this->design_enabled() ) {
			return;
		}

		$this->maybe_seed_front_page();
		$this->maybe_seed_blog_page();
		$this->maybe_sync_front_page_pattern_content();
		$this->maybe_seed_support_pages();
		$this->maybe_sync_seed_menus();
		$this->maybe_sync_footer_widget_blocks();
	}

	/**
	 * Build a homepage section URL that still works from inner pages.
	 *
	 * @param string $section Section anchor without the leading #.
	 * @return string
	 */
	protected function get_section_url( $section ) {
		return $this->get_home_path_url() . '#' . ltrim( $section, '#' );
	}

	/**
	 * Return the update bar HTML used in active defaults and seeding.
	 *
	 * @return string
	 */
	protected function get_seeded_update_bar_html() {
		return '<p>&#9989; Prices last verified: <strong>' . esc_html( $this->get_current_site_date() ) . '</strong> &mdash; <a href="' . esc_url( $this->get_section_url( 'full-menu' ) ) . '">View full price list &darr;</a></p>';
	}

	/**
	 * Return the raw homepage pattern markup from the theme file.
	 *
	 * @return string
	 */
	protected function get_homepage_pattern_markup() {
		$homepage_pattern = require get_theme_file_path( '/inc/mcprices/pattern-homepage.php' );

		return is_string( $homepage_pattern ) ? $homepage_pattern : '';
	}

	/**
	 * Return a stable signature for managed seeded content.
	 *
	 * @param mixed $value Source value.
	 * @return string
	 */
	protected function get_seed_signature( $value ) {
		if ( is_string( $value ) ) {
			return hash( 'sha256', $value );
		}

		return hash( 'sha256', wp_json_encode( $value ) );
	}

	/**
	 * Return the current signature for seeded menu definitions.
	 *
	 * @return string
	 */
	protected function get_seeded_menu_signature() {
		return $this->get_seed_signature(
			array(
				'primary' => $this->get_default_primary_menu_items(),
				'footer'  => $this->get_default_footer_menu_items(),
			)
		);
	}

	/**
	 * Return the current signature for seeded footer widgets.
	 *
	 * @return string
	 */
	protected function get_seeded_footer_widget_signature() {
		return $this->get_seed_signature( $this->get_default_footer_widget_blocks() );
	}

	/**
	 * Register native shortcodes used by the managed homepage content.
	 *
	 * @return void
	 */
	public function register_shortcodes() {
		add_shortcode( 'mcprices_hero_search', array( $this, 'render_hero_search_shortcode' ) );
	}

	/**
	 * Render the native hero search markup outside of post-content KSES stripping.
	 *
	 * @return string
	 */
	public function render_hero_search_shortcode() {
		return '<form class="hero-search" role="search" data-mcprices-search>'
			. '<div class="hero-search-icon" aria-hidden="true">&#128269;</div>'
			. '<input type="search" name="mcprices_search" placeholder="Search for Big Mac, McFlurry, Happy Meal&hellip;" autocomplete="off" data-mcprices-search-input>'
			. '<button type="submit">Search</button>'
			. '</form>'
			. '<div class="hero-search-feedback" data-mcprices-search-feedback aria-live="polite"></div>';
	}

	/**
	 * Render a mobile-only search trigger in the Kadence mobile header while
	 * still using the managed homepage search experience.
	 *
	 * @param string $row Header row.
	 * @param string $column Header column.
	 * @return void
	 */
	public function render_mobile_header_search_toggle( $row, $column ) {
		if ( ! $this->design_enabled() || ! is_front_page() || is_home() ) {
			return;
		}

		if ( 'main' !== $row || 'right' !== $column ) {
			return;
		}
		?>
		<div class="site-header-item site-header-focus-item mcprices-mobile-search-header-item">
			<button
				type="button"
				class="mcprices-mobile-search-toggle"
				data-mcprices-mobile-search-toggle
				aria-expanded="false"
				aria-label="<?php esc_attr_e( 'Open menu search', 'kadence' ); ?>"
			>
				<span class="mcprices-mobile-search-toggle-icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
						<circle cx="11" cy="11" r="6.5"></circle>
						<path d="M16 16L21 21"></path>
					</svg>
				</span>
			</button>
		</div>
		<?php
	}

	/**
	 * Render a dedicated mobile search panel that expands from the header area
	 * instead of reusing the desktop hero search form.
	 *
	 * @return void
	 */
	public function render_mobile_search_panel() {
		if ( ! $this->design_enabled() || ! is_front_page() || is_home() ) {
			return;
		}
		?>
		<div class="mcprices-mobile-search-shell" data-mcprices-mobile-search-shell aria-hidden="true">
			<div class="mcprices-mobile-search-panel" data-mcprices-mobile-search-panel aria-hidden="true">
				<form class="mcprices-mobile-search-form" role="search" data-mcprices-search data-mcprices-search-mobile>
					<span class="mcprices-mobile-search-form__icon" aria-hidden="true">&#128269;</span>
					<input
						type="search"
						name="mcprices_search"
						placeholder="Search for Big Mac, McFlurry, Happy Meal&hellip;"
						autocomplete="off"
						data-mcprices-search-input
						data-mcprices-mobile-search-input
					>
					<button type="submit"><?php esc_html_e( 'Search', 'kadence' ); ?></button>
				</form>
				<div class="hero-search-feedback mcprices-mobile-search-feedback" data-mcprices-search-feedback aria-live="polite"></div>
			</div>
		</div>
		<?php
	}

	/**
	 * Filter managed homepage content so update dates stay current daily.
	 *
	 * @param string $content Rendered content.
	 * @return string
	 */
	public function filter_dynamic_date_content( $content ) {
		if ( ! $this->design_enabled() || ! is_front_page() || is_home() ) {
			return $content;
		}

		return $this->replace_dynamic_date_strings( $content );
	}

	/**
	 * Filter rendered block HTML that belongs to the managed McPrices surface.
	 *
	 * @param string $block_content Rendered block HTML.
	 * @param array  $block         Parsed block data.
	 * @return string
	 */
	public function filter_dynamic_date_block_html( $block_content, $block ) {
		unset( $block );

		if ( ! $this->design_enabled() ) {
			return $block_content;
		}

		if ( false === strpos( (string) $block_content, '2026' ) && false === strpos( (string) $block_content, 'April 2026' ) && false === strpos( (string) $block_content, 'Prices last verified:' ) ) {
			return $block_content;
		}

		return $this->replace_dynamic_date_strings( $block_content );
	}

	/**
	 * Keep the Kadence header update bar using the current date without forcing DB writes.
	 *
	 * @param string $content Theme mod HTML content.
	 * @return string
	 */
	public function filter_dynamic_update_bar_html( $content ) {
		if ( ! $this->design_enabled() || false === strpos( (string) $content, 'Prices last verified:' ) ) {
			return $content;
		}

		return $this->get_seeded_update_bar_html();
	}

	/**
	 * Return the default footer widget block markup.
	 *
	 * @return array
	 */
	protected function get_default_footer_widget_blocks() {
		return array(
			'footer1' => sprintf(
				'<!-- wp:html --><div class="footer-brand"><a href="%1$s" class="logo"><div class="logo-icon">M</div><div class="logo-text">McDonald&#8217;s Menu Prices USA<span>Independent Menu Guide</span></div></a><p>An independent website covering McDonald&#8217;s USA menu prices, calories, deals, breakfast hours, drinks, combo meals, and limited-time items. Not affiliated with McDonald&#8217;s Corporation.</p></div><!-- /wp:html -->',
				esc_url( $this->get_home_path_url() )
			),
			'footer2' => sprintf(
				'<!-- wp:html --><div class="footer-col-title">Menu Categories</div><ul class="footer-links"><li><a href="%1$s">What&#8217;s New</a></li><li><a href="%2$s">McValue</a></li><li><a href="%3$s">Breakfast</a></li><li><a href="%4$s">Burgers</a></li><li><a href="%5$s">Chicken &amp; Fish</a></li><li><a href="%6$s">McNuggets &amp; Strips</a></li><li><a href="%7$s">Snack Wrap</a></li><li><a href="%8$s">Fries &amp; Sides</a></li><li><a href="%9$s">Happy Meal</a></li><li><a href="%10$s">Sweets &amp; Treats</a></li><li><a href="%11$s">McCafe</a></li><li><a href="%12$s">Beverages</a></li><li><a href="%13$s">Extra Value Meals</a></li><li><a href="%14$s">Sauces &amp; Condiments</a></li></ul><!-- /wp:html -->',
				esc_url( $this->get_section_url( 'whats-new' ) ),
				esc_url( $this->get_section_url( 'mcvalue' ) ),
				esc_url( $this->get_section_url( 'breakfast' ) ),
				esc_url( $this->get_section_url( 'burgers' ) ),
				esc_url( $this->get_section_url( 'chickenfish' ) ),
				esc_url( $this->get_section_url( 'nuggets' ) ),
				esc_url( $this->get_section_url( 'snackwrap' ) ),
				esc_url( $this->get_section_url( 'sides' ) ),
				esc_url( $this->get_section_url( 'happymeal' ) ),
				esc_url( $this->get_section_url( 'sweets' ) ),
				esc_url( $this->get_section_url( 'mccafe' ) ),
				esc_url( $this->get_section_url( 'beverages' ) ),
				esc_url( $this->get_section_url( 'meals' ) ),
				esc_url( $this->get_section_url( 'sauces' ) ),
			),
			'footer3' => sprintf(
				'<!-- wp:html --><div class="footer-col-title">Information</div><ul class="footer-links"><li><a href="%1$s">About Us</a></li><li><a href="%2$s">Privacy Policy</a></li><li><a href="%3$s">Cookie Policy</a></li><li><a href="%4$s">Ad Disclosure</a></li><li><a href="%5$s">Disclaimer</a></li><li><a href="%6$s">Contact</a></li><li><a href="%7$s">Sitemap</a></li></ul><!-- /wp:html -->',
				esc_url( home_url( '/about/' ) ),
				esc_url( home_url( '/privacy-policy/' ) ),
				esc_url( home_url( '/cookie-policy/' ) ),
				esc_url( home_url( '/ad-disclosure/' ) ),
				esc_url( home_url( '/disclaimer/' ) ),
				esc_url( home_url( '/contact/' ) ),
				esc_url( home_url( '/sitemap/' ) )
			),
			'footer4' => sprintf(
				'<!-- wp:html --><div class="footer-col-title">Popular Guides</div><ul class="footer-links"><li><a href="%1$s">Big Mac Price USA</a></li><li><a href="%2$s">McDonald&#8217;s App Deals</a></li><li><a href="%3$s">Calorie Counter</a></li><li><a href="%4$s">Breakfast Hours</a></li><li><a href="%5$s">Allergen Guide</a></li><li><a href="%6$s">Price History</a></li><li><a href="%7$s">Rewards Guide</a></li><li><a href="%8$s">Limited-Time Menu</a></li><li><a href="%9$s">Delivery Guide</a></li><li><a href="%10$s">Breakfast Menu</a></li><li><a href="%11$s">Burgers Menu</a></li></ul><!-- /wp:html -->',
				esc_url( home_url( '/big-mac-price-usa/' ) ),
				esc_url( home_url( '/mcdonalds-app-deals/' ) ),
				esc_url( home_url( '/calorie-counter/' ) ),
				esc_url( home_url( '/breakfast-hours/' ) ),
				esc_url( home_url( '/allergen-guide/' ) ),
				esc_url( home_url( '/price-history/' ) ),
				esc_url( home_url( '/rewards-guide/' ) ),
				esc_url( home_url( '/limited-time-menu/' ) ),
				esc_url( home_url( '/delivery-guide/' ) ),
				esc_url( home_url( '/breakfast-menu/' ) ),
				esc_url( home_url( '/burgers-menu/' ) )
			),
		);
	}

	/**
	 * Return the default primary menu items.
	 *
	 * @return array
	 */
	protected function get_default_primary_menu_items() {
		return array(
			array(
				'title' => __( 'Home', 'kadence' ),
				'url'   => $this->get_home_path_url(),
			),
			array(
				'title' => __( 'What\'s New', 'kadence' ),
				'url'   => $this->get_section_url( 'whats-new' ),
			),
			array(
				'title' => __( 'Menu', 'kadence' ),
				'url'   => $this->get_section_url( 'full-menu' ),
			),
			array(
				'title' => __( 'Deals', 'kadence' ),
				'url'   => $this->get_section_url( 'deals' ),
			),
			array(
				'title' => __( 'Breakfast', 'kadence' ),
				'url'   => $this->get_section_url( 'breakfast' ),
			),
			array(
				'title' => __( 'Guides', 'kadence' ),
				'url'   => $this->get_section_url( 'guides' ),
			),
			array(
				'title' => __( 'Blogs', 'kadence' ),
				'url'   => $this->get_blog_url(),
			),
		);
	}

	/**
	 * Return the default footer legal menu items.
	 *
	 * @return array
	 */
	protected function get_default_footer_menu_items() {
		return array(
			array(
				'title' => __( 'Privacy', 'kadence' ),
				'url'   => home_url( '/privacy-policy/' ),
			),
			array(
				'title' => __( 'Cookies', 'kadence' ),
				'url'   => home_url( '/cookie-policy/' ),
			),
			array(
				'title' => __( 'Disclaimer', 'kadence' ),
				'url'   => home_url( '/disclaimer/' ),
			),
			array(
				'title' => __( 'Ad Policy', 'kadence' ),
				'url'   => home_url( '/ad-disclosure/' ),
			),
		);
	}

	/**
	 * Return the assigned primary menu items in menu order.
	 *
	 * @param int $limit Optional maximum number of items to return.
	 * @return array
	 */
	protected function get_primary_menu_items( $limit = 0 ) {
		$locations = get_nav_menu_locations();
		$menu_id   = isset( $locations['primary'] ) ? (int) $locations['primary'] : 0;

		if ( ! $menu_id ) {
			return array();
		}

		$menu_items = wp_get_nav_menu_items(
			$menu_id,
			array(
				'update_post_term_cache' => false,
			)
		);

		if ( empty( $menu_items ) || ! is_array( $menu_items ) ) {
			return array();
		}

		$top_level_items = array();
		foreach ( $menu_items as $menu_item ) {
			if ( 'publish' !== $menu_item->post_status || ! empty( $menu_item->menu_item_parent ) ) {
				continue;
			}

			$top_level_items[] = $menu_item;
		}

		usort(
			$top_level_items,
			static function ( $left, $right ) {
				return (int) $left->menu_order <=> (int) $right->menu_order;
			}
		);

		if ( $limit > 0 ) {
			$top_level_items = array_slice( $top_level_items, 0, $limit );
		}

		return $top_level_items;
	}

	/**
	 * Return the mobile quick-nav context key for a menu item.
	 *
	 * @param \WP_Post $item Menu item object.
	 * @return string
	 */
	protected function get_mobile_quick_nav_item_context( $item ) {
		$title = strtolower( wp_strip_all_tags( (string) $item->title ) );
		$url   = strtolower( (string) $item->url );
		$text  = trim( $title . ' ' . $url );

		if ( false !== strpos( $text, 'home' ) || '/' === trim( wp_parse_url( (string) $item->url, PHP_URL_PATH ) ?: '' ) ) {
			return 'home';
		}

		if ( false !== strpos( $text, 'menu' ) || false !== strpos( $text, 'full-menu' ) || false !== strpos( $text, 'price' ) ) {
			return 'menu';
		}

		if ( false !== strpos( $text, 'deal' ) || false !== strpos( $text, 'offer' ) || false !== strpos( $text, 'save' ) ) {
			return 'deals';
		}

		if ( false !== strpos( $text, 'new' ) || false !== strpos( $text, 'latest' ) || false !== strpos( $text, 'news' ) ) {
			return 'whats-new';
		}

		if ( false !== strpos( $text, 'blog' ) || false !== strpos( $text, 'guide' ) || false !== strpos( $text, 'article' ) ) {
			return 'guides';
		}

		if ( false !== strpos( $text, 'share' ) ) {
			return 'sharers';
		}

		return 'default';
	}

	/**
	 * Return the inline SVG icon for a mobile quick-nav item.
	 *
	 * @param string $context Menu context key.
	 * @return string
	 */
	protected function get_mobile_quick_nav_icon( $context ) {
		switch ( $context ) {
			case 'home':
				return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3.4L3.8 10v10.1c0 .6.5 1.1 1.1 1.1h5.4v-6.1h3.4v6.1h5.4c.6 0 1.1-.5 1.1-1.1V10L12 3.4z"/></svg>';
			case 'menu':
				return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 5.2h12a1.2 1.2 0 010 2.4H6a1.2 1.2 0 010-2.4zm0 5.3h12a1.2 1.2 0 010 2.4H6a1.2 1.2 0 010-2.4zm0 5.3h8.2a1.2 1.2 0 010 2.4H6a1.2 1.2 0 010-2.4z"/></svg>';
			case 'deals':
				return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.3 10.1l-6.4-6.4a1.7 1.7 0 00-1.2-.5H6.2c-.9 0-1.7.8-1.7 1.7v6.5c0 .5.2.9.5 1.2l6.4 6.4c.7.7 1.7.7 2.4 0l6.5-6.5c.7-.7.7-1.8 0-2.4zM8.2 9.3a1.6 1.6 0 110-3.2 1.6 1.6 0 010 3.2z"/></svg>';
			case 'whats-new':
				return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12.7 2.9l1.9 4.1 4.5.6c.9.1 1.3 1.2.7 1.8l-3.3 3.2.8 4.5c.2.9-.8 1.6-1.6 1.2L12 16.1l-4 2.2c-.8.4-1.8-.3-1.6-1.2l.8-4.5-3.3-3.2c-.6-.6-.3-1.7.7-1.8l4.5-.6 1.9-4.1c.4-.8 1.5-.8 1.9 0z"/></svg>';
			case 'guides':
				return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6.4 4h8.8c1.6 0 2.8 1.3 2.8 2.8V20l-5.2-2.4L7.6 20V6.8C7.6 5.3 8.8 4 10.4 4H6.4z"/></svg>';
			case 'sharers':
				return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8.1 11.2a3.1 3.1 0 100-6.2 3.1 3.1 0 000 6.2zm7.8 0a3.1 3.1 0 100-6.2 3.1 3.1 0 000 6.2zm-7.8 1.6c-2.8 0-5.1 1.7-5.1 3.8V19h10.2v-2.4c0-2.1-2.3-3.8-5.1-3.8zm7.8.3c-.5 0-1 .1-1.5.2 1.1.9 1.8 2 1.8 3.3V19H21v-1.5c0-2.4-2.3-4.4-5.1-4.4z"/></svg>';
			default:
				return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3.6a8.4 8.4 0 108.4 8.4A8.4 8.4 0 0012 3.6zm0 13.7a5.3 5.3 0 115.3-5.3 5.3 5.3 0 01-5.3 5.3zm0-8.4a3.1 3.1 0 103.1 3.1A3.1 3.1 0 0012 8.9z"/></svg>';
		}
	}

	/**
	 * Render a dynamic mobile quick nav from the first primary menu items so
	 * admin menu changes flow through automatically.
	 *
	 * @return void
	 */
	public function render_mobile_quick_nav() {
		if ( ! $this->design_enabled() ) {
			return;
		}

		$items = $this->get_primary_menu_items( 4 );
		if ( empty( $items ) ) {
			return;
		}
		?>
		<nav class="mcprices-mobile-quick-nav" aria-label="<?php esc_attr_e( 'Mobile quick links', 'kadence' ); ?>">
			<div class="mcprices-mobile-quick-nav__inner">
				<?php foreach ( $items as $index => $item ) : ?>
					<?php $context = $this->get_mobile_quick_nav_item_context( $item ); ?>
					<a
						class="mcprices-mobile-quick-nav__link mcprices-mobile-quick-nav__link--<?php echo esc_attr( $context ); ?>"
						href="<?php echo esc_url( $item->url ); ?>"
					>
						<span class="mcprices-mobile-quick-nav__icon" aria-hidden="true"><?php echo $this->get_mobile_quick_nav_icon( $context ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<span class="mcprices-mobile-quick-nav__label"><?php echo esc_html( $item->title ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		</nav>
		<?php
	}

	/**
	 * Return the Kadence theme-mod keys that should be imported once so the
	 * parent theme immediately adopts the attached design on existing installs.
	 *
	 * @return array
	 */
	protected function get_seeded_theme_mod_keys() {
		return array(
			'base_font',
			'heading_font',
			'h1_font',
			'h2_font',
			'h3_font',
			'h4_font',
			'link_color',
			'scroll_up',
			'header_desktop_items',
			'header_mobile_items',
			'header_top_height',
			'header_main_height',
			'header_html_content',
			'header_html_wpautop',
			'mobile_html_content',
			'mobile_html_wpautop',
			'header_button_label',
			'header_button_link',
			'header_button_style',
			'header_button_size',
			'header_button_padding',
			'header_button_radius',
			'header_button_color',
			'header_button_background',
			'header_button_shadow',
			'header_button_shadow_hover',
			'primary_navigation_typography',
			'primary_navigation_color',
			'primary_navigation_background',
			'mobile_navigation_color',
			'header_popup_background',
			'header_popup_close_color',
			'transparent_header_enable',
			'transparent_header_page',
			'transparent_header_post',
			'transparent_header_archive',
			'transparent_header_device',
			'transparent_header_site_title_color',
			'transparent_header_navigation_color',
			'transparent_header_html_color',
			'header_sticky',
			'mobile_header_sticky',
			'footer_items',
			'footer_wrap_background',
			'footer_middle_background',
			'footer_bottom_background',
			'footer_middle_columns',
			'footer_bottom_columns',
			'footer_html_content',
		);
	}

	/**
	 * Filter Kadence option defaults so the native builder starts in the attached layout.
	 *
	 * Saved site options still win; these are only defaults.
	 *
	 * @param array $defaults Existing defaults.
	 * @return array
	 */
	public function filter_kadence_defaults( $defaults ) {
		$defaults['base_font'] = array(
			'size'       => array(
				'desktop' => 16,
			),
			'lineHeight' => array(
				'desktop' => 1.65,
			),
			'family'     => 'DM Sans',
			'google'     => false,
			'weight'     => '400',
			'variant'    => '400',
			'color'      => 'palette4',
		);
		$defaults['heading_font'] = array(
			'family'  => 'Poppins',
			'google'  => false,
			'weight'  => '800',
			'variant' => '800',
		);
		$defaults['h1_font']      = array(
			'size'       => array(
				'desktop' => 42,
			),
			'lineHeight' => array(
				'desktop' => 1.15,
			),
			'family'     => 'Poppins',
			'google'     => false,
			'weight'     => '800',
			'variant'    => '800',
			'color'      => 'palette3',
		);
		$defaults['h2_font']      = array(
			'size'       => array(
				'desktop' => 34,
			),
			'lineHeight' => array(
				'desktop' => 1.15,
			),
			'family'     => 'Poppins',
			'google'     => false,
			'weight'     => '800',
			'variant'    => '800',
			'color'      => 'palette3',
		);
		$defaults['h3_font']      = array(
			'size'       => array(
				'desktop' => 26,
			),
			'lineHeight' => array(
				'desktop' => 1.2,
			),
			'family'     => 'Poppins',
			'google'     => false,
			'weight'     => '700',
			'variant'    => '700',
			'color'      => 'palette3',
		);
		$defaults['h4_font']      = array(
			'size'       => array(
				'desktop' => 22,
			),
			'lineHeight' => array(
				'desktop' => 1.2,
			),
			'family'     => 'Poppins',
			'google'     => false,
			'weight'     => '700',
			'variant'    => '700',
			'color'      => 'palette4',
		);
		$defaults['link_color']   = array(
			'highlight'      => 'palette1',
			'highlight-alt'  => 'palette2',
			'highlight-alt2' => 'palette9',
			'style'          => 'standard',
		);
		$defaults['scroll_up']    = true;

		$defaults['header_desktop_items'] = array(
			'top'    => array(
				'top_left'         => array(),
				'top_left_center'  => array(),
				'top_center'       => array( 'html' ),
				'top_right_center' => array(),
				'top_right'        => array(),
			),
			'main'   => array(
				'main_left'         => array( 'logo' ),
				'main_left_center'  => array(),
				'main_center'       => array( 'navigation' ),
				'main_right_center' => array(),
				'main_right'        => array( 'button' ),
			),
			'bottom' => array(
				'bottom_left'         => array(),
				'bottom_left_center'  => array(),
				'bottom_center'       => array(),
				'bottom_right_center' => array(),
				'bottom_right'        => array(),
			),
		);
		$defaults['header_mobile_items']  = array(
			'popup'  => array(
				'popup_content' => array( 'mobile-navigation' ),
			),
			'top'    => array(
				'top_left'   => array(),
				'top_center' => array( 'mobile-html' ),
				'top_right'  => array(),
			),
			'main'   => array(
				'main_left'   => array( 'mobile-logo' ),
				'main_center' => array(),
				'main_right'  => array( 'popup-toggle' ),
			),
			'bottom' => array(
				'bottom_left'   => array(),
				'bottom_center' => array(),
				'bottom_right'  => array(),
			),
		);

		$defaults['header_top_height'] = array(
			'size' => array(
				'mobile'  => 40,
				'tablet'  => 40,
				'desktop' => 40,
			),
			'unit' => array(
				'mobile'  => 'px',
				'tablet'  => 'px',
				'desktop' => 'px',
			),
		);
		$defaults['header_main_height'] = array(
			'size' => array(
				'mobile'  => 72,
				'tablet'  => 72,
				'desktop' => 72,
			),
			'unit' => array(
				'mobile'  => 'px',
				'tablet'  => 'px',
				'desktop' => 'px',
			),
		);

		$defaults['header_html_content'] = $this->get_seeded_update_bar_html();
		$defaults['header_html_wpautop'] = false;
		$defaults['mobile_html_content'] = $this->get_seeded_update_bar_html();
		$defaults['mobile_html_wpautop'] = false;

		$defaults['header_button_label']    = __( 'View Prices', 'kadence' );
		$defaults['header_button_link']     = $this->get_section_url( 'full-menu' );
		$defaults['header_button_style']    = 'custom';
		$defaults['header_button_size']     = 'custom';
		$defaults['header_button_padding']  = array(
			'size'   => array( 10, 22, 10, 22 ),
			'unit'   => 'px',
			'locked' => false,
		);
		$defaults['header_button_radius']   = array(
			'size'   => array( 10, 10, 10, 10 ),
			'unit'   => 'px',
			'locked' => true,
		);
		$defaults['header_button_color']    = array(
			'color' => '#ffffff',
			'hover' => '#ffffff',
		);
		$defaults['header_button_background'] = array(
			'color' => '#C8102E',
			'hover' => '#a50d24',
		);
		$defaults['header_button_shadow']   = array(
			'color'    => 'rgba(200,16,46,0.25)',
			'hOffset'  => 0,
			'vOffset'  => 8,
			'blur'     => 30,
			'spread'   => -6,
			'inset'    => false,
			'disabled' => false,
		);
		$defaults['header_button_shadow_hover'] = array(
			'color'    => 'rgba(200,16,46,0.35)',
			'hOffset'  => 0,
			'vOffset'  => 12,
			'blur'     => 34,
			'spread'   => -8,
			'inset'    => false,
			'disabled' => false,
		);
		$defaults['primary_navigation_typography'] = array(
			'size'       => array(
				'desktop' => 14,
			),
			'lineHeight' => array(
				'desktop' => 1.2,
			),
			'family'     => 'Poppins',
			'google'     => false,
			'weight'     => '600',
			'variant'    => '600',
		);
		$defaults['primary_navigation_color'] = array(
			'color'  => '#444440',
			'hover'  => '#C8102E',
			'active' => '#C8102E',
		);
		$defaults['primary_navigation_background'] = array(
			'color'  => '',
			'hover'  => 'rgba(200,16,46,0.08)',
			'active' => 'rgba(200,16,46,0.08)',
		);
		$defaults['mobile_navigation_color'] = array(
			'color'  => '#ffffff',
			'hover'  => '#FFC72C',
			'active' => '#FFC72C',
		);
		$defaults['header_popup_background'] = array(
			'desktop' => array(
				'color' => '#1a1a18',
			),
		);
		$defaults['header_popup_close_color'] = array(
			'color' => '#ffffff',
			'hover' => '#FFC72C',
		);

		$defaults['transparent_header_enable']  = true;
		$defaults['transparent_header_page']    = false;
		$defaults['transparent_header_post']    = true;
		$defaults['transparent_header_archive'] = true;
		$defaults['transparent_header_device']  = array(
			'desktop' => true,
			'mobile'  => true,
		);
		$defaults['transparent_header_site_title_color'] = array(
			'color' => '#ffffff',
		);
		$defaults['transparent_header_navigation_color'] = array(
			'color'  => '#ffffff',
			'hover'  => '#FFC72C',
			'active' => '#FFC72C',
		);
		$defaults['transparent_header_html_color'] = array(
			'color' => '#ffffff',
			'link'  => '#FFC72C',
			'hover' => '#FFC72C',
		);
		$defaults['header_sticky']        = 'main';
		$defaults['mobile_header_sticky'] = 'main';

		$defaults['footer_items'] = array(
			'top'    => array(
				'top_1' => array(),
				'top_2' => array(),
				'top_3' => array(),
				'top_4' => array(),
				'top_5' => array(),
			),
			'middle' => array(
				'middle_1' => array( 'footer-widget1' ),
				'middle_2' => array( 'footer-widget2' ),
				'middle_3' => array( 'footer-widget3' ),
				'middle_4' => array( 'footer-widget4' ),
				'middle_5' => array(),
			),
			'bottom' => array(
				'bottom_1' => array( 'footer-html' ),
				'bottom_2' => array(),
				'bottom_3' => array(),
				'bottom_4' => array( 'footer-navigation' ),
				'bottom_5' => array(),
			),
		);
		$defaults['footer_wrap_background']   = array(
			'desktop' => array(
				'color' => '#1a1a18',
			),
		);
		$defaults['footer_middle_background'] = array(
			'desktop' => array(
				'color' => '#1a1a18',
			),
		);
		$defaults['footer_bottom_background'] = array(
			'desktop' => array(
				'color' => '#1a1a18',
			),
		);
		$defaults['footer_middle_columns']    = '4';
		$defaults['footer_bottom_columns']    = '2';
		$defaults['footer_html_content']      = '<p>&copy; {year} McDonald&#8217;s Menu Prices USA. Independent menu guide. Not affiliated with McDonald&#8217;s.</p>';

		return $defaults;
	}

	/**
	 * Filter the default Kadence palette so a fresh layout starts in the attached brand colors.
	 *
	 * @param string $palette_json Existing palette JSON.
	 * @return string
	 */
	public function filter_palette_defaults( $palette_json ) {
		$palette = array(
			'palette'        => array(
				array( 'color' => '#C8102E', 'slug' => 'palette1', 'name' => 'Palette Color 1' ),
				array( 'color' => '#FFC72C', 'slug' => 'palette2', 'name' => 'Palette Color 2' ),
				array( 'color' => '#1a1a18', 'slug' => 'palette3', 'name' => 'Palette Color 3' ),
				array( 'color' => '#444440', 'slug' => 'palette4', 'name' => 'Palette Color 4' ),
				array( 'color' => '#888884', 'slug' => 'palette5', 'name' => 'Palette Color 5' ),
				array( 'color' => '#d0d0cc', 'slug' => 'palette6', 'name' => 'Palette Color 6' ),
				array( 'color' => '#f2f2f0', 'slug' => 'palette7', 'name' => 'Palette Color 7' ),
				array( 'color' => '#f8f8f6', 'slug' => 'palette8', 'name' => 'Palette Color 8' ),
				array( 'color' => '#ffffff', 'slug' => 'palette9', 'name' => 'Palette Color 9' ),
				array( 'color' => '#ffffff', 'slug' => 'palette10', 'name' => 'Palette Color Complement' ),
				array( 'color' => '#00a651', 'slug' => 'palette11', 'name' => 'Palette Color Success' ),
				array( 'color' => '#1159af', 'slug' => 'palette12', 'name' => 'Palette Color Info' ),
				array( 'color' => '#b82105', 'slug' => 'palette13', 'name' => 'Palette Color Alert' ),
				array( 'color' => '#f7630c', 'slug' => 'palette14', 'name' => 'Palette Color Warning' ),
				array( 'color' => '#f5a524', 'slug' => 'palette15', 'name' => 'Palette Color Rating' ),
			),
			'second-palette' => array(),
			'third-palette'  => array(),
			'active'         => 'palette',
		);

		$palette['second-palette'] = $palette['palette'];
		$palette['third-palette']  = $palette['palette'];

		return wp_json_encode( $palette );
	}

	/**
	 * Adjust the front page layout so the homepage pattern can take over the full canvas.
	 *
	 * @param array $layout Layout data.
	 * @return array
	 */
	public function filter_front_page_layout( $layout ) {
		if ( ! $this->design_enabled() || ! is_front_page() || is_home() ) {
			return $layout;
		}

		$layout['layout']      = 'fullwidth';
		$layout['boxed']       = 'unboxed';
		$layout['title']       = 'hide';
		$layout['feature']     = 'hide';
		$layout['sidebar']     = 'disable';
		$layout['vpadding']    = 'hide';
		$layout['transparent'] = 'enable';

		return $layout;
	}

	/**
	 * Add a body class that scopes the site-wide header/footer layer.
	 *
	 * @param array $classes Existing body classes.
	 * @return array
	 */
	public function filter_body_classes( $classes ) {
		if ( $this->design_enabled() ) {
			$classes[] = 'mcprices-theme-active';
		}

		return $classes;
	}

	/**
	 * Improve font loading for the design typography.
	 *
	 * @param array  $hints URLs to print for resource hints.
	 * @param string $relation_type Hint relation type.
	 * @return array
	 */
	public function filter_resource_hints( $hints, $relation_type ) {
		if ( 'preconnect' !== $relation_type ) {
			return $hints;
		}

		$hints[] = 'https://fonts.googleapis.com';
		$hints[] = array(
			'href'        => 'https://fonts.gstatic.com',
			'crossorigin' => 'anonymous',
		);

		return $hints;
	}

	/**
	 * Return whether the current request is the managed McPrices homepage.
	 *
	 * @return bool
	 */
	protected function is_seo_homepage() {
		return $this->design_enabled() && is_front_page() && ! is_home();
	}

	/**
	 * Return the homepage primary keyword.
	 *
	 * @return string
	 */
	protected function get_homepage_primary_keyword() {
		return "McDonald's Menu Prices USA " . $this->get_current_site_year();
	}

	/**
	 * Return the structured-data organization name.
	 *
	 * @return string
	 */
	protected function get_schema_organization_name() {
		return "McDonald's Menu Prices USA";
	}

	/**
	 * Return the structured-data site description.
	 *
	 * @return string
	 */
	protected function get_schema_site_description() {
		return "McDonald's Menu Prices USA is an independent guide covering McDonald's USA menu prices, calories, breakfast hours, McValue deals, combo meals, drinks, desserts, and FAQs with a current " . $this->get_current_site_date() . ' update.';
	}

	/**
	 * Return the homepage SEO title.
	 *
	 * @return string
	 */
	protected function get_homepage_meta_title() {
		return $this->get_homepage_primary_keyword() . ' | Full Price List & Calories';
	}

	/**
	 * Return the homepage SEO meta description.
	 *
	 * @return string
	 */
	protected function get_homepage_meta_description() {
		return "Complete McDonald's USA menu prices updated " . $this->get_current_site_date() . '. Find prices for burgers, breakfast, McCafe, drinks, McValue deals, McNuggets, Happy Meals, desserts, and combo meals in dollars.';
	}

	/**
	 * Return the current homepage HTML so schema can stay aligned with the
	 * editable front-page content instead of a hard-coded duplicate.
	 *
	 * @return string
	 */
	protected function get_homepage_seo_html() {
		static $html = null;

		if ( null !== $html ) {
			return $html;
		}

		$front_page_id = (int) get_option( 'page_on_front' );
		$html          = $front_page_id ? (string) get_post_field( 'post_content', $front_page_id ) : '';

		if ( '' === trim( wp_strip_all_tags( $html ) ) ) {
			$html = (string) require get_theme_file_path( '/inc/mcprices/pattern-homepage.php' );
		}

		$html = $this->replace_dynamic_date_strings( $html );

		if ( false !== strpos( $html, '[mcprices_' ) ) {
			$html = do_shortcode( $html );
		}

		return (string) $html;
	}

	/**
	 * Return a DOMXPath parser for the current homepage HTML.
	 *
	 * @return \DOMXPath|null
	 */
	protected function get_homepage_dom_xpath() {
		static $xpath = null;

		if ( null !== $xpath ) {
			return $xpath;
		}

		if ( ! class_exists( '\DOMDocument' ) || ! class_exists( '\DOMXPath' ) ) {
			return null;
		}

		$html     = $this->get_homepage_seo_html();
		$document = new \DOMDocument( '1.0', 'UTF-8' );
		$wrapped  = '<!DOCTYPE html><html><body><div id="mcprices-seo-root">' . $html . '</div></body></html>';
		$previous = libxml_use_internal_errors( true );

		$document->loadHTML( '<?xml encoding="utf-8" ?>' . $wrapped );
		libxml_clear_errors();
		libxml_use_internal_errors( $previous );

		$xpath = new \DOMXPath( $document );

		return $xpath;
	}

	/**
	 * Return an XPath class matcher expression.
	 *
	 * @param string $class_name CSS class name.
	 * @return string
	 */
	protected function get_xpath_class_selector( $class_name ) {
		return "contains(concat(' ', normalize-space(@class), ' '), ' {$class_name} ')";
	}

	/**
	 * Return normalized text from an XPath node query.
	 *
	 * @param \DOMXPath $xpath XPath helper.
	 * @param \DOMNode  $context Query context node.
	 * @param string    $query Relative XPath query.
	 * @return string
	 */
	protected function get_xpath_node_text( $xpath, $context, $query ) {
		$node = $xpath->query( $query, $context )->item(0);
		if ( ! $node ) {
			return '';
		}

		return trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( (string) $node->textContent ) ) );
	}

	/**
	 * Return the FAQ items currently shown on the homepage.
	 *
	 * @return array
	 */
	protected function get_homepage_faq_items() {
		return $this->get_homepage_faq_schema_items();

		static $faq_items = null;

		if ( null !== $faq_items ) {
			return $faq_items;
		}

		$current_date = $this->get_current_site_date();

		$faq_items = array(
			array(
				'question' => 'How much is a Big Mac in the UK?',
				'answer'   => 'A Big Mac costs £5.09 in this ' . $current_date . ' update. Meal pricing can vary slightly by restaurant and local offer.',
			),
			array(
				'question' => 'What is on the McValue menu right now?',
				'answer'   => 'The current McValue lineup highlighted on the site includes $5 meal deals, buy one add one for $1 breakfast offers, lunch and dinner add-on offers, and low-entry items such as McChicken, McDouble, McNuggets, fries, and hash browns.',
			),
			array(
				'question' => 'How much is a Happy Meal in the UK?',
				'answer'   => 'Most current Happy Meal options are £3.89 in this ' . $current_date . ' update, including Hamburger, Cheeseburger, Mayo Chicken and 4-piece Chicken McNuggets Happy Meals.',
			),
		);
		$xpath     = $this->get_homepage_dom_xpath();

		if ( ! $xpath ) {
			return $faq_items;
		}

		$faq_nodes = $xpath->query( '//div[' . $this->get_xpath_class_selector( 'faq-item' ) . ']' );
		foreach ( $faq_nodes as $faq_node ) {
			$question = $this->get_xpath_node_text( $xpath, $faq_node, './/div[' . $this->get_xpath_class_selector( 'faq-q' ) . ']' );
			$answer   = $this->get_xpath_node_text( $xpath, $faq_node, './/div[' . $this->get_xpath_class_selector( 'faq-a' ) . ']' );

			$question = trim( preg_replace( '/\s*\+\s*$/u', '', $question ) );

			if ( '' === $question || '' === $answer ) {
				continue;
			}

			$faq_items[] = array(
				'question' => $question,
				'answer'   => $answer,
			);
		}

		$unique_faq_items = array();
		$seen_questions   = array();

		foreach ( $faq_items as $faq_item ) {
			$key = strtolower( trim( preg_replace( '/[^a-z0-9]+/i', ' ', remove_accents( (string) $faq_item['question'] ) ) ) );

			if ( '' === $key || isset( $seen_questions[ $key ] ) ) {
				continue;
			}

			$seen_questions[ $key ] = true;
			$unique_faq_items[]     = $faq_item;
		}

		$faq_items = $unique_faq_items;

		return $faq_items;
	}

	/**
	 * Return the exact homepage FAQ items used for FAQPage schema.
	 *
	 * @return array
	 */
	protected function get_homepage_faq_schema_items() {
		$current_date = $this->get_current_site_date();

		return array(
			array(
				'question' => 'How much is a Big Mac in the USA?',
				'answer'   => 'The current USA menu data on this site lists the Big Mac at $5.99 on the core burger menu. Local restaurant, app, tax, and delivery pricing can still change the final total.',
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
				'answer'   => 'Large World Famous Fries are listed at 480 calories in the current USA menu data on this site.',
			),
			array(
				'question' => 'Does McDonald\'s USA have a vegan burger?',
				'answer'   => 'This USA menu build does not currently show a national vegan burger on the main McDonald\'s USA lineup. Most location coverage is centered on burgers, chicken, breakfast, fries, coffee, and desserts.',
			),
			array(
				'question' => 'How much is a McFlurry in the USA?',
				'answer'   => 'The current USA sweets data lists a regular OREO McFlurry at $5.59, a regular M&M\'s McFlurry at $5.59, and mini McFlurry options at $3.19 in the McValue section.',
			),
			array(
				'question' => 'What is the cheapest item on the McDonald\'s USA menu?',
				'answer'   => 'The lowest paid items in the current USA menu data on this site are Vanilla Cone at $1.29 and the Honest Kids Appley Ever After juice box at $1.29, followed by several $1.69 drink options.',
			),
		);
	}

	/**
	 * Return the absolute current request URL for structured data.
	 *
	 * @return string
	 */
	protected function get_current_request_url() {
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';

		if ( is_string( $request_uri ) && '' !== $request_uri ) {
			return esc_url_raw( home_url( $request_uri ) );
		}

		return esc_url_raw( home_url( '/' ) );
	}

	/**
	 * Return an absolute section URL for schema references.
	 *
	 * @param string $section Section hash.
	 * @return string
	 */
	protected function get_schema_section_url( $section ) {
		return esc_url_raw( trailingslashit( home_url( '/' ) ) . '#' . ltrim( $section, '#' ) );
	}

	/**
	 * Return breadcrumb items for the current inner page.
	 *
	 * @return array
	 */
	protected function get_breadcrumb_schema_items() {
		$items    = array();
		$home_url = esc_url_raw( home_url( '/' ) );

		$items[] = array(
			'name' => 'Home',
			'url'  => $home_url,
		);

		if ( is_front_page() ) {
			$items[] = array(
				'name' => $this->get_schema_organization_name(),
				'url'  => $home_url,
			);

			return $items;
		}

		if ( is_home() ) {
			$posts_page_id = (int) get_option( 'page_for_posts' );
			$items[]       = array(
				'name' => $posts_page_id ? get_the_title( $posts_page_id ) : 'Blog',
				'url'  => $posts_page_id ? esc_url_raw( get_permalink( $posts_page_id ) ) : esc_url_raw( $this->get_current_request_url() ),
			);

			return $items;
		}

		if ( is_page() ) {
			$page = get_queried_object();

			if ( $page instanceof \WP_Post ) {
				$ancestors = array_reverse( get_post_ancestors( $page ) );

				foreach ( $ancestors as $ancestor_id ) {
					$items[] = array(
						'name' => get_the_title( $ancestor_id ),
						'url'  => esc_url_raw( get_permalink( $ancestor_id ) ),
					);
				}

				$items[] = array(
					'name' => get_the_title( $page ),
					'url'  => esc_url_raw( get_permalink( $page ) ),
				);
			}

			return $items;
		}

		if ( is_single() ) {
			$post = get_queried_object();

			if ( $post instanceof \WP_Post ) {
				$post_type = get_post_type( $post );

				if ( 'post' === $post_type ) {
					$posts_page_id = (int) get_option( 'page_for_posts' );

					if ( $posts_page_id ) {
						$items[] = array(
							'name' => get_the_title( $posts_page_id ),
							'url'  => esc_url_raw( get_permalink( $posts_page_id ) ),
						);
					}
				} else {
					$post_type_object = get_post_type_object( $post_type );
					$archive_link     = get_post_type_archive_link( $post_type );

					if ( $post_type_object && $archive_link ) {
						$items[] = array(
							'name' => $post_type_object->labels->name,
							'url'  => esc_url_raw( $archive_link ),
						);
					}
				}

				$items[] = array(
					'name' => get_the_title( $post ),
					'url'  => esc_url_raw( get_permalink( $post ) ),
				);
			}

			return $items;
		}

		if ( is_category() || is_tag() || is_tax() ) {
			$term = get_queried_object();

			if ( $term instanceof \WP_Term ) {
				$posts_page_id = (int) get_option( 'page_for_posts' );

				if ( $posts_page_id && in_array( $term->taxonomy, array( 'category', 'post_tag' ), true ) ) {
					$items[] = array(
						'name' => get_the_title( $posts_page_id ),
						'url'  => esc_url_raw( get_permalink( $posts_page_id ) ),
					);
				}

				$ancestors = array_reverse( get_ancestors( $term->term_id, $term->taxonomy ) );
				foreach ( $ancestors as $ancestor_id ) {
					$ancestor = get_term( $ancestor_id, $term->taxonomy );

					if ( ! $ancestor || is_wp_error( $ancestor ) ) {
						continue;
					}

					$ancestor_link = get_term_link( $ancestor );

					if ( is_wp_error( $ancestor_link ) ) {
						continue;
					}

					$items[] = array(
						'name' => $ancestor->name,
						'url'  => esc_url_raw( $ancestor_link ),
					);
				}

				$term_link = get_term_link( $term );

				if ( ! is_wp_error( $term_link ) ) {
					$items[] = array(
						'name' => $term->name,
						'url'  => esc_url_raw( $term_link ),
					);
				}
			}

			return $items;
		}

		if ( is_post_type_archive() ) {
			$post_type = get_query_var( 'post_type' );
			$post_type = is_array( $post_type ) ? reset( $post_type ) : $post_type;

			if ( is_string( $post_type ) && '' !== $post_type ) {
				$post_type_object = get_post_type_object( $post_type );
				$archive_link     = get_post_type_archive_link( $post_type );

				if ( $post_type_object && $archive_link ) {
					$items[] = array(
						'name' => $post_type_object->labels->name,
						'url'  => esc_url_raw( $archive_link ),
					);
				}
			}

			return $items;
		}

		if ( is_search() ) {
			$items[] = array(
				'name' => 'Search results for: ' . get_search_query(),
				'url'  => esc_url_raw( $this->get_current_request_url() ),
			);

			return $items;
		}

		if ( is_404() ) {
			$items[] = array(
				'name' => 'Not Found',
				'url'  => esc_url_raw( $this->get_current_request_url() ),
			);

			return $items;
		}

		$items[] = array(
			'name' => wp_get_document_title(),
			'url'  => esc_url_raw( $this->get_current_request_url() ),
		);

		return $items;
	}

	/**
	 * Return the parsed price value from a text string.
	 *
	 * @param string $price_text Price text.
	 * @return string
	 */
	protected function parse_schema_price( $price_text ) {
		$decoded = html_entity_decode( (string) $price_text, ENT_QUOTES | ENT_HTML5, 'UTF-8' );

		if ( preg_match( '/([0-9]+(?:\.[0-9]{1,2})?)/', $decoded, $matches ) ) {
			return $matches[1];
		}

		return '';
	}

	/**
	 * Return the parsed calorie value from a text string.
	 *
	 * @param string $calorie_text Calorie text.
	 * @return string
	 */
	protected function parse_schema_calories( $calorie_text ) {
		if ( preg_match( '/([\d,]+)\s*kcal/i', (string) $calorie_text, $matches ) ) {
			return str_replace( ',', '', $matches[1] );
		}

		return '';
	}

	/**
	 * Normalize an item key to match the generated media manifest.
	 *
	 * @param string $value Raw item name.
	 * @return string
	 */
	protected function normalize_media_key( $value ) {
		$normalized = html_entity_decode( (string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		$normalized = remove_accents( $normalized );
		$normalized = strtolower( $normalized );
		$normalized = str_replace( '&', ' and ', $normalized );
		$normalized = str_replace( array( '®', '™', '’', '\'' ), '', $normalized );
		$normalized = preg_replace( '/\b(limited time only|here to stay)\b/', ' ', $normalized );
		$normalized = preg_replace( '/[^a-z0-9]+/', ' ', $normalized );
		$normalized = preg_replace( '/\btm\b/', ' ', $normalized );

		return trim( preg_replace( '/\s+/', ' ', (string) $normalized ) );
	}

	/**
	 * Return the generated media manifest data.
	 *
	 * @return array
	 */
	protected function get_media_manifest() {
		static $media_manifest = null;

		if ( null !== $media_manifest ) {
			return $media_manifest;
		}

		$media_manifest_path = get_theme_file_path( '/assets/data/mcprices-media-manifest.json' );
		$media_manifest      = array();

		if ( file_exists( $media_manifest_path ) ) {
			$decoded = json_decode( (string) file_get_contents( $media_manifest_path ), true );
			if ( is_array( $decoded ) ) {
				$media_manifest = $decoded;
			}
		}

		return $media_manifest;
	}

	/**
	 * Return the relative media path for a named product.
	 *
	 * @param string $item_name Product name.
	 * @return string
	 */
	protected function get_item_media_relative_path( $item_name ) {
		$manifest = $this->get_media_manifest();
		$items    = isset( $manifest['items'] ) && is_array( $manifest['items'] ) ? $manifest['items'] : array();

		if ( empty( $items ) || '' === trim( (string) $item_name ) ) {
			return '';
		}

		$normalized     = $this->normalize_media_key( $item_name );
		$stripped_paren = $this->normalize_media_key( preg_replace( '/\([^)]*\)/', ' ', (string) $item_name ) );
		$no_mcdonalds   = preg_replace( '/^mcdonalds\s+/', '', $normalized );
		$no_size        = $this->normalize_media_key( preg_replace( '/\b(regular|mini|small|medium|large|selected|restaurants)\b/', ' ', $normalized ) );
		$variants       = array_filter(
			array(
				$normalized,
				$stripped_paren,
				$no_mcdonalds,
				$no_size,
			)
		);

		if ( preg_match( '/^mcdonalds fries (small|medium|large)$/', $normalized, $fries_match ) ) {
			$variants[] = $this->normalize_media_key( 'fries ' . $fries_match[1] );
			$variants[] = $this->normalize_media_key( $fries_match[1] . ' fries' );
		}

		$variants = array_values( array_unique( $variants ) );

		foreach ( $variants as $variant ) {
			if ( isset( $items[ $variant ] ) ) {
				return (string) $items[ $variant ];
			}
		}

		return '';
	}

	/**
	 * Return the public media URL for a named product.
	 *
	 * @param string $item_name Product name.
	 * @return string
	 */
	protected function get_item_media_url( $item_name ) {
		$relative_path = $this->get_item_media_relative_path( $item_name );
		if ( '' === $relative_path ) {
			return '';
		}

		return trailingslashit( get_theme_file_uri( '/assets/images/mcprices/official' ) ) . ltrim( $relative_path, '/' );
	}

	/**
	 * Return the popular product data shown in the homepage hero.
	 *
	 * @return array
	 */
	protected function get_homepage_popular_products() {
		static $products = null;

		if ( null !== $products ) {
			return $products;
		}

		$products = array();
		$xpath    = $this->get_homepage_dom_xpath();

		if ( ! $xpath ) {
			return $products;
		}

		$product_nodes = $xpath->query(
			'//div[' . $this->get_xpath_class_selector( 'hero-card-main' ) . ']//div[' . $this->get_xpath_class_selector( 'featured-item' ) . ']'
		);

		foreach ( $product_nodes as $product_node ) {
			$name      = $this->get_xpath_node_text( $xpath, $product_node, './/div[' . $this->get_xpath_class_selector( 'item-name' ) . ']' );
			$meta_text = $this->get_xpath_node_text( $xpath, $product_node, './/div[' . $this->get_xpath_class_selector( 'item-cal' ) . ']' );
			$price     = $this->get_xpath_node_text( $xpath, $product_node, './/div[' . $this->get_xpath_class_selector( 'item-price' ) . ']' );
			$category  = '';

			if ( preg_match( '/kcal\s*[·\x{00B7}-]\s*(.+)$/u', $meta_text, $matches ) ) {
				$category = trim( $matches[1] );
			}

			if ( '' === $name || '' === $price ) {
				continue;
			}

			$products[] = array(
				'name'     => $name,
				'price'    => $this->parse_schema_price( $price ),
				'calories' => $this->parse_schema_calories( $meta_text ),
				'category' => $category,
				'image'    => $this->get_item_media_url( $name ),
			);
		}

		return $products;
	}

	/**
	 * Return the homepage modified date for SEO metadata.
	 *
	 * @return string
	 */
	protected function get_homepage_modified_date() {
		return wp_date( 'c', null, wp_timezone() );
	}

	/**
	 * Return the site logo URL when available.
	 *
	 * @return string
	 */
	protected function get_site_logo_url() {
		$logo_id = (int) get_theme_mod( 'custom_logo' );
		if ( $logo_id ) {
			$logo_url = wp_get_attachment_image_url( $logo_id, 'full' );

			if ( $logo_url ) {
				return (string) $logo_url;
			}
		}

		$site_icon_url = get_site_icon_url( 512 );

		if ( $site_icon_url ) {
			return (string) $site_icon_url;
		}

		return (string) get_theme_file_uri( '/assets/images/mcprices/mcprices-logo-schema.svg' );
	}

	/**
	 * Filter the homepage title tag.
	 *
	 * @param string $title Current title.
	 * @return string
	 */
	public function filter_document_title( $title ) {
		if ( ! $this->is_seo_homepage() ) {
			return $title;
		}

		return $this->get_homepage_meta_title();
	}

	/**
	 * Refine the homepage robots directives.
	 *
	 * @param array $robots Existing robots directives.
	 * @return array
	 */
	public function filter_homepage_robots( $robots ) {
		if ( ! $this->is_seo_homepage() ) {
			return $robots;
		}

		unset( $robots['noindex'], $robots['nofollow'] );

		$robots['index']             = true;
		$robots['follow']            = true;
		$robots['max-image-preview'] = 'large';
		$robots['max-snippet']       = '-1';
		$robots['max-video-preview'] = '-1';

		return $robots;
	}

	/**
	 * Output the homepage SEO meta tags without changing the visible design.
	 *
	 * @return void
	 */
	public function render_homepage_meta_tags() {
		if ( ! $this->is_seo_homepage() ) {
			return;
		}

		$title       = $this->get_homepage_meta_title();
		$description = $this->get_homepage_meta_description();
		$url         = home_url( '/' );
		$image_url   = get_template_directory_uri() . '/assets/images/mcprices/official/items/big-mac.jpg';
		$sitemap_url = home_url( '/sitemap.xml' );
		?>
		<meta name="description" content="<?php echo esc_attr( $description ); ?>">
		<link rel="canonical" href="<?php echo esc_url( $url ); ?>">
		<link rel="sitemap" type="application/xml" title="<?php esc_attr_e( 'Sitemap', 'kadence' ); ?>" href="<?php echo esc_url( $sitemap_url ); ?>">
		<meta property="og:locale" content="en_US">
		<meta property="og:type" content="website">
		<meta property="og:title" content="<?php echo esc_attr( $title ); ?>">
		<meta property="og:description" content="<?php echo esc_attr( $description ); ?>">
		<meta property="og:url" content="<?php echo esc_url( $url ); ?>">
		<meta property="og:image" content="<?php echo esc_url( $image_url ); ?>">
		<meta property="og:site_name" content="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
		<meta name="twitter:card" content="summary_large_image">
		<meta name="twitter:title" content="<?php echo esc_attr( $title ); ?>">
		<meta name="twitter:description" content="<?php echo esc_attr( $description ); ?>">
		<?php
	}

	/**
	 * Output JSON-LD structured data in the theme header via wp_head.
	 *
	 * @return void
	 */
	public function render_homepage_schema() {
		if ( ! $this->design_enabled() ) {
			return;
		}

		$url         = esc_url_raw( home_url( '/' ) );
		$title       = $this->is_seo_homepage() ? $this->get_homepage_meta_title() : wp_get_document_title();
		$description = $this->is_seo_homepage() ? $this->get_homepage_meta_description() : $this->get_schema_site_description();
		$logo_url    = $this->get_site_logo_url();
		$faq_items   = $this->is_seo_homepage() ? $this->get_homepage_faq_schema_items() : array();
		$products    = $this->is_seo_homepage() ? $this->get_homepage_popular_products() : array();
		$current_url = esc_url_raw( $this->get_current_request_url() );
		$breadcrumbs = $this->get_breadcrumb_schema_items();
		$search_url  = $url . '?s={search_term_string}';
		$graph       = array();

		$graph[] = array_filter(
			array(
				'@type'       => 'Organization',
				'@id'         => $url . '#organization',
				'name'        => $this->get_schema_organization_name(),
				'url'         => $url,
				'description' => $this->get_schema_site_description(),
				'logo'        => $logo_url ? array(
					'@type' => 'ImageObject',
					'url'   => esc_url_raw( $logo_url ),
				) : null,
			)
		);

		$graph[] = array(
			'@type'           => 'WebSite',
			'@id'             => $url . '#website',
			'url'             => $url,
			'name'            => $this->get_schema_organization_name(),
			'description'     => $this->get_schema_site_description(),
			'inLanguage'      => 'en-US',
			'publisher'       => array( '@id' => $url . '#organization' ),
				'potentialAction' => array(
					'@type'       => 'SearchAction',
					'target'      => array(
						'@type'       => 'EntryPoint',
						'urlTemplate' => $search_url,
					),
					'query-input' => 'required name=search_term_string',
				),
		);

		if ( $this->is_seo_homepage() ) {
			$graph[] = array(
				'@type'        => 'CollectionPage',
				'@id'          => $url . '#webpage',
				'url'          => $url,
				'name'         => $title,
				'description'  => $description,
				'inLanguage'   => 'en-US',
				'isPartOf'     => array( '@id' => $url . '#website' ),
				'about'        => array(
					$this->get_homepage_primary_keyword(),
					"McDonald's USA calories",
					"McDonald's USA breakfast hours",
					"McDonald's USA deals",
					'McValue menu',
				),
				'dateModified' => $this->get_homepage_modified_date(),
			);
		}

		if ( ! empty( $faq_items ) ) {
			$graph[] = array(
				'@type'      => 'FAQPage',
				'@id'        => $url . '#faq',
				'url'        => $url,
				'isPartOf'   => array( '@id' => $url . '#webpage' ),
				'mainEntity' => array_map(
					static function ( $faq_item ) {
						return array(
							'@type'          => 'Question',
							'name'           => $faq_item['question'],
							'acceptedAnswer' => array(
								'@type' => 'Answer',
								'text'  => $faq_item['answer'],
							),
						);
					},
					$faq_items
				),
			);
		}

		if ( ! empty( $products ) ) {
			$item_list = array();

			foreach ( $products as $index => $product ) {
				$product_id = $url . '#product-' . sanitize_title( $product['name'] );

				$graph[] = array_filter(
					array(
						'@type'              => 'Product',
						'@id'                => $product_id,
						'name'               => $product['name'],
						'image'              => $product['image'] ? array( $product['image'] ) : null,
						'description'        => sprintf(
							'%1$s is a popular McDonald\'s USA menu item in the %3$s update.%2$s',
							$product['name'],
							$product['category'] ? ' Category: ' . $product['category'] . '.' : '',
							$this->get_current_site_date()
						),
						'brand'              => array(
							'@type' => 'Brand',
							'name'  => "McDonald's",
						),
						'additionalProperty' => $product['calories'] ? array(
							array(
								'@type' => 'PropertyValue',
								'name'  => 'Calories',
								'value' => $product['calories'] . ' kcal',
							),
						) : null,
						'offers'             => $product['price'] ? array(
							'@type'         => 'Offer',
							'priceCurrency' => 'USD',
							'price'         => $product['price'],
							'availability'  => 'https://schema.org/InStock',
							'url'           => $this->get_schema_section_url( 'full-menu' ),
						) : null,
					)
				);

				$item_list[] = array(
					'@type'    => 'ListItem',
					'position' => $index + 1,
					'item'     => array(
						'@id' => $product_id,
						'name' => $product['name'],
					),
				);
			}

			$graph[] = array(
				'@type'           => 'ItemList',
				'@id'             => $url . '#popular-items',
				'name'            => 'Popular McDonald\'s USA Menu Items',
				'itemListElement' => $item_list,
			);
		}

		if ( ! empty( $breadcrumbs ) && count( $breadcrumbs ) > 1 ) {
			$breadcrumb_items = array();

			foreach ( $breadcrumbs as $index => $breadcrumb ) {
				if ( empty( $breadcrumb['name'] ) || empty( $breadcrumb['url'] ) ) {
					continue;
				}

				$breadcrumb_items[] = array(
					'@type'    => 'ListItem',
					'position' => $index + 1,
					'name'     => $breadcrumb['name'],
					'item'     => $breadcrumb['url'],
				);
			}

			if ( ! empty( $breadcrumb_items ) ) {
				$graph[] = array(
					'@type'           => 'BreadcrumbList',
					'@id'             => $current_url . '#breadcrumb',
					'itemListElement' => $breadcrumb_items,
				);
			}
		}
		?>
		<script type="application/ld+json"><?php echo wp_json_encode( array( '@context' => 'https://schema.org', '@graph' => $graph ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); ?></script>
		<?php
	}

	/**
	 * Return whether the current request is for the dynamic sitemap endpoint.
	 *
	 * @return bool
	 */
	protected function is_sitemap_request() {
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
		$request     = is_string( $request_uri ) ? (string) wp_parse_url( $request_uri, PHP_URL_PATH ) : '';
		$target      = (string) wp_parse_url( home_url( '/sitemap.xml' ), PHP_URL_PATH );

		return '' !== $request && untrailingslashit( $request ) === untrailingslashit( $target );
	}

	/**
	 * Return whether the current request is for the dynamic robots endpoint.
	 *
	 * @return bool
	 */
	protected function is_robots_request() {
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
		$request     = is_string( $request_uri ) ? (string) wp_parse_url( $request_uri, PHP_URL_PATH ) : '';
		$target      = (string) wp_parse_url( home_url( '/robots.txt' ), PHP_URL_PATH );

		return '' !== $request && untrailingslashit( $request ) === untrailingslashit( $target );
	}

	/**
	 * Return the sitemap entries for public pages and posts.
	 *
	 * @return array
	 */
	protected function get_sitemap_entries() {
		$entries       = array();
		$front_page_id = (int) get_option( 'page_on_front' );
		$posts_page_id = (int) get_option( 'page_for_posts' );

		$append_entry = static function ( &$bucket, $url, $lastmod, $changefreq, $priority ) {
			if ( empty( $url ) ) {
				return;
			}

			$bucket[] = array(
				'loc'        => $url,
				'lastmod'    => $lastmod ?: gmdate( 'c' ),
				'changefreq' => $changefreq,
				'priority'   => $priority,
			);
		};

		if ( $front_page_id ) {
			$append_entry(
				$entries,
				get_permalink( $front_page_id ),
				get_post_modified_time( 'c', true, $front_page_id ),
				'weekly',
				'1.0'
			);
		} else {
			$append_entry( $entries, home_url( '/' ), gmdate( 'c' ), 'weekly', '1.0' );
		}

		if ( $posts_page_id && $posts_page_id !== $front_page_id ) {
			$append_entry(
				$entries,
				get_permalink( $posts_page_id ),
				get_post_modified_time( 'c', true, $posts_page_id ),
				'daily',
				'0.8'
			);
		}

		$content_posts = get_posts(
			array(
				'post_type'              => array( 'page', 'post' ),
				'post_status'            => 'publish',
				'posts_per_page'         => 200,
				'post__not_in'           => array_filter( array( $front_page_id, $posts_page_id ) ),
				'orderby'                => 'modified',
				'order'                  => 'DESC',
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);

		foreach ( $content_posts as $content_post ) {
			$append_entry(
				$entries,
				get_permalink( $content_post ),
				get_post_modified_time( 'c', true, $content_post ),
				'weekly',
				'page' === $content_post->post_type ? '0.8' : '0.7'
			);
		}

		return $entries;
	}

	/**
	 * Return XML-safe text.
	 *
	 * @param string $value Raw text.
	 * @return string
	 */
	protected function escape_xml( $value ) {
		return htmlspecialchars( (string) $value, ENT_XML1 | ENT_COMPAT, 'UTF-8' );
	}

	/**
	 * Render a dynamic sitemap.xml response.
	 *
	 * @return void
	 */
	public function maybe_render_sitemap() {
		if ( ! $this->design_enabled() || is_admin() || ! $this->is_sitemap_request() ) {
			return;
		}

		nocache_headers();
		status_header( 200 );
		header( 'Content-Type: application/xml; charset=UTF-8' );

		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

		foreach ( $this->get_sitemap_entries() as $entry ) {
			echo '<url>';
			echo '<loc>' . $this->escape_xml( $entry['loc'] ) . '</loc>';
			echo '<lastmod>' . $this->escape_xml( $entry['lastmod'] ) . '</lastmod>';
			echo '<changefreq>' . $this->escape_xml( $entry['changefreq'] ) . '</changefreq>';
			echo '<priority>' . $this->escape_xml( $entry['priority'] ) . '</priority>';
			echo '</url>';
		}

		echo '</urlset>';
		exit;
	}

	/**
	 * Render a dynamic robots.txt response.
	 *
	 * @return void
	 */
	public function maybe_render_robots() {
		if ( ! $this->design_enabled() || is_admin() || ! $this->is_robots_request() ) {
			return;
		}

		nocache_headers();
		status_header( 200 );
		header( 'Content-Type: text/plain; charset=UTF-8' );
		echo $this->filter_robots_txt( '', (bool) get_option( 'blog_public', 1 ) );
		exit;
	}

	/**
	 * Output a sitewide robots.txt that points crawlers to the custom sitemap.
	 *
	 * @param string $output Current robots content.
	 * @param bool   $public Whether the site is public.
	 * @return string
	 */
	public function filter_robots_txt( $output, $public ) {
		$lines = array(
			'User-agent: *',
			'Allow: /',
			'Disallow: /wp-admin/',
			'Allow: /wp-admin/admin-ajax.php',
			'',
			'Sitemap: ' . home_url( '/sitemap.xml' ),
		);

		return implode( "\n", $lines );
	}

	/**
	 * Register the small native Customizer section that controls this integration.
	 *
	 * @param \WP_Customize_Manager $wp_customize Customizer manager.
	 * @return void
	 */
	public function register_customizer( $wp_customize ) {
		$wp_customize->add_section(
			'mcprices_native_design',
			array(
				'title'       => __( 'McPrices Native Design', 'kadence' ),
				'panel'       => 'kadence_customizer_general',
				'priority'    => 5,
				'description' => __( 'This keeps Kadence as the real parent theme while applying the attached McPrices design natively. Use Header Builder and Footer Builder for drag-and-drop header/footer changes, and insert the "McPrices Homepage" pattern in the block editor for the body layout.', 'kadence' ),
			)
		);

		$wp_customize->add_setting(
			self::ENABLE_SETTING,
			array(
				'default'           => true,
				'type'              => 'theme_mod',
				'sanitize_callback' => array( $this, 'sanitize_checkbox' ),
			)
		);
		$wp_customize->add_control(
			self::ENABLE_SETTING,
			array(
				'section' => 'mcprices_native_design',
				'label'   => __( 'Enable McPrices site-wide skin', 'kadence' ),
				'type'    => 'checkbox',
			)
		);

		$wp_customize->add_setting(
			self::DISCLAIMER_ENABLE_SETTING,
			array(
				'default'           => true,
				'type'              => 'theme_mod',
				'sanitize_callback' => array( $this, 'sanitize_checkbox' ),
			)
		);
		$wp_customize->add_control(
			self::DISCLAIMER_ENABLE_SETTING,
			array(
				'section' => 'mcprices_native_design',
				'label'   => __( 'Show the legal disclaimer bar above the footer', 'kadence' ),
				'type'    => 'checkbox',
			)
		);

		$wp_customize->add_setting(
			self::DISCLAIMER_TEXT_SETTING,
			array(
				'default'           => $this->get_default_disclaimer_text(),
				'type'              => 'theme_mod',
				'sanitize_callback' => array( $this, 'sanitize_rich_text' ),
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Control(
				$wp_customize,
				self::DISCLAIMER_TEXT_SETTING,
				array(
					'section'     => 'mcprices_native_design',
					'label'       => __( 'Footer disclaimer text', 'kadence' ),
					'type'        => 'textarea',
					'description' => __( 'Keep this independent-site disclosure visible on pages with ads or pricing content.', 'kadence' ),
				)
			)
		);
	}

	/**
	 * Seed the active Kadence parent theme with the attached design one time so
	 * existing installs immediately get the native layout and remain editable.
	 *
	 * @return void
	 */
	public function maybe_seed_native_design() {
		if ( self::SEED_VERSION === get_theme_mod( self::SEED_VERSION_SETTING, '' ) ) {
			return;
		}

		foreach ( $this->get_seeded_theme_mod_keys() as $key ) {
			set_theme_mod( $key, kadence()->default( $key ) );
		}

		set_theme_mod( self::ENABLE_SETTING, true );
		set_theme_mod( self::DISCLAIMER_ENABLE_SETTING, true );
		set_theme_mod( self::DISCLAIMER_TEXT_SETTING, $this->get_default_disclaimer_text() );
		update_option( 'kadence_global_palette', $this->filter_palette_defaults( '' ) );

		$this->maybe_seed_front_page();
		$this->maybe_seed_blog_page();
		$this->maybe_seed_nav_menus( true );
		$this->maybe_seed_footer_widgets( true );
		$this->maybe_seed_front_page_content( true );
		$this->maybe_disable_legacy_custom_css();

		set_theme_mod( self::SEED_VERSION_SETTING, self::SEED_VERSION );
	}

	/**
	 * Ensure the site uses a static front page that can receive the managed
	 * homepage pattern.
	 *
	 * @return int
	 */
	protected function maybe_seed_front_page() {
		$front_page_id = (int) get_option( 'page_on_front' );
		$front_page    = $front_page_id ? get_post( $front_page_id ) : null;

		if ( $front_page instanceof \WP_Post && 'page' === $front_page->post_type ) {
			if ( 'page' !== get_option( 'show_on_front' ) ) {
				update_option( 'show_on_front', 'page' );
			}

			return $front_page_id;
		}

		$home_page = get_page_by_path( 'home' );
		if ( ! $home_page instanceof \WP_Post ) {
			$home_page_id = wp_insert_post(
				array(
					'post_title'  => 'Home',
					'post_name'   => 'home',
					'post_type'   => 'page',
					'post_status' => 'publish',
				),
				true
			);

			if ( is_wp_error( $home_page_id ) || ! $home_page_id ) {
				return 0;
			}

			$home_page = get_post( $home_page_id );
		}

		if ( ! $home_page instanceof \WP_Post ) {
			return 0;
		}

		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', (int) $home_page->ID );

		return (int) $home_page->ID;
	}

	/**
	 * Seed the registered homepage pattern into the static front page when the
	 * current front page is empty or still using an older managed McPrices body.
	 *
	 * @param bool $force_sync Whether to replace previously managed content even
	 *                         when the page already has content.
	 * @return void
	 */
	protected function maybe_seed_front_page_content( $force_sync = false ) {
		if ( 'page' !== get_option( 'show_on_front' ) ) {
			return;
		}

		$front_page_id = (int) get_option( 'page_on_front' );
		if ( ! $front_page_id ) {
			return;
		}

		$front_page = get_post( $front_page_id );
		if ( ! $front_page || 'page' !== $front_page->post_type ) {
			return;
		}

		$homepage_pattern = $this->get_homepage_pattern_markup();
		if ( empty( $homepage_pattern ) ) {
			return;
		}

		$content = (string) $front_page->post_content;
		$pattern_version = $this->get_homepage_pattern_version( $homepage_pattern );
		if ( $pattern_version && false !== strpos( $content, 'data-mcprices-pattern-version="' . $pattern_version . '"' ) ) {
			return;
		}

		$content_without_block_comments = preg_replace( '/<!--[\s\S]*?-->/', '', $content );
		$plain_content                  = trim( wp_strip_all_tags( (string) $content_without_block_comments ) );
		$should_sync                    = '' === $plain_content;

		if ( ! $should_sync && $force_sync && $this->is_managed_homepage_content( $content ) ) {
			$should_sync = true;
		}

		if ( ! $should_sync ) {
			return;
		}

		wp_update_post(
			array(
				'ID'           => $front_page_id,
				'post_content' => $homepage_pattern,
			)
		);
		update_option( self::HOMEPAGE_PATTERN_SIGNATURE_OPTION, $this->get_seed_signature( $homepage_pattern ), false );
	}

	/**
	 * Synchronize the managed homepage page body when the pattern file changes.
	 *
	 * @return void
	 */
	protected function maybe_sync_front_page_pattern_content() {
		if ( 'page' !== get_option( 'show_on_front' ) ) {
			return;
		}

		$front_page_id = (int) get_option( 'page_on_front' );
		if ( ! $front_page_id ) {
			return;
		}

		$front_page = get_post( $front_page_id );
		if ( ! $front_page || 'page' !== $front_page->post_type ) {
			return;
		}

		$homepage_pattern = $this->get_homepage_pattern_markup();
		if ( '' === $homepage_pattern ) {
			return;
		}

		$content           = (string) $front_page->post_content;
		$is_managed        = $this->is_managed_homepage_content( $content );
		$pattern_signature = $this->get_seed_signature( $homepage_pattern );
		$stored_signature  = (string) get_option( self::HOMEPAGE_PATTERN_SIGNATURE_OPTION, '' );
		$pattern_version   = $this->get_homepage_pattern_version( $homepage_pattern );
		$has_current_version = $pattern_version && false !== strpos( $content, 'data-mcprices-pattern-version="' . $pattern_version . '"' );

		$content_without_block_comments = preg_replace( '/<!--[\s\S]*?-->/', '', $content );
		$plain_content                  = trim( wp_strip_all_tags( (string) $content_without_block_comments ) );
		$is_empty                       = '' === $plain_content;
		$content_matches_pattern        = trim( $content ) === trim( $homepage_pattern );

		if ( $is_empty ) {
			$this->maybe_seed_front_page_content( true );
			return;
		}

		if ( ! $is_managed ) {
			return;
		}

		if ( ! $has_current_version || $stored_signature !== $pattern_signature || ! $content_matches_pattern ) {
			wp_update_post(
				array(
					'ID'           => $front_page_id,
					'post_content' => $homepage_pattern,
				)
			);
		}

		update_option( self::HOMEPAGE_PATTERN_SIGNATURE_OPTION, $pattern_signature, false );
	}

	/**
	 * Seed a native WordPress posts page for the Blogs menu when none exists yet.
	 *
	 * @return void
	 */
	protected function maybe_seed_blog_page() {
		$front_page_id = (int) get_option( 'page_on_front' );
		$posts_page_id = (int) get_option( 'page_for_posts' );
		$blog_page     = null;

		if ( $posts_page_id && $posts_page_id !== $front_page_id ) {
			$existing_posts_page = get_post( $posts_page_id );

			if ( ! $existing_posts_page instanceof \WP_Post || 'page' !== $existing_posts_page->post_type ) {
				return;
			}

			$existing_slug  = sanitize_title( $existing_posts_page->post_name );
			$existing_title = trim( wp_strip_all_tags( $existing_posts_page->post_title ) );

			if ( ! in_array( $existing_slug, array( 'blog', 'blogs' ), true ) && ! in_array( $existing_title, array( 'Blog', 'Blogs' ), true ) ) {
				return;
			}

			$blog_page = $existing_posts_page;
		}

		if ( ! $blog_page instanceof \WP_Post ) {
			$blog_page = get_page_by_path( 'blog' );
		}

		if ( ! $blog_page instanceof \WP_Post ) {
			$blog_page = get_page_by_path( 'blogs' );
		}

		if ( ! $blog_page instanceof \WP_Post ) {
			$blog_page_id = wp_insert_post(
				array(
					'post_title'  => 'Blog',
					'post_name'   => 'blog',
					'post_type'   => 'page',
					'post_status' => 'publish',
				),
				true
			);

			if ( is_wp_error( $blog_page_id ) || ! $blog_page_id ) {
				return;
			}

			$blog_page = get_post( $blog_page_id );
		}

		if ( $blog_page instanceof \WP_Post ) {
			$blog_page_update = array(
				'ID' => (int) $blog_page->ID,
			);

			if ( 'Blog' !== $blog_page->post_title ) {
				$blog_page_update['post_title'] = 'Blog';
			}

			if ( 'blog' !== $blog_page->post_name ) {
				$blog_page_update['post_name'] = 'blog';
			}

			if ( count( $blog_page_update ) > 1 ) {
				wp_update_post( $blog_page_update );
			}

			update_option( 'page_for_posts', (int) $blog_page->ID );
		}
	}

	/**
	 * Seed native Kadence menu locations only when they are still empty.
	 *
	 * @return void
	 */
	protected function maybe_seed_nav_menus( $force_sync = false ) {
		$locations = get_theme_mod( 'nav_menu_locations', array() );
		if ( ! is_array( $locations ) ) {
			$locations = array();
		}

		$locations['primary'] = $this->create_seed_menu(
			'McPrices Primary',
			$this->get_default_primary_menu_items(),
			$force_sync
		);
		$locations['footer']  = $this->create_seed_menu(
			'McPrices Footer Legal',
			$this->get_default_footer_menu_items(),
			$force_sync
		);

		set_theme_mod( 'nav_menu_locations', $locations );
	}

	/**
	 * Create a native WordPress menu with custom links if it does not already
	 * contain items.
	 *
	 * @param string $menu_name  Menu name.
	 * @param array  $items      Menu item definitions.
	 * @param bool   $force_sync Whether to rebuild the menu items from scratch.
	 * @return int
	 */
	protected function create_seed_menu( $menu_name, $items, $force_sync = false ) {
		$menu = wp_get_nav_menu_object( $menu_name );
		if ( ! $menu ) {
			$menu_id = wp_create_nav_menu( $menu_name );
			$menu    = wp_get_nav_menu_object( $menu_id );
		}

		if ( ! $menu || empty( $menu->term_id ) ) {
			return 0;
		}

		$menu_id        = (int) $menu->term_id;
		$existing_items = wp_get_nav_menu_items( $menu_id );
		if ( ! $force_sync && ! empty( $existing_items ) ) {
			return $menu_id;
		}

		if ( $force_sync && ! empty( $existing_items ) ) {
			foreach ( $existing_items as $existing_item ) {
				wp_delete_post( (int) $existing_item->ID, true );
			}
		}

		foreach ( $items as $item ) {
			wp_update_nav_menu_item(
				$menu_id,
				0,
				array(
					'menu-item-title'  => $item['title'],
					'menu-item-url'    => $item['url'],
					'menu-item-status' => 'publish',
					'menu-item-type'   => 'custom',
				)
			);
		}

		return $menu_id;
	}

	/**
	 * Synchronize the managed McPrices menus when the file-driven defaults change.
	 *
	 * @return void
	 */
	protected function maybe_sync_seed_menus() {
		$current_signature = $this->get_seeded_menu_signature();
		$stored_signature  = (string) get_option( self::MENU_SIGNATURE_OPTION, '' );

		if ( $stored_signature !== $current_signature ) {
			$this->maybe_seed_nav_menus( true );
		}

		update_option( self::MENU_SIGNATURE_OPTION, $current_signature, false );
	}

	/**
	 * Seed footer block widgets into Kadence footer sidebars when those areas
	 * are still empty.
	 *
	 * @return void
	 */
	protected function maybe_seed_footer_widgets( $force_sync = false ) {
		$sidebars_widgets = get_option( 'sidebars_widgets', array() );
		if ( empty( $sidebars_widgets ) || ! is_array( $sidebars_widgets ) ) {
			$sidebars_widgets = wp_get_widget_defaults();
		}

		if ( ! isset( $sidebars_widgets['wp_inactive_widgets'] ) ) {
			$sidebars_widgets['wp_inactive_widgets'] = array();
		}

		$widget_blocks = get_option( 'widget_block', array() );
		if ( ! is_array( $widget_blocks ) ) {
			$widget_blocks = array();
		}
		if ( ! isset( $widget_blocks['_multiwidget'] ) ) {
			$widget_blocks['_multiwidget'] = 1;
		}

		$updated = false;

		foreach ( $this->get_default_footer_widget_blocks() as $sidebar_id => $content ) {
			if ( ! $force_sync && ! empty( $sidebars_widgets[ $sidebar_id ] ) ) {
				continue;
			}

			$widget_number                   = $this->get_next_block_widget_number( $widget_blocks );
			$widget_blocks[ $widget_number ] = array(
				'content' => $content,
			);
			$sidebars_widgets[ $sidebar_id ] = array( 'block-' . $widget_number );
			$updated                         = true;
		}

		if ( ! $updated ) {
			return;
		}

		update_option( 'widget_block', $widget_blocks );
		wp_set_sidebars_widgets( $sidebars_widgets );
	}

	/**
	 * Synchronize the managed footer widget blocks when their file defaults change.
	 *
	 * @return void
	 */
	protected function maybe_sync_footer_widget_blocks() {
		$current_signature = $this->get_seeded_footer_widget_signature();
		$stored_signature  = (string) get_option( self::FOOTER_WIDGET_SIGNATURE_OPTION, '' );

		if ( $stored_signature !== $current_signature ) {
			$this->maybe_seed_footer_widgets( true );
		}

		update_option( self::FOOTER_WIDGET_SIGNATURE_OPTION, $current_signature, false );
	}

	/**
	 * Return the current managed homepage pattern version marker.
	 *
	 * @param string $homepage_pattern Pattern markup.
	 * @return string
	 */
	protected function get_homepage_pattern_version( $homepage_pattern ) {
		if ( preg_match( '/data-mcprices-pattern-version="([^"]+)"/', $homepage_pattern, $matches ) ) {
			return (string) $matches[1];
		}

		return '';
	}

	/**
	 * Return whether the supplied homepage body looks like McPrices-managed
	 * content that may be safely refreshed during a seed version migration.
	 *
	 * @param string $content Page content.
	 * @return bool
	 */
	protected function is_managed_homepage_content( $content ) {
		$managed_markers = array(
			'mcprices-managed-homepage',
			'data-mcprices-pattern-version=',
			'mcprices-page',
			'[mcprices_hero_search]',
			'hero-badge-float',
			'menu-tab',
		);

		foreach ( $managed_markers as $marker ) {
			if ( false !== strpos( (string) $content, $marker ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Remove the old McPrices Additional CSS blob once the native theme files are
	 * active so it cannot keep overriding the newer managed integration.
	 *
	 * @return void
	 */
	protected function maybe_disable_legacy_custom_css() {
		$custom_css = (string) wp_get_custom_css();
		if ( '' === trim( $custom_css ) ) {
			return;
		}

		$looks_like_legacy_mcprices_css =
			( false !== strpos( $custom_css, 'McPrices UK' ) || false !== strpos( $custom_css, 'McPrices USA' ) || false !== strpos( $custom_css, "McDonald's Menu Prices USA" ) ) &&
			false !== strpos( $custom_css, '--mcprice-red' ) &&
			false !== strpos( $custom_css, '.hero-search-wrap' );

		if ( ! $looks_like_legacy_mcprices_css ) {
			return;
		}

		wp_update_custom_css_post(
			'',
			array(
				'stylesheet' => get_stylesheet(),
			)
		);
	}

	/**
	 * Return the next available block widget number.
	 *
	 * @param array $widget_blocks Existing block widget option data.
	 * @return int
	 */
	protected function get_next_block_widget_number( $widget_blocks ) {
		$max = 1;
		foreach ( array_keys( $widget_blocks ) as $key ) {
			if ( is_numeric( $key ) ) {
				$max = max( $max, (int) $key );
			}
		}

		return $max + 1;
	}

	/**
	 * Enqueue the design stylesheet and fonts.
	 *
	 * @return void
	 */
	public function enqueue_assets() {
		if ( ! $this->design_enabled() ) {
			return;
		}

		wp_enqueue_style(
			'kadence-mcprices-fonts',
			'https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&family=DM+Sans:wght@300;400;500;600;700&display=swap',
			array(),
			null
		);

		wp_enqueue_style(
			'kadence-mcprices-design',
			get_theme_file_uri( '/assets/css/mcprices-integrated.css' ),
			array( 'kadence-global' ),
			kadence()->get_asset_version( get_theme_file_path( '/assets/css/mcprices-integrated.css' ) )
		);

		wp_enqueue_style(
			'kadence-mcprices-enhanced-layer',
			get_theme_file_uri( '/assets/css/mcprices-enhanced-layer.css' ),
			array( 'kadence-mcprices-design' ),
			kadence()->get_asset_version( get_theme_file_path( '/assets/css/mcprices-enhanced-layer.css' ) )
		);

		wp_enqueue_script(
			'kadence-mcprices-interactions',
			get_theme_file_uri( '/assets/js/mcprices-integrated.js' ),
			array(),
			kadence()->get_asset_version( get_theme_file_path( '/assets/js/mcprices-integrated.js' ) ),
			true
		);

		$media_manifest_path = get_theme_file_path( '/assets/data/mcprices-media-manifest.json' );
		if ( file_exists( $media_manifest_path ) ) {
			$media_manifest = json_decode( (string) file_get_contents( $media_manifest_path ), true );
			if ( is_array( $media_manifest ) ) {
				wp_add_inline_script(
					'kadence-mcprices-interactions',
					'window.mcpricesMediaBaseUrl = ' . wp_json_encode( trailingslashit( get_theme_file_uri( '/assets/images/mcprices/official' ) ) ) . ';'
					. 'window.mcpricesMediaManifest = ' . wp_json_encode( $media_manifest ) . ';',
					'before'
				);
			}
		}
	}

	/**
	 * Render the native disclaimer bar above the footer.
	 *
	 * @return void
	 */
	public function render_footer_disclaimer() {
		if ( ! $this->design_enabled() || ! get_theme_mod( self::DISCLAIMER_ENABLE_SETTING, true ) ) {
			return;
		}

		$text = get_theme_mod( self::DISCLAIMER_TEXT_SETTING, $this->get_default_disclaimer_text() );
		if ( empty( $text ) ) {
			return;
		}
		?>
		<div class="mcprices-footer-disclaimer">
			<div class="mcprices-footer-disclaimer-inner">
				<p><?php echo wp_kses_post( $text ); ?></p>
			</div>
		</div>
		<?php
	}

	/**
	 * Register the editable block patterns used for the body layout.
	 *
	 * @return void
	 */
	public function register_patterns() {
		if ( ! function_exists( 'register_block_pattern' ) || ! function_exists( 'register_block_pattern_category' ) ) {
			return;
		}

		register_block_pattern_category(
			'mcprices',
			array(
				'label' => __( 'McPrices', 'kadence' ),
			)
		);

		$homepage_pattern = require get_theme_file_path( '/inc/mcprices/pattern-homepage.php' );
		if ( ! empty( $homepage_pattern ) ) {
			register_block_pattern(
				'kadence/mcprices-homepage',
				array(
					'title'       => __( 'McPrices Homepage', 'kadence' ),
					'description' => __( 'Editable homepage sections matching the attached McPrices design while staying inside the block editor.', 'kadence' ),
					'categories'  => array( 'mcprices', 'featured' ),
					'keywords'    => array( 'mcprices', 'menu', 'homepage', 'kadence' ),
					'content'     => $homepage_pattern,
				)
			);
		}
	}

	/**
	 * Sanitize a checkbox value.
	 *
	 * @param mixed $value Raw checkbox value.
	 * @return bool
	 */
	public function sanitize_checkbox( $value ) {
		return (bool) $value;
	}

	/**
	 * Sanitize rich text while still allowing basic markup.
	 *
	 * @param string $value Raw text.
	 * @return string
	 */
	public function sanitize_rich_text( $value ) {
		return wp_kses_post( $value );
	}
}




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

require_once __DIR__ . '/seed-menu-root.php';

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
	 * Option used to track the currently seeded menu directory page signature.
	 */
	const MENU_DIRECTORY_SIGNATURE_OPTION = 'mcprices_menu_directory_signature';

	/**
	 * Option used to track the current page-category admin seeding signature.
	 */
	const PAGE_CATEGORY_SIGNATURE_OPTION = 'mcprices_page_category_signature';

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
		add_action( 'init', array( $this, 'register_page_category_support' ), 12 );
		add_action( 'init', array( $this, 'maybe_seed_page_categories' ), 26 );
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
		add_action( 'admin_menu', array( $this, 'register_page_categories_submenu' ), 20 );
		add_action( 'restrict_manage_posts', array( $this, 'render_page_category_filter' ) );
		add_action( 'pre_get_posts', array( $this, 'filter_page_admin_query_by_category' ) );
		add_filter( 'manage_pages_columns', array( $this, 'filter_page_admin_columns' ) );
		add_action( 'manage_pages_custom_column', array( $this, 'render_page_admin_column' ), 10, 2 );
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
	 * Make the built-in category taxonomy available on pages so menu items and
	 * category landing pages can be managed from normal WordPress admin screens.
	 *
	 * @return void
	 */
	public function register_page_category_support() {
		register_taxonomy_for_object_type( 'category', 'page' );
	}

	/**
	 * Add a convenient categories submenu under Pages in wp-admin.
	 *
	 * @return void
	 */
	public function register_page_categories_submenu() {
		add_submenu_page(
			'edit.php?post_type=page',
			__( 'Page Categories', 'kadence' ),
			__( 'Page Categories', 'kadence' ),
			'manage_categories',
			'edit-tags.php?taxonomy=category&post_type=page'
		);
	}

	/**
	 * Seed real WordPress categories for the managed menu pages and assign them
	 * to the seeded page tree so editing can happen from wp-admin.
	 *
	 * @return void
	 */
	public function maybe_seed_page_categories() {
		if ( ! $this->design_enabled() ) {
			return;
		}

		$signature = md5(
			wp_json_encode(
				array(
					'terms'    => $this->get_page_category_seed_terms(),
					'supports' => $this->get_page_category_support_page_map(),
				)
			)
		);

		if ( $signature === (string) get_option( self::PAGE_CATEGORY_SIGNATURE_OPTION, '' ) ) {
			return;
		}

		$term_ids = array();

		foreach ( $this->get_page_category_seed_terms() as $category_key => $term_data ) {
			$term_id = $this->upsert_page_category_term( $term_data );

			if ( $term_id ) {
				$term_ids[ $category_key ] = $term_id;
			}
		}

		if ( empty( $term_ids ) ) {
			return;
		}

		$this->assign_seeded_terms_to_managed_pages( $term_ids );
		$this->assign_seeded_terms_to_support_pages( $term_ids );

		update_option( self::PAGE_CATEGORY_SIGNATURE_OPTION, $signature, false );
	}

	/**
	 * Return the menu category terms that should exist in WordPress admin.
	 *
	 * @return array<string, array<string, string>>
	 */
	protected function get_page_category_seed_terms() {
		$terms = array();

		foreach ( $this->get_menu_directory_category_blueprints() as $category_key => $blueprint ) {
			$terms[ $category_key ] = array(
				'name'        => (string) $blueprint['card_title'],
				'slug'        => sanitize_title( (string) $blueprint['slug'] ),
				'description' => (string) $blueprint['description'],
			);
		}

		$terms['sharers'] = array(
			'name'        => 'Shareables & Bundles',
			'slug'        => 'shareables-bundles',
			'description' => 'Sharers, bundles, and group-friendly McDonald\'s USA menu pages.',
		);

		return $terms;
	}

	/**
	 * Return support-page slugs that should also receive seeded categories.
	 *
	 * @return array<string, string>
	 */
	protected function get_page_category_support_page_map() {
		return array(
			'dollar-menu'          => 'mcvalue',
			'extra-value-meals'    => 'meals',
			'limited-time-menu'    => 'whats-new',
			'breakfast-menu'       => 'breakfast',
			'breakfast-hours'      => 'breakfast',
			'breakfast-times'      => 'breakfast',
			'burgers-menu'         => 'burgers',
			'big-mac-price-usa'    => 'burgers',
			'chicken-fish-menu'    => 'chickenfish',
			'fries-sides'          => 'sides',
			'happy-meal-menu'      => 'happymeal',
			'mccafe-menu'          => 'mccafe',
			'beverage-menu'        => 'beverages',
			'snack-wrap'           => 'snackwrap',
			'sweets-treats'        => 'sweets',
			'sauces-condiments'    => 'sauces',
			'mcdonalds-app-deals'  => 'deals',
			'mcdonalds-deals-mcvalue-guide' => 'deals',
			'rewards-guide'        => 'deals',
			'shareables-bundles'   => 'sharers',
		);
	}

	/**
	 * Create or update one seeded page category term.
	 *
	 * @param array<string, string> $term_data Term definition.
	 * @return int
	 */
	protected function upsert_page_category_term( array $term_data ) {
		$slug        = sanitize_title( (string) ( $term_data['slug'] ?? '' ) );
		$name        = trim( (string) ( $term_data['name'] ?? '' ) );
		$description = trim( (string) ( $term_data['description'] ?? '' ) );

		if ( '' === $slug || '' === $name ) {
			return 0;
		}

		$term = get_term_by( 'slug', $slug, 'category' );

		if ( ! $term instanceof \WP_Term ) {
			$created = wp_insert_term(
				$name,
				'category',
				array(
					'slug'        => $slug,
					'description' => $description,
				)
			);

			if ( is_wp_error( $created ) ) {
				return 0;
			}

			return (int) $created['term_id'];
		}

		wp_update_term(
			(int) $term->term_id,
			'category',
			array(
				'name'        => $name,
				'description' => $description,
				'slug'        => $slug,
			)
		);

		return (int) $term->term_id;
	}

	/**
	 * Assign the seeded category terms to the managed menu page tree.
	 *
	 * @param array<string, int> $term_ids Seeded term IDs by category key.
	 * @return void
	 */
	protected function assign_seeded_terms_to_managed_pages( array $term_ids ) {
		$pages = get_posts(
			array(
				'post_type'      => 'page',
				'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
				'posts_per_page' => -1,
				'meta_query'     => array(
					array(
						'key'     => '_mcprices_managed_key',
						'compare' => 'EXISTS',
					),
				),
			)
		);

		foreach ( $pages as $page ) {
			$managed_key = trim( (string) get_post_meta( $page->ID, '_mcprices_managed_key', true ) );
			$category_key = $managed_key;

			if ( false !== strpos( $managed_key, '::' ) ) {
				list( $category_key ) = explode( '::', $managed_key, 2 );
			}

			if ( 'menu' === $category_key || ! isset( $term_ids[ $category_key ] ) ) {
				continue;
			}

			$this->assign_page_category_term( (int) $page->ID, (int) $term_ids[ $category_key ] );
		}
	}

	/**
	 * Assign the seeded category terms to support pages that act as guides.
	 *
	 * @param array<string, int> $term_ids Seeded term IDs by category key.
	 * @return void
	 */
	protected function assign_seeded_terms_to_support_pages( array $term_ids ) {
		foreach ( $this->get_page_category_support_page_map() as $page_slug => $category_key ) {
			if ( ! isset( $term_ids[ $category_key ] ) ) {
				continue;
			}

			$page = get_page_by_path( $page_slug, OBJECT, 'page' );

			if ( ! $page instanceof \WP_Post ) {
				continue;
			}

			$this->assign_page_category_term( (int) $page->ID, (int) $term_ids[ $category_key ] );
		}
	}

	/**
	 * Attach one category term to a page while stripping only the default
	 * uncategorized assignment.
	 *
	 * @param int $page_id Page ID.
	 * @param int $term_id Category term ID.
	 * @return void
	 */
	protected function assign_page_category_term( $page_id, $term_id ) {
		$existing_ids = wp_get_object_terms(
			$page_id,
			'category',
			array(
				'fields' => 'ids',
			)
		);

		if ( is_wp_error( $existing_ids ) ) {
			$existing_ids = array();
		}

		$default_category = (int) get_option( 'default_category' );
		$existing_ids     = array_map( 'intval', (array) $existing_ids );

		if ( $default_category ) {
			$existing_ids = array_values( array_diff( $existing_ids, array( $default_category ) ) );
		}

		$term_ids = array_values( array_unique( array_merge( $existing_ids, array( (int) $term_id ) ) ) );

		wp_set_post_terms( (int) $page_id, $term_ids, 'category', false );
	}

	/**
	 * Add a category filter to the Pages admin list table.
	 *
	 * @param string $post_type Current post type slug.
	 * @return void
	 */
	public function render_page_category_filter( $post_type ) {
		if ( 'page' !== $post_type ) {
			return;
		}

		$selected = isset( $_GET['mcprices_page_category'] ) ? sanitize_title( wp_unslash( $_GET['mcprices_page_category'] ) ) : '';

		wp_dropdown_categories(
			array(
				'show_option_all' => __( 'All page categories', 'kadence' ),
				'taxonomy'        => 'category',
				'name'            => 'mcprices_page_category',
				'orderby'         => 'name',
				'selected'        => $selected,
				'hide_empty'      => false,
				'value_field'     => 'slug',
			)
		);
	}

	/**
	 * Apply the selected category filter to the Pages admin query.
	 *
	 * @param \WP_Query $query Main admin query.
	 * @return void
	 */
	public function filter_page_admin_query_by_category( $query ) {
		global $pagenow;

		if ( ! is_admin() || ! $query->is_main_query() || 'edit.php' !== $pagenow ) {
			return;
		}

		if ( 'page' !== $query->get( 'post_type' ) ) {
			return;
		}

		$selected = isset( $_GET['mcprices_page_category'] ) ? sanitize_title( wp_unslash( $_GET['mcprices_page_category'] ) ) : '';

		if ( '' === $selected ) {
			return;
		}

		$query->set(
			'tax_query',
			array(
				array(
					'taxonomy' => 'category',
					'field'    => 'slug',
					'terms'    => $selected,
				),
			)
		);
	}

	/**
	 * Show a page-category column in the Pages admin list table.
	 *
	 * @param array<string, string> $columns Existing columns.
	 * @return array<string, string>
	 */
	public function filter_page_admin_columns( $columns ) {
		$updated_columns = array();

		foreach ( $columns as $key => $label ) {
			$updated_columns[ $key ] = $label;

			if ( 'title' === $key ) {
				$updated_columns['mcprices_page_categories'] = __( 'Categories', 'kadence' );
			}
		}

		return $updated_columns;
	}

	/**
	 * Render the page-category column in wp-admin.
	 *
	 * @param string $column_name Column name.
	 * @param int    $post_id     Page ID.
	 * @return void
	 */
	public function render_page_admin_column( $column_name, $post_id ) {
		if ( 'mcprices_page_categories' !== $column_name ) {
			return;
		}

		$terms = get_the_terms( $post_id, 'category' );

		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			echo '&mdash;';
			return;
		}

		echo esc_html( implode( ', ', wp_list_pluck( $terms, 'name' ) ) );
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
		return array_merge(
			array(
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
					'content' => $this->build_seeded_support_topic_page_content(
						array(
							'intro'         => array(
								'This Big Mac Price USA guide focuses on the current Big Mac sandwich price, Big Mac meal pricing, calorie context, and the role the Big Mac plays as a flagship reference point on the McDonald&#8217;s USA burger menu.',
								'Most readers do not look up the Big Mac in isolation. They are usually comparing it with McDouble, Quarter Pounder, or a full combo meal, which is why this page works best as a focused answer that also routes into the wider burger and value guides.',
							),
							'highlights'    => array(
								'Use this page when the real decision is Big Mac sandwich versus Big Mac meal price.',
								'Compare Big Mac pricing against the burgers pillar when you want to see the full burger value ladder.',
								'App offers, local taxes, franchise pricing, and delivery can still change the final local total.',
							),
							'sections'      => array(
								array(
									'heading'    => 'Why the Big Mac matters as a pricing benchmark',
									'paragraphs' => array(
										'The Big Mac remains one of the clearest burger benchmarks on any McDonald\'s pricing site because readers use it to judge whether the wider burger menu feels expensive, average, or unusually good value in their market.',
										'That makes the Big Mac page useful not only for one burger price check, but also as a decision point before moving into the wider burgers guide, the full menu directory, or the deals and McValue coverage.',
									),
								),
							),
							'related_links' => array(
								array(
									'label'       => 'Read the burgers menu prices pillar',
									'url'         => $this->get_seeded_page_url( 'burgers-menu' ),
									'description' => 'Compare Big Mac pricing against the wider burger ladder and meal context.',
								),
								array(
									'label'       => 'Open the live burgers category page',
									'url'         => $this->get_menu_category_page_url( 'burgers' ),
									'description' => 'Jump into the tracked burgers category and item pages.',
								),
								array(
									'label'       => 'Read the deals and McValue guide',
									'url'         => $this->get_seeded_page_url( 'mcdonalds-deals-mcvalue-guide' ),
									'description' => 'Use this when the real question is burger value rather than the standalone Big Mac price.',
								),
							),
						)
					),
				),
				'mcdonalds-app-deals' => array(
					'title'   => 'McDonald&#8217;s App Deals',
					'content' => $this->build_seeded_support_topic_page_content(
						array(
							'intro'         => array(
								'Our McDonald&#8217;s App Deals guide tracks the most useful USA app offers, including McValue bundles, buy-one-add-one deals, meal discounts, rewards offers, and limited-time promotions that can lower the cost of popular items.',
								'This page is especially useful for readers whose final price decision depends more on app participation than on the static board price, because app-only deals can change the real best-value choice across burgers, breakfast, fries, nuggets, and desserts.',
							),
							'highlights'    => array(
								'App pricing can create a different value story from the counter price or delivery total.',
								'Rewards and meal deals often overlap, so the best next step is usually to compare both before ordering.',
								'Local participation can still vary, which is why the live app remains the final checkpoint.',
							),
							'sections'      => array(
								array(
									'heading'    => 'What readers usually compare on an app-deals page',
									'paragraphs' => array(
										'Most users on this page are not simply asking whether the app has deals. They are trying to decide whether an app coupon beats a meal deal, whether points redemption makes more sense than cash savings, and whether the cheapest local order is actually found inside McValue rather than the app banner itself.',
									),
								),
							),
							'related_links' => array(
								array(
									'label'       => 'Read the deals and McValue guide',
									'url'         => $this->get_seeded_page_url( 'mcdonalds-deals-mcvalue-guide' ),
									'description' => 'Compare app savings against the broader McDonald\'s USA value structure.',
								),
								array(
									'label'       => 'Read the rewards guide',
									'url'         => $this->get_seeded_page_url( 'rewards-guide' ),
									'description' => 'Go deeper when the question is about points, redemptions, and repeat-use value.',
								),
								array(
									'label'       => 'Open the live deals category page',
									'url'         => $this->get_menu_category_page_url( 'deals' ),
									'description' => 'See the current tracked deals page inside the live menu directory.',
								),
							),
						)
					),
				),
				'calorie-counter' => array(
					'title'   => 'Calorie Counter',
					'content' => $this->build_seeded_support_topic_page_content(
						array(
							'intro'         => array(
								'The McDonald&#8217;s Menu Prices USA calorie counter page helps you compare menu items by calories so you can spot lighter burgers, breakfast choices, sides, drinks, and dessert options more easily.',
								'Readers typically use this page when calories and value need to be considered together, especially for burgers, breakfast, fries, sweet drinks, and desserts where the final order total can change quickly.',
							),
							'highlights'    => array(
								'Calories usually matter most when readers are comparing a full meal rather than one item in isolation.',
								'Large fries, drink upgrades, and desserts often create the biggest jump in the order.',
								'Ingredient and allergen decisions should still be confirmed with official McDonald\'s sources.',
							),
							'related_links' => array(
								array(
									'label'       => 'Read the nutrition, calories, and allergens guide',
									'url'         => $this->get_seeded_page_url( 'mcdonalds-nutrition-calories-allergens' ),
									'description' => 'Open the main nutrition pillar for broader calorie and allergen context.',
								),
								array(
									'label'       => 'Read the desserts menu prices pillar',
									'url'         => $this->get_seeded_page_url( 'sweets-treats' ),
									'description' => 'Desserts and shakes are one of the most common calorie-comparison follow-ups.',
								),
								array(
									'label'       => 'Read the drinks menu prices pillar',
									'url'         => $this->get_seeded_page_url( 'beverage-menu' ),
									'description' => 'Use the drinks pillar when beverage calories are driving the decision.',
								),
							),
						)
					),
				),
				'breakfast-hours' => array(
					'title'   => 'Breakfast Hours',
					'content' => $this->build_seeded_support_topic_page_content(
						array(
							'intro'         => array(
								'This page explains typical McDonald&#8217;s breakfast hours in the USA, including when breakfast usually ends and which menu items are normally available in the morning window.',
								'Breakfast timing is one of the most important practical filters on a McDonald\'s USA order because breakfast items disappear before many readers have finished comparing prices, calories, and combos.',
							),
							'highlights'    => array(
								'Morning availability often matters more than the headline breakfast price.',
								'Breakfast end times can differ by day, location, and store format.',
								'Use this page with the breakfast menu pages when you need both timing and price context together.',
							),
							'related_links' => array(
								array(
									'label'       => 'Read the breakfast menu prices pillar',
									'url'         => $this->get_seeded_page_url( 'breakfast-menu' ),
									'description' => 'Combine breakfast timing with the full breakfast pricing and value guide.',
								),
								array(
									'label'       => 'Open the live breakfast category page',
									'url'         => $this->get_menu_category_page_url( 'breakfast' ),
									'description' => 'Jump into the tracked breakfast category and item pages.',
								),
								array(
									'label'       => 'Read the deals and McValue guide',
									'url'         => $this->get_seeded_page_url( 'mcdonalds-deals-mcvalue-guide' ),
									'description' => 'Useful when your breakfast decision depends on morning app offers and value structures.',
								),
							),
						)
					),
				),
				'allergen-guide' => array(
					'title'   => 'Allergen Guide',
					'content' => $this->build_seeded_support_topic_page_content(
						array(
							'intro'         => array(
								'Our allergen guide explains how to approach McDonald&#8217;s USA menu choices more carefully, but always use the official McDonald&#8217;s allergen tool and restaurant information for final decisions.',
								'This page supports the broader pricing and category pages by showing readers where extra verification matters most before they move from research into a final food decision.',
							),
							'highlights'    => array(
								'Use unofficial menu guides for planning, comparison, and narrowing choices.',
								'Use official McDonald\'s sources for final allergen, ingredient, and preparation checks.',
								'Breakfast, burgers, nuggets, desserts, and drinks are the most common ingredient research paths.',
							),
							'related_links' => array(
								array(
									'label'       => 'Read the nutrition, calories, and allergens guide',
									'url'         => $this->get_seeded_page_url( 'mcdonalds-nutrition-calories-allergens' ),
									'description' => 'Use the main nutrition pillar for broader ingredient, calorie, and menu-comparison context.',
								),
								array(
									'label'       => 'Read the breakfast menu prices pillar',
									'url'         => $this->get_seeded_page_url( 'breakfast-menu' ),
									'description' => 'Breakfast is one of the most common allergen and ingredient research paths.',
								),
								array(
									'label'       => 'Read the burgers menu prices pillar',
									'url'         => $this->get_seeded_page_url( 'burgers-menu' ),
									'description' => 'Use the burgers pillar when the ingredient question is tied to a burger choice.',
								),
							),
						)
					),
				),
				'price-history' => array(
					'title'   => 'Price History',
					'content' => $this->build_seeded_support_topic_page_content(
						array(
							'intro'         => array(
								'The McDonald&#8217;s Menu Prices USA price history page tracks how popular menu prices have changed over time, helping readers compare current pricing with previous months and seasonal promotions.',
								'Readers usually land here because they want context for a price increase or because a current local total feels different from what they remember paying before.',
							),
							'highlights'    => array(
								'Price-history pages work best when used alongside current menu pages, not instead of them.',
								'Limited-time items, app deals, and regional pricing can distort year-to-year comparisons.',
								'Flagship burgers, breakfast staples, nuggets, fries, and drinks are usually the clearest comparison anchors.',
							),
							'related_links' => array(
								array(
									'label'       => 'Read the prices by state pillar',
									'url'         => $this->get_seeded_page_url( 'mcdonalds-prices-by-state' ),
									'description' => 'Use the regional pillar when the real question is local variation rather than time-based change alone.',
								),
								array(
									'label'       => 'Read the burgers menu prices pillar',
									'url'         => $this->get_seeded_page_url( 'burgers-menu' ),
									'description' => 'Big Mac and burger comparisons are one of the clearest ways to track price movement over time.',
								),
								array(
									'label'       => 'Read the breakfast menu prices pillar',
									'url'         => $this->get_seeded_page_url( 'breakfast-menu' ),
									'description' => 'Breakfast staples also make strong history anchors because readers remember them well.',
								),
							),
						)
					),
				),
				'delivery-guide' => array(
					'title'   => 'Delivery Guide',
					'content' => $this->build_seeded_support_topic_page_content(
						array(
							'intro'         => array(
								'This delivery guide explains what to expect when ordering McDonald&#8217;s USA through delivery platforms, including price differences, fees, bundle availability, and app-linked promotions.',
								'Delivery can change the real menu-price story more than many readers expect because the delivered total is shaped by platform fees, bundled promotions, menu availability, and location-specific service coverage.',
							),
							'highlights'    => array(
								'The cheapest in-store order is not always the cheapest delivered order.',
								'Delivery menus can differ from in-store menus and app pickup menus.',
								'Deals, McValue, and rewards pages are the best follow-up when you want to compare delivery against pickup or counter value.',
							),
							'related_links' => array(
								array(
									'label'       => 'Read the deals and McValue guide',
									'url'         => $this->get_seeded_page_url( 'mcdonalds-deals-mcvalue-guide' ),
									'description' => 'Compare delivery totals against the current value structure before ordering.',
								),
								array(
									'label'       => 'Read the McDonald\'s app deals page',
									'url'         => $this->get_seeded_page_url( 'mcdonalds-app-deals' ),
									'description' => 'Use this when pickup or app-led ordering may beat delivery on final cost.',
								),
								array(
									'label'       => 'Read the prices by state pillar',
									'url'         => $this->get_seeded_page_url( 'mcdonalds-prices-by-state' ),
									'description' => 'Regional pricing is one of the reasons delivered totals vary so much across markets.',
								),
							),
						)
					),
				),
				'rewards-guide' => array(
					'title'   => 'Rewards Guide',
					'content' => $this->build_seeded_support_topic_page_content(
						array(
							'intro'         => array(
								'This guide covers how MyMcDonald&#8217;s Rewards fits into current USA pricing, including points, redemptions, app-only discounts, and how rewards interact with meal deals.',
								'Rewards matter because many readers are not only comparing menu prices. They are also deciding whether to pay cash, redeem points, or stack a local app offer into the same order path.',
							),
							'highlights'    => array(
								'Rewards change the practical value of burgers, breakfast, fries, drinks, and desserts.',
								'The most useful comparison is often rewards versus a meal deal, not rewards in isolation.',
								'The live app remains the final authority on what a reader can redeem locally.',
							),
							'related_links' => array(
								array(
									'label'       => 'Read the McDonald\'s app deals page',
									'url'         => $this->get_seeded_page_url( 'mcdonalds-app-deals' ),
									'description' => 'Open the app page when the question is about live offers and app-led savings.',
								),
								array(
									'label'       => 'Read the deals and McValue guide',
									'url'         => $this->get_seeded_page_url( 'mcdonalds-deals-mcvalue-guide' ),
									'description' => 'Compare points value against the broader budget and meal-deal strategy.',
								),
								array(
									'label'       => 'Open the live deals category page',
									'url'         => $this->get_menu_category_page_url( 'deals' ),
									'description' => 'See the current tracked deals page inside the live directory.',
								),
							),
						)
					),
				),
				'limited-time-menu' => array(
					'title'   => 'Limited-Time Menu',
					'content' => $this->build_seeded_support_topic_page_content(
						array(
							'intro'         => array(
								'This page highlights current limited-time McDonald&#8217;s USA menu items, seasonal sandwiches, desserts, breakfast collaborations, and short-run deal bundles that may not stay on the menu for long.',
								'Limited-time pages are valuable because they help readers separate evergreen menu pricing from short-run releases that can quickly change the burger, dessert, fries, breakfast, and deals conversation.',
							),
							'highlights'    => array(
								'Limited-time menu pages are most useful when paired with the live what\'s-new category page.',
								'Short-run items can temporarily reset normal value comparisons inside burgers, breakfast, and desserts.',
								'Regional availability and app promotion support can still vary by restaurant.',
							),
							'related_links' => array(
								array(
									'label'       => 'Open the live what\'s-new category page',
									'url'         => $this->get_menu_category_page_url( 'whats-new' ),
									'description' => 'See the tracked limited-time item cards and individual item pages.',
								),
								array(
									'label'       => 'Read the burgers menu prices pillar',
									'url'         => $this->get_seeded_page_url( 'burgers-menu' ),
									'description' => 'Use this when a limited-time burger needs wider comparison against core burgers.',
								),
								array(
									'label'       => 'Read the deals and McValue guide',
									'url'         => $this->get_seeded_page_url( 'mcdonalds-deals-mcvalue-guide' ),
									'description' => 'Helpful when a short-run item also appears inside a bundle, app promotion, or meal-deal context.',
								),
							),
						)
					),
				),
				'snack-wrap' => array(
					'title'   => 'Snack Wrap',
					'content' => $this->build_seeded_support_topic_page_content(
						array(
							'intro'         => array(
								'This guide tracks the current McDonald&#8217;s USA snack wrap lineup, including spicy and ranch builds, pricing, and calories.',
								'Snack Wrap interest is usually tied to lighter lunch decisions, lower-entry chicken comparisons, or the question of whether a wrap order makes more sense than a sandwich or nugget meal.',
							),
							'highlights'    => array(
								'Readers normally compare Snack Wrap pricing against McChicken, McCrispy, and nuggets rather than against wraps alone.',
								'Snack Wrap pages work best when paired with the chicken-and-fish pillar and the live wrap category page.',
								'Calories, sauce choice, and add-on sides still shape the final order value.',
							),
							'related_links' => array(
								array(
									'label'       => 'Open the live Snack Wrap category page',
									'url'         => $this->get_menu_category_page_url( 'snackwrap' ),
									'description' => 'See the tracked wrap cards and individual Snack Wrap item pages.',
								),
								array(
									'label'       => 'Read the chicken and fish menu prices pillar',
									'url'         => $this->get_seeded_page_url( 'chicken-fish-menu' ),
									'description' => 'Compare Snack Wrap value against the wider chicken menu.',
								),
								array(
									'label'       => 'Read the deals and McValue guide',
									'url'         => $this->get_seeded_page_url( 'mcdonalds-deals-mcvalue-guide' ),
									'description' => 'Use this when the Snack Wrap question is really about lower-cost order building.',
								),
							),
						)
					),
				),
				'dollar-menu' => array(
					'title'   => '$1 $2 $3 Menu',
					'content' => $this->build_seeded_support_topic_page_content(
						array(
							'intro'         => array(
								'This guide covers the budget-focused McDonald&#8217;s USA value lineup, including lower-cost breakfast picks, burgers, nuggets, fries, and other entry-price items.',
								'Readers still search for a dollar-menu-style answer even when the live savings structure is now shaped by McValue, meal deals, and app offers rather than one simple national low-price menu.',
							),
							'highlights'    => array(
								'Use this page when the real question is the lowest realistic spend rather than a premium combo meal.',
								'The best next step is usually the deals and McValue pillar because that reflects the live structure more accurately.',
								'Breakfast, burgers, nuggets, fries, and dessert add-ons are the most common low-entry comparison paths.',
							),
							'related_links' => array(
								array(
									'label'       => 'Read the deals and McValue guide',
									'url'         => $this->get_seeded_page_url( 'mcdonalds-deals-mcvalue-guide' ),
									'description' => 'Open the main value pillar for the current McDonald\'s USA savings structure.',
								),
								array(
									'label'       => 'Open the live McValue category page',
									'url'         => $this->get_menu_category_page_url( 'mcvalue' ),
									'description' => 'See the tracked current value items and their item pages.',
								),
								array(
									'label'       => 'Read the extra value meals guide',
									'url'         => $this->get_seeded_page_url( 'extra-value-meals' ),
									'description' => 'Use this when the low-cost question turns into a full combo-meal comparison.',
								),
							),
						)
					),
				),
				'extra-value-meals' => array(
					'title'   => 'Extra Value Meals',
					'content' => $this->build_seeded_support_topic_page_content(
						array(
							'intro'         => array(
								'This guide covers McDonald&#8217;s USA combo meals for breakfast, lunch, and dinner, including burger meals, chicken meals, fish meals, and wrap meal pricing.',
								'Extra Value Meals matter because many readers do not buy a standalone sandwich. They want the real full-order comparison with fries and a drink included, which makes meal pricing more useful than the base item price alone.',
							),
							'highlights'    => array(
								'Meal pricing helps users compare the true full-order cost instead of only the main item.',
								'Breakfast, burgers, chicken, fish, nuggets, and wraps all feed into the wider meal comparison path.',
								'App offers and McValue bundles can still compete with traditional meal pricing, so value pages matter here too.',
							),
							'related_links' => array(
								array(
									'label'       => 'Open the live extra value meals category page',
									'url'         => $this->get_menu_category_page_url( 'meals' ),
									'description' => 'See the tracked meal cards and item pages inside the live directory.',
								),
								array(
									'label'       => 'Read the deals and McValue guide',
									'url'         => $this->get_seeded_page_url( 'mcdonalds-deals-mcvalue-guide' ),
									'description' => 'Compare standard meal pricing against bundle and app-led savings.',
								),
								array(
									'label'       => 'Open the full menu directory',
									'url'         => $this->get_menu_directory_root_url(),
									'description' => 'Jump back to the full menu when you need the category-level context behind a meal.',
								),
							),
						)
					),
				),
				'shareables-bundles' => array(
					'title'   => 'Shareables & Bundles',
					'content' => $this->build_seeded_support_topic_page_content(
						array(
							'intro'         => array(
								'This guide covers larger McDonald&#8217;s USA share boxes and bundles, including 40-piece McNuggets, large fries packs, cookie totes, and family-style ordering ideas.',
								'Shareable pages help readers who are building group orders rather than solo meals. That usually changes what value means, because the best family-style total can come from nuggets, fries, desserts, drinks, or a mix of separate categories.',
							),
							'highlights'    => array(
								'Group orders should be compared by total usefulness, not just by the biggest headline pack.',
								'Shareables connect most strongly with nuggets, fries, desserts, and drinks pages.',
								'App participation and local availability can still affect which bundles actually appear at checkout.',
							),
							'related_links' => array(
								array(
									'label'       => 'Read the nuggets and strips menu prices pillar',
									'url'         => $this->get_seeded_page_url( 'nuggets-and-strips' ),
									'description' => 'Nuggets are one of the strongest shareable-order anchors on the site.',
								),
								array(
									'label'       => 'Read the fries and sides menu prices pillar',
									'url'         => $this->get_seeded_page_url( 'fries-sides' ),
									'description' => 'Fries often decide the side-order cost of a group meal.',
								),
								array(
									'label'       => 'Read the drinks menu prices pillar',
									'url'         => $this->get_seeded_page_url( 'beverage-menu' ),
									'description' => 'Drinks often complete the group-order value comparison.',
								),
							),
						)
					),
				),
			),
			$this->get_seeded_pillar_support_pages()
		);
	}

	/**
	 * Return the long-form pillar support pages used for topical SEO coverage.
	 *
	 * @return array<string, array<string, string>>
	 */
	protected function get_seeded_pillar_support_pages() {
		return array(
			'breakfast-menu' => array(
				'title'   => "McDonald's Breakfast Menu Prices USA",
				'content' => $this->build_seeded_category_pillar_page_content(
					array(
						'category'               => 'breakfast',
						'page_label'             => "McDonald's Breakfast Menu Prices USA",
						'focus_label'            => "McDonald's breakfast menu",
						'include_items'          => array( 'McMuffins', 'biscuits', 'McGriddles', 'bagels', 'hotcakes', 'oatmeal', 'Hash Browns' ),
						'value_points'           => array(
							'Standalone sandwiches usually make the easiest price-to-satiety comparison when you only want breakfast and coffee.',
							'Meal pricing matters most when you also want Hash Browns and a drink, because that is where breakfast totals rise quickly.',
							'Breakfast buy-one-add-one offers and app deals can change the real value equation versus the posted menu price.',
						),
						'nutrition_points'       => array(
							'Bagels, platters, and larger breakfast sandwiches can climb faster in calories than the simplest McMuffin builds.',
							'Hash Browns are one of the most searched breakfast add-ons because they affect both total calories and total spend.',
							'Readers who want a lighter breakfast usually compare oatmeal, smaller sandwiches, and coffee choices before adding sides.',
						),
						'availability_paragraphs' => array(
							'Breakfast is one of the most time-sensitive areas of the McDonald\'s USA menu. Many readers are not just comparing prices; they are checking whether they can still order the item before breakfast service ends at their local restaurant.',
							'That is why this breakfast pillar works best alongside the separate breakfast-hours guide and the live breakfast category page. Use this page for the broader price and menu overview, then use the item pages for focused checks on one sandwich, one meal, or one breakfast side.',
						),
						'featured_items'         => array( 'Egg McMuffin', 'Sausage McMuffin with Egg', 'Sausage Biscuit', 'Hash Browns', 'Hotcakes' ),
						'faq_items'              => array(
							array(
								'question' => 'What are the most searched McDonald\'s breakfast items in the USA?',
								'answer'   => 'The most compared breakfast items on menu-price sites are usually Egg McMuffin, Sausage McMuffin with Egg, Sausage Biscuit, Hash Browns, Hotcakes, and breakfast combo meals. They cover the main price tiers readers care about: classic sandwich, heavier sandwich, side item, and full breakfast order.',
							),
							array(
								'question' => 'Does the McDonald\'s breakfast menu price include a drink?',
								'answer'   => 'Usually not. On most menu pages the sandwich price and the meal price are different numbers, so readers need to decide whether they are comparing a single breakfast item or the upgraded combo total with a drink and Hash Browns.',
							),
							array(
								'question' => 'Why do breakfast prices vary from one McDonald\'s location to another?',
								'answer'   => 'Breakfast prices can change because of franchise-level pricing, state and city operating costs, local taxes, app participation, and whether you are ordering in store, in the app, or through delivery. The site shows dollar prices for planning, but the checkout total may still move slightly.',
							),
							array(
								'question' => 'What is the best way to use this breakfast guide?',
								'answer'   => 'Use this pillar to understand the overall breakfast lineup, compare value and calories, and identify the items worth opening next. Then move into the live breakfast category page or individual item pages when you need one exact sandwich, meal, or side.',
							),
						),
						'related_links'          => array(
							array(
								'label'       => 'Browse the live breakfast category page',
								'url'         => $this->get_menu_category_page_url( 'breakfast' ),
								'description' => 'Open the managed breakfast directory with all tracked breakfast items and their item pages.',
							),
							array(
								'label'       => 'Check breakfast hours',
								'url'         => $this->get_seeded_page_url( 'breakfast-hours' ),
								'description' => 'See the timing context that matters most when breakfast availability is the real question.',
							),
							array(
								'label'       => 'Open the full menu directory',
								'url'         => $this->get_menu_directory_root_url(),
								'description' => 'Jump back to the full menu if you also want burgers, deals, drinks, and desserts.',
							),
							array(
								'label'       => 'Read the deals and McValue guide',
								'url'         => $this->get_seeded_page_url( 'mcdonalds-deals-mcvalue-guide' ),
								'description' => 'Compare breakfast pricing against the current savings and app-led value story.',
							),
						),
					)
				),
			),
			'burgers-menu' => array(
				'title'   => "McDonald's Burgers Menu Prices USA",
				'content' => $this->build_seeded_category_pillar_page_content(
					array(
						'category'        => 'burgers',
						'page_label'      => "McDonald's Burgers Menu Prices USA",
						'focus_label'     => "McDonald's burgers menu",
						'include_items'   => array( 'Big Mac', 'Quarter Pounder builds', 'McDouble', 'Daily Double', 'cheeseburgers', 'hamburgers', 'limited-time burger releases' ),
						'value_points'    => array(
							'Burger buyers usually compare three tiers: signature burgers, mid-tier doubles, and the cheapest single-burger options.',
							'Meal pricing can matter more than sandwich pricing when fries and a drink are already part of the order plan.',
							'Limited-time burger launches such as BIG ARCH-style releases create short windows where price, hype, and curiosity all rise together.',
						),
						'nutrition_points' => array(
							'Double-patty Quarter Pounder builds and limited-time larger burgers sit at a very different calorie level from simple cheeseburgers.',
							'Burger add-ons such as bacon, extra cheese, and meal upgrades matter because they push both price and calories upward quickly.',
							'Many readers want the burger page to answer value questions and nutrition questions at the same time, not as separate research tasks.',
						),
						'availability_paragraphs' => array(
							'Burger pricing is one of the clearest signals of how McDonald\'s USA balances value and signature ordering. A burger pillar has to do more than list names: it needs to explain where Big Mac and Quarter Pounder pricing sits relative to McDouble, Hamburger, and combo meal pricing.',
							'That is also where a category pillar helps more than a single-item post. Readers can decide whether they want the flagship burger, the best value burger, or the filling burger meal before they jump to an individual item page.',
						),
						'featured_items'  => array( 'Big Mac', 'Quarter Pounder with Cheese', 'Double Quarter Pounder with Cheese', 'McDouble', 'Hamburger', 'The BIG ARCH' ),
						'faq_items'       => array(
							array(
								'question' => 'Which McDonald\'s burgers are compared most often?',
								'answer'   => 'The biggest comparison cluster is Big Mac versus Quarter Pounder with Cheese versus McDouble. Those three products cover signature burger pricing, quarter-pound beef positioning, and lower-cost double-beef value in one glance.',
							),
							array(
								'question' => 'Why is a burger meal usually a better comparison than the sandwich alone?',
								'answer'   => 'Many customers do not order a burger on its own. They are deciding between complete meals, so fries, drink size, and the meal upgrade cost often matter more than a single posted sandwich price.',
							),
							array(
								'question' => 'Do burger prices stay the same across the USA?',
								'answer'   => 'No. Burger pricing changes by market, store operator, taxes, promotions, and ordering channel. A burger guide should be treated as a current planning resource rather than a promise that every location will show the same total.',
							),
							array(
								'question' => 'What makes the burgers pillar different from a single Big Mac page?',
								'answer'   => 'The pillar answers menu-level comparison questions. It helps readers understand the whole burger ladder, then move into the exact burger page once they know which item they actually want to compare in detail.',
							),
						),
						'related_links'   => array(
							array(
								'label'       => 'Browse the live burgers category page',
								'url'         => $this->get_menu_category_page_url( 'burgers' ),
								'description' => 'Open the full burgers listing with tracked item cards and dedicated burger item pages.',
							),
							array(
								'label'       => 'Check Big Mac pricing',
								'url'         => $this->get_seeded_page_url( 'big-mac-price-usa' ),
								'description' => 'Use the focused Big Mac guide if that is the one burger you need to compare first.',
							),
							array(
								'label'       => 'Read the nutrition and allergens guide',
								'url'         => $this->get_seeded_page_url( 'mcdonalds-nutrition-calories-allergens' ),
								'description' => 'Compare burger calories, ingredients, and allergen considerations with the broader nutrition pillar.',
							),
							array(
								'label'       => 'Read the deals and McValue guide',
								'url'         => $this->get_seeded_page_url( 'mcdonalds-deals-mcvalue-guide' ),
								'description' => 'See how burger prices line up against McValue meal deals and app-led savings.',
							),
						),
					)
				),
			),
			'chicken-fish-menu' => array(
				'title'   => "McDonald's Chicken & Fish Menu Prices USA",
				'content' => $this->build_seeded_category_pillar_page_content(
					array(
						'category'        => 'chickenfish',
						'page_label'      => "McDonald's Chicken & Fish Menu Prices USA",
						'focus_label'     => "McDonald's chicken and fish menu",
						'include_items'   => array( 'McCrispy sandwiches', 'spicy builds', 'deluxe builds', 'McChicken', 'Filet-O-Fish', 'Snack Wrap crossovers' ),
						'value_points'    => array(
							'Chicken buyers often compare premium McCrispy sandwiches with lower-entry McChicken pricing before looking at meal totals.',
							'Fish pricing matters differently because Filet-O-Fish readers are usually checking a specific preference item rather than an entire value tier.',
							'Snack Wrap attention changes the category because it pulls lighter and lower-entry comparisons into the same decision path.',
						),
						'nutrition_points' => array(
							'Spicy and deluxe builds can change both calories and perceived value versus a standard chicken sandwich.',
							'The difference between a sandwich alone and a meal matters here because fries and drink upgrades can widen the final total quickly.',
							'Readers usually want to compare texture, spice level, and calories at the same time when choosing among McCrispy variations.',
						),
						'availability_paragraphs' => array(
							'This category is especially useful for readers deciding between burger alternatives, spicy chicken options, and fish sandwiches without bouncing between multiple pages. It also helps families and group orders when one person wants chicken and another wants nuggets or a wrap-style option.',
							'That is why the chicken and fish pillar links outward to nuggets, strips, deals, and nutrition resources. The real buying decision is often broader than one sandwich, even when the search starts with one specific menu name.',
							'Chicken and fish readers also tend to compare texture, spice, price, and meal practicality together. Some are looking for the crispier premium sandwich, some want the cheapest non-beef option, and some simply want to know whether Filet-O-Fish or a McCrispy build makes more sense for the money. That wider comparison intent is why the pillar adds real explanation instead of acting like a short ingredient list.',
							'In practice, this means the category often works as a decision bridge between burgers on one side and nuggets or wraps on the other. The page helps readers understand where the chicken-and-fish lineup sits in the wider menu instead of forcing them to compare only one sandwich at a time.',
						),
						'featured_items'  => array( 'McCrispy', 'Spicy McCrispy', 'Deluxe McCrispy', 'McChicken', 'Filet-O-Fish', 'Spicy Snack Wrap' ),
						'faq_items'       => array(
							array(
								'question' => 'What items are included in McDonald\'s chicken and fish pricing coverage?',
								'answer'   => 'The category usually includes the core chicken sandwich range, spicy and deluxe variants, McChicken, Filet-O-Fish, and wrap-style products or related chicken entries when they are live in the attached menu data.',
							),
							array(
								'question' => 'Why do readers compare chicken and fish together?',
								'answer'   => 'Because they often serve the same search intent: a non-beef sandwich choice at McDonald\'s. Many people want the best alternative to a burger, so a shared category overview makes the comparison faster.',
							),
							array(
								'question' => 'Do chicken sandwich meal prices matter more than the item price?',
								'answer'   => 'Often yes. A large share of customers compare the complete order, not the sandwich alone, so fries, drink size, and any app discounts can make the meal price the more practical number.',
							),
							array(
								'question' => 'What is the next step after reading this pillar?',
								'answer'   => 'Use this guide to narrow the shortlist, then move into the live category page or a dedicated item page such as McCrispy, McChicken, or Filet-O-Fish when you need the exact current listing.',
							),
						),
						'related_links'   => array(
							array(
								'label'       => 'Browse the live chicken and fish category page',
								'url'         => $this->get_menu_category_page_url( 'chickenfish' ),
								'description' => 'Open the managed chicken-and-fish directory with live item cards and direct item links.',
							),
							array(
								'label'       => 'Open the nuggets and strips pillar',
								'url'         => $this->get_seeded_page_url( 'nuggets-and-strips' ),
								'description' => 'Compare sandwich orders against McNuggets and McCrispy Strips ordering paths.',
							),
							array(
								'label'       => 'Read the deals and McValue guide',
								'url'         => $this->get_seeded_page_url( 'mcdonalds-deals-mcvalue-guide' ),
								'description' => 'Check where chicken meal deals and app offers affect the final value.',
							),
							array(
								'label'       => 'Read the nutrition and allergens guide',
								'url'         => $this->get_seeded_page_url( 'mcdonalds-nutrition-calories-allergens' ),
								'description' => 'Compare chicken sandwich calories, ingredients, and allergen considerations.',
							),
						),
					)
				),
			),
			'nuggets-and-strips' => array(
				'title'   => "McDonald's McNuggets & Strips Menu Prices USA",
				'content' => $this->build_seeded_category_pillar_page_content(
					array(
						'category'        => 'nuggets',
						'page_label'      => "McDonald's McNuggets & Strips Menu Prices USA",
						'focus_label'     => "McDonald's McNuggets and strips menu",
						'include_items'   => array( '4-piece nuggets', '6-piece nuggets', '10-piece nuggets', '20-piece nuggets', '40-piece shareables', 'McCrispy Strips' ),
						'value_points'    => array(
							'Portion size is the first comparison point here because nugget buyers often scale their order to snack, meal, or group size.',
							'Sauce choice can alter the perceived value of the order when extra sauces are paid add-ons rather than included dips.',
							'Shareable boxes matter because the best value per piece is not always the same as the easiest order for one person.',
						),
						'nutrition_points' => array(
							'Calories rise quickly with larger McNuggets counts and meal upgrades, even when the per-piece price looks attractive.',
							'McCrispy Strips change the comparison because readers start judging nuggets versus strips, not just one nugget portion versus another.',
							'Parents and family buyers often compare nugget pricing with Happy Meal pricing, which makes cross-category links especially useful.',
						),
						'availability_paragraphs' => array(
							'This pillar is useful because nuggets are searched in several different ways: by piece count, by meal size, by family/share order, and by sauce pairing. A short item list does not explain those decision paths clearly enough on its own.',
							'Use the category-level guide when you want to compare order sizes quickly, then move to the individual nugget or strips page when you need one exact portion or meal listing.',
							'It is also one of the best menu areas for comparing convenience versus value. A small nugget order, a 10-piece meal, a shareable box, and a strips-based order can all serve completely different situations even though they live in the same category. That difference matters for both search intent and for the real-world buying decision the reader is trying to make.',
							'Because of that flexibility, nuggets and strips also connect naturally to family ordering, app deals, sauces, and Happy Meal planning. The pillar gives readers a faster route through those connected questions than a thin price table ever could.',
						),
						'featured_items'  => array( '10 pc Chicken McNuggets', '20 pc Chicken McNuggets', '40 pc Chicken McNuggets', 'McCrispy Strips', '4 pc Chicken McNuggets', '6 pc Chicken McNuggets' ),
						'faq_items'       => array(
							array(
								'question' => 'Why do McNuggets piece counts matter so much on a price page?',
								'answer'   => 'Because the ordering intent changes with the count. A 4-piece or 6-piece order is usually a snack or child-focused comparison, while 10, 20, and 40 pieces move into meal, sharing, and family-value territory.',
							),
							array(
								'question' => 'Should nuggets be compared to sandwiches or to Happy Meals?',
								'answer'   => 'Both. Nuggets overlap with sandwich ordering for solo meals and with Happy Meals for family orders, so a good nuggets pillar helps readers move to the next most relevant page instead of trapping them in one narrow view.',
							),
							array(
								'question' => 'Do sauce choices change the real total?',
								'answer'   => 'Yes, especially if the restaurant charges for extra sauces beyond the included dips. That is why nuggets and strips work best when linked with the sauces page and the fries-and-sides page.',
							),
							array(
								'question' => 'What is the best next click after this page?',
								'answer'   => 'If you already know the size you want, open the exact item page. If you are still comparing a solo meal versus a family order, move next to the deals guide, sauces guide, or Happy Meal pillar.',
							),
						),
						'related_links'   => array(
							array(
								'label'       => 'Browse the live nuggets and strips category page',
								'url'         => $this->get_menu_category_page_url( 'nuggets' ),
								'description' => 'Open the managed nuggets directory with every tracked portion and its dedicated item page.',
							),
							array(
								'label'       => 'Open the Happy Meal pillar',
								'url'         => $this->get_seeded_page_url( 'happy-meal-menu' ),
								'description' => 'Compare nugget pricing against the kids-meal ordering path.',
							),
							array(
								'label'       => 'Open the sauces and condiments pillar',
								'url'         => $this->get_seeded_page_url( 'sauces-condiments' ),
								'description' => 'See how dips and condiments affect the full nuggets order.',
							),
							array(
								'label'       => 'Read the deals and McValue guide',
								'url'         => $this->get_seeded_page_url( 'mcdonalds-deals-mcvalue-guide' ),
								'description' => 'Compare nuggets pricing against McValue and meal-deal options.',
							),
						),
					)
				),
			),
			'fries-sides' => array(
				'title'   => "McDonald's Fries & Sides Prices USA",
				'content' => $this->build_seeded_category_pillar_page_content(
					array(
						'category'        => 'sides',
						'page_label'      => "McDonald's Fries & Sides Prices USA",
						'focus_label'     => "McDonald's fries and sides menu",
						'include_items'   => array( 'small fries', 'medium fries', 'large fries', 'Apple Slices', 'breakfast side crossovers such as Hash Browns' ),
						'value_points'    => array(
							'Fries size comparison is the most practical pricing task in this category because size upgrades affect almost every combo order.',
							'Side-item pricing matters more than many readers expect because it shapes the final meal total after the main item has already been chosen.',
							'Apple Slices and lighter side options matter when parents or calorie-conscious readers are comparing alternatives to fries.',
						),
						'nutrition_points' => array(
							'Fries size changes both calories and value perception at the same time, which is why readers often search this category after already deciding on a burger or nugget order.',
							'Some of the biggest calorie jumps on McDonald\'s orders happen when drinks and fries are upsized together rather than when the sandwich itself changes.',
							'This category is especially helpful when you want to see whether a small side keeps the order balanced or whether a full meal upgrade is the better choice.',
						),
						'availability_paragraphs' => array(
							'Fries and sides look simple, but they sit at the center of most combo decisions. The side category tells readers how much of the final total comes from the add-on portion of the order rather than the headline burger, chicken, or breakfast item.',
							'That makes this page a useful bridge between the main entrée pages and the broader deals guide. It helps explain how a cheap item can turn into a meaningfully larger checkout total once the side strategy changes.',
							'Readers also use the sides category to judge whether a combo is really worth taking. If the fries size increase, extra sauce, or side swap changes the total more than expected, the best value path can shift immediately. That makes this page an important comparison layer rather than a minor add-on page.',
							'It also makes the sides pillar one of the clearest places to understand portion logic on the McDonald\'s menu. When readers know how much the side decision changes the order, they can compare burgers, nuggets, breakfast, and Happy Meals more realistically.',
							'That same logic makes fries and sides one of the strongest support categories for SEO too. A lot of McDonald\'s searches that sound like burger or nuggets searches are really side-upgrade questions underneath, and this page helps answer that hidden intent directly.',
						),
						'featured_items'  => array( 'Small World Famous Fries', 'Medium World Famous Fries', 'World Famous Fries Large', 'Apple Slices' ),
						'faq_items'       => array(
							array(
								'question' => 'Why is the fries size comparison so important?',
								'answer'   => 'Because fries are one of the most common upgrades in a McDonald\'s order. Even readers who already know the sandwich they want often use a sides page to decide whether the meal upgrade really adds value.',
							),
							array(
								'question' => 'Are fries and other sides part of most combo decisions?',
								'answer'   => 'Yes. Fries sit at the center of burger, chicken, nugget, and deal comparisons, so the side page is one of the easiest places to understand how a final total is built.',
							),
							array(
								'question' => 'What side alternatives matter most for families or lower-calorie orders?',
								'answer'   => 'Apple Slices and smaller side sizes matter most when readers want to reduce spend or calories while keeping the order complete enough to feel like a real meal.',
							),
							array(
								'question' => 'What should I open after this page?',
								'answer'   => 'Use the fries and sides pillar before moving to the relevant burger, nuggets, Happy Meal, or deals page. The order makes more sense once you know whether you are building around a small side, a full combo, or a lighter alternative.',
							),
						),
						'related_links'   => array(
							array(
								'label'       => 'Browse the live fries and sides category page',
								'url'         => $this->get_menu_category_page_url( 'sides' ),
								'description' => 'Open the live sides directory with current tracked fries sizes and side-item pages.',
							),
							array(
								'label'       => 'Open the sauces and condiments pillar',
								'url'         => $this->get_seeded_page_url( 'sauces-condiments' ),
								'description' => 'Compare sides with the dips and packets most readers add next.',
							),
							array(
								'label'       => 'Read the deals and McValue guide',
								'url'         => $this->get_seeded_page_url( 'mcdonalds-deals-mcvalue-guide' ),
								'description' => 'See how fries fit inside current meal deals and value combinations.',
							),
							array(
								'label'       => 'Read the nutrition and allergens guide',
								'url'         => $this->get_seeded_page_url( 'mcdonalds-nutrition-calories-allergens' ),
								'description' => 'Use the nutrition pillar when calories matter as much as price.',
							),
						),
					)
				),
			),
			'happy-meal-menu' => array(
				'title'   => "McDonald's Happy Meal Prices USA",
				'content' => $this->build_seeded_category_pillar_page_content(
					array(
						'category'        => 'happymeal',
						'page_label'      => "McDonald's Happy Meal Prices USA",
						'focus_label'     => "McDonald's Happy Meal menu",
						'include_items'   => array( 'Hamburger Happy Meal', '4-piece McNuggets Happy Meal', '6-piece McNuggets Happy Meal', 'kid-focused side and drink choices' ),
						'value_points'    => array(
							'Happy Meal price comparisons are usually about the full kids-meal build rather than a single entrée item.',
							'Parents often compare Hamburger Happy Meal pricing against nugget-based Happy Meals to judge the best family-value mix.',
							'Happy Meal value is shaped by what is bundled, so it makes sense to compare it against nuggets, fries, and drink pricing rather than only against adult sandwiches.',
						),
						'nutrition_points' => array(
							'Happy Meal calories matter because parents often compare the kid-focused total against a separate adult order plus a side.',
							'The side and drink choice can change the overall balance of the meal, even when the headline entrée stays the same.',
							'This category works best when paired with the fries, beverages, and nuggets pillars for a full family-order comparison.',
						),
						'availability_paragraphs' => array(
							'Happy Meal searches are often less about one exact product and more about planning an entire family order. That is why this pillar focuses on the meal structure, price tiers, and cross-category decisions that affect the final checkout total.',
							'Use this page to compare the main kids-meal options first, then move into nuggets, fries, drinks, or desserts when you want to build a broader family order around the Happy Meal choice.',
							'Parents also use Happy Meal pages for a different kind of comparison than they use on burger or deals pages. They are not only asking what costs less; they are asking what feels easiest for a child, what looks balanced enough for the occasion, and whether the bundled meal is better than piecing together separate nuggets, fries, slices, and drinks. That makes the category unusually strong for both value and planning intent.',
							'Because of that family-order context, Happy Meal content works best when it connects outward to nuggets, beverages, desserts, and fries instead of pretending the kids-meal decision sits on its own. The pillar helps parents understand the bundle first, then the rest of the site helps them plan the wider group order around it.',
							'That planning role is especially useful for larger orders. Once more than one child or a mixed family meal is involved, readers need the Happy Meal page to work as a bundle guide, not just a list of kids items, so they can compare convenience, price, and add-on choices all in one place.',
							'It also gives the site a stronger family-order layer overall. When readers can move cleanly from Happy Meals into nuggets, fries, drinks, and desserts, the whole menu becomes easier to use for group planning instead of only solo ordering.',
							'That is why this page deserves more depth than a simple kids-menu summary. It sits at the point where bundle value, child-friendly choices, and whole-order convenience all come together in one decision.',
							'For many families, that makes the Happy Meal page the easiest place to start the wider McDonald\'s order plan.',
						),
						'featured_items'  => array( 'Hamburger Happy Meal', '4 pc McNuggets Happy Meal', '6 pc McNuggets Happy Meal' ),
						'faq_items'       => array(
							array(
								'question' => 'What are the main Happy Meal choices on the current USA menu?',
								'answer'   => 'The main tracked Happy Meal options usually center on Hamburger Happy Meal plus nugget-based Happy Meals in different piece counts. Those are the options most families compare first on a price-and-calorie basis.',
							),
							array(
								'question' => 'Why do families compare Happy Meals with nuggets and fries pages too?',
								'answer'   => 'Because the real decision is often broader than one kids meal. Families want to know whether the bundled kids option is better than ordering nuggets, fries, and drinks separately around a larger group order.',
							),
							array(
								'question' => 'Do Happy Meal totals vary by location?',
								'answer'   => 'Yes. Like the rest of the menu, Happy Meal pricing can vary by market, franchise, tax, and app or delivery channel. The pillar gives a reliable planning range, but the live final total still needs a local check.',
							),
							array(
								'question' => 'How should this page be used with the rest of the site?',
								'answer'   => 'Start here for the kids-meal overview, then move to nuggets, fries, beverages, or desserts if you want to understand the bigger family-order picture around the Happy Meal choice.',
							),
						),
						'related_links'   => array(
							array(
								'label'       => 'Browse the live Happy Meal category page',
								'url'         => $this->get_menu_category_page_url( 'happymeal' ),
								'description' => 'Open the managed Happy Meal directory with current tracked kids-meal items.',
							),
							array(
								'label'       => 'Open the nuggets and strips pillar',
								'url'         => $this->get_seeded_page_url( 'nuggets-and-strips' ),
								'description' => 'Compare Happy Meal nugget builds against larger nugget portions and share boxes.',
							),
							array(
								'label'       => 'Open the beverages pillar',
								'url'         => $this->get_seeded_page_url( 'beverage-menu' ),
								'description' => 'Check drink choices that usually matter in a family order.',
							),
							array(
								'label'       => 'Open the sweets and treats pillar',
								'url'         => $this->get_seeded_page_url( 'sweets-treats' ),
								'description' => 'Add dessert context when the meal plan includes cones, pies, or McFlurry picks.',
							),
						),
					)
				),
			),
			'sweets-treats' => array(
				'title'   => "McDonald's Desserts Menu Prices USA",
				'content' => $this->build_seeded_category_pillar_page_content(
					array(
						'category'        => 'sweets',
						'page_label'      => "McDonald's Desserts Menu Prices USA",
						'focus_label'     => "McDonald's desserts menu",
						'include_items'   => array( 'McFlurries', 'mini McFlurry picks', 'sundaes', 'cones', 'apple pie', 'cookies', 'shakes' ),
						'value_points'    => array(
							'Dessert buyers often compare premium McFlurry pricing against simple low-entry treats such as cones, cookies, or pie.',
							'Mini McFlurry and value-led dessert options matter because they sit in a different price bracket than full-size frozen desserts.',
							'Shakes and sundaes can look similar in the menu flow but serve different calorie and value expectations, so side-by-side context matters.',
						),
						'nutrition_points' => array(
							'Dessert calories vary widely, which is why readers often want price and calories together rather than in separate guides.',
							'Frozen desserts, shakes, and baked sweets do not serve the same purpose in an order, so a useful dessert page should explain that difference as well as the raw number.',
							'Readers balancing a meal total often use the dessert pillar after the burger, nuggets, or Happy Meal choice is already made.',
						),
						'availability_paragraphs' => array(
							'Desserts are also where limited-time flavor rotations and special promotional tie-ins show up, which makes freshness especially important. A dessert pillar has to be current enough to show readers whether the headline frozen treat is still part of the active menu mix.',
							'That is why the desserts pillar works as both a planning page and a navigation page. It helps readers move from broad dessert comparison into one exact product page or the limited-time menu guide when a flavor is seasonal.',
						),
						'featured_items'  => array( 'OREO McFlurry', "McFlurry with M&M'S Candies", 'Vanilla Cone', 'Baked Apple Pie', 'Hot Fudge Sundae', 'Chocolate Shake Small' ),
						'faq_items'       => array(
							array(
								'question' => 'What counts as dessert on the McDonald\'s USA menu?',
								'answer'   => 'Dessert coverage on this site includes McFlurry flavors, mini McFlurry options where listed, shakes, sundaes, vanilla cones, cookies, pie, and other sweet items surfaced in the attached USA menu data.',
							),
							array(
								'question' => 'Why do McFlurry prices matter so much in dessert comparisons?',
								'answer'   => 'Because McFlurry products are usually the premium frozen-dessert comparison point. Readers often want to know whether a full McFlurry is worth the jump over a cone, pie, cookie, or mini dessert option.',
							),
							array(
								'question' => 'Do dessert prices and availability change often?',
								'answer'   => 'They can, especially around promotional flavors, McValue dessert picks, and limited-time product runs. That is why dessert content needs a freshness mindset instead of one static frozen menu list.',
							),
							array(
								'question' => 'What should I read after the desserts pillar?',
								'answer'   => 'If you want the broad frozen-and-sweet overview, stay with this pillar. If you want a current live listing, open the desserts category page. If your real question is value, move next to the deals and McValue guide.',
							),
						),
						'related_links'   => array(
							array(
								'label'       => 'Browse the live desserts category page',
								'url'         => $this->get_menu_category_page_url( 'sweets' ),
								'description' => 'Open the full sweets-and-treats directory with current tracked dessert item pages.',
							),
							array(
								'label'       => 'Read the deals and McValue guide',
								'url'         => $this->get_seeded_page_url( 'mcdonalds-deals-mcvalue-guide' ),
								'description' => 'Compare premium desserts against lower-entry McValue sweet picks.',
							),
							array(
								'label'       => 'Open the limited-time menu guide',
								'url'         => $this->get_seeded_page_url( 'limited-time-menu' ),
								'description' => 'Check short-run dessert flavors and seasonal product changes.',
							),
							array(
								'label'       => 'Read the nutrition and allergens guide',
								'url'         => $this->get_seeded_page_url( 'mcdonalds-nutrition-calories-allergens' ),
								'description' => 'Use the broader nutrition pillar when calories or allergen questions matter more than the treat itself.',
							),
						),
					)
				),
			),
			'mccafe-menu' => array(
				'title'   => "McDonald's McCafe Menu Prices USA",
				'content' => $this->build_seeded_category_pillar_page_content(
					array(
						'category'        => 'mccafe',
						'page_label'      => "McDonald's McCafe Menu Prices USA",
						'focus_label'     => "McDonald's McCafe menu",
						'include_items'   => array( 'hot coffee', 'iced coffee', 'lattes', 'cappuccinos', 'macchiatos', 'frappes', 'hot chocolate' ),
						'value_points'    => array(
							'McCafe pricing often scales by size, so readers need more than one product name to compare properly.',
							'Iced coffee and espresso drinks sit in different value tiers, which is why the category page needs to explain the menu structure rather than only list item names.',
							'McCafe orders are frequently paired with breakfast, which makes this pillar especially useful when readers are comparing the total cost of a morning order.',
						),
						'nutrition_points' => array(
							'Sugar, syrup flavor, whipped toppings, and size changes can affect both calories and the practical value of the drink.',
							'Readers often compare McCafe not just against other McDonald\'s beverages but against outside coffee-shop pricing, which raises the importance of size-specific context.',
							'This category overlaps with desserts because frappe-style drinks and sweet coffee builds behave differently from simple coffee orders in both price and calories.',
						),
						'availability_paragraphs' => array(
							'A coffee pillar needs to work for two different search styles: the reader who wants a simple hot coffee price and the reader who wants to compare several espresso-based drinks before ordering. That is why the McCafe page needs structure, not just one long undifferentiated list.',
							'Use the pillar to understand the larger coffee menu first, then move into the live McCafe category page or an individual drink page when you want one exact size and one exact flavor build.',
						),
						'featured_items'  => array( 'Premium Roast Coffee', 'McCafe Iced Coffee Large', 'McCafe Latte Medium', 'McCafe Caramel Frappe Medium', 'McCafe Hot Chocolate Small' ),
						'faq_items'       => array(
							array(
								'question' => 'What is included in the McCafe menu on this site?',
								'answer'   => 'The McCafe pillar covers hot coffee, iced coffee, espresso drinks, lattes, cappuccinos, macchiatos, frappes, and hot chocolate as surfaced across the current tracked USA coffee data.',
							),
							array(
								'question' => 'Why is size so important on McCafe pages?',
								'answer'   => 'Because McCafe pricing is often built around small, medium, and large drink differences. A useful guide has to show how the menu scales, not just repeat one drink name without context.',
							),
							array(
								'question' => 'Should McCafe be compared with the general beverages page?',
								'answer'   => 'Yes. McCafe handles the coffee and espresso side of the menu, while the beverages pillar covers sodas, tea, juice, smoothies, water, and other non-coffee drinks. Together they show the full drinks picture.',
							),
							array(
								'question' => 'What is the best next click after this page?',
								'answer'   => 'If you already know the exact drink, open its item page. If you are deciding between coffee and non-coffee options, move next to the beverages pillar or the breakfast menu pillar.',
							),
						),
						'related_links'   => array(
							array(
								'label'       => 'Browse the live McCafe category page',
								'url'         => $this->get_menu_category_page_url( 'mccafe' ),
								'description' => 'Open the current coffee directory with tracked sizes, flavors, and drink item pages.',
							),
							array(
								'label'       => 'Open the beverages pillar',
								'url'         => $this->get_seeded_page_url( 'beverage-menu' ),
								'description' => 'Compare McCafe coffee pricing against soft drinks, tea, juice, smoothies, and frozen drinks.',
							),
							array(
								'label'       => 'Open the breakfast pillar',
								'url'         => $this->get_seeded_page_url( 'breakfast-menu' ),
								'description' => 'See how coffee fits into the wider breakfast order and meal-planning context.',
							),
							array(
								'label'       => 'Read the nutrition and allergens guide',
								'url'         => $this->get_seeded_page_url( 'mcdonalds-nutrition-calories-allergens' ),
								'description' => 'Use the nutrition pillar when the main question is calories, sugar, or ingredient checks.',
							),
						),
					)
				),
			),
			'beverage-menu' => array(
				'title'   => "McDonald's Drinks Menu Prices USA",
				'content' => $this->build_seeded_category_pillar_page_content(
					array(
						'category'        => 'beverages',
						'page_label'      => "McDonald's Drinks Menu Prices USA",
						'focus_label'     => "McDonald's drinks menu",
						'include_items'   => array( 'soft drinks', 'sweet tea', 'unsweetened tea', 'lemonade', 'juice', 'smoothies', 'frozen drinks', 'water' ),
						'value_points'    => array(
							'Drink size is one of the most common ways an order total changes, especially inside meals and combo upgrades.',
							'Readers often compare the beverage page against McCafe because they want the cheapest drink that still fits the meal they are building.',
							'Frozen drinks, smoothies, and bottled options matter because they do not sit in the same price band as standard fountain drinks.',
						),
						'nutrition_points' => array(
							'Sugar and calories vary sharply across soft drinks, lemonade, smoothies, and sweet tea, so this page helps readers compare beyond the name alone.',
							'Many low-friction ordering choices happen here: water versus soda, tea versus lemonade, or a small fountain drink versus a more expensive premium beverage.',
							'The drinks page is often the last comparison point before checkout because beverage upgrades can change both calories and total spend in a single tap.',
						),
						'availability_paragraphs' => array(
							'The beverage pillar works best as a decision page for readers who already know the food item but have not finalized the drink. That is why the content here focuses on size tiers, category differences, and practical order-building rather than a thin list of drink names.',
							'Use this page to compare general beverage pricing, then move to the McCafe pillar if the real comparison is coffee and espresso, or move back to the burger, chicken, or breakfast pillar if the drink is only one part of the wider order.',
						),
						'featured_items'  => array( 'Soft Drink Small', 'Sweet Tea Small', 'Lemonade Small', 'Minute Maid Premium Orange Juice Small', 'DASANI Water', 'Frozen Coca-Cola Classic Small' ),
						'faq_items'       => array(
							array(
								'question' => 'What is covered on the McDonald\'s drinks menu page?',
								'answer'   => 'The drinks pillar covers fountain drinks, tea, lemonade, juice, smoothies, frozen drinks, bottled water, and other non-coffee beverages that appear in the tracked USA menu data.',
							),
							array(
								'question' => 'Why should drinks be compared separately from McCafe?',
								'answer'   => 'Because the ordering intent is different. McCafe is mainly about coffee and espresso, while the drinks pillar answers the broader meal-building question around soda, tea, juice, smoothies, frozen drinks, and water.',
							),
							array(
								'question' => 'Do drink sizes change value significantly?',
								'answer'   => 'Yes. Drink upgrades are one of the easiest ways a combo total grows, so comparing small, medium, and large beverage pricing matters more than many readers expect.',
							),
							array(
								'question' => 'What page should I read after this drinks guide?',
								'answer'   => 'If you need coffee or frappe detail, open the McCafe pillar. If the drink is part of a meal comparison, move back to burgers, chicken, nuggets, breakfast, or the deals guide.',
							),
						),
						'related_links'   => array(
							array(
								'label'       => 'Browse the live beverages category page',
								'url'         => $this->get_menu_category_page_url( 'beverages' ),
								'description' => 'Open the managed beverages directory with current drink listings and item pages.',
							),
							array(
								'label'       => 'Open the McCafe pillar',
								'url'         => $this->get_seeded_page_url( 'mccafe-menu' ),
								'description' => 'Separate coffee and espresso comparisons from the broader drinks menu.',
							),
							array(
								'label'       => 'Read the deals and McValue guide',
								'url'         => $this->get_seeded_page_url( 'mcdonalds-deals-mcvalue-guide' ),
								'description' => 'See how drinks fit inside meal deals and value bundles.',
							),
							array(
								'label'       => 'Read the nutrition and allergens guide',
								'url'         => $this->get_seeded_page_url( 'mcdonalds-nutrition-calories-allergens' ),
								'description' => 'Use the broader nutrition pillar when calories and sugar are the main question.',
							),
						),
					)
				),
			),
			'sauces-condiments' => array(
				'title'   => "McDonald's Sauces & Condiments Prices USA",
				'content' => $this->build_seeded_category_pillar_page_content(
					array(
						'category'        => 'sauces',
						'page_label'      => "McDonald's Sauces & Condiments Prices USA",
						'focus_label'     => "McDonald's sauces and condiments menu",
						'include_items'   => array( 'barbecue sauce', 'ranch', 'honey mustard', 'sweet and sour', 'buffalo-style options', 'ketchup', 'mustard', 'mayo packets' ),
						'value_points'    => array(
							'Sauces matter because they are often treated as small extras, but paid dips and extra packets can still move the final order total.',
							'Nuggets, fries, strips, and wraps all pull this page into the wider meal comparison path, which makes sauces a practical add-on category rather than a trivial one.',
							'Readers often want to know which sauces are included and which extras may carry a small charge at their restaurant.',
						),
						'nutrition_points' => array(
							'Even when the price difference is small, sauces can still matter for calories, sugar, and ingredient questions.',
							'This page is especially useful for readers comparing nuggets, strips, and fries because the dip choice changes the order beyond the headline entrée price.',
							'Condiments also matter for customization because packets and add-ons can shift the taste profile of lower-cost items without changing the main item itself.',
						),
						'availability_paragraphs' => array(
							'Most menu guides underplay sauces, but they are one of the clearest examples of how a simple add-on can change both spending and satisfaction. A proper sauces pillar helps readers understand the add-on layer that sits under nuggets, fries, and shareable chicken orders.',
							'Use this page when you want to compare dipping options before you finalize a nuggets, strips, fries, or snack-wrap order. Then move into the live sauces category page or the linked meal pillars to understand the full order context.',
						),
						'featured_items'  => array( 'Tangy Barbecue Sauce', 'Spicy Buffalo Sauce', 'Creamy Ranch Sauce', 'Honey Mustard Sauce', "Sweet 'N Sour Sauce", 'Ketchup Packet' ),
						'faq_items'       => array(
							array(
								'question' => 'Why do sauces deserve their own McDonald\'s price guide?',
								'answer'   => 'Because sauces affect more than flavor. They can influence the final order total, calorie load, and the overall value of nuggets, fries, and strips orders. Readers often need that add-on context before they feel the order is fully planned.',
							),
							array(
								'question' => 'Are all McDonald\'s sauces free?',
								'answer'   => 'Not always. Some sauces or extra quantities may be included with certain items, while others may be paid extras depending on the restaurant and order type. That is why the sauces pillar is helpful in a price-focused menu site.',
							),
							array(
								'question' => 'What menu areas use the sauces page the most?',
								'answer'   => 'McNuggets, McCrispy Strips, fries, and some wrap or chicken orders are the most common drivers. Those categories are where extra dip choices are most likely to matter in real ordering behavior.',
							),
							array(
								'question' => 'What should I open next after this page?',
								'answer'   => 'If you are building a chicken order, move next to nuggets or chicken-and-fish. If you are balancing a meal total, open fries-and-sides or the deals guide to understand how the add-on layer fits the overall order.',
							),
						),
						'related_links'   => array(
							array(
								'label'       => 'Browse the live sauces category page',
								'url'         => $this->get_menu_category_page_url( 'sauces' ),
								'description' => 'Open the current sauces and condiments directory with tracked sauce listings.',
							),
							array(
								'label'       => 'Open the nuggets and strips pillar',
								'url'         => $this->get_seeded_page_url( 'nuggets-and-strips' ),
								'description' => 'Compare sauces against the chicken items that use them most often.',
							),
							array(
								'label'       => 'Open the fries and sides pillar',
								'url'         => $this->get_seeded_page_url( 'fries-sides' ),
								'description' => 'Use the sides pillar to see how dips fit with fries and other side orders.',
							),
							array(
								'label'       => 'Read the nutrition and allergens guide',
								'url'         => $this->get_seeded_page_url( 'mcdonalds-nutrition-calories-allergens' ),
								'description' => 'Move to the nutrition pillar when calories, sugar, or ingredient checks matter more than the add-on price alone.',
							),
						),
					)
				),
			),
			'mcdonalds-deals-mcvalue-guide' => array(
				'title'   => "McDonald's Deals & McValue Guide USA",
				'content' => $this->build_seeded_long_form_page_content(
					array(
						'intro'       => array(
							'This McDonald&#8217;s Deals &amp; McValue Guide USA page is the main value pillar for readers who care about the cheapest useful order, the best app-led savings, and the gap between menu price and real checkout total. A good deals page has to explain more than one headline discount because McDonald&#8217;s USA value now comes from several moving parts at once: meal deals, buy-one-add-one offers, app exclusives, rewards, dessert picks, and low-entry add-ons.',
							'That is why this pillar combines the live deals category with the live McValue category. Together they show how the current savings story actually works across burgers, chicken, breakfast, fries, desserts, and app behavior instead of pretending there is one simple universal &#8220;dollar menu&#8221; that answers every value question.',
							'Use this page when price is the main decision factor, when you are comparing a la carte ordering against a meal deal, or when you want to understand how app participation can make the same McDonald&#8217;s order feel either average or unusually good value.',
							'It is also one of the best pages for understanding the difference between cheap and efficient. The absolute lowest spend is not always the best practical value, and a slightly higher bundle can sometimes beat a scattered order once fries, drinks, and desserts are factored in. That broader value logic is what this pillar is built to explain.',
						),
						'snapshots'    => array(
							array(
								'heading' => 'Current meal deals snapshot',
								'items'   => $this->get_category_seed_snapshot_items(
									array( 'deals' ),
									6,
									array( 'McChicken Meal Deal', 'McDouble Meal Deal', 'Daily Double Meal Deal', 'Breakfast Buy 1 Add 1 for $1', 'Lunch Buy 1 Add 1 for $1' )
								),
							),
							array(
								'heading' => 'Current McValue snapshot',
								'items'   => $this->get_category_seed_snapshot_items(
									array( 'mcvalue' ),
									8,
									array( 'McValue Mini McFlurry Picks', 'McDouble', 'McChicken', '6 pc Chicken McNuggets', 'Small World Famous Fries', 'Hash Browns' )
								),
							),
						),
						'sections'    => array(
							array(
								'heading'    => 'How McDonald\'s value works in the USA right now',
								'paragraphs' => array(
									'The value story is no longer one flat cheap menu. McDonald&#8217;s USA uses different savings layers for different customers: low-entry add-ons, meal bundles, breakfast-specific offers, app-driven discounting, and rewards logic. A strong value guide has to show how those layers overlap so readers can tell whether a posted meal deal is better than building a lower-cost custom order from separate menu items.',
									'That matters because some readers want the cheapest possible full meal, while others want the best value within a certain appetite level. Those are not always the same thing. A bundle can look attractive, but a McValue path with one burger, one side, and one drink can sometimes fit the budget more cleanly depending on what the customer actually wants to eat.',
								),
								'list'       => array(
									'$5-style meal deals matter when you want a complete order with the fewest moving parts.',
									'Buy-one-add-one offers matter when you are flexible about which breakfast or lunch items you pair together.',
									'McValue dessert and snack picks matter when the goal is a smaller spend rather than a full meal.',
								),
							),
							array(
								'heading'    => 'Meal deal buyers versus low-entry value buyers',
								'paragraphs' => array(
									'There are usually two main ways readers use a McDonald&#8217;s value guide. The first group wants the cleanest complete meal at a fixed price point. The second group wants the lowest realistic spend while still leaving satisfied. Those users often compare McChicken, McDouble, nugget counts, fries, hash browns, and dessert picks rather than jumping straight to the meal-deal headline.',
									'That is why this pillar links value to the wider menu structure. A deal page becomes much more helpful when it also shows where burgers, chicken, fries, breakfast, and dessert pages connect to the savings story.',
								),
								'list'       => array(
									'Meal-deal shoppers should compare the full bundle, not just the main sandwich name.',
									'Low-entry shoppers should compare the cheapest satisfying order, not just the cheapest single item.',
									'App users should always consider whether rewards or local app offers beat the static menu price path.',
								),
							),
							array(
								'heading'    => 'App offers, rewards, and why the final total can still move',
								'paragraphs' => array(
									'Deals and McValue pages also need an EEAT-style note about real-world variability. The best local price can come from a standard counter order, from an app-only coupon, or from a rewards redemption. That means a value guide should be transparent about what it is showing: the tracked current menu data and public offer structure, not a guarantee that every restaurant will show the same live checkout.',
									'Local taxes, delivery fees, store participation, and the timing of rotating app offers can all change the final total. The pillar helps you understand the structure of the value system first, then the live app or restaurant confirms the last step.',
								),
							),
							array(
								'heading'    => 'Best next steps after the value pillar',
								'paragraphs' => array(
									'Once you know whether you are chasing a full meal deal, a McValue-style snack order, or the best category-specific bargain, the next click becomes obvious. Move to burgers if the real question is McDouble versus Big Mac value. Move to breakfast if you are planning a morning order. Move to fries, nuggets, desserts, or beverages if those add-ons are what truly change the order economics.',
									'That is the job of this pillar: not to replace the rest of the site, but to route value-focused readers into the most useful next comparison page without losing the broader savings context.',
									'In that sense, the deals pillar works like a decision hub. It translates broad budget intent into the right menu branch, helping readers decide whether they should chase a bundle, a low-entry custom build, or a category-specific offer path before the final order is placed.',
								),
							),
						),
						'faq_items'   => array(
							array(
								'question' => 'What is the difference between McValue and a meal deal?',
								'answer'   => 'McValue is the broader value platform, while meal deals are one part of that ecosystem. McValue can include low-entry add-ons, dessert picks, breakfast offers, and app-led savings, whereas a meal deal is usually a more fixed bundle with a clearer complete-order structure.',
							),
							array(
								'question' => 'Why is an app disclaimer important on a deals page?',
								'answer'   => 'Because the final best price often depends on app participation, store location, local promotions, and rewards availability. A reliable value guide should make that clear instead of pretending one posted number works identically everywhere.',
							),
							array(
								'question' => 'What is the smartest way to use this page?',
								'answer'   => 'Use the value pillar first to decide whether you want a full deal, a low-entry custom order, or an app-led strategy. Then move into the specific food category that matters most for the actual order you plan to place.',
							),
							array(
								'question' => 'Which readers benefit most from the deals and McValue guide?',
								'answer'   => 'Budget-focused solo diners, families, students, and app users benefit the most because they are usually the readers most sensitive to meal structure, add-ons, and rotating promotions.',
							),
						),
						'related_links' => array(
							array(
								'label'       => 'Open the live deals category page',
								'url'         => $this->get_menu_category_page_url( 'deals' ),
								'description' => 'See the current tracked meal deals and related price pages.',
							),
							array(
								'label'       => 'Open the live McValue category page',
								'url'         => $this->get_menu_category_page_url( 'mcvalue' ),
								'description' => 'Compare the lower-entry value layer separately from headline meal deals.',
							),
							array(
								'label'       => 'Read the McDonald\'s app deals guide',
								'url'         => $this->get_seeded_page_url( 'mcdonalds-app-deals' ),
								'description' => 'Use the focused app page when the value question is mainly about app behavior and rewards.',
							),
							array(
								'label'       => 'Read the breakfast pillar',
								'url'         => $this->get_seeded_page_url( 'breakfast-menu' ),
								'description' => 'Compare breakfast pricing against the current morning-value angle.',
							),
							array(
								'label'       => 'Read the burgers pillar',
								'url'         => $this->get_seeded_page_url( 'burgers-menu' ),
								'description' => 'Use the burgers page when the value comparison is really about choosing the right burger tier.',
							),
						),
						'shortcodes'   => array(
							'[mcprices_menu_category category="deals"]',
							'[mcprices_menu_category category="mcvalue"]',
						),
					)
				),
			),
			'mcdonalds-nutrition-calories-allergens' => array(
				'title'   => "McDonald's Nutrition, Calories & Allergens Guide",
				'content' => $this->build_seeded_long_form_page_content(
					array(
						'intro'       => array(
							'This McDonald&#8217;s Nutrition, Calories &amp; Allergens Guide is the main information pillar for readers who care about more than the posted dollar price. On a real food-search journey, people often want to know whether a burger is filling but not excessive, whether a breakfast meal is worth the calories, how fries change the total, and which menu areas deserve a second allergen check before ordering.',
							'That is why the nutrition pillar sits alongside the price pillars rather than outside them. A strong McDonald&#8217;s USA menu site should help readers compare price, calories, ingredients, and allergens together, because that is how menu choices are made in real life.',
							'This guide is also where transparency matters most. Menu data helps with planning, but allergen, ingredient, and final nutrition decisions should always be confirmed in the official McDonald&#8217;s app, on the official website, or with the restaurant when the order is actually being placed.',
						),
						'snapshots'    => array(
							array(
								'heading' => 'Sample calories and price snapshot across major menu areas',
								'items'   => $this->get_category_seed_snapshot_items(
									array( 'burgers', 'breakfast', 'nuggets', 'sides', 'sweets', 'beverages' ),
									8,
									array( 'Big Mac', 'Egg McMuffin', '10 pc Chicken McNuggets', 'World Famous Fries Large', 'OREO McFlurry', 'Soft Drink Small', 'McChicken', 'Hash Browns' )
								),
							),
						),
						'sections'    => array(
							array(
								'heading'    => 'How to use calories on a menu-price site',
								'paragraphs' => array(
									'Calories are most useful when they help answer a concrete ordering question. Readers usually want to know which entrée is heavier, how much a meal upgrade changes the total, or whether a side or dessert pushes the order past a personal limit. That is why the calorie context on this site is designed to support ordering decisions, not to replace the official nutrition database.',
									'In practice, calorie comparisons usually happen inside one of four paths: burger versus burger, breakfast versus breakfast, side-size comparison, or dessert comparison. Grouping calories inside those real buying paths is more helpful than dropping one isolated number without menu context.',
								),
								'list'       => array(
									'Use category pillars when you want the bigger nutritional pattern within burgers, breakfast, drinks, or desserts.',
									'Use item pages when you already know the exact product and want the current tracked price plus a quick calorie reference.',
									'Use the official McDonald\'s tools when the decision depends on a strict ingredient or allergen requirement.',
								),
							),
							array(
								'heading'    => 'Where calories climb fastest on the McDonald\'s menu',
								'paragraphs' => array(
									'The biggest calorie jumps often come from stacking categories together rather than from the main sandwich alone. A double-beef burger, large fries, a sweetened drink, and a dessert can move far faster than readers expect when they only look at the headline burger price first.',
									'Breakfast can surprise people in the same way. Larger breakfast sandwiches, bagels, platters, and added sides can create totals that feel very different from a simpler McMuffin-and-coffee order. The nutrition pillar helps readers spot those patterns before the final checkout.',
								),
								'list'       => array(
									'Large fries and upsized drinks often change total calories more than a small sandwich switch.',
									'Premium desserts and sweet coffee drinks can turn a moderate meal into a much heavier total.',
									'Breakfast sides and combo builds matter because a simple morning order can become much denser very quickly.',
								),
							),
							array(
								'heading'    => 'Allergens, ingredients, and what must be verified officially',
								'paragraphs' => array(
									'Allergen and ingredient information should be treated differently from price information. Prices and calorie references are useful planning data, but allergen and ingredient decisions can involve far more risk if a restaurant changes supply, preparation, or customization details. That is why this page keeps a strong verification note at the center of the guidance.',
									'Use this pillar to understand which parts of the menu deserve a second look, then use the official allergen tools and the restaurant itself when you need final certainty. That approach is both more accurate and more trustworthy than pretending one unofficial summary can replace the official source in a high-stakes situation.',
								),
							),
							array(
								'heading'    => 'Building a lighter or better-balanced McDonald\'s order',
								'paragraphs' => array(
									'A nutrition guide should not only warn readers away from heavy combinations. It should also help them see the better-balanced order paths inside the real menu. Smaller burgers, simpler breakfast sandwiches, water or tea instead of a sweet drink, and lighter dessert choices are all practical examples of how readers use this information.',
									'The goal is not to moralize food choices. The goal is to help readers compare clearly, spend intentionally, and understand what changes the order the most before they tap checkout.',
								),
							),
							array(
								'heading'    => 'How readers use nutrition data in real McDonald\'s menu decisions',
								'paragraphs' => array(
									'Most nutrition-focused readers are not looking for abstract numbers. They are trying to solve a live ordering problem. That may mean finding the burger that feels most filling for a reasonable calorie total, choosing a breakfast option that is satisfying without becoming too heavy, or spotting whether fries and dessert are what really push the total over the edge. Framing nutrition around those real menu moments makes the guide far more useful than a disconnected list of calorie figures.',
									'This is also why category context matters so much. A calorie number becomes more helpful when it sits next to an item description, a price reference, and a likely order pattern. Readers do not usually eat menu items as isolated data points; they eat them as part of meals, bundles, and add-on combinations. The guide is built to reflect that reality instead of flattening the menu into one undifferentiated nutrition table.',
								),
								'list'       => array(
									'Use burgers, breakfast, and chicken pages when you need the main-item calorie pattern inside one category.',
									'Use fries, beverages, and desserts pages when the real calorie jump comes from the add-on side of the order.',
									'Use official McDonald\'s ingredient and allergen tools whenever the decision is medically or personally sensitive.',
								),
							),
							array(
								'heading'    => 'Where a nutrition guide adds the most value for SEO and EEAT',
								'paragraphs' => array(
									'Nutrition content earns trust when it explains the difference between broad planning and final verification. This page is useful for identifying heavy menu patterns, lighter alternatives, and the categories where calories tend to rise fastest. But it also makes clear that official McDonald\'s sources remain the final checkpoint for ingredients, preparation changes, and live allergen information. That transparent boundary is part of what makes the guide more credible.',
									'From a search perspective, the nutrition pillar also strengthens topical authority across the site because it connects burgers, breakfast, nuggets, fries, beverages, and desserts into one shared comparison framework. Search engines and AI systems can use that wider contextual coverage to understand that the site is not only a price list, but a broader decision-support resource for McDonald\'s USA menu research.',
								),
							),
							array(
								'heading'    => 'How to compare calories without losing sight of value',
								'paragraphs' => array(
									'One reason nutrition pages perform well is that readers rarely want calorie information in isolation. They are usually trying to balance cost, fullness, convenience, and category preference at the same time. A burger that looks cheap may not feel like the best value if a slightly different burger keeps the reader satisfied longer. A breakfast order may look light until Hash Browns and a sweet drink are added. The best nutrition guide helps readers keep those trade-offs visible instead of splitting price and calorie thinking into two separate research tasks.',
									'That broader comparison approach is also what makes this page more helpful than a static nutrition chart. It speaks to real menu decisions: which meal combination feels heavier than expected, which category usually hides the biggest calorie jump, and which add-ons matter most when the goal is a more balanced total. For both search visibility and user experience, those layered explanations are more useful than isolated numbers alone.',
								),
								'list'       => array(
									'Compare the main item first, then check whether fries, drinks, and desserts are what really change the total.',
									'Look at category patterns rather than one calorie number when you are deciding between breakfast, burgers, nuggets, and sweets.',
									'Use item pages for quick price-plus-calorie checks and the official tools for final allergen or ingredient verification.',
								),
							),
							array(
								'heading'    => 'Why a menu guide should separate planning from verification',
								'paragraphs' => array(
									'Readers trust nutrition content more when the page is honest about what it can and cannot do. This site can help with category-level comparison, tracked calorie context, and clearer ordering decisions. But it should never blur the line between planning support and final medical or dietary verification. That is why the guide repeatedly points readers back to official McDonald\'s resources when allergens, ingredients, or location-specific preparation details are the deciding factor.',
									'That separation is not a weakness. It is part of what makes the page more authoritative. Search engines, AI systems, and human readers all respond better to content that explains its scope clearly, supports the decision responsibly, and avoids pretending that one unofficial page can replace the official source in every situation.',
								),
							),
							array(
								'heading'    => 'How this nutrition pillar supports the rest of the site',
								'paragraphs' => array(
									'The nutrition guide is also a connective page. It helps readers move from broad calorie and allergen questions into the exact category page or item page where the decision becomes concrete. That makes it valuable not only as a standalone guide, but also as a supporting authority page for burgers, breakfast, nuggets, fries, desserts, drinks, and family-order planning.',
									'In topical SEO terms, this page gives the site a stronger explanatory layer. Instead of only saying what an item costs, the site can now explain how heavier and lighter order patterns work across the menu, where calorie surprises usually appear, and why official allergen checks remain essential before the final purchase.',
									'That wider support role is important because readers often discover their real question only after they begin comparing. A burger query can become a fries query, a breakfast query can become an allergen question, and a dessert query can become a total-calorie question. The nutrition pillar helps connect those paths so the whole site behaves more like a decision system than a disconnected set of price pages.',
									'That is exactly why this page matters for topical authority: it helps unify the rest of the menu coverage into one clearer nutrition-aware decision framework.',
									'For readers, that means fewer isolated fact checks and a much clearer path from menu curiosity to a more informed final order.',
									'That clarity is valuable on every major menu branch.',
								),
							),
						),
						'faq_items'   => array(
							array(
								'question' => 'Should I rely on this page for official allergen decisions?',
								'answer'   => 'No. Use this guide for planning and comparison, then confirm any allergen-sensitive or ingredient-sensitive decision through the official McDonald\'s app, website, or restaurant. That is the safest and most accurate workflow.',
							),
							array(
								'question' => 'Why are calories useful on a price site?',
								'answer'   => 'Because readers rarely make menu decisions based on price alone. They usually compare value, calories, fullness, and add-on choices at the same time, especially for burgers, breakfast, fries, desserts, and drinks.',
							),
							array(
								'question' => 'What menu areas should I compare first if I want a lighter order?',
								'answer'   => 'Start with breakfast, burgers, fries and sides, beverages, and desserts. Those areas usually drive the clearest calorie differences in a typical McDonald\'s order.',
							),
							array(
								'question' => 'What is the best next step after reading this guide?',
								'answer'   => 'Move into the relevant category pillar or item page once you know which section of the menu matters most. Use the broader nutrition guide to frame the comparison, then use the focused page to make the actual order decision.',
							),
						),
						'related_links' => array(
							array(
								'label'       => 'Open the calorie counter page',
								'url'         => $this->get_seeded_page_url( 'calorie-counter' ),
								'description' => 'Use the focused calorie page when you want a simpler comparison entry point.',
							),
							array(
								'label'       => 'Open the allergen guide',
								'url'         => $this->get_seeded_page_url( 'allergen-guide' ),
								'description' => 'Read the separate allergen page for a more direct verification reminder and context.',
							),
							array(
								'label'       => 'Read the burgers pillar',
								'url'         => $this->get_seeded_page_url( 'burgers-menu' ),
								'description' => 'Burger comparisons are one of the most common calorie-plus-price use cases.',
							),
							array(
								'label'       => 'Read the breakfast pillar',
								'url'         => $this->get_seeded_page_url( 'breakfast-menu' ),
								'description' => 'Breakfast is one of the easiest menu areas to underestimate on calories.',
							),
							array(
								'label'       => 'Read the desserts pillar',
								'url'         => $this->get_seeded_page_url( 'sweets-treats' ),
								'description' => 'Desserts and shakes often decide whether the total order stays moderate or becomes heavy.',
							),
						),
					)
				),
			),
			'mcdonalds-prices-by-state' => array(
				'title'   => "McDonald's Prices by State",
				'content' => $this->build_seeded_long_form_page_content(
					array(
						'intro'       => array(
							'This McDonald&#8217;s Prices by State page is the regional pillar for readers who know that menu pricing is not identical across the United States. A Big Mac, breakfast meal, fries upgrade, or McValue offer can feel very different depending on labor costs, rent, franchise strategy, taxes, and whether you are ordering inside a high-cost city or a lower-cost market.',
							'That is why a serious price guide cannot pretend one national number explains everything. National menu pricing is useful for planning, but local price variation is one of the biggest reasons people search for fast-food prices with state names, city names, and near-me intent attached.',
							'Use this page as the regional framework for understanding why prices move, how to compare one state against another, and which menu areas tend to show the clearest local variation. Then use the main category and item pages to compare the specific food choices that matter most to your order.',
						),
						'snapshots'    => array(
							array(
								'heading' => 'Popular items readers compare when checking local price variation',
								'items'   => $this->get_category_seed_snapshot_items(
									array( 'burgers', 'breakfast', 'nuggets', 'sides', 'beverages' ),
									8,
									array( 'Big Mac', 'McDouble', 'Egg McMuffin', '10 pc Chicken McNuggets', 'World Famous Fries Large', 'Soft Drink Small', 'Hash Browns' )
								),
							),
						),
						'sections'    => array(
							array(
								'heading'    => 'Why McDonald\'s prices change by state and city',
								'paragraphs' => array(
									'Regional pricing is shaped by several layers at once: labor and wage structure, real-estate costs, supply-chain differences, local competition, delivery economics, franchise strategy, and tax environments. That means two restaurants under the same brand can still feel materially different at checkout even when the core menu looks familiar.',
									'City-level variation can sometimes matter as much as state-level variation, especially in dense metro areas and travel-heavy markets. That is why the best regional guide explains the pricing logic first instead of pretending every local change can be summarized with one neat national table.',
								),
								'list'       => array(
									'High-cost urban markets often show the clearest price pressure on combo meals and premium sandwiches.',
									'Lower-cost markets can still differ if app participation, local promotions, or franchise strategies are different.',
									'Delivery prices and fees may create a bigger practical gap than the board price alone.',
								),
							),
							array(
								'heading'    => 'Which menu areas usually show the clearest regional differences',
								'paragraphs' => array(
									'Signature burgers, breakfast combos, nugget meals, fries upgrades, and drinks are some of the most useful comparison points because they are widely recognized and ordered often. They make it easier for readers to judge whether one market feels only slightly higher or meaningfully more expensive overall.',
									'Value and deal pricing also matters because a restaurant may participate differently in offers, app bundles, or local savings structures. A state-aware price guide therefore has to connect regional variation with the deals pillar rather than treat them as separate worlds.',
								),
							),
							array(
								'heading'    => 'Regional menu entities worth tracking',
								'paragraphs' => array(
									'For SEO and topical completeness, a strong McDonald\'s USA site should speak the language of states and major cities directly. That does not mean inventing false precision. It means recognizing the places readers naturally search when they want local price context.',
									'The most useful way to structure that coverage is by region first, then by state, and then by major metro comparisons where relevant.',
								),
								'list'       => array(
									'<strong>Northeast:</strong> Connecticut, Maine, Massachusetts, New Hampshire, Rhode Island, Vermont, New Jersey, New York, Pennsylvania, Delaware, Maryland, and the District of Columbia.',
									'<strong>South:</strong> Alabama, Arkansas, Florida, Georgia, Kentucky, Louisiana, Mississippi, North Carolina, South Carolina, Tennessee, Virginia, West Virginia, Texas, Oklahoma, and surrounding Southern markets.',
									'<strong>Midwest:</strong> Illinois, Indiana, Iowa, Kansas, Michigan, Minnesota, Missouri, Nebraska, North Dakota, Ohio, South Dakota, Wisconsin, and nearby Plains/Midwest markets.',
									'<strong>West:</strong> Alaska, Arizona, California, Colorado, Hawaii, Idaho, Montana, Nevada, New Mexico, Oregon, Utah, Washington, Wyoming, and neighboring Western markets.',
								),
							),
							array(
								'heading'    => 'How to use this regional pillar with the rest of the site',
								'paragraphs' => array(
									'Start here when your real question is local price movement rather than one exact product. Once you know that regional variation is the core issue, the next step is to move into the category or item page that represents the order you are pricing. For one reader that is Big Mac. For another it is breakfast, McValue, or the cheapest family-friendly combination.',
									'That workflow is more honest and more useful than pretending the regional page can replace the menu pillars. The regional pillar gives you the local lens. The category and item pillars give you the actual food comparison.',
								),
							),
							array(
								'heading'    => 'How state-by-state pricing helps readers plan more realistically',
								'paragraphs' => array(
									'Readers often search with a state modifier because they have already noticed that one McDonald\'s order does not feel the same everywhere. Travelers, people moving between markets, and app users comparing nearby stores all run into this problem. A regional pillar helps those users understand that the difference is structural, not random: it comes from market conditions, operating costs, franchise strategy, and the way local promotions are implemented.',
									'That makes the page valuable even before every regional child page is opened. It gives readers the mental model they need to interpret why prices vary and which menu categories are most worth checking first in their own market. In other words, it turns scattered local observations into a clear regional comparison framework.',
								),
								'list'       => array(
									'Use burgers and breakfast as the cleanest first comparison points when you want to feel the price difference between markets quickly.',
									'Use deals and McValue pages when your local market question is really about app participation or bundle economics.',
									'Use city and state pages as localized support, then return to the category and item pillars for the actual product comparison.',
								),
							),
							array(
								'heading'    => 'Why regional pages strengthen topical authority',
								'paragraphs' => array(
									'A national menu site becomes stronger when it can explain both the broad menu structure and the local variation layered on top of it. That is one of the biggest gaps many generic menu sites leave open. They may list products well enough, but they often do not explain how prices behave across different U.S. markets or why readers keep searching with city and state modifiers attached.',
									'Regional coverage closes that gap by giving search engines and users a clearer map of the whole subject. The prices-by-state pillar supports local intent, reinforces the credibility of the national menu pages, and creates natural internal links into burgers, breakfast, deals, fries, nuggets, and beverage coverage whenever readers want to compare one specific order inside their own market.',
								),
							),
							array(
								'heading'    => 'What to compare first when checking a new state or city',
								'paragraphs' => array(
									'When readers are new to a market, they do not need every product at once. They need a few dependable comparison anchors. That is why well-known burgers, breakfast sandwiches, nugget counts, fries, and common drinks are so useful. Those items make it easier to feel whether the local menu is only slightly different or meaningfully more expensive than another state or city.',
									'Once that first comparison is clear, the rest of the menu becomes easier to interpret. The reader can then move from the regional pillar into the exact category or item page that best matches the order they care about most. This step-by-step method is more useful than dropping readers into dozens of local pages without a framework for comparison.',
								),
								'list'       => array(
									'Start with a flagship burger, a breakfast staple, a nugget count, fries, and a standard drink.',
									'Check deal participation separately because app-led offers can reshape the local value story.',
									'Use category pillars after the first comparison anchor is clear so the regional question turns into a real ordering decision.',
								),
							),
							array(
								'heading'    => 'How delivery apps, taxes, and store participation complicate local prices',
								'paragraphs' => array(
									'State-by-state pricing is only part of the picture because many readers now order through multiple channels. In-store, app pickup, delivery, and third-party marketplaces can all make the same menu item feel like a different purchase. Taxes, fees, and app participation may create a larger practical gap than the base board price alone, especially in busy metro areas.',
									'That is why this pillar helps readers think in layers. First, understand the regional market. Second, understand the category or item you actually want. Third, remember that ordering channel can still alter the final number. That layered framework is more realistic for modern McDonald\'s ordering than any one-price-fits-all assumption.',
								),
							),
							array(
								'heading'    => 'How this regional guide prepares readers for deeper state coverage',
								'paragraphs' => array(
									'The prices-by-state pillar also works as the foundation for future state and city pages. Before readers drill down into one location-specific page, they need to understand the wider regional logic that makes those local differences meaningful. This guide gives them that context first, which makes the localized pages easier to interpret later.',
									'That structure is useful for SEO as well because it mirrors how the topic naturally expands: national menu understanding first, then regional variation, then the exact state or city layer, and finally the category or item page that answers the product-level question. The pillar is what ties those layers together into one coherent topical map.',
									'It also improves usability for readers who are comparing more than one location. Instead of bouncing between isolated city pages without context, they can start here, understand the broad pricing pattern, and then move into the local pages or category pages that answer their exact market question more efficiently.',
									'That first-step clarity matters because state pricing research is usually part of a bigger journey, not the final stop on its own.',
									'In other words, the regional pillar turns scattered local price checks into a more navigable and trustworthy comparison process.',
								),
							),
							array(
								'heading'    => 'Why readers search regional pricing before choosing an exact menu item',
								'paragraphs' => array(
									'Many readers use a state or city price page before they know which exact product they will order. They want to understand whether the local market feels generally expensive, whether value deals still look competitive, and whether a familiar order will likely cost more than expected. Once they have that regional sense, they can move into burgers, breakfast, nuggets, fries, drinks, or deals with much better context.',
									'That ordering journey is why the prices-by-state pillar deserves depth of its own. It does not just support local SEO; it also supports better menu decisions by helping readers interpret the national menu through a real local-price lens before they commit to one exact item comparison.',
								),
							),
						),
						'faq_items'   => array(
							array(
								'question' => 'Do McDonald\'s prices really change by state?',
								'answer'   => 'Yes. They can vary because of labor costs, rent, franchise strategy, taxes, local promotions, and ordering channel differences. In some situations the city and delivery platform matter almost as much as the state itself.',
							),
							array(
								'question' => 'What is the best menu item to compare across states?',
								'answer'   => 'A well-known burger such as Big Mac, a clear breakfast item such as Egg McMuffin, a common nugget count, fries, and a standard drink are usually the easiest starting points for regional comparisons because they are widely recognized and widely ordered.',
							),
							array(
								'question' => 'Should I treat national menu prices as exact local totals?',
								'answer'   => 'No. National menu guides are useful for planning and comparison, but the official app or the local restaurant should always confirm the final total when state or city pricing is the main concern.',
							),
							array(
								'question' => 'What page should I read after the prices-by-state guide?',
								'answer'   => 'Open the category or item page that matches the order you are trying to price: burgers, breakfast, nuggets, fries, drinks, or the deals guide. The regional page tells you why the local price may move; the next page tells you what item to compare.',
							),
						),
						'related_links' => array(
							array(
								'label'       => 'Open the full menu directory',
								'url'         => $this->get_menu_directory_root_url(),
								'description' => 'Move from local pricing context back into the full menu structure.',
							),
							array(
								'label'       => 'Read the burgers pillar',
								'url'         => $this->get_seeded_page_url( 'burgers-menu' ),
								'description' => 'Use burger pricing as a common regional comparison point.',
							),
							array(
								'label'       => 'Read the breakfast pillar',
								'url'         => $this->get_seeded_page_url( 'breakfast-menu' ),
								'description' => 'Use breakfast pricing when your local market question starts in the morning menu.',
							),
							array(
								'label'       => 'Read the deals and McValue guide',
								'url'         => $this->get_seeded_page_url( 'mcdonalds-deals-mcvalue-guide' ),
								'description' => 'See how local market variation interacts with value and app-led savings.',
							),
						),
					)
				),
			),
		);
	}

	/**
	 * Return a seeded site URL for a managed page slug.
	 *
	 * @param string $slug Page slug.
	 * @return string
	 */
	protected function get_seeded_page_url( $slug ) {
		return home_url( '/' . ltrim( trim( (string) $slug ), '/' ) . '/' );
	}

	/**
	 * Return the primary long-form guide mapped to each menu category.
	 *
	 * @return array<string, array<string, string>>
	 */
	protected function get_menu_category_primary_guide_map() {
		return array(
			'whats-new'   => array(
				'slug'  => 'limited-time-menu',
				'title' => 'Limited-Time Menu',
			),
			'meals'       => array(
				'slug'  => 'extra-value-meals',
				'title' => 'Extra Value Meals',
			),
			'mcvalue'     => array(
				'slug'  => 'mcdonalds-deals-mcvalue-guide',
				'title' => "McDonald's Deals & McValue Guide USA",
			),
			'deals'       => array(
				'slug'  => 'mcdonalds-deals-mcvalue-guide',
				'title' => "McDonald's Deals & McValue Guide USA",
			),
			'breakfast'   => array(
				'slug'  => 'breakfast-menu',
				'title' => "McDonald's Breakfast Menu Prices USA",
			),
			'burgers'     => array(
				'slug'  => 'burgers-menu',
				'title' => "McDonald's Burgers Menu Prices USA",
			),
			'chickenfish' => array(
				'slug'  => 'chicken-fish-menu',
				'title' => "McDonald's Chicken & Fish Menu Prices USA",
			),
			'nuggets'     => array(
				'slug'  => 'nuggets-and-strips',
				'title' => "McDonald's McNuggets & Strips Prices USA",
			),
			'snackwrap'   => array(
				'slug'  => 'snack-wrap',
				'title' => 'Snack Wrap',
			),
			'sides'       => array(
				'slug'  => 'fries-sides',
				'title' => "McDonald's Fries & Sides Prices USA",
			),
			'happymeal'   => array(
				'slug'  => 'happy-meal-menu',
				'title' => "McDonald's Happy Meal Prices USA",
			),
			'sweets'      => array(
				'slug'  => 'sweets-treats',
				'title' => "McDonald's Desserts Menu Prices USA",
			),
			'mccafe'      => array(
				'slug'  => 'mccafe-menu',
				'title' => "McDonald's McCafe Menu Prices USA",
			),
			'beverages'   => array(
				'slug'  => 'beverage-menu',
				'title' => "McDonald's Drinks Menu Prices USA",
			),
			'sauces'      => array(
				'slug'  => 'sauces-condiments',
				'title' => "McDonald's Sauces & Condiments Prices USA",
			),
			'sharers'     => array(
				'slug'  => 'shareables-bundles',
				'title' => 'Shareables & Bundles',
			),
		);
	}

	/**
	 * Return the primary guide URL for a menu category.
	 *
	 * @param string $category_id Category identifier.
	 * @return string
	 */
	protected function get_menu_category_primary_guide_url( $category_id ) {
		$map = $this->get_menu_category_primary_guide_map();

		if ( empty( $map[ $category_id ]['slug'] ) ) {
			return '';
		}

		return $this->get_seeded_page_url( $map[ $category_id ]['slug'] );
	}

	/**
	 * Return the primary guide label for a menu category.
	 *
	 * @param string $category_id Category identifier.
	 * @return string
	 */
	protected function get_menu_category_primary_guide_title( $category_id ) {
		$map = $this->get_menu_category_primary_guide_map();

		return isset( $map[ $category_id ]['title'] ) ? (string) $map[ $category_id ]['title'] : '';
	}

	/**
	 * Wrap a heading in Gutenberg comment markup.
	 *
	 * @param string $text  Heading text.
	 * @param int    $level Heading level.
	 * @return string
	 */
	protected function build_seed_block_heading( $text, $level = 2 ) {
		$level = max( 2, min( 4, (int) $level ) );

		return sprintf(
			'<!-- wp:heading {"level":%1$d} --><h%1$d>%2$s</h%1$d><!-- /wp:heading -->',
			$level,
			esc_html( (string) $text )
		);
	}

	/**
	 * Wrap trusted HTML in a paragraph block.
	 *
	 * @param string $html Paragraph HTML.
	 * @return string
	 */
	protected function build_seed_block_paragraph( $html ) {
		return '<!-- wp:paragraph --><p>' . wp_kses_post( (string) $html ) . '</p><!-- /wp:paragraph -->';
	}

	/**
	 * Wrap list items in a Gutenberg list block.
	 *
	 * @param string[] $items List item HTML strings.
	 * @return string
	 */
	protected function build_seed_block_list( array $items ) {
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
	 * Wrap trusted HTML in a core/html block.
	 *
	 * @param string $html HTML fragment.
	 * @return string
	 */
	protected function build_seed_block_html( $html ) {
		return '<!-- wp:html -->' . (string) $html . '<!-- /wp:html -->';
	}

	/**
	 * Wrap a shortcode in a Gutenberg shortcode block.
	 *
	 * @param string $shortcode Shortcode text.
	 * @return string
	 */
	protected function build_seed_block_shortcode( $shortcode ) {
		return '<!-- wp:shortcode -->' . trim( (string) $shortcode ) . '<!-- /wp:shortcode -->';
	}

	/**
	 * Return a separator block.
	 *
	 * @return string
	 */
	protected function build_seed_block_separator() {
		return '<!-- wp:separator --><hr class="wp-block-separator has-alpha-channel-opacity"/><!-- /wp:separator -->';
	}

	/**
	 * Build a human-readable list from an array.
	 *
	 * @param string[] $items Items to join.
	 * @return string
	 */
	protected function build_seed_human_list( array $items ) {
		$items = array_values(
			array_filter(
				array_map(
					static function ( $item ) {
						return trim( (string) $item );
					},
					$items
				)
			)
		);

		$count = count( $items );

		if ( 0 === $count ) {
			return '';
		}

		if ( 1 === $count ) {
			return $items[0];
		}

		if ( 2 === $count ) {
			return $items[0] . ' and ' . $items[1];
		}

		$last = array_pop( $items );

		return implode( ', ', $items ) . ', and ' . $last;
	}

	/**
	 * Return selected category items, prioritizing requested names first.
	 *
	 * @param string[] $category_ids    Category IDs.
	 * @param int      $limit           Maximum number of items.
	 * @param string[] $preferred_names Preferred item names.
	 * @return array<int, array<string, mixed>>
	 */
	protected function get_category_seed_snapshot_items( array $category_ids, $limit = 8, array $preferred_names = array() ) {
		$limit    = max( 1, (int) $limit );
		$selected = array();
		$seen     = array();

		foreach ( $preferred_names as $preferred_name ) {
			foreach ( $category_ids as $category_id ) {
				$item = $this->get_menu_directory_item_data( $category_id, $preferred_name );

				if ( ! is_array( $item ) || empty( $item['name'] ) ) {
					continue;
				}

				$key = $this->normalize_media_key( (string) $item['name'] );

				if ( isset( $seen[ $key ] ) ) {
					continue;
				}

				$selected[]   = $item;
				$seen[ $key ] = true;
				break;
			}

			if ( count( $selected ) >= $limit ) {
				return array_slice( $selected, 0, $limit );
			}
		}

		foreach ( $category_ids as $category_id ) {
			$category = $this->get_menu_directory_category_data( $category_id );

			if ( ! is_array( $category ) || empty( $category['items'] ) || ! is_array( $category['items'] ) ) {
				continue;
			}

			foreach ( $category['items'] as $item ) {
				if ( empty( $item['name'] ) ) {
					continue;
				}

				$key = $this->normalize_media_key( (string) $item['name'] );

				if ( isset( $seen[ $key ] ) ) {
					continue;
				}

				$selected[]   = $item;
				$seen[ $key ] = true;

				if ( count( $selected ) >= $limit ) {
					return array_slice( $selected, 0, $limit );
				}
			}
		}

		return array_slice( $selected, 0, $limit );
	}

	/**
	 * Build a simple snapshot table block for item comparisons.
	 *
	 * @param string $heading Table heading.
	 * @param array  $items   Item data.
	 * @return string
	 */
	protected function build_seeded_item_snapshot_table( $heading, array $items ) {
		if ( empty( $items ) ) {
			return '';
		}

		$rows = '';

		foreach ( $items as $item ) {
			$name     = isset( $item['name'] ) ? (string) $item['name'] : '';
			$price    = isset( $item['price'] ) && '' !== trim( (string) $item['price'] ) ? (string) $item['price'] : 'Varies';
			$calories = isset( $item['calories'] ) && '' !== trim( (string) $item['calories'] ) ? (string) $item['calories'] : 'Calories vary';
			$summary  = isset( $item['summary'] ) ? (string) $item['summary'] : '';

			if ( '' === $name ) {
				continue;
			}

			$rows .= sprintf(
				'<tr><td>%1$s</td><td>%2$s</td><td>%3$s</td><td>%4$s</td></tr>',
				esc_html( $name ),
				esc_html( $price ),
				esc_html( $calories ),
				esc_html( $summary )
			);
		}

		if ( '' === $rows ) {
			return '';
		}

		$table_html  = '<div class="mcprices-seed-table"><table><thead><tr><th>Item</th><th>Price</th><th>Calories</th><th>Quick take</th></tr></thead><tbody>';
		$table_html .= $rows;
		$table_html .= '</tbody></table></div>';

		return $this->build_seed_block_heading( $heading, 2 ) . $this->build_seed_block_html( $table_html );
	}

	/**
	 * Build FAQ-style blocks from an item list.
	 *
	 * @param array<int, array<string, string>> $faq_items FAQ items.
	 * @return string
	 */
	protected function build_seeded_faq_blocks( array $faq_items ) {
		if ( empty( $faq_items ) ) {
			return '';
		}

		$content = $this->build_seed_block_heading( 'Common questions readers ask before ordering', 2 );

		foreach ( $faq_items as $faq_item ) {
			$question = trim( (string) ( $faq_item['question'] ?? '' ) );
			$answer   = trim( (string) ( $faq_item['answer'] ?? '' ) );

			if ( '' === $question || '' === $answer ) {
				continue;
			}

			$content .= $this->build_seed_block_heading( $question, 3 );
			$content .= $this->build_seed_block_paragraph( $answer );
		}

		return $content;
	}

	/**
	 * Build a related-links block for internal linking.
	 *
	 * @param array<int, array<string, string>> $links Related link data.
	 * @return string
	 */
	protected function build_seeded_related_links_block( array $links ) {
		$list_items = array();

		foreach ( $links as $link ) {
			$label       = trim( (string) ( $link['label'] ?? '' ) );
			$url         = trim( (string) ( $link['url'] ?? '' ) );
			$description = trim( (string) ( $link['description'] ?? '' ) );

			if ( '' === $label || '' === $url ) {
				continue;
			}

			$item_html = '<a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a>';

			if ( '' !== $description ) {
				$item_html .= ' &mdash; ' . esc_html( $description );
			}

			$list_items[] = $item_html;
		}

		if ( empty( $list_items ) ) {
			return '';
		}

		return $this->build_seed_block_heading( 'Related guides and live menu pages', 2 ) . $this->build_seed_block_list( $list_items );
	}

	/**
	 * Build a shorter support page with richer internal linking.
	 *
	 * @param array<string, mixed> $args Page arguments.
	 * @return string
	 */
	protected function build_seeded_support_topic_page_content( array $args ) {
		$args = wp_parse_args(
			$args,
			array(
				'intro'         => array(),
				'highlights'    => array(),
				'sections'      => array(),
				'related_links' => array(),
			)
		);

		$content = '';

		foreach ( $args['intro'] as $paragraph ) {
			$content .= $this->build_seed_block_paragraph( $paragraph );
		}

		if ( ! empty( $args['highlights'] ) && is_array( $args['highlights'] ) ) {
			$content .= $this->build_seed_block_heading( 'Key takeaways', 2 );
			$content .= $this->build_seed_block_list( $args['highlights'] );
		}

		foreach ( $args['sections'] as $section ) {
			$heading = trim( (string) ( $section['heading'] ?? '' ) );

			if ( '' !== $heading ) {
				$content .= $this->build_seed_block_heading( $heading, 2 );
			}

			if ( ! empty( $section['paragraphs'] ) && is_array( $section['paragraphs'] ) ) {
				foreach ( $section['paragraphs'] as $paragraph ) {
					$content .= $this->build_seed_block_paragraph( $paragraph );
				}
			}

			if ( ! empty( $section['list'] ) && is_array( $section['list'] ) ) {
				$content .= $this->build_seed_block_list( $section['list'] );
			}
		}

		$content .= $this->build_seed_block_heading( 'How to use this page on McDonald\'s Menu Prices USA', 2 );
		$content .= $this->build_seed_block_paragraph( 'Use this focused guide when you already know the topic you want to compare, then move into the linked pillar pages, category pages, and item pages when you need broader context, deeper price comparisons, or a more exact menu path before ordering.' );

		$content .= $this->build_seeded_related_links_block( is_array( $args['related_links'] ) ? $args['related_links'] : array() );

		return $content;
	}

	/**
	 * Return the shared methodology paragraphs used on seeded SEO pages.
	 *
	 * @return string[]
	 */
	protected function get_seeded_methodology_paragraphs() {
		return array(
			'This page is built from the current tracked McDonald&#8217;s USA menu data used across the site, combined with category-level explanation designed to make comparison easier for readers. It is written as a planning guide, not as a replacement for the final live checkout in the McDonald&#8217;s app or restaurant.',
			'Prices can vary by location, franchise, tax, delivery fee structure, app participation, and timing of promotions. For allergens, ingredients, and final live availability, always confirm details with official McDonald&#8217;s sources before ordering.',
		);
	}

	/**
	 * Build a complete long-form content body from reusable sections.
	 *
	 * @param array<string, mixed> $args Content arguments.
	 * @return string
	 */
	protected function build_seeded_long_form_page_content( array $args ) {
		$args = wp_parse_args(
			$args,
			array(
				'intro'        => array(),
				'snapshots'    => array(),
				'sections'     => array(),
				'faq_items'    => array(),
				'related_links' => array(),
				'shortcodes'   => array(),
				'methodology'  => array(),
			)
		);

		$content = '';

		foreach ( $args['intro'] as $paragraph ) {
			$content .= $this->build_seed_block_paragraph( $paragraph );
		}

		foreach ( $args['snapshots'] as $snapshot ) {
			$content .= $this->build_seeded_item_snapshot_table(
				(string) ( $snapshot['heading'] ?? 'Quick price snapshot' ),
				isset( $snapshot['items'] ) && is_array( $snapshot['items'] ) ? $snapshot['items'] : array()
			);
		}

		foreach ( $args['sections'] as $section ) {
			$heading = trim( (string) ( $section['heading'] ?? '' ) );

			if ( '' !== $heading ) {
				$content .= $this->build_seed_block_heading( $heading, 2 );
			}

			if ( ! empty( $section['paragraphs'] ) && is_array( $section['paragraphs'] ) ) {
				foreach ( $section['paragraphs'] as $paragraph ) {
					$content .= $this->build_seed_block_paragraph( $paragraph );
				}
			}

			if ( ! empty( $section['list'] ) && is_array( $section['list'] ) ) {
				$content .= $this->build_seed_block_list( $section['list'] );
			}
		}

		$content .= $this->build_seed_block_heading( 'How to use this guide with the live menu pages', 2 );
		$content .= $this->build_seed_block_paragraph( 'A long-form McDonald&#8217;s USA guide works best when it does two jobs at the same time. First, it should answer the broad search intent behind the query so readers understand the menu area, price behavior, and likely next decision. Second, it should route readers toward the live category pages and item pages when they are ready for one exact product, one meal, or one more precise comparison. That combination is what turns a thin reference page into a useful planning resource.' );
		$content .= $this->build_seed_block_paragraph( 'Many visitors do not arrive knowing exactly which page they need. They may start with a menu question, then realize they really need a deal page, an allergen check, a category comparison, or a more local pricing explanation. That is why each pillar on this site is written to help readers move from broad intent to specific action without losing the context that makes the final order decision easier.' );
		$content .= $this->build_seed_block_heading( 'What usually changes the final price or decision', 2 );
		$content .= $this->build_seed_block_paragraph( 'The posted menu price is only one part of the real answer for most readers. Final value is shaped by combo structure, add-ons, local pricing, taxes, app participation, delivery fees, and limited-time offers. In practice, that means a guide should help readers understand why the final total can move instead of pretending one number explains every location and every ordering method perfectly.' );
		$content .= $this->build_seed_block_paragraph( 'This is also where EEAT-style transparency matters. A trustworthy menu guide explains what it can confidently help with, such as category comparison and current tracked prices, and what should still be verified at the official source, such as high-stakes allergen questions, live app-only deals, or one exact local checkout total. That balance makes the content more useful for search engines, AI retrieval systems, and real users alike.' );
		$content .= $this->build_seed_block_list(
			array(
				'Location and franchise pricing can shift the final total even when the headline menu structure looks familiar.',
				'Meal upgrades, drink sizes, fries sizes, and desserts often change the real order cost more than readers expect.',
				'App-exclusive offers, rewards points, and delivery pricing can create a different value story from the in-store board price.',
				'Ingredient, allergen, and availability checks should always be confirmed with official McDonald&#8217;s sources before ordering.',
			)
		);

		$content .= $this->build_seeded_faq_blocks( is_array( $args['faq_items'] ) ? $args['faq_items'] : array() );

		$methodology = is_array( $args['methodology'] ) && ! empty( $args['methodology'] )
			? $args['methodology']
			: $this->get_seeded_methodology_paragraphs();

		$content .= $this->build_seed_block_heading( 'How we use and verify menu data', 2 );
		foreach ( $methodology as $paragraph ) {
			$content .= $this->build_seed_block_paragraph( $paragraph );
		}

		$content .= $this->build_seeded_related_links_block( is_array( $args['related_links'] ) ? $args['related_links'] : array() );

		foreach ( $args['shortcodes'] as $shortcode ) {
			$shortcode = trim( (string) $shortcode );

			if ( '' === $shortcode ) {
				continue;
			}

			$content .= $this->build_seed_block_separator();
			$content .= $this->build_seed_block_shortcode( $shortcode );
		}

		return $content;
	}

	/**
	 * Build a long-form pillar page body for one tracked menu category.
	 *
	 * @param array<string, mixed> $config Category content config.
	 * @return string
	 */
	protected function build_seeded_category_pillar_page_content( array $config ) {
		$config = wp_parse_args(
			$config,
			array(
				'category'                => '',
				'page_label'              => '',
				'focus_label'             => '',
				'include_items'           => array(),
				'value_points'            => array(),
				'nutrition_points'        => array(),
				'availability_paragraphs' => array(),
				'featured_items'          => array(),
				'faq_items'               => array(),
				'related_links'           => array(),
			)
		);

		$category = $this->get_menu_directory_category_data( $config['category'] );

		if ( ! is_array( $category ) ) {
			return '';
		}

		$page_label   = '' !== trim( (string) $config['page_label'] ) ? (string) $config['page_label'] : (string) $category['title'];
		$focus_label  = '' !== trim( (string) $config['focus_label'] ) ? (string) $config['focus_label'] : (string) $category['card_title'];
		$item_count   = isset( $category['items'] ) && is_array( $category['items'] ) ? count( $category['items'] ) : 0;
		$include_copy = $this->build_seed_human_list( is_array( $config['include_items'] ) ? $config['include_items'] : array() );
		$category_url = $this->get_menu_category_page_url( $config['category'] );
		$snapshot     = $this->get_category_seed_snapshot_items(
			array( (string) $config['category'] ),
			8,
			is_array( $config['featured_items'] ) ? $config['featured_items'] : array()
		);

		$availability_paragraphs = is_array( $config['availability_paragraphs'] ) && ! empty( $config['availability_paragraphs'] )
			? $config['availability_paragraphs']
			: array(
				'This pillar is meant to help readers understand the category before they commit to one exact item page. It connects general category decisions with the live tracked menu cards and the dedicated item pages that sit underneath them.',
				'Use the pillar for the broad comparison, then use the live category page or a specific item page when the question narrows to one product, one meal, or one exact menu price.',
			);

		$related_links = is_array( $config['related_links'] ) ? $config['related_links'] : array();
		$faq_items      = is_array( $config['faq_items'] ) ? $config['faq_items'] : array();

		array_unshift(
			$related_links,
			array(
				'label'       => 'Open the live category page',
				'url'         => $category_url,
				'description' => 'See the current tracked ' . strtolower( (string) $category['card_title'] ) . ' items and their individual price pages.',
			)
		);

		$faq_items[] = array(
			'question' => 'Do prices in this McDonald\'s USA category stay the same everywhere?',
			'answer'   => 'No. Prices in this category can still change by state, city, franchise operator, taxes, app participation, delivery fees, and limited-time promotions. The guide is built for comparison and planning, while the official McDonald\'s ordering flow should confirm the final local total.',
		);
		$faq_items[] = array(
			'question' => 'Should I use this pillar first or go straight to an item page?',
			'answer'   => 'Use the pillar first when you are still comparing options inside the category or trying to understand the value ladder. Go straight to the item page when you already know the exact product you want and only need the focused price, calories, and related menu context.',
		);

		return $this->build_seeded_long_form_page_content(
			array(
				'intro'     => array(
					'This ' . esc_html( $page_label ) . ' page helps readers compare current McDonald&#8217;s USA prices in dollars, quick calorie references, and direct item-page links without bouncing between multiple menu screens. Instead of treating the category as a short list of names, it explains how the section works, what the price tiers look like, and how to move from category browsing into exact item decisions.',
					'The live ' . esc_html( strtolower( $focus_label ) ) . ' coverage on this site currently tracks ' . esc_html( (string) $item_count ) . ' items. That means readers can move from the big-picture guide into the current category data for ' . esc_html( $include_copy ) . ' without leaving the native WordPress page structure.',
					'Prices on this site are shown in dollars for planning and comparison, but the final checkout can still vary by state, city, franchise, app participation, tax, delivery fees, and limited-time promotions. The goal of this pillar is to make the decision clearer before you open the final order screen.',
				),
				'snapshots' => array(
					array(
						'heading' => 'Quick price snapshot for this category',
						'items'   => $snapshot,
					),
				),
				'sections'  => array(
					array(
						'heading'    => 'What is on the ' . $focus_label . '?',
						'paragraphs' => array(
							'At a practical level, this category exists to answer the biggest menu-navigation question readers have before ordering: what exactly belongs in this part of the menu, and which items deserve a closer look first? For most readers, that is more useful than a thin unordered list because the decision normally starts with category comparison before it narrows into a single product.',
							'That is why this page is written as both a topical guide and a menu-routing page. It helps readers understand where the category fits inside the wider McDonald&#8217;s USA menu, what the likely price ladder looks like, and which items are usually the best starting points for comparison.',
						),
						'list'       => is_array( $config['include_items'] ) ? $config['include_items'] : array(),
					),
					array(
						'heading'    => $category['card_title'] . ' prices, value, and popular order patterns',
						'paragraphs' => array(
							'Category-level value is rarely just one number. Readers compare standalone item pricing, meal or add-on pricing, size changes, and the difference between a quick low-entry order and a more complete order that feels like a real meal. That is why value needs to be explained as a pattern rather than one flat claim.',
							'The live category page and its item pages handle the exact listings. This pillar handles the broader comparison logic so readers can understand which branch of the menu tree is actually relevant before they click deeper.',
						),
						'list'       => is_array( $config['value_points'] ) ? $config['value_points'] : array(),
					),
					array(
						'heading'    => 'Calories, customization, and what to double-check',
						'paragraphs' => array(
							'Price and calories are often researched together. Readers want to know not just what something costs, but how filling it is, how heavy it feels in the wider order, and whether an add-on or size change makes the category less practical than it first appeared.',
							'That is why this pillar keeps nutrition context visible while still pointing readers toward the separate nutrition and allergen resources when the decision becomes more sensitive or ingredient-specific.',
						),
						'list'       => is_array( $config['nutrition_points'] ) ? $config['nutrition_points'] : array(),
					),
					array(
						'heading'    => 'Availability, ordering strategy, and useful next steps',
						'paragraphs' => $availability_paragraphs,
					),
					array(
						'heading'    => 'How readers compare this category with the rest of the menu',
						'paragraphs' => array(
							'Most people do not compare this category in isolation. They are deciding whether it beats the closest alternative somewhere else on the McDonald&#8217;s USA menu. That may mean breakfast versus burgers, nuggets versus sandwiches, fries versus another side, or a dessert versus a drink-led treat order. A good pillar needs to explain that cross-category reality because it mirrors how actual search behavior works.',
							'The strongest comparison pages are the ones that help readers decide what type of order they are building before they obsess over one exact item. Once that higher-level decision is made, the item-page comparison becomes faster and cleaner because the reader already understands the category context, price ladder, and likely add-on path.',
							'This is also one of the reasons search engines reward broader topical coverage. A category page that understands adjacent menu entities is more useful than a thin page that repeats only one item name. It signals that the site can answer the wider decision set around value, calories, timing, and add-ons rather than treating every menu query as an isolated fact lookup.',
						),
					),
					array(
						'heading'    => 'What usually changes the final total in this category',
						'paragraphs' => array(
							'Readers often search for one posted item price, but the real order total in this category is usually shaped by what happens next. A meal upgrade, larger drink, extra sauce, dessert add-on, or premium customization can move the total far more than the first price on the menu board suggests. That is why this pillar emphasizes ordering patterns rather than only one number.',
							'For some categories, the hidden swing comes from portion size. For others, it comes from combo structure, side choices, or premium limited-time items. Either way, the important SEO and user-experience job of the pillar is to explain where the price pressure usually appears so readers do not misread a low-entry item as the final likely spend.',
							'This category context is also helpful for AI search visibility because it makes the page retrieval-ready for more than one query style. Someone searching for price, value, calories, best order, or cheapest build can all land on the same page and still find an explanation that matches their real intent.',
						),
						'list'       => array(
							'Standalone item pricing and full meal pricing can tell very different value stories.',
							'Add-ons such as fries, drinks, sauces, desserts, or premium customizations often create the biggest hidden jump.',
							'Local pricing and app participation may change the practical best-value choice inside the same category.',
							'Limited-time items can temporarily reset the normal category price ladder and draw clicks away from evergreen favorites.',
						),
					),
					array(
						'heading'    => 'Who this category usually serves best',
						'paragraphs' => array(
							'Every major McDonald&#8217;s USA category solves a slightly different ordering problem. Some categories are strongest for quick solo orders, some for heavier meal seekers, some for families, some for snack-style add-ons, and some for readers who are balancing taste, cost, and convenience at the same time. A category pillar becomes more useful when it acknowledges those audience differences directly.',
							'That audience framing is part of EEAT as well. Helpful content is not only factually organized; it is written in a way that shows the writer understands how real customers use the menu in practice. Readers searching these pages are often trying to spend wisely, compare fairly, and avoid surprise calories or surprise total costs. The content should respect that practical intent.',
							'Once the likely use case is clear, the best next step is usually straightforward: open the live category page, jump to the most relevant item page, or move sideways into deals, nutrition, breakfast hours, or regional pricing depending on what is blocking the final decision.',
						),
					),
				),
				'faq_items' => $faq_items,
				'related_links' => $related_links,
				'shortcodes' => array(
					'[mcprices_menu_category category="' . esc_attr( (string) $config['category'] ) . '"]',
				),
			)
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
	 * Create or update one managed menu directory page.
	 *
	 * @param array $page_args Managed page arguments.
	 * @return array{id:int,changed:bool}
	 */
	protected function upsert_menu_directory_page( array $page_args ) {
		$defaults = array(
			'path'        => '',
			'title'       => '',
			'slug'        => '',
			'content'     => '',
			'excerpt'     => '',
			'post_parent' => 0,
			'menu_order'  => 0,
			'type'        => 'menu-page',
			'key'         => '',
		);

		$page_args = wp_parse_args( $page_args, $defaults );
		$page_path = trim( (string) $page_args['path'], '/' );

		if ( '' === $page_path || '' === trim( (string) $page_args['title'] ) || '' === trim( (string) $page_args['slug'] ) ) {
			return array(
				'id'      => 0,
				'changed' => false,
			);
		}

		$page    = get_page_by_path( $page_path, OBJECT, 'page' );
		$changed = false;

		if ( $page instanceof \WP_Post ) {
			$needs_update =
				$page_args['title'] !== (string) $page->post_title ||
				$page_args['slug'] !== (string) $page->post_name ||
				'publish' !== (string) $page->post_status ||
				(int) $page_args['post_parent'] !== (int) $page->post_parent ||
				(int) $page_args['menu_order'] !== (int) $page->menu_order ||
				trim( (string) $page_args['content'] ) !== trim( (string) $page->post_content ) ||
				trim( (string) $page_args['excerpt'] ) !== trim( (string) $page->post_excerpt );

			if ( $needs_update ) {
				wp_update_post(
					array(
						'ID'           => (int) $page->ID,
						'post_title'   => $page_args['title'],
						'post_name'    => $page_args['slug'],
						'post_status'  => 'publish',
						'post_parent'  => (int) $page_args['post_parent'],
						'menu_order'   => (int) $page_args['menu_order'],
						'post_content' => $page_args['content'],
						'post_excerpt' => $page_args['excerpt'],
					)
				);
				$changed = true;
				$page    = get_post( (int) $page->ID );
			}
		} else {
			$page_id = wp_insert_post(
				array(
					'post_title'   => $page_args['title'],
					'post_name'    => $page_args['slug'],
					'post_type'    => 'page',
					'post_status'  => 'publish',
					'post_parent'  => (int) $page_args['post_parent'],
					'menu_order'   => (int) $page_args['menu_order'],
					'post_content' => $page_args['content'],
					'post_excerpt' => $page_args['excerpt'],
				),
				true
			);

			if ( is_wp_error( $page_id ) || ! $page_id ) {
				return array(
					'id'      => 0,
					'changed' => false,
				);
			}

			$page    = get_post( (int) $page_id );
			$changed = true;
		}

		if ( $page instanceof \WP_Post ) {
			update_post_meta( (int) $page->ID, '_mcprices_managed_page', (string) $page_args['type'] );
			update_post_meta( (int) $page->ID, '_mcprices_managed_key', (string) $page_args['key'] );
		}

		return array(
			'id'      => $page instanceof \WP_Post ? (int) $page->ID : 0,
			'changed' => $changed,
		);
	}

	/**
	 * Return the current signature for the generated menu directory pages.
	 *
	 * @return string
	 */
	protected function get_menu_directory_signature() {
		return $this->get_seed_signature(
			array(
				'categories' => $this->get_menu_directory_categories(),
				'root'       => $this->get_menu_directory_root_url(),
			)
		);
	}

	/**
	 * Seed the native /menu/ directory, its category pages, and item pages.
	 *
	 * @return bool
	 */
	protected function maybe_seed_menu_directory_pages() {
		$current_signature = $this->get_menu_directory_signature();
		$stored_signature  = (string) get_option( self::MENU_DIRECTORY_SIGNATURE_OPTION, '' );
		$root_page         = get_page_by_path( 'menu', OBJECT, 'page' );

		if ( $root_page instanceof \WP_Post && $stored_signature === $current_signature ) {
			return false;
		}

		$changed     = false;
		$root_result = $this->upsert_menu_directory_page(
			array(
				'path'        => 'menu',
				'title'       => 'Menu',
				'slug'        => 'menu',
				'post_parent' => 0,
				'menu_order'  => 0,
				'content'     => '<!-- wp:shortcode -->[mcprices_menu_directory]<!-- /wp:shortcode -->',
				'excerpt'     => 'Browse every McDonald\'s USA menu category, then open separate item pages for prices, calories, and quick details.',
				'type'        => 'menu-root',
				'key'         => 'menu',
			)
		);

		if ( $root_result['changed'] ) {
			$changed = true;
		}

		$root_page_id = (int) $root_result['id'];

		if ( ! $root_page_id ) {
			return $changed;
		}

		$category_order = 10;

		foreach ( $this->get_menu_directory_categories() as $category_id => $category ) {
			$category_result = $this->upsert_menu_directory_page(
				array(
					'path'        => 'menu/' . $category['slug'],
					'title'       => (string) $category['title'],
					'slug'        => (string) $category['slug'],
					'post_parent' => $root_page_id,
					'menu_order'  => $category_order,
					'content'     => '<!-- wp:shortcode -->[mcprices_menu_category category="' . esc_attr( $category_id ) . '"]<!-- /wp:shortcode -->',
					'excerpt'     => (string) $category['description'],
					'type'        => 'menu-category',
					'key'         => (string) $category_id,
				)
			);

			if ( $category_result['changed'] ) {
				$changed = true;
			}

			$category_page_id = (int) $category_result['id'];

			if ( ! $category_page_id ) {
				$category_order += 10;
				continue;
			}

			$item_order = 10;

			foreach ( $category['items'] as $item ) {
				$item_title = trim( (string) $item['name'] ) . ' Price USA';
				$item_excerpt = trim( (string) $item['summary'] );

				$item_result = $this->upsert_menu_directory_page(
					array(
						'path'        => 'menu/' . $category['slug'] . '/' . $item['slug'],
						'title'       => $item_title,
						'slug'        => (string) $item['slug'],
						'post_parent' => $category_page_id,
						'menu_order'  => $item_order,
						'content'     => '<!-- wp:shortcode -->[mcprices_menu_item category="' . esc_attr( $category_id ) . '" item="' . esc_attr( $item['slug'] ) . '"]<!-- /wp:shortcode -->',
						'excerpt'     => $item_excerpt,
						'type'        => 'menu-item',
						'key'         => (string) $category_id . '::' . (string) $item['slug'],
					)
				);

				if ( $item_result['changed'] ) {
					$changed = true;
				}

				$item_order += 10;
			}

			$category_order += 10;
		}

		update_option( self::MENU_DIRECTORY_SIGNATURE_OPTION, $current_signature, false );

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
		$this->maybe_seed_menu_directory_pages();
		McPrices_Menu_Root_Seed::maybe_seed();
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
		add_shortcode( 'mcprices_menu_directory', array( $this, 'render_menu_directory_shortcode' ) );
		add_shortcode( 'mcprices_menu_category', array( $this, 'render_menu_category_shortcode' ) );
		add_shortcode( 'mcprices_menu_item', array( $this, 'render_menu_item_shortcode' ) );
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
	 * Return the public root URL for the native menu directory.
	 *
	 * @return string
	 */
	public function get_menu_directory_root_url() {
		return home_url( '/menu/' );
	}

	/**
	 * Return the public category page URL for a menu directory category.
	 *
	 * @param string $category_id Category identifier.
	 * @return string
	 */
	public function get_menu_category_page_url( $category_id ) {
		$category = $this->get_menu_directory_category_data( $category_id );

		if ( ! is_array( $category ) ) {
			return $this->get_menu_directory_root_url();
		}

		return trailingslashit( untrailingslashit( $this->get_menu_directory_root_url() ) . '/' . $category['slug'] );
	}

	/**
	 * Return the public item page URL for a menu directory item.
	 *
	 * @param string $category_id     Category identifier.
	 * @param string $item_identifier Item slug, title, or raw name.
	 * @return string
	 */
	public function get_menu_item_page_url( $category_id, $item_identifier ) {
		$category = $this->get_menu_directory_category_data( $category_id );

		if ( ! is_array( $category ) ) {
			return $this->get_menu_directory_root_url();
		}

		$item = $this->get_menu_directory_item_data( $category_id, $item_identifier );

		if ( ! is_array( $item ) ) {
			return $this->get_menu_category_page_url( $category_id );
		}

		return trailingslashit( untrailingslashit( $this->get_menu_category_page_url( $category_id ) ) . '/' . $item['slug'] );
	}

	/**
	 * Return the public category artwork URL for a menu category.
	 *
	 * @param string $category_id Category identifier.
	 * @return string
	 */
	public function get_menu_category_media_asset_url( $category_id ) {
		return $this->get_category_media_url( $category_id );
	}

	/**
	 * Return the public item artwork URL for a menu item.
	 *
	 * @param string $item_name Item name.
	 * @return string
	 */
	public function get_menu_item_media_asset_url( $item_name ) {
		return $this->get_item_media_url( $item_name );
	}

	/**
	 * Return the parsed USA menu source data used across the native pages.
	 *
	 * @return array
	 */
	protected function get_menu_source_data() {
		static $menu_source = null;

		if ( null !== $menu_source ) {
			return $menu_source;
		}

		$menu_source_path = get_theme_file_path( '/assets/data/mcprices-usa-menu.json' );
		$menu_source_json = file_exists( $menu_source_path ) ? file_get_contents( $menu_source_path ) : false;
		$menu_source      = is_string( $menu_source_json ) ? json_decode( $menu_source_json, true ) : array();
		$menu_source      = is_array( $menu_source ) ? $menu_source : array();

		return $menu_source;
	}

	/**
	 * Return a single source section from the menu JSON file.
	 *
	 * @param string $source_id Source section ID.
	 * @return array
	 */
	protected function get_menu_source_section( $source_id ) {
		foreach ( $this->get_menu_source_data() as $section ) {
			if ( isset( $section['id'] ) && $source_id === $section['id'] ) {
				return is_array( $section ) ? $section : array();
			}
		}

		return array(
			'id'    => $source_id,
			'title' => '',
			'icon'  => '',
			'subs'  => array(),
		);
	}

	/**
	 * Return the item count for a source section.
	 *
	 * @param array $section Section array.
	 * @return int
	 */
	protected function count_menu_source_rows( array $section ) {
		$count = 0;

		foreach ( $section['subs'] ?? array() as $sub_section ) {
			$count += count( $sub_section['rows'] ?? array() );
		}

		return $count;
	}

	/**
	 * Format a calorie string in a consistent display format.
	 *
	 * @param string $calories Raw calorie text.
	 * @return string
	 */
	protected function format_menu_directory_calories( $calories ) {
		$calories = trim( (string) $calories );

		if ( '' === $calories ) {
			return '';
		}

		if ( false !== stripos( $calories, 'kcal' ) ) {
			return $calories;
		}

		return $calories . ' kcal';
	}

	/**
	 * Normalize a raw menu item label for human-readable page titles.
	 *
	 * @param string $name Raw menu item name.
	 * @return string
	 */
	protected function get_menu_directory_item_title( $name ) {
		$title = html_entity_decode( (string) $name, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		$title = preg_replace( '/\([^)]*\)/', '', $title );
		$title = str_replace( array( 'TM', '®', '™' ), '', (string) $title );

		return trim( preg_replace( '/\s+/', ' ', (string) $title ) );
	}

	/**
	 * Build the status label used on menu rows and item cards.
	 *
	 * @param string $source_id Source section ID.
	 * @param string $sub_label Sub-section label.
	 * @param string $row_name  Item name.
	 * @param string $price     Item price.
	 * @param array  $tags      Optional row tags.
	 * @return string
	 */
	protected function get_menu_directory_row_status( $source_id, $sub_label, $row_name, $price = '', array $tags = array() ) {
		$status = 'Item';

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
				$status = false !== stripos( $row_name, 'Fish' ) ? 'Fish' : 'Chicken';
				break;
			case 'nuggets':
				if ( false !== stripos( $row_name, 'Strips' ) ) {
					$status = in_array( 'new', $tags, true ) ? 'New' : 'Strips';
				} elseif ( false !== stripos( $row_name, '20 pc' ) || false !== stripos( $row_name, '40 pc' ) ) {
					$status = 'Shareable';
				} else {
					$status = 'Nuggets';
				}
				break;
			case 'wrap':
				$status = 'Wrap';
				break;
			case 'sides':
				$status = false !== stripos( $row_name, 'Apple' ) ? 'Fruit' : 'Side';
				break;
			case 'happy':
				$status = 'Kids';
				break;
			case 'sweets':
				if ( in_array( 'ltd', $tags, true ) ) {
					$status = 'Limited';
				} elseif ( false !== stripos( $row_name, 'McFlurry' ) ) {
					$status = 'McFlurry';
				} elseif ( false !== stripos( $row_name, 'Shake' ) ) {
					$status = 'Shake';
				} elseif ( false !== stripos( $row_name, 'Pie' ) || false !== stripos( $row_name, 'Cookie' ) ) {
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
				$status = in_array( trim( (string) $price ), array( 'Incl.', 'Free' ), true ) ? 'Included' : 'Sauce';
				break;
			case 'kpop':
			case 'bigarch':
				$status = in_array( 'new', $tags, true ) ? 'New' : 'Limited Time';
				break;
		}

		return $status;
	}

	/**
	 * Build a short readable summary for a menu item page.
	 *
	 * @param string $row_name       Raw item name.
	 * @param string $sub_label      Source sub label.
	 * @param string $category_title Category title.
	 * @return string
	 */
	protected function get_menu_directory_item_summary( $row_name, $sub_label, $category_title ) {
		$row_name = html_entity_decode( (string) $row_name, ENT_QUOTES | ENT_HTML5, 'UTF-8' );

		if ( preg_match( '/\(([^)]{4,})\)/', $row_name, $matches ) ) {
			$summary = trim( preg_replace( '/\s+/', ' ', (string) $matches[1] ) );

			if ( '' !== $summary ) {
				return ucfirst( rtrim( $summary, '.' ) ) . '.';
			}
		}

		if ( '' !== trim( (string) $sub_label ) ) {
			return sprintf(
				'%1$s in the %2$s grouping on the current McDonald\'s USA menu data tracked by this site.',
				$this->get_menu_directory_item_title( $row_name ),
				trim( (string) $sub_label )
			);
		}

		return sprintf(
			'Current %1$s menu listing tracked in the %2$s section on McDonald\'s Menu Prices USA.',
			strtolower( $this->get_menu_directory_item_title( $row_name ) ),
			$category_title
		);
	}

	/**
	 * Return the configured top-level menu category blueprints.
	 *
	 * @return array
	 */
	protected function get_menu_directory_category_blueprints() {
		return array(
			'whats-new'  => array(
				'id'          => 'whats-new',
				'slug'        => 'whats-new',
				'title'       => 'What\'s New Menu Prices USA',
				'card_title'  => 'What\'s New',
				'count_label' => 'limited-time items',
				'description' => 'Current limited-time and featured items showing up across the tracked McDonald\'s USA menu snapshot.',
				'sources'     => array( 'kpop', 'bigarch' ),
			),
			'meals'      => array(
				'id'          => 'meals',
				'slug'        => 'meals',
				'title'       => 'Extra Value Meals Menu Prices USA',
				'card_title'  => 'Extra Value Meals',
				'count_label' => 'meals',
				'description' => 'Breakfast, lunch, and dinner combo meal pricing pulled from the tracked McDonald\'s USA menu data.',
				'sources'     => array( 'evm' ),
			),
			'mcvalue'    => array(
				'id'          => 'mcvalue',
				'slug'        => 'mcvalue',
				'title'       => 'McValue Menu Prices USA',
				'card_title'  => 'McValue',
				'count_label' => 'value picks',
				'description' => 'Current McValue picks, meal deals, buy-one-add-one offers, and lower-cost dessert and snack entries.',
				'sources'     => array( 'mcvalue' ),
			),
			'breakfast'  => array(
				'id'          => 'breakfast',
				'slug'        => 'breakfast',
				'title'       => 'Breakfast Menu Prices USA',
				'card_title'  => 'Breakfast',
				'count_label' => 'breakfast items',
				'description' => 'Breakfast sandwiches, McMuffins, biscuits, McGriddles, bagels, platters, oatmeal, and breakfast sides.',
				'sources'     => array( 'bfast' ),
			),
			'burgers'    => array(
				'id'          => 'burgers',
				'slug'        => 'burgers',
				'title'       => 'Burgers Menu Prices USA',
				'card_title'  => 'Burgers',
				'count_label' => 'burgers',
				'description' => 'Current burger prices for Big Mac, Quarter Pounder builds, McDouble, Daily Double, cheeseburgers, and hamburgers.',
				'sources'     => array( 'burgers' ),
			),
			'chickenfish' => array(
				'id'          => 'chickenfish',
				'slug'        => 'chickenfish',
				'title'       => 'Chicken & Fish Menu Prices USA',
				'card_title'  => 'Chicken & Fish',
				'count_label' => 'sandwiches',
				'description' => 'Chicken and fish sandwich pricing for McCrispy builds, Filet-O-Fish, and McChicken options in the USA.',
				'sources'     => array( 'chicken' ),
			),
			'nuggets'    => array(
				'id'          => 'nuggets',
				'slug'        => 'nuggets',
				'title'       => 'McNuggets & Strips Prices USA',
				'card_title'  => 'McNuggets & Strips',
				'count_label' => 'chicken items',
				'description' => 'Chicken McNuggets and McCrispy Strips pricing, from snack sizes up to 40-piece shareable packs.',
				'sources'     => array( 'nuggets' ),
			),
			'snackwrap'  => array(
				'id'          => 'snackwrap',
				'slug'        => 'snackwrap',
				'title'       => 'Snack Wrap Prices USA',
				'card_title'  => 'Snack Wrap',
				'count_label' => 'wraps',
				'description' => 'Both current Snack Wrap flavors from the tracked McDonald\'s USA menu source.',
				'sources'     => array( 'wrap' ),
			),
			'sides'      => array(
				'id'          => 'sides',
				'slug'        => 'sides',
				'title'       => 'Fries & Sides Prices USA',
				'card_title'  => 'Fries & Sides',
				'count_label' => 'side items',
				'description' => 'World Famous Fries in each listed size plus Apple Slices and other side-menu add-ons in the USA.',
				'sources'     => array( 'sides' ),
			),
			'happymeal'  => array(
				'id'          => 'happymeal',
				'slug'        => 'happymeal',
				'title'       => 'Happy Meal Prices USA',
				'card_title'  => 'Happy Meal',
				'count_label' => 'kids meals',
				'description' => 'Current Happy Meal pricing for hamburger and McNuggets builds from the tracked USA menu file.',
				'sources'     => array( 'happy' ),
			),
			'sweets'     => array(
				'id'          => 'sweets',
				'slug'        => 'sweets',
				'title'       => 'Sweets & Treats Prices USA',
				'card_title'  => 'Sweets & Treats',
				'count_label' => 'treats',
				'description' => 'McFlurries, cones, sundaes, shakes, pies, cookies, and other dessert pricing from the current USA sweets section.',
				'sources'     => array( 'sweets' ),
			),
			'mccafe'     => array(
				'id'          => 'mccafe',
				'slug'        => 'mccafe',
				'title'       => 'McCafe Coffee Prices USA',
				'card_title'  => 'McCafe Coffees',
				'count_label' => 'coffee drinks',
				'description' => 'Full McCafe coffee and espresso pricing, including hot drinks, iced drinks, frappes, and hot chocolate.',
				'sources'     => array( 'coffee' ),
			),
			'beverages'  => array(
				'id'          => 'beverages',
				'slug'        => 'beverages',
				'title'       => 'Beverage Prices USA',
				'card_title'  => 'Beverages',
				'count_label' => 'drinks',
				'description' => 'Soft drinks, frozen drinks, smoothies, lemonade, tea, juice, milk, and bottled water from the current USA beverage menu.',
				'sources'     => array( 'bev' ),
			),
			'sauces'     => array(
				'id'          => 'sauces',
				'slug'        => 'sauces',
				'title'       => 'Sauces & Condiments Prices USA',
				'card_title'  => 'Sauces & Condiments',
				'count_label' => 'sauce items',
				'description' => 'Current dipping sauces and condiments, including included sauces and low-cost paid extras.',
				'sources'     => array( 'sauce' ),
			),
			'deals'      => array(
				'id'             => 'deals',
				'slug'           => 'deals',
				'title'          => 'McDonald\'s Deals Prices USA',
				'card_title'     => 'Deals',
				'count_label'    => 'Current McValue offers',
				'static_count'   => 'Current McValue offers',
				'description'    => 'Current McValue meal deals, buy-one-add-one offers, and value-led picks highlighted on McDonald\'s Menu Prices USA.',
				'special_source' => 'deals',
				'sources'        => array(),
			),
		);
	}

	/**
	 * Return the curated homepage-style deal items used for the deals page.
	 *
	 * @return array
	 */
	protected function get_menu_directory_deal_items() {
		$items = array(
			array(
				'name'        => 'McChicken Meal Deal',
				'raw_name'    => 'McChicken Meal Deal',
				'price'       => '$5.00',
				'calories'    => '',
				'status'      => '$5 Meal Deal',
				'summary'     => 'McChicken, 4 pc McNuggets, small fries, and a small drink from the current McValue lineup.',
				'image'       => $this->get_item_media_url( 'McChicken' ),
				'category_id' => 'deals',
			),
			array(
				'name'        => 'McDouble Meal Deal',
				'raw_name'    => 'McDouble Meal Deal',
				'price'       => '$5.00',
				'calories'    => '',
				'status'      => '$5 Meal Deal',
				'summary'     => 'McDouble, 4 pc McNuggets, small fries, and a small drink from the same current McValue offer set.',
				'image'       => $this->get_item_media_url( 'McDouble' ),
				'category_id' => 'deals',
			),
			array(
				'name'        => 'Daily Double Meal Deal',
				'raw_name'    => 'Daily Double Meal Deal',
				'price'       => '~$6.00',
				'calories'    => '',
				'status'      => '$6 Meal Deal',
				'summary'     => 'The Daily Double Meal Deal appears in the current McValue data as the higher-entry limited-time meal deal option.',
				'image'       => $this->get_item_media_url( 'Daily Double Meal Deal' ),
				'category_id' => 'deals',
			),
			array(
				'name'        => 'Breakfast Buy 1 Add 1 for $1',
				'raw_name'    => 'Breakfast Buy 1 Add 1 for $1',
				'price'       => '$2.99 base items',
				'calories'    => '',
				'status'      => 'Breakfast BOGO',
				'summary'     => 'The breakfast offer applies to Sausage Biscuit, Sausage McMuffin, Sausage Burrito, and Hash Browns.',
				'image'       => $this->get_category_media_url( 'deals' ),
				'category_id' => 'deals',
			),
			array(
				'name'        => 'Lunch Buy 1 Add 1 for $1',
				'raw_name'    => 'Lunch Buy 1 Add 1 for $1',
				'price'       => '$2.89-$4.39 items',
				'calories'    => '',
				'status'      => 'Lunch BOGO',
				'summary'     => 'Current lunch and dinner McValue add-on picks include Double Cheeseburger, McChicken, 6 pc McNuggets, and Small World Famous Fries.',
				'image'       => $this->get_category_media_url( 'deals' ),
				'category_id' => 'deals',
			),
			array(
				'name'        => 'McValue Mini McFlurry Picks',
				'raw_name'    => 'McValue Mini McFlurry Picks',
				'price'       => '$3.19',
				'calories'    => '',
				'status'      => 'Mini dessert',
				'summary'     => 'Mini M&M\'s and OREO McFlurry cups appear in the tracked McValue Eats section as low-entry dessert options.',
				'image'       => $this->get_item_media_url( 'OREO McFlurry' ),
				'category_id' => 'deals',
			),
		);

		return $this->assign_unique_menu_item_slugs( $items );
	}

	/**
	 * Assign stable unique slugs to a list of menu directory items.
	 *
	 * @param array $items Item list.
	 * @return array
	 */
	protected function assign_unique_menu_item_slugs( array $items ) {
		$used_slugs = array();

		foreach ( $items as $index => $item ) {
			$base_slug = sanitize_title( $item['name'] ?? '' );
			$base_slug = '' !== $base_slug ? $base_slug : 'menu-item';
			$slug      = $base_slug;
			$suffix    = 2;

			while ( isset( $used_slugs[ $slug ] ) ) {
				$slug = $base_slug . '-' . $suffix;
				++$suffix;
			}

			$used_slugs[ $slug ] = true;
			$items[ $index ]['slug'] = $slug;
		}

		return $items;
	}

	/**
	 * Build the data rows for a single source section.
	 *
	 * @param string $source_id      Source section ID.
	 * @param string $category_id    Directory category ID.
	 * @param string $category_title Directory category title.
	 * @return array
	 */
	protected function get_menu_directory_items_from_source( $source_id, $category_id, $category_title ) {
		$section = $this->get_menu_source_section( $source_id );
		$items   = array();

		foreach ( $section['subs'] ?? array() as $sub_section ) {
			$sub_label = trim( (string) ( $sub_section['sub'] ?? '' ) );

			foreach ( $sub_section['rows'] ?? array() as $row ) {
				$raw_name = trim( (string) ( $row['name'] ?? '' ) );

				if ( '' === $raw_name ) {
					continue;
				}

				$title = $this->get_menu_directory_item_title( $raw_name );
				$tags  = isset( $row['tags'] ) && is_array( $row['tags'] ) ? $row['tags'] : array();

				$items[] = array(
					'name'        => $title,
					'raw_name'    => $raw_name,
					'price'       => trim( (string) ( $row['price'] ?? '' ) ),
					'calories'    => $this->format_menu_directory_calories( $row['calories'] ?? '' ),
					'status'      => $this->get_menu_directory_row_status( $source_id, $sub_label, $raw_name, trim( (string) ( $row['price'] ?? '' ) ), $tags ),
					'summary'     => $this->get_menu_directory_item_summary( $raw_name, $sub_label, $category_title ),
					'image'       => $this->get_item_media_url( $raw_name ),
					'category_id' => $category_id,
					'sub_label'   => $sub_label,
				);
			}
		}

		return $items;
	}

	/**
	 * Return all configured menu directory categories with item data.
	 *
	 * @return array
	 */
	protected function get_menu_directory_categories() {
		static $categories = null;

		if ( null !== $categories ) {
			return $categories;
		}

		$categories = array();

		foreach ( $this->get_menu_directory_category_blueprints() as $category_id => $blueprint ) {
			$items = array();

			if ( isset( $blueprint['special_source'] ) && 'deals' === $blueprint['special_source'] ) {
				$items = $this->get_menu_directory_deal_items();
			} else {
				foreach ( $blueprint['sources'] as $source_id ) {
					$items = array_merge(
						$items,
						$this->get_menu_directory_items_from_source( $source_id, $category_id, $blueprint['card_title'] )
					);
				}

				$items = $this->assign_unique_menu_item_slugs( $items );
			}

			$count_text = isset( $blueprint['static_count'] )
				? (string) $blueprint['static_count']
				: count( $items ) . ' ' . $blueprint['count_label'];

			$categories[ $category_id ] = array_merge(
				$blueprint,
				array(
					'items'      => $items,
					'count_text' => $count_text,
				)
			);
		}

		return $categories;
	}

	/**
	 * Return one menu directory category data array when available.
	 *
	 * @param string $category_id Category ID.
	 * @return array|null
	 */
	protected function get_menu_directory_category_data( $category_id ) {
		$categories = $this->get_menu_directory_categories();

		return isset( $categories[ $category_id ] ) ? $categories[ $category_id ] : null;
	}

	/**
	 * Return one menu directory item from a category by slug or title.
	 *
	 * @param string $category_id     Category ID.
	 * @param string $item_identifier Item slug or title.
	 * @return array|null
	 */
	protected function get_menu_directory_item_data( $category_id, $item_identifier ) {
		$category = $this->get_menu_directory_category_data( $category_id );

		if ( ! is_array( $category ) ) {
			return null;
		}

		$item_identifier = trim( (string) $item_identifier );
		$normalized_item = $this->normalize_media_key( $item_identifier );

		foreach ( $category['items'] as $item ) {
			$matches_slug  = isset( $item['slug'] ) && $item_identifier === $item['slug'];
			$matches_name  = isset( $item['name'] ) && 0 === strcasecmp( $item_identifier, $item['name'] );
			$matches_raw   = isset( $item['raw_name'] ) && 0 === strcasecmp( $item_identifier, $item['raw_name'] );
			$matches_key   = isset( $item['raw_name'] ) && $normalized_item === $this->normalize_media_key( $item['raw_name'] );

			if ( $matches_slug || $matches_name || $matches_raw || $matches_key ) {
				return $item;
			}
		}

		return null;
	}

	/**
	 * Return the category artwork URL when available.
	 *
	 * @param string $category_id Category ID.
	 * @return string
	 */
	protected function get_category_media_url( $category_id ) {
		$manifest   = $this->get_media_manifest();
		$categories = isset( $manifest['categories'] ) && is_array( $manifest['categories'] ) ? $manifest['categories'] : array();
		$relative   = isset( $categories[ $category_id ] ) ? (string) $categories[ $category_id ] : '';

		if ( '' === $relative ) {
			return '';
		}

		return trailingslashit( get_theme_file_uri( '/assets/images/mcprices/official' ) ) . ltrim( $relative, '/' );
	}

	/**
	 * Return the artwork that should appear for a specific item card.
	 *
	 * @param array $item Item data.
	 * @return string
	 */
	protected function get_menu_directory_item_image( array $item ) {
		if ( ! empty( $item['image'] ) ) {
			return (string) $item['image'];
		}

		return $this->get_category_media_url( $item['category_id'] ?? '' );
	}

	/**
	 * Render the managed root menu directory page.
	 *
	 * @return string
	 */
	public function render_menu_directory_shortcode() {
		$categories = $this->get_menu_directory_categories();

		ob_start();
		?>
		<div class="mcprices-page mcprices-directory-page mcprices-directory-index">
			<section class="categories mcprices-directory-section">
				<div class="container">
					<div class="section-header">
						<div class="section-label">Menu Directory</div>
						<h2 class="section-title">Browse McDonald&rsquo;s USA Menu Categories</h2>
						<p class="section-sub">Open any category page to browse every tracked item, then use the read-more links to open dedicated item pages without changing the homepage design.</p>
					</div>
					<div class="cat-grid">
						<?php foreach ( $categories as $category ) : ?>
							<a href="<?php echo esc_url( $this->get_menu_category_page_url( $category['id'] ) ); ?>" class="cat-card" data-category-id="<?php echo esc_attr( $category['id'] ); ?>">
								<span class="cat-emoji">
									<?php if ( $this->get_category_media_url( $category['id'] ) ) : ?>
										<img class="mcprices-media-icon mcprices-media-icon--category" src="<?php echo esc_url( $this->get_category_media_url( $category['id'] ) ); ?>" alt="<?php echo esc_attr( 'McDonald\'s ' . $category['card_title'] . ' menu USA' ); ?>" loading="lazy" decoding="async">
									<?php endif; ?>
								</span>
								<div class="cat-name"><?php echo esc_html( $category['card_title'] ); ?></div>
								<div class="cat-count"><?php echo esc_html( $category['count_text'] ); ?></div>
								<div class="cat-arrow">&rarr;</div>
							</a>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
		</div>
		<?php

		return (string) ob_get_clean();
	}

	/**
	 * Render one managed category landing page.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function render_menu_category_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'category' => '',
			),
			(array) $atts,
			'mcprices_menu_category'
		);

		$category = $this->get_menu_directory_category_data( $atts['category'] );

		if ( ! is_array( $category ) ) {
			return '';
		}

		$guide_url   = $this->get_menu_category_primary_guide_url( $category['id'] );
		$guide_title = $this->get_menu_category_primary_guide_title( $category['id'] );

		ob_start();
		?>
		<div class="mcprices-page mcprices-directory-page mcprices-category-page">
			<section class="internal-links mcprices-directory-section">
				<div class="container">
					<div class="mcprices-directory-crumbs">
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a>
						<span>&rsaquo;</span>
						<a href="<?php echo esc_url( $this->get_menu_directory_root_url() ); ?>">Menu</a>
						<span>&rsaquo;</span>
						<span><?php echo esc_html( $category['card_title'] ); ?></span>
					</div>
					<div class="section-header section-header-left">
						<div class="section-label">Category Page</div>
						<h2 class="section-title"><?php echo esc_html( $category['title'] ); ?></h2>
						<p class="section-sub"><?php echo esc_html( $category['description'] ); ?></p>
						<?php if ( $guide_url && $guide_title ) : ?>
							<p class="section-sub">Need the broader category context first? Read the <a href="<?php echo esc_url( $guide_url ); ?>"><?php echo esc_html( $guide_title ); ?></a> guide, or return to the <a href="<?php echo esc_url( $this->get_menu_directory_root_url() ); ?>">full menu directory</a>.</p>
						<?php endif; ?>
					</div>
					<div class="mcprices-directory-meta">
						<div class="mcprices-directory-meta-card">
							<strong><?php echo esc_html( (string) count( $category['items'] ) ); ?></strong>
							<span>Tracked items</span>
						</div>
						<div class="mcprices-directory-meta-card">
							<strong>USD</strong>
							<span>Prices shown in dollars</span>
						</div>
						<div class="mcprices-directory-meta-card">
							<strong>Live</strong>
							<span>Based on current site menu data</span>
						</div>
					</div>
					<div class="menu-cards-grid featured-grid mcprices-directory-cards">
						<?php foreach ( $category['items'] as $item ) : ?>
							<article class="menu-card mcprices-directory-card">
								<div class="card-img">
									<?php if ( $this->get_menu_directory_item_image( $item ) ) : ?>
										<img class="mcprices-card-media" src="<?php echo esc_url( $this->get_menu_directory_item_image( $item ) ); ?>" alt="<?php echo esc_attr( 'McDonald\'s ' . $item['name'] . ' price USA 2026' ); ?>" loading="lazy" decoding="async">
									<?php endif; ?>
								</div>
								<div class="card-body">
									<div class="card-top">
										<div>
											<div class="card-name"><a href="<?php echo esc_url( $this->get_menu_item_page_url( $category['id'], $item['slug'] ) ); ?>"><?php echo esc_html( $item['name'] ); ?></a></div>
											<div class="card-cal"><?php echo esc_html( $item['calories'] ? $item['calories'] : 'Calories vary by selection' ); ?></div>
										</div>
										<div class="card-price"><?php echo esc_html( $item['price'] ? $item['price'] : 'Varies' ); ?></div>
									</div>
									<div class="card-meta">
										<span class="card-chip"><?php echo esc_html( $item['status'] ); ?></span>
									</div>
									<p class="mcprices-directory-copy"><?php echo esc_html( $item['summary'] ); ?></p>
								</div>
								<div class="card-footer">
									<a class="btn-card" href="<?php echo esc_url( $this->get_menu_item_page_url( $category['id'], $item['slug'] ) ); ?>">Read more</a>
								</div>
							</article>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
		</div>
		<?php

		return (string) ob_get_clean();
	}

	/**
	 * Render one managed menu item page.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function render_menu_item_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'category' => '',
				'item'     => '',
			),
			(array) $atts,
			'mcprices_menu_item'
		);

		$category = $this->get_menu_directory_category_data( $atts['category'] );
		$item     = $this->get_menu_directory_item_data( $atts['category'], $atts['item'] );

		if ( ! is_array( $category ) || ! is_array( $item ) ) {
			return '';
		}

		$guide_url   = $this->get_menu_category_primary_guide_url( $category['id'] );
		$guide_title = $this->get_menu_category_primary_guide_title( $category['id'] );

		$related_items = array_values(
			array_filter(
				$category['items'],
				static function ( $candidate ) use ( $item ) {
					return isset( $candidate['slug'], $item['slug'] ) && $candidate['slug'] !== $item['slug'];
				}
			)
		);
		$related_items = array_slice( $related_items, 0, 3 );

		ob_start();
		?>
		<div class="mcprices-page mcprices-directory-page mcprices-item-page">
			<section class="featured mcprices-directory-section">
				<div class="container">
					<div class="mcprices-directory-crumbs">
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a>
						<span>&rsaquo;</span>
						<a href="<?php echo esc_url( $this->get_menu_directory_root_url() ); ?>">Menu</a>
						<span>&rsaquo;</span>
						<a href="<?php echo esc_url( $this->get_menu_category_page_url( $category['id'] ) ); ?>"><?php echo esc_html( $category['card_title'] ); ?></a>
						<span>&rsaquo;</span>
						<span><?php echo esc_html( $item['name'] ); ?></span>
					</div>
					<div class="section-header section-header-left">
						<div class="section-label">Item Page</div>
						<h2 class="section-title"><?php echo esc_html( $item['name'] ); ?> Price USA</h2>
						<p class="section-sub"><?php echo esc_html( $item['summary'] ); ?></p>
					</div>
					<div class="mcprices-item-layout">
						<div class="menu-card mcprices-item-card">
							<div class="card-img">
								<?php if ( $this->get_menu_directory_item_image( $item ) ) : ?>
									<img class="mcprices-card-media" src="<?php echo esc_url( $this->get_menu_directory_item_image( $item ) ); ?>" alt="<?php echo esc_attr( 'McDonald\'s ' . $item['name'] . ' price USA 2026' ); ?>" loading="lazy" decoding="async">
								<?php endif; ?>
							</div>
							<div class="card-body">
								<div class="card-top">
									<div>
										<div class="card-name"><?php echo esc_html( $item['name'] ); ?></div>
										<div class="card-cal"><?php echo esc_html( $category['card_title'] ); ?></div>
									</div>
									<div class="card-price"><?php echo esc_html( $item['price'] ? $item['price'] : 'Varies' ); ?></div>
								</div>
								<div class="mcprices-item-facts">
									<div class="mcprices-item-fact">
										<span class="mcprices-item-fact-label">Calories</span>
										<strong><?php echo esc_html( $item['calories'] ? $item['calories'] : 'Varies' ); ?></strong>
									</div>
									<div class="mcprices-item-fact">
										<span class="mcprices-item-fact-label">Status</span>
										<strong><?php echo esc_html( $item['status'] ); ?></strong>
									</div>
									<div class="mcprices-item-fact">
										<span class="mcprices-item-fact-label">Category</span>
										<strong><?php echo esc_html( $category['card_title'] ); ?></strong>
									</div>
								</div>
								<div class="card-footer">
									<a class="btn-card" href="<?php echo esc_url( $this->get_menu_category_page_url( $category['id'] ) ); ?>">Back to <?php echo esc_html( $category['card_title'] ); ?></a>
									<a class="btn-card" href="<?php echo esc_url( home_url( '/#full-menu' ) ); ?>">View full menu</a>
								</div>
							</div>
						</div>
						<div class="mcprices-item-notes">
							<div class="sidebar-widget mcprices-item-notes-card">
								<h3 class="sidebar-widget-title">Quick Notes</h3>
								<p>This dedicated page keeps the current price, calorie reference, and category context for <strong><?php echo esc_html( $item['name'] ); ?></strong> inside your native WordPress menu structure.</p>
								<p>Final pricing can still vary by restaurant, location, app offer, combo selection, delivery platform, and taxes.</p>
								<?php if ( $guide_url && $guide_title ) : ?>
									<p>For broader comparison, read the <a href="<?php echo esc_url( $guide_url ); ?>"><?php echo esc_html( $guide_title ); ?></a> page before returning to this item.</p>
								<?php endif; ?>
							</div>
						</div>
					</div>
					<?php if ( ! empty( $related_items ) ) : ?>
						<div class="section-header section-header-left mcprices-related-header">
							<div class="section-label">Same Category</div>
							<h3 class="section-title">Related <?php echo esc_html( $category['card_title'] ); ?> Items</h3>
						</div>
						<div class="menu-cards-grid featured-grid mcprices-directory-cards">
							<?php foreach ( $related_items as $related_item ) : ?>
								<article class="menu-card mcprices-directory-card">
									<div class="card-img">
										<?php if ( $this->get_menu_directory_item_image( $related_item ) ) : ?>
											<img class="mcprices-card-media" src="<?php echo esc_url( $this->get_menu_directory_item_image( $related_item ) ); ?>" alt="<?php echo esc_attr( 'McDonald\'s ' . $related_item['name'] . ' price USA 2026' ); ?>" loading="lazy" decoding="async">
										<?php endif; ?>
									</div>
									<div class="card-body">
										<div class="card-top">
											<div>
												<div class="card-name"><a href="<?php echo esc_url( $this->get_menu_item_page_url( $category['id'], $related_item['slug'] ) ); ?>"><?php echo esc_html( $related_item['name'] ); ?></a></div>
												<div class="card-cal"><?php echo esc_html( $related_item['calories'] ? $related_item['calories'] : 'Calories vary' ); ?></div>
											</div>
											<div class="card-price"><?php echo esc_html( $related_item['price'] ? $related_item['price'] : 'Varies' ); ?></div>
										</div>
										<p class="mcprices-directory-copy"><?php echo esc_html( $related_item['summary'] ); ?></p>
									</div>
									<div class="card-footer">
										<a class="btn-card" href="<?php echo esc_url( $this->get_menu_item_page_url( $category['id'], $related_item['slug'] ) ); ?>">Read more</a>
									</div>
								</article>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			</section>
		</div>
		<?php

		return (string) ob_get_clean();
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
				esc_url( $this->get_menu_category_page_url( 'whats-new' ) ),
				esc_url( $this->get_menu_category_page_url( 'mcvalue' ) ),
				esc_url( $this->get_menu_category_page_url( 'breakfast' ) ),
				esc_url( $this->get_menu_category_page_url( 'burgers' ) ),
				esc_url( $this->get_menu_category_page_url( 'chickenfish' ) ),
				esc_url( $this->get_menu_category_page_url( 'nuggets' ) ),
				esc_url( $this->get_menu_category_page_url( 'snackwrap' ) ),
				esc_url( $this->get_menu_category_page_url( 'sides' ) ),
				esc_url( $this->get_menu_category_page_url( 'happymeal' ) ),
				esc_url( $this->get_menu_category_page_url( 'sweets' ) ),
				esc_url( $this->get_menu_category_page_url( 'mccafe' ) ),
				esc_url( $this->get_menu_category_page_url( 'beverages' ) ),
				esc_url( $this->get_menu_category_page_url( 'meals' ) ),
				esc_url( $this->get_menu_category_page_url( 'sauces' ) ),
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
				'<!-- wp:html --><div class="footer-col-title">Popular Guides</div><ul class="footer-links"><li><a href="%1$s">Big Mac Price USA</a></li><li><a href="%2$s">McDonald&#8217;s App Deals</a></li><li><a href="%3$s">Nutrition &amp; Allergens Guide</a></li><li><a href="%4$s">Breakfast Hours</a></li><li><a href="%5$s">Prices by State</a></li><li><a href="%6$s">Rewards Guide</a></li><li><a href="%7$s">Limited-Time Menu</a></li><li><a href="%8$s">Delivery Guide</a></li><li><a href="%9$s">Breakfast Menu</a></li><li><a href="%10$s">Burgers Menu</a></li><li><a href="%11$s">Drinks Menu</a></li><li><a href="%12$s">Deals &amp; McValue Guide</a></li></ul><!-- /wp:html -->',
				esc_url( home_url( '/big-mac-price-usa/' ) ),
				esc_url( home_url( '/mcdonalds-app-deals/' ) ),
				esc_url( home_url( '/mcdonalds-nutrition-calories-allergens/' ) ),
				esc_url( home_url( '/breakfast-hours/' ) ),
				esc_url( home_url( '/mcdonalds-prices-by-state/' ) ),
				esc_url( home_url( '/rewards-guide/' ) ),
				esc_url( home_url( '/limited-time-menu/' ) ),
				esc_url( home_url( '/delivery-guide/' ) ),
				esc_url( home_url( '/breakfast-menu/' ) ),
				esc_url( home_url( '/burgers-menu/' ) ),
				esc_url( home_url( '/beverage-menu/' ) ),
				esc_url( home_url( '/mcdonalds-deals-mcvalue-guide/' ) )
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
		$this->maybe_seed_menu_directory_pages();
		$this->maybe_seed_nav_menus( true );
		$this->maybe_seed_footer_widgets( true );
		$this->maybe_seed_front_page_content( true );
		$this->maybe_disable_legacy_custom_css();

		set_theme_mod( self::SEED_VERSION_SETTING, self::SEED_VERSION );

		// Seed the /menu/ root page content.
		McPrices_Menu_Root_Seed::maybe_seed();
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




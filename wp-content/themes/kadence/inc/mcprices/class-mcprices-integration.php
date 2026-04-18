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
	const SEED_VERSION = '1.4.0';

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
		add_filter( 'wp_robots', array( $this, 'filter_homepage_robots' ), 20 );
		add_filter( 'robots_txt', array( $this, 'filter_robots_txt' ), 20, 2 );

		add_action( 'after_setup_theme', array( $this, 'maybe_seed_native_design' ), 30 );
		add_action( 'customize_register', array( $this, 'register_customizer' ), 100 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ), 30 );
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
		return '&#9888;&#65039; <strong>Disclaimer:</strong> This is an independent, unofficial website. McPrices UK is not affiliated with, endorsed by, or connected to McDonald\'s Corporation or McDonald\'s UK Ltd in any way. All prices are sourced from publicly available menus and may vary by location and date. This site may contain advertisements.';
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
		return '<p>&#9989; Prices last verified: <strong>April 2026</strong> &mdash; <a href="' . esc_url( $this->get_section_url( 'full-menu' ) ) . '">View full price list &darr;</a></p>';
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
	 * Return the default footer widget block markup.
	 *
	 * @return array
	 */
	protected function get_default_footer_widget_blocks() {
		return array(
			'footer1' => sprintf(
				'<!-- wp:html --><div class="footer-brand"><a href="%1$s" class="logo"><div class="logo-icon">M</div><div class="logo-text">McPrices UK<span>Independent Price Guide</span></div></a><p>An independent website providing up-to-date McDonald&#8217;s UK menu prices, calories, and deals. Not affiliated with McDonald&#8217;s Corporation or McDonald&#8217;s UK Ltd.</p></div><!-- /wp:html -->',
				esc_url( $this->get_home_path_url() )
			),
			'footer2' => sprintf(
				'<!-- wp:html --><div class="footer-col-title">Menu Categories</div><ul class="footer-links"><li><a href="%1$s">What&#8217;s New 2026</a></li><li><a href="%2$s">Burger Prices</a></li><li><a href="%3$s">Saver Menu</a></li><li><a href="%4$s">Breakfast Prices</a></li><li><a href="%5$s">McCaf&#233; Prices</a></li><li><a href="%6$s">Desserts &amp; McFlurry</a></li><li><a href="%7$s">Happy Meal</a></li><li><a href="%8$s">Wraps &amp; Salads</a></li><li><a href="%9$s">Sharers &amp; Bundles</a></li><li><a href="%10$s">Condiments &amp; Sauces</a></li><li><a href="%11$s">Breakfast Saver</a></li></ul><!-- /wp:html -->',
				esc_url( $this->get_section_url( 'whats-new' ) ),
				esc_url( $this->get_section_url( 'burgers' ) ),
				esc_url( $this->get_section_url( 'saver' ) ),
				esc_url( $this->get_section_url( 'breakfast' ) ),
				esc_url( $this->get_section_url( 'mccafe' ) ),
				esc_url( $this->get_section_url( 'desserts' ) ),
				esc_url( $this->get_section_url( 'happymeal' ) ),
				esc_url( $this->get_section_url( 'wraps' ) ),
				esc_url( $this->get_section_url( 'sharers' ) ),
				esc_url( $this->get_section_url( 'sauces' ) ),
				esc_url( $this->get_section_url( 'bsaver' ) )
			),
			'footer3' => '<!-- wp:html --><div class="footer-col-title">Information</div><ul class="footer-links"><li><a href="#">About Us</a></li><li><a href="#">Privacy Policy</a></li><li><a href="#">Cookie Policy</a></li><li><a href="#">Ad Disclosure</a></li><li><a href="#">Disclaimer</a></li><li><a href="#">Contact</a></li><li><a href="#">Sitemap</a></li></ul><!-- /wp:html -->',
			'footer4' => sprintf(
				'<!-- wp:html --><div class="footer-col-title">Popular Guides</div><ul class="footer-links"><li><a href="#">Big Mac Price UK</a></li><li><a href="#">McDonald&#8217;s App Deals</a></li><li><a href="#">Calorie Counter</a></li><li><a href="#">Breakfast Times</a></li><li><a href="#">Allergen Guide</a></li><li><a href="#">Price History</a></li><li><a href="%1$s">Vegan Options</a></li><li><a href="#">Limited-Time Menu</a></li><li><a href="#">McDelivery Guide</a></li><li><a href="%2$s">Desserts &amp; McFlurry</a></li></ul><!-- /wp:html -->',
				esc_url( $this->get_section_url( 'vegan' ) ),
				esc_url( $this->get_section_url( 'desserts' ) )
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
				'title' => __( 'Sharers', 'kadence' ),
				'url'   => $this->get_section_url( 'sharers' ),
			),
			array(
				'title' => __( 'Deals', 'kadence' ),
				'url'   => $this->get_section_url( 'deals' ),
			),
			array(
				'title' => __( 'Guides', 'kadence' ),
				'url'   => $this->get_section_url( 'blog' ),
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
				'url'   => '#',
			),
			array(
				'title' => __( 'Cookies', 'kadence' ),
				'url'   => '#',
			),
			array(
				'title' => __( 'Disclaimer', 'kadence' ),
				'url'   => '#',
			),
			array(
				'title' => __( 'Ad Policy', 'kadence' ),
				'url'   => '#',
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
		$defaults['footer_html_content']      = '<p>&copy; {year} McPrices UK. Independent price guide. Not affiliated with McDonald&#8217;s.</p>';

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
		return "McDonald's Menu Prices UK 2026";
	}

	/**
	 * Return the homepage SEO title.
	 *
	 * @return string
	 */
	protected function get_homepage_meta_title() {
		return $this->get_homepage_primary_keyword() . ' - Updated April 2026';
	}

	/**
	 * Return the homepage SEO meta description.
	 *
	 * @return string
	 */
	protected function get_homepage_meta_description() {
		return "McDonald's Menu Prices UK 2026, updated April 2026 with the full UK menu, current prices, calories, breakfast times, deals, delivery tips, FAQs and value picks.";
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

		if ( false !== strpos( $html, '[mcprices_hero_search]' ) ) {
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
		static $faq_items = null;

		if ( null !== $faq_items ) {
			return $faq_items;
		}

		$faq_items = array();
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

		return $faq_items;
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
		$front_page_id = (int) get_option( 'page_on_front' );
		if ( $front_page_id ) {
			$modified = get_post_modified_time( 'c', true, $front_page_id );
			if ( $modified ) {
				return $modified;
			}
		}

		return gmdate( 'c' );
	}

	/**
	 * Return the site logo URL when available.
	 *
	 * @return string
	 */
	protected function get_site_logo_url() {
		$logo_id = (int) get_theme_mod( 'custom_logo' );
		if ( ! $logo_id ) {
			return '';
		}

		$logo_url = wp_get_attachment_image_url( $logo_id, 'full' );

		return $logo_url ? (string) $logo_url : '';
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
		$sitemap_url = home_url( '/sitemap.xml' );
		?>
		<meta name="description" content="<?php echo esc_attr( $description ); ?>">
		<link rel="canonical" href="<?php echo esc_url( $url ); ?>">
		<link rel="sitemap" type="application/xml" title="<?php esc_attr_e( 'Sitemap', 'kadence' ); ?>" href="<?php echo esc_url( $sitemap_url ); ?>">
		<meta property="og:locale" content="en_GB">
		<meta property="og:type" content="website">
		<meta property="og:title" content="<?php echo esc_attr( $title ); ?>">
		<meta property="og:description" content="<?php echo esc_attr( $description ); ?>">
		<meta property="og:url" content="<?php echo esc_url( $url ); ?>">
		<meta property="og:site_name" content="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
		<meta name="twitter:card" content="summary_large_image">
		<meta name="twitter:title" content="<?php echo esc_attr( $title ); ?>">
		<meta name="twitter:description" content="<?php echo esc_attr( $description ); ?>">
		<?php
	}

	/**
	 * Output homepage JSON-LD for FAQPage, Product/Offer, and site context.
	 *
	 * @return void
	 */
	public function render_homepage_schema() {
		if ( ! $this->is_seo_homepage() ) {
			return;
		}

		$url         = home_url( '/' );
		$title       = $this->get_homepage_meta_title();
		$description = $this->get_homepage_meta_description();
		$logo_url    = $this->get_site_logo_url();
		$faq_items   = $this->get_homepage_faq_items();
		$products    = $this->get_homepage_popular_products();
		$graph       = array();

		$graph[] = array_filter(
			array(
				'@type'       => 'Organization',
				'@id'         => $url . '#organization',
				'name'        => get_bloginfo( 'name' ),
				'url'         => $url,
				'description' => $description,
				'logo'        => $logo_url ? array(
					'@type' => 'ImageObject',
					'url'   => $logo_url,
				) : null,
			)
		);

		$graph[] = array(
			'@type'           => 'WebSite',
			'@id'             => $url . '#website',
			'url'             => $url,
			'name'            => $title,
			'description'     => $description,
			'inLanguage'      => 'en-GB',
			'publisher'       => array( '@id' => $url . '#organization' ),
			'potentialAction' => array(
				'@type'       => 'SearchAction',
				'target'      => home_url( '/?s={search_term_string}' ),
				'query-input' => 'required name=search_term_string',
			),
		);

		$graph[] = array(
			'@type'        => 'CollectionPage',
			'@id'          => $url . '#webpage',
			'url'          => $url,
			'name'         => $title,
			'description'  => $description,
			'inLanguage'   => 'en-GB',
			'isPartOf'     => array( '@id' => $url . '#website' ),
			'about'        => array(
				$this->get_homepage_primary_keyword(),
				"McDonald's UK calories",
				"McDonald's UK breakfast times",
				"McDonald's UK deals",
			),
			'dateModified' => $this->get_homepage_modified_date(),
		);

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
							'%1$s is a popular McDonald\'s UK menu item in the April 2026 update.%2$s',
							$product['name'],
							$product['category'] ? ' Category: ' . $product['category'] . '.' : ''
						),
						'brand'              => array(
							'@type' => 'Brand',
							'name'  => "McDonald's UK",
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
							'priceCurrency' => 'GBP',
							'price'         => $product['price'],
							'availability'  => 'https://schema.org/InStock',
							'url'           => $this->get_section_url( 'full-menu' ),
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
				'name'            => 'Popular McDonald\'s UK Menu Items',
				'itemListElement' => $item_list,
			);
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

		$homepage_pattern = require get_theme_file_path( '/inc/mcprices/pattern-homepage.php' );
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
	}

	/**
	 * Seed a native WordPress posts page for the Blogs menu when none exists yet.
	 *
	 * @return void
	 */
	protected function maybe_seed_blog_page() {
		$front_page_id = (int) get_option( 'page_on_front' );
		$posts_page_id = (int) get_option( 'page_for_posts' );
		if ( $posts_page_id && $posts_page_id !== $front_page_id ) {
			return;
		}

		$blog_page = get_page_by_path( 'blog' );
		if ( ! $blog_page instanceof \WP_Post ) {
			$blog_page = get_page_by_path( 'blogs' );
		}

		if ( ! $blog_page instanceof \WP_Post ) {
			$blog_page_id = wp_insert_post(
				array(
					'post_title'  => 'Blogs',
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
			false !== strpos( $custom_css, 'McPrices UK' ) &&
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




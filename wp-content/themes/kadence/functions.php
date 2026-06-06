<?php
/**
 * Kadence functions and definitions
 *
 * This file must be parseable by PHP 5.2.
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package kadence
 */

define( 'KADENCE_VERSION', '1.4.5' );
define( 'KADENCE_MINIMUM_WP_VERSION', '6.0' );
define( 'KADENCE_MINIMUM_PHP_VERSION', '7.4' );

// Bail if requirements are not met.
if ( version_compare( $GLOBALS['wp_version'], KADENCE_MINIMUM_WP_VERSION, '<' ) || version_compare( phpversion(), KADENCE_MINIMUM_PHP_VERSION, '<' ) ) {
	require get_template_directory() . '/inc/back-compat.php';
	return;
}
// Include WordPress shims.
require get_template_directory() . '/inc/wordpress-shims.php';

// Load the `kadence()` entry point function.
require get_template_directory() . '/inc/class-theme.php';

// Load the `kadence()` entry point function.
require get_template_directory() . '/inc/functions.php';

// Native McPrices integration for this Kadence parent theme install.
require get_template_directory() . '/inc/mcprices/class-mcprices-integration.php';
require get_template_directory() . '/inc/sitemap.php';

// Initialize the theme.
call_user_func( 'Kadence\kadence' );

// Bootstrap the native McPrices layer after the theme is loaded.
\Kadence\McPrices_Integration::get_instance();

/**
 * Return whether a dedicated SEO plugin should own meta tags.
 *
 * @return bool
 */
function kadence_mcprices_has_active_seo_plugin() {
	return defined( 'WPSEO_VERSION' ) || class_exists( 'WPSEO_Frontend' ) || defined( 'RANK_MATH_VERSION' ) || class_exists( '\RankMath\Helper' );
}

/**
 * Return the current site-local date string for McPrices.
 *
 * @param string $format PHP date format.
 * @return string
 */
function kadence_mcprices_get_current_site_date( $format = 'd-m-Y' ) {
	return wp_date( $format, null, wp_timezone() );
}

/**
 * Return the current site-local year for McPrices.
 *
 * @return string
 */
function kadence_mcprices_get_current_site_year() {
	return kadence_mcprices_get_current_site_date( 'Y' );
}

/**
 * Replace hardcoded day/month date strings with the current site-local date.
 *
 * @param string $text Source text.
 * @return string
 */
function kadence_mcprices_replace_dynamic_dates( $text ) {
	if ( ! is_string( $text ) || '' === trim( $text ) ) {
		return is_string( $text ) ? $text : '';
	}

	$updated = preg_replace(
		'/\b\d{1,2}[\/-]\d{1,2}[\/-](?:19|20)?\d{2}\b/u',
		kadence_mcprices_get_current_site_date(),
		$text
	);

	return is_string( $updated ) ? $updated : $text;
}

/**
 * Build the dynamic meta title for the current request.
 *
 * @return string
 */
function kadence_mcprices_get_dynamic_meta_title() {
	$current_year = kadence_mcprices_get_current_site_year();
	$queried_object = get_queried_object();

	if ( is_front_page() && ! is_home() ) {
		return "McDonald's Menu Prices USA {$current_year} | Prices, Calories & Deals";
	}

	if ( $queried_object instanceof \WP_Post ) {
		$rank_math_title = trim( (string) get_post_meta( (int) $queried_object->ID, 'rank_math_title', true ) );

		if ( '' !== $rank_math_title ) {
			return kadence_mcprices_replace_dynamic_dates( trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( $rank_math_title ) ) ) );
		}
	}

	if ( $queried_object instanceof \WP_Term ) {
		$rank_math_title = trim( (string) get_term_meta( (int) $queried_object->term_id, 'rank_math_title', true ) );

		if ( '' !== $rank_math_title ) {
			return kadence_mcprices_replace_dynamic_dates( trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( $rank_math_title ) ) ) );
		}
	}

	if ( is_singular( 'post' ) ) {
		return single_post_title( '', false ) . " | McDonald's Menu Prices USA";
	}

	if ( is_category() || is_tag() || is_tax() || is_post_type_archive() || is_author() || is_date() ) {
		$archive_label = single_term_title( '', false );

		if ( '' === $archive_label && is_post_type_archive() ) {
			$archive_label = post_type_archive_title( '', false );
		}

		if ( '' === $archive_label ) {
			$archive_label = wp_strip_all_tags( get_the_archive_title() );
		}

		if ( '' !== $archive_label ) {
			return $archive_label . ' Prices USA ' . $current_year . " | McDonald's Menu Prices USA";
		}
	}

	if ( is_404() ) {
		return "Page Not Found | McDonald's Menu Prices USA";
	}

	if ( is_page() ) {
		return single_post_title( '', false ) . " | McDonald's Menu Prices USA";
	}

	return '';
}

/**
 * Build the dynamic meta description for the current request.
 *
 * @return string
 */
function kadence_mcprices_get_dynamic_meta_description() {
	$current_date = kadence_mcprices_get_current_site_date();
	$current_year = kadence_mcprices_get_current_site_year();
	$queried_object = get_queried_object();

	if ( $queried_object instanceof \WP_Post ) {
		$rank_math_description = trim( (string) get_post_meta( (int) $queried_object->ID, 'rank_math_description', true ) );

		if ( '' !== $rank_math_description ) {
			return kadence_mcprices_replace_dynamic_dates( trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( $rank_math_description ) ) ) );
		}
	}

	if ( $queried_object instanceof \WP_Term ) {
		$rank_math_description = trim( (string) get_term_meta( (int) $queried_object->term_id, 'rank_math_description', true ) );

		if ( '' !== $rank_math_description ) {
			return kadence_mcprices_replace_dynamic_dates( trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( $rank_math_description ) ) ) );
		}
	}

	if ( is_front_page() && ! is_home() ) {
		return "Compare McDonald's menu prices in the USA, including breakfast, burgers, Happy Meal prices, small drink prices, McCafe, McValue deals, calories, and local price notes.";
	}

	if ( is_singular( 'post' ) ) {
		$post = get_queried_object();

		if ( $post instanceof \WP_Post ) {
			$excerpt = has_excerpt( $post ) ? $post->post_excerpt : wp_trim_words( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ), 28, '' );
			$excerpt = trim( preg_replace( '/\s+/u', ' ', $excerpt ) );

			if ( '' !== $excerpt ) {
				return $excerpt;
			}

			return sprintf( "Read %s on McDonald's Menu Prices USA for current McDonald's USA menu prices, calories, deals, and guides.", get_the_title( $post ) );
		}
	}

	if ( is_category() || is_tag() || is_tax() || is_post_type_archive() || is_author() || is_date() ) {
		$archive_label = single_term_title( '', false );

		if ( '' === $archive_label && is_post_type_archive() ) {
			$archive_label = post_type_archive_title( '', false );
		}

		if ( '' === $archive_label ) {
			$archive_label = wp_strip_all_tags( get_the_archive_title() );
		}

		if ( '' !== $archive_label ) {
			return sprintf( "Browse %s prices, deals, calories, and McDonald's USA menu updates for %s on McDonald's Menu Prices USA.", $archive_label, $current_year );
		}
	}

	if ( is_404() ) {
		return "The page you were looking for could not be found. Explore the latest McDonald's USA menu prices, calories, deals, and guides on McDonald's Menu Prices USA.";
	}

	if ( is_page() ) {
		$page = get_queried_object();

		if ( $page instanceof \WP_Post ) {
			$excerpt = has_excerpt( $page ) ? $page->post_excerpt : wp_trim_words( wp_strip_all_tags( strip_shortcodes( $page->post_content ) ), 28, '' );
			$excerpt = trim( preg_replace( '/\s+/u', ' ', $excerpt ) );

			if ( '' !== $excerpt ) {
				return $excerpt;
			}
		}
	}

	return trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( get_bloginfo( 'description' ) ) ) );
}

/**
 * Filter the document title when no dedicated SEO plugin is active.
 *
 * @param string $title Current title.
 * @return string
 */
function kadence_mcprices_filter_document_title( $title ) {
	if ( kadence_mcprices_has_active_seo_plugin() ) {
		return $title;
	}

	$dynamic_title = kadence_mcprices_get_dynamic_meta_title();

	return $dynamic_title ? $dynamic_title : $title;
}

/**
 * Output a dynamic meta description in wp_head when no SEO plugin is active.
 *
 * @return void
 */
function kadence_mcprices_output_dynamic_meta_tags() {
	if ( kadence_mcprices_has_active_seo_plugin() ) {
		return;
	}

	$description = kadence_mcprices_get_dynamic_meta_description();

	if ( '' === $description ) {
		return;
	}
	?>
	<meta name="description" content="<?php echo esc_attr( $description ); ?>">
	<?php
}

/**
 * Build the canonical URL for the current request when no SEO plugin is active.
 *
 * @return string
 */
function kadence_mcprices_get_canonical_url() {
	$page_number = max( 1, absint( get_query_var( 'paged' ) ), absint( get_query_var( 'page' ) ) );

	if ( is_front_page() ) {
		if ( $page_number > 1 ) {
			return get_pagenum_link( $page_number );
		}

		return home_url( '/' );
	}

	if ( is_singular() ) {
		$permalink = get_permalink();

		if ( ! $permalink ) {
			return '';
		}

		if ( $page_number > 1 ) {
			global $wp_rewrite;

			if ( $wp_rewrite->using_permalinks() ) {
				return user_trailingslashit( trailingslashit( $permalink ) . $page_number, 'single_paged' );
			}

			return add_query_arg( 'page', $page_number, $permalink );
		}

		return $permalink;
	}

	if ( ( is_category() || is_tag() || is_tax() ) && $page_number > 1 ) {
		return get_pagenum_link( $page_number );
	}

	if ( is_category() || is_tag() || is_tax() ) {
		$term = get_queried_object();

		if ( ! ( $term instanceof \WP_Term ) ) {
			return '';
		}

		$term_link = get_term_link( $term );

		return is_wp_error( $term_link ) ? '' : $term_link;
	}

	if ( is_paged() && $page_number > 1 ) {
		return get_pagenum_link( $page_number );
	}

	return '';
}

/**
 * Output a canonical tag in wp_head when no SEO plugin is active.
 *
 * @return void
 */
function kadence_mcprices_output_canonical_tag() {
	if ( kadence_mcprices_has_active_seo_plugin() ) {
		return;
	}

	$canonical_url = kadence_mcprices_get_canonical_url();

	if ( '' === $canonical_url ) {
		return;
	}

	printf( "<link rel=\"canonical\" href=\"%s\" />\n", esc_url( $canonical_url ) );
}

/**
 * Filter Rank Math's generated title using the shared theme logic.
 *
 * @param string $title Rank Math title.
 * @return string
 */
function kadence_mcprices_filter_rank_math_title( $title ) {
	$dynamic_title = kadence_mcprices_get_dynamic_meta_title();

	return kadence_mcprices_replace_dynamic_dates( $dynamic_title ? $dynamic_title : $title );
}

/**
 * Filter Rank Math's generated description using the shared theme logic.
 *
 * @param string $description Rank Math description.
 * @return string
 */
function kadence_mcprices_filter_rank_math_description( $description ) {
	$dynamic_description = kadence_mcprices_get_dynamic_meta_description();

	return kadence_mcprices_replace_dynamic_dates( $dynamic_description ? $dynamic_description : $description );
}

/**
 * Normalize old internal site URLs to the currently installed WordPress home.
 *
 * @param string $url URL to normalize.
 * @return string
 */
function kadence_mcprices_normalize_runtime_url( $url ) {
	if ( ! is_string( $url ) || '' === trim( $url ) ) {
		return is_string( $url ) ? $url : '';
	}

	$current_home = untrailingslashit( home_url() );

	return str_replace(
		array(
			'http://localhost/wordpress',
			'http://127.0.0.1/wordpress',
			'https://mcdomenuusa.com',
			'https://www.mcdomenuusa.com',
		),
		array(
			$current_home,
			$current_home,
			$current_home,
			$current_home,
		),
		$url
	);
}

/**
 * Return the current singular page path relative to the WordPress home URL.
 *
 * @return string
 */
function kadence_mcprices_get_current_relative_page_path() {
	if ( ! is_singular( 'page' ) ) {
		return '';
	}

	$permalink = get_permalink();

	if ( ! is_string( $permalink ) || '' === $permalink ) {
		return '';
	}

	$path      = trim( (string) wp_parse_url( $permalink, PHP_URL_PATH ), '/' );
	$home_path = trim( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ), '/' );

	if ( '' !== $home_path && 0 === strpos( $path, $home_path . '/' ) ) {
		$path = substr( $path, strlen( $home_path ) + 1 );
	} elseif ( $path === $home_path ) {
		$path = '';
	}

	return trim( (string) $path, '/' );
}

/**
 * Return category/navigation pages that should canonicalize to guide pages.
 *
 * @return array<string, string>
 */
function kadence_mcprices_get_authority_guide_canonical_path_map() {
	return array(
		'menu'                    => '',
		'menu/whats-new'         => 'limited-time-menu',
		'menu/extra-value-meals' => 'extra-value-meals',
		'menu/mcvalue-menu'      => 'mcdonalds-deals-mcvalue-guide',
		'menu/breakfast-menu'    => 'breakfast-menu',
		'menu/burgers-menu'      => 'burgers-menu',
		'menu/chicken-fish'      => 'chicken-fish-menu',
		'menu/mcnuggets-strips'  => 'nuggets-and-strips',
		'menu/snack-wrap'        => 'snack-wrap',
		'menu/fries-sides'       => 'fries-sides',
		'menu/happy-meal'        => 'happy-meal-menu',
		'menu/sweets-treats'     => 'sweets-treats',
		'mccafe-menu'            => 'menu/mccafe-coffees',
		'beverage-menu'          => 'menu/beverages-drinks',
		'menu/sauces-condiments' => 'sauces-condiments',
		'menu/deals-and-offers'  => 'mcdonalds-deals-mcvalue-guide',
	);
}

/**
 * Keep Rank Math canonical URLs portable between localhost and live.
 *
 * @param string $canonical Canonical URL.
 * @return string
 */
function kadence_mcprices_filter_rank_math_canonical( $canonical ) {
	$canonical = kadence_mcprices_normalize_runtime_url( $canonical );
	$path      = kadence_mcprices_get_current_relative_page_path();
	$map       = kadence_mcprices_get_authority_guide_canonical_path_map();

	if ( '' !== $path && isset( $map[ $path ] ) ) {
		$target_path = trim( (string) $map[ $path ], '/' );

		return '' === $target_path ? home_url( '/' ) : home_url( '/' . $target_path . '/' );
	}

	return $canonical;
}

/**
 * Return the default social image URL for the current request.
 *
 * @return string
 */
function kadence_mcprices_get_default_social_image_url() {
	$post_id = 0;

	if ( is_singular() ) {
		$post_id = get_queried_object_id();
	} elseif ( is_home() ) {
		$post_id = (int) get_option( 'page_for_posts' );
	}

	if ( $post_id ) {
		$featured_image = get_the_post_thumbnail_url( $post_id, 'full' );

		if ( $featured_image ) {
			return esc_url_raw( $featured_image );
		}
	}

	return esc_url_raw( get_template_directory_uri() . '/assets/images/mcprices/official/items/big-mac.jpg' );
}

/**
 * Return the default favicon URL for the McPrices native theme layer.
 *
 * @return string
 */
function kadence_mcprices_get_default_favicon_url() {
	return esc_url_raw( get_template_directory_uri() . '/assets/images/mcprices/mcprices-logo-schema.svg' );
}

/**
 * Output a favicon fallback when the WordPress Site Icon is not configured.
 *
 * @return void
 */
function kadence_mcprices_output_favicon_fallback() {
	if ( function_exists( 'has_site_icon' ) && has_site_icon() ) {
		return;
	}

	$favicon_url = kadence_mcprices_get_default_favicon_url();

	if ( '' === $favicon_url ) {
		return;
	}
	?>
	<link rel="icon" href="<?php echo esc_url( $favicon_url ); ?>" type="image/svg+xml" />
	<link rel="shortcut icon" href="<?php echo esc_url( $favicon_url ); ?>" type="image/svg+xml" />
	<?php
}

/**
 * Provide a default Open Graph image for Rank Math when none is set.
 *
 * @param string $image Open Graph image URL.
 * @return string
 */
function kadence_mcprices_filter_rank_math_facebook_image( $image ) {
	if ( ! empty( $image ) ) {
		return $image;
	}

	return kadence_mcprices_get_default_social_image_url();
}

/**
 * Replace the old homepage-only meta handlers with the shared theme-level ones.
 *
 * @return void
 */
function kadence_mcprices_register_dynamic_meta_hooks() {
	if ( class_exists( '\Kadence\McPrices_Integration' ) ) {
		$mcprices_integration = \Kadence\McPrices_Integration::get_instance();

		remove_filter( 'pre_get_document_title', array( $mcprices_integration, 'filter_document_title' ), 20 );
		remove_action( 'wp_head', array( $mcprices_integration, 'render_homepage_meta_tags' ), 2 );
	}

	remove_action( 'wp_head', 'rel_canonical' );
	add_filter( 'pre_get_document_title', 'kadence_mcprices_filter_document_title', 20 );
	add_action( 'wp_head', 'kadence_mcprices_output_dynamic_meta_tags', 2 );
	add_action( 'wp_head', 'kadence_mcprices_output_canonical_tag', 3 );
	add_filter( 'rank_math/frontend/title', 'kadence_mcprices_filter_rank_math_title', 20 );
	add_filter( 'rank_math/frontend/description', 'kadence_mcprices_filter_rank_math_description', 20 );
	add_filter( 'rank_math/frontend/canonical', 'kadence_mcprices_filter_rank_math_canonical', 20 );
	add_filter( 'rank_math/opengraph/facebook/image', 'kadence_mcprices_filter_rank_math_facebook_image' );
	add_filter( 'rank_math/sitemap/exlude_posts_with_canonical_urls', '__return_true' );
	add_filter( 'rank_math/sitemap/enable_caching', '__return_false' );
}
add_action( 'after_setup_theme', 'kadence_mcprices_register_dynamic_meta_hooks', 60 );
add_action( 'wp_head', 'kadence_mcprices_output_favicon_fallback', 1 );
add_action( 'login_head', 'kadence_mcprices_output_favicon_fallback', 1 );
add_action( 'admin_head', 'kadence_mcprices_output_favicon_fallback', 1 );

/**
 * Return a site-relative path for a post ID.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function kadence_mcprices_get_relative_page_path_for_post_id( $post_id ) {
	$post_id = absint( $post_id );

	if ( ! $post_id ) {
		return '';
	}

	$permalink = get_permalink( $post_id );

	if ( ! is_string( $permalink ) || '' === $permalink ) {
		return '';
	}

	$path      = trim( (string) wp_parse_url( $permalink, PHP_URL_PATH ), '/' );
	$home_path = trim( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ), '/' );

	if ( '' !== $home_path && 0 === strpos( $path, $home_path . '/' ) ) {
		$path = substr( $path, strlen( $home_path ) + 1 );
	} elseif ( $path === $home_path ) {
		$path = '';
	}

	return trim( (string) $path, '/' );
}

/**
 * Return the current priority SEO page path.
 *
 * @return string
 */
function kadence_mcprices_get_priority_request_path() {
	$request_uri  = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
	$request_path = trim( (string) wp_parse_url( $request_uri, PHP_URL_PATH ), '/' );
	$home_path    = trim( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ), '/' );

	if ( '' !== $home_path && 0 === strpos( $request_path, $home_path . '/' ) ) {
		$request_path = substr( $request_path, strlen( $home_path ) + 1 );
	} elseif ( $request_path === $home_path ) {
		$request_path = '';
	}

	if ( '' === trim( (string) $request_path, '/' ) || is_front_page() ) {
		return '';
	}

	if ( is_singular( 'page' ) ) {
		return kadence_mcprices_get_relative_page_path_for_post_id( get_queried_object_id() );
	}

	return '';
}

/**
 * Return explicit priority-page SEO titles from the Search Console action plan.
 *
 * @param string $path Site-relative path.
 * @return string
 */
function kadence_mcprices_get_priority_page_title_for_path( $path ) {
	$path  = trim( (string) $path, '/' );
	$year  = kadence_mcprices_get_current_site_year();
	$month = kadence_mcprices_get_current_site_date( 'F Y' );

	switch ( $path ) {
		case '':
		case 'home':
		case 'homepage':
			return "McDonald's Menu Prices USA {$year} | Prices, Calories & Deals";
		case 'happy-meal-menu':
			return "McDonald's Happy Meal Price ({$year})";
		case 'menu/beverages-drinks/soft-drink-small':
			return "How Much Is a Small Drink at McDonald's? ({$year})";
		case 'menu/beverages-drinks':
			return "McDonald's Beverages & Drinks Menu Prices USA {$year}";
		case 'breakfast-menu':
			return "McDonald's Breakfast Menu Prices USA {$year} | Calories & Hours";
		case 'burgers-menu':
			return "McDonald's Burgers Menu Prices in USA {$year}";
		case 'menu/mccafe-coffees':
			return html_entity_decode( "McDonald's McCaf&eacute; Menu Prices ({$year})", ENT_QUOTES, 'UTF-8' );
		case 'mcdonalds-app-deals':
			return "McDonald's App Deals ({$month}) | Offers & Rewards";
		default:
			return '';
	}
}

/**
 * Return explicit priority-page meta descriptions.
 *
 * @param string $path Site-relative path.
 * @return string
 */
function kadence_mcprices_get_priority_page_description_for_path( $path ) {
	$path  = trim( (string) $path, '/' );
	$month = kadence_mcprices_get_current_site_date( 'F Y' );

	switch ( $path ) {
		case '':
		case 'home':
		case 'homepage':
			return "Compare McDonald's menu prices in the USA, including breakfast, burgers, Happy Meal prices, small drink prices, McCafe, McValue deals, calories, and local price notes.";
		case 'happy-meal-menu':
			return "Review McDonald's Happy Meal prices, calories, kids meal choices, Hamburger Happy Meal, McNuggets Happy Meal, sides, drinks, and toys.";
		case 'menu/beverages-drinks/soft-drink-small':
			return "See the current tracked McDonald's small drink price, soft drink calories, fountain drink options, size comparison, and USA ordering notes.";
		case 'menu/beverages-drinks':
			return "Compare McDonald's drinks menu prices by size, including small drinks, medium drinks, large soft drinks, tea, lemonade, smoothies, juice, milk, and water.";
		case 'menu/mccafe-coffees':
			return "Compare McDonald's McCafe menu prices, coffee, iced coffee, lattes, frappes, hot chocolate, calories, sizes, and current USA item pages.";
		case 'mcdonalds-app-deals':
			return "Review current McDonald's app deals for {$month}, rewards, digital offers, McValue promos, delivery notes, and ways to compare savings against menu prices.";
		default:
			return '';
	}
}

/**
 * Force priority titles through SEO plugin and theme title paths.
 *
 * @param string $title Current title.
 * @return string
 */
function kadence_mcprices_filter_priority_title( $title ) {
	$priority_title = kadence_mcprices_get_priority_page_title_for_path( kadence_mcprices_get_priority_request_path() );

	return '' !== $priority_title ? $priority_title : $title;
}

/**
 * Force priority descriptions through SEO plugin paths.
 *
 * @param string $description Current description.
 * @return string
 */
function kadence_mcprices_filter_priority_description( $description ) {
	$priority_description = kadence_mcprices_get_priority_page_description_for_path( kadence_mcprices_get_priority_request_path() );

	return '' !== $priority_description ? $priority_description : $description;
}

/**
 * Keep visible page headings aligned with the Search Console action plan.
 *
 * @param string $title   Current post title.
 * @param int    $post_id Post ID.
 * @return string
 */
function kadence_mcprices_filter_priority_visible_title( $title, $post_id = 0 ) {
	if ( is_admin() || ! ( is_singular( 'page' ) || is_front_page() ) ) {
		return $title;
	}

	if ( absint( $post_id ) !== absint( get_queried_object_id() ) ) {
		return $title;
	}

	$priority_title = kadence_mcprices_get_priority_page_title_for_path( kadence_mcprices_get_relative_page_path_for_post_id( $post_id ) );

	return '' !== $priority_title ? $priority_title : $title;
}

/**
 * Return whether the current page should be noindexed by runtime policy.
 *
 * @return bool
 */
function kadence_mcprices_current_page_is_priority_noindex() {
	$path = kadence_mcprices_get_priority_request_path();

	return in_array( $path, array( 'test', 'ad-disclosure' ), true );
}

/**
 * Apply noindex/follow to thin or trust-support pages that should not rank.
 *
 * @param array $robots Robots directives.
 * @return array
 */
function kadence_mcprices_filter_priority_wp_robots( $robots ) {
	if ( ! kadence_mcprices_current_page_is_priority_noindex() ) {
		return $robots;
	}

	unset( $robots['index'], $robots['nofollow'] );

	$robots['noindex'] = true;
	$robots['follow']  = true;

	return $robots;
}

/**
 * Apply noindex/follow inside Rank Math's robots output.
 *
 * @param mixed $robots Robots directives.
 * @return array
 */
function kadence_mcprices_filter_priority_rank_math_robots( $robots ) {
	if ( ! kadence_mcprices_current_page_is_priority_noindex() ) {
		return $robots;
	}

	return array( 'noindex', 'follow' );
}

/**
 * Build a compact priority SEO block for the homepage.
 *
 * @return string
 */
function kadence_mcprices_get_homepage_priority_block() {
	return '<section class="mcprices-priority-seo-block" data-mcprices-priority-seo="homepage"><h2>McDonald&#8217;s Menu Prices USA Entity Statement</h2><p><strong>Entity statement:</strong> McDonald&#8217;s Menu Prices USA is a country-specific reference for U.S. McDonald&#8217;s menu prices, calories, deals, breakfast items, burgers, drinks, Happy Meals, and current ordering notes. Start with the <a href="' . esc_url( home_url( '/happy-meal-menu/' ) ) . '">Happy Meal price guide</a>, the <a href="' . esc_url( home_url( '/menu/beverages-drinks/soft-drink-small/' ) ) . '">small drink price page</a>, or the <a href="' . esc_url( home_url( '/breakfast-menu/' ) ) . '">breakfast menu prices</a> if you need the highest-priority answers first.</p></section>';
}

/**
 * Build a compact Happy Meal direct answer block.
 *
 * @return string
 */
function kadence_mcprices_get_happy_meal_priority_block() {
	return '<section class="mcprices-priority-seo-block" data-mcprices-priority-seo="happy-meal"><h2>McDonald&#8217;s Happy Meal Price (2026)</h2><p><strong>Direct answer:</strong> The tracked McDonald&#8217;s Happy Meal price range on this site is about $5.89 to $7.29 before local tax, delivery markup, or app-only changes. Hamburger Happy Meals are usually the lowest entry point, while 6-piece McNuggets Happy Meals usually sit at the higher end.</p></section>';
}

/**
 * Build the breakfast quick-price table requested by the Search Console plan.
 *
 * @return string
 */
function kadence_mcprices_get_breakfast_priority_block() {
	return '<section class="mcprices-priority-seo-block" data-mcprices-priority-seo="breakfast"><h2>McDonald&#8217;s Breakfast Prices: Quick Table</h2><p><strong>Direct breakfast price answer:</strong> The fastest breakfast checks are McMuffins, biscuits, McGriddles, hash browns, oatmeal, and breakfast meal upgrades. Use this quick table before the full guide.</p><div class="wp-block-table mcprices-seed-table"><table><thead><tr><th>Breakfast item</th><th>Tracked price</th><th>Why it matters</th></tr></thead><tbody><tr><td>Egg McMuffin</td><td>$4.79</td><td>Core breakfast sandwich benchmark</td></tr><tr><td>Sausage McMuffin</td><td>$2.49</td><td>Lowest-entry McMuffin-style sandwich</td></tr><tr><td>Bacon, Egg &amp; Cheese Biscuit</td><td>$5.59</td><td>Popular biscuit comparison item</td></tr><tr><td>Sausage McGriddles</td><td>$3.99</td><td>Sweet-savory value breakfast pick</td></tr><tr><td>Hash Browns</td><td>$2.69</td><td>Main breakfast side and combo anchor</td></tr><tr><td>Fruit &amp; Maple Oatmeal</td><td>$2.79</td><td>Lighter hot breakfast option</td></tr></tbody></table></div></section>';
}

/**
 * Build the drinks size comparison table.
 *
 * @return string
 */
function kadence_mcprices_get_beverages_priority_block() {
	return '<section class="mcprices-priority-seo-block" data-mcprices-priority-seo="beverages"><h2>McDonald&#8217;s drinks price comparison by size</h2><p><strong>Direct answer:</strong> A small McDonald&#8217;s soft drink is tracked at $1.69 in the current USA menu data. The table below compares small, medium, large, and any-size drink options before you choose a specific item page.</p><div class="wp-block-table mcprices-seed-table"><table><thead><tr><th>Drink type</th><th>Small</th><th>Medium</th><th>Large / any size</th><th>Notes</th></tr></thead><tbody><tr><td>Soft drinks</td><td>$1.69</td><td>$1.89</td><td>$2.29</td><td>Coke, Sprite, Dr Pepper, Fanta, Diet Coke, Hi-C</td></tr><tr><td>Frozen drinks</td><td>$2.49</td><td>$2.89</td><td>$3.39</td><td>Frozen Fanta and Frozen Coca-Cola style drinks</td></tr><tr><td>Smoothies</td><td>$3.99</td><td>$4.59</td><td>$5.29</td><td>Strawberry Banana and Mango Pineapple</td></tr><tr><td>Lemonade</td><td>$2.49</td><td>$2.99</td><td>$3.49</td><td>Size-led lemonade comparison</td></tr><tr><td>Sweet tea</td><td>$1.00</td><td>$1.29</td><td>$1.49</td><td>Lower-entry tea option</td></tr><tr><td>Unsweetened iced tea</td><td>$1.00</td><td>$1.00</td><td>$1.00</td><td>Any-size listing in tracked data</td></tr><tr><td>Kids drinks, milk, and water</td><td>$1.49</td><td>$1.59</td><td>$1.99</td><td>Packaged drinks rather than fountain sizes</td></tr></tbody></table></div><p>For the highest-priority exact answer, open the <a href="' . esc_url( home_url( '/menu/beverages-drinks/soft-drink-small/' ) ) . '">small drink price page</a>.</p></section>';
}

/**
 * Build the McCafe category table.
 *
 * @return string
 */
function kadence_mcprices_get_mccafe_priority_block() {
	return '<section class="mcprices-priority-seo-block" data-mcprices-priority-seo="mccafe"><h2>McDonald&#8217;s McCafe price table</h2><p>This complete McCafe price table covers the main tracked coffee, espresso, iced coffee, frappe, and hot chocolate items before you open a specific drink page.</p><div class="wp-block-table mcprices-seed-table"><table><thead><tr><th>McCafe item</th><th>Small</th><th>Medium</th><th>Large</th></tr></thead><tbody><tr><td>Premium Roast Coffee</td><td>$1.49</td><td>$1.79</td><td>$2.09</td></tr><tr><td>Americano</td><td>$2.49</td><td>$2.99</td><td>$3.49</td></tr><tr><td>Latte</td><td>$3.49</td><td>$3.99</td><td>$4.49</td></tr><tr><td>Caramel Latte</td><td>$3.69</td><td>$4.19</td><td>$4.69</td></tr><tr><td>French Vanilla Latte</td><td>$3.69</td><td>$4.19</td><td>$4.69</td></tr><tr><td>Cappuccino</td><td>$3.49</td><td>$3.99</td><td>$4.49</td></tr><tr><td>Hot Chocolate</td><td>$2.99</td><td>$3.49</td><td>$3.99</td></tr><tr><td>Iced Coffee</td><td>$3.19</td><td>$3.59</td><td>$4.19</td></tr><tr><td>Caramel Frappe</td><td>$3.79</td><td>$4.29</td><td>$4.99</td></tr><tr><td>Mocha Frappe</td><td>$3.79</td><td>$4.29</td><td>$4.99</td></tr></tbody></table></div></section>';
}

/**
 * Build the current app deals block.
 *
 * @return string
 */
function kadence_mcprices_get_app_deals_priority_block() {
	$month = kadence_mcprices_get_current_site_date( 'F Y' );

	return '<section class="mcprices-priority-seo-block" data-mcprices-priority-seo="app-deals"><h2>Current McDonald&#8217;s app deals to check in ' . esc_html( $month ) . '</h2><p><strong>Direct answer:</strong> Current McDonald&#8217;s app deals to check in ' . esc_html( $month ) . ' include the $5 McChicken Meal Deal, $5 McDouble Meal Deal, Daily Double Meal Deal, Buy 1 Add 1 for $1 breakfast/lunch offers, and Mini McFlurry picks where available in the app.</p><ul><li><strong>$5 McChicken Meal Deal:</strong> A low-cost chicken meal path when available locally.</li><li><strong>$5 McDouble Meal Deal:</strong> A beef value meal path for app and McValue comparisons.</li><li><strong>Daily Double Meal Deal:</strong> A rotating value-style meal option to compare against burgers.</li><li><strong>Buy 1 Add 1 for $1:</strong> Often split between breakfast and lunch/dinner items.</li><li><strong>Mini McFlurry picks:</strong> Useful when dessert offers appear beside meal deals.</li></ul></section>';
}

/**
 * Add priority SEO content blocks without changing the site's design system.
 *
 * @param string $content Current content.
 * @return string
 */
function kadence_mcprices_filter_priority_content_blocks( $content ) {
	static $added_paths = array();

	if ( is_admin() ) {
		return $content;
	}

	if ( false !== strpos( (string) $content, 'data-mcprices-priority-seo=' ) ) {
		return $content;
	}

	$path  = kadence_mcprices_get_priority_request_path();
	$block = '';

	if ( isset( $added_paths[ $path ] ) ) {
		return $content;
	}

	switch ( $path ) {
		case '':
		case 'home':
		case 'homepage':
			$block = false === strpos( (string) $content, 'country-specific reference for U.S. McDonald' ) ? kadence_mcprices_get_homepage_priority_block() : '';
			break;
		case 'happy-meal-menu':
			$block = false === strpos( (string) $content, "McDonald's Happy Meal Price (2026)" ) ? kadence_mcprices_get_happy_meal_priority_block() : '';
			break;
		case 'breakfast-menu':
			$block = false === strpos( (string) $content, "McDonald's Breakfast Prices: Quick Table" ) ? kadence_mcprices_get_breakfast_priority_block() : '';
			break;
		case 'menu/beverages-drinks':
			$block = false === strpos( (string) $content, 'drinks price comparison by size' ) ? kadence_mcprices_get_beverages_priority_block() : '';
			break;
		case 'menu/mccafe-coffees':
			$block = false === strpos( (string) $content, 'McCafe price table' ) ? kadence_mcprices_get_mccafe_priority_block() : '';
			break;
		case 'mcdonalds-app-deals':
			$block = false === strpos( (string) $content, '$5 McChicken Meal Deal' ) ? kadence_mcprices_get_app_deals_priority_block() : '';
			break;
		default:
			break;
	}

	if ( '' === $block ) {
		return $content;
	}

	$added_paths[ $path ] = true;

	return $block . $content;
}

/**
 * Keep critical Rank Math stored state aligned for pages that Rank Math handles
 * outside normal title/robots filters.
 *
 * @return void
 */
function kadence_mcprices_sync_priority_runtime_meta() {
	$home_title       = kadence_mcprices_get_priority_page_title_for_path( '' );
	$home_description = kadence_mcprices_get_priority_page_description_for_path( '' );
	$rank_math_titles = get_option( 'rank-math-options-titles', array() );

	if ( is_array( $rank_math_titles ) ) {
		$changed = false;

		if ( $home_title && ( ! isset( $rank_math_titles['homepage_title'] ) || $home_title !== $rank_math_titles['homepage_title'] ) ) {
			$rank_math_titles['homepage_title'] = $home_title;
			$changed = true;
		}

		if ( $home_description && ( ! isset( $rank_math_titles['homepage_description'] ) || $home_description !== $rank_math_titles['homepage_description'] ) ) {
			$rank_math_titles['homepage_description'] = $home_description;
			$changed = true;
		}

		if ( $changed ) {
			update_option( 'rank-math-options-titles', $rank_math_titles, false );
		}
	}

	$front_page_id = (int) get_option( 'page_on_front' );
	if ( $front_page_id > 0 ) {
		update_post_meta( $front_page_id, 'rank_math_title', $home_title );
		update_post_meta( $front_page_id, 'rank_math_description', $home_description );
	}

	foreach ( array( 'test', 'ad-disclosure' ) as $slug ) {
		$page = get_page_by_path( $slug );

		if ( $page instanceof \WP_Post ) {
			update_post_meta( (int) $page->ID, 'rank_math_robots', array( 'noindex', 'follow' ) );
		}
	}
}

add_filter( 'pre_get_document_title', 'kadence_mcprices_filter_priority_title', 9999 );
add_filter( 'rank_math/frontend/title', 'kadence_mcprices_filter_priority_title', 9999 );
add_filter( 'wpseo_title', 'kadence_mcprices_filter_priority_title', 9999 );
add_filter( 'rank_math/frontend/description', 'kadence_mcprices_filter_priority_description', 9999 );
add_filter( 'wpseo_metadesc', 'kadence_mcprices_filter_priority_description', 9999 );
add_filter( 'the_title', 'kadence_mcprices_filter_priority_visible_title', 9999, 2 );
add_filter( 'wp_robots', 'kadence_mcprices_filter_priority_wp_robots', 9999 );
add_filter( 'rank_math/frontend/robots', 'kadence_mcprices_filter_priority_rank_math_robots', 9999 );
add_filter( 'the_content', 'kadence_mcprices_filter_priority_content_blocks', 9999 );
add_action( 'init', 'kadence_mcprices_sync_priority_runtime_meta', 99 );

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
 * Build the dynamic meta title for the current request.
 *
 * @return string
 */
function kadence_mcprices_get_dynamic_meta_title() {
	$current_year = kadence_mcprices_get_current_site_year();

	if ( is_front_page() && ! is_home() ) {
		return "McDonald's Menu Prices USA {$current_year} | Full Price List & Calories";
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
			return trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( $rank_math_description ) ) );
		}
	}

	if ( $queried_object instanceof \WP_Term ) {
		$rank_math_description = trim( (string) get_term_meta( (int) $queried_object->term_id, 'rank_math_description', true ) );

		if ( '' !== $rank_math_description ) {
			return trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( $rank_math_description ) ) );
		}
	}

	if ( is_front_page() && ! is_home() ) {
		return "Complete McDonald's USA menu prices updated {$current_date}. Find prices for burgers, breakfast, McCafe, drinks, McValue deals, McNuggets, Happy Meals, desserts, and combo meals in dollars.";
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

	return $dynamic_title ? $dynamic_title : $title;
}

/**
 * Filter Rank Math's generated description using the shared theme logic.
 *
 * @param string $description Rank Math description.
 * @return string
 */
function kadence_mcprices_filter_rank_math_description( $description ) {
	$dynamic_description = kadence_mcprices_get_dynamic_meta_description();

	return $dynamic_description ? $dynamic_description : $description;
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
	add_filter( 'rank_math/opengraph/facebook/image', 'kadence_mcprices_filter_rank_math_facebook_image' );
}
add_action( 'after_setup_theme', 'kadence_mcprices_register_dynamic_meta_hooks', 60 );

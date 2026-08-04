<?php
/**
 * Plugin Name: McPrices Site Integration
 * Description: Loads the McPrices design, content, SEO, and media layer from an update-safe location.
 * Version: 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MCPRICES_SITE_ROOT', __DIR__ . '/mcprices-site' );
define( 'MCPRICES_SITE_URL', trailingslashit( WPMU_PLUGIN_URL ) . 'mcprices-site' );

/**
 * Resolve an update-safe McPrices package path.
 *
 * @param string $relative_path Package-relative path.
 * @return string
 */
function mcprices_site_path( $relative_path = '' ) {
	$relative_path = ltrim( (string) $relative_path, '/\\' );

	return MCPRICES_SITE_ROOT . ( '' !== $relative_path ? '/' . $relative_path : '' );
}

/**
 * Resolve an update-safe McPrices package URL.
 *
 * @param string $relative_path Package-relative path.
 * @return string
 */
function mcprices_site_url( $relative_path = '' ) {
	$relative_path = ltrim( (string) $relative_path, '/\\' );

	return MCPRICES_SITE_URL . ( '' !== $relative_path ? '/' . $relative_path : '' );
}

/**
 * Keep existing WordPress content compatible after assets leave the theme.
 *
 * The database remains admin-editable; only the rendered asset base changes.
 *
 * @param mixed $content Rendered content.
 * @return mixed
 */
function mcprices_site_rewrite_legacy_asset_urls( $content ) {
	if ( ! is_string( $content ) || false === strpos( $content, '/wp-content/themes/kadence/assets/' ) ) {
		return $content;
	}

	return str_replace(
		array(
			'/wp-content/themes/kadence/assets/images/mcprices/',
			'/wp-content/themes/kadence/assets/css/mcprices-',
			'/wp-content/themes/kadence/assets/js/mcprices-',
			'/wp-content/themes/kadence/assets/data/mcprices-',
		),
		array(
			'/wp-content/mu-plugins/mcprices-site/assets/images/mcprices/',
			'/wp-content/mu-plugins/mcprices-site/assets/css/mcprices-',
			'/wp-content/mu-plugins/mcprices-site/assets/js/mcprices-',
			'/wp-content/mu-plugins/mcprices-site/assets/data/mcprices-',
		),
		$content
	);
}
add_filter( 'the_content', 'mcprices_site_rewrite_legacy_asset_urls', PHP_INT_MAX );
add_filter( 'widget_text', 'mcprices_site_rewrite_legacy_asset_urls', PHP_INT_MAX );
add_filter( 'widget_text_content', 'mcprices_site_rewrite_legacy_asset_urls', PHP_INT_MAX );

/**
 * Load the site integration after the Kadence parent theme is available.
 *
 * @return void
 */
function mcprices_site_bootstrap() {
	if ( 'kadence' !== get_template() || ! function_exists( '\\Kadence\\kadence' ) ) {
		return;
	}

	require_once mcprices_site_path( 'inc/mcprices/asset-paths.php' );

	if ( ! class_exists( '\\Kadence\\McPrices_Integration', false ) ) {
		require_once mcprices_site_path( 'inc/mcprices/class-mcprices-integration.php' );
	}

	if ( ! function_exists( 'kadence_mcprices_output_json_ld_schema' ) ) {
		require_once mcprices_site_path( 'inc/mcprices/schema.php' );
	}

	if ( ! function_exists( 'kadence_mcprices_normalize_robots_value' ) ) {
		require_once mcprices_site_path( 'inc/mcprices/indexing.php' );
	}

	if ( ! function_exists( 'kadence_mcprices_convert_html_to_gutenberg_blocks' ) ) {
		require_once mcprices_site_path( 'bootstrap.php' );
	}

	\Kadence\McPrices_Integration::get_instance();
}
add_action( 'after_setup_theme', 'mcprices_site_bootstrap', 1 );

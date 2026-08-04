<?php
/**
 * Update-safe asset resolution for the McPrices Kadence integration.
 *
 * @package mcprices-site
 */

namespace Kadence;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( __NAMESPACE__ . '\\get_theme_file_path' ) ) {
	/**
	 * Prefer a packaged McPrices file and otherwise use the parent theme file.
	 *
	 * @param string $relative_path Relative file path.
	 * @return string
	 */
	function get_theme_file_path( $relative_path = '' ) {
		$package_path = \mcprices_site_path( $relative_path );

		return file_exists( $package_path ) ? $package_path : \get_theme_file_path( $relative_path );
	}
}
if ( ! function_exists( __NAMESPACE__ . '\\get_theme_file_uri' ) ) {
	/**
	 * Prefer a packaged McPrices URL and otherwise use the parent theme URL.
	 *
	 * @param string $relative_path Relative file path.
	 * @return string
	 */
	function get_theme_file_uri( $relative_path = '' ) {
		$package_path = \mcprices_site_path( $relative_path );

		return file_exists( $package_path ) ? \mcprices_site_url( $relative_path ) : \get_theme_file_uri( $relative_path );
	}
}

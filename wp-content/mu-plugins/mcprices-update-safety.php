<?php
/**
 * Plugin Name: McPrices Update Safety
 * Description: Keeps critical layout styles out of generated cache bundles so core updates and cache refreshes cannot leave pages unstyled.
 *
 * This must-use plugin deliberately lives outside the active theme. WordPress
 * core updates do not replace files in wp-content/mu-plugins, so the header
 * safeguard is available before the theme and cache optimizer initialise.
 *
 * @package McPrices
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Keep the styles that establish the site layout as stable, direct asset URLs.
 *
 * LiteSpeed may safely optimise non-critical assets, but combining these styles
 * into a generated file couples cached HTML to a disposable cache directory.
 * A core update or cache rebuild can otherwise leave an edge-cached page
 * pointing at a generated stylesheet which no longer exists.
 *
 * @param string $html   Original stylesheet tag.
 * @param string $handle Enqueued handle.
 * @param string $href   Stylesheet URL.
 * @param string $media  Media attribute.
 * @return string
 */
function mcprices_update_safety_filter_critical_stylesheet_tag( $html, $handle, $href, $media ) {
	$critical_handles = array(
		'kadence-global',
		'kadence-header',
		'kadence-content',
		'kadence-comments',
		'kadence-footer',
		'kadence-mcprices-design',
		'kadence-mcprices-enhanced-layer',
		'kadence-rankmath',
		'rank-math-toc-block',
	);

	if ( ! in_array( $handle, $critical_handles, true ) || false !== strpos( $html, 'data-no-optimize=' ) ) {
		return $html;
	}

	return preg_replace( '/<link\b/i', '<link data-no-optimize="1"', $html, 1 );
}
add_filter( 'style_loader_tag', 'mcprices_update_safety_filter_critical_stylesheet_tag', 1, 4 );

<?php
/**
 * Local XAMPP stability fixes for WordPress admin and frontend requests.
 *
 * @package McPrices
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check whether the current runtime is the local XAMPP site.
 *
 * @return bool
 */
function mcprices_is_local_xampp_runtime() {
	$host = '';

	if ( defined( 'WP_HOME' ) ) {
		$parsed_host = wp_parse_url( WP_HOME, PHP_URL_HOST );
		if ( is_string( $parsed_host ) ) {
			$host = $parsed_host;
		}
	}

	if ( '' === $host && ! empty( $_SERVER['HTTP_HOST'] ) ) {
		$host = (string) $_SERVER['HTTP_HOST'];
	}

	$host = strtolower( preg_replace( '/:\d+$/', '', $host ) );

	return in_array( $host, array( 'localhost', '127.0.0.1', '::1' ), true );
}

if ( ! mcprices_is_local_xampp_runtime() ) {
	return;
}

add_action(
	'muplugins_loaded',
	static function () {
		if ( function_exists( 'hostinger_use_proxy_services' ) ) {
			remove_filter( 'pre_http_request', 'hostinger_use_proxy_services', 10 );
		}
	},
	20
);

add_filter(
	'pre_http_request',
	static function ( $preempt, $parsed_args, $url ) {
		$host = wp_parse_url( $url, PHP_URL_HOST );

		if ( ! is_string( $host ) || '' === $host ) {
			return $preempt;
		}

		$host = strtolower( $host );

		if ( in_array( $host, array( 'localhost', '127.0.0.1', '::1' ), true ) ) {
			return $preempt;
		}

		return new WP_Error(
			'mcprices_local_http_blocked',
			'External HTTP requests are disabled for the local XAMPP environment.'
		);
	},
	999,
	3
);

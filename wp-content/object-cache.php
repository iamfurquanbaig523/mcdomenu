<?php
// phpcs:ignoreFile

/**
 * Plugin Name:       LiteSpeed Cache - Object Cache (Drop-in)
 * Plugin URI:        https://www.litespeedtech.com/products/cache-plugins/wordpress-acceleration
 * Description:       High-performance page caching and site optimization from LiteSpeed.
 * Author:            LiteSpeed Technologies
 * Author URI:        https://www.litespeedtech.com
 */

defined( 'WPINC' ) || exi

$data_file = WP_CONTENT_DIR . '/.litespeed_conf.dat';
$lib_file  = $lscwp_dir . 'src/object.lib.php';

// Can't find LSCWP location, terminate object cache process
if ( ! $lscwp_dir || ! file_exists( $data_file ) || ( ! file_exists( $lib_file ) ) ) {
	if ( ! is_admin() ) { // Bypass object cache for frontend
		require_once ABSPATH . WPINC . '/cache.php';
	} else {
		$err = 'Can NOT find LSCWP path for object cache initialization in ' . __FILE__;
		error_log( $err );
		add_action(
			is_network_admin() ? 'network_admin_notices' : 'admin_notices',
			function () use ( &$err ) {
				echo $err;
			}
		);
	}
} elseif ( ! LSCWP_OBJECT_CACHE ) {
	// Disable cache
		wp_using_ext_object_cache( false );
}
	// Init object cache & LSCWP
elseif ( file_exists( $lib_file ) ) {
	require_once $lib_file;
}

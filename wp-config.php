<?php
/**
 * Environment-aware WordPress configuration for local, staging, and release
 * directories.
 *
 * Production secrets should be supplied by the server environment or by a
 * shared .env file symlinked into each immutable release.
 *
 * @package WordPress
 */

/**
 * Load simple KEY=value pairs without requiring Composer on production.
 *
 * @param string $path Environment file path.
 * @return void
 */
function mcprices_config_load_env( $path ) {
	if ( ! is_readable( $path ) ) {
		return;
	}

	$lines = file( $path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );

	if ( ! is_array( $lines ) ) {
		return;
	}

	foreach ( $lines as $line ) {
		$line = trim( (string) $line );

		if ( '' === $line || '#' === $line[0] || false === strpos( $line, '=' ) ) {
			continue;
		}

		list( $key, $value ) = explode( '=', $line, 2 );
		$key                = trim( $key );
		$value              = trim( $value );

		if ( '' === $key || preg_match( '/[^A-Z0-9_]/', $key ) ) {
			continue;
		}

		$first = substr( $value, 0, 1 );
		$last  = substr( $value, -1 );

		if ( ( '"' === $first && '"' === $last ) || ( "'" === $first && "'" === $last ) ) {
			$value = substr( $value, 1, -1 );
		}

		if ( false === getenv( $key ) ) {
			putenv( $key . '=' . $value );
			$_ENV[ $key ]    = $value;
			$_SERVER[ $key ] = $value;
		}
	}
}

/**
 * Return an environment value.
 *
 * @param string $key Environment key.
 * @param mixed  $default Default value.
 * @return mixed
 */
function mcprices_config_env( $key, $default = '' ) {
	$value = getenv( $key );

	return false === $value ? $default : $value;
}

/**
 * Return a boolean environment value.
 *
 * @param string $key Environment key.
 * @param bool   $default Default value.
 * @return bool
 */
function mcprices_config_bool( $key, $default = false ) {
	$value = mcprices_config_env( $key, null );

	if ( null === $value || '' === $value ) {
		return (bool) $default;
	}

	return in_array( strtolower( (string) $value ), array( '1', 'true', 'yes', 'on' ), true );
}

$mcprices_env_file = mcprices_config_env( 'MCPRICES_ENV_FILE', __DIR__ . '/.env' );
mcprices_config_load_env( $mcprices_env_file );

define( 'WP_CACHE', mcprices_config_bool( 'WP_CACHE', true ) );

define( 'DB_NAME', mcprices_config_env( 'DB_NAME', 'mcmenu' ) );
define( 'DB_USER', mcprices_config_env( 'DB_USER', 'root' ) );
define( 'DB_PASSWORD', mcprices_config_env( 'DB_PASSWORD', '' ) );
define( 'DB_HOST', mcprices_config_env( 'DB_HOST', 'localhost' ) );
define( 'DB_CHARSET', mcprices_config_env( 'DB_CHARSET', 'utf8' ) );
define( 'DB_COLLATE', mcprices_config_env( 'DB_COLLATE', '' ) );

define( 'AUTH_KEY', mcprices_config_env( 'AUTH_KEY', 'local-auth-key-change-me' ) );
define( 'SECURE_AUTH_KEY', mcprices_config_env( 'SECURE_AUTH_KEY', 'local-secure-auth-key-change-me' ) );
define( 'LOGGED_IN_KEY', mcprices_config_env( 'LOGGED_IN_KEY', 'local-logged-in-key-change-me' ) );
define( 'NONCE_KEY', mcprices_config_env( 'NONCE_KEY', 'local-nonce-key-change-me' ) );
define( 'AUTH_SALT', mcprices_config_env( 'AUTH_SALT', 'local-auth-salt-change-me' ) );
define( 'SECURE_AUTH_SALT', mcprices_config_env( 'SECURE_AUTH_SALT', 'local-secure-auth-salt-change-me' ) );
define( 'LOGGED_IN_SALT', mcprices_config_env( 'LOGGED_IN_SALT', 'local-logged-in-salt-change-me' ) );
define( 'NONCE_SALT', mcprices_config_env( 'NONCE_SALT', 'local-nonce-salt-change-me' ) );
define( 'WP_CACHE_KEY_SALT', mcprices_config_env( 'WP_CACHE_KEY_SALT', 'local-cache-key-salt-change-me' ) );

$table_prefix = preg_replace( '/[^A-Za-z0-9_]/', '', (string) mcprices_config_env( 'DB_TABLE_PREFIX', 'wp_' ) );

if ( '' === $table_prefix ) {
	$table_prefix = 'wp_';
}

$mcprices_wp_home    = trim( (string) mcprices_config_env( 'WP_HOME', 'http://localhost/wordpress' ) );
$mcprices_wp_siteurl = trim( (string) mcprices_config_env( 'WP_SITEURL', $mcprices_wp_home ) );

if ( '' !== $mcprices_wp_home ) {
	define( 'WP_HOME', $mcprices_wp_home );
}

if ( '' !== $mcprices_wp_siteurl ) {
	define( 'WP_SITEURL', $mcprices_wp_siteurl );
}

$mcprices_environment = trim( (string) mcprices_config_env( 'WP_ENVIRONMENT_TYPE', '' ) );

if ( '' === $mcprices_environment ) {
	$mcprices_host = 'localhost';

	if ( ! empty( $_SERVER['HTTP_HOST'] ) ) {
		$mcprices_host = strtolower( preg_replace( '/:\d+$/', '', (string) $_SERVER['HTTP_HOST'] ) );
	}

	$mcprices_environment = in_array( $mcprices_host, array( 'localhost', '127.0.0.1', '::1' ), true ) ? 'local' : 'production';
}

if ( ! defined( 'WP_ENVIRONMENT_TYPE' ) ) {
	define( 'WP_ENVIRONMENT_TYPE', $mcprices_environment );
}

if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', mcprices_config_bool( 'WP_DEBUG', false ) );
}

if ( ! defined( 'DISABLE_WP_CRON' ) ) {
	define( 'DISABLE_WP_CRON', mcprices_config_bool( 'DISABLE_WP_CRON', 'local' === $mcprices_environment ) );
}

if ( ! defined( 'DISALLOW_FILE_MODS' ) ) {
	define( 'DISALLOW_FILE_MODS', mcprices_config_bool( 'DISALLOW_FILE_MODS', 'production' === $mcprices_environment ) );
}

if ( ! defined( 'AUTOMATIC_UPDATER_DISABLED' ) ) {
	define( 'AUTOMATIC_UPDATER_DISABLED', mcprices_config_bool( 'AUTOMATIC_UPDATER_DISABLED', 'production' === $mcprices_environment ) );
}

$mcprices_auto_update_core = mcprices_config_env( 'WP_AUTO_UPDATE_CORE', 'minor' );

if ( in_array( strtolower( (string) $mcprices_auto_update_core ), array( 'true', 'false' ), true ) ) {
	$mcprices_auto_update_core = mcprices_config_bool( 'WP_AUTO_UPDATE_CORE', true );
}

define( 'FS_METHOD', mcprices_config_env( 'FS_METHOD', 'direct' ) );
define( 'WP_AUTO_UPDATE_CORE', $mcprices_auto_update_core );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';

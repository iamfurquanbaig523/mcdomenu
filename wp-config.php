<?php
define( 'WP_CACHE', true );

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'mcmenu' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',          '6KQdl5oZ{_:A*xM13w4}t^R&p#{^V aKdBm&$TX (%|^Syx.uBf9G#wTqV{.]>T|' );
define( 'SECURE_AUTH_KEY',   'yr##y}o#VEFT+8F8u]yx:%@&DCid:*PK(A0KW^r1l(_~QY3,-VrA/@_xaWfE>L[N' );
define( 'LOGGED_IN_KEY',     '$Iz50V2+Oc`_>&9ZYn/>B94R!3x#.Jd(Umv|{$xVS)CG5/fB80|8<a-e(nq,,:hp' );
define( 'NONCE_KEY',         '*{cPfn2V+1eT]4AEi*+K5Spi#+y!I1@.hPsK.?5LBr;L~7J!3J-;q0bMZaf%NCA6' );
define( 'AUTH_SALT',         '!(|?MT1z+v>4CKJfhb:W]sdfn<;A.;365+o4spRm&}U(KRxpa4ZQwh L{woq~1Ah' );
define( 'SECURE_AUTH_SALT',  'AV6b$qR-UTfY,YH.+9=8i~fPILR_MT#@`7g3I,7e8*:Qf*9eQz`XBlZtPP 4M.jt' );
define( 'LOGGED_IN_SALT',    'f| 6VJdmH.3pCNY*6KJhghB  r)]ySL;J|JZxtC^uT,q%B;_GPuA D|m=~]@6vN~' );
define( 'NONCE_SALT',        'ngi:DUNq>u=QZh#M=B<de?XT>t)q,/&9G_,UJnNiK WEw)]bJMbV0tRSl%`R ]S=' );
define( 'WP_CACHE_KEY_SALT', 'Vp]oy[D_Ry!k1d.wmBk qB<<jIL.CHNa?vr@:sf)qqI]zp|=o,g!IX/0<duu+x~E' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */

define( 'WP_HOME', 'http://localhost/wordpress' );
define( 'WP_SITEURL', 'http://localhost/wordpress' );

$mcprices_local_host = 'localhost';

if ( ! empty( $_SERVER['HTTP_HOST'] ) ) {
	$mcprices_local_host = strtolower( preg_replace( '/:\d+$/', '', (string) $_SERVER['HTTP_HOST'] ) );
}

if ( in_array( $mcprices_local_host, array( 'localhost', '127.0.0.1', '::1' ), true ) ) {
	define( 'WP_ENVIRONMENT_TYPE', 'local' );
	define( 'DISABLE_WP_CRON', true );
}



/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

define( 'FS_METHOD', 'direct' );
define( 'WP_AUTO_UPDATE_CORE', 'minor' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';

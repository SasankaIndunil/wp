<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'testwordpress_db' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', '' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'Gpx)VHZx8YL7d}?T cAPvD;9xD(ca];Mop3e-yY<5^|Fq{ag#9SYCPJ~eW+~ y6H' );
define( 'SECURE_AUTH_KEY',  ' oJeFsb<)?YK+l?&i]SIMwzph2TX#HoO#Yywwg-H`KJ4lE~#yJ*c)`_N{|fT w6_' );
define( 'LOGGED_IN_KEY',    '2n#@@mDYTx6V?Rqq)6g6kB[mQd$P;I+8*Rr6abc[kT(MzeZ)wYRMgwo% =$lek#]' );
define( 'NONCE_KEY',        'e=$Q^20G9ydMZ^ Y&,C^=_w5(c#Ab!GB33:%r^[C.~w0Al3)mvp*k<G.VB ~(1%f' );
define( 'AUTH_SALT',        'o_()Gx;CA2ellqBjl>PyP/4ra8LS&312[ECy+(b{6VpPEN;8yA]/k-aDE{sgr5_|' );
define( 'SECURE_AUTH_SALT', '2D7DK[dR9]+6!q_TbiJtmDoNkyz&fi9Vem-.{ g@ph1tpYEo;Cct(T7~ 9> nk<u' );
define( 'LOGGED_IN_SALT',   '$h,e#4BDbDx(3c3wO)lrozbDpiR{DqBk51(A3K P@Ue-$4urrU82S`+XLRe`V_j9' );
define( 'NONCE_SALT',       ') *mdC{Bk?h|+sz&a7G)_J>9z~A3r2xl!:r#^T)=B .%=0wM+Y!=1!Dy`cfdojm%' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

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
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';

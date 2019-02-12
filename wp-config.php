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
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'goinduc1_wp_one_page_demo');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', 'root');

/** MySQL hostname
 Use 173.254.28.110 to use with goindu.com
 */
// define('DB_HOST', '173.254.28.110');
 define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         ']9/r9F{fv 1t!gm?BD-]dRTi #Z %w@bU+jkFuL~g3~|y8+l9C{Yqw_Tx#D.a1gZ');
define('SECURE_AUTH_KEY',  'xPsI2l$P7mP?Vh)r)YJV[UHGn)5[h37?Q;$+Oe)-omR*yLu599p)sA[Q|03nlkT~');
define('LOGGED_IN_KEY',    'F6(hVE,j@PB+qu+b6[DgoJDSZ+0g?z8BH<&F<I?b8j;QOa4O~|=g2!MCL27ji|UL');
define('NONCE_KEY',        '}Bl!ZN#o-}y|ZBK-Hv+Rvi/OUbW3{1y<GpMT7L9,J;oPs38&0+V0LgGg#GPD,>mE');
define('AUTH_SALT',        ' {NMEFdbW/+S1KACY$R.mYG }@w5R)@A6C|K#rC<RI-WIHc&@ZwJ_sVa`QMBxcJr');
define('SECURE_AUTH_SALT', 'YByh.txb{|<HEM%B^KE?R=LY2TD>!%>5ZRf|Iu|_75LxM3Ax>*NeV-({zCRTS:^H');
define('LOGGED_IN_SALT',   'O!H+.=9RE:Z]b;$#GrjIupY8`{-/hvdKqlm1>L8,`ON-M%DV8$-(D-Z)Ta+Ziy 3');
define('NONCE_SALT',       '?q~tBkWmYJ7R:%``=5W:6o@-fC#[ie3)J1,`OoHZ=QlMpC`<~@41><`J*0NPKV}W');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wpopd_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');

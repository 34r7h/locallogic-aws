<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
$db_data = false;
if ( file_exists('/opt/aws/cloud_formation.json') ) {
	$db_data = json_decode(file_get_contents('/opt/aws/cloud_formation.json'), true);
	if ( isset($db_data['rds']) ) {
		$db_data = $db_data['rds'];
		$db_data['host'] = $db_data['endpoint'] . ':' . $db_data['port'];
	}
}
if ( !$db_data ) {
	$db_data = array(
		'database' => 'i_5b72d1b6',
		'username' => 'wp_j9nnl7jmbl5a9',
		'password' => 'JnrErpewIIzVwTBHAgiR2$)YM&^taeot',
		'host'     => 'localhost',
	);
}

/** The name of the database for WordPress */
define('DB_NAME', $db_data['database']);

/** MySQL database username */
define('DB_USER', $db_data['username']);

/** MySQL database password */
define('DB_PASSWORD', $db_data['password']);

/** MySQL hostname */
define('DB_HOST', $db_data['host']);

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

unset($db_data);

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '$bCZ.L/1<no7@qNI5-2:siDd-5ftT8CL7q^(]JJ~<$Ha|O=LO_MF-bqw}_GCMUr)');
define('SECURE_AUTH_KEY',  'u5tLDr;EAe)ct@!dPd{^)|vO]a/XL|mpZz,u 0]!c$N)j!kWC?P$BoWgTk7|CH5|');
define('LOGGED_IN_KEY',    '3.~Ev6t Z#AeX<RVQGZQ4[w7e1fnTW3[ug u3Z=tRoS/-`n@+]|e+Ivo+?6$hz(G');
define('NONCE_KEY',        'HLnvg2WT}/V;8mI@UT/pb1s!5#f/C=BDCZ`38I9v,?d$+RpY/YC@NUu&If=z%Z:s');
define('AUTH_SALT',        '4hc|`0yB#Wqmc|bGHzB; ^uXa2.| li&>gmBKy)4_<0hxqo!Gzy;*s~>/F}G-~6N');
define('SECURE_AUTH_SALT', '.8:E82`Q+[Uy|*fFidR@00<O-(5[P{GMKHs&<=.1kG;_%]>I4g;]LrTM-Z;-#M+}');
define('LOGGED_IN_SALT',   'h~*2+sZ-4WhCmWQ9?xqtI?|izQ szStY}s7i|0o0td`L]N:-|Do}kGM!]Wocq,@z');
define('NONCE_SALT',       '39lI.$M?p:4^HJZ/+L!@Md~uW_lOcJ7;rNo^m#Tq@5e(:Se4/h;$yd=80pr{oEb%');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);
//define('WP_DEBUG', true);
//define('SAVEQUERIES', true);
//define('WP_DEBUG_DISPLAY', false);

/**
 * Multisite Settings
 */
// define ('WP_ALLOW_MULTISITE', true);
//
// define('MULTISITE', true);
// define('SUBDOMAIN_INSTALL', false);
// define('DOMAIN_CURRENT_SITE', 'example.com');
// define('PATH_CURRENT_SITE', '/');
// define('SITE_ID_CURRENT_SITE', 1);
// define('BLOG_ID_CURRENT_SITE', 1);

/**
 * For VaultPress
 */
define( 'VAULTPRESS_DISABLE_FIREWALL', true );
if ( !empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
   $forwarded_ips = explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] );
   $_SERVER['REMOTE_ADDR'] = $forwarded_ips[0];
   unset( $forwarded_ips );
}

/**
 * For Nginx Cache Controller
 */
define('IS_AMIMOTO', true);
define('NCC_CACHE_DIR', '/var/cache/nginx/proxy_cache');

/**
 * set post revisions
 */
//define('WP_POST_REVISIONS', 5);

/**
 * disallow file edit and modifie
 */
//define('DISALLOW_FILE_MODS',true);
//define('DISALLOW_FILE_EDIT',true);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
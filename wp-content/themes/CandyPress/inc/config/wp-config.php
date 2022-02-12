
<?php
// start session
// if (!session_id()){
//  session_start();
// }
 
defined('wodpress_env') 
  || define('wodpress_env', (getenv('wodpress_env') ? getenv('wodpress_env') : 'local'));
 
//defined('THEME_URL')
//    || define('THEME_URL', WP_SITEURL . '/wp-content/themes/mytheme/');
 
//Wordpress Env
switch(wodpress_env) {
  case 'production':
    $db_name     = 'x';
    $db_user     = 'x';
    $db_password = 'x';
    $db_host     = 'x.x.x';

    @ini_set('log_errors', 'On');
    @ini_set('display_errors', 'Off');
    define('WP_DEBUG', false);

    define('WP_AUTO_UPDATE_CORE',false);
    define('DISALLOW_FILE_MODS',true);
    define('DISALLOW_FILE_EDIT',true);

    define('COMPRESS_CSS', true);
    define('COMPRESS_SCRIPTS', true);
    define('CONCATENATE_SCRIPTS', true);
    define('ENFORCE_GZIP', true);

    define('WP_CACHE', true);
    break;

  case 'local':
    $db_name     = 'x';
    $db_user     = 'root';
    $db_password = 'root';
    $db_host     = 'localhost:8889';

    @ini_set('log_errors', 'On');
    @ini_set('display_errors', 'On');
    @error_reporting(E_NOTICE ^ E_DEPRECATED);
    
    define('WP_DEBUG', true);
    define('WP_DEBUG_LOG', true);
    define('WP_DEBUG_DISPLAY', true);
    define('SCRIPT_DEBUG', true);
    define('SAVEQUERIES', true);

    /* Autosave. In Seconds*/
    define('AUTOSAVE_INTERVAL', 300 );
    /* Multisite. */
    define( 'WP_ALLOW_MULTISITE', false );

    /* Specify maximum number of Revisions. */
    define( 'WP_POST_REVISIONS', '4' );

    /* Media Trash. */
    define( 'MEDIA_TRASH', true );

    break;

  default:
    break;
}

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', $db_name);
 
/** MySQL database username */
define('DB_USER', $db_user);
 
/** MySQL database password */
define('DB_PASSWORD', $db_password);
 
/** MySQL hostname */
define('DB_HOST', $db_host);
 
/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');
 
/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');
 
/** Facebook Keys */
define('FB_APPID', 'x');
define('FB_SECRET', 'x');
define('FB_REDIRECT', 'x');
 
/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'put your unique phrase here' );
define( 'SECURE_AUTH_KEY',  'put your unique phrase here' );
define( 'LOGGED_IN_KEY',    'put your unique phrase here' );
define( 'NONCE_KEY',        'put your unique phrase here' );
define( 'AUTH_SALT',        'put your unique phrase here' );
define( 'SECURE_AUTH_SALT', 'put your unique phrase here' );
define( 'LOGGED_IN_SALT',   'put your unique phrase here' );
define( 'NONCE_SALT',       'put your unique phrase here' );

/** **/
 
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
define('WPLANG', 'en_US');


/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';

<?php
/**
 * The base configurations of the system.
 *
 * @author Renfei Song
 * @since 2.0.0
 */

/* The name of the database */
define('DB_NAME', 'weixin');

/* MySQL database username */
define('DB_USER', 'root');

/* MySQL database password */
define('DB_PASSWORD', 'root');

/* MySQL hostname */
define('DB_HOST', 'localhost');

/* Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/* Authentication unique salts. */
define('LOGIN_SALT', 'unique string here');

/* Website Root URL */
define('ROOT_URL', '/');

// Define ABSPATH as this file's directory
define( 'ABSPATH', dirname(__FILE__) . '/' );

error_reporting( E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_ERROR | E_WARNING | E_PARSE | E_USER_ERROR | E_USER_WARNING | E_RECOVERABLE_ERROR );

/* Sets up vars and included files. */
require_once ABSPATH . 'includes/load.php';
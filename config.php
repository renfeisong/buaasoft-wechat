<?php
/**
 * The base configurations of the system.
 *
 * @author Renfei Song
 * @since 2.0.0
 */

// MySQL database name
define('DB_NAME', 'weixin');

// MySQL database username
define('DB_USER', 'root');

// MySQL database password
define('DB_PASSWORD', 'root');

// MySQL hostname
define('DB_HOST', 'localhost');

// MySQL database handle charset
define('DB_CHARSET', 'utf8');

// Authentication unique salts
define('LOGIN_SALT', 'unique string here');

// Website root URL
define('ROOT_URL', '/');

// Define ABSPATH as this file's directory
define('ABSPATH', dirname(__FILE__) . '/');

// Sets which PHP errors are reported
error_reporting(E_ALL);

// Sets up vars and included files
require_once ABSPATH . 'includes/load.php';
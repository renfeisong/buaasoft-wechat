<?php
/**
 * The base configurations of the system.
 *
 * @author Renfei Song
 * @since 1.0.0
 */

/** The name of the database */
define('DB_NAME', 'weixin');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', 'root');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** Absolute path to the directory. */
if (!defined('ABSPATH'))
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up vars and included files. */
require_once(ABSPATH . 'includes/settings.php');
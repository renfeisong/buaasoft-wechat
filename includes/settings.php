<?php
/**
 * Used to set up common variables and include the procedural and class library.
 *
 * @author Renfei Song
 * @since 2.0.0
 */

require_once ABSPATH . 'includes/InputType.php';
require_once ABSPATH . 'includes/UserInput.php';
require_once ABSPATH . 'includes/BaseModule.php';
require_once ABSPATH . 'includes/OutputFormatter.php';
require_once ABSPATH . 'includes/MessageReceiver.php';
require_once ABSPATH . 'includes/module.php';
require_once ABSPATH . 'includes/load.php';

// Constants
define('OBJECT', 'OBJECT');
define('OBJECT_K', 'OBJECT_K');
define('ARRAY_A', 'ARRAY_A');
define('ARRAY_N', 'ARRAY_N');

// Globals

$modules = array();
$filters = array();
$wxdb = null;
$time_start = 0.0;
$time_end = 0.0;

require_db();

load_modules(get_modules());
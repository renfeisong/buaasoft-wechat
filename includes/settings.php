<?php
/**
 * Used to set up common variables and include the procedural and class library.
 *
 * @since 1.0.0
 * @author Renfei Song
 */

require_once ABSPATH . 'includes/InputType.php';
require_once ABSPATH . 'includes/UserInput.php';
require_once ABSPATH . 'includes/BaseModule.php';
require_once ABSPATH . 'includes/OutputFormatter.php';
require_once ABSPATH . 'includes/MessageReceiver.php';
require_once ABSPATH . 'includes/load.php';

// Globals

$modules = array();
$wxdb = null;
$time_start = 0.0;
$time_end = 0.0;

require_db();

load_modules(get_modules());
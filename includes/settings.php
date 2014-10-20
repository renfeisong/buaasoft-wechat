<?php
/**
 * Used to set up common variables and include the procedural and class library.
 *
 * @since 1.0.0
 * @author Renfei Song
 */

require_once 'InputType.php';
require_once 'UserInput.php';
require_once 'BaseModule.php';
require_once 'OutputFormatter.php';
require_once 'MessageReceiver.php';
require_once 'load.php';

// Globals

$modules = array();
$wxdb = null;
$time_start = 0.0;
$time_end = 0.0;

require_db();

load_modules(get_modules());
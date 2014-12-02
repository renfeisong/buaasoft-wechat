<?php
/**
 * Used to set up common variables and include the procedural and class library.
 *
 * @author Renfei Song
 * @since 2.0.0
 */

if (defined('WX_DEBUG')) {
    // Sets which PHP errors are reported
    error_reporting(E_ALL);
    ini_set('display_errors', 'On');
}

require_once ABSPATH . 'includes/functions.php';
require_once ABSPATH . 'includes/module.php';

require_once ABSPATH . 'includes/InputType.php';
require_once ABSPATH . 'includes/UserInput.php';
require_once ABSPATH . 'includes/BaseModule.php';
require_once ABSPATH . 'includes/OutputFormatter.php';
require_once ABSPATH . 'includes/MessageReceiver.php';
require_once ABSPATH . 'includes/wxdb.php';

// Constants
define('OBJECT', 'OBJECT');
define('OBJECT_K', 'OBJECT_K');
define('ARRAY_A', 'ARRAY_A');
define('ARRAY_N', 'ARRAY_N');

// Globals
$modules = array();
$actions = array();
$global_options = array(
    'general' => '通用设置',
    'users' => '用户管理',
    'modules' => '模块管理',
    'debug' => '系统调试'
);
$global_option_icons = array(
    'general' => 'dashboard',
    'users' => 'user',
    'modules' => 'plug',
    'debug' => 'wrench'
);
$wxdb = null;
$time_start = 0.0;
$time_end = 0.0;
$userChecked = false; // Flag to guarantee that user request is logged only once
$adminUser = null; // Current admin user, for caching purpose

date_default_timezone_set('Asia/Shanghai');
timer_start();

if (system_ready()) {
    require_db();
    load_modules(get_modules());
}
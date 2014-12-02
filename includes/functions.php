<?php
/**
 * These functions are needed to load the system.
 *
 * @author Renfei Song
 * @since 2.0.0
 */

function system_ready() {
    $dbc = @new mysqli(DB_HOST, DB_USER, DB_PASSWORD);

    if ($dbc->connect_errno)
        return false;

    $dbc->select_db(DB_NAME);

    if ($dbc->errno)
        return false;

    // check if it's already installed
    $tbl_user = $dbc->query("show tables like 'user'")->num_rows;
    $tbl_admin = $dbc->query("show tables like 'admin_user'")->num_rows;
    $tbl_configuration = $dbc->query("show tables like 'configuration'")->num_rows;
    $tbl_log = $dbc->query("show tables like 'security_log'")->num_rows;
    $num_admin = $tbl_admin == 0 ? 0 : $dbc->query("select * from `admin_user`")->num_rows;
    if ($tbl_user + $tbl_admin + $tbl_configuration + $tbl_log == 4 && $num_admin >= 1) {
        return true;
    }

    return false;
}

/**
 * Start the micro-timer.
 *
 * @global float $time_start Unix timestamp set at the beginning of the page load.
 * @see timer_stop()
 */
function timer_start() {
    global $time_start;
    $time_start = microtime(true);
}

/**
 * Retrieve the time from the page start to when function is called.
 *
 * @global float $timestart Seconds from when timer_start() is called.
 * @global float $timeend   Seconds from when function is called.
 *
 * @param int $precision The number of digits from the right of the decimal to retrieve. Default 3.
 *
 * @return string The "second.microsecond" finished time calculation. The number is formatted or human consumption,
 * both localized and rounded.
 */
function timer_stop($precision = 3) {
    global $time_start, $time_end;
    $time_end = microtime(true);
    $time_total = $time_end - $time_start;
    $r = (function_exists('number_format_i18n')) ? number_format_i18n($time_total, $precision) : number_format($time_total, $precision);
    return $r;
}

/**
 * Return the number of queries the system has executed.
 *
 * @return int Number of queries.
 */
function queries_count() {
    global $wxdb; /* @var $wxdb wxdb */
    return $wxdb->num_queries;
}

function listQueries() {
    global $wxdb; /* @var $wxdb wxdb */
    foreach ($wxdb->queryHistory as $q) {
        echo $q . "\n";
    }
}

/**
 * Instantiate the `$wxdb` global.
 *
 * @global wxdb $wxdb The database class.
 */
function require_db() {
    global $wxdb;
    $wxdb = new wxdb(DB_USER, DB_PASSWORD, DB_NAME, DB_HOST);
}

/**
 * Retrieve an array of module paths and names.
 *
 * @return array Array of exist modules.
 */
function get_modules() {
    $module_list = array();
    $path = ABSPATH . "modules";
    $idx = 0;
    foreach (new DirectoryIterator($path) as $fileInfo) {
        if ($fileInfo->isDot() == false && $fileInfo->isDir()) {
            $module_name = $fileInfo->getFilename();
            $module_path = ABSPATH . "modules/" . $module_name . "/index.php";
            if (file_exists($module_path)) {
                $module_list[$idx]["path"] = $module_path;
                $module_list[$idx]["name"] = $module_name;
                $idx++;
            }
        }
    }

    return $module_list;
}

/**
 * Load all valid modules from given list.
 *
 * @see get_modules()
 * @param $module_list Array of module paths and names.
 */
function load_modules($module_list) {
    global $modules;

    foreach ($module_list as $module) {
        if (get_global_value('enabled_' . $module["name"]) == false) {
            continue;
        }
        require_once $module['path'];
        if (class_exists($module['name'])) {
            $m = new $module['name'];
            if (is_subclass_of($m, 'BaseModule')) {
                $modules[] = $m; /* @var $m BaseModule */
                set_global_value('display_name_' . get_class($m), $m->display_name());
                $m->prepare();
            }
        }
    }
    usort($modules, 'cmp_modules');
}

function cmp_modules(BaseModule $a, BaseModule $b) {
    if (get_module_priority($a) == get_module_priority($b))
        return 0;
    return (get_module_priority($a) < get_module_priority($b)) ? 1 : -1;
}

function get_module_priority(BaseModule $module) {
    $priority = get_global_value('priority_' . get_class($module));
    return $priority == null ? 10 : $priority;
}
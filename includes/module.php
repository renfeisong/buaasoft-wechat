<?php
/**
 * The module API is located in this file, which allows for creating custom modules.
 * Also see the {@link https://github.com/renfeisong/buaasoft-wechat/wiki/Module-Programming-Guide}
 * for more information and examples on how to create a module.
 *
 * @author Renfei Song
 * @since 2.0.0
 */

/**
 * Add an action hook to the event.
 *
 * @param string $tag Event name.
 * @param BaseModule $object Current module object, typically use `$this`
 * @param callable $function_to_add Function name.
 * @param int $priority Priority of this action. Higher priority results in earlier invocation.
 *
 * @return bool Success or not.
 */
function add_action($tag, $object, $function_to_add, $priority = 10) {
    global $actions;

    if (has_action($tag, $object, $function_to_add)) {
        return false;
    }

    if (!isset($actions[$tag])) {
        $actions[$tag] = array();
    }
    array_push($actions[$tag], array(
        'module' => $object,
        'function' => $function_to_add,
        'priority' => $priority
    ));

    // sort
    usort($actions[$tag], 'cmp_actions');

    return true;
}

function cmp_actions($a, $b) {
    if ($a['priority'] == $b['priority'])
        return 0;
    return ($a['priority'] < $b['priority']) ? 1 : -1;
}

function remove_action($tag, $object, $function_to_remove) {
    global $actions;
    if (isset($actions[$tag])) {
        foreach ($actions[$tag] as $action) {
            if ($action['module'] == $object && $action['function'] == $function_to_remove) {
                unset($action);
            }
        }
    }
}

function remove_all_actions($tag, $object) {
    global $actions;
    if (isset($actions[$tag])) {
        foreach ($actions[$tag] as $action) {
            if ($action['module'] == $object) {
                unset($action);
            }
        }
    }
}

function has_action($tag, $object, $function_to_check) {
    global $actions;
    if (isset($actions[$tag])) {
        foreach ($actions[$tag] as $action) {
            if ($action['module'] == $object && $action['function'] == $function_to_check) {
                return true;
            }
        }
    }
    return false;
}

function do_actions($tag, $args) {
    global $actions;
    if (!empty($actions[$tag])) {
        foreach ($actions[$tag] as $action) {
            call_user_func_array(array($action['module'], $action['function']), $args);
        }
    }
}

function set_value($object, $key, $value) {
    return _set_value(get_class($object), $key, $value);
}

function set_global_value($key, $value) {
    return _set_value('global', $key, $value);
}

function get_value($object, $key) {
    return _get_value(get_class($object), $key);
}

function get_option($key) {
    return _get_value($_GET['page'], $key);
}

function get_global_value($key) {
    return _get_value('global', $key);
}

function set_option($key, $value) {
    return _set_value($_GET['page'], $key, $value);
}

function _set_value($scope, $key, $value) {
    if ($scope == null || $scope == '') {
        return false;
    }

    global $wxdb, $configurations, $timesConfigSets; /* @var $wxdb wxdb */

    // Discard unneeded set operations
    if ($configurations[$scope][$key] == serialize($value))
        return true;

    // Execute set operation
    ++$timesConfigSets;
    $wxdb->replace('configuration', array(
        'scope' => $scope,
        'key' => $key,
        'value' => serialize($value)
    ));
    $configurations[$scope][$key] = serialize($value);
    return $wxdb->last_error == 0;
}

function _get_value($scope, $key) {
    global $configurations, $timesConfigGets;
    ++$timesConfigGets;
    $raw = @$configurations[$scope][$key];
    if (!empty($raw)) {
        return unserialize($raw);
    } else {
        return null;
    }
}
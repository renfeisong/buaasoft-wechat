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

    if (!is_array($actions[$tag])) {
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
    if (is_array($actions[$tag])) {
        foreach ($actions[$tag] as $action) {
            if ($action['module'] == $object && $action['function'] == $function_to_remove) {
                unset($action);
            }
        }
    }
}

function remove_all_actions($tag, $object) {
    global $actions;
    if (is_array($actions[$tag])) {
        foreach ($actions[$tag] as $action) {
            if ($action['module'] == $object) {
                unset($action);
            }
        }
    }
}

function has_action($tag, $object, $function_to_check) {
    global $actions;
    if (is_array($actions[$tag])) {
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
    foreach ($actions[$tag] as $action) {
        call_user_func_array(array($action['module'], $action['function']), $args);
    }
}

function set_value($object, $key, $value) {
    if ($object == null) {
        return false;
    }
    global $wxdb; /* @var $wxdb wxdb */
    $wxdb->replace('configuration', array(
        'scope' => $object == null ? 'global' : get_class($object),
        'key' => $key,
        'value' => serialize($value)
    ));

    return $wxdb->last_error == 0;
}

function get_value($object, $key) {
    global $wxdb; /* @var $wxdb wxdb */
    $scope = $object == null ? 'global' : get_class($object);
    $row = $wxdb->get_row($wxdb->prepare("SELECT * FROM `configuration` WHERE `scope` = '%s' AND `key` = '%s'", $scope, $key), ARRAY_A);

    if ($row)
        return unserialize($row['value']);
    else
        return null;
}
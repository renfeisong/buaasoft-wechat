<?php
/**
 * The module API is located in this file, which allows for creating custom modules.
 * Also see the {@link https://github.com/renfeisong/buaasoft-wechat/wiki/Module-Programming-Guide}
 * for more information and examples on how to create a module.
 *
 * @author Renfei Song
 * @since 2.0.0
 */

function add_filter($tag, $object, $function_to_add, $priority = 10) {
    global $filters;
    if (!is_array($filters[$tag])) {
        $filters[$tag] = array();
    }
    array_push($filters[$tag], array(
        'module' => $object,
        'function' => $function_to_add,
        'priority' => $priority
    ));
}

function remove_filter($tag, $object, $function_to_remove) {
    global $filters;
    if (is_array($filters[$tag])) {
        foreach ($filters[$tag] as $filter) {
            if ($filter['module'] == $object && $filter['function'] == $function_to_remove) {
                unset($filter);
            }
        }
    }
}

function has_filter($tag, $object, $function_to_check) {
    global $filters;
    if (is_array($filters[$tag])) {
        foreach ($filters[$tag] as $filter) {
            if ($filter['module'] == $object && $filter['function'] == $function_to_check) {
                return true;
            }
        }
    }
    return false;
}

function apply_filters($tag, $args) {
    global $filters;
    foreach ($filters[$tag] as $filter) {
        call_user_func_array(array($filter['module'], $filter['function']), $args);
    }
}
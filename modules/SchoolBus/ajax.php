<?php
/**
 * Handling AJAX request from School Bus Module (settings.php)
 *
 * @author Zhan Yu
 */
require_once dirname(dirname(dirname(__FILE__))) . '/config.php';

if($_REQUEST['action'] == 'getRoute') {
    reply(0, get_route_rest());
    exit;
}

$ajax_key = _get_value('SchoolBus', 'ajax_key');
if ($ajax_key != @$_REQUEST['auth']) {
    header($_SERVER['SERVER_PROTOCOL'] . " 401 Unauthorized");
    echo ' 权限验证失败。';
    exit;
}

global $wxdb;

switch($_REQUEST['action']) {
    case 'editBus':
        if($_REQUEST['name'] == 'departureTime') {
            if(!preg_match("/^(([01]?[0-9])|(2[0-3])):[0-5]?[0-9]$/", $_REQUEST['value'])) {
                header($_SERVER['SERVER_PROTOCOL'] . " 403 Forbidden");
                echo ' 时间不符合格式。';
                exit;
            }
            $_REQUEST['value'].=":00";
        }
        $wxdb->update(_get_value('SchoolBus', 'table_bus'), array(
            $_REQUEST['name']=>$_REQUEST['value']
        ), array(
            'id'=>$_REQUEST['pk']
        ));
        reply(0, 'ok');
        break;
    case 'delBus':
        $wxdb->delete(_get_value('SchoolBus', 'table_bus'), array('id' => $_REQUEST['pk']));
        reply(0, 'ok');
        break;
    case 'newBus':
        if(!preg_match("/^(([01]?[0-9])|(2[0-3])):[0-5]?[0-9]$/", $_REQUEST['departureTime'])) {
            header($_SERVER['SERVER_PROTOCOL'] . " 403 Forbidden");
            echo ' 时间不符合格式。';
            exit;
        }
        $wxdb->insert(_get_value('SchoolBus', 'table_bus'), array(
            'departureTime' => $_REQUEST['departureTime'].":00",
            'day' => $_REQUEST['day'],
            'departure' => $_REQUEST['departure'],
            'destination' => $_REQUEST['destination']
        ));
        reply(0, $wxdb->insert_id);
        break;
    case 'newRoute':
        $wxdb->insert(_get_value('SchoolBus', 'table_route'), array(
            'departure' => $_REQUEST['departure'],
            'destination' => $_REQUEST['destination']
        ));
        reply(0, $wxdb->insert_id);
        break;
    case 'delRoute':
        $wxdb->delete(_get_value('SchoolBus', 'table_route'), array('id' => $_REQUEST['pk']));
        reply(0, 'ok');
        break;
}

/**
 * Return all possible routes
 *
 * @return array
 */
function get_route_all() {
    global $wxdb;
    $table = _get_value('SchoolBus', 'table_bus');
    $departures = $wxdb->get_results("SELECT `departure` FROM `".$table."` WHERE 1 GROUP BY `departure`", ARRAY_A);
    $ret = array();
    foreach($departures as $departure) {
        $departure = $departure['departure'];
        $destinations = $wxdb->get_results("SELECT `destination` FROM `".$table."` WHERE `departure` = '$departure' GROUP BY `destination`", ARRAY_A);
        $dest = array();
        foreach($destinations as $destination) {
            array_push($dest, $destination['destination']);
        }
        $ret[$departure] = $dest;
    }
    return $ret;
}

/**
 * Return all rest routes
 *
 * @return array
 */
function get_route_rest() {
    global $wxdb;
    $table_bus = _get_value('SchoolBus', 'table_bus');
    $table_route = _get_value('SchoolBus', 'table_route');
    $departures = $wxdb->get_results("SELECT `departure` FROM `".$table_bus."` WHERE 1 GROUP BY `departure`", ARRAY_A);
    $routes_selected = $wxdb->get_results("SELECT `departure`, `destination` FROM `{$table_route}` WHERE 1", ARRAY_A);
    $rr = array();
    foreach($routes_selected as $route) {
        if(isset($rr[$route['departure']])) {
            array_push($rr[$route['departure']], $route['destination']);
        } else {
            $rr[$route['departure']] = array($route['destination']);
        }
    }
    $ret = array();
    foreach($departures as $departure) {
        $departure = $departure['departure'];
        $destinations = $wxdb->get_results("SELECT `destination` FROM `".$table_bus."` WHERE `departure` = '$departure' GROUP BY `destination`", ARRAY_A);
        $dest = array();
        foreach($destinations as $destination) {
            if(isset($rr[$departure]) && in_array($destination['destination'], $rr[$departure]))
                continue;
            array_push($dest, $destination['destination']);
        }
        if(count($dest) < 1) continue;
        $ret[$departure] = $dest;
    }
    return $ret;
}

function reply($status, $msg) {
    header("Content-Type: application/json;charset=utf-8");
    echo json_encode(array(
        "status"=>$status,
        "msg"=>$msg
    ), JSON_UNESCAPED_UNICODE);
}

$validator_time = "/^(([01]?[0-9])|(2[0-3])):[0-5]?[0-9]:[0-5]?[0-9]$/";
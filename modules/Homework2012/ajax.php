<?php
/**
 * Handling AJAX request from Homework Module (settings.php)
 *
 * @author Renfei Song
 */

require_once dirname(dirname(dirname(__FILE__))) . '/config.php';

// Security check
$ajax_key = _get_value(@$_GET['m'], 'ajax');
if (sha1(AJAX_SALT . $ajax_key) != @$_GET['auth'] || !isset($_GET['table'])) {
    header($_SERVER['SERVER_PROTOCOL'] . " 401 Unauthorized");
    echo ' 请求失败－权限验证错误。';
    exit;
}

global $wxdb; /* @var $wxdb wxdb */

$table = $_GET['table'];

if (isset($_GET['action']) && $_GET['action'] == 'delete'): // Delete homework

    $pk = $_GET['pk'];
    $wxdb->delete($table, array('homeworkId' => $pk));

else: // Update homework

    $name = $_POST['name'];
    $value = $_POST['value'];
    $pk = $_POST['pk'];

    if ($name == 'publishDate') {
        $publish_date = $value;

        $publish_date = validate_date($publish_date);
        if ($publish_date == false) {
            header($_SERVER['SERVER_PROTOCOL'] . " 403 Forbidden");
            echo ' 作业发布日期无效。';
            exit;
        }

        $now_timestamp = time();
        $publish_timestamp = strtotime($publish_date);
        if ($publish_timestamp > $now_timestamp) {
            header($_SERVER['SERVER_PROTOCOL'] . " 403 Forbidden");
            echo ' 发布日期不得晚于今天。';
            exit;
        }

        $sql = $wxdb->prepare("SELECT `dueDate` FROM `$table` WHERE `homeworkId` = %s", $pk);
        $due_date = $wxdb->get_var($sql);
        if (!empty($due_date)) {
            $due_timestamp = strtotime($due_date);
            if ($due_timestamp < $publish_timestamp) {
                header($_SERVER['SERVER_PROTOCOL'] . " 403 Forbidden");
                echo ' 发布日期不得晚于截止日期。';
                exit;
            }
        }
    }

    if ($name == 'dueDate') {
        $due_date = $value;
        if (!empty($due_date)) {
            $due_date = validate_date($due_date);
            if ($due_date == false) {
                header($_SERVER['SERVER_PROTOCOL'] . " 403 Forbidden");
                echo ' 作业截止日期无效。';
                exit;
            }

            $sql = $wxdb->prepare("SELECT `publishDate` FROM `$table` WHERE `homeworkId` = %s", $pk);
            $publish_date = $wxdb->get_var($sql);
            $publish_timestamp = strtotime($publish_date);
            $due_timestamp = strtotime($due_date);
            if ($due_timestamp < $publish_timestamp) {
                header($_SERVER['SERVER_PROTOCOL'] . " 403 Forbidden");
                echo ' 截止日期不得早于发布日期。';
                exit;
            }
        }
    }

    $wxdb->update($table, array($name => $value, 'dateUpdated' => date('c')), array('homeworkId' => $pk));

endif;

function validate_date($date) {
    $dt = DateTime::createFromFormat('Y-m-d', $date);
    if ($dt !== false && !array_sum($dt->getLastErrors())) {
        return $dt->format('Y-m-d');
    }
    return false;
}
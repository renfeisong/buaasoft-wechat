<?php
/**
 * Bound process.
 *
 * @author TimmyXu
 * @since 2.0.0
 */

require_once dirname(dirname(dirname(__FILE__))) . '/config.php';

global $wxdb; /* @var $wxdb wxdb */

$openid = $_POST['openid'];
$stuid = $_POST['stuid'];
$identity = $_POST['identity'];
$from = $_POST['from'];

if (empty($openid) || empty($stuid) || empty($identity) || empty($from)) {
    echo '绑定失败';
    exit;
}

if ($from == 'main') {
    $wxdb->query($wxdb->prepare("select * from `user` where userId = '%s' and openid = '%s' and identifyId = '%s'", $stuid, $openid, $identity));
    $result = $wxdb->last_result;
    $num = $wxdb->num_rows;
    if ($num != 0) {
        echo '您已经绑定，请勿重复提交';
        exit;
    }

    $wxdb->query($wxdb->prepare("select * from `user` where userId = '%s'", $stuid));
    $result = $wxdb->last_result;
    $num = $wxdb->num_rows;
    if ($num != 0) {
        $row = $result[0];
        if ($row->identifyId == $identity) {
            $result = $wxdb->update('user', array('openid' => $openid), array('userId' => $stuid));
            if ($result === false)
                echo '绑定失败';
            else
                echo 0;
        } else {
            echo '身份验证失败';
        }
    } else {
        echo '无法找到此学号，请检查您的输入或联系管理员';
    }
    exit;
}

if ($from == 'alt') {
    if (strpos($stuid, 'ZY') === false && strpos($stuid, 'SY') === false && strpos($stuid, 'BY') === false) {
        echo '本入口为研究生专用绑定通道';
        exit;
    }

    $wxdb->query($wxdb->prepare("select * from `user` where userId = '%s' and openid = '%s' and userName = '%s'", $stuid, $openid, $identity));
    $result = $wxdb->last_result;
    $num = $wxdb->num_rows;
    if ($num != 0) {
        echo '您已经绑定，请勿重复提交';
        exit;
    }

    $wxdb->query($wxdb->prepare("select * from `user` where userId = '%s'", $stuid));
    $result = $wxdb->last_result;
    $num = $wxdb->num_rows;
    if ($num != 0) {
        $row = $result[0];
        if ($row->userName == $identity) {
            $result = $wxdb->update('user', array('openid' => $openid), array('userId' => $stuid));
            if ($result === false)
                echo '绑定失败';
            else
                echo 0;
        } else {
            echo '身份验证失败';
        }
    } else {
        echo '无法找到此学号，请检查您的输入或联系管理员';
    }
    exit;
}

echo '绑定失败';
exit;
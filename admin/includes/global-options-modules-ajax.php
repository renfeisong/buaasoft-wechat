<?php
/**
 * Handling AJAX request from Module Settings Panel (global-options-modules.php)
 *
 * @author Renfei Song
 */

require_once dirname(__FILE__) . "/admin.php";

global $wxdb; /* @var $wxdb wxdb */

// Security check
$ajax_key = get_global_value('options_module_ajax');
if (sha1(AJAX_SALT . $ajax_key) != @$_GET['auth']) {
    header($_SERVER['SERVER_PROTOCOL'] . " 401 Unauthorized");
    echo ' 权限验证失败。';
    exit;
}

if (strval(intval($_POST['value'])) === $_POST['value']) {
    $old_value = get_global_value('priority_' . $_POST['pk']);
    $value = intval($_POST['value']);
    if ($value >= 0 && $value <= 65535) {
        set_global_value('priority_' . $_POST['pk'], $value);
        $wxdb->insert('security_log', array(
            'userName' => current_user_name(),
            'opName' => 'Module.setPriority',
            'opDetail' => 'Success: Priority for module [' . $_POST["pk"] . '] set from [' . $old_value . '] to [' . $value . ']',
            'ip' => $_SERVER['REMOTE_ADDR'],
            'agent' => $_SERVER['HTTP_USER_AGENT']
        ));
        exit;
    }
}

header($_SERVER['SERVER_PROTOCOL'] . " 403 Forbidden");
echo ' 权重必须为 0~65535 之间的整数，请重试。';
exit;
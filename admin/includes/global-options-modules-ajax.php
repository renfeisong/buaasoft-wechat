<?php

/**
 * Handling AJAX request from Module Settings Panel (global-options-modules.php)
 *
 * @author Renfei Song
 */

require_once dirname(dirname(dirname(__FILE__))) . '/config.php';

// Security check
if (sha1(AJAX_SALT) != @$_GET['auth']) {
    header($_SERVER['SERVER_PROTOCOL'] . " 401 Unauthorized");
    echo <<<HTML
<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<html><head><title>401 Unauthorized</title></head>
<body><h1>401 Unauthorized</h1><p>Your request has been denied by the server. Back off.</p></body></html>
HTML;
    exit;
}

// Currently allows input such as '01', and will convert to '1'.
// Change '==' to '===' to disallow it.

if (strval(intval($_POST['value'])) == $_POST['value']) {
    $value = intval($_POST['value']);
    if ($value >= 0 && $value <= 65535) {
        set_global_value('priority_' . $_POST['pk'], $value);
        exit;
    }
}

header($_SERVER['SERVER_PROTOCOL'] . " 403 Forbidden");
echo ' 权重必须为 0~65535 之间的整数，请重试。';
exit;
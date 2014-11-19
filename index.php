<?php
/**
 * Starting point.
 *
 * @author Renfei Song
 * @since 2.0.0
 */

require_once dirname(__FILE__) . "/config.php";

global $wxdb;
if (system_ready() == false) {
    header('Location: admin/install.php');
    exit;
}

if (isset($GLOBALS["HTTP_RAW_POST_DATA"]) == false) {
    header('Location: admin/index.php');
    exit;
}

$receiver = new MessageReceiver();

$success = $receiver->receive();

if ($success === false) {
    header($_SERVER['SERVER_PROTOCOL'] . " 400 Bad request");
    echo <<<HTML
<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<html>
<head>
<title>400 Bad request</title>
</head>
<body>
<h1>400 Bad request</h1>
<p>The request could not be fulfilled due to the incorrect syntax of the request.</p>
<p>{$receiver->error_msg}</p>
</body>
</html>
HTML;
    exit;
}
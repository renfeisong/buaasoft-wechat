<?php
/**
 * Starting point.
 *
 * @author Renfei Song
 * @since 2.0.0
 */

require_once dirname(__FILE__) . "/config.php";

$receiver = new MessageReceiver();

$error = $receiver->receive();

if ($error !== true) {
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
<p>$error</p>
</body>
</html>
HTML;
    exit;
}
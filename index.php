<?php
/**
 * Starting point.
 *
 * @author Renfei Song
 * @since 2.0.0
 */

require_once dirname(__FILE__) . "/config.php";

$receiver = new MessageReceiver();

$success = $receiver->receive();

if (!$success) {
    header($_SERVER['SERVER_PROTOCOL'] . " 405 Method not allowed");
    echo <<<HTML
<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<html><head><title>405 Method not allowed</title></head>
<body><h1>405 Method not allowed</h1><p>The resource was requested using a method that is not allowed. For example, requesting a resource via a POST method when the resource only supports the GET method.</p></body></html>
HTML;
    exit;
}
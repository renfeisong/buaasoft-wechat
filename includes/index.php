<?php
/**
 * Starting point.
 *
 * @author Renfei Song
 * @since 1.0
 */

require_once "wxdb.php";
require_once "module.php";
require_once "OutputFormatter.php";

$wxdb = new wxdb('root','root','weixin','localhost');
$modules = array();
$input = new UserInput();

load_modules();

$post = $GLOBALS['HTTP_RAW_POST_DATA'];

if (!empty($post)) {
    $object = simplexml_load_string($post, 'SimpleXMLElement', LIBXML_NOCDATA);
    $input->openid = $object->FromUserName;
    $input->accountId = $object->ToUserName;
    switch ($object->MsgType) {
        case "text":
            $input->inputType = InputType::Text;
            $input->content = trim($object->Content);
            break;
        case "voice":
            $input->inputType = InputType::Voice;
            $input->format = $object->Format;
            $input->recognition = $object->Recognition;
            break;
        case "image":
            $input->inputType = InputType::Image;
            $input->mediaId = $object->MediaId;
            break;
        case "video":
            $input->inputType = InputType::Video;
            $input->mediaId = $object->MediaId;
            $input->thumbMediaId = $object->ThumbNediaId;
            break;
        case "location":
            $input->inputType = InputType::Location;
            $input->latitude = $object->Location_X;
            $input->longitude = $object->Location_Y;
            $input->scale = $object->Scale;
            $input->label = $object->Label;
            break;
        case "link":
            $input->inputType = InputType::Link;
            $input->title = $object->Title;
            $input->description = $object->Description;
            $input->url = $object->Url;
            break;
        case "event":
            if ($object->Event == "subscribe") {
                $input->inputType = InputType::Subscribe;
                $input->eventKey = $object->EventKey;
                $input->ticket = $object->Ticket;
            } elseif ($object->Event == "unsubscribe") {
                $input->inputType = InputType::Unsubscribe;
            } elseif ($object->Event == "CLICK") {
                $input->inputType = InputType::Click;
                $input->eventKey = $object->EventKey;
            } elseif ($object->Event == "VIEW") {
                $input->inputType = InputType::View;
                $input->eventKey = $object->EventKey;
            } elseif ($object->Event == "SCAN") {
                $input->inputType = InputType::Scan;
                $input->eventKey = $object->EventKey;
                $input->ticket = $object->Ticket;
            } elseif ($object->Event == "LOCATION") {
                $input->inputType = InputType::LocationReport;
                $input->latitude = $object->Latitude;
                $input->longitude = $object->Longitude;
                $input->precision = $object->Precision;
            }
            break;
    }
}

foreach ($modules as $module) {
    if ($module->can_handle_input($input)) {
        echo $module->raw_output($input);
        break;
    }
}
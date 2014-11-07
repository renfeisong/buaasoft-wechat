<?php
/**
 * MessageReceiver Class
 *
 * @author Renfei Song
 * @since 2.0.0
 */

class MessageReceiver {

    public $input;

    public function receive() {
        $this->input = new UserInput();

        $post = @$GLOBALS['HTTP_RAW_POST_DATA'];

        if (empty($post)) {
            return 'Empty HTTP_RAW_POST_DATA.';
        }

        $object = @simplexml_load_string($post, 'SimpleXMLElement', LIBXML_NOCDATA);

        if ($object == false) {
            $error_msg = 'Error when parsing XML string.';
            $last_error = libxml_get_last_error();
            if (isset($last_error->message)) {
                $error_msg .= ' Error message: ' . $last_error->message;
            }
            return $error_msg;
        }

        $this->input->openid = trim($object->FromUserName);
        $this->input->accountId = trim($object->ToUserName);

        global $wxdb; /* @var $wxdb wxdb */

        $sql = $wxdb->prepare("SELECT * FROM `user` WHERE `identifyId` = '%s'", $this->input->openid);
        $this->user = $wxdb->get_row($sql, ARRAY_A);

        switch (trim($object->MsgType)) {
            case "text":
                $this->input->inputType = InputType::Text;
                $this->input->content = trim($object->Content);
                break;
            case "voice":
                $this->input->inputType = InputType::Voice;
                $this->input->format = $object->Format;
                $this->input->recognition = $object->Recognition;
                break;
            case "image":
                $this->input->inputType = InputType::Image;
                $this->input->mediaId = $object->MediaId;
                break;
            case "video":
                $this->input->inputType = InputType::Video;
                $this->input->mediaId = $object->MediaId;
                $this->input->thumbMediaId = $object->ThumbNediaId;
                break;
            case "location":
                $this->input->inputType = InputType::Location;
                $this->input->latitude = $object->Location_X;
                $this->input->longitude = $object->Location_Y;
                $this->input->scale = $object->Scale;
                $this->input->label = $object->Label;
                break;
            case "link":
                $this->input->inputType = InputType::Link;
                $this->input->title = $object->Title;
                $this->input->description = $object->Description;
                $this->input->url = $object->Url;
                break;
            case "event":
                if ($object->Event == "subscribe") {
                    $this->input->inputType = InputType::Subscribe;
                    $this->input->eventKey = $object->EventKey;
                    $this->input->ticket = $object->Ticket;
                } elseif ($object->Event == "unsubscribe") {
                    $this->input->inputType = InputType::Unsubscribe;
                } elseif ($object->Event == "CLICK") {
                    $this->input->inputType = InputType::Click;
                    $this->input->eventKey = $object->EventKey;
                } elseif ($object->Event == "VIEW") {
                    $this->input->inputType = InputType::View;
                    $this->input->eventKey = $object->EventKey;
                } elseif ($object->Event == "SCAN") {
                    $this->input->inputType = InputType::Scan;
                    $this->input->eventKey = $object->EventKey;
                    $this->input->ticket = $object->Ticket;
                } elseif ($object->Event == "LOCATION") {
                    $this->input->inputType = InputType::LocationReport;
                    $this->input->latitude = $object->Latitude;
                    $this->input->longitude = $object->Longitude;
                    $this->input->precision = $object->Precision;
                }
                break;
            default:
                return 'Invalid MsgType `'.$object->MsgType.'`. MsgType must be one of the following: text, voice, video, image, location, link, event.';
        }

        do_actions('message_received', array($this->input));

        $catched = false;

        global $modules;

        foreach ($modules as $module) {
            /* @var $module BaseModule */
            if ($module->can_handle_input($this->input)) {
                $catched = true;
                do_actions('module_hit', array($this->input, get_class($module)));
                echo $module->raw_output($this->input);
                break;
            }
        }

        if ($catched == false) {
            do_actions('modules_missed', array($this->input));
        }

        return true;
    }
}
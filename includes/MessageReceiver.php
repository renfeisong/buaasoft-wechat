<?php
/**
 * MessageReceiver Class
 *
 * @author Renfei Song
 * @since 2.0.0
 */

class MessageReceiver {

    public $error_msg;

    /**
     * Try to receive post data and respond to it.
     *
     * @return bool false on failure or true on success
     */
    public function receive() {
        $post = @$GLOBALS['HTTP_RAW_POST_DATA'];

        if (empty($post)) {
            $this->error_msg = 'Empty HTTP_RAW_POST_DATA.';
            return false;
        }

        $input = $this->process_post($post);

        if ($input == false) {
            return false;
        }

        $hit = false;
        $hit_by = null;
        $response = null;

        global $modules;

        foreach ($modules as $module) {
            /* @var $module BaseModule */
            if ($module->can_handle_input($input)) {
                $hit = true;
                $hit_by = get_class($module);
                $response = $module->raw_output($input);
                break;
            }
        }

        echo $response;

        do_actions('message_received', array($input, $hit, $hit_by, $response));

        return true;
    }

    /**
     * Get raw HTTP POST string and return processed UserUnput object.
     *
     * If any error occurs, this function will return false and the $error_msg member variable will be set.
     *
     * @param $post string raw HTTP POST string
     *
     * @return bool|UserInput Object on success or false on failure.
     */
    public function process_post($post) {
        $input = new UserInput();
        $input->rawXml = $post;

        $object = @simplexml_load_string($post, 'SimpleXMLElement', LIBXML_NOCDATA);

        if ($object === false) {
            $error_msg = 'Error when parsing XML string.';
            $last_error = libxml_get_last_error();
            if (isset($last_error->message)) {
                $error_msg .= ' Error message: ' . $last_error->message;
            }
            $this->error_msg = $error_msg;
            return false;
        }

        $input->openid = trim($object->FromUserName);
        $input->accountId = trim($object->ToUserName);
        $input->msgType = trim($object->MsgType);
        $input->initiateMethod = $object->InitiateMethod == null ? 'default' : trim($object->InitiateMethod);

        global $wxdb; /* @var $wxdb wxdb */

        $sql = $wxdb->prepare("SELECT * FROM `user` WHERE `openid` = '%s'", $input->openid);
        $input->user = $wxdb->get_row($sql, ARRAY_A);

        switch (trim($object->MsgType)) {
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
                $input->event = $object->Event;
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
                } else {
                    $input->inputType = InputType::Unsupported; // Unsupported Event
                }
                break;
            default:
                $input->inputType = InputType::Unsupported; // Unsupported MsgType
                break;
        }

        return $input;
    }
}
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
        $post = $GLOBALS['HTTP_RAW_POST_DATA'];

        if (!empty($post)) {
            $object = simplexml_load_string($post, 'SimpleXMLElement', LIBXML_NOCDATA);
            $this->input->openid = $object->FromUserName;
            $this->input->accountId = $object->ToUserName;
            switch ($object->MsgType) {
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
            }
        }

        apply_filters('message_received', array($this->input));
    }
}
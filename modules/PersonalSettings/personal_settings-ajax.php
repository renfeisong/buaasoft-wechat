<?php
/**
 * Ajax page for personal setting page.
 *
 * @author Bingchen Qin
 * @since 2.0.0
 */

require_once dirname(dirname(dirname(__FILE__))) . "/config.php";

if (isset($_POST["action"])) {
    $return_dict = array();
    global $wxdb;

    switch ($_POST["action"]) {
        case "edit": {
            if (preg_match("^[a-zA-Z0-9_.-]+@[a-zA-Z0-9-]+.[a-zA-Z0-9-.]+$", $_POST["phone_number"])) {
                $return_dict["code"] = 1;
                $return_dict["message"] = "wrong email";
                goto complete;
            }
            if (preg_match("/^1[3|4|5|8][0-9]\\d{8}$/", $_POST["phone_number"])) {
                $return_dict["code"] = 2;
                $return_dict["message"] = "wrong phone number";
                goto complete;
            }

            $wxdb->update("user", array("phoneNumber"=>$_POST["phone_number"]), array("openid"=>$_POST["openid"]));
            $wxdb->update("user", array("email"=>$_POST["email"]), array("openid"=>$_POST["openid"]));

            complete:
            $return_dict["code"] = 0;
            $return_dict["message"] = "success";
            echo json_encode($return_dict);
            break;
        }
        default: break;
    }
}
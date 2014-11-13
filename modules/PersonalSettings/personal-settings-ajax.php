<?php
/**
 * Ajax page for personal setting page.
 *
 * @author Bingchen Qin
 * @since 2.0.0
 */

require_once dirname(dirname(__FILE__)) . '/config.php';

if (isset($_POST["action"])) {
    $return_dict = array();
    global $wxdb;

    switch ($_POST["action"]) {
        case "edit": {
            echo $_POST["mobile"];
            echo $_POST["email"];
            $wxdb->update("contact", array("phone_number"=>$_POST["mobile"]), array("id"=>$_POST["id"]));
            $wxdb->update("contact", array("email"=>$_POST["email"]), array("id"=>$_POST["id"]));
            $return_dict["code"] = 0;
            $return_dict["message"] = "success";
            echo json_encode($return_dict);
            break;
        }
        default: break;
    }
}
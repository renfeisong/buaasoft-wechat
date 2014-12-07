<?php
/**
 * Ajax page for contact query module.
 *
 * @author Bingchen Qin
 * @since 2.0.0
 */

require_once dirname(dirname(dirname(__FILE__))) . '/config.php';

if (isset($_GET["action"])) {
    $return_dict = array();
    global $wxdb; /* @var $wxdb wxdb */

    switch ($_GET["action"]) {
        case "add-record": {
            $result = $wxdb->insert("contact", array(
                "userName"=>$_POST["user_name"],
                "identity"=>$_POST["identity"],
                "phoneNumber"=>$_POST["phone_number"],
                "email"=>$_POST["email"]));
            if ($result != false) {
                if ($result != 0) {
                    $return_dict["code"] = 0;
                    $return_dict["message"] = "success";
                    $return_dict["id"] = $wxdb->insert_id;
                } else {
                    $return_dict["code"] = 1;
                    $return_dict["message"] = "already added";
                }
            } else {
                $return_dict["code"] = 2;
                $return_dict["message"] = "error";
            }
            break;
        }
        case "delete-record": {
            $result = $wxdb->delete("contact", array("id"=>$_POST["id"]));
            if ($result != false) {
                if ($result != 0) {
                    $return_dict["code"] = 0;
                    $return_dict["message"] = "success";
                } else {
                    $return_dict["code"] = 1;
                    $return_dict["message"] = "already deleted";
                }
            } else {
                $return_dict["code"] = 2;
                $return_dict["message"] = "error";
            }
            break;
        }
        case "edit-user-name": {
            if ($_POST["value"] == "") {
                header($_SERVER['SERVER_PROTOCOL'] . " 403 Forbidden");
                echo " 姓名不能为空";
                exit;
            }
            if ($wxdb->update("contact", array("userName"=>$_POST["value"]), array("id"=>$_POST["pk"])) == false) {
                header($_SERVER['SERVER_PROTOCOL'] . " 403 Forbidden");
                echo " 出现未知错误";
                exit;
            }
            break;
        }
        case "edit-identity": {
            if ($wxdb->update("contact", array("identity"=>$_POST["value"]), array("id"=>$_POST["pk"])) == false) {
                header($_SERVER['SERVER_PROTOCOL'] . " 403 Forbidden");
                echo " 出现未知错误";
                exit;
            }
            break;
        }
        case "edit-phone-number": {
            if ($_POST["value"] != "" && !preg_match("/^1[3|4|5|7|8]\\d{9}$/", $_POST["value"])) {
                header($_SERVER['SERVER_PROTOCOL'] . " 403 Forbidden");
                echo " 手机号格式不正确";
                exit;
            }
            if ($wxdb->update("contact", array("phoneNumber"=>$_POST["value"]), array("id"=>$_POST["pk"])) == false) {
                header($_SERVER['SERVER_PROTOCOL'] . " 403 Forbidden");
                echo " 出现未知错误";
                exit;
            }
            break;
        }
        case "edit-email": {
            if ($_POST["value"] != "" && !preg_match("/^[a-zA-Z0-9_.-]+@[a-zA-Z0-9-]+.[a-zA-Z0-9-.]+$/", $_POST["value"])) {
                header($_SERVER['SERVER_PROTOCOL'] . " 403 Forbidden");
                echo " 邮箱格式不正确";
                exit;
            }
            if ($wxdb->update("contact", array("email"=>$_POST["value"]), array("id"=>$_POST["pk"])) == false) {
                header($_SERVER['SERVER_PROTOCOL'] . " 403 Forbidden");
                echo " 出现未知错误";
                exit;
            }
            break;
        }
        case "edit-format": {
            _set_value("Contact", "output_format", $_POST["format"]);
            $return_dict["code"] = 0;
            $return_dict["message"] = "success";
            break;
        }
        default: {
            $return_dict["code"] = 1;
            $return_dict["message"] = "error";
            break;
        }
    }
    echo json_encode($return_dict);
}
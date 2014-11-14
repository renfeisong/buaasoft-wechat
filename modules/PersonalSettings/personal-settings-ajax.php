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
            $row = $wxdb->get_row("SELECT * FROM user WHERE openid = '" . $input->openid . "'", ARRAY_A, 0);
            $id = $row["stu_id"];
            $wxdb->update("contact", array("phone_number"=>$_POST["mobile"]), array("stu_id"=>$id));
            $wxdb->update("contact", array("email"=>$_POST["email"]), array("stu_id"=>$id));
            $return_dict["code"] = 0;
            $return_dict["message"] = "success";
            echo json_encode($return_dict);
            break;
        }
        default: break;
    }
}
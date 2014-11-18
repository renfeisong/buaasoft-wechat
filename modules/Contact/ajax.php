<?php
/**
 * Ajax page for contact query module.
 *
 * @author Bingchen Qin
 * @since 2.0.0
 */

require_once dirname(dirname(dirname(__FILE__))) . '/config.php';

if (isset($_POST["action"])) {
    $return_dict = array();

    switch ($_POST["action"]) {
        case "edit": {
            _set_value("Contact", "output_format", $_POST["format"]);
            $return_dict["code"] = 0;
            $return_dict["message"] = "success";
            echo json_encode($return_dict);
            break;
        }
        default: break;
    }
}
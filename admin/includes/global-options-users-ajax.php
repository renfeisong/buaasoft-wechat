<?php

require_once dirname(__FILE__) . '/admin.php';
require_once dirname(dirname(dirname(__FILE__))) . '/config.php';
global $wxdb;

if (isset($_POST["action"])) {
    switch ($_POST["action"]) {
        case "edit-permission": {
            $return_dict = array();
            global $global_options;
            global $modules;
            $reverted_tags = array();
            foreach ($global_options as $name => $display_name) {
                $reverted_tags[$display_name] = $name;
            }
            foreach ($modules as $module) {
                if (has_settings_page($module)) {
                    $reverted_tags[$module->display_name()] = get_class($module);
                }
            }
            $permission_list = array();
            foreach ($_POST["permission"] as $permission) {
                array_push($permission_list, $reverted_tags[$permission]);
            }

            $result = $wxdb->update("admin_user", array("authorizedPages"=>json_encode($permission_list)), array("userName"=>$_POST["username"]));
            if (false !== $result) {
                $return_dict["code"] = 0;
                $return_dict["message"] = "success";
            } else {
                $return_dict["code"] = 1;
                $return_dict["message"] = "error";
            }
            echo json_encode($return_dict);
            sleep(2);
            break;
        }
        case "enable": {
            $return_dict = array();
            $result = $wxdb->update("admin_user", array("isEnabled"=>"1"), array("userName"=>$_POST["username"]));
            if (false !== $result) {
                if ($result != 0) {
                    $return_dict["code"] = 0;
                    $return_dict["message"] = "success";
                } else {
                    $return_dict["code"] = 1;
                    $return_dict["message"] = "already enabled";
                }
            } else {
                $return_dict["code"] = 2;
                $return_dict["message"] = "error";
            }
            echo json_encode($return_dict);
            sleep(2);
            break;
        }
        case "disable": {
            $return_dict = array();
            $result = $wxdb->update("admin_user", array("isEnabled"=>"0"), array("userName"=>$_POST["username"]));
            if (false !== $result) {
                if ($result != 0) {
                    $return_dict["code"] = 0;
                    $return_dict["message"] = "success";
                } else {
                    $return_dict["code"] = 1;
                    $return_dict["message"] = "already disabled";
                }
            } else {
                $return_dict["code"] = 2;
                $return_dict["message"] = "error";
            }
            echo json_encode($return_dict);
            sleep(2);
            break;
        }
        case "delete": {
            $return_dict = array();
            $result = $wxdb->delete("admin_user", array("userName"=>$_POST["username"]));
            if (false !== $result) {
                if ($result != 0) {
                    $return_dict["code"] = 0;
                    $return_dict["message"] = "success";
                } else {
                    $return_dict["code"] = 1;
                    $return_dict["message"] = "not exist or already deleted";
                }
            } else {
                $return_dict["code"] = 2;
                $return_dict["message"] = "error";
            }
            echo json_encode($return_dict);
            sleep(2);
            break;
        }
        default: {
        break;
        }
    }
}

?>
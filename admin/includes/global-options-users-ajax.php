<?php
/**
 * This file provides ajax services for the user management page.
 *
 * @author Bingchen Qin
 * @since 2.0.0
 */


require_once dirname(__FILE__) . '/admin.php';
global $wxdb; /* @var $wxdb wxdb */

if (isset($_POST["action"])) {
    switch ($_POST["action"]) {
        case "edit-permission": {
            $return_dict = array();
            global $global_options;
            $modules = get_modules();
            $reverted_tags = array();
            foreach ($global_options as $name => $display_name) {
                $reverted_tags[$display_name] = $name;
            }
            foreach ($modules as $module) {
                if (has_settings_page($module["name"])) {
                    $display_name= _get_value("global", "display_name_" . $module["name"]);
                    if ($display_name == null) {
                        $display_name = $module["name"];
                    }
                    $reverted_tags[$display_name] = $module["name"];
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
                $wxdb->insert('security_log', array(
                    'userName' => current_user_name(),
                    'opName' => 'User.setPrivileges',
                    'opDetail' => 'Success: Privileges for user [' . $_POST["username"] . '] set to [' . implode(', ', $_POST["permission"]) . ']',
                    'ip' => $_SERVER['REMOTE_ADDR'],
                    'agent' => $_SERVER['HTTP_USER_AGENT']
                ));
            } else {
                $return_dict["code"] = 1;
                $return_dict["message"] = "error";
            }
            echo json_encode($return_dict);
            break;
        }
        case "enable": {
            $return_dict = array();
            $result = $wxdb->update("admin_user", array("isEnabled"=>"1"), array("userName"=>$_POST["username"]));
            if (false !== $result) {
                if ($result != 0) {
                    $return_dict["code"] = 0;
                    $return_dict["message"] = "success";
                    $wxdb->insert('security_log', array(
                        'userName' => current_user_name(),
                        'opName' => 'User.enable',
                        'opDetail' => 'Success: User [' . $_POST["username"] . '] enabled',
                        'ip' => $_SERVER['REMOTE_ADDR'],
                        'agent' => $_SERVER['HTTP_USER_AGENT']
                    ));
                } else {
                    $return_dict["code"] = 1;
                    $return_dict["message"] = "already enabled";
                }
            } else {
                $return_dict["code"] = 2;
                $return_dict["message"] = "error";
            }
            echo json_encode($return_dict);
            break;
        }
        case "disable": {
            $return_dict = array();
            $result = $wxdb->update("admin_user", array("isEnabled"=>"0"), array("userName"=>$_POST["username"]));
            if (false !== $result) {
                if ($result != 0) {
                    $return_dict["code"] = 0;
                    $return_dict["message"] = "success";
                    $wxdb->insert('security_log', array(
                        'userName' => current_user_name(),
                        'opName' => 'User.disable',
                        'opDetail' => 'Success: User [' . $_POST["username"] . '] disabled',
                        'ip' => $_SERVER['REMOTE_ADDR'],
                        'agent' => $_SERVER['HTTP_USER_AGENT']
                    ));
                } else {
                    $return_dict["code"] = 1;
                    $return_dict["message"] = "already disabled";
                }
            } else {
                $return_dict["code"] = 2;
                $return_dict["message"] = "error";
            }
            echo json_encode($return_dict);
            break;
        }
        case "delete": {
            $return_dict = array();
            $result = $wxdb->delete("admin_user", array("userName"=>$_POST["username"]));
            if (false !== $result) {
                if ($result != 0) {
                    $return_dict["code"] = 0;
                    $return_dict["message"] = "success";
                    $wxdb->insert('security_log', array(
                        'userName' => current_user_name(),
                        'opName' => 'User.delete',
                        'opDetail' => 'Success: User [' . $_POST["username"] . '] deleted',
                        'ip' => $_SERVER['REMOTE_ADDR'],
                        'agent' => $_SERVER['HTTP_USER_AGENT']
                    ));
                } else {
                    $return_dict["code"] = 1;
                    $return_dict["message"] = "not exist or already deleted";
                }
            } else {
                $return_dict["code"] = 2;
                $return_dict["message"] = "error";
            }
            echo json_encode($return_dict);
            break;
        }
        default: {
        break;
        }
    }
}

?>
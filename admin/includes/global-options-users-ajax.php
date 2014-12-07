<?php
/**
 * This file provides ajax services for the user management page.
 *
 * @author Bingchen Qin
 * @author Renfei Song
 * @since 2.0.0
 */

require_once dirname(__FILE__) . '/admin.php';
global $wxdb; /* @var $wxdb wxdb */

if (isset($_GET["action"])) {
    $return_dict = array();
    switch ($_GET["action"]) {
        case "edit-permission": {
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
            global $public_pages;
            foreach ($public_pages as $public_page) {
                unset($reverted_tags[array_search($public_page, $reverted_tags)]);
            }
            $permission_list = array();
            foreach ($_POST["value"] as $permission) {
                array_push($permission_list, $reverted_tags[$permission]);
            }

            if (is_super_admin()) {
                $operator_permissions = array_values($reverted_tags);
            } else {
                $sql = $wxdb->prepare("select authorizedPages from admin_user where userName = '%s'", current_user_name());
                $operator_permissions = json_decode($wxdb->get_var($sql));
            }
            $sql = $wxdb->prepare("select authorizedPages from admin_user where userName = '%s'", $_POST['pk']);
            $original_permissions = json_decode($wxdb->get_var($sql));
            foreach ($_POST["value"] as $permission) {
                if (in_array($reverted_tags[$permission], $original_permissions, true) == false
                    && in_array($reverted_tags[$permission], $operator_permissions, true) == false) {
                    header($_SERVER['SERVER_PROTOCOL'] . " 403 Forbidden");
                    echo " 无法添加当前用户没有的权限";
                    exit;
                }
            }
            foreach (array_diff($original_permissions, $operator_permissions) as $permission) {
                if (in_array($permission, $permission_list, true) == false) {
                    header($_SERVER['SERVER_PROTOCOL'] . " 403 Forbidden");
                    echo " 无法删除当前用户没有的权限";
                    exit;
                }
            }

            $result = $wxdb->update("admin_user", array("authorizedPages"=>json_encode($permission_list)), array("userName"=>$_POST["pk"]));
            if (false !== $result) {
                $return_dict["code"] = 0;
                $return_dict["message"] = "success";
                $wxdb->insert('security_log', array(
                    'userName' => current_user_name(),
                    'opName' => 'User.setPrivileges',
                    'opDetail' => 'Success: Privileges for user [' . $_POST["pk"] . '] set from ' . json_encode($original_permissions) . ' to ' . json_encode($permission_list),
                    'ip' => $_SERVER['REMOTE_ADDR'],
                    'agent' => $_SERVER['HTTP_USER_AGENT']
                ));
            } else {
                $return_dict["code"] = 3;
                $return_dict["message"] = "error";
            }
            break;
        }
        case "edit-note": {
            $success = $wxdb->update('admin_user', array('note' => $_POST['value']), array('userName' => $_POST['pk']));
            if ($success !== false) {
                $wxdb->insert('security_log', array(
                    'userName' => current_user_name(),
                    'opName' => 'User.editNote',
                    'opDetail' => 'Success: User [' . $_POST["pk"] . ']\'s note set to [' . $_POST['value'] . ']',
                    'ip' => $_SERVER['REMOTE_ADDR'],
                    'agent' => $_SERVER['HTTP_USER_AGENT']
                ));
                $return_dict["code"] = 0;
                $return_dict["message"] = "success";
            } else {
                header($_SERVER['SERVER_PROTOCOL'] . " 403 Forbidden");
                echo " 系统错误";
                exit;
            }
            break;
        }
        case "enable": {
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
            break;
        }
        case "disable": {
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
            break;
        }
        case "delete": {
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
            break;
        }
        default: {
            break;
        }
    }
    echo json_encode($return_dict);
}

?>
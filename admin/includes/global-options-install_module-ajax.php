<?php
/**
 * This file provides ajax services for the user management page.
 *
 * @author Bingchen Qin
 * @since 2.0.0
 *
 * Error list:
 *
 * 0 success
 * 1 upload error
 * 2 size limit exceeded
 * 3 type not zip
 * 4 already exists
 * 5 extract error
 * 6 file names not match
 * 7 index.php not found
 * 8 class name and file name not identical
 * 9 not subclass of BaseClass
 *
 * 100 unknown error
 */

require_once dirname(__FILE__) . "/admin.php";
global $wxdb;

$module_dir = dirname(dirname(dirname(__FILE__))) . "/modules/";

$return_dict = array();
$error_message = array(0 => "success",
                       1 => "upload error",
                       2 => "size limit exceeded",
                       3 => "type not zip",
                       4 => "already exists",
                       5 => "extract error",
                       6 => "file name and class name not match",
                       7 => "index.php not found",
                       //8 => "class name and file name not identical ---->  case 6",
                       8 => "not subclass of BaseClass",
                     100 => "unknown error");

if (!isset($_FILES["file"]["error"]) || is_array($_FILES["file"]["error"])) {
    $return_dict["code"] = 1;
    goto complete;
}

if ($_FILES["file"]["error"] == UPLOAD_ERR_INI_SIZE || $_FILES["file"]["error"] == UPLOAD_ERR_FORM_SIZE) {
    $return_dict["code"] = 2;
    goto complete;
}

if ($_FILES["file"]["error"] == UPLOAD_ERR_OK) {
    $module_name = basename($_FILES["file"]["name"], ".zip");
    $temp_dir = dirname(dirname(dirname(__FILE__))) . "/temp/" . $module_name . "_" . md5(rand(1000, 1000000)) . "_" . date("YmdHis", time()) . "/";
    $temp_file = $temp_dir . $_FILES["file"]["name"];

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $file_type = finfo_file($finfo, $_FILES["file"]['tmp_name']);
    finfo_close($finfo);
    if ($file_type != "application/zip") {
        $return_dict["code"] = 3;
        goto complete;
    }

    if (file_exists($module_dir . $module_name)) {
        $return_dict["code"] = 4;
        goto complete;
    }

    mkdir($temp_dir);
    move_uploaded_file($_FILES["file"]["tmp_name"], $temp_file);
    $zip = new ZipArchive();
    if ($zip->open($temp_file) !== true) {
        $return_dict["code"] = 5;
        goto complete;
    }

    if (!$zip->extractTo($temp_dir)) {
        $return_dict["code"] = 5;
        goto complete;
    }

    $zip->close();
    //unlink($temp_file);
    if (!file_exists($temp_dir . $module_name)) {
        $return_dict["code"] = 6;
        goto complete;
    }

    if (!file_exists($temp_dir . $module_name . "/index.php")) {
        $return_dict["code"] = 7;
        goto complete;
    }

    ob_start();
    require_once($temp_dir . $module_name . "/index.php");
    ob_end_clean();
    if (!class_exists($module_name)) {
        $return_dict["code"] = 6;
        goto complete;
    }

    //TODO check class name --- not easy

    $module = new $module_name;
    if (!is_subclass_of($module, "BaseModule")) {
        $return_dict["code"] = 9;
        goto complete;
    }

    rename($temp_dir . $module_name, $module_dir . $module_name);
    $return_dict["code"] = 0;
    goto complete;
}

$return_dict["code"] = 1;

complete:
$return_dict["message"] = $error_message[$return_dict["code"]];
echo json_encode($return_dict);

?>
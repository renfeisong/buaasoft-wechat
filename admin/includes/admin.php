<?php
/**
 * This file includes everything needed and defines both public and private Admin Panel API.
 *
 * @author Renfei Song
 * @since 2.0.0
 */

require_once dirname(dirname(dirname(__FILE__))) . '/config.php';

///// Internal API

function current_user() {
    $user = $_COOKIE['user'];
    $token = $_COOKIE['token'];

    global $wxdb; /* @var $wxdb wxdb */
    $sql = $wxdb->prepare("SELECT * FROM `admin_user` WHERE userName = '%s'", $user);
    $user = $wxdb->get_row($sql, ARRAY_A);
    if ($user) {
        if (sha1(LOGIN_SALT . $user['userName']) == $token) {
            return $user;
        }
    }

    return null;
}

function current_user_id() {
    return ($user = current_user()) == null ? 0 : $user['id'];
}

function current_user_name() {
    return ($user = current_user()) == null ? null : $user['userName'];
}

function is_admin() {
    return ($user = current_user()) != null && ($user['userType'] == 1 || $user['userType'] == 0);
}

function is_superadmin() {
    return ($user = current_user()) != null && $user['userType'] == 0;
}

function is_disabled() {
    return ($user = current_user()) != null && $user['userType'] == 2;
}

function is_logged_in() {
    return current_user() != null;
}

function log_in($username, $password) {
    global $wxdb; /* @var $wxdb wxdb */
    $sql = $wxdb->prepare("SELECT * FROM `admin_user` WHERE userName = '%s' AND hashedPassword = '%s'", $username, sha1($password));
    $user = $wxdb->get_row($sql, ARRAY_A);
    if ($user) {
        setcookie('user', $user['userName'], time() + 3600 * 24 * 30);
        setcookie('token', sha1(LOGIN_SALT . $user['userName']), time() + 3600 * 24 * 30);
        return true;
    }
    return false;
}

function log_out() {
    setcookie('id', null, 0);
    setcookie('token', null, 0);
}

function register($username, $password) {
    global $wxdb; /* @var $wxdb wxdb */
    $success = $wxdb->insert('admin_user', array(
        'userName' => $username,
        'hashedPassword' => sha1($password),
        'userType' => 2
    ));
    return $success != false;
}

function redirect($location, $status = 302) {
    header("Location: " . $location, true, $status);
}

function has_settings_page(BaseModule $module) {
    return file_exists(ABSPATH . 'modules/' . get_class($module) . '/settings.php');
}

function settings_page_url(BaseModule $module) {
    return ROOT_URL . 'modules/' . get_class($module) . '/settings.php';
}

function include_settings_page($module_name) {
    if ($module_name == 'global')
        require_once ABSPATH . 'admin/includes/global-options.php';
    else
        require_once ABSPATH . 'modules/' . $module_name . '/settings.php';
}

function list_module_navigation_items() {
    echo '<ul>';

    global $modules;

    foreach ($modules as $module) {
        if (has_settings_page($module)) {
            /* @var $module BaseModule */
            $template = '<li class="module-navigation-item"><a href="%s">%s</a></li>';
            echo sprintf($template, ROOT_URL . 'admin/index.php?module=' . get_class($module), $module->display_name());
        }
    }

    echo '</ul>';
}

///// Public Admin Panel API

function submit_button($text = 'Submit', $class = '', $id = '') {
    $template = '<input type="submit" name="submit" value="%s" id="%s" class="%s">';
    echo sprintf($template, $text, $id, $class);
}
<?php
/**
 * This file includes everything needed and defines both public and private Admin Panel API.
 *
 * @author Renfei Song
 * @since 2.0.0
 */

require_once dirname(dirname(dirname(__FILE__))) . '/config.php';

///// Internal API

// Get strings

// User Mgmt.

function current_user() {
    if (!isset($_COOKIE['login'])) {
        return null;
    }

    global $adminUser;

    if ($adminUser != null)
        return $adminUser;

    global $wxdb; /* @var $wxdb wxdb */
    global $userChecked;

    $sql = $wxdb->prepare("SELECT * FROM `admin_user` WHERE `loginToken` = '%s'", $_COOKIE['login']);
    $user = $wxdb->get_row($sql, ARRAY_A);

    if ($user) {
        if ($_SERVER['REMOTE_ADDR'] == $user['ip']) {
            if ($userChecked === false) {
                $wxdb->update('admin_user', array('lastActivity' => date('c')), array('userName' => $user['userName']));

                $sql = $wxdb->prepare("select count(*) from `security_log` where `userName` = '%s' and `opName` = '%s' and `timestamp` > timestamp(DATE_SUB(NOW(), INTERVAL 20 MINUTE))", $user['userName'], 'User.startSession');
                $count = $wxdb->get_var($sql);
                if ($count == 0) {
                    $wxdb->insert('security_log', array(
                        'userName' => $user['userName'],
                        'opName' => 'User.startSession',
                        'opDetail' => 'Success',
                        'ip' => $_SERVER['REMOTE_ADDR'],
                        'agent' => $_SERVER['HTTP_USER_AGENT']
                    ));
                }

                $userChecked = true;
            }

            return $adminUser = $user;
        } else {
            $wxdb->insert('security_log', array(
                'userName' => $user['userName'],
                'opName' => 'User.forceLogout',
                'opDetail' => 'Info: Client IP changed from ['. $user['ip'] .'] to ['. $_SERVER['REMOTE_ADDR'] .']',
                'ip' => $_SERVER['REMOTE_ADDR'],
                'agent' => $_SERVER['HTTP_USER_AGENT']
            ));
            log_out();
        }
    }

    return $adminUser = null;
}

function current_user_name() {
    return ($user = current_user()) == null ? null : $user['userName'];
}

function current_user_can_manage($page) {
    global $public_pages;
    if (in_array($page, $public_pages))
        return true;

    $user = current_user();
    if ($user['isSuperAdmin'] == 1)
        return true;

    $authorized_pages = json_decode($user['authorizedPages']);
    return in_array($page, $authorized_pages);
}

function admin_unauthorized_error() {
    $html = <<<HTML
<div>您的账户当前无权管理本模块。</div>
HTML;
    echo $html;
}

function is_super_admin() {
    return ($user = current_user()) != null && $user['isSuperAdmin'] == 1;
}

function is_enabled() {
    return ($user = current_user()) != null && $user['isEnabled'] == 1;
}

function is_logged_in() {
    return current_user() != null;
}

function log_in($username, $password, $remember) {
    global $wxdb; /* @var $wxdb wxdb */
    $sql = $wxdb->prepare("SELECT * FROM `admin_user` WHERE userName = '%s' AND hashedPassword = '%s'", $username, sha1($password));
    $user = $wxdb->get_row($sql, ARRAY_A);
    if ($user) {
        $ip = $_SERVER['REMOTE_ADDR'];
        $token = sha1(LOGIN_SALT . $ip) . sha1(strval(rand(11111111, 99999999)) . $user['userName']);
        $wxdb->update('admin_user', array(
            'ip' => $ip,
            'loginToken' => $token
        ), array(
            'userName' => $username
        ));
        if ($remember) {
            setcookie('login', $token, time() + 3600 * 24 * 30);
        } else {
            setcookie('login', $token);
        }
        return true;
    }
    return false;
}

function log_out() {
    setcookie('login', '', 1);
    unset($_COOKIE['login']);
    global $adminUser;
    $adminUser = null;
}

function register($username, $password) {
    global $wxdb; /* @var $wxdb wxdb */
    $success = $wxdb->insert('admin_user', array(
        'userName' => $username,
        'hashedPassword' => sha1($password),
        'isEnabled' => 0,
        'joinDate' => date('c'),
        'lastActivity' => date('c'),
        'authorizedPages' => '[]',
        'isSuperAdmin' => 0
    ));
    return false != $success;
}

function changePassword($username, $password) {
    global $wxdb;  /* @var $wxdb wxdb */
    $success = $wxdb->update('admin_user', array(
        'hashedPassword' => sha1($password)
    ), array(
        'userName' => $username
    ));

    return false !== $success;
}

function passwordDisallowed($password) {
    // disallow passwords that only contain 1 kind of character
    $platitude = true;
    for ($i = 1; $i < strlen($password); ++$i) {
        if ($password[$i] !== $password[0])
            $platitude = false;
    }
    if ($platitude)
        return true;

    // disallow certain patterns
    $disallowList = array(
        "123456", "password", "qwerty"
    );
    foreach ($disallowList as $test)
        if ($test === $password)
            return true;

    return false;
}

function validatePassword($password) {
    if (strlen($password) < 6 || strlen($password) > 20)
        return 4; // 密码长度必须在6~20位之间

    if (preg_match("/[^A-Za-z0-9!@\#\$\%\^\&\*\_\-\+\=\(\)\[\]\{\}\<\>\|\\\?\,\.\;\:\'\"\/\~\`]/", $password))
        return 5; // 密码包含非法字符

    if (passwordDisallowed($password))
        return 6; // 该密码已被系统禁止使用

    return 0;
}

// Pages and Items

function has_settings_page($module) {
    if (is_object($module)) {
        return file_exists(ABSPATH . 'modules/' . get_class($module) . '/settings.php');
    } else {
        return file_exists(ABSPATH . 'modules/' . $module . '/settings.php');
    }
}

function settings_page_url(BaseModule $module) {
    return ROOT_URL . 'modules/' . get_class($module) . '/settings.php';
}

function include_settings($page_or_module_name) {
    global $global_options;
    if (array_key_exists($page_or_module_name, $global_options))
        require_once ABSPATH . 'admin/includes/global-options-' . $page_or_module_name . '.php';
    else
        require_once ABSPATH . 'modules/' . $page_or_module_name . '/settings.php';
}

function list_global_setting_items() {
    global $global_options;
    global $global_option_icons;
    foreach ($global_options as $slug_name => $display_name) {
        if (current_user_can_manage($slug_name)) {
            $icon_name = $global_option_icons[$slug_name];
            $class = $_GET['page'] == $slug_name ? 'current' : '';
            $template = '<li class="module-navigation-item %s"><a href="%s"><i class="fa fa-lg fa-fw fa-%s"></i>&nbsp; %s</a></li>';
            echo sprintf($template, $class, ROOT_URL . 'admin/index.php?page=' . $slug_name, $icon_name, $display_name);
        }
    }
}

function list_module_setting_items() {
    global $modules;
    foreach ($modules as $module) {
        if (has_settings_page($module) && current_user_can_manage(get_class($module))) {
            /* @var $module BaseModule */
            $class = $_GET['page'] == get_class($module) ? 'current' : '';
            $template = '<li class="module-navigation-item %s"><a href="%s">%s</a></li>';
            echo sprintf($template, $class, ROOT_URL . 'admin/index.php?page=' . get_class($module), $module->display_name());
        }
    }
}


// Misc.

function redirect($location, $status = 302) {
    header("Location: " . $location, true, $status);
}

///// Public Admin Panel API

function submit_button($text = 'Submit', $class = '') {
    $template = '<button type="submit" name="wx_submit" class="button submit-button green-button button-with-icon %s"><i class="fa fa-check"></i> %s</button>';
    echo sprintf($template, $class, $text);
}

function reset_button($callback = '', $text = 'Reset', $class = '') {
    $template = '<button class="button reset-button red-button %s" onclick="%s;return false;">%s</button>';
    echo sprintf($template, $class, $callback, $text);
}

///// Public Admin Panel API for Custom Pages

function redirect_success($message = null) {
    $page = $_GET['page'];
    $token = time();
    $auth = sha1(MESSAGE_SALT . $message);
    redirect('index.php?page=' . $page . '&success=1&msg=' . urlencode($message) . '&token=' . $token . '&auth=' . $auth);
}

function redirect_failure($message = null) {
    $page = $_GET['page'];
    $token = time();
    $auth = sha1(MESSAGE_SALT . $message);
    redirect('index.php?page=' . $page . '&failure=1&msg=' . urlencode($message) . '&token=' . $token . '&auth=' . $auth);
}

function redirect_notice($message = null) {
    $page = $_GET['page'];
    $token = time();
    $auth = sha1(MESSAGE_SALT . $message);
    redirect('index.php?page=' . $page . '&notice=1&msg=' . urlencode($message) . '&token=' . $token . '&auth=' . $auth);
}
<?php
/**
 * The logout utility of Admin Panel.
 *
 * @author Renfei Song
 * @since 2.0.0
 */

require_once dirname(__FILE__) . '/includes/admin.php';

global $wxdb; /* @var $wxdb wxdb */

$wxdb->insert('security_log', array(
    'userName' => current_user_name(),
    'opName' => 'User.logout',
    'opDetail' => 'Success',
    'ip' => $_SERVER['REMOTE_ADDR'],
    'agent' => $_SERVER['HTTP_USER_AGENT']
));

log_out();

redirect('index.php');
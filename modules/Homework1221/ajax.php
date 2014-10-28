<?php
/**
 * Handling AJAX request from Homework Module (settings.php)
 *
 * @author Renfei Song
 */

require_once dirname(dirname(dirname(__FILE__))) . '/config.php';


// Security check
if (sha1(AJAX_SALT) != $_GET['auth'])
    exit;

global $wxdb; /* @var $wxdb wxdb */

$table = $_GET['table'];
$pk = $_GET['pk'];

if ($_GET['action'] == 'delete'): // Delete homework

    $wxdb->delete($table, array('homeworkId' => $pk));

else: // Update homework

    $name = $_POST['name'];
    $value = $_POST['value'];
    $pk = $_POST['pk'];

    $wxdb->update($table, array($name => $value, 'dateUpdated' => date('c')), array('homeworkId' => $pk));

endif;
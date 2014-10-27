<?php

require_once dirname(dirname(dirname(__FILE__))) . '/config.php';

if (sha1(AJAX_SALT) != $_GET['auth'])
    exit;

//print_r($_POST);

global $wxdb; /* @var $wxdb wxdb */

$table = $_GET['table'];
$pk = $_GET['pk'];

if ($_GET['action'] == 'delete'):

    $wxdb->delete($table, array('homeworkId' => $pk));

else:

    $name = $_POST['name'];
    $value = $_POST['value'];
    $pk = $_POST['pk'];

    $wxdb->update($table, array($name => $value, 'dateUpdated' => date('c')), array('homeworkId' => $pk));

endif;
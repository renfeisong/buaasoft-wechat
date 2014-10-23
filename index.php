<?php
/**
 * Starting point.
 *
 * @author Renfei Song
 * @since 2.0.0
 */

require_once dirname(__FILE__) . "/config.php";

$receiver = new MessageReceiver();

$receiver->receive();

$catched = false;

foreach ($modules as $module) {
    /* @var $module BaseModule */
    if ($module->can_handle_input($receiver->input)) {
        $catched = true;
        do_actions('module_hit', array($receiver->input, get_class($module)));
        echo $module->raw_output($receiver->input);
        break;
    }
}

if ($catched == false) {
    do_actions('modules_missed', array($receiver->input));
}
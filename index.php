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

foreach ($modules as $module) {
    /* @var $module BaseModule */
    if ($module->can_handle_input($receiver->input)) {
        echo $module->raw_output($receiver->input);
        break;
    }
}
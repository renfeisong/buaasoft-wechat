<?php
/**
 * Starting point.
 *
 * @author Renfei Song
 * @since 1.0.0
 */

require_once "includes/settings.php";

$receiver = new MessageReceiver();

$receiver->receive();

foreach ($modules as $module) {
	if ($module->can_handle_input($receiver->input)) {
		echo $module->raw_output($receiver->input);
		break;
	}
}
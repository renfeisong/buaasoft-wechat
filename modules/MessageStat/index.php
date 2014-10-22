<?php

/**
 * Class MessageStat
 * Sample Module: Just dump the received message object.
 */

class MessageStat extends BaseModule {
    public function prepare() {
        add_action('message_received', $this, 'log_message');
    }

    public function log_message($input) {
        if (get_value($this, 'print') == true)
            print_r($input);
    }

    public function display_name() {
        return "Message Statistics";
    }
}
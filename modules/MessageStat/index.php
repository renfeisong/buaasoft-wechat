<?php

/**
 * Class MessageStat
 * Sample Module: Just dump the received message object.
 */

class MessageStat extends BaseModule {
    public function prepare() {
        add_filter('message_received', $this, 'log_message');
    }

    public function log_message($input) {
        print_r($input);
    }
}
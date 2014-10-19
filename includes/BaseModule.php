<?php

require_once "UserInput.php";

class BaseModule {

    public function can_handle_input(UserInput $input) {
        return false;
    }

    public function priority() {
        return 10;
    }

    public function raw_output(UserInput $input) {
        return "";
    }

} 
<?php

/**
 * Class SayHello
 * Sample Module: Replies "你好" when user says "你好".
 */

class SayHello extends BaseModule {

    public function can_handle_input(UserInput $input) {
        if ($input->inputType == InputType::Text && $input->content == "你好")
            return true;
        return false;
    }

    public function priority() {
        return 1;
    }

    public function raw_output(UserInput $input) {



        $formatter = new OutputFormatter($input->openid, $input->accountId);
        return $formatter->textOutput("你好");
    }
}







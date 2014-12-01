<?php

/**
 * Class SayHello
 * Sample Module: Replies "你好" when user says "你好".
 */


class IdeaGen extends BaseModule {
    public function can_handle_input(UserInput $input) {
        if ($input->inputType == InputType::Click && $input->eventKey == "IDEAGEN")
            return true;
        return false;
    }

    public function priority() {
        return 1;
    }

    public function raw_output(UserInput $input) {
        require "data.php";
        $words = new Data();
        $formatter = new OutputFormatter($input->openid, $input->accountId);
        return $formatter->textOutput($words->getAdj(rand(0,$words->getAdjSize()-1))."的".$words->getNoun(rand(0,$words->getNounSize()-1)));
    }

    public function display_name() {
        return "IdeaGen";
    }
}
<?php
/**
 * Contact query module.
 *
 * @author Bingchen Qin
 * @since 2.0.0
 */

class PersonalSettings extends BaseModule {

    public function prepare() {

    }

    public function can_handle_input(UserInput $input) {
        global $wxdb;
        $row = $wxdb->get_row("SELECT * FROM user WHERE openid = '" . $input->openid . "'", ARRAY_A, 0);
        if ($input->inputType == InputType::Click && $input->eventKey == "PERSONAL_SETTINGS" && $row!= null) {
            return true;
        }
        return false;
    }

    public function raw_output(UserInput $input) {
        global $wxdb;
        $row = $wxdb->get_row("SELECT * FROM user WHERE openid = '" . $input->openid . "'", ARRAY_A, 0);
        $formatter = new OutputFormatter($input->openid, $input->accountId);
        $url = "personal_settings.php?openid=" . $input->openid;
        $output = "<a href=\"" . $url . "\">点击进入个人设置页面</a>";
        return $formatter->textOutput($output);
    }

    public function display_name() {
        return "个人设置页面";
    }
}

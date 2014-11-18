<?php
/**
 * Contact query module.
 *
 * @author Bingchen Qin
 * @since 2.0.0
 */

class Contact extends BaseModule {

    private $name;

    public function prepare() {

    }

    public function can_handle_input(UserInput $input) {
        global $wxdb; /* @var $wxdb wxdb */
        $names = $wxdb->get_col("SELECT name FROM contact", 0);
        if ($input->inputType == InputType::Text) {
            foreach ($names as $name) {
                if (substr_count($input->content, $name) > 0) {
                    $this->name = $name;
                    return true;
                }
            }
        }
        return false;
    }

    public function raw_output(UserInput $input) {
        global $wxdb; /* @var $wxdb wxdb */
        $row = $wxdb->get_row("SELECT * FROM contact WHERE openid = '" . $this->name . "'", ARRAY_A, 0);
        $formatter = new OutputFormatter($input->openid, $input->accountId);
        $output = get_value($this, "output_format");
        if (isset($row)) {
            if (isset($row["phone_number"])) {
                str_replace("[phone_number]", $row["phone_number"], $output);
            } else {
                str_replace("[phone_number]", "[没有查询到手机号码]", $output);
            }
            if (isset($row["email"])) {
                str_replace("[email]", $row["email"], $output);
            } else {
                str_replace("[email]", "[没有查询到邮箱]", $output);
            }
        }
        return $formatter->textOutput("没有查询到相关信息");
    }

    public function display_name() {
        return "通讯信息查询管理";
    }
}
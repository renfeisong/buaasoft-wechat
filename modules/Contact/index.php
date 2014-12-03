<?php
/**
 * Contact query module.
 *
 * @author Bingchen Qin
 * @since 2.0.0
 */

class Contact extends BaseModule {

    private $source; //从contact表查询为1，user表为2
    private $name;

    public function prepare() {
        $format = _get_value("Contact", "output_format");
        if (empty($format)) {
            _set_value("Contact", "output_format", "[identity]\n电话号码 [phone_number]\n邮箱 [email]");
        }
    }

    public function can_handle_input(UserInput $input) {
        global $wxdb;
        $names = $wxdb->get_col("SELECT userName FROM contact", 0);
        if ($input->inputType == InputType::Text) {
            foreach ($names as $name) {
                if (substr_count($input->content, $name) > 0) {
                    $this->source = 1;
                    $this->name = $name;
                    return true;
                }
            }
        }
        $names = $wxdb->get_col("SELECT userName FROM user", 0);
        if ($input->inputType == InputType::Text) {
            foreach ($names as $name) {
                if (substr_count($input->content, $name) > 0) {
                    $this->source = 2;
                    $this->name = $name;
                    return true;
                }
            }
        }
        return false;
    }

    public function raw_output(UserInput $input) {
        global $wxdb;
        if ($this->source == 1) {
            $results = $wxdb->get_results("SELECT userId, phoneNumber, email FROM contact WHERE userName = '" . $this->name . "'", ARRAY_A);
        } else {
            $results = $wxdb->get_results("SELECT userId, phoneNumber, email FROM user WHERE userName = '" . $this->name . "'", ARRAY_A);
        }
        $formatter = new OutputFormatter($input->openid, $input->accountId);
        $output_format = get_value($this, "output_format");
        $return_text = "";
        if (isset($results)) {
            foreach ($results as $result) {
                $return_text = $return_text . $output_format;
                $return_text = str_replace("[identity]", $this->name, $return_text);
                if (count($results) == 1) {
                    $return_text = str_replace("[identity]", $this->name, $return_text);
                } else {
                    if (isset($result["userId"]) && $result["userId"] != "") {
                        $return_text = str_replace("[identity]", $result["userId"] . $this->name, $return_text);
                    } else {
                        $return_text = str_replace("[identity]", $this->name, $return_text);
                    }
                }
                if (isset($result["phoneNumber"]) && $result["phoneNumber"] != "") {
                    $return_text = str_replace("[phone_number]", $result["phoneNumber"], $return_text);
                } else {
                    $return_text = str_replace("[phone_number]", "[没有查询到手机号码]", $return_text);
                }
                if (isset($result["email"]) && $result["email"] != "") {
                    $return_text = str_replace("[email]", $result["email"], $return_text);
                } else {
                    $return_text = str_replace("[email]", "[没有查询到邮箱]", $return_text);
                }
                $return_text = $return_text . "\n";
            }
            return $formatter->textOutput($return_text);
        }
        return $formatter->textOutput("没有查询到相关信息");
    }

    public function display_name() {
        return "通讯信息查询管理";
    }
}
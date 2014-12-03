<?php
/**
 * Contact query module.
 *
 * @author Bingchen Qin
 * @since 2.0.0
 */

class Contact extends BaseModule {

    private $table_name = "contact";
    private $source; //从contact表查询为1，user表为2
    private $name;

    public function prepare() {
        global $wxdb;
        if (!$wxdb->schema_exists($this->table_name)) {
            $sql = <<<SQL
CREATE TABLE `{$this->table_name}` (
`id` int(11) NOT NULL,
  `userName` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `identity` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phoneNumber` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SQL;
            $wxdb->query($sql);
        }
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
        global $wxdb; /* @var $wxdb wxdb */
        if ($this->source == 1) {
            $sql = $wxdb->prepare("SELECT identity, phoneNumber, email FROM contact WHERE userName = '%s'", $this->name);
        } else {
            $sql = $wxdb->prepare("SELECT userId, phoneNumber, email FROM contact WHERE userName = '%s'", $this->name);
        }
        $results = $wxdb->get_results($sql, ARRAY_A);
        $formatter = new OutputFormatter($input->openid, $input->accountId);
        $output_format = get_value($this, "output_format");
        $return_text = "";
        if (!empty($results)) {
            foreach ($results as $result) {
                $return_text = $return_text . $output_format;
                if (count($results) == 1) {
                    $return_text = str_replace("[identity]", $this->name, $return_text);
                } else {
                    if ($this->source == 1) {
                        if (!empty($result["identity"])) {
                            $return_text = str_replace("[identity]", $result["identity"] . $this->name, $return_text);
                        } else {
                            $return_text = str_replace("[identity]", $this->name, $return_text);
                        }
                    } else {
                        if (!empty($result["userId"])) {
                            $return_text = str_replace("[identity]", $result["userId"] . $this->name, $return_text);
                        } else {
                            $return_text = str_replace("[identity]", $this->name, $return_text);
                        }
                    }
                }
                if (!empty($result["phoneNumber"])) {
                    $return_text = str_replace("[phone_number]", $result["phoneNumber"], $return_text);
                } else {
                    $return_text = str_replace("[phone_number]", "[未填写]", $return_text);
                }
                if (!empty($result["email"])) {
                    $return_text = str_replace("[email]", $result["email"], $return_text);
                } else {
                    $return_text = str_replace("[email]", "[未填写]", $return_text);
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
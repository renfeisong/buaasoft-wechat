<?php
/**
 * Contact query module.
 *
 * @author Bingchen Qin
 * @since 2.0.0
 */

abstract class SearchMode
{
    const NAME_TO_INFO = 0;
    const PHONE_NUMBER_TO_NAME = 1;
    const EMAIL_TO_NAME = 2;
}

abstract class SearchSource
{
    const CONTACT = 0;
    const USER = 1;
}

class Contact extends BaseModule {

    private $table_name = "contact";
    private $mode = -1;
    private $source = -1;
    private $name;
    private $phone_numeber;
    private $email;

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
        if ($input->inputType == InputType::Text) {
            $names = $wxdb->get_col("SELECT userName FROM contact", 0);
            if ($names != null) {
                $this->mode = SearchMode::NAME_TO_INFO;
                foreach ($names as $name) {
                    if (substr_count($input->content, $name) > 0) {
                        $this->source = SearchSource::CONTACT;
                        $this->name = $name;
                        return true;
                    }
                }
            }
            $names = $wxdb->get_col("SELECT userName FROM user", 0);
            if ($names != null) {
                $this->mode = SearchMode::NAME_TO_INFO;
                foreach ($names as $name) {
                    if (substr_count($input->content, $name) > 0) {
                        $this->source = SearchSource::USER;
                        $this->name = $name;
                        return true;
                    }
                }
            }
            $phone_numbers = $wxdb->get_col("SELECT phoneNumber FROM contact", 0, 0);
            if ($phone_numbers != null) {
                $this->mode = SearchMode::PHONE_NUMBER_TO_NAME;
                foreach ($phone_numbers as $phone_number) {
                    if (substr_count($input->content, $phone_number) > 0) {
                        $this->source = SearchSource::CONTACT;
                        $this->phone_numeber = $phone_number;
                        return true;
                    }
                }
            }
            $phone_numbers = $wxdb->get_col("SELECT phoneNumber FROM user", 0, 0);
            if ($phone_numbers != null) {
                $this->mode = SearchMode::PHONE_NUMBER_TO_NAME;
                foreach ($phone_numbers as $phone_number) {
                    if (substr_count($input->content, $phone_number) > 0) {
                        $this->source = SearchSource::USER;
                        $this->phone_numeber = $phone_number;
                        return true;
                    }
                }
            }
            $emails = $wxdb->get_col("SELECT email FROM contact", 0, 0);
            if ($emails != null) {
                $this->mode = SearchMode::EMAIL_TO_NAME;
                foreach ($emails as $email) {
                    if (substr_count($input->content, $email) > 0) {
                        $this->source = SearchSource::CONTACT;
                        $this->email = $email;
                        return true;
                    }
                }
            }
            $emails = $wxdb->get_col("SELECT email FROM user", 0, 0);
            if ($emails != null) {
                $this->mode = SearchMode::EMAIL_TO_NAME;
                foreach ($emails as $email) {
                    if (substr_count($input->content, $email) > 0) {
                        $this->source = SearchSource::USER;
                        $this->email = $email;
                        return true;
                    }
                }
            }
        }
        return false;
    }

    public function raw_output(UserInput $input) {
        global $wxdb; /* @var $wxdb wxdb */
        $formatter = new OutputFormatter($input->openid, $input->accountId);
        $return_text = "";
        switch ($this->mode) {
            case SearchMode::NAME_TO_INFO: {
                $output_format = get_value($this, "output_format");
                if ($this->source == SearchSource::CONTACT) {
                    $sql = $wxdb->prepare("SELECT identity, phoneNumber, email FROM contact WHERE userName = '%s'", $this->name);
                } else {
                    $sql = $wxdb->prepare("SELECT userId, phoneNumber, email FROM user WHERE userName = '%s'", $this->name);
                }
                $results = $wxdb->get_results($sql, ARRAY_A);
                if (!empty($results)) {
                    foreach ($results as $result) {
                        $return_text = $return_text . $output_format;
                        if (count($results) == 1) {
                            $return_text = str_replace("[identity]", $this->name, $return_text);
                        } else {
                            if ($this->source == SearchSource::CONTACT) {
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
                }
                break;
            }
            case SearchMode::PHONE_NUMBER_TO_NAME: {
                if ($this->source == SearchSource::CONTACT) {
                    $sql = $wxdb->prepare("SELECT userName FROM contact WHERE phoneNumber = '%s'", $this->phone_numeber);
                } else {
                    $sql = $wxdb->prepare("SELECT userName FROM user WHERE phoneNumber = '%s'", $this->phone_numeber);
                }
                $results = $wxdb->get_results($sql, ARRAY_A);
                if (!empty($results)) {
                    foreach ($results as $result) {
                        $return_text = $return_text . $this->phone_numeber . "是" . $result["userName" ]. "的电话号码。\n";
                    }
                }
                break;
            }
            case SearchMode::EMAIL_TO_NAME: {
                if ($this->source == SearchSource::CONTACT) {
                    $sql = $wxdb->prepare("SELECT userName FROM contact WHERE email = '%s'", $this->email);
                } else {
                    $sql = $wxdb->prepare("SELECT userName FROM user WHERE email = '%s'", $this->email);
                }
                $results = $wxdb->get_results($sql, ARRAY_A);
                if (!empty($results)) {
                    foreach ($results as $result) {
                        $return_text = $return_text . $this->email . "是" . $result["userName" ]. "的邮箱。\n";
                    }
                }
                break;
            }
            default: {
                $return_text = "没有查询到相关信息";
                break;
            }
        }
        return $formatter->textOutput($return_text);
    }

    public function display_name() {
        return "通讯信息查询管理";
    }
}
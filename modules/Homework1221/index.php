<?php
/**
 * Class Homework1221
 *
 * @author Renfei Song
 */

class Homework1221 extends BaseModule {

    public $table_name = "homework";

    public function prepare() {
        global $wxdb; /* @var $wxdb wxdb */
        set_value($this, 'table', $this->table_name);

        if (!$wxdb->schema_exists($this->table_name)) {
            $sql = <<<SQL
CREATE TABLE `{$this->table_name}` (
  `homeworkId` int(11) NOT NULL,
  `subject` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
  `content` varchar(1000) COLLATE utf8_unicode_ci NOT NULL,
  `userName` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
  `publishDate` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
  `dueDate` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
  `dateUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`homeworkId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
SQL;
            $wxdb->query($sql);
        }
    }

    public function can_handle_input(UserInput $input) {
        if ($input->inputType == InputType::Click && $input->eventKey == "HOMEWORK")
            if (substr($input->user['class'], 0, 4) == '1221')
                return true;
        return false;
    }

    public function get_homework() {
        global $wxdb; /* @var $wxdb wxdb */
        $today = date('c');
        $sql = $wxdb->prepare("SELECT * FROM `" . $this->table_name . "` WHERE `dueDate` = '' OR `dueDate` >= '%s' ORDER BY `publishDate` DESC, `subject` ASC", $today);
        $rows = $wxdb->get_results($sql, ARRAY_A);
        $homework = '';
        $last_date = '';
        $last_subject = '';
        foreach ($rows as $row) {
            if ($row['publishDate'] != $last_date) {
                $last_date = $row['publishDate'];
                $last_subject = '';
                $homework .= "\n【" . $row['publishDate'] . "】";
            }
            if ($row['subject'] != $last_subject) {
                $last_subject =  $row['subject'];
                $homework .= "\n" . $row['subject'] . '：';
            }
            $homework .= $row['content'];

            if ($row['dueDate'] != '') {
                $homework .= '（' . date('n/j',strtotime($row['dueDate'])) . '过期）';
            }
        }

        if ($homework == '')
            $homework = '暂时没有作业信息';

        return $homework;
    }

    public function raw_output(UserInput $input) {
        $formatter = new OutputFormatter($input->openid, $input->accountId);
        return $formatter->textOutput($this->get_homework());
    }

    public function display_name() {
        return '作业管理';
    }
}
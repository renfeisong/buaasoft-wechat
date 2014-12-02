<?php
/**
 * Class Homework2012
 *
 * @author Renfei Song
 */

class Homework2012 extends BaseModule {

    /* configurable */
    public $table_name = 'homework2012';
    public $start_year = '2012';
    public $dept = '21';

    public function prepare() {
        global $wxdb; /* @var $wxdb wxdb */
        set_value($this, 'table', $this->table_name);
        set_value($this, 'module', get_class($this));

        if (!$wxdb->schema_exists($this->table_name)) {
            $sql = <<<SQL
CREATE TABLE `{$this->table_name}` (
  `homeworkId` int(11) NOT NULL AUTO_INCREMENT,
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
            if ($input->user['startYear'] == $this->start_year && $input->user['dept'] == $this->dept)
                return true;
        return false;
    }

    public function get_homework() {
        global $wxdb; /* @var $wxdb wxdb */
        $today = date('Y-m-d');
        $sql = $wxdb->prepare("SELECT * FROM `" . $this->table_name . "` WHERE `dueDate` = '' OR `dueDate` >= '%s' ORDER BY `publishDate` DESC, `subject` ASC", $today);
        $rows = $wxdb->get_results($sql, ARRAY_A);
        $homework = '';
        $last_date = '';
        $last_subject = '';
        foreach ($rows as $row) {
            if ($row['publishDate'] != $last_date) {
                $last_date = $row['publishDate'];
                $last_subject = '';
                if ($homework == '')
                    $homework .= "【" . $row['publishDate'] . "】";
                else
                    $homework .= "\n【" . $row['publishDate'] . "】";
            }
            if ($row['subject'] != $last_subject) {
                $last_subject =  $row['subject'];
                $homework .= "\n" . $row['subject'] . '：';
            }
            $homework .= $row['content'];

            if ($row['dueDate'] != '') {
                if (date('n/j') == date('n/j', strtotime($row['dueDate']))) {
                    $homework .= '（今天过期）';
                } else {
                    $homework .= '（' . date('n/j', strtotime($row['dueDate'])) . '过期）';
                }
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
        return '作业管理 ' . $this->start_year;
    }
}
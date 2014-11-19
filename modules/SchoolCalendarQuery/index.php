<?php

/**
 * Class SchoolCalenday
 *
 * try to get the school calendar from jiaowu.buaa.edu.cn
 * try to use the persistence
 */
class SchoolCalendarQuery extends BaseModule {

    private $first_day = "2014-9-15";

    /**
     * persistence the first day str
     */
    public function prepare() {
        set_value($this, "first_day", $this->first_day);
    }

    /**
     * now response to a menu click
     * @param UserInput $input
     * @return bool
     */
    public function can_handle_input(UserInput $input) {
        if ($input->inputType == InputType::Click && $input->eventKey == "CALENDAR") {
            return true;
        } else {
            return false;
        }
    }

    public function raw_output(UserInput $input) {
        $formatter = new OutputFormatter($input->openid, $input->accountId);
        $date_str = date("Y年m月d日", time());
        $teaching_week = $this->get_teaching_week($this->first_day);
        return $formatter->textOutput("今天是{$date_str}，第{$teaching_week}教学周");
    }

    public function display_name() {
        return "校历查询";
    }

    /**
     * @param $first_day
     */
    public function set_first_day($first_day) {
        $this->first_day = $first_day;
    }

    /**
     * parse the $first_day and compute the teaching week
     * @todo try to get content from jiaowu.buaa.edu.cn
     * @return int the teaching week from
     */
    public function get_teaching_week($first_day_str) {
        $first_time = strtotime($first_day_str);

        // 第一周的星期一
        $first_day_id = date("w", $first_time);
        $first_day = date("z", $first_time);

        // current time
        $cur_time = time();
        $cur_day = date("z", $cur_time);

        $teaching_week = (int)(($first_day_id + $cur_day - $first_day) / 7 + 1);
        return $teaching_week;
    }

}

?>
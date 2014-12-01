<?php
/**
 * Created by PhpStorm.
 * User: timmyxu
 * Date: 14/11/9
 * Time: 下午1:41
 */

class Ranking2012 extends BaseModule {
    private $grade, $stuid, $stuname;
    private $start_year = '2012'; /* configurable */

    public function prepare() {
        set_value($this, "grade", $this->start_year);
        if (get_value($this, "ranking_".get_value($this, "grade")) == null)
            set_value($this, "ranking_".get_value($this, "grade"), array());
        if (get_value($this, "ranking_".get_value($this, "grade").'_content') == null)
            set_value($this, "ranking_".get_value($this, "grade").'_content', "暂无成绩信息");
        if (!file_exists(ABSPATH.'/modules/Ranking'.get_value($this, "grade").'/score/')) {
            mkdir(ABSPATH.'/modules/Ranking'.get_value($this, "grade").'/score/');
        }
    }

    public function can_handle_input(UserInput $input) {
        global $wxdb;
        $row = $wxdb->get_row("SELECT * FROM `user` WHERE openid = '" . $input->openid . "'", ARRAY_A);
        $this->grade = $row['startYear'];
        if ($input->inputType == InputType::Click && $input->eventKey == "RANKING" && $this->grade == get_value($this, "grade")) {
            $this->stuid = $row['userId'];
            $this->stuname = $row['userName'];
            return true;
        }
        return false;
    }

    public function priority() {
        return 1;
    }

    public function grade_exist() {
        $ranking_list = get_value($this, 'ranking_'.$this->grade);
        if (empty($ranking_list))
            return false;
        return true;
    }

    public function get_id($type) {
        switch ($type) {
            case "savg":
                return 2;
            case "sno":
                return 3;
            case "ssno":
                return 4;
            case "gavg":
                return 5;
            case "gno":
                return 6;
            case "gsno":
                return 7;
            case "ctot":
                return 8;
            case "gtot":
                return 9;
        }
    }

    public function raw_output(UserInput $input) {
        $formatter = new OutputFormatter($input->openid, $input->accountId);
        if ($this->grade_exist())
        {
            $ranking_list = get_value($this, "ranking_".$this->grade);
            $output = $this->stuname.'，你的成绩如下：'."\n";
            $statement = get_value($this, "ranking_".$this->grade."_content");
            while (preg_match('/(?:\[)([^\[]*)(?:\])/', $statement, $matches) != 0)
            {
                //取ID开头
                preg_match('/(?:\[)(.*)(:)/', $matches[0], $id_array);
                $id = substr($id_array[0], 1);
                $id = rtrim(trim($id),':');

                //取type开头
                preg_match('/(:)(.*)(?:\])/', $matches[0], $type_array);
                $type = substr($type_array[0],1);
                $type = rtrim(trim($type),']');

                $choosefile = $ranking_list[(int)$id-1];

                $ans = "";
                $file = fopen(dirname(__FILE__).'/score/'.$choosefile . '.csv','r');
                while ($data = fgetcsv($file))
                {
                    if ($data[0] == $this->stuid)
                    {
                        $ans = $data[$this->get_id($type)];
                        break;
                    }
                }
                $statement = preg_replace('/(?:\[)([^\[]*)(?:\])/', $ans, $statement, 1);
            }
            $output .= $statement;
            $url = ROOT_URL.'modules/Ranking'.$this->grade.'/rank.php?openid='.$input->openid;
            $output .= "\n\n<a href='$url'>点击查看更多成绩</a>";
            return $formatter->textOutput($output);
        }
        else
            return $formatter->textOutput(get_value($this, "ranking_".$this->grade."_content"));
    }

    public function display_name() {
        return get_value($this, "grade")."级成绩管理";
    }
}
<?php
/**
 * Class SchoolBus
 *
 * @author Zhan Yu
 */

class SchoolBus extends BaseModule {

    /**
     * @var string table name of bus in database
     */
    public $table_name_bus = "schoolbus_bus";
    /**
     * @var string table name of route in database
     */
    public $table_name_route = "schoolbus_route";

    /**
     * Check and create tables in database
     */
    public function prepare() {
        global $wxdb;
        set_value($this, 'table_bus', $this->table_name_bus);
        set_value($this, 'table_route', $this->table_name_route);
        if(!get_value($this, 'ajax_secret')) {
            set_value($this, 'ajax_secret', '__YUZHANEncrypted__');
        }

        if (!$wxdb->schema_exists($this->table_name_bus)) {
            $sql = <<<SQL
CREATE TABLE `{$this->table_name_bus}` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `departure` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
    `destination` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
    `departureTime` time NOT NULL,
    `day` int(11) NOT NULL,
    `dateUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
SQL;
            $wxdb->query($sql);
        }

        if (!$wxdb->schema_exists($this->table_name_route)) {
            $sql = <<<SQL
CREATE TABLE `{$this->table_name_route}` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `departure` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
    `destination` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
    `dateUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
SQL;
            $wxdb->query($sql);
        }
    }

    /**
     * Decide if this module could handle this input
     *
     * @param $input UserInput
     * @return bool
     */
    public function can_handle_input(UserInput $input) {
        if ($input->inputType == InputType::Click && $input->eventKey == "SCHOOL_BUS")
            return true;
        if($input->inputType == InputType::Text && preg_match("/([校班]|通勤)车时([间刻][表]?)/", $input->content))
            return true;
        return false;
    }

    /**
     * Get bus of the given route
     *
     * @param $route array
     * @return string
     */
    public function get_school_bus($route) {
        global $wxdb;
        $day = (date('w') < 6 && date('w') > 0) ? 1 : 2;
        $time = date('G:i:s');
        $sql = <<<SQL
SELECT * FROM `{$this->table_name_bus}`
  WHERE `departureTime` > %s
  AND `day` = %s
  AND `departure` = %s
  AND `destination` = %s
  ORDER BY `departureTime` ASC
SQL;
        $sql = $wxdb->prepare($sql, $time, $day, $route['from'], $route['to']);
        $result = $wxdb->get_results($sql, ARRAY_A);
        if(count($result) < 1) {
            return "太晚了，今天没有班车了:(";
        } else {
            preg_match_all("/(\d+)/", $result[0]["departureTime"], $match);
            $th = $match[0][0] - date('G');
            $tm = $match[0][1] - date('i');
            if($tm < 0) {
                $th -= 1;
                $tm += 60;
            }
            return "距下一次发车（".substr($result[0]['departureTime'], 0, -3)."）还有".$th."小时".$tm."分钟。".
            (count($result) > 1 ? "还有".count($result)."班车。" : "只有最后一班了。");
        }
    }

    /**
     * Get routes required by settings
     *
     * @return array
     */
    public function get_route() {
        global $wxdb;
        $sql = "SELECT * FROM `{$this->table_name_route}` WHERE 1";
        $result = $wxdb->get_results($sql, ARRAY_A);
        $ret = array();
        foreach($result as $key=>$value) {
            array_push($ret, array(
                'from'=>$value['departure'],
                'to'=>$value['destination']
            ));
        }
        return $ret;
    }

    /**
     * Output to Weixin client
     *
     * @param $input UserInput
     * @return string
     */
    public function raw_output(UserInput $input) {
        $link = 'http://'.$_SERVER['HTTP_HOST'].ROOT_URL."/modules/SchoolBus/list.php";
        $reply = '现在是'.date('n月j日G:i');
        foreach($this->get_route() as $key=>$value) {
            $reply .= "\n\n".$value['from']."到".$value['to'].":\n".$this->get_school_bus($value);
        }
        $reply .= "\n\n附：<a href=\"".$link."\">查看完整校车时刻表</a>";
        $formatter = new OutputFormatter($input->openid, $input->accountId);
        return $formatter->textOutput($reply);
    }

    /**
     * Display name if AdminCenter
     *
     * @return string
     */
    public function display_name() {
        return '校车管理';
    }
}
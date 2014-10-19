<?php 

class Util {

    /**
     * 获得当前的周数
     *
     * @param $firstDayStr string 代表开学第一天的字符串信息
     * @return int 当前的周数
     * @todo 从jiaowu.buaa上获得数据，或者向后台web页面开放接口
     */
    public static function getTeachingWeek($firstDayStr) {

        $firstDayStr = date("Y")."-9-15";
        $firstTime = strtotime($firstDayStr);
        //第一周的星期一
        $firstDayId = date("w", $firstTime);
        $firstDay = date("z", $firstTime);
        
        // current time
        $curTime = time();
        $curDay = date("z", $curTime);
        
        $week = (int)(($firstDayId + $curDay - $firstDay) / 7 + 1);
        return $week;
    }

    /**
     * 将数字转换成字符串
     *
     * @param $weekday int 星期几
     * @return string 星期几
     */
    public function weekdayToStr($weekdayInt) {
        switch ($weekdayInt) {
            case 0: return "星期日";
            case 1: return "星期一";
            case 2: return "星期二";
            case 3: return "星期三";
            case 4: return "星期四";
            case 5: return "星期五";
            case 6: return "星期六";
            default: return "未知";
        }
    }
}

?>
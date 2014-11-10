<?php

// class_schedule_query_test();

/**
 * test function, not complete
 */
function class_schedule_query_test() {
	global $wxdb;
	require_once("../../config.php");

    // I dont have the database, so it is hard for me to test whether this module is normal
	$class_schedule_query = new ClassScheduleQuery();
//	$class_schedule_query->query_class();
}

require_once("classSchedule.php");
require_once("dailySchedule.php");

/**
 * Class ClassScheduleQuery
 *
 * @todo the query the other time not just the current time
 */
class ClassScheduleQuery extends BaseModule {

	const STR_FIRST_DAY = "2014-9-15";

	// the content for query, such as the student info
	private $config;

	const TABLE_USER = "user";

	function __construct() {
		$this->config = array();
	}

    /**
     * prepare the database schema include classSchedule and dailySchedule
     */
    public function prepare() {
        global $wxdb;
        // prepare the classSchedule schema
        if (!$wxdb->schema_exists(ClassSchedule::TABLE_CLASS_SCHEDULE)) {
            ClassSchedule::create_table(ClassSchedule::TABLE_CLASS_SCHEDULE);
        }
        // prepare the shahe dailySchedule schema
        if (!$wxdb->schema_exists(DailySchedule::TABLE_SHAHE_SCHEDULE)) {
            DailySchedule::create_table(DailySchedule::TABLE_SHAHE_SCHEDULE);
        }
        // prepare the xueyuan dailySchedule schema
        if (!$wxdb->schema_exists(DailySchedule::TABLE_XUEYUAN_SCHEDULE)) {
            DailySchedule::create_table(DailySchedule::TABLE_XUEYUAN_SCHEDULE);
        }
    }

	/**
	 * @todo according to menu setting to complete this function
	 */
	public function can_handle_input(UserInput $input) {
		if ($input->inputType == InputType::Click && $input->eventKey == "....") {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @todo according to OuputFormatter.php to complete this function
	 * @todo can control the query time
	 */
	public function raw_output(UserInput $input) {
		$class_schedule_query = new ClassScheduleQuery();
		$query_result = $class_schedule_query->query_class($input->openid, time());

		$formatter = new OutputFormatter($input->openid, $input->accoutId);
		return $formatter->MultiNewsOutput($query_result);
	}

	public function display_name() {
		return "课程查询";
	}

	/**
	 * the interface for querying
	 *
	 * @param string $openid openid
	 * @param int $time_stamp time stamp
	 */
	public function query_class($openid, $time_stamp) {
		$this->pre_query($openid, $time_stamp);

		$config = $this->config;

		// if is saturday or sunday
		if ($config['weekday'] == 6 || $config['weekday'] == 0) {
			
		}

		$query_result = $this->on_query();

		return $this->post_query($query_result);
	}

	/**
	 * set the $config for query
	 * @param array $config the content for query
	 */
	public function set_config($config) {
		$this->config = $config;
	}

	/**
	 * prepare for querying, will fill $config
	 *
	 * @param string $openid openid
	 * @param int $time_stamp time stamp
	 * @todo need teaching week get function
     * @todo 使用这种方式得到first_day并不好
	 */
	private function pre_query($openid, $time_stamp) {

		// store needed infomation for quering
		$config = array();

		$config['timeStamp'] = $time_stamp; // time_stamp
		$config['weekday'] = date("w", $time_stamp); // weekday

		$student_info = $this->get_student_info($openid);
		$config['studentInfo'] = $student_info; // student info
		$config['class'] = $this->get_class_from_time($student_info, $time_stamp);
		$config['group'] = $this->get_group($student_info); // student group
		$config['teachingWeek'] =  $this->get_teaching_week(self::STR_FIRST_DAY);// teaching week
		
		$this->set_config($config);
	}

	/**
	 * the main function, query the database according to $config
	 *
	 * @return array the array contain class information
	 * @todo changing according to wxdb.php
	 */
	private function on_query() {
		$config = $this->config;
		// query the class schedule from database
		$class_schedule = new ClassSchedule(ClassSchedule::TABLE_CLASS_SCHEDULE, $config['classification']);
		$classes = $class_schedule->query($config['weekday']);

		$result_array = array();
		$class_count = 0;

		// loop all database content
		for ($i = 1; $i <= ClassSchedule::NUM_MAX_CLASS_DB; $i++) {
			// key from class_1 to class_12 ....
			$content = $classes['class_'.$i];
			
			if ($content != "" && strpos($content, "#") != -1) {
				// get the class_info and info_str
				$class_info = explode(ClassSchedule::SEPARATOR, $content);
				$class_str = $class_info[0];
				$info_str = $class_info[1];
				
				$info = ClassSchedule::parse_info_str($info_str);
				// check if match
				if ($this->is_info_match($info, $config)) {
					// change the start class
					$info['s'] = ($info['s'] < $config['class']) ? $config['class'] : $info['s'];

					$result_array[$class_count]["s"] = $info['s'][0];
					$result_array[$class_count]["e"] = $info['e'][0];
					$result_array[$class_count]["str"] = $class_str;
					$class_count++;
				}
			}
		}
		
		return $result_array;
	}

	/**
	 * handle the result from on_query function and return article array for output
	 *
	 * @param array $query_result the result from on_query
	 * @return array article array for output
	 * @todo try to control the return string format | according to article format to change
	 * @todo according to transmit weekday str function
	 * @todo must can control the pic_url
	 */
	private function post_query($query_result) {
		$config = $this->$config;
		$result_array = array();

		foreach ($query_result as $index => $info) {
			if ($info['s'] != $info['e']) {
				$result_array[$index]["title"] = "第".$info['s'].'-'.$info['e'].' '.$info['str'];
			} else {
				$result_array[$index]["title"] = "第".$info['s'].' '.$info['str'];
			}
		}

		// $result_array["total"] = count($query_result);
		// $result_array["date"] = date("n")." 月 ".date("j", $config['time'])." 日 ".$this->transmitWeekdayStr($config['weekday']);
	 // 	$result_array["isWeekend"] = "false";
		return $result_array;
	}

	/**
	 * 来对学生进行分组
	 * 
	 * @see get_student_info()
	 * @param array $student_info the info about student
	 * @return int the group of the student
	 */
	private function get_group($student_info) {
		$group = (int)substr($student_info["class"], 5, 1);
		return $group;
	}

	/**
	 * get the student info from database according to the openid
	 * @param string $openid the openid of user 
	 * @return array the info about student
	 * @todo create a user.php tand move this function into it?
	 */
	private function get_student_info($openid) {
		global $wxdb;
		$student_info = null;
		$results = $wxdb->get_results("SELECT * FROM  where openid='$openid'");
		if ($wxdb->num_rows == 1) {
			$student_info = $results[0];
		} else {
			// error
		}
		return $student_info;
	}

    /**
     * parse the $first_day and compute the current teaching week
     * @todo try to use SchoolCalendar module
     * @return int the teaching week from
     */
    public function get_teaching_week($first_day_str) {
        $first_time = strtotime($first_day_str);

        // the monday of first week
        $first_day_id = date("w", $first_time);
        $first_day = date("z", $first_time);

        // current time
        $cur_time = time();
        $cur_day = date("z", $cur_time);

        $teaching_week = (int)(($first_day_id + $cur_day - $first_day) / 7 + 1);
        return $teaching_week;
    }

	/**
	 * get current class according to daily schedule
	 * 
	 * @param int $time_stamp time stamp
	 * @return int the class sequence id
	 */
	private function get_class_from_time($student_info, $time_stamp) {
		$cur_hour = (int)date("H", $time_stamp);
		$cur_minute = (int)date("i", $time_stamp);
		
		$total_minutes = $cur_hour * 60 + $cur_minute;
		
		// get the last two digits of the year, will occur error in next century
		$year = (int)date("y", $time_stamp);

		// try to select the daily schedule according to the classification of the student, but this will occur error, for example, the student of department 24 (中法工程师学院) study in shahe.

		// current implementation is simple
		$daily_schedule = null;
		if ($student_info['classification'] == '1321' || $student_info['classification'] == '1421') {
			$daily_schedule = new DailySchedule(DailySchedule::TABLE_SHAHE_SCHEDULE);
		} else if ($student_info['classification'] == '1221') {
			$daily_schedule = new DailySchedule(DailySchedule::TABLE_XUEYUAN_SCHEDULE);
		}

		$section = $daily_schedule->select_section($total_minutes);

		return $section['cid'];
	}

	/**
	 * check the $info which is parsed from the database content whether match the $config
	 *
	 * @param array $config 查询所需要的配置信息
	 * @param array $info 课程的信息
	 * @return boolean 当课程信息和配置信息符合时，返回true，否则返回false
	 */
	private function is_info_match($info, $config) {
		$counts = array(
			"s" => count($info['s']), 
			"e" => count($info['e']), 
			"k" => count($info['k']), 
			"j" => count($info['j']), 
			);
		if ($counts['s'] != $counts['e'] || $counts['k'] != $counts['j']) {
			// config is illegal
			return false;
		}

		// in correct group -- g
		$in_group = false;
		
		foreach ($info["g"] as $group) {
			$in_group |= ($group == $config["group"]);
		}

		if (count($info["g"]) != 0 && !$in_group) {
			return false;
		}

		//in correct week -- k -- j
		$in_time = false;
		
		for ($i = 0, $weekCount = $counts['k']; $i < $weekCount; $i++) {
			$in_time |= ($info['k'][$i] <= $config['week'] && $config['week'] <= $info['j'][$i]);
		}
		

		if ($counts['k'] != 0 && !$in_time) {
			return false;
		}

		// in correct odd or even week -- w
		if (count($info['w']) != 0 && $config['w'] % 2 != $info['w'][0]) {
			return false;
		}
		
		// in correct time -- e
		if ($info['e'][0] < $config['class']) {
			return false;
		}

		return true;
	}
}


?>
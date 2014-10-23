
<?php

$wxdb = null;
class_schedule_query_test();

function class_schedule_query_test() {
	require_once("../../includes/wxdb.php");
	$wxdb = new wxdb("root", "root", "weixin", "localhost");

	$class_schedule_query = new ClassScheduleQuery();
	$class_schedule_query->query_class();
}

require_once("classSchedule.php");
require_once("dailySchedule.php");

/**
 * Class ClassScheduleQuery
 *
 * @todo need util class
 */
class ClassScheduleQuery extends BaseModule {

	const STR_FIRST_DAY = "2014-9-15";

	private $shaheSchedule;
	private $campusRoadSchedule;

	const TABLE_USER = "user";
	const TABLE_CLASS_SCHEDULE = "class_schedule";
	const TABLE_SHAEH_SCHEDULE = "shahe_schedule";
	const TABLE_XUEYUAN_SCHEDULE = "xueyuan_schedule";

	/**
	 * @todo according to menu setting to complete this function
	 */
	public function can_handle_input(UserInput $input) {
		if ($input->inputType == InputType::Click && $input->EventKey == "....") {
			return true;
		} else {
			return false;
		}
	}

	public function priority() {
		return 10;
	}

	/**
	 * @todo according to OuputFormatter.php to complete this function
	 */
	public function raw_output(UserInput $input) {
		$formatter = new OutputFormatter($input->openid, $input->accoutId);
		return $formatter->textOutput("功能测试中");
	}

	/**
	 * the interface for querying
	 *
	 * @param string $openid openid
	 * @param int $time_stamp time stamp
	 */
	public function query_class($openid, $time_stamp) {
		$this->pre_query($openid, $time_stamp);
	}

	/**
	 * prepare for querying, will set $config
	 *
	 * @param string $openid openid
	 * @param int $time_stamp time stamp
	 * @todo according to article format to change the return format
	 */
	private function pre_query($openid, $time_stamp) {

		// store needed infomation for quering
		$config = array();

		$config['timeStamp'] = $time_stamp; // time_stamp
		$config['weekday'] = date("w", $time_stamp); // weekday

		// if is saturday or sunday
		if ($config['weekday'] == 6 || $config['weekday'] == 0) {
			return array (
				"isWeekend"=>"true", 
				"total"=>"0", 
				"date"=>date("n", $time_stamp)." 月 ".date("j", $time_stamp)." 日 ".$this->transmitWeekdayStr($config['day']));
		}

		$student_info = $this->get_student_info($openid);
		$config['studentInfo'] = $student_info; // student info
		$config['class'] = $this->get_class_from_time($student_info, $time_stamp);
		$config['group'] = $this->get_group($student_info); // student group
		$config['teachingWeek'] = $this->get_week(self::STR_FIRST_DAY); // teaching week
		
		return $this->on_query($config);
	}

	/**
	 * the main function
	 *
	 * @param config array preQuery函数中所设置的信息数组
	 * @return array the array contain class information
	 * @todo changing according to wxdb.php
	 */
	private function on_query($config) {

		$class_schedule = new ClassSchedule(self::TABLE_CLASS_SCHEDULE, $config['classification']);
		$classes = $class_schedule->query($config['weekday']);

		$result_array = array();
		$class_count = 0;

		// loop all database content
		for ($i = 1; $i <= ClassSchedule::NUM_MAX_CLASS_DB; $i++) {
			// key from class_1 to class_12 ....
			$content = $classes['class_'.$i];
			
			if ($content != "") {
				//取出信息字符串 infoStr 例如：s1e2k3j4g5
				$class_info = explode(ClassSchedule::SEPARATOR, $content);
				$class_str = $class_info[0];
				$info_str = $class_info[1];
				
				$info = $this->analyze_info_str($info_str);
				// 匹配
				if ($this->is_info_match($info, $config)) {
					// change the start class
					$info['s'] = ($info['s'] < $config['class']) ? $config['class'] : $info['s'];

					// 格式：第1-2节 口语 J0-000
					$result_array[$class_count]["s"] = $info['s'][0];
					$result_array[$class_count]["e"] = $info['e'][0];
					$result_array[$class_count]["str"] = $class_str;
					$class_count++;
				}
			}
		}
		
		return $this->post_query($result_array, $config);
	}

	/**
	 * handle the result from onQuery function and return article array
	 *
	 * @param array $raw_data 由on_query函数查询产生的结果
	 * @return array
	 * @todo try to control the return string format | according to article format to change
	 * @todo according to transmit weekday str function
	 */
	private function post_query($raw_data, $config) {
		$result_array = array();

		foreach ($raw_data as $index => $info) {
			if ($info['s'] != $info['e']) {
				$result_array[$index."classInfo"] = "第".$info['s'].'-'.$info['e'].' '.$info['str'];
			} else {
				$result_array[$index."classInfo"] = "第".$info['s'].' '.$info['str'];
			}
		}

		$result_array["total"] = count($raw_data);
		$result_array["date"] = date("n")." 月 ".date("j", $config['time'])." 日 ".$this->transmitWeekdayStr($config['weekday']);
	 	$result_array["isWeekend"] = "false";
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
			$daily_schedule = new DailySchedule(self::TABLE_SHAEH_SCHEDULE);
		} else if ($student_info['classification'] == '1221') {
			$daily_schedule = new DailySchedule(self::TABLE_XUEYUAN_SCHEDULE);
		}

		$section = $daily_scheduel->select_section($total_minutes);

		return $section['cid'];
	}
	
	/**
	 * 根据信息字符串来获得课程信息
	 *
	 * @param string $info_str a string contain the class information
	 * @return array 返回解析 $info_str 后获得的包含信息的数组
	 */
	private function analyze_info_str($info_str) {
		// store the info 
		$info["s"] = array();
		$info["e"] = array();
		$info["w"] = array();
		$info["k"] = array();
		$info["j"] = array();
		$info["g"] = array();
		
		$patterns["s"] = '/s([0-9]+)/';
		$patterns["e"] = '/e([0-9]+)/';
		$patterns["w"] = '/w([0-9]+)/';
		$patterns["k"] = '/k([0-9]+)/';
		$patterns["j"] = '/j([0-9]+)/';
		$patterns["g"] = '/g([0-9]+)/';

		$match = array();
		foreach ($patterns as $key => $pattern) {
			$times = preg_match($pattern, $info_str, $match);
			if ($times == 1) {
				$info[$key][count($info[$key])] = $match[1];
			}
		}
		
		return $info;
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
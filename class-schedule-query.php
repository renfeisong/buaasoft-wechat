<?php

require_once("utils.php");

/**
 * 数据存储：
 * 数据表名称:schedule_1221, schedule_1321.....
 * 
 * 数据表结构:
 *	 每个数据表中存在5个条目，分别对应周一到周五的课表
 * 需要的字段:1.weekday,2.class1..12
 */
// header("Content-type:text/html;charset='utf-8'");
// $publicLessonQuery = new ClassScheduleQuery();
// $publicLessonQuery->setDevelopEnv();
// print_r($publicLessonQuery->queryClass('1', 1));

/**
 * 临时类，用来分离工具函数，在和外部的utils函数中，添加了一个中间层
 */
class _ScheduleUtils {
	const DB_HOST = "localhost";
	const DB_USER = "root";
	const DB_PASS = "";
	const DB_NAME = "app_sunstaryu";

	/**
	 * 获得数据库连接
	 *
	 * @return object 数据库连接标识
	 */
	public static function getDBConnection() {
		$dbc = null;
		if ($this->env == $this->developEnv) {
			$dbc = mysql_connect(self::DB_HOST, self::DB_USER);
			mysql_query("set names 'utf8'", $dbc);
			mysql_select_db(self::DB_NAME, $dbc);
		} else if ($this->env == $this->formalEnv) {
			$dbc = mysql_connect(SAE_MYSQL_HOST_M.":".SAE_MYSQL_PORT, SAE_MYSQL_USER, SAE_MYSQL_PASS);
			mysql_query("set names 'utf8'", $dbc);
			mysql_select_db(SAE_MYSQL_DB, $dbc);
		}

		return $dbc;
	}

	

	/**
	 * 创建沙河作息时间表
	 */
	public static function _createShaheDailySchedule() {
		$shaheDS = new DailySchedule("shahe_schedule");
		// morning
		$shaheDS->addClass(8, 10, 9, 0, 1);
		$shaheDS->addBreak(9, 0, 9, 10, 1);
		$shaheDS->addClass(9, 10, 10, 0, 2);
		$shaheDS->addBreak(10, 0, 10, 10, 2);
		$shaheDS->addClass(10, 10, 11, 0, 3);
		$shaheDS->addBreak(11, 0, 11, 10, 3);
		$shaheDS->addClass(11, 10, 12, 0, 4);

		// noon
		$shaheDS->addBreak(12, 0, 13, 30, 4);

		$shaheDS->addClass(13, 30, 14, 20, 5);
		$shaheDS->addBreak(14, 20, 14, 30, 5);
		$shaheDS->addClass(14, 30, 15, 20, 6);
		$shaheDS->addBreak(15, 20, 15, 30, 6);
		$shaheDS->addClass(15, 30, 16, 20, 7);
		$shaheDS->addBreak(16, 20, 16, 30, 7);
		$shaheDS->addClass(16, 30, 17, 20, 8);

		// afternoon
		$shaheDS->addBreak(17, 20, 18, 20, 8);
	}

	/**
	 * 定义class的类型为varchar(60)
	 */
	public static function _createTable($tableName) {
		$dbc = self::getDBConnection();
		$createSql = "create table ".$tableName." (day int, class1 varchar(60), class2 varchar(60), class3 varchar(60), class4 varchar(60), class5 varchar(60), class6 varchar(60), class7 varchar(60))"
		mysqli_query("");
	}
}

/**
 * Class ClassScheduleQuery
 */
class ClassScheduleQuery
{
	//时间信息
	const MINUTE_PER_CLASS = 50;//每节课的时间
	const MINUTE_BETWEEN_CLASS = 10;//课间休息的时间

	//数据库表名
	const SCHEDULE_1221 = "schedule_1221";
	const SCHEDULE_1321 = "schedule_1321";
	const SCHEDULE_1421 = "schedule_1421";

	//每天最多的课程数
	const NUM_MAX_CLASS = 11;

	//数据库中每天最多的课程数
	const NUM_MAX_CLASS_DB = 7;

	const STR_FIRST_DAY = "2014-9-15";

	private $shaheSchedule;
	private $campusRoadSchedule;

	private $developEnv = "develop";
	private $formalEnv = "formal";

	private $env = "unknown";

	function __construct() {
		$this->setFormalEnv();
	}
	/**
	 * 向外开放的查询接口
	 *
	 * @param $openid string openid
	 * @param $timeStamp int 时间戳
	 */
	public function queryClass($openid, $timeStamp) {
		$this->preQuery($openid, $timeStamp);
	}

	/**
	 * 完成查询之前的预处理，预处理完成后将调用onQuery()
	 *
	 * @param $openid string openid
	 * @param $timeStamp int 时间戳
	 */
	private function preQuery($openid, $timeStamp) {
		/* 查询所需要的信息 */
		$config = array();
		if ($this->env == $this->formalEnv) { // 正常环境下获得需要的信息
			$config['time'] = $timeStamp;

			//今天是周几，用数字表示：1是星期一，0是星期日
			$config['day'] = date("w", $timeStamp);

			//如果是星期六或者星期日
			if ($config['day'] == 6 || $config['day'] == 0) {
				return array (
					"isWeekend"=>"true", 
					"total"=>"0", 
					"date"=>date("n", $timeStamp)." 月 ".date("j", $timeStamp)." 日 ".$this->transmitWeekdayStr($config['day']));
			}

			//学生信息
			$studentInfo = $this->getStudentInfo($openid);
			$config['student'] = $studentInfo;
			
			// 对应的数据表名字
			$config['table'] = $this->getScheduleTable($studentInfo);

			//当前的课
			$config['class'] = $this->getClassFromTime($timeStamp);
			
			//学生对应的组别
			$config['group'] = $this->getGroup($studentInfo);

			//周数
			$config['week'] = $this->getWeek(self::STR_FIRST_DAY);
		} else if ($this->env == $this->developEnv) { // 开发环境下设置的信息值
			$config['time'] = strtotime("2014-9-15") + 12 * 60 * 60;
			$config['day'] = 1;
			$studentInfo["openid"] = "develop openid";
			$studentInfo['classification'] = "1421";

			$config['student'] = $studentInfo;
			$config['table'] = $this->getScheduleTable($studentInfo);
			$config['class'] = 5;
			$config['group'] = 1;
			$config['week'] = 2;
		}

		return $this->onQuery($config);
	}

	/**
	 * 查询数据库中课程的主要函数
	 *
	 * @param config array preQuery函数中所设置的信息数组
	 *
	 * @return array 返回
	 */
	private function onQuery($config)
	{
		//连接到数据库
		$dbc = $this->getDBConnection();
		$resultArray = array();
		//查询特定某一天的公用课表
		$result = mysql_query("select * from ".$config['table']." where day=".$config['day'], $dbc);

		if ($result == false) {
			echo "Server Database Error";
			assert(false);
		}

		$classCount = 0;

		$row = mysql_fetch_array($result);
		
		// 循环添加课表内容
		for ($i = 1; $i <= self::NUM_MAX_CLASS_DB; $i++) {//查找一天当中的所有课程
			// 查询键值为 class1 - class7
			$content = $row['class'.$i];
			
			//当内容不为空
			if ($content != "") {
				//取出信息字符串 configStr 例如：s1e2
				$classInfo = explode("#", $content);
				$classStr = $classInfo[0];
				$infoStr = $classInfo[1];
				
				$info = $this->analyzeInfoStr($infoStr);
				// 匹配
				if ($this->isInfoMatch($info, $config)) {
					$info['s'] = ($info['s'] < $config['class']) ? $config['class'] : $info['s'];

					// 格式：第1-2节 口语 J0-000
					$resultArray[$classCount]["s"] = $info['s'][0];
					$resultArray[$classCount]["e"] = $info['e'][0];
					$resultArray[$classCount]["str"] = $classStr;
					$classCount++;
				}
			}
		}
		
		return $this->postQuery($resultArray, $config);
	}

	/**
	 * 对$resultArray的内容进行进一步的处理，用以返回格式化的数据
	 *
	 * @param $rawData array 由onQuery函数查询产生的结果
	 */
	private function postQuery($rawData, $config) {
		$resultArray = array();

		foreach ($rawData as $index => $info) {
			print_r($info);
			if ($info['s'] != $info['e']) {
				$resultArray[$index."classInfo"] = "第".$info['s'].'-'.$info['e'].' '.$info['str'];
			} else {
				$resultArray[$index."classInfo"] = "第".$info['s'].' '.$info['str'];
			}
		}

		$resultArray["total"] = count($rawData);
		$resultArray["date"] = date("n")." 月 ".date("j", $config['time'])." 日 ".$this->transmitWeekdayStr($config['day']);
	 	$resultArray["isWeekend"] = "false";
	 	print_r($resultArray);
		return $resultArray;
	}

	/**
	 * 设成运行环境为develop，用于开发下测试
	 */
	public function setDevelopEnv() {
		$this->setEnv($this->developEnv);
	}

	/**
	 * 设置运行环境
	 *
	 * @todo 希望不再使用类似函数
	 */
	private function setEnv($env) {
		$this->env = $env;
	}

	/**
	 * 设成运行环境为formal
	 */
	public function setFormalEnv() {
		$this->setEnv($this->formalEnv);
	}

	/**
	 * 根据时间差获得要上的课程
	 * 
	 * 例如：8:20与早上时段的开始时间(MIN_CLASS_START_AM):8:10之间的时间差是10min
	 * 据此来计算当前正在上的课程应1是第一节课
	 * 
	 * @param minBetween 与最接近的时段开始时间(MIN_CLASS_START)之间的时间差
	 */
	private function computeClassId($minBetween)
	{
		//为了避免当在课间的时候被算到前一节课，例如minBetween=50时，虽然已经下课，但是会被计算到上一节课，所以加上课间时间
		$minBetween += self::MINUTE_BETWEEN_CLASS;
		return (int)($minBetween / (self::MINUTE_BETWEEN_CLASS + self::MINUTE_PER_CLASS));
	}

	/**
	 * 忽地用户对应的课表的数据表名称
	 *
	 * @param $studentInfo array 关于学生信息的数组
	 * 
	 * @return 学生所使用的课表数据库
	 */
	private function getScheduleTable($studentInfo) {
		switch ($studentInfo["classification"]) {
			case "1221": return self::SCHEDULE_1221;
			case "1321": return self::SCHEDULE_1321;
			case "1421": return self::SCHEDULE_1421;
			default: return -1;
		}
	}

	/**
	 * 来对学生进行分组
	 * 
	 * @param $studentInfo array 关于学生信息的数组
	 *
	 * @return 学生对应的组别
	 */
	private function getGroup($studentInfo)
	{
		//提取出学号的后3位，并转换成int
		$num = (int)substr($studentInfo["class"], 5, 1);

		return $num;
	}
	
	/**
	 * 根据学生微信openId获得学生信息，这个函数不应该存在该文件里
	 * @param openid string 学生微信opneid
	 * 
	 * @return array 包含学生信息的数组
	 */
	private function getStudentInfo($openid)
	{
		$dbc = $this->getDBConnection();
	
		$result = mysql_query("select * from user where openid='$openid'", $dbc);

		if ($result == false) {
			echo "Server Database Error";
			return -1;
		} else if (mysql_affected_rows($dbc) == 0) {
			echo "Cannot found openid : ".$openid;
			return -1;
		}

		$studentInfo = mysql_fetch_array($result);
		//关闭连接
		mysql_close($dbc);

		return $studentInfo;
	}
	
	/**
	 * 根据时间戳获得要上的课的id
	 * 
	 * @param timeStamp number UNIX时间戳
	 * @return int 当前正在上的课程
	 */
	private function getClassFromTime($timeStamp)
	{
		$curHour = (int)date("H", $timeStamp);
		$curMinute = (int)date("i", $timeStamp);
		
		$totalMinutes = $curHour * 60 + $curMinute;
		
		if($totalMinutes <= $this->shaheSchedule["min_class_start_am"]) {
			$curClass = 1;
		} else if($totalMinutes < $this->shaheSchedule["min_class_end_am"]) {
			$curClass = 1 + $this->computeClassId($totalMinutes - $this->shaheSchedule["min_class_start_am"]);
		} else if($totalMinutes < $this->shaheSchedule["min_class_start_pm"]) {
			$curClass = 5;
		} else if ($totalMinutes < $this->shaheSchedule["min_class_end_pm"]) {
			$curClass = 5 + $this->computeClassId($totalMinutes - $this->shaheSchedule["min_class_start_pm"]);
		} else if ($totalMinutes < $this->shaheSchedule["min_class_start_night"]) {
			$curClass = 9;
		} else if ($totalMinutes < $this->shaheSchedule["min_class_end_night"]) {
			$curClass = 9 + $this->computeClassId($totalMinutes - $this->shaheSchedule["min_class_start_night"]);
		} else {//其他的时间都被作为超过最大节数
			$curClass = self::NUM_MAX_CLASS + 1;
		}
		return $curClass;
	}
	
	/**
	 * 根据信息字符串来获得课程信息
	 *
	 * @param $speStr string 包含课程时间信息的字符串
	 *
	 * @return array 返回解析 $infoStr 后获得的包含信息的数组
	 */
	private function analyzeInfoStr($infoStr)
	{
		// 记录特殊信息用
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
			$times = preg_match($pattern, $infoStr, $match);

			if ($times == 1) {
				$info[$key][count($info[$key])] = $match[1];
			}
		}
		
		return $info;
	}
	
	/**
	 * 检查要查询的课程状态是否符合
	 *
	 * @param $config array 查询所需要的配置信息
	 * @param $info array 课程的信息
	 *
	 * @return boolean 当课程信息和配置信息符合时，返回true，否则返回false
	 */
	private function isInfoMatch($info, $config)
	{
		$counts = array(
			"s" => count($info['s']), 
			"e" => count($info['e']), 
			"k" => count($info['k']), 
			"j" => count($info['j']), 
			);
		if ($counts['s'] != $counts['e'] || $counts['k'] != $counts['j']) {
			echo "config is illegal";
			return false;
		}

		//是否在组中 -- g
		$inGroup = false;
		
		foreach ($info["g"] as $group) {
			$inGroup |= ($group == $config["group"]);
		}

		if (count($info["g"]) != 0 && !$inGroup) {
			return false;
		}

		//是否在正确的周数 -- k -- j
		$inTime = false;
		
		for ($i = 0, $weekCount = $counts['k']; $i < $weekCount; $i++) {
			$inTime |= ($info['k'][$i] <= $config['week'] && $config['week'] <= $info['j'][$i]);
		}
		
		if ($counts['k'] != 0 && !$inTime) {
			return false;
		}

		// 是否在正确的奇偶周数 -- w
		if (count($info['w']) != 0 && $config['w'] % 2 != $info['w'][0]) {
			return false;
		}
		
		// 是否在正确的时间内 -- e
		if ($info['e'][0] < $config['class']) {
			
			return false;
		}

		return true;
	}
}

/**
 * ClassScheduleQuery类和数据库之间连接的接口
 */
class Schedule {
	private $scheduleDayArray;
	private $tableName;
	
	function __construct($tableName) {
		$this->tableName = $tableName;
		$scheduleDayArray = array();
	}

	/**
	 * 删除数据表中已有的数据
	 * @return bool
	 */
	public function clear() {
		$dbc = _ScheduleUtils::getDBConnection();
		$deleteSql = "delete from " + $tableName;
		$result = mysqli_query($dbc, $deleteSql);

		if ($result == false) {
			assert(false);
		}

		$affectedRows = mysqli_affected_rows($dbc);

		echo "删除了".$affectedRows."行";
		return true;
	}

	/**
	 * 设置要添加数据的日期
	 * 
	 * @param $day int 当前是星期几
	 *
	 * @return object 返回scheduleDay
	 */
	public function setDay($day) {
		$scheduleDay = new ScheduleDay($day);
		array_push($scheduleDayArray, $ScheduleDay);

		return $scheduleDay;
	}

	/**
	 * 保存内容到数据库
	 */
	public function save() {
		$dbc = _ScheduleUtils::getDBConnection();
		// 清空
		for ($scheduleDayArray as $scheduleDay) { 
			
		}
	}
}

/**
 * Class ScheduleDay
 */
class ScheduleDay {
	private $day;
	private $classArray = array();
	private $infoArray = array();

	function __construct($day) {
		$this->day = $day;
	}

	/**
	 * 添加课程
	 * 
	 * @param $classStr string 关于课程的内容
	 * @param $infoStr string 关于课程附加的内容
	 */
	public function addClass($classStr, $infoStr) {
		array_push($classArray, $classStr);
		array_push($infoArray, $infoStr);
		return $this;
	}
}


?>
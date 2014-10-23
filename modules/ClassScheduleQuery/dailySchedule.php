<?php

// print_r(daily_scheduel_test());
// $wxdb = null;

function daily_scheduel_test() {
	global $wxdb;
	require_once("../../includes/wxdb.php");
	$wxdb = new wxdb("root", "root", "weixin", "localhost");

	$shahe = new DailySchedule("shahe_schedule");
	// morning
	$shahe->add_class_str(1, "8:10", "9:00")
		  ->add_break_str(2, "9:00", "9:10")
		  ->add_class_str(2, "9:10", "10:00")
		  ->add_break_str(3, "10:00", "10:10")
		  ->add_class_str(3, "10:10", "11:00")
		  ->add_break_str(4, "11:00", "11:10")
		  ->add_class_str(4, "11:10", "12:00");

	// noon
	$shahe->add_break_str(5, "12:00", "13:30");

	$shahe->add_class_str(5, "13:30", "14:20")
		  ->add_break_str(6, "14:20", "14:30")
		  ->add_class_str(6, "14:30", "15:20")
		  ->add_break_str(7, "15:20", "15:30")
		  ->add_class_str(7, "15:30", "16:20")
		  ->add_break_str(8, "16:20", "16:30")
		  ->add_class_str(8, "16:30", "17:20");

	// afternoon
	$shahe->add_break_str(9, "17:20","18:20");
	$shahe->save();
	return $shahe->get_sections();
}

/**
 * Class DailySchedule
 *
 * the daily schedule database model
 *
 * 需要的数据库字段:1.一个主键,2.开始时间,3.结束时间,4.时间段类型
 * 不需要的内容:1.表所对应的校区
 * database table scheme:
 * 1. id int (PK, A_I)
 * 2. cid int
 * 3. startTime int 
 * 4. endTime int 
 * 5. type int
 * @todo change according to wxdb.php
 * @todo modify the scheme
 */
class DailySchedule {
	// database table name
	private $table_name;
	// store info
	private $sections;
	// section type
	const SECTION_TYPE_CLASS = 1;
	const SECTION_TYPE_BREAK = 2;

	function __construct($table_name) {
		$this->set_table($table_name);
		$this->sections = array();
	}

	/**
	 * 设置数据表名称，并检测数据库是否存在，不存在则创建，
	 *
	 * @param $table_name string 数据表名称
	 * @return 当设置成功时，返回true，否则返回false
	 * @todo changing according to wxdb.php
	 */
	public function set_table($table_name) {
		$this->table_name = $table_name;
		$this->create_table($table_name);
	}

	public function get_sections() {
		return $this->sections;
	}

	/**
	 * create the database table
	 * @todo change the scheme
	 */
	public function create_table($table_name) {
		global $wxdb;
		$wxdb->query("CREATE TABLE IF NOT EXISTS $table_name (cid int NOT NULL AUTO_INCREMENT, type int NOT NULL, startTime int NOT NULL, endTime int, PRIMARY KEY (`cid`)) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1");
	}

	/**
	 * a prepare implementation for add_section
	 * add a section (type == SECTION_TYPE_BREAK)
	 *
	 * @param int $cid the class id, e.g. the first class => 1
	 * @param int $startTime start time
	 * @param int $endTime end time
	 */
	public function add_break_str($cid, $start_time, $end_time) {
		return $this->add_section($cid, self::SECTION_TYPE_BREAK, $start_time, $end_time);
	}

	public function add_break_int($cid, $start_hour, $start_min, $end_hour, $end_min) {
		return $this->add_section($cid, self::SECTION_TYPE_BREAK, $start_hour * 60 + $start_min, $end_hour * 60 + $end_min);
	}

	/**
	 * a prepare implementation for add_section
	 */
	public function add_class_str($cid, $start_time, $end_time) {
		return $this->add_section($cid, self::SECTION_TYPE_CLASS, $start_time, $end_time);
	}

	public function add_class_int($cid, $start_hour, $start_min, $end_hour, $end_min) {
		return $this->add_section($cid, self::SECTION_TYPE_CLASS, $start_hour * 60 + $start_min, $end_hour * 60 + $end_min);
	}

	/**
	 * add section
	 * 
	 * @param int $cid section type
	 * @param $type int section type
	 * @param int/string $start_time start time in int or string
	 * @param int/string $end_time end time in int or string, the e.g. 480/"8:00"
	 * @return object the instance of dailySchedule
	 */
	private function add_section($cid, $type, $start_time, $end_time) {

		$section = null;

		if (is_int($start_time) && is_int($end_time)) {
			$section = array(
				"type"=>$type, 
				"start"=>$start_time, 
				"end"=>$end_time
			);
		} else if (is_string($start_time) && is_string($end_time)) {
			$_start_time = explode(":", $start_time);
			$_end_time = explode(":", $end_time);

			$section = array(
				"cid"=>$cid, 
				"type"=>$type, 
				"startTime"=>(int)$_start_time[0] * 60 + (int)$_start_time[1], 
				"endTime"=>(int)$_end_time[0] * 60 + (int)$_end_time[1]
			);
		} else {
			// error
		}
		
		array_push($this->sections, $section);
		return $this;
	}

	/**
	 * select the section according to minutes
	 * @param int $minute total minutes from zero o'clock
	 * @return object the time section
	 */
	public function select_section($minutes) {
		$sections = $this->sections;
		foreach ($sections as $section) {
			if ($section['startTime'] < $minutes && $minutes <= section['endTime']) {
				return $section;
			}
		}
	}

	/**
	 * clear the data
	 *
	 * @return return true if success, or false.
	 * @todo need test
	 */
	public function delete() {
		global $wxdb;
		return $wxdb->query("DELETE FROM $this->table_name");
	}

	/**
	 * query the data and fill this instance
	 *
	 * @return 成功时返回true, 否则返回false
	 */
	public function query() {
		global $wxdb;
		$results= $wxdb->get_results("SELECT * FROM $this->table_name");

		// fill data
		foreach ($results as $result) {
			$section = array(
				"cid"=>$result['cid'],
				"type"=>$result['type'], 
				"startTime"=>$result['start'], 
				"endTime"=>$result['end']
				);
			array_push($this->sections, $section);
		}
		return true;
	}

	/**
	 * save info into database
	 */
	public function save() {
		global $wxdb;
		$section_count = count($this->sections);

		if ($section_count <= 0)

			return true;
		
		// clean old data
		if ($this->delete() === 'false')
			return false;

		// query
		for ($i = 0; $i < $section_count; $i++) {
			$result = $wxdb->insert($this->table_name, $this->sections[$i]);
			if (!$result) {
				echo "fail";
			}
		}
	}
}

?>
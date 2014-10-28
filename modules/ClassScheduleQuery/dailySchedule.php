<?php

// daily_scheduel_test();

function daily_scheduel_test() {
	global $wxdb;
	require_once("../../config.php");

	$shahe = new DailySchedule(DailySchedule::TABLE_SHAEH_SCHEDULE);

	// clean the table
	$shahe->drop_table();
	$shahe->create_table();

	$shahe->delete();
	$shahe->query();
	print_r($shahe->get_sections()); // must be empty
	echo "\n";
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

	$shahe->add_class_str(9, "18:20", "19:10")
		  ->add_break_str(10, "19:10", "19:20")
		  ->add_class_str(10, "19:20", "20:10")
		  ->add_break_str(11, "20:10", "20:20")
		  ->add_class_str(11, "20:20", "21:10")
		  ->add_break_str(12, "21:10", "21:20")
		  ->add_class_str(12, "21:20", "22:10");

	// need add the midnight?
	$shahe->save();
	$shahe->clean();
	print_r($shahe->get_sections()); // must be empty
	echo "\n";
	$shahe->query();
	print_r($shahe->get_sections()); // the database content
	echo "\n";

	// just a sample
	$xueyuan = new DailySchedule(DailySchedule::TABLE_XUEYUAN_SCHEDULE);
	$xueyuan->drop_table();
	$xueyuan->create_table();
	// morning
	$xueyuan->add_class_str(1, "8:00", "8:50")
			->add_break_str(2, "8:50", "8:55")
			->add_class_str(2, "8:55", "9:45")
			->add_break_str(3, "9:45", "10:00")
			->add_class_str(3, "10:00", "10:50")
			->add_break_str(4, "10:50", "10:55")
			->add_class_str(4, "10:55", "11:45");

	// noon
	$xueyuan->add_break_str(5, "11:45", "14:00");

	$xueyuan->add_class_str(5, "14:00", "14:50")
			->add_break_str(6, "14:50", "14:55")
			->add_class_str(6, "14:55", "15:45")
			->add_break_str(7, "15:45", "16:00")
			->add_class_str(7, "16:00", "16:50")
			->add_break_str(8, "16:50", "16:55")
			->add_class_str(8, "16:55", "17:45");

	// afternoon
	$xueyuan->add_break_str(9, "17:45","18:00");

	$xueyuan->add_class_str(9, "18:00", "18:50")
			->add_break_str(10, "18:50", "18:55")
			->add_class_str(10, "18:55", "19:45")
			->add_break_str(11, "19:45", "20:00")
			->add_class_str(11, "20:00", "20:50")
			->add_break_str(12, "20:50", "20:55")
			->add_class_str(12, "20:55", "21:45");

	// need add the midnight?
	$xueyuan->save();
}

/**
 * Class DailySchedule
 *
 * the daily schedule database model. 
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

	// the database table name
	const TABLE_SHAEH_SCHEDULE = "shahe_schedule";
	const TABLE_XUEYUAN_SCHEDULE = "xueyuan_schedule";

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

	/**
	 * can get the data query from database.
	 * 
	 * @see query()
	 * @return array all time section @see $this->sections
	 */
	public function get_sections() {
		return $this->sections;
	}

	/**
	 * create the database table
	 * @todo change the scheme is hard
	 */
	public function create_table() {
		global $wxdb;
		$table_name = $this->table_name;
		$wxdb->query("CREATE TABLE IF NOT EXISTS $table_name (id int NOT NULL AUTO_INCREMENT, cid int NOT NULL, type int NOT NULL, startTime int NOT NULL, endTime int, PRIMARY KEY (`id`)) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1");
	}

	public function drop_table() {
		global $wxdb;
		$table_name = $this->table_name;
		$wxdb->query("DROP TABLE $table_name");
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
		
		$this->sections[] = $section;
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
			if ($section['startTime'] < $minutes && $minutes <= $section['endTime']) {
				return $section;
			}
		}
	}

	/**
	 * clean the content of $this->sections
	 */
	public function clean() {
		$this->sections = array();
	}

	/**
	 * clear the data
	 *
	 * @return return true if success, or false.
	 */
	public function delete() {
		global $wxdb;
		return $wxdb->query("TRUNCATE TABLE $this->table_name");
	}

	/**
	 * query the data and fill this $this->sections
	 *
	 * @return bool return true if success, or false
	 */
	public function query() {
		global $wxdb;
		$wxdb->query("SELECT * FROM $this->table_name");
		$results = $wxdb->last_result;
		// fill data
		foreach ($results as $result) {
			$section = array(
				"cid"=>$result->cid, 
				"type"=>$result->type, 
				"startTime"=>$result->startTime, 
				"endTime"=>$result->endTime
				);
			$this->sections[] = $section;
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
			if ($result != 1) {
				echo "last_error:$wxdb->last_error";
			}
		}
	}
}

?>
<?php

//daily_scheduel_test();

function daily_scheduel_test() {
	global $wxdb;
	require_once("../../config.php");

	$shahe = new DailySchedule(DailySchedule::TABLE_SHAHE_SCHEDULE);

	// clean the table
	$shahe->drop_table();
	$shahe->create_table(DailySchedule::TABLE_SHAHE_SCHEDULE);

	$shahe->delete();
	$shahe->query();
	print_r($shahe->get_sections()); // must be empty
	echo "\n";
	// morning
	$shahe->add_class_str(1, "8:10", "9:00")

        // the sequence is wrong
          ->add_class_str(2, "9:10", "10:00")

		  ->add_class_str(3, "10:10", "11:00")
		  ->add_class_str(4, "11:10", "12:00");

	// noon

	$shahe->add_class_str(5, "13:30", "14:20")
		  ->add_class_str(6, "14:30", "15:20")
		  ->add_class_str(7, "15:30", "16:20")
		  ->add_class_str(8, "16:30", "17:20");

	// afternoon

	$shahe->add_class_str(9, "18:20", "19:10")
		  ->add_class_str(10, "19:20", "20:10")
		  ->add_class_str(11, "20:20", "21:10")
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
	$xueyuan->create_table(DailySchedule::TABLE_XUEYUAN_SCHEDULE);
	// morning
	$xueyuan->add_class_str(1, "8:00", "8:50")
			->add_class_str(2, "8:55", "9:45")
			->add_class_str(3, "10:00", "10:50")
			->add_class_str(4, "10:55", "11:45");

	// noon

	$xueyuan->add_class_str(5, "14:00", "14:50")
			->add_class_str(6, "14:55", "15:45")
			->add_class_str(7, "16:00", "16:50")
			->add_class_str(8, "16:55", "17:45");

	// afternoon

	$xueyuan->add_class_str(9, "18:00", "18:50")
			->add_class_str(10, "18:55", "19:45")
			->add_class_str(11, "20:00", "20:50")
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
 * 2. cid int Class id or cid is the id of the class and corresponding break after class
 * 3. startTime int 
 * 4. endTime int
 * @todo change according to wxdb.php
 * @todo modify the scheme
 */
class DailySchedule {
    // database table name
    private $table_name;
	// store all time info about shahe campus
	private $sections;

	// the database table name
	const TABLE_SHAHE_SCHEDULE = "shahe_schedule";
	const TABLE_XUEYUAN_SCHEDULE = "xueyuan_schedule";

	function __construct($table_name) {
        $this->set_table_name($table_name);
		$this->sections = array();
	}

    /**
     * set the database table name
     * @param string $table_name database table name
     */
    public function set_table_name($table_name) {
        $this->table_name = $table_name;
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
     * @todo need to check if the table schema is right
	 */
	public static function create_table($table_name) {
		global $wxdb;
        $table_schema = <<<SQL
CREATE TABLE IF NOT EXISTS `{$table_name}`
(id int NOT NULL AUTO_INCREMENT,
cid int NOT NULL,
startTime int NOT NULL,
endTime int,
PRIMARY KEY (`id`))
ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1
SQL;
		$wxdb->query($table_schema);
	}

    /**
     * to sort the section according to cid
     * @param $a
     * @param $b
     */
    public static function section_compare($a, $b) {
        if ($a['cid'] < $b['cid']) {
            return -1;
        } else if ($a['cid'] > $b['cid']) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * sample input '8:00'
     * try to transmit '8:00' to the minutes type '480'
     * @param $str
     * @return mixed
     */
    public static function toInt($str) {
        $info = explode(":", $str);
        return (int)$info[0] * 60 + (int)$info[1];
    }

    /**
     * the input must be integer
     * @param $int
     */
    public static function toStr($int) {
        $hour = (int)($int / 60);
        $minute = (int)($int % 60);
        $minute = $minute < 10 ? "0".$minute : $minute;
        return $hour.":".$minute;
    }

	public function drop_table() {
		global $wxdb;
		$table_name = $this->table_name;
		$wxdb->query("DROP TABLE $table_name");
	}

	/**
	 * a prepare implementation for add_section
	 *
	 * @param int $cid the class id, e.g. the first class => 1
	 * @param int $startTime start time
	 * @param int $endTime end time
	 */
	public function add_class_str($cid, $start_time, $end_time) {
		return $this->add_section($cid, $start_time, $end_time);
	}

	public function add_class_int($cid, $start_hour, $start_min, $end_hour, $end_min) {
		return $this->add_section($cid, $start_hour * 60 + $start_min, $end_hour * 60 + $end_min);
	}

	/**
	 * add section
	 * 
	 * @param int $cid section type
	 * @param int/string $start_time start time in int or string
	 * @param int/string $end_time end time in int or string, the e.g. 480/"8:00"
	 * @return object the instance of dailySchedule
	 */
	private function add_section($cid, $start_time, $end_time) {

		$section = null;

		if (is_int($start_time) && is_int($end_time)) {
			$section = array(
				"start"=>$start_time, 
				"end"=>$end_time
			);
		} else if (is_string($start_time) && is_string($end_time)) {
			$section = array(
				"cid"=>$cid,
				"startTime"=>self::toInt($start_time),
				"endTime"=>self::toInt($end_time)
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
	 * query the data and fill this $this->sections, $this->section will be sorted according to function section_compare
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
				"startTime"=>$result->startTime, 
				"endTime"=>$result->endTime
				);
			$this->sections[] = $section;
		}
        usort($this->sections, "self::section_compare");
//        print_r($this->sections);
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
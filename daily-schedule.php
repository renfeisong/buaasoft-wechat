<?php
/**
 * @todo 测试
 */
require_once("db-helper.php");

/**
 * 对于数据库中的作息时间表的操作
 * 需要的数据库字段:1.一个主键,2.开始时间,3.结束时间,4.时间段类型
 * 不需要的内容:1.表所对应的校区
 * 规定字段:1.sid int, startTime int , endTime int , type
 */
class DailySchedule {
	/* 需要操作的数据表名称 */
	private $tableName;
	/* 保存的数据 */
	private $sections;
	/* section类型 */
	const SECTION_TYPE_CLASS = 1;
	const SECTION_TYPE_BREAK = 2;

	function __construct($tableName) {
		$this->setTable($tableName);
		$this->sections = array();
	}

	/**
	 * 设置数据表名称，并检测数据库是否存在，不存在则创建，
	 *
	 * @param $tableName string 数据表名称
	 * @todo 完善数据表生成SQL，如果数据库中没有该表的情况下，将添加一个新的表
	 * @return 当设置成功时，返回true，否则返回false
	 */
	public function setTable($tableName) {
		$this->tableName = $tableName;

		// 生成新的表
		$dbc = DB::connect();
		$sql = "create table ".$tableName."";
	}

	/**
	 * 向$this->sections中添加一个 "课间"
	 *
	 * @param $startTime int 开始时间，单位分钟
	 * @param $endTime int 结束时间，单位分钟
	 * @param $sid int 课程序号
	 */
	public function addBreak($startHour, $startMin, $endHour, $endMin, $sid) {
		$this->addSection(self::SECTION_TYPE_BREAK, $startHour * 60 + $startMin, $endHour * 60 + $endMin, $sid);
	}

	/**
	 * 向$this->sections中添加一个 "课上"
	 *
	 * @param $startTime int 开始时间，单位分钟
	 * @param $endTime int 结束时间，单位分钟
	 * @param $sid int 课程序号
	 */
	public function addClass($startHour, $startMin, $endHour, $endMin, $sid) {
		$this->addSection(self::SECTION_TYPE_CLASS, $startHour * 60 + $startMin, $endHour * 60 + $endMin, $sid);
	}

	/**
	 * 向$sections中添加新的时间段内容
	 * 
	 * @param $type int section类型
	 * @param $startTime int 开始时间，单位分钟
	 * @param $endTime int 结束时间，单位分钟
	 * @param $sid int 课程序号
	 * @return 成功返回true, 否则返回false
	 */
	private function addSection($type, $startTime, $endTime, $sid) {
		$section = array(
			"type"=>$type, 
			"start"=>$startTime, 
			"end"=>$endTime, 
			"sid"=>$sid
			);
		array_push($this->sections, $section);
		return true;
	}

	/**
	 * for safe
	 */
	private function checkTable() {
		if (null == $tableName) {
			echo "没有设置table";
			return false;
		}
		return true;
	}

	/**
	 * 删除时间表中的所有数据
	 *
	 * @return 成功返回true, 否则返回false
	 * @todo 希望能处理false的情况
	 */
	public function delete() {
		checkTable();
		$dbc = DB::connect();

		$deleteSql = "delete from ".$tableName;
		$result = mysqli_query($dbc, $deleteSql);

		return $result;
	}

	/**
	 * 使用数据库中的数据填充当前对象
	 *
	 * @return 成功时返回true, 否则返回false
	 */
	public function query() {
		checkTable();

		$dbc = DB::connect();
		$selectSql = "select * from ".$tableName;
		$result = mysqli_query($dbc, $selectSql);

		if ($result == false) {
			return false;
		}

		// 填充数据
		while ($row = mysqli_fetch_array($result)) {
			$section = array(
				"type"=>$row['type'], 
				"start"=>$row['start'], 
				"end"=>$row['end'], 
				"sid"=>$row['sid']
				);
			array_push($this->sections, $section);
		}
		return true;
	}

	/**
	 * 向数据库中添加数据，如果指定的数据表中已经存在数据，那么将覆盖原本的数据
	 * 当对象中没有数据时将不会执行任何动作，并返回成功
	 *
	 * @
	 */
	public function save() {
		checkTable();

		$dbc = DB::connect();
		$sectionCount = count($this->sections);

		if ($sectionCount <= 0) {
			return true;
		}

		// clean old data
		!$this->delete() or return false;

		// prepare new data
		$insertSql = "insert into ".$tableName." (type, start, end, sid) values ";
		$tailSection = array_pop($sections);
		for ($sections as $section) { 
			$insertSql .= '('.$section['type'].','.$section['start'].','.$section['end'].','.$section['sid'].'),';
		}
		$insertSql .= '('.$tailSection['type'].','.$tailSection['start'].','.$tailSection['end'].','.$tailSection['sid'].')';

		// query
		$result = mysqli_query($dbc, $insertSql);
		if ($result == false) {
			assert(false);
		}

		$affectedRows = mysqli_affected_rows($dbc);

		if ($debug) {
			echo $insertSql;
		}
	}
}

?>
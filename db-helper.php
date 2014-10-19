<?php 

class DB {

	/**
	 * 获得数据库连接
	 */
	public static function connect() {
		$dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME) or die("Cannot connect to database.");
    	mysqli_query($dbc, "set names 'utf8'");
    	return $dbc;
	}

}

?>
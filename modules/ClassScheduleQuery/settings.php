<?php 
require_once("ClassSchedule.php");
require_once("DailySchedule.php");
?>
<h2>课程查询和作息时间表设定</h2>

<!-- 需要设置好多个年级的内容 -->
<!-- todo:change the classification according to year -->
<!-- todo:now must expose the database implementation -->
<!-- todo:need test -->
<!-- try to add new line, it is hard =.= -->
<h3>大一课程</h3>

<form method="post">
	<?php
		$freshman_class_schedule = new ClassSchedule(ClassSchedule::TABLE_CLASS_SCHEDULE, "1421");
		$classes_all_weekday = $freshman_class_schedule->query_all_weekday();

		foreach ($classes_all_weekday as $weekday => $classes) {
			foreach ($classes as $class) {
				echo "<input type='text' value='$class'>";
				// todo:try to start a new line
				echo "<br/>";
			}
		}
	?>
	<?php submit_button(); ?>
</form>

<h3>设置沙河校区作息时间表</h3>
<!-- 怎么设定队列顺序 -->
<form method="post">
    
    <?php 
    	$shahe = new DailySchedule(DailySchedule::TABLE_SHAHE_SCHEDULE);
    	$shahe->query();
    	$daily_schedule = $shahe->get_sections();

    	foreach ($daily_schedule as $time_section) {
    		echo "<input type='text' value='$time_section['startTime']'><input type='text' value='$time_section['endTime']'>"
    	}
    ?>
    <?php submit_button(); ?>
</form>

<h3>设置学院路校区作息时间表</h3>

<form method="post">
	<?php submit_button(); ?>
</form>

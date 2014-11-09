<?php 
require_once("ClassSchedule.php");
require_once("DailySchedule.php");

// try to change the freshmen class schedule
if (isset($_POST["submit-freshmen-schedule"])) {

}

// try to change the shahe schedule
if (isset($_POST["submit-shahe-schedule"])) {
    $shahe = new DailySchedule(DailySchedule::TABLE_SHAHE_SCHEDULE);

    $cid = 1;
    while (isset($_POST["class_start_time_{$cid}"]) && isset($_POST["class_end_time_{$cid}"])) {
        $shahe->add_class_str($cid, $_POST["class_start_time_{$cid}"], $_POST["class_end_time_{$cid}"]);
        $cid++;
    }

    $cid = 1;
    while (isset($_POST["break_start_time_{$cid}"]) && isset($_POST["break_end_time_{$cid}"])) {
        $shahe->add_break_str($cid, $_POST["break_start_time_{$cid}"], $_POST["break_end_time_{$cid}"]);
        $cid++;
    }
    $shahe->save();
}

// try to change the xueyuan schedule
if (isset($_POST["submit-xueyuan-schedule"])) {
    $xueyuan = new DailySchedule(DailySchedule::TABLE_XUEYUAN_SCHEDULE);

    $cid = 1;
    while (isset($_POST["class_start_time_{$cid}"]) && isset($_POST["class_end_time_{$cid}"])) {
        $xueyuan->add_class_str($cid, $_POST["class_start_time_{$cid}"], $_POST["class_end_time_{$cid}"]);
        $cid++;
    }

    $cid = 1;
    while (isset($_POST["break_start_time_{$cid}"]) && isset($_POST["break_end_time_{$cid}"])) {
        $xueyuan->add_break_str($cid, $_POST["break_start_time_{$cid}"], $_POST["break_end_time_{$cid}"]);
        $cid++;
    }
    $xueyuan->save();
}

?>

<h2>课程查询和作息时间表设定</h2>
<link rel="stylesheet" href="../modules/ClassScheduleQuery/settings.css">
<!-- 需要设置好多个年级的课程内容，并且这些内容都得在同一个页面中设置，这样不好!-->
<!-- todo:change the classification according to year -->
<!-- todo:now must expose the database implementation -->
<!-- todo:need test -->
<!-- try to add new line, it is hard =.= -->
<h3>大一课程</h3>

<form method="post">
	<?php
		$freshmen_class_schedule = new ClassSchedule(ClassSchedule::TABLE_CLASS_SCHEDULE, "1421");
		$classes_all_weekday = $freshmen_class_schedule->query_all_weekday();
        $html = array();

		foreach ($classes_all_weekday as $weekday => $classes) {
			foreach ($classes as $class) {
				$html[] = "<input type='text' value='$class'>";
			}
		}
	?>
    <button type="submit" class="submit-button button button-with-icon green-button" name="submit-freshmen-schedule">提交</button>
</form>

<h3>沙河校区作息时间表</h3>
<!-- 怎么设定队列顺序 -->
<form method="post">
    
    <?php 
    	$shahe = new DailySchedule(DailySchedule::TABLE_SHAHE_SCHEDULE);
    	$shahe->query();
    	$daily_schedule = $shahe->get_sections();
        $html = array();

        // 如果对上课时间进行修改的话，那么下课时间也必须修改
    	foreach ($daily_schedule as $time_section) {
            $cid = $time_section['cid'];
            $start_time = DailySchedule::toStr($time_section['startTime']);
            $end_time = DailySchedule::toStr($time_section['endTime']);
            $html[] = "<div class='form-group row'>";
            //todo need to test
            $html[] = "<p class='col-md-1'>第{$cid}节课</p>";
            $html[] = "<div class='col-md-2'><input class='form-control' type='text' name='class_start_time_{$cid}' value='".$start_time."' /></div>";
            $html[] = "<div class='col-md-2'><input class='form-control' type='text' name='class_end_time_{$cid}' value='".$end_time."' /></div>";

            $html[] = "</div>";
    	}

        // output the html
        echo join("", $html);
    ?>
    <button type="submit" class="submit-button button button-with-icon green-button" name="submit-shahe-schedule">提交</button>
</form>

<h3>学院路校区作息时间表</h3>

<form method="post">

    <?php
    $xueyuan = new DailySchedule(DailySchedule::TABLE_XUEYUAN_SCHEDULE);
    $xueyuan->query();
    $daily_schedule = $xueyuan->get_sections();
    $html = array();

    // 如果对上课时间进行修改的话，那么下课时间也必须修改
    foreach ($daily_schedule as $time_section) {
        $cid = $time_section['cid'];
        $start_time = DailySchedule::toStr($time_section['startTime']);
        $end_time = DailySchedule::toStr($time_section['endTime']);
        $html[] = "<div class='form-group row'>";
        //todo need to test
        $html[] = "<p class='col-md-1'>第 {$cid} 节课</p>";
        $html[] = "<div class='col-md-2'><input class='form-control' type='text' name='class_start_time_{$cid}' value='".$start_time."' /></div>";
        $html[] = "<div class='col-md-2'><input class='form-control' type='text' name='class_end_time_{$cid}' value='".$end_time."' /></div>";
        $html[] = "</div>";
    }

    // output the html
    echo join("", $html);
    ?>
    <button type="submit" class="submit-button button button-with-icon green-button" name="submit-xueyuan-schedule">提交</button>
</form>

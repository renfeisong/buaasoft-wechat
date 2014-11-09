<?php 
require_once("ClassSchedule.php");
require_once("DailySchedule.php");

// extra function
function weekdayToStr($weekday) {
    switch ($weekday) {
        case 0:return "星期日";
        case 1:return "星期一";
        case 2:return "星期二";
        case 3:return "星期三";
        case 4:return "星期四";
        case 5:return "星期五";
        case 6:return "星期六";
    }
}

// try to change the freshmen class schedule
if (isset($_POST["submit-freshmen-schedule"])) {

}

if (isset($_POST["submit-junior-schedule"])) {
    $junior = new ClassSchedule(ClassSchedule::TABLE_CLASS_SCHEDULE, "1221");
    for ($weekday = 1; $weekday <= 5; $weekday++) {
        $junior->set_weekday($weekday);
        for ($i = 1; $i <= ClassSchedule::NUM_MAX_CLASS; $i++) {
            $subscript = "class_{$weekday}_{$i}";
            if (isset($_POST[$subscript]) && $_POST[$subscript] != "") {
                $class_info = explode(ClassSchedule::SEPARATOR, $_POST[$subscript]);
                $junior->add_class($class_info[0], $class_info[1]);
            }
        }
    }
    // save the weekday content
    if ($junior->save()) {
        redirect_success("修改成功");
    } else {
        redirect_failure("修改失败");
    }
}

// try to change the shahe schedule
if (isset($_POST["submit-shahe-schedule"])) {
    $shahe = new DailySchedule(DailySchedule::TABLE_SHAHE_SCHEDULE);

    $cid = 1;
    while (isset($_POST["class_start_time_{$cid}"]) && isset($_POST["class_end_time_{$cid}"])) {
        $shahe->add_class_str($cid, $_POST["class_start_time_{$cid}"], $_POST["class_end_time_{$cid}"]);
        $cid++;
    }

    if ($shahe->save()) {
        redirect_success("修改成功");
    } else {
        redirect_failure("修改失败");
    }
}

// try to change the xueyuan schedule
if (isset($_POST["submit-xueyuan-schedule"])) {
    $xueyuan = new DailySchedule(DailySchedule::TABLE_XUEYUAN_SCHEDULE);

    $cid = 1;
    while (isset($_POST["class_start_time_{$cid}"]) && isset($_POST["class_end_time_{$cid}"])) {
        $xueyuan->add_class_str($cid, $_POST["class_start_time_{$cid}"], $_POST["class_end_time_{$cid}"]);
        $cid++;
    }

    if ($xueyuan->save()) {
        redirect_success("修改成功");
    } else {
        redirect_failure("修改失败");
    }
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

        echo join("", $html);
	?>
    <button type="submit" class="submit-button button button-with-icon green-button" name="submit-freshmen-schedule">提交</button>
</form>

<h3>大三课程</h3>

<form method="post">
    <?php
    $junior_class_schedule = new ClassSchedule(ClassSchedule::TABLE_CLASS_SCHEDULE, "1221");
    $classes_all_weekday = $junior_class_schedule->query_all_weekday();
    $html = array();

    $html[] = "<div class='form-group row'>";
    for ($weekday = 1; $weekday <= 5; $weekday++) {
        $html[] = "<div class='col-md-2'><p class='text-center'>".weekdayToStr($weekday)."</p></div>";
    }
    $html[] = "</div>";

    for ($i = 1; $i < ClassSchedule::NUM_MAX_CLASS; $i++) {

        $html[] = "<div class='form-group row'>";
        for ($weekday = 1; $weekday <= 5; $weekday++) {
            if (isset($classes_all_weekday[$weekday]["class_".$i])) {
                $class = $classes_all_weekday[$weekday]["class_".$i];
            } else {
                $class = "";
            }
            $html[] = "<div class='col-md-2'><input class='form-control' name='class_{$weekday}_{$i}' type='text' value='$class' /></div>";
        }
        $html[] = "</div>";
    }

    echo join("", $html);
    ?>
    <button type="submit" class="submit-button button button-with-icon green-button" name="submit-junior-schedule">提交</button>
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
            $html[] = "<p class='col-md-1'>第{$cid}节</p>";
            $html[] = "<div class='col-md-2'><input class='form-control' type='text' name='class_start_time_{$cid}' value='".$start_time."' /></div>";
            $html[] = "<div class='col-md-2'><input class='form-control' type='text' name='class_end_time_{$cid}' value='".$end_time."' /></div>";

            $html[] = "</div>";
    	}

        // output the html
        echo join("", $html);
    ?>
    <button type="submit" class="submit-button button button-with-icon green-button" name="submit-shahe-schedule">提交</button><span>沙河作息时间表</span>
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
        $html[] = "<p class='col-md-1'>第{$cid}节</p>";
        $html[] = "<div class='col-md-2'><input class='form-control' type='text' name='class_start_time_{$cid}' value='".$start_time."' /></div>";
        $html[] = "<div class='col-md-2'><input class='form-control' type='text' name='class_end_time_{$cid}' value='".$end_time."' /></div>";
        $html[] = "</div>";
    }

    // output the html
    echo join("", $html);
    ?>
    <button type="submit" class="submit-button button button-with-icon green-button" name="submit-xueyuan-schedule">提交</button>
</form>

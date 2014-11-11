<?php
/**
 * Created by PhpStorm.
 * User: timmyxu
 * Date: 14/11/10
 * Time: 下午3:16
 */
header("Content-Type=text/html;charset=utf-8");
$grade = get_option("grade");

if (isset($_POST['display'])) {
    set_option('display_ranking_' . $grade . '_' . $_POST['display'], true);
    redirect_success('已成功显示该表。');
    exit;
}

if (isset($_POST['hide'])) {
    set_option('display_ranking_' . $grade . '_' . $_POST['hide'], false);
    redirect_success('已经隐藏该表。');
    exit;
}

if (isset($_POST['delete'])) {
    $file = dirname(__FILE__) ."/score/ranking_".$grade.'_'.$_POST['delete'].'.csv';
    unlink($file);
    set_option('display_ranking_' . $grade . '_' . $_POST['delete'], false);
    $ranking_list = get_option("ranking_".$grade);
    $key = array_search('ranking_'.$grade.'_'.$_POST['delete'], $ranking_list);
    array_splice($ranking_list, $key, 1);
    set_option("ranking_".$grade, $ranking_list);
    redirect_success("删除成功！");
    exit;
}

if (isset($_POST['modify-info'])) {
    $content = $_POST['content'];
    set_option("ranking_".$grade.'_content', $content);
    redirect_success("修改成功！");
}

if (isset($_POST['add-score'])) {
    $tname = $_POST['tname'];

    if (empty($tname)) {
        redirect_failure('请填写完整表格。');
        exit;
    }

    $score_dir = dirname(__FILE__) ."/score/";

    $return_dict = array();
    $error_message = array(0 => "success",
        1 => "upload error",
        2 => "size limit exceeded",
        4 => "already exists");

    if (!isset($_FILES["file"]["error"]) || is_array($_FILES["file"]["error"])) {
        $return_dict["code"] = 1;
        goto complete;
    }

    if ($_FILES["file"]["error"] == UPLOAD_ERR_INI_SIZE || $_FILES["file"]["error"] == UPLOAD_ERR_FORM_SIZE) {
        $return_dict["code"] = 2;
        goto complete;
    }

    if ($_FILES["file"]["error"] == UPLOAD_ERR_OK) {
        $latest_score = 'ranking_' . $grade . '_' . $tname;
        $score_name = $latest_score . '.csv';
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $file_type = finfo_file($finfo, $_FILES["file"]['tmp_name']);
        finfo_close($finfo);
        if ($file_type != "text/plain") {
            $return_dict["code"] = $file_type;
            goto complete;
        }

        if (file_exists($score_dir . $score_name)) {
            $return_dict["code"] = 4;
            goto complete;
        }

        move_uploaded_file($_FILES["file"]["tmp_name"], $score_dir.$score_name);
        $return_dict["code"] = 0;
        goto complete;
    }

    $return_dict["code"] = 1;

    complete:
    $return_dict["message"] = $error_message[$return_dict["code"]];

    if ($return_dict["message"] == "success")
    {
        $ranking_list = get_option("ranking_".$grade);
        array_push($ranking_list, $latest_score);
        set_option('ranking_' . $grade, $ranking_list);
        set_option('display_'.$latest_score, false);
        redirect_success('添加成功！');
    }
    else
        redirect_failure($return_dict["message"]);
    exit;
}

$ranking_grade = array();
$ranking_list = get_option("ranking_".$grade);
foreach ($ranking_list as $item)
{
    $is_display = get_option('display_'.$item);
    array_push($ranking_grade, array(
        'name' => substr($item, 13),
        'display' => $is_display == true
    ));
}

?>
<h2><?=get_option("grade")?>级成绩管理</h2>
<h3>展示信息管理</h3>
<form method="POST" id="modify-info">
    <div class="form-group">
        <div class="prompt">
            <label for="content">展示信息</label>
            <p class="note">某张表的某类值表示如下：[1:savg]，表示id为1的表的算术平均分。可供选择的type有，<br><code>savg-算术平均分<br>sno-算术平均分大班排名<br>ssno-算术平均分小班排名<br>gavg-学分绩点和<br>gno-学分绩点和大班排名<br>gsno-学分绩点和小班排名<br>ctot-小班人数<br>gtot-大班人数</code><br>无需显示学生姓名。</p>
        </div>
        <div class="control">
            <textarea class="form-control" name="content" rows="5" id="content" required><?=get_option("ranking_".$grade.'_content')?></textarea>
        </div>
    </div>
    <button type="submit" class="button submit-button green-button button-with-icon" name="modify-info"><i class="fa fa-plus"></i>修改</button>
</form>
<hr>
<h3>成绩表管理</h3>
<table id="scoretable" class="table table-striped table-bordered table-hover">
    <thead>
    <tr>
        <th width="100px">序号</th><th width="400px">表名</th><th width="200px">操作</th>
    </tr>
    </thead>
    <tbody>
    <?php
    $i = 0;
    foreach ($ranking_grade as $item) {
        $i++;
        if ($item['display']) {
            $button = '<form method="POST"><button type="submit" class="button xs-button gray-button" name="hide" value="'.$item['name'].'">隐藏该表</button>';
        } else {
            $button = '<form method="POST"><button type="submit" class="button xs-button gray-button" name="display" value="'.$item['name'].'">显示该表</button>';
        }
        $button .= ' <button type="submit" class="button xs-button red-button" name="delete" value="'.$item['name'].'">删除</button>';
        $button .= ' <a class="button xs-button green-button" href = "'.ROOT_URL.'modules/Ranking'.$grade.'/'.'download.php?filename='.$item['name'].'&grade='.$grade.'" target="_blank">下载</a></form>';
        echo "<tr><td>".$i."</td><td>".$item['name']."</td><td>".$button."</td></tr>";
    }
    ?>
    </tbody>
</table>
<hr>
<h3>添加成绩表</h3>
<form method="POST" id="add-score" enctype="multipart/form-data">
    <div class="form-group">
        <div class="prompt">
            <label for="scoreType">表名</label>
        </div>
        <div class="control">
            <input type="text" name="tname" class="form-control" placeholder="例：大一上、前三学期、前两学年等" required="">
        </div>
    </div>
    <div class="form-group">
        <div class="prompt">
            <label for="uploadscore">上传成绩</label>
        </div>
        <div class="control">
            <input type="file" name="file" id="file" accept="text/csv" required/>
            <p class="note">上传类型必须为csv，列向需严格按照如下顺序排列：学号，姓名，算术平均分，算术平均分大班排名，算数平均分小班排名，学分绩点和，学分绩点和大班排名，学分绩点和小班排名，小班人数，大班人数。若没有数据则留占位列。文本编码为UTF-8。</p>
        </div>
    </div>
    <button type="submit" class="button submit-button green-button button-with-icon" name="add-score"><i class="fa fa-plus"></i>添加成绩</button>
</form>

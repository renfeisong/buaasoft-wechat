<?php
/**
 * More ranking information page.
 *
 * @author TimmyXu
 * @since 2.0.0
 */

require_once dirname(dirname(dirname(__FILE__))) . '/config.php';

global $wxdb;
$openid = $_GET['openid'];
$row = $wxdb->get_row("SELECT * FROM `user` WHERE openid = '".$openid."'", ARRAY_A);
$userName = $row['userName'];
$userId = $row['userId'];
$grade = $row['startYear'];

$ranking_list = _get_value("Ranking".$grade, "ranking_".$grade);
$display_list = array();
$flag = false;
foreach ($ranking_list as $item) {
    if (_get_value("Ranking".$grade, "display_".$item)) {
        array_push($display_list, $item);
        $flag = true;
    }

}

?>
<!DOCTYPE html>
<html lang="zh-CN" >
<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="../../includes/css/components.css" media="all">
    <link rel="stylesheet" href="../../includes/css/table.css" media="all">
    <script src="../../includes/plugins/jquery/jquery-2.1.1.min.js"></script>
    <script src="../../includes/plugins/bootstrap/bootstrap.min.js"></script>
</head>
<body>
<h2><?=$userName?>的成绩详情</h2>

<?php
if (!$flag) {
    echo "暂无更多成绩信息~";
}
foreach ($display_list as $item) {
    $scoretype = substr($item, 13);
    $file = fopen(dirname(__FILE__).'/score/'.$item . '.csv','r');
    while ($data = fgetcsv($file)) {
        if ($data[0] == $userId)
        {
?>
    <h4><?=$scoretype?></h4>
<?php
            if ($data[2] != "")
                echo "<p>算术平均分：".$data[2]."</p>";
            if ($data[3] != "")
                echo "<p>算术平均分大班排名：".$data[3]."</p>";
            if ($data[4] != "")
                echo "<p>算术平均分小班排名：".$data[4]."</p>";
            if ($data[5] != "")
                echo "<p>学分绩点和：".$data[5]."</p>";
            if ($data[6] != "")
                echo "<p>学分绩点和大班排名：".$data[6]."</p>";
            if ($data[7] != "")
                echo "<p>学分绩点和小班排名：".$data[7]."</p>";
            if ($data[8] != "")
                echo "<p>小班人数：".$data[8]."</p>";
            if ($data[9] != "")
                echo "<p>大班人数：".$data[9]."</p>";
            break;
        }
    }
}
?>
</body>
</html>
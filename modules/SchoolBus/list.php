<?php
/**
 * School Bus List
 *
 * @author Zhan Yu
 */
require_once dirname(dirname(dirname(__FILE__))) . '/config.php';
global $wxdb;

function get_school_bus($route) {
    global $wxdb;
    $table = _get_value('SchoolBus', 'table_bus');
    $sql = <<<SQL
SELECT * FROM `{$table}`
  WHERE `departure` = %s
  AND `destination` = %s
  ORDER BY `departureTime` ASC
SQL;
    $sql = $wxdb->prepare($sql, $route['from'], $route['to']);
    return $wxdb->get_results($sql, ARRAY_A);
}

function get_route_all() {
    global $wxdb;
    $table = _get_value('SchoolBus', 'table_bus');
    return $wxdb->get_results("SELECT `departure` AS `from`, `destination` AS `to` FROM `{$table}` WHERE 1 GROUP BY `departure`, `destination`", ARRAY_A);
}
?><!DOCTYPE html>
<html lang="zh-cmn-Hans">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=EDGE">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../../includes/css/table.css" rel="stylesheet">
    <script src="../../includes/plugins/jquery/jquery-2.1.1.min.js"></script>
    <script src="../../includes/plugins/datatables/js/jquery.dataTables.min.js"></script>
    <title>班车时刻表</title>
</head>
<body>
<div>
<?php foreach(get_route_all() as $route): ?>
    <h3><?=$route['from']?>开往<?=$route['to']?></h3>
    <table class="table table-striped table-bordered table-hover dataTable">
        <thead><tr>
            <th>星期</th>
            <th>发车时间</th>
        </tr></thead>
        <tbody>
        <?php foreach(get_school_bus($route) as $bus): ?>
            <tr>
                <td><?= $bus['day'] == 1 ? '周一至周五' : '周六、周日' ?></td>
                <td><?= substr($bus['departureTime'], 0, -3) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endforeach; ?>
</div>
<script>
    $(document).ready(function() {
        $("table").dataTable({
            "paging": false,
            "searching": false,
            "info": false
        });
    });
</script>
</body>
</html>

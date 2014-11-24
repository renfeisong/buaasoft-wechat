<?php
/**
 * Setting page for Analytics Module
 *
 * @author Renfei Song
 */

global $wxdb; /* @var $wxdb wxdb */

?>
<h2>统计和分析</h2>

<h3>模块统计</h3>

<table id="module-stat" class="table table-striped table-bordered table-hover">
    <thead>
    <tr>
        <th>模块名称</th>
        <th>内部名称</th>
        <th>总触发次数</th>
        <th>百分比</th>
    </tr>
    </thead>
    <tbody>
    <?php
    $rows = $wxdb->get_results("select hitBy, count(*) as count from frontend_log group by hitBy having hitBy != ''", ARRAY_A);
    $count = 0;
    foreach ($rows as $row) {
        $count += $row['count'];
    }
    foreach ($rows as $row) {
        $ratio = $count == 0 ? 'NaN' : sprintf("%.2f%%", ($row['count'] / $count) * 100);
        $displayName = _get_value('global', 'display_name_'.$row['hitBy']);
        echo "<tr><td>$displayName</td><td>{$row['hitBy']}</td><td>{$row['count']}</td><td>$ratio</td></tr>";
    }
    ?>
    </tbody>
</table>

<h3>消息统计</h3>
<table id="msg-stat" class="table table-striped table-bordered table-hover">
    <tbody>
    <tr><td>总接收消息数</td><td><?php echo $wxdb->get_var('select count(*) from frontend_log') ?></td></tr>
    <tr><td>已绑定用户消息数</td><td><?php echo $bound_count = $wxdb->get_var("select count(*) from frontend_log where hitBy != 'Bound'") ?></td></tr>
    <tr><td>未绑定用户消息数</td><td><?php echo $wxdb->get_var("select count(*) from frontend_log where hitBy = 'Bound'") ?></td></tr>
    <tr><td>有效消息数（已绑定且被识别）</td><td><?php echo $valid_count = $wxdb->get_var("select count(*) from frontend_log where hitBy != 'Bound' and hitBy != ''") ?></td></tr>
    <tr><td>消息有效率（有效消息数/已绑定用户消息数）</td><td><?php echo $valid_count / $bound_count ?></td></tr>
    </tbody>
</table>
<h4>近100条消息记录</h4>
<table id="recent-msg" class="table table-striped table-bordered table-hover">
    <thead>
    <tr>
        <th>消息ID</th>
        <th>时间</th>
        <th>姓名</th>
        <th>消息类型</th>
        <th>消息内容</th>
        <th>回复类型</th>
        <th>回复内容</th>
    </tr>
    </thead>
    <tbody>
    <?php
    $rows = $wxdb->get_results('select frontend_log.id as msgid, time, userName, rawXml, responseXml from frontend_log left join user on frontend_log.openid = user.openid order by frontend_log.id desc limit 0, 100', ARRAY_A);
    foreach ($rows as $row) {
        $rawXml = simplexml_load_string($row['rawXml'], 'SimpleXMLElement', LIBXML_NOCDATA);
        $responseXml = simplexml_load_string($row['responseXml'], 'SimpleXMLElement', LIBXML_NOCDATA);
        $msgType = $rawXml->MsgType;
        $msgContent = @$rawXml->Content;
        $responseType = @$responseXml->MsgType;
        $responseContent = @$responseXml->Content;
        $time = $row['time'];
        echo "<tr><td>{$row['msgid']}</td><td>$time</td><td>{$row['userName']}</td><td>$msgType</td><td>$msgContent</td><td>$responseType</td><td>$responseContent</td></tr>";
    }
    ?>
    </tbody>
</table>

<h3>用户统计</h3>

<table id="user-stat" class="table table-striped table-bordered table-hover">
    <thead>
    <tr>
        <th>用户ID</th>
        <th>姓名</th>
        <th>总发送消息数</th>
        <th>有效消息数</th>
        <th>有效百分比</th>
    </tr>
    </thead>
    <tbody>
    <?php
    $rowsAll = $wxdb->get_results('select user.id as uid, user.openid as openid, userName, userId, count(*) as msgc from frontend_log left join user on frontend_log.openid = user.openid where userId != \'\' group by user.openid', ARRAY_A);
    $rowsValid = $wxdb->get_results('select openid, count(*) as msgc from frontend_log where isHit = 1 group by openid', OBJECT_K);
    foreach ($rowsAll as $row) {
        $validCount = $rowsValid[$row['openid']]->msgc;
        $allCount = $row['msgc'];
        $ratio = $allCount == 0 ? 'NaN' : sprintf("%.2f%%", ($validCount / $allCount) * 100);
        echo "<tr><td>{$row['uid']}</td><td>{$row['userName']}</td><td>$allCount</td><td>$validCount</td><td>$ratio</td></tr>";
    }
    ?>
    </tbody>
</table>

<script>
    $('#module-stat').DataTable({
        paging: false
    });
    $('#recent-msg').DataTable({
        "order": [[ 1, "desc" ]]
    });
    $('#user-stat').DataTable({
        "order": [[ 2, "desc" ]],
        "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]]
    });
</script>
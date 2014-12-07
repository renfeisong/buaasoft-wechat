<?php
/**
 * Setting page for Feedback Module
 *
 * @author Renfei Song
 */

global $wxdb; /* @var $wxdb wxdb */

?>
<h2>用户反馈</h2>

<table id="module-stat" class="table table-striped table-bordered table-hover">
    <thead>
    <tr>
        <th>编号</th>
        <th>作者</th>
        <th>内容</th>
        <th>日期</th>
    </tr>
    </thead>
    <tbody>
    <?php
    $rows = $wxdb->get_results("select `user`.userName as `name`, feedback.id as id, feedback.content as content, feedback.timestamp as `timestamp` from feedback inner join `user` on feedback.openid = `user`.openid order by feedback.id desc", ARRAY_A);

    foreach ($rows as $row) {
        echo "<tr><td>{$row['id']}</td><td>{$row['name']}</td><td>{$row['content']}</td><td>{$row['timestamp']}</td></tr>";
    }
    ?>
    </tbody>
</table>
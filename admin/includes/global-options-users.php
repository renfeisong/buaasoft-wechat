<?php
global $wxdb; /* @var $wxdb wxdb */
$result = $wxdb->get_results('select * from admin_user');
?>

<h2>用户管理</h2>

<!--<h3>待审核用户</h3>-->
<!---->
<!--<h3>所有用户</h3>-->

<table id="user-table">
    <thead>
    <tr>
        <th>用户名</th>
        <th>用户类型</th>
        <th>后台管理</th>
        <th>模块管理</th>
        <th>加入时间</th>
        <th>上次活动时间</th>
        <th>操作</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach($result as $row):?>
    <tr>
        <td><?=$row->userName?></td>
        <td><?=$row->isSuperAdmin == 1 ? "超级管理员" : "管理员"?></td>
        <td style="padding: 0">
            <a href="#" id="tags" data-type="select2" data-pk="1" data-title="Enter tags" class="editable editable-click"">css, javascript, ajax</a>
        </td>
        <td></td>
        <td><?=$row->joinDate?></td>
        <td><?=$row->lastActivity?></td>
        <td>1</td>
    </tr>
    <?php endforeach;?>
    </tbody>
</table>
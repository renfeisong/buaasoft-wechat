

<h2>用户管理</h2>

<h3>待审核用户</h3>

<h3>所有用户</h3>

<table>
    <thead>
    <tr>
        <th>用户名</th>
        <th>用户类型</th>
        <th>加入时间</th>
        <th>上次活动时间</th>
        <th>操作</th>
    </tr>
    </thead>
    <tbody>
    <?php
        global $wxdb; /* @var $wxdb wxdb */
        $wxdb->get_results('select * from admin_user');
    ?>
    </tbody>
</table>
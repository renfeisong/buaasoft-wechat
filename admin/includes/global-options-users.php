<?php
global $wxdb; /* @var $wxdb wxdb */
$result = $wxdb->get_results('select * from admin_user', ARRAY_A);

global $global_options;
global $modules;

$tags = $global_options;
foreach ($modules as $module) {
    if (has_settings_page($module)) {
        $tags[get_class($module)] = $module->display_name();
    }
}
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
        <th>可管理模块</th>
        <th>加入时间</th>
        <th>上次活动时间</th>
        <th>操作</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach($result as $row):?>
    <tr>
        <td><?=$row["userName"]?></td>
        <td><?=$row["isSuperAdmin"] == 1 ? "超级管理员" : "管理员"?></td>
        <td>
            <a href="#" class="x-editable">
                <?php
                $authorized_pages = json_decode($row["authorizedPages"]);
                foreach ($authorized_pages as $authorized_page) {
                    echo $tags[$authorized_page];
                    echo ", ";
                }

                ?>
            </a>
        </td>
        <td><?=$row["joinDate"]?></td>
        <td><?=$row["lastActivity"]?></td>
        <td>1</td>
    </tr>
    <?php endforeach;?>
    </tbody>
</table>

<script>
    $(document).ready(function() {
        $("#user-table").DataTable();
        $(".x-editable").editable({
            type: "select2",
            select2: {
                tags: <?=json_encode(array_values($tags))?>,
                placeholder: "点击添加...",
                createSearchChoice: null
            },
            placeholder: "点击添加...",
            title: "点击添加..."
        });

    });
</script>
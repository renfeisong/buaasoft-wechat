<?php

if (isset($_POST['enable'])) {
    set_global_value('enabled_' . $_POST['enable'], true);
    redirect_success('该模块已启用。');
    exit;
}

if (isset($_POST['disable'])) {
    set_global_value('enabled_' . $_POST['disable'], false);
    redirect_success('该模块已停用。');
    exit;
}

// Iterate over all installed modules
$all_modules = array();
$path = ABSPATH . "modules";
foreach (new DirectoryIterator($path) as $fileInfo) {
    if ($fileInfo->isDot() == false && $fileInfo->isDir()) {
        $module_name = $fileInfo->getFilename();
        $module_path = ABSPATH . "modules/" . $module_name . "/index.php";

        // Check if module file exists
        if (file_exists($module_path)) {
            // Get the display name at runtime or via cached configuration
            if (get_global_value('enabled_' . $module_name) == true) {
                $m = new $module_name; /* @var $m BaseModule */
                $display_name = $m->display_name();
            } else {
                $display_name = get_global_value('display_name_' . $module_name);
            }
            // Get the priority
            $priority = get_global_value('priority_' . $module_name);
            if (is_null($priority))
                $priority = 10;
            // Save module information
            array_push($all_modules, array(
                'name' => $module_name,
                'display_name' => $display_name,
                'enabled' => get_global_value('enabled_' . $module_name) == true,
                'priority' => $priority
            ));
        }
    }
}

?>

<h2>模块管理</h2>
<table id="modules-table" class="table table-striped table-bordered table-hover">
    <thead>
    <tr>
        <th>名称</th>
        <th>内部名称</th>
        <th>总触发次数</th>
        <th>近24小时触发次数</th>
        <th>权重</th>
        <th>状态</th>
        <th>控制</th>
    </tr>
    </thead>
    <tbody>
    <?php
        foreach ($all_modules as $module) {
            $template = '<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td><form method="POST">%s</form></td></tr>';
            if ($module['enabled']) {
                $status = '已启用';
                $button = '<button type="submit" name="disable" value="'.$module['name'].'">停用</button>';
            } else {
                $status = '未启用';
                $button = '<button type="submit" name="enable" value="'.$module['name'].'">启用</button>';
            }

            echo sprintf($template, $module['display_name'], $module['name'], 0, 0, $module['priority'], $status, $button);
        }
    ?>
    </tbody>
</table>

<script>
    $('#modules-table').DataTable();
</script>
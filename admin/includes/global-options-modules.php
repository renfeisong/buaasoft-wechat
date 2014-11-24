<?php

if (isset($_POST['enable'])) {
    set_global_value('enabled_' . $_POST['enable'], true);
    redirect_success('已成功启用模块。');
    exit;
}

if (isset($_POST['disable'])) {
    set_global_value('enabled_' . $_POST['disable'], false);
    redirect_success('已经停用该模块。');
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

// Get AJAX Key
$ajax_key = sha1(rand(111111, 999999));
set_global_value('options_module_ajax', $ajax_key);

?>

<h2>模块管理</h2>
<table id="modules-table" class="table table-striped table-bordered table-hover">
    <thead>
    <tr>
        <th>名称</th>
        <th>内部名称</th>
        <th>权重</th>
        <th class="nosort">控制</th>
    </tr>
    </thead>
    <tbody>
    <?php
        foreach ($all_modules as $module) {
            $template = '<tr><td>%s%s</td><td><code>%s</code></td><td><a href="#" data-type="text" data-pk="%s" data-url="includes/global-options-modules-ajax.php?auth=%s" data-name="priority" class="x-editable-field">%s</a></td><td><form method="POST">%s</form></td></tr>';
            if ($module['enabled']) {
                $status = '<span class="label green-label">已启用</span>';
                $button = '<button type="submit" class="button xs-button gray-button" name="disable" value="'.$module['name'].'">停用</button>';
            } else {
                $status = '<span class="label gray-label">未启用</span>';
                $button = '<button type="submit" class="button xs-button gray-button" name="enable" value="'.$module['name'].'">启用</button>';
            }

            echo sprintf($template, $module['display_name'], $status, $module['name'], $module['name'], sha1(AJAX_SALT . $ajax_key), $module['priority'], $button);
        }
    ?>
    </tbody>
</table>

<script>
    $('#modules-table').DataTable({
        paging: false,
        aoColumnDefs: [{
            bSortable: false,
            aTargets: ['nosort']
        }]
    });
    $('.x-editable-field').editable();
</script>
<style>
    #modules-table .label {
        float: right;
    }
</style>
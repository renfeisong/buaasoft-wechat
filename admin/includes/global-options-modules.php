<?php

if (isset($_POST['enable'])) {
    set_global_value('enabled_' . $_POST['enable'], true);
    global $wxdb; /* @var $wxdb wxdb */
    $wxdb->insert('security_log', array(
        'userName' => current_user_name(),
        'opName' => 'Module.enable',
        'opDetail' => 'Success: Module [' . $_POST['enable'] . '] enabled',
        'ip' => $_SERVER['REMOTE_ADDR'],
        'agent' => $_SERVER['HTTP_USER_AGENT']
    ));
    redirect_success('已成功启用模块。');
    exit;
}

if (isset($_POST['disable'])) {
    set_global_value('enabled_' . $_POST['disable'], false);
    global $wxdb; /* @var $wxdb wxdb */
    $wxdb->insert('security_log', array(
        'userName' => current_user_name(),
        'opName' => 'Module.disable',
        'opDetail' => 'Success: Module [' . $_POST['disable'] . '] disabled',
        'ip' => $_SERVER['REMOTE_ADDR'],
        'agent' => $_SERVER['HTTP_USER_AGENT']
    ));
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

<style>

    #file-name {
        margin-bottom: 10px;
    }

    progress,
    progress::-webkit-progress-bar {
        display: block;
        background-color: #f3f3f3;
        border: 0;
        border-radius: 0;
        height: 20px;
        width: 250px;
        margin-bottom: 20px;
    }

    progress::-webkit-progress-value {
        display: block;
        background-color: #4b8df8;
        border: 0;
        border-radius: 0;
        height: 20px;
        width: 250px;
        margin-bottom: 10px;
    }

    progress::-moz-progress-bar {
        display: block;
        background-color: #4b8df8;
        border: 0;
        border-radius: 0;
        height: 20px;
        width: 250px;
        margin-bottom: 10px;
    }

</style>

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

<!--<h2>安装模块</h2>-->

<h4 id="file-name"></h4>
<progress class="progress-bar hidden" value="0" max="100"></progress>

<button id="add-file" class="button blue-button button-with-icon"><i class="fa fa-plus fa-fw"></i> 安装模块...</button>
<button id="upload-file" class="button blue-button button-with-icon hidden"><i class="fa fa-upload fa-fw"></i> 开始上传</button>
<button id="uploading-file" class="button blue-button button-with-icon disabled-button hidden"><i class="fa fa-spinner fa-spin fa-fw"></i> 正在上传...</button>
<button id="upload-success" class="button green-button button-with-icon disabled-button hidden"><i class="fa fa-check fa-fw"></i> 安装成功</button>
<button id="upload-fail" class="button red-button button-with-icon disabled-button hidden"><i class="fa fa-close fa-fw"></i> 安装失败</button>

<form id="file-form" class="hidden" enctype="multipart/form-data">
    <input id="add-file-hidden" class="button blue-button" type="file" name="file" accept="application/zip"/>
</form>


<script>
    var error_message = {
        0: "模块安装成功",
        1: "上传过程中出现错误",
        2: "文件大小超过限制",
        3: "文件类型不是zip",
        4: "同名模块已存在",
        5: "文件解压时出现错误",
        6: "压缩包名、文件夹名和类名不相同",
        7: "未找到index.php文件",
        8: "模块类不是BaseClass的子类",
        100: "出现了未知错误"
    };

    $('#modules-table').DataTable({
        paging: false,
        aoColumnDefs: [{
            bSortable: false,
            aTargets: ['nosort']
        }]
    });
    $('.x-editable-field').editable();

    $(document).ready(function() {

        $("#add-file").click(function () {
            $("#add-file-hidden").trigger('click');
        });

        $("#add-file-hidden").change(function() {
            $("#file-name").html(this.files[0].name).removeClass("hidden");
            $("#add-file").addClass("hidden");
            $("#upload-file").removeClass("hidden");
        });

        $("#upload-file").click(function() {
            $("#upload-file").addClass("hidden");
            $("#uploading-file").removeClass("hidden");
            $(".progress-bar").removeClass("hidden");
            var form_data = new FormData($("#file-form")[0]);
            $.ajax({
                url: "./includes/global-options-install_module-ajax.php",
                type: "POST",
                xhr: function() {
                    var file_xhr = $.ajaxSettings.xhr();
                    if (file_xhr.upload) { // Check if upload property exists
                        file_xhr.upload.addEventListener("progress", handle_progress, false);
                    }
                    return file_xhr;
                },
                data: form_data,
                cache: false,
                contentType: false,
                processData: false,
                dataType: "json"
            }).done(function(data) {
                if (data["code"] == 0) {
                    $("#uploading-file").addClass("hidden");
                    $(".progress-bar").addClass("hidden");
                    $("#upload-success").removeClass("hidden");
                } else {
                    toastr.error(error_message[data["code"]], "Error");
                    $("#uploading-file").addClass("hidden");
                    $(".progress-bar").addClass("hidden");
                    $("#upload-fail").removeClass("hidden");
                    setTimeout(function() {
                        $("#file-name").html("");
                        $("#upload-fail").addClass("hidden");
                        $("#add-file").removeClass("hidden");
                    }, 2000);
                }
            });
        });

        function handle_progress(e) {
            if (e.lengthComputable) {
                $(".progress-bar").attr({
                    value:e.loaded,
                    max:e.total
                });
            }
        }
    });

</script>
<style>
    #modules-table .label {
        float: right;
    }
</style>
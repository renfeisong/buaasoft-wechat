<?php
/**
 * This file is the user management page for the admin center.
 *
 * @author Bingchen Qin
 * @author Renfei Song
 * @since 2.0.0
 */

global $wxdb; /* @var $wxdb wxdb */
$results = $wxdb->get_results("SELECT * FROM admin_user", ARRAY_A);

global $global_options;
echo json_encode($global_options);
$all_modules = get_modules();

$all_tags = $global_options;
foreach ($all_modules as $module) {
    if (has_settings_page($module["name"])) {
        $display_name= _get_value("global", "display_name_" . $module["name"]);
        if ($display_name == null) {
            $display_name = $module["name"];
        }
        $all_tags[$module["name"]] = $display_name;
    }
}

$authorized_module_results = $wxdb->get_var("SELECT authorizedPages FROM admin_user WHERE userName = '" . current_user_name() . "'");
$authorized_modules = json_decode($authorized_module_results);
$authorized_tags = array();
foreach ($authorized_modules as $module) {
    if (has_settings_page($module)) {
        $display_name= _get_value("global", "display_name_" . $module);
        if ($display_name == null) {
            $display_name = $module;
        }
        $authorized_tags[$module] = $display_name;
    }
}
echo json_encode($authorized_tags);

?>

<style>
    .admin {
        color: #4b8df8;
    }

    .super-admin {
        color: #d84a38;
    }

    td .button {
        min-width: 60px;
        margin-right: 8px;
    }

    tr.disabled,
    tr.disabled a {
        color: #aaaaaa !important;
        border-bottom-color: #aaaaaa !important;
    }

    .editable-container.popover,
    .editable-container.editable-popup {
        max-width: 750px !important;
    }

    .editable-buttons {
        display: block !important;
        margin: 15px 0 0;
    }

    button.btn.blue.editable-submit {
        margin-left: 0 !important;
    }

</style>

<h2>用户管理</h2>

<table id="user-table" class="table table-striped table-bordered table-hover">
    <thead>
    <tr>
        <th style="min-width: 100px;">用户名</th>
        <th class="nosort" style="min-width: 150px;">备注</th>
        <th class="nosort">可管理模块</th>
        <th style="width: 130px; min-width: 130px; max-width: 130px;">加入时间</th>
        <th style="width: 130px; min-width: 130px; max-width: 130px;">上次活动时间</th>
        <th class="nosort" style="width: 180px; max-width: 180px; min-width: 180px;">操作</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach($results as $row):?>
    <tr class="<?php echo $row["isEnabled"] == 1 ? "" : "disabled" ?>" data-username="<?php echo $row["userName"] ?>">
        <td>
            <i class="fa fa-user fa-fw <?php echo $row["isSuperAdmin"] == 1 ? "super-admin" : "admin" ?>" title="<?=$row["isSuperAdmin"] == 1 ? "超级管理员" : "管理员"?>"></i>
            <?php echo $row["userName"] ?>
        </td>
        <td><a href="#" class="x-editable-note"><?php echo $row['note'] ?></a></td>
        <?php if ($row["isSuperAdmin"] == 0): ?>
            <td>
                <?php if (current_user_name() != $row["userName"]): ?><a href="#" class="x-editable"><?php endif; ?>
                    <?php
                    $authorized_pages = json_decode($row["authorizedPages"]);
                    $i = 0;
                    foreach ($authorized_pages as $authorized_page) {
                        echo $all_tags[$authorized_page];
                        if ($i < count($authorized_pages) - 1) {
                            echo ", ";
                        }
                        $i++;
                    }
                    ?>
                <?php if (current_user_name() != $row["userName"]): ?></a><?php endif; ?>
            </td>
        <?php else:?>
            <td>所有模块</td>
        <?php endif;?>
        <td><?=$row["joinDate"]?></td>
        <td><?=$row["lastActivity"]?></td>
        <?php if (current_user_name() == $row["userName"]):?>
            <td>无法操作当前用户</td>
        <?php elseif ($row["isSuperAdmin"] == 1):?>
            <td>无法操作超级管理员</td>
        <?php else:?>
            <td>
                <button class="button blue-button xs-button enable-account <?=$row["isEnabled"] == 1 ? "hidden" : ""?>"><i class="fa fa-toggle-off fa-fw"></i>  启用</button>
                <button class="button blue-button xs-button disable-account <?=$row["isEnabled"] == 0 ? "hidden" : ""?>"><i class="fa fa-toggle-on fa-fw"></i>  禁用</button>
                <button class="button xs-button red-button delete-account"><i class="fa fa-trash fa-fw"></i>  删除</button>
                <button class="button xs-button red-button delete-account-confirm hidden">请确认</button>
            </td>
        <?php endif;?>
    </tr>
    <?php endforeach;?>
    </tbody>
</table>

<script>

    function switchButton($button) {
        $button.addClass("hidden");
        $button.siblings(".enable-account").removeClass("hidden");
        $button.siblings(".disable-account").removeClass("hidden");
        //reset buttons
        $(".enable-account").html("<i class=\"fa fa-toggle-off fa-fw\"></i>  启用");
        $(".disable-account").html("<i class=\"fa fa-toggle-on fa-fw\"></i>  禁用");
    }

    $(document).ready(function() {

        var is_deleting = false;

        $(document).click(function() {
            if (is_deleting == false) {
                $(".delete-account-confirm").addClass("hidden");
                $(".delete-account").removeClass("hidden");
            }
        });

        $("#user-table").DataTable({
            bPaginate: false,
            order: [[ 4, "desc" ]],
            aoColumnDefs: [{
                bSortable: false,
                aTargets: ['nosort']
            }]
        });

        $(".x-editable").editable({
            type: "select2",
            select2: {
                tags: <?=json_encode(array_values($all_tags))?>,
                createSearchChoice: null
            },
            emptytext: "点击添加..."
        });

        $(".x-editable").on("save", function(e, params) {
            $.ajax({
                url: "includes/global-options-users-ajax.php",
                type: "POST",
                dataType: "json",
                data: {
                    "action": "edit-permission",
                    "username": $(this).parents("tr").data("username"),
                    "permission": params.newValue
                }
            }).done(function(data){
                switch (data["code"]) {
                    case 0: {
                        toastr.success("修改权限成功", "Success");
                        break;
                    }
                    case 1: {
                        toastr.error("系统错误", "Error");
                        break;
                    }
                    default: {
                        break;
                    }
                }
            });
        });

        $(".x-editable-note").editable({
            type: "text",
            emptytext: "点击编辑..."
        });

        $(".x-editable-note").on("save", function(e, params) {
            $.ajax({
                url: "includes/global-options-users-ajax.php",
                type: "POST",
                dataType: "json",
                data: {
                    "action": "edit-note",
                    "username": $(this).parents("tr").data("username"),
                    "note": params.newValue
                }
            }).done(function(data){
                switch (data["code"]) {
                    case 0: {
                        toastr.success("修改成功", "Success");
                        break;
                    }
                    case 1: {
                        toastr.error("系统错误", "Error");
                        break;
                    }
                    default: {
                        break;
                    }
                }
            });
        });

        $(".enable-account").click(function() {
            var $button = $(this);
            $button.html("<i class=\"fa fa-spinner fa-spin fa-fw\"></i>  正在启用");
            $.ajax({
                url: "includes/global-options-users-ajax.php",
                type: "POST",
                dataType: "json",
                data: {
                    "action": "enable",
                    "username": $button.parents("tr").data("username")
                }
            }).done(function(data) {
                switch (data["code"]) {
                    case 0: {
                        $button.html("<i class=\"fa fa-check fa-fw\"></i>  已启用");
                        $button.parents("tr").removeClass("disabled");
                        window.setTimeout(function () {
                            $button.addClass("hidden");
                            $button.siblings(".disable-account").removeClass("hidden");
                            $button.html("<i class=\"fa fa-toggle-off fa-fw\"></i>  启用");
                        }, 1000);
                        break;
                    }
                    case 1: {
                        toastr.info("账户已启用，请勿重复提交", "Info");
                        break;
                    }
                    case 2: {
                        toastr.error("服务器出现未知错误", "Error");
                        break;
                    }
                    default: {
                        break;
                    }
                }
            });
        });

        $(".disable-account").click(function() {
            var $button = $(this);
            $button.html("<i class=\"fa fa-spinner fa-spin fa-fw\"></i>  正在禁用");
            $.ajax({
                url: "includes/global-options-users-ajax.php",
                type: "POST",
                dataType: "json",
                data: {
                    "action": "disable",
                    "username": $button.parents("tr").data("username")
                }
            }).done(function(data) {
                switch (data["code"]) {
                    case 0: {
                        $button.html("<i class=\"fa fa-check fa-fw\"></i>  已禁用");
                        $button.parents("tr").addClass("disabled");
                        window.setTimeout(function () {
                            $button.addClass("hidden");
                            $button.siblings(".enable-account").removeClass("hidden");
                            $button.html("<i class=\"fa fa-toggle-on fa-fw\"></i>  禁用");
                        }, 1000);
                        break;
                    }
                    case 1: {
                        toastr.info("未找到账户，或是账户已禁用，请勿重复提交", "Info");
                        break;
                    }
                    case 2: {
                        toastr.error("服务器出现未知错误", "Error");
                        break;
                    }
                    default: {
                        break;
                    }
                }
            });
        });

        $(".delete-account").click(function(e) {
            e.stopPropagation();
            $(this).addClass("hidden");
            $(this).siblings(".delete-account-confirm").removeClass("hidden");
        });

        $(".delete-account-confirm").click(function(e) {
            e.stopPropagation();
            is_deleting = true;
            var $button = $(this);
            $button.html("<i class=\"fa fa-spinner fa-spin fa-fw\"></i>  正在删除");
            $.ajax({
                url: "includes/global-options-users-ajax.php",
                type: "POST",
                dataType: "json",
                data: {
                    "action": "delete",
                    "username": $(this).parents("tr").data("username")
                }
            }).done(function(data){
                switch (data["code"]) {
                    case 0: {
                        $button.html("<i class=\"fa fa-check fa-fw\"></i>  已删除");
                        window.setTimeout(function() {
                            $button.parents("tr").fadeOut();
                        }, 1000);
                        break;
                    }
                    case 1: {
                        toastr.info("账户已删除，请勿重复提交", "Info");
                        break;
                    }
                    case 2: {
                        toastr.error("服务器出现未知错误", "Error");
                        break;
                    }
                    default: {
                        break;
                    }
                }
            }).always(function() {
                is_deleting = false;
            });
        });

    });
</script>
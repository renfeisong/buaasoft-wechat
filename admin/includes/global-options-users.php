<?php
/**
 * This file is the user management page for the admin center.
 *
 * @author Bingchen Qin
 * @since 2.0.0
 */

global $wxdb; /* @var $wxdb wxdb */
$result = $wxdb->get_results('SELECT * FROM admin_user', ARRAY_A);

global $global_options;
$modules = get_modules();

$tags = $global_options;
foreach ($modules as $module) {
    if (has_settings_page($module["name"])) {
        $display_name= _get_value("global", "display_name_" . $module["name"]);
        if ($display_name == null) {
            $display_name = $module["name"];
        }
        $tags[$module["name"]] = $display_name;
    }
}

?>

<style>
    .admin {
        color: #4b8df8;
    }

    .super-admin {
        color: #d84a38;
    }

    td .button {
        min-width: 83px;
        margin-right: 8px;
    }

    tr.disabled,
    tr.disabled a {
        color: #aaaaaa !important;
        border-bottom-color: #aaaaaa !important;
    }

</style>

<h2>用户管理</h2>

<table id="user-table" class="table table-striped table-bordered table-hover">
    <thead>
    <tr>
        <th>用户名</th>
        <th>可管理模块</th>
        <th>加入时间</th>
        <th>上次活动时间</th>
        <th>操作</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach($result as $row):?>
    <tr class="<?=$row["isEnabled"] == 1 ? "" : "disabled"?>" data-username="<?=$row["userName"]?>">
        <td>
            <i class="fa fa-user fa-fw <?=$row["isSuperAdmin"] == 1 ? "super-admin" : "admin"?>" title="<?=$row["isSuperAdmin"] == 1 ? "超级管理员" : "管理员"?>"></i>
            <?=$row["userName"]?>
        </td>
        <?php if ($row["isSuperAdmin"] == 0):?>
            <td>
                <?php if (current_user_name() != $row["userName"]):?><a href="#" class="x-editable"><?php endif;?>
                    <?php
                    $authorized_pages = json_decode($row["authorizedPages"]);
                    $i = 0;
                    foreach ($authorized_pages as $authorized_page) {
                        echo $tags[$authorized_page];
                        if ($i < count($authorized_pages) - 1) {
                            echo ", ";
                        }
                        $i++;
                    }
                    ?>
                <?php if (current_user_name() != $row["userName"]):?></a><?php endif;?>
            </td>
        <?php else:?>
            <td>所有模块</td>
        <?php endif;?>
        <td style="width: 132px; max-width: 132px; min-width: 132px;"><?=$row["joinDate"]?></td>
        <td style="width: 132px; max-width: 132px; min-width: 132px;"><?=$row["lastActivity"]?></td>
        <?php if (current_user_name() != $row["userName"]):?>
        <td style=" width: 186px; max-width: 186px; min-width: 186px;">
            <button class="button blue-button xs-button enable-account <?=$row["isEnabled"] == 1 ? "hidden" : ""?>"><i class="fa fa-toggle-off fa-fw"></i>  启用账户</button>
            <button class="button blue-button xs-button disable-account <?=$row["isEnabled"] == 0 ? "hidden" : ""?>"><i class="fa fa-toggle-on fa-fw"></i>  禁用账户</button>
            <button class="button xs-button red-button delete-account"><i class="fa fa-trash fa-fw"></i>  删除账户</button>
            <button class="button xs-button red-button delete-account-confirm hidden">请确认</button>
        </td>
        <?php else:?>
        <td>无法操作当前用户</td>
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
        $(".enable-account").html("<i class=\"fa fa-toggle-off fa-fw\"></i>  启用账户");
        $(".disable-account").html("<i class=\"fa fa-toggle-on fa-fw\"></i>  禁用账户");
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
            "bPaginate": false
        });

        $(".x-editable").editable({
            type: "select2",
            select2: {
                tags: <?=json_encode(array_values($tags))?>,
                createSearchChoice: null
            },
            emptytext: "点击添加..."
        });

        $(".x-editable").on("save", function(e, params) {
            console.log('Saved value: ' + params.newValue);
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
                console.log(data);
                switch (data["code"]) {
                    case 0: {
                        toastr.success("修改权限成功", "Success");
                        break;
                    }
                    case 1: {
                        toastr.error("服务器出现未知错误", "Error");
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
                        window.setTimeout(function () {
                            $button.addClass("hidden");
                            $button.siblings(".disable-account").removeClass("hidden");
                            $button.parents("tr").removeClass("disabled");
                            $button.html("<i class=\"fa fa-toggle-off fa-fw\"></i>  启用账户");
                        }, 2000);
                        break;
                    }
                    case 1: {
                        toastr.success("账户已启用，请勿重复提交", "Info");
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
                        window.setTimeout(function () {
                            $button.addClass("hidden");
                            $button.siblings(".enable-account").removeClass("hidden");
                            $button.parents("tr").addClass("disabled");
                            $button.html("<i class=\"fa fa-toggle-on fa-fw\"></i>  禁用账户");
                        }, 2000);
                        break;
                    }
                    case 1: {
                        toastr.success("未找到账户，或是账户已删除，请勿重复提交", "Info");
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
                        }, 2000);
                        break;
                    }
                    case 1: {
                        toastr.success("账户已删除，请勿重复提交", "Info");
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
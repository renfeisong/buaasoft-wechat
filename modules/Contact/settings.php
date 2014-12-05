<?php
/**
 * Settings page for contact query module.
 *
 * @author Bingchen Qin
 * @since 2.0.0
 */

require_once dirname(dirname(dirname(__FILE__))) . '/config.php';
$format = _get_value("Contact", "output_format");

global $wxdb; /* @var $wxdb wxdb */
$results = $wxdb->get_results("SELECT * FROM contact", ARRAY_A);

?>

<style>

    td .button {
        min-width: 60px;
    }

    textarea {
        font-family: Menlo, Courier, 'Liberation Mono', Consolas, Monaco, Lucida Console, monospace;
    }

    .error {
        border-color: #a94442;
    }

    .error-message {
        color: #a94442;
    }

    .list-1 {
        list-style: disc inside;
        padding-left: inherit;
    }

    .list-2 {
        list-style: circle inside;
        padding-left: inherit;
    }

    #submit,
    #submitting,
    #success {
        margin-top: 30px;
    }

</style>


<h2>通讯信息查询管理</h2>

<h3>Contact表管理</h3>
<table id="user-table" class="table table-striped table-bordered table-hover">
    <thead>
    <tr>
        <th>姓名</th>
        <th>身份</th>
        <th>电话号码</th>
        <th>邮箱</th>
        <th style="width: 100px; max-width: 100px; min-width: 100px;">操作</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach($results as $row):?>
        <tr data-id="<?php echo $row["id"] ?>">
            <td>
                <a href="#" class="x-editable x-editable-user-name">
                    <?php echo $row["userName"] ?>
                </a>
            </td>
            <td>
                <a href="#" class="x-editable x-editable-identity">
                    <?php echo $row['identity'] ?>
                </a>
            </td>
            <td>
                <a href="#" class="x-editable x-editable-phone-number">
                    <?php echo $row['phoneNumber'] ?>
                </a>
            </td>
            <td>
                <a href="#" class="x-editable x-editable-email">
                    <?php echo $row['email'] ?>
                </a>
            </td>
            <td>
                <button class="button xs-button red-button delete-record"><i class="fa fa-trash fa-fw"></i>  删除</button>
                <button class="button xs-button red-button delete-record-confirm hidden">请确认</button>
            </td>
        </tr>
    <?php endforeach;?>
    <tr id="add-record-row">
        <td>
            <input id="add-user-name" class="form-control" type="text" placeholder="例：吕云翔（必填）">
        </td>
        <td>
            <input id="add-identity" class="form-control" type="text" placeholder="例：教师（可选）">
        </td>
        <td>
            <input id="add-phone-number" class="form-control" type="text" placeholder="例：18688888888（可选）">
        </td>
        <td>
            <input id="add-email" class="form-control" type="text" placeholder="例：lyx@example.com（可选）">
        </td>
        <td>
            <button id="add-record" class="button blue-button xs-button"><i class="fa fa-plus fa-fw"></i>  添加</button>
        </td>
    </tr>
    </tbody>
</table>


<h3>展示信息管理</h3>

<textarea id="format" class="form-control" rows="5"><?php echo $format ?></textarea>
<label id="error-empty" class="error hidden">输入不能为空</label>
<h4>提示</h4>
<ul class="list-1">
    <li>输出格式中可带有占位符，目前可用的占位符有：</li>
    <ul class="list-2">
        <li><code>[identity]</code> --- 身份</li>
        <li><code>[phone_number]</code> --- 电话号码</li>
        <li><code>[email]</code> --- 邮箱</li>
    </ul>
    <li>
        示例：<br/>
        <pre>[identity]
电话号码：[phone_number]
邮箱：[email]</pre>
    </li>
</ul>
<button id="submit" class="button blue-button button-with-icon"><i class="fa fa-edit fa-fw"></i> 修改</button>
<button id="submitting" class="button blue-button button-with-icon hidden"><i class="fa fa-spinner fa-spin fa-fw"></i> 正在提交...</button>
<button id="success" class="button green-button button-with-icon hidden"><i class="fa fa-check fa-fw"></i> 修改成功</button>


<script>

function setup_x_editable() {

    $(".x-editable").editable({
        type: "text",
        emptytext: "点击添加..."
    });

    $(".x-editable-user-name").on("save", function(e, params) {
        $.ajax({
            url: "../modules/Contact/ajax.php",
            type: "POST",
            dataType: "json",
            data: {
                "action": "edit-user-name",
                "id": $(this).parents("tr").data("id"),
                "user_name": params.newValue
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

    $(".x-editable-identity").on("save", function(e, params) {
        $.ajax({
            url: "../modules/Contact/ajax.php",
            type: "POST",
            dataType: "json",
            data: {
                "action": "edit-identity",
                "id": $(this).parents("tr").data("id"),
                "identity": params.newValue
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

    $(".x-editable-phone-number").on("save", function(e, params) {
        $.ajax({
            url: "../modules/Contact/ajax.php",
            type: "POST",
            dataType: "json",
            data: {
                "action": "edit-phone-number",
                "id": $(this).parents("tr").data("id"),
                "phone_number": params.newValue
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

    $(".x-editable-email").on("save", function(e, params) {
        $.ajax({
            url: "../modules/Contact/ajax.php",
            type: "POST",
            dataType: "json",
            data: {
                "action": "edit-email",
                "id": $(this).parents("tr").data("id"),
                "email": params.newValue
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
}

    $(document).ready(function() {

        setup_x_editable();

        var is_deleting = false;

        $(document).click(function() {
            if (is_deleting == false) {
                $(".delete-account-confirm").addClass("hidden");
                $(".delete-account").removeClass("hidden");
            }
        });

        $("#add-record").click(function() {
            var $button = $(this);
            var user_name = $("#add-user-name").val();
            var identity = $("#add-identity").val();
            var phone_number = $("#add-phone-number").val();
            var email = $("#add-email").val();
            if (user_name == "") {
                toastr.error("必须填写姓名", "Error");
                $("#add-user-name").addClass("error");
                $("#add-user-name").focus();
                return;
            }
            $button.html("<i class=\"fa fa-spinner fa-spin fa-fw\"></i>  正在添加");
            $.ajax({
                url: "../modules/Contact/ajax.php",
                type: "POST",
                dataType: "json",
                data: {
                    "action": "add-record",
                    "user_name": user_name,
                    "identity": identity,
                    "phone_number": phone_number,
                    "email": email
                }
            }).done(function(data) {
                switch (data["code"]) {
                    case 0: {
                        $button.html("<i class=\"fa fa-check fa-fw\"></i>  已添加");
                        $("#add-record-row").before(
                            "<tr data-id=\"" + data["id"] + "\">" +
                            "<td><a href=\"#\" class=\"x-editable x-editable-user-name\">" + user_name + "</a></td>" +
                            "<td><a href=\"#\" class=\"x-editable x-editable-identity\">" + identity + "</a></td>" +
                            "<td><a href=\"#\" class=\"x-editable x-editable-phone-number\">" + phone_number + "</a></td>" +
                            "<td><a href=\"#\" class=\"x-editable x-editable-email\">" + email + "</a></td>" +
                            "<td>" +
                            "<button class=\"button xs-button red-button delete-record\"><i class=\"fa fa-trash fa-fw\"></i>  删除</button>" +
                            "<button class=\"button xs-button red-button delete-record-confirm hidden\">请确认</button>" +
                            "</td>" +
                            "</tr>"
                        );
                        setup_x_editable();
                        window.setTimeout(function () {
                            $button.html("<i class=\"fa fa-plusfa-fw\"></i>  添加");
                        }, 1000);
                        break;
                    }
                    case 1: {
                        toastr.info("记录已添加，请勿重复提交", "Info");
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

        $(".delete-record").click(function(e) {
            e.stopPropagation();
            $(this).addClass("hidden");
            $(this).siblings(".delete-record-confirm").removeClass("hidden");
        });

        $(".delete-record-confirm").click(function(e) {
            e.stopPropagation();
            is_deleting = true;
            var $button = $(this);
            $button.html("<i class=\"fa fa-spinner fa-spin fa-fw\"></i>  正在删除");
            $.ajax({
                url: "../modules/Contact/ajax.php",
                type: "POST",
                dataType: "json",
                data: {
                    "action": "delete-record",
                    "id": $(this).parents("tr").data("id")
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
                        toastr.info("记录已删除，请勿重复提交", "Info");
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

        $("#submit").click(function() {
            var format = $("#format").val();
            if (format == "") {
                $("#error-empty").removeClass("hidden");
                $("#format").addClass("error");
                $("#format").focus();
                return;
            } else {
                $("#error-empty").addClass("hidden");
                $("#format").removeClass("error");
            }
            $("#submit").addClass("hidden");
            $("#submitting").removeClass("hidden");
            $.ajax({
                url: "<?php echo ROOT_URL ?>modules/Contact/ajax.php",
                type: "POST",
                data: {
                    "action": "edit-format",
                    "format": format
                },
                dataType: "json"
            }).done(function(data) {
                $("#submitting").addClass("hidden");
                $("#success").removeClass("hidden");
                setTimeout(function() {
                    $("#submit").removeClass("hidden");
                    $("#success").addClass("hidden");
                }, 2000);
            });
        });

    });

</script>
<?php
/**
 * Settings page for contact query module.
 *
 * @author Bingchen Qin
 * @since 2.0.0
 */

require_once dirname(dirname(dirname(__FILE__))) . '/config.php';
$format = _get_value("Contact", "output_format");

?>

<style>
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

    $(document).ready(function() {

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
                    "action": "edit",
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
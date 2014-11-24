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

<textarea id="text" class="form-control" rows="5" placeholder="<?=$format?>"><?=$format?></textarea>
<h4>提示</h4>
<ul class="list-1">
    <li>输出格式中可带有占位符，目前可用的占位符有：</li>
    <ul class="list-2">
        <li><code>[name]</code> --- 姓名</li>
        <li><code>[id]</code> --- 学号或职工号</li>
        <li><code>[phone_number]</code> --- 电话号码</li>
        <li><code>[email]</code> --- 邮箱</li>
    </ul>
    <li>换行请用 <code>\n</code></li>
    <li>示例：<code>[id]</code> <code>[name]</code>的个人信息如下：<code>\n</code>电话号码：<code>[phone_number]</code> <code>\n</code>邮箱：<code>[email]</code></li>
</ul>
<button id="submit" class="button blue-button button-with-icon"><i class="fa fa-edit fa-fw"></i> 修改</button>
<button id="submitting" class="button blue-button button-with-icon hidden"><i class="fa fa-spinner fa-spin fa-fw"></i> 正在提交...</button>
<button id="success" class="button green-button button-with-icon hidden"><i class="fa fa-check fa-fw"></i> 修改成功</button>

<script>

    $(document).ready(function() {

        $("#submit").click(function() {
            $("#submit").addClass("hidden");
            $("#submitting").removeClass("hidden");
            $.ajax({
                url: "/modules/Contact/ajax.php",
                type: "POST",
                data: {
                    "action": "edit",
                    "format": $("#text").val()
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
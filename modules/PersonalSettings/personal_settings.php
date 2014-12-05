<?php
/**
 * Personal setting page.
 *
 * @author Bingchen Qin
 * @since 2.0.0
 */

require_once dirname(dirname(dirname(__FILE__))) . "/config.php";

if (isset($_GET["openid"])) {
    global $wxdb;
    $row = $wxdb->get_row("SELECT phoneNumber, email FROM user WHERE openid = '" . $_GET["openid"] . "'", ARRAY_A, 0);
} else {
    echo "未获取openid";
    exit;
}

?>

<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="utf-8">
    <meta name="robots" content="all">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no">
    <link rel="stylesheet" href="../../includes/css/reset.css" media="all">
    <link rel="stylesheet" href="../../includes/css/font-awesome.css" media="all">
    <link rel="stylesheet" href="personal_settings.css" media="all">
    <script src="../../includes/plugins/jquery/jquery-2.1.1.min.js"></script>
    <title>个人设置</title>
</head>

<body>

<div class="form">
    <div class="form-group">
        <label><strong>手机号码</strong></label>
        <input id="phone-number" type="text" class="form-control" value="<?=$row["phoneNumber"]?>">
        <div class="warning" id="phone-number-warning"></div>
    </div>

    <div class="form-group">
        <label><strong>邮箱</strong></label>
        <input id="email" type="email" class="form-control" value="<?=$row["email"]?>">
        <div class="warning" id="email-warning"></div>
    </div>
</div>

<button id="submit" class="button blue-button">提交</button>

</body>

<script>

    $(document).ready(function() {

        $("#submit").click(function () {
            var check = true;
            if(!(/^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/.test($("#email").val()))){
                $("#email-warning").html("<i class=\"fa fa-warning fa-fw\"></i>邮箱格式不准确");
                check = false;
            }
            if(!(/^1[3|4|5|7|8]\d{9}$/.test($("#phone-number").val()))){
                $("#phone-number-warning").html("<i class=\"fa fa-warning fa-fw\"></i>手机号格式不准确");
                check = false;
            }
            if (check == false) {
                return;
            }

            $.ajax({
                url: "./personal_settings-ajax.php",
                type: "POST",
                data: {
                    action: "edit",
                    openid: "<?=$_GET["openid"]?>",
                    phone_number: $("#phone-number").val(),
                    email: $("#email").val()
                },
                dataType: "json"
            }).done(function(data) {
                switch (data["code"]) {
                    case 0: {
                        alert("修改成功");
                        break;
                    }
                    default: break;
                }
            });
        });

    });

</script>
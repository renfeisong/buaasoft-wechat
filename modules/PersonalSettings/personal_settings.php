<?php
/**
 * Personal setting page.
 *
 * @author Bingchen Qin
 * @since 2.0.0
 */

require_once dirname(dirname(__FILE__)) . '/config.php';

if (isset($_GET["openid"])) {
    global $wxdb;
    $row = $wxdb->get_row("SELECT phone_number, email FROM user NATURAL JOIN contact WHERE openid = '" . $_GET["openid"] . "'", ARRAY_A, 0);
} else {
    exit;
}

?>

<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="utf-8">
    <meta name="robots" content="all">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no">
    <link rel="stylesheet" href="../includes/css/reset.css" media="all">
    <link rel="stylesheet" href="../includes/css/font-awesome.css" media="all">
    <script src="../includes/plugins/jquery/jquery-2.1.1.min.js"></script>
    <title>个人设置</title>

    <style>

    </style>
</head>

<body>

<div class="form-group">
    <label><strong>手机号码</strong></label>
    <input id="mobile" type="number" class="form-control" value="<?=$row["phone_number"]?>">
</div>

<div class="form-group">
    <label>邮箱</label>
    <input id="email" type="email" class="form-control" value="<?=$row["email"]?>">
</div>

<button id="submit">提交</button>

</body>

<script>

    $(document).ready(function() {

        $("#submit").click(function () {
            $.ajax({
                url: "./personal-settings-ajax.php",
                type: "POST",
                data: {
                    action: "edit",
                    openid: $_GET["openid"],
                    mobile: $("#mobile").val(),
                    email: $("#email").val()
                },
                dataType: "json"
            }).done(function() {

            });
        });

    });

</script>
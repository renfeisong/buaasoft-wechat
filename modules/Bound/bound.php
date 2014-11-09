<?php
/**
 * Created by PhpStorm.
 * User: timmyxu
 * Date: 14/11/8
 * Time: 下午11:56
 */
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="zh-CN" >
<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="../../includes/css/components.css" media="all">
    <script src="../../includes/plugins/jquery/jquery-2.1.1.min.js"></script>
    <script src="../../includes/plugins/bootstrap/bootstrap.min.js"></script>
    <style type="text/css">
        body {
            padding-top: 40px;
            padding-bottom: 40px;
            background-color: #f5f5f5;
        }

        .form-signin {
            max-width: 300px;
            padding: 19px 29px 29px;
            margin: 0 auto 20px;
            background-color: #fff;
            border: 1px solid #e5e5e5;
            -webkit-border-radius: 5px;
            -moz-border-radius: 5px;
            border-radius: 5px;
            -webkit-box-shadow: 0 1px 2px rgba(0,0,0,.05);
            -moz-box-shadow: 0 1px 2px rgba(0,0,0,.05);
            box-shadow: 0 1px 2px rgba(0,0,0,.05);
        }
        .form-signin .form-signin-heading,
        .form-signin .checkbox {
            margin-bottom: 10px;
        }
        .form-signin input[type="text"],
        .form-signin input[type="password"] {
            font-size: 16px;
            height: auto;
            margin-top: 8px;
            margin-bottom: 15px;
            padding: 7px 9px;
            display: block;
        }
        .control-group {
            text-align: center;
        }

    </style>

    <script type="text/javascript">

        $(document).ready(function(){

            function GetURLParameter(sParam)
            {
                var sPageURL = window.location.search.substring(1);
                var sURLVariables = sPageURL.split('&');
                for (var i = 0; i < sURLVariables.length; i++)
                {
                    var sParameterName = sURLVariables[i].split('=');
                    if (sParameterName[0] == sParam)
                    {
                        return sParameterName[1];
                    }
                }
            }
            var b = GetURLParameter("openid");
            $("#bound").click(function() {
                var stuid = $("#stuid").val();
                var identify = $("#identify").val();
                if (stuid == "") {
                    $("#stuidlabel").html("请输入学号~").css("color", "#ff6666");
                }
                if (identify == "") {
                    $("#identifylabel").html("请输入身份证号~").css("color", "#ff6666");
                }
                if(stuid != "" && identify != "") {
                    $.post("ajax.php", {
                        stuid: stuid,
                        identify: identify,
                        openid: b
                    }, function(data){
                        console.log(data);
                        if(data == "1"){
                            $("#main-form").hide();
                            $("#succeed").show();
                        }
                        if(data == "2"){
                            $("#isok").html("<p>身份验证失败</p>");
                        }
                        if(data == "0"){
                            $("#isok").html("<p>绑定失败</p>");
                        }
                    });
                }
                return false;
            });
        });
    </script>
</head>
<body>
<div class="container">
    <div class="alert alert-success" id="succeed" style="display:none;padding:8px 14px 8px 14px;">恭喜，绑定成功！我们推荐您返回查看用户手册（点击欢迎消息中的链接）。也请尽快完善自己的设定（点击菜单"其他" - "个人设定"）。</div>
    <form class="form-signin" id="main-form">
        <h3 class="form-signin-heading">绑定账号</h3>
        <label id="stuidlabel">学号</label>
        <input type="text" class="form-control input-block-level" id="stuid">
        <label id="identifylabel">身份证号</label>
        <input type="text" class="form-control input-block-level" id="identify">
        <div class="control-group">
            <div class="control">
                <button class="button blue-button" id="bound">立即绑定</button>
                <label  class="control-label" id="isok"></label>
            </div>
        </div>
    </form>
</div>

</body>
</html>
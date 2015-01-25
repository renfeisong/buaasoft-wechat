<?php
/**
 * Bound page.
 *
 * @author TimmyXu
 * @since 2.0.0
 */
?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../../includes/css/components.css" media="all">
    <script src="../../includes/plugins/jquery/jquery-2.1.1.min.js"></script>
    <script src="../../includes/plugins/bootstrap/bootstrap.min.js"></script>
    <style type="text/css">
        body {
            font-family: sans-serif;
            padding: 10px;
            background-color: #f5f5f5;
        }
        .form-signin {
            max-width: 300px;
            padding: 19px 29px 29px;
            margin: 0 auto;
            background-color: #fff;
            border: 1px solid #e5e5e5;
            border-radius: 5px;
            box-shadow: 0 1px 2px rgba(0,0,0,.05);
        }
        .form-signin-heading {
            margin: 5px 0 15px 0;;
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
            margin-top: 30px;
        }
        #succeed {
            display: none;
            padding: 15px;
            line-height: 1.5;
            margin-bottom: 20px;
            color: #3c763d;
            background-color: #dff0d8;
            border: 1px solid #d6e9c6;
            border-radius: 4px;
        }
        #isok p {
            margin-bottom: 0;
            margin-top: 20px;
            color: #ff6666;
        }
    </style>

    <script>
        $(function() {
            var getURLParameter = function(sParam) {
                var sPageURL = window.location.search.substring(1);
                var sURLVariables = sPageURL.split('&');
                for (var i = 0; i < sURLVariables.length; i++) {
                    var sParameterName = sURLVariables[i].split('=');
                    if (sParameterName[0] == sParam) {
                        return sParameterName[1];
                    }
                }
            };
            var b = getURLParameter("openid");
            $("#bound").click(function() {
                var stuid = $("#stuid").val();
                var identity = $("#identity").val();
                if (stuid == "") {
                    $("#stuidlabel").html("请输入学号").css("color", "#ff6666");
                }
                if (identity == "") {
                    $("#identitylabel").html("请输入姓名").css("color", "#ff6666");
                }
                if(stuid != "" && identity != "") {
                    $.post("ajax.php", {
                        stuid: stuid,
                        identity: identity,
                        openid: b,
                        from: 'alt'
                    }, function(data){
                        if (data == "0") {
                            $("#main-form").hide();
                            $("#succeed").fadeIn();
                        } else {
                            $("#isok").html("<p>" + data + "</p>");
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
    <form class="form-signin" id="main-form">
        <h3 class="form-signin-heading">绑定账号</h3>
        <label id="stuidlabel">学号</label>
        <input type="text" class="form-control input-block-level" id="stuid">
        <label id="identitylabel">姓名</label>
        <input type="text" class="form-control input-block-level" id="identity">
        <div class="control-group">
            <div class="control">
                <button class="button blue-button" id="bound">立即绑定</button>
                <label  class="control-label" id="isok"></label>
            </div>
        </div>
    </form>
    <div class="alert alert-success" id="succeed">恭喜，绑定成功！</div>
</div>
</body>
</html>
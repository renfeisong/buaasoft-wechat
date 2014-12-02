<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=false">
    <link rel="stylesheet" href="../../includes/css/components.css" media="all">
    <link rel="stylesheet" href="../../includes/css/font-awesome.css" media="all">
    <script src="../../includes/plugins/jquery/jquery-2.1.1.min.js"></script>
    <script src="../../includes/plugins/jquery-validation/jquery.validate.min.js"></script>
    <script src="../../includes/plugins/jquery-validation/messages_zh.js"></script>
    <style>
        body {
            font-family: 'Open Sans', 'Hiragino Sans GB', 'Helvetica Neue', Helvetica, Arial, sans-serif;
        }
        #submit-button {
            margin-top: 10px;
        }
        .msg-failure {
            color: #f3565d;
        }
        .msg-success {
            color: #5cb85c;
        }
    </style>
</head>
<body>

<?php
if (isset($_POST['submit'])):
    require_once dirname(dirname(dirname(__FILE__))) . '/config.php';
    global $wxdb; /* @var $wxdb wxdb  */
if (empty($_POST['openid']) || empty($_POST['feedback'])):
    ?>

    <h2 class="msg-failure"><i class="fa fa-times"></i> 提交失败</h2>
    <p>请确保您从微信进入本页面，并且所填内容不为空。</p>

<?php

else:
    $wxdb->insert('feedback', array(
        'openid' => $_POST['openid'],
        'content' => $_POST['feedback']
    ));
    ?>

    <h2 class="msg-success"><i class="fa fa-check-circle"></i> 成功</h2>
    <p>您的意见将帮助我们做得更好。</p>

<?php endif; ?>

<?php else: ?>

    <form id="feedback-form" method="post" action="submit-feedback.php">
        <h2>反馈</h2>
        <div><label for="feedback">请在此处填写您的想法。</label></div>
        <textarea id="feedback" class="form-control" name="feedback" rows="4" required></textarea>
        <div>
            <input type="hidden" name="openid" value="<?php echo $_GET['openid'] ?>">
            <input type="submit" value="立即提交" class="button green-button" id="submit-button" name="submit">
        </div>
    </form>

    <script>
        $(document).ready(function() {
            $("#feedback-form").validate();
        });
    </script>

<?php endif; ?>

</body>
</html>
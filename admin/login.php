<?php

require_once '../config.php';
require_once "includes/admin.php";

if (isset($_POST['submit'])) {
    if (log_in($_POST['username'], $_POST['password'])) {
        redirect('index.php');
        exit;
    }
}

?>

<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=EDGE">
    <link rel="stylesheet" href="../includes/css/admin.css" media="all">
    <script type="text/javascript" src="../includes/js/jquery/jquery-2.1.1.js"></script>
    <title>后台登录</title>
</head>
<body>
<form method="post" action="login.php">
    <input name="username" placeholder="用户名">
    <input name="password" placeholder="密码">
    <input name="submit" type="submit">
</form>
<a href="register.php">注册</a>
</body>
</html>
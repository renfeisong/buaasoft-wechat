<?php
/**
 * The register page of Admin Panel.
 *
 * @author Renfei Song
 * @since 2.0.0
 */

require_once dirname(__FILE__) . '/includes/admin.php';

if (isset($_POST['submit'])) {
    if ($_POST['password1'] == $_POST['password2']) {
        if (register($_POST['username'], $_POST['password1'])) {
            log_in($_POST['username'], $_POST['password1']);
            redirect('index.php');
            exit;
        }
    }
}

?>

<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=EDGE">
    <link rel="stylesheet" href="../includes/css/admin.css" media="all">
    <script type="text/javascript" src="../includes/js/jquery/jquery-2.1.1.js"></script>
    <title>后台账户注册</title>
</head>
<body>
<form method="post" action="register.php">
    <input name="username" placeholder="用户名">
    <input name="password1" placeholder="密码">
    <input name="password2" placeholder="确认密码">
    <input name="submit" type="submit">
</form>
<a href="login.php">登陆</a>
</body>
</html>
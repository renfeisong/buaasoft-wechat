<?php
/**
 * The login page of Admin Panel.
 *
 * @author Renfei Song
 * @since 2.0.0
 */

require_once dirname(__FILE__) . '/includes/admin.php';

if (isset($_POST['submit'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $remember = $_POST['remember'];

    if (empty($username) || empty($password)) {
        redirect('login.php?msg=1&token=' . time());
    } else if (log_in($username, $password, isset($remember))) {
        redirect('index.php');
    } else {
        redirect('login.php?msg=2&token=' . time());
    }
    exit;
}

if (isset($_GET['msg']) && (time() - $_GET['token']) < 3 && (time() - $_GET['token']) >= 0) {
    switch ($_GET['msg']) {
        case 1:
            $msg = "请输入用户名和密码。";
            break;
        case 2:
            $msg = "用户名或密码错误。";
            break;
    }
}

?><!DOCTYPE HTML>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=EDGE">
    <link rel="stylesheet" href="../includes/css/reset.css" media="all">
    <link rel="stylesheet" href="../includes/css/font-awesome.css" media="all">
    <link rel="stylesheet" href="../includes/plugins/icheck/grey.css" media="all">
    <link rel="stylesheet" href="../includes/css/components.css" media="all">
    <link rel="stylesheet" href="../includes/css/admin.css" media="all">
    <script type="text/javascript" src="../includes/plugins/jquery/jquery-2.1.1.js"></script>
    <script type="text/javascript" src="../includes/plugins/icheck/icheck.js"></script>
    <title>后台登录</title>
</head>
<body class="login">
<h1 class="site-title">AdminCenter</h1>
<div class="content">
    <h2>Login to your account</h2>
    <?php if (isset($msg)): ?>
    <div class="error"><?php echo $msg ?></div>
    <?php endif; ?>
    <form method="POST" action="login.php">
        <div class="input">
            <i class="fa fa-user"></i>
            <input name="username" type="text" class="form-control" placeholder="Username">
        </div>
        <div class="input">
            <i class="fa fa-lock"></i>
            <input name="password" type="password" class="form-control" placeholder="Password">
        </div>
        <label>
            <input type="checkbox" name="remember" value="1"> Remember me
        </label>
        <button name="submit" type="submit" class="button submit-button">Login <i class="fa fa-sign-in"></i></button>
    </form>
    <div class="alternate-option">
        Don't have an account? <a href="register.php">Click to register</a>
    </div>
</div>
<script>
    $('input').iCheck({
        checkboxClass: 'icheckbox_minimal-grey',
        radioClass: 'iradio_minimal-grey'
    });
</script>
</body>
</html>
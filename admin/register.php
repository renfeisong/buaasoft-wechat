<?php
/**
 * The register page of Admin Panel.
 *
 * @author Renfei Song
 * @since 2.0.0
 */

require_once dirname(__FILE__) . '/includes/admin.php';

if (isset($_POST['submit'])) {
    $username = $_POST['username'];
    $password1 = $_POST['password1'];
    $password2 = $_POST['password2'];

    if (empty($username) || empty($password2) || empty($password2)) {
        redirect('register.php?msg=1&token=' . time());
    } else if ($password1 != $password2) {
        redirect('register.php?msg=2&token=' . time());
    } else if (register($username,$password1)) {
        log_in($username, $password1, false);
        redirect('index.php');
    } else {
        redirect('register.php?msg=3&token=' . time());
    }
    exit;
}

if (isset($_GET['msg']) && (time() - $_GET['token']) < 3 && (time() - $_GET['token']) >= 0) {
    switch ($_GET['msg']) {
        case 1:
            $msg = "请输入用户名和密码。";
            break;
        case 2:
            $msg = "密码和确认密码不一致。";
            break;
        case 3:
            $msg = "用户名已经被占用。";
            break;
        case 4:
            $msg = "密码过于简单。";
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
    <link rel="stylesheet" href="../includes/css/components.css" media="all">
    <link rel="stylesheet" href="../includes/css/admin.css" media="all">
    <script type="text/javascript" src="../includes/plugins/jquery/jquery-2.1.1.js"></script>
    <title>后台账户注册</title>
</head>
<body class="login">
<h1 class="site-title">AdminCenter</h1>
<div class="content">
    <h2>Register an account</h2>
    <?php if (isset($msg)): ?>
        <div class="error"><?php echo $msg ?></div>
    <?php endif; ?>
    <form method="POST" action="register.php">
        <div class="input">
            <i class="fa fa-user"></i>
            <input name="username" type="text" class="form-control" placeholder="Username">
        </div>
        <div class="input">
            <i class="fa fa-lock"></i>
            <input name="password1" type="password" class="form-control" placeholder="Password">
        </div>
        <div class="input">
            <i class="fa fa-lock"></i>
            <input name="password2" type="password" class="form-control" placeholder="Confirm Password">
        </div>
        <button name="submit" type="submit" class="button submit-button">Register <i class="fa fa-sign-in"></i></button>
    </form>
    <div class="alternate-option">
        Already have an account? <a href="login.php">Click to login</a>
    </div>
</div>
</body>
</html>
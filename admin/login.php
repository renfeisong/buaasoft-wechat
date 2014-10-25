<?php
/**
 * The login page of Admin Panel.
 *
 * @author Renfei Song
 * @since 2.0.0
 */

require_once dirname(__FILE__) . '/includes/admin.php';

if (isset($_POST['submit'])) {
    if (log_in($_POST['username'], $_POST['password'], isset($_POST['remember']))) {
        redirect('index.php');
        exit;
    }
}

?>

<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=EDGE">
    <link rel="stylesheet" href="../includes/css/reset.css" media="all">
    <link rel="stylesheet" href="../includes/css/font-awesome.css" media="all">
    <link rel="stylesheet" href="../includes/css/components.css" media="all">
    <link rel="stylesheet" href="../includes/css/admin.css" media="all">
    <script type="text/javascript" src="../includes/js/jquery/jquery-2.1.1.js"></script>
    <title>后台登录</title>
</head>
<body class="login">
<h1 class="site-title">AdminCenter</h1>
<div class="content">
    <form method="POST" action="login.php">
        <h2>Login to your account</h2>
<!--        <div class="alert alert-danger display-hide">-->
<!--			Enter any username and password. -->
<!--        </div>-->
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
</body>
</html>
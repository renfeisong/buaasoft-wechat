<?php
/**
 * This page is shown to user if his/her account is disabled.
 *
 * @author Renfei Song
 * @since 2.0.0
 */

require_once dirname(__FILE__) . '/includes/admin.php';

if (is_disabled() == false) {
    redirect('index.php');
    exit;
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
    <title>无权访问</title>
</head>
<body class="account-disabled">
    <h1 class="site-title"><i class="fa fa-lock"></i>&nbsp; AdminCenter</h1>
    <div class="content">
        <div class="user"><?php echo current_user_name() ?></div>
        <p class="message">您无权访问此区域</p>
        <p class="note">请联系现有管理员为您启用权限。</p>
        <a class="logout" href="logout.php">登出</a>
    </div>
</body>
</html>
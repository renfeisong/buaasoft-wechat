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
    <link rel="stylesheet" href="../includes/css/admin.css" media="all">
    <script type="text/javascript" src="../includes/js/jquery/jquery-2.1.1.js"></script>
    <title>没有权限</title>
</head>
<body>
    您当前登陆的账户 <?php echo current_user_name() ?> 没有权限。
    <a href="logout.php">登出</a>
</body>
</html>
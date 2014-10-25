<?php
/**
 * The index page of Admin Panel.
 *
 * @author Renfei Song
 * @since 2.0.0
 */

require_once dirname(__FILE__) . '/includes/admin.php';

if (is_logged_in() == false) {
    redirect('login.php');
    exit;
}

if (is_disabled()) {
    redirect('account-disabled.php');
    exit;
}

if (!isset($_GET['page'])) {
    redirect('index.php?page=general');
    exit;
}

if (isset($_POST['submit'])) {
    $data = $_POST;
    foreach ($data as $key => $value) {
        set_value(new $_GET['page'], $key, $value);
    }
}

?><!DOCTYPE HTML><html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=EDGE">
    <link rel="stylesheet" href="../includes/css/reset.css" media="all">
    <link rel="stylesheet" href="../includes/css/font-awesome.css" media="all">
    <link rel="stylesheet" href="../includes/css/components.css" media="all">
    <link rel="stylesheet" href="../includes/css/admin.css" media="all">
    <script type="text/javascript" src="../includes/js/jquery/jquery-2.1.1.js"></script>
    <title>管理后台</title>
</head>
<body>

<div id="wrapper" class="site home">
    <header id="masthead" class="site-header">
        <div class="inner">
            <h1 class="site-title">Admin<span>Center</span></h1>
            <a href="logout.php" class="log-out" title="Log Out"><i class="fa fa-sign-out"></i> 登出</a>
        </div>
    </header>
    <div id="main" class="site-main">
        <div class="content-area">
            <div id="primary" class="site-content">
                <?php include_settings($_GET['page']) ?>
            </div>
        </div>
        <div id="secondary" class="site-sidebar">
            <ul class="site-navigation" role="navigation">
                <?php list_global_setting_items() ?>
                <li class="heading">Modules</li>
                <?php list_module_setting_items() ?>
            </ul>
        </div>
    </div>
    <footer id="colophon" class="site-footer">
        footer content
    </footer>
</div>

<script>
    window.addEventListener('resize', onWindowResize);
    onWindowResize();
    function onWindowResize() {
        $('.site-sidebar')[0].style.minHeight = ($(window).height() - 46 - 38) + 'px';
        $('.site-content')[0].style.minHeight = ($(window).height() - 46 - 38) + 'px';
    }
</script>

</body>
</html>
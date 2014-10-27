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

if (!is_enabled()) {
    redirect('account-disabled.php');
    exit;
}

if (!isset($_GET['page'])) {
    redirect('index.php?page=general');
    exit;
}

// Handle Form Submission

if (isset($_POST['wx_submit'])) {
    foreach ($_POST as $key => $value) {
        set_value(new $_GET['page'], $key, $value);
    }
    redirect_success('');
    exit;
}

// Show Messages

$show_success_msg = $show_failure_msg = false;

if (!empty($_GET['msg']) && sha1(MESSAGE_SALT . $_GET['msg']) == $_GET['auth']) {
    $show_message_content = addslashes($_GET['msg']);
}

if (isset($_GET['success']) && (time() - $_GET['token']) < 3 && (time() - $_GET['token']) >= 0) {
    $show_success_msg = true;
}

if (isset($_GET['failure']) && (time() - $_GET['token']) < 3 && (time() - $_GET['token']) >= 0) {
    $show_failure_msg = true;
}

// Start the output buffer to allow possible headers sent
// by modules setting page
ob_start();

?><!DOCTYPE HTML>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=EDGE">
    <link rel="stylesheet" href="../includes/css/reset.css" media="all">
    <link rel="stylesheet" href="../includes/css/font-awesome.css" media="all">
    <link rel="stylesheet" href="../includes/plugins/toastr-notifications/toastr.css" media="all">
    <link rel="stylesheet" href="../includes/plugins/icheck/grey.css" media="all">
    <link rel="stylesheet" href="../includes/plugins/select2/select2.css" media="all">
    <link rel="stylesheet" href="../includes/css/select2-custom.css" media="all">
    <link rel="stylesheet" href="../includes/plugins/datatables/css/jquery.dataTables.css" media="all">
    <link rel="stylesheet" href="../includes/css/components.css" media="all">
    <link rel="stylesheet" href="../includes/css/admin.css" media="all">
    <script type="text/javascript" src="../includes/plugins/jquery/jquery-2.1.1.min.js"></script>
    <script type="text/javascript" src="../includes/plugins/jquery-validation/jquery.validate.min.js"></script>
    <script type="text/javascript" src="../includes/plugins/jquery-validation/additional-methods.min.js"></script>
    <script type="text/javascript" src="../includes/plugins/jquery-validation/messages_zh.js"></script>
    <script type="text/javascript" src="../includes/plugins/toastr-notifications/toastr.min.js"></script>
    <script type="text/javascript" src="../includes/plugins/icheck/icheck.min.js"></script>
    <script type="text/javascript" src="../includes/plugins/select2/select2.min.js"></script>
    <script type="text/javascript" src="../includes/plugins/select2/select2_locale_zh-CN.js"></script>
    <script type="text/javascript" src="../includes/plugins/datatables/js/jquery.dataTables.min.js"></script>

    <script type="text/javascript" src="../includes/js/global-options-users.js"></script>
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
            <ul class="site-navigation">
                <?php list_global_setting_items() ?>
                <li class="heading">Modules</li>
                <?php list_module_setting_items() ?>
            </ul>
        </div>
    </div>
    <footer id="colophon" class="site-footer">
        <?php echo queries_count() ?> queries processed in <?php echo timer_stop(6) * 1000 ?> μs.
    </footer>
</div>

<script>
    toastr.options = {
        "closeButton": true,
        "debug": false,
        "positionClass": "toast-top-right",
        "onclick": null,
        "showDuration": "1000",
        "hideDuration": "1000",
        "timeOut": "5000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    };
</script>
<?php echo $show_success_msg ?>
<?php if ($show_success_msg): ?>
    <script>
        toastr.success('<?php if (isset($show_message_content)) echo $show_message_content; else echo 'Your settings have been saved.'; ?>', 'Success');
    </script>
<?php endif; ?>

<?php if ($show_failure_msg): ?>
    <script>
        toastr.error('<?php if (isset($show_message_content)) echo $show_message_content; else echo 'An error occured.'; ?>', 'Error');
    </script>
<?php endif; ?>

<script>
    $('input').iCheck({
        checkboxClass: 'icheckbox_minimal-grey',
        radioClass: 'iradio_minimal-grey'
    });

    $('select').select2();

    window.addEventListener('resize', onWindowResize);
    onWindowResize();
    function onWindowResize() {
        $('.site-sidebar')[0].style.minHeight = ($(window).height() - 46 - 38) + 'px';
        $('.site-content')[0].style.minHeight = ($(window).height() - 46 - 38) + 'px';
    }
</script>

</body>
</html>

<?php ob_end_flush(); ?>
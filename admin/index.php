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

    $page = $_GET['page'];

// Handle Form Submission

if (isset($_POST['wx_submit'])) {
    foreach ($_POST as $key => $value) {
        set_value(new $_GET['page'], $key, $value);
    }
    redirect_success('');
    exit;
}

// Show Messages

$show_success_msg = $show_failure_msg = $show_notice_msg = false;

if (!empty($_GET['msg']) && sha1(MESSAGE_SALT . $_GET['msg']) == $_GET['auth']) {
    $show_message_content = addslashes($_GET['msg']);
}

if (isset($_GET['success']) && (time() - $_GET['token']) < 3 && (time() - $_GET['token']) >= 0) {
    $show_success_msg = true;
}

if (isset($_GET['failure']) && (time() - $_GET['token']) < 3 && (time() - $_GET['token']) >= 0) {
    $show_failure_msg = true;
}

if (isset($_GET['notice']) && (time() - $_GET['token']) < 3 && (time() - $_GET['token']) >= 0) {
    $show_notice_msg = true;
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
    <link rel="stylesheet" href="../includes/css/table.css" media="all">
    <link rel="stylesheet" href="../includes/css/editable.css" media="all">
    <link rel="stylesheet" href="../includes/css/tab.css" media="all">
    <link rel="stylesheet" href="../includes/css/components.css" media="all">
    <link rel="stylesheet" href="../includes/css/admin.css" media="all">
    <script src="../includes/plugins/jquery/jquery-2.1.1.min.js"></script>
    <script src="../includes/plugins/jquery-validation/jquery.validate.min.js"></script>
    <script src="../includes/plugins/jquery-validation/additional-methods.min.js"></script>
    <script src="../includes/plugins/jquery-validation/messages_zh.js"></script>
    <script src="../includes/plugins/toastr-notifications/toastr.min.js"></script>
    <script src="../includes/plugins/icheck/icheck.min.js"></script>
    <script src="../includes/plugins/select2/select2.min.js"></script>
    <script src="../includes/plugins/select2/select2_locale_zh-CN.js"></script>
    <script src="../includes/plugins/datatables/js/jquery.dataTables.min.js"></script>
    <script src="../includes/plugins/datatables/js/dataTables.bootstrap.js"></script>
    <script src="../includes/plugins/bootstrap/bootstrap.min.js"></script>
    <script src="../includes/plugins/bootstrap3-editable/js/bootstrap-editable.js"></script>
    <script src="../includes/plugins/bootstrap3-editable/js/bootstrap-datepicker.js"></script><!-- Optional -->
    <script src="../includes/plugins/bootstrap3-editable/js/bootstrap-datepicker.zh-CN.js"></script><!-- Optional -->
    <script src="../includes/js/global-options-users.js"></script>
    <title>管理后台</title>
</head>
<body>

    <header id="masthead" class="site-header">
        <div class="inner">
            <h1 class="site-title">Admin<span>Center</span></h1>
            <a href="logout.php" class="log-out" title="Log Out"><i class="fa fa-sign-out"></i> 登出</a>
        </div>
    </header>
    <div id="main" class="site-main">
        <div class="content-area">
            <div id="primary" class="site-content">
                <?php
                    if (current_user_can_manage($page)) {
                        include_settings($_GET['page']);
                    } else {
                        admin_unauthorized_error();
                    }
                 ?>
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

    <?php if ($show_notice_msg): ?>
        <script>
            toastr.info('<?php if (isset($show_message_content)) echo $show_message_content; else echo 'Something happened.'; ?>', 'Notice');
        </script>
    <?php endif; ?>

<script>
    $(document).ready(function() {
        $('input').iCheck({
            checkboxClass: 'icheckbox_minimal-grey',
            radioClass: 'iradio_minimal-grey'
        });

        $('select').select2();
        window.addEventListener('resize', onWindowResize);
        onWindowResize();
    });

    function onWindowResize() {
        $('.site-sidebar')[0].style.minHeight = ($(window).height() - 46 - 38) + 'px';
        $('.site-content')[0].style.minHeight = ($(window).height() - 46 - 38) + 'px';
    }
</script>

</body>
</html>

<?php ob_end_flush(); ?>
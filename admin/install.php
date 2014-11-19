<?php
/**
 * Standalone Installation Script
 *
 * @author Renfei Song
 * @since 2.0.0
 */

define('ABSPATH', dirname(dirname(__FILE__)) . '/');

require_once ABSPATH . 'config.php';

// creating table user
$sql1 = <<<SQL
CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `userName` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `gender` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `identifyId` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `openid` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `startYear` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dept` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `class` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `birthday` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phoneNumber` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `qq` varchar(100) COLLATE utf8_unicode_ci  DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SQL;

// creating table admin_user
$sql2 = <<<SQL
CREATE TABLE IF NOT EXISTS `admin_user` (
  `userName` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `hashedPassword` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `isEnabled` int(11) DEFAULT NULL,
  `joinDate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `lastActivity` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `authorizedPages` varchar(500) COLLATE utf8_unicode_ci NOT NULL DEFAULT '[]',
  `isSuperAdmin` int(11) NOT NULL DEFAULT '0',
  `note` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `loginToken` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`userName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SQL;

// creating table configuration
$sql3 = <<<SQL
CREATE TABLE IF NOT EXISTS `configuration` (
  `scope` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `key` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `value` varchar(1000) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dateUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`scope`,`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SQL;

$dbc = @new mysqli(DB_HOST, DB_USER, DB_PASSWORD);

if ($dbc->connect_errno) {
    $blocking_msg = '数据库服务器连接失败，请检查<code>config.php</code>中的配置是否正确。';
} else {
	$dbc->select_db(DB_NAME);
	if ($dbc->errno) {
    	$blocking_msg = '数据库服务器已连接，但名为<code>' . DB_NAME . '</code>的数据库不存在。你需要创建该数据库后才能安装（如要使用其他名称，请修改<code>config.php</code>中的配置）。';
    } else {
	    // check if it's already installed
		$tbl_user = $dbc->query("show tables like 'user'")->num_rows;
		$tbl_admin = $dbc->query("show tables like 'admin_user'")->num_rows;
		$tbl_configuration = $dbc->query("show tables like 'configuration'")->num_rows;
		$num_admin = $tbl_admin == 0 ? 0 : $dbc->query("select * from `admin_user`")->num_rows;
		if ($tbl_user + $tbl_admin + $tbl_configuration + $num_admin >= 4) {
			$blocking_msg = '系统似乎已经安装。如要重新安装，请删除整个数据库后重试。';
		} else {
			$dbc->query($sql1);
			$dbc->query($sql2);
			$dbc->query($sql3);
		}
    }
}

if (isset($_POST['submit'])) {
    $name = @$_POST['admin_name'];
    $pass = @$_POST['admin_pass'];
    $pass2 = @$_POST['admin_pass2'];
    if (!empty($name) && !empty($pass) && !empty($pass2)) {
        if ($pass == $pass2) {
            $hpass = sha1($pass);
            $date = date('c');
            $sql = "insert into `admin_user` (userName, hashedPassword, isEnabled, joinDate, lastActivity, isSuperAdmin) values ('$name', '$hpass', 1, '$date', '$date', 1)";
            $dbc->query($sql);
            header('Location: index.php');
            exit;
        } else {
            $errmsg = '两次输入的密码不匹配，请重试。';
        }
    } else {
        $errmsg = '请填写完整表单后提交。';
    }
}

?><!DOCTYPE HTML>
<html>
<head>
    <meta charset="UTF-8">
    <title>Install</title>
    <link rel="stylesheet" href="../includes/css/admin.css">
    <link rel="stylesheet" href="../includes/css/components.css">
    <style>
        .page-title {
            margin: 0 0 15px;
            font-size: 24px;
        }
        .error-block {
            color: #a94442;
            background-color: #f2dede;
            border:1px solid #ebccd1;
            padding: 10px 15px;
            border-radius: 4px;
        }
        .help-block {
            color: #999;
            margin: 15px 0;
        }
        #wrapper {
            background: white;
            width: 550px;
            padding: 20px 25px;
            margin: 40px auto 0;
        }
    </style>
</head>
<body>
<div id="wrapper">
    <h1 class="page-title">安装</h1>
    <?php if (isset($blocking_msg)): ?>
        <?php echo $blocking_msg; ?>
    <?php else: ?>
        <?php if (isset($errmsg)): ?>
            <div class="error-block">
                <?php echo $errmsg ?>
            </div>
        <?php else: ?>
            <div class="help-block">
                欢迎！我们已经为你创建好了所需的数据表，请在此处设置后台超级管理员账户的用户名和密码。
            </div>
        <?php endif; ?>
        <form method="post" action="install.php" id="install">
            <div class="form-group">
                <label for="admin_name">管理员用户名</label>
                <input type="text" name="admin_name" id="admin_name" class="form-control">
            </div>
            <div class="form-group">
                <label for="admin_pass">管理员密码</label>
                <input type="password" name="admin_pass" id="admin_pass" class="form-control">
            </div>
            <div class="form-group">
                <label for="admin_pass2">确认管理员密码</label>
                <input type="password" name="admin_pass2" id="admin_pass2" class="form-control">
            </div>
            <input type="submit" name="submit" value="安装" class="button green-button">
        </form>
    <?php endif; ?>
</div>
</body>
</html>
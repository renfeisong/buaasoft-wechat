<?php
if (isset($_POST['submit'])) {
    $username = $_POST['username'];
    $password1 = $_POST['password1'];
    $password2 = $_POST['password2'];

    if (empty($username) || empty($password2) || empty($password2)) {
        redirect_failure('密码不能为空，请重试。');
    } else if ($password1 != $password2) {
        redirect_failure('两次输入的密码不相同，请重试。');
    } else if (($p = validatePassword($password1)) != 0) {
        if ($p == 4)
            redirect_failure('密码长度须在 6~20 位之间，请重试。');
        elseif ($p == 5)
            redirect_failure('密码包含非法字符，请重试。');
        elseif ($p == 6)
            redirect_failure('该密码由于易受攻击，已被系统禁止使用。如有疑问，请联系管理员。');
        else
            redirect_failure('未知错误 ' . $p . '。');
    } else if (changePassword($username, $password1)) {
        global $wxdb; /* @var $wxdb wxdb */
        $wxdb->insert('security_log', array(
            'userName' => current_user_name(),
            'opName' => 'User.changePassword',
            'opDetail' => 'Success',
            'ip' => $_SERVER['REMOTE_ADDR'],
            'agent' => $_SERVER['HTTP_USER_AGENT']
        ));
        redirect_success('密码修改成功。');
    } else {
        redirect_failure('系统错误。');
    }
    exit;
}
?>
<h2>安全选项</h2>
<h3>修改密码</h3>
<form method="post" id="change-password-form" style="max-width: 450px">
    <div class="form-group">
        <div class="prompt">
            <label for="password1">新密码</label>
        </div>
        <div class="control">
            <input class="form-control" type="password" name="password1" id="password1" required>
        </div>
    </div>
    <div class="form-group">
        <div class="prompt">
            <label for="password2">确认新密码</label>
        </div>
        <div class="control">
            <input class="form-control" type="password" name="password2" id="password2" required>
        </div>
    </div>
    <input type="hidden" name="username" value="<?php echo current_user_name() ?>">
    <input type="submit" name="submit" class="button gray-button" value="修改密码">
</form>
<h3>操作历史</h3>
<p>这里列出了和你的账户相关的重要操作记录。</p>
<table class="table table-striped table-bordered table-hover" style="margin: 15px 0; word-break: break-all">
    <thead>
    <tr>
        <th style="min-width: 100px;">actor</th>
        <th>action</th>
        <th style="min-width: 130px;">actor_ip</th>
        <th style="min-width: 180px;">created_at</th>
    </tr>
    </thead>
    <tbody>
    <?php
    global $wxdb; /* @var $wxdb wxdb */
    $user = current_user_name();
    $sql = $wxdb->prepare("select * from security_log where userName = '%s' order by id desc limit 0, 20", $user);
    $rows = $wxdb->get_results($sql, ARRAY_A);

    foreach ($rows as $row) {
        if ($row['opDetail'] == 'Success') {
            $detail = '';
        } else {
            $detail = " – {$row['opDetail']}";
            $detail = str_replace('Success: ', '', $detail);
        }
        echo "<tr><td>$user</td><td><strong>{$row['opName']}</strong>$detail</td><td>{$row['ip']}</td><td>{$row['timestamp']}</td></tr>";
    }
    ?>
    </tbody>
</table>
<script>
    $(document).ready(function() {
        $("#change-password-form").validate();
    });
</script>
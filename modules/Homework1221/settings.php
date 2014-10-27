<?php
/**
 * Homework Module
 *
 * @author Renfei Song
 */

if (isset($_POST['add'])) {
    $publish_date = $_POST['publishDate'];
    $due_date = $_POST['dueDate'];
    $subject = $_POST['subject'];
    $content = $_POST['content'];

    if (empty($publish_date) || empty($due_date) || empty($subject) || empty($content)) {
        redirect_failure('Empty form is not accepted.');
        exit;
    }

    global $wxdb; /* @var $wxdb wxdb */
    $wxdb->insert('homework', array(
        'subject' => $subject,
        'content' => $content,
        'userName' => current_user_name(),
        'publishDate' => $publish_date,
        'dueDate' => $due_date,
        'dateUpdated' => date('c')
    ));

    redirect_success('Homework added!');
    exit;
}
?>

<h2>Homework Mgmt. Panel</h2>

<h3>添加作业</h3>
<form method="POST" id="add-homework">
    <div class="form-group">
        <div class="prompt">
            <label for="publishDate">布置日期</label>
        </div>
        <div class="control">
            <input class="form-control" type="text" name="publishDate" id="publishDate" required>
        </div>
    </div>
    <div class="form-group">
        <div class="prompt">
            <label for="dueDate">截止日期</label>
        </div>
        <div class="control">
            <input class="form-control" type="text" name="dueDate" id="dueDate">
        </div>
    </div>
    <div class="form-group">
        <div class="prompt">
            <label for="subject">科目</label>
        </div>
        <div class="control">
            <input class="form-control" type="text" name="subject" id="subject">
        </div>
    </div>
    <div class="form-group">
        <div class="prompt">
            <label for="content">内容</label>
        </div>
        <div class="control">
            <textarea class="form-control" name="content" rows="3" id="content"></textarea>
        </div>
    </div>
    <button type="submit" class="button submit-button" name="add"><i class="fa fa-plus"></i> 添加作业</button>
</form>

<script>
    $("#add-homework").validate();
</script>

<h3>管理作业</h3>

<table id="show-homework">
    <thead>
    <tr>
        <th>序号</th>
        <th>布置日期</th>
        <th>过期日期</th>
        <th>添加人</th>
        <th>科目</th>
        <th>内容</th>
        <th>更新日期</th>
        <th>操作</th>
    </tr>
    </thead>
    <tbody>
    <?php
    global $wxdb; /* @var $wxdb wxdb */
    $sql = "SELECT * FROM homework ORDER BY homeworkId DESC";
    $rows = $wxdb->get_results($sql, ARRAY_A);
    foreach ($rows as $row) {
        echo <<<HTML
<tr>
    <td>{$row['homeworkId']}</td>
    <td>{$row['publishDate']}</td>
    <td>{$row['dueDate']}</td>
    <td>{$row['userName']}</td>
    <td>{$row['subject']}</td>
    <td>{$row['content']}</td>
    <td>{$row['dateUpdated']}</td>
    <td><button class="button" data-id="{$row['homeworkId']}">编辑</button></td>
</tr>
HTML;
    }
    ?>
    </tbody>
</table>

<script>
    $("#show-homework").dataTable();
</script>
<?php
/**
 * Homework Module
 *
 * @author Renfei Song
 */

if (isset($_POST['add'])) {
    redirect_success('Homework added!');
}
?>

<h2>Homework Mgmt. Panel</h2>

<h3>添加作业</h3>
<form method="POST">
    <div class="form-group">
        <div class="prompt">
            <label for="publishDate">布置日期</label>
        </div>
        <div class="control">
            <input class="form-control" type="text" name="publishDate" id="publishDate">
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

<h3>管理作业</h3>
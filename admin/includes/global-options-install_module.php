<?php
/**
 * This file is the install module page for the admin center.
 *
 * @author Bingchen Qin
 * @since 2.0.0
 */

global $wxdb; /* @var $wxdb wxdb */

$result = $wxdb->get_results('SELECT * FROM admin_user', ARRAY_A);

?>

<h2>安装模块</h2>

<form action="./includes/global-options-install_module-ajax.php" method="post" enctype="multipart/form-data">
<!--    <input type="file" name="file">-->
<!--    <button class="button red-button" type="submit">选择文件...</button>-->

    <label for="file">Filename:</label>
    <input type="file" name="file" id="file" />
    <input type="submit" name="submit" value="Submit" />
</form>
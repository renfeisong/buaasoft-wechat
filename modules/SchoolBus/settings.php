<?php
/**
 * Setting page for School Bus Module
 *
 * @author Zhan Yu
 */

/**
 * Return required routes
 *
 * @return array
 */
function get_route_required() {
    global $wxdb;
    return $wxdb->get_results("SELECT * FROM `".get_option('table_route')."` WHERE 1", ARRAY_A);
}

/**
 * Return all bus
 *
 * @return array
 */
function get_bus() {
    global $wxdb;
    return $wxdb->get_results("SELECT * FROM `".get_option('table_bus')."` WHERE 1", ARRAY_A);
}

// Generate ajax secret key
$ajax_key = sha1(get_option('ajax_secret') . rand(111111, 999999));
set_option('ajax_key', $ajax_key);
?>
<link rel="stylesheet" href="../modules/SchoolBus/settings.css"/>
<h2>校车管理面板</h2>
<h3>校车管理</h3>
<table id="schoolbus-bus-table" class="table table-striped table-bordered table-hover">
    <thead>
    <tr>
        <th>日期</th>
        <th>始发站</th>
        <th>终点站</th>
        <th>发车时间</th>
        <th class="nosort">操作</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach (get_bus() as $row): ?>
        <tr data-pk="<?=$row['id']?>">
            <td><a href="#" data-type="select2" data-pk="<?=$row['id']?>" data-url="<?=ROOT_URL?>/modules/SchoolBus/ajax.php?action=editBus&auth=<?=$ajax_key?>" data-name="day" class="x-editable-day"><?=($row['day'] == 1 ? '周一至周五' : '周六、周日')?></a></td>
            <td><a href="#" data-type="text" data-pk="<?=$row['id']?>" data-url="<?=ROOT_URL?>/modules/SchoolBus/ajax.php?action=editBus&auth=<?=$ajax_key?>" data-name="departure" class="x-editable-text"><?=$row['departure']?></a></td>
            <td><a href="#" data-type="text" data-pk="<?=$row['id']?>" data-url="<?=ROOT_URL?>/modules/SchoolBus/ajax.php?action=editBus&auth=<?=$ajax_key?>" data-name="destination" class="x-editable-text"><?=$row['destination']?></a></td>
            <td><a href="#" data-type="text" data-pk="<?=$row['id']?>" data-url="<?=ROOT_URL?>/modules/SchoolBus/ajax.php?action=editBus&auth=<?=$ajax_key?>" data-name="departureTime" class="x-editable-time"><?=substr($row['departureTime'], 0, -3)?></a></td>
            <td>
                <button class="button red-button xs-button idle delete-item" data-name="bus" data-pk="<?=$row['id']?>">
                    <span class="idle-only" style="display: none"><i class="fa fa-trash-o"></i> 删除</span>
                    <span class="confirm-only" style="display: none">请确认</span>
                    <span class="in-progress-only" style="display: none"><i class="fa fa-spinner fa-spin"></i> 稍等..</span>
                </button>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot>
    <tr>
        <td><select class="form-control" id="new-bus-day">
                <option value="1">周一至周五</option>
                <option value="0">周六、周日</option>
        </select></td>
        <td><input class="form-control" type="text" id="new-bus-departure" placeholder="例：沙河"></td>
        <td><input class="form-control" type="text" id="new-bus-destination" placeholder="例：学院路"></td>
        <td><input class="form-control" type="text" id="new-bus-departureTime" placeholder="例：14:03"></td>
        <td><a style="margin-top:5px;" class="button xs-button blue-button idle" href="javascript:void(0);" onclick="addBus();"><i class="fa fa-plus"></i> 添加</a></td>
    </tr>
    </tfoot>
</table>
<h3>微信回复路线管理</h3>
<table id="schoolbus-route-table" class="table table-striped table-bordered table-hover">
    <thead>
    <tr>
        <th>始发站</th>
        <th>终点站</th>
        <th class="nosort">操作</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach (get_route_required() as $row): ?>
        <tr data-pk="<?=$row['id']?>">
            <td><?=$row['departure']?></td>
            <td><?=$row['destination']?></td>
            <td>
                <button class="button red-button xs-button idle delete-item" data-name="route" data-pk="<?=$row['id']?>">
                    <span class="idle-only" style="display: none"><i class="fa fa-trash-o"></i> 删除</span>
                    <span class="confirm-only" style="display: none">请确认</span>
                    <span class="in-progress-only" style="display: none"><i class="fa fa-spinner fa-spin"></i> 稍等..</span>
                </button>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot>
    <tr>
        <td><select class="form-control" id="new-route-departure"><option value="0">请选择</option></select></td>
        <td><select class="form-control" id="new-route-destination"><option value="0">请选择</option></select></td>
        <td><a style="margin-top:5px;" class="button xs-button blue-button idle" href="javascript:void(0);" onclick="addRoute();"><i class="fa fa-plus"></i> 添加</a></td>
    </tr>
    </tfoot>
</table>
<div><p>
    在此处选中的路线会在微信交互信息中显示，所有的班车都会在班车列表中显示。
</p></div>
<script>
    var authKey = '<?=$ajax_key?>';
    var rootUrl = '<?=ROOT_URL?>';
</script>
<script src="../modules/SchoolBus/settings.js"></script>
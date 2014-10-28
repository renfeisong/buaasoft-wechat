<h2>MessageStat 配置</h2>

<h3>基础设定</h3>

<form method="post">
    <?php $print = get_option('print') ?>
    <div class="form-group">
        <div class="prompt">
            <label for="print">输出设定</label>
            <p class="note">控制是否输出用户输入的信息。</p>
        </div>
        <div class="control">
            <input class="form-control" type="hidden" name="print">
            <input class="form-control" type="checkbox" name="print" id="print" value="true" <?php if ($print == 'true') echo 'checked' ?>> print?
<!--            <p class="note">控制是否输出用户输入的信息。</p>-->
        </div>
    </div>
    <?php submit_button(); ?>
</form>

<h3>更多设定(sample)</h3>

<form method="post" id="my-form">
    <div class="form-group">
        <div class="prompt">
            <label for="in1">设置1</label>
        </div>
        <div class="control">
            <input class="form-control" type="text" name="in1" id="in1" value="<?php echo get_option('in1') ?>" required>
        </div>
    </div>

    <div class="form-group">
        <div class="prompt">
            <label for="in2">设置2</label>
            <p class="note">note2</p>
        </div>
        <div class="control">
            <input class="form-control" type="text" name="in2" id="in2" value="<?php echo get_option('in2') ?>">
        </div>
    </div>

    <div class="form-group">
        <div class="prompt">
            <label for="in3">设置3</label>
            <p class="note">note3</p>
        </div>
        <div class="control">
            <?php $in3 = get_option('in3') ?>
            <input class="form-control" type="radio" name="in3" value="v3-1" <?php if ($in3 == 'v3-1') echo 'checked' ?>> Offer A<br>
            <input class="form-control" type="radio" name="in3" value="v3-2" <?php if ($in3 == 'v3-2') echo 'checked' ?>> Offer B<br>
            <input class="form-control" type="radio" name="in3" value="v3-3" <?php if ($in3 == 'v3-3') echo 'checked' ?>> Offer C
        </div>
    </div>

    <div class="form-group">
        <div class="prompt">
            <label for="in4">设置4</label>
        </div>
        <div class="control">
            <?php $in4 = get_option('in4') ?>
            <input class="form-control" type="hidden" name="in4[]">
            <input class="form-control" type="checkbox" name="in4[]" value="v4-1" <?php if (in_array('v4-1', $in4)) echo 'checked' ?>> Product A<br>
            <input class="form-control" type="checkbox" name="in4[]" value="v4-2" <?php if (in_array('v4-2', $in4)) echo 'checked' ?>> Product B<br>
            <input class="form-control" type="checkbox" name="in4[]" value="v4-3" <?php if (in_array('v4-3', $in4)) echo 'checked' ?>> Product C
        </div>
    </div>

    <div class="form-group">
        <div class="prompt">
            <label for="in5">设置5</label>
        </div>
        <div class="control">
            <textarea class="form-control" name="in5" rows="4" id="in5"><?php echo get_option('in5') ?></textarea>
        </div>
    </div>

    <div class="form-group">
        <div class="prompt">
            <label for="in6">设置6</label>
        </div>
        <div class="control">
            <?php $in6 = get_option('in6') ?>
            <select name="in6" class="form-control" id="in6">
                <option value="v6-1" <?php if ($in6 == 'v6-1') echo 'selected' ?>>Option 1</option>
                <option value="v6-2" <?php if ($in6 == 'v6-2') echo 'selected' ?>>Option 2</option>
                <option value="v6-3" <?php if ($in6 == 'v6-3') echo 'selected' ?>>Option 3</option>
                <option value="v6-4" <?php if ($in6 == 'v6-4') echo 'selected' ?>>Option 4</option>
            </select>
        </div>
    </div>

    <?php submit_button(); ?>
    <?php reset_button('reset_form()'); ?>
</form>

<script>
    $("#my-form").validate();
    function reset_form() {
        // reset the form
    }
</script>
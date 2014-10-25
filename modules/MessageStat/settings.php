<h2>设定</h2>

<form method="post">
    <?php $print = get_option('print') ?>
    <p>
        <input type="hidden" name="print">
        <input type="checkbox" name="print" value="true" <?php if ($print == 'true') echo 'checked' ?>> print?
    </p>
    <?php submit_button(); ?>
</form>

<h2>设定(sample)</h2>

<form method="post">
    <p><input type="text" name="in1" value="<?php echo get_option('in1') ?>"></p>
    <p><input type="text" name="in2" value="<?php echo get_option('in2') ?>"></p>
    <p>
        <?php $in3 = get_option('in3') ?>
        <input type="radio" name="in3" value="v3-1" <?php if ($in3 == 'v3-1') echo 'checked' ?>> Offer A
        <input type="radio" name="in3" value="v3-2" <?php if ($in3 == 'v3-2') echo 'checked' ?>> Offer B
        <input type="radio" name="in3" value="v3-3" <?php if ($in3 == 'v3-3') echo 'checked' ?>> Offer C
    </p>
    <p>
        <?php $in4 = get_option('in4') ?>
        <input type="hidden" name="in4[]">
        <input type="checkbox" name="in4[]" value="v4-1" <?php if (in_array('v4-1', $in4)) echo 'checked' ?>> Product A
        <input type="checkbox" name="in4[]" value="v4-2" <?php if (in_array('v4-2', $in4)) echo 'checked' ?>> Product B
        <input type="checkbox" name="in4[]" value="v4-3" <?php if (in_array('v4-3', $in4)) echo 'checked' ?>> Product C
    </p>
    <p><textarea name="in5"><?php echo get_option('in5') ?></textarea></p>
    <p>
        <?php $in6 = get_option('in6') ?>
        <select name="in6">
            <option value="v6-1" <?php if ($in6 == 'v6-1') echo 'selected' ?>>Option 1</option>
            <option value="v6-2" <?php if ($in6 == 'v6-2') echo 'selected' ?>>Option 2</option>
            <option value="v6-3" <?php if ($in6 == 'v6-3') echo 'selected' ?>>Option 3</option>
            <option value="v6-4" <?php if ($in6 == 'v6-4') echo 'selected' ?>>Option 4</option>
        </select>
    </p>
    <p>
        <?php $in7 = get_option('in7') ?>
        <input type="hidden" name="in7[]">
        <select name="in7[]" multiple>
            <option value="v7-1" <?php if (in_array('v7-1', $in7)) echo 'selected' ?>>Option 1</option>
            <option value="v7-2" <?php if (in_array('v7-2', $in7)) echo 'selected' ?>>Option 2</option>
            <option value="v7-3" <?php if (in_array('v7-3', $in7)) echo 'selected' ?>>Option 3</option>
            <option value="v7-4" <?php if (in_array('v7-4', $in7)) echo 'selected' ?>>Option 4</option>
        </select>
    </p>
    <?php submit_button(); ?>
</form>
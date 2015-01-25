<?php
/**
 * Bound module.
 *
 * @author TimmyXu
 * @since 2.0.0
 */

class Bound extends BaseModule {
    public function can_handle_input(UserInput $input) {
        global $wxdb;
        $openid = $input->openid;
        $sql = $wxdb->prepare("SELECT * FROM user WHERE openid = '%s'" , $openid);
        $wxdb->query($sql);
        $num = $wxdb->num_rows;
        if ($num == 0)
            return true;
        else
            return false;
    }

    public function priority() {
        return 1;
    }

    public function raw_output(UserInput $input) {
        $formatter = new OutputFormatter($input->openid, $input->accountId);
        $url1 = ROOT_URL . 'modules/Bound/bound.php?openid=' . $input->openid;
        $url2 = ROOT_URL . 'modules/Bound/bound-alt.php?openid=' . $input->openid;
        $output = 	"你好！欢迎关注北京航空航天大学软件学院微信公众账号，需要先绑定才能使用：\n\n".
            "<a href=\"" . $url1 . "\">在校本科生绑定通道</a>\n\n".
            "<a href=\"" . $url2 . "\">在校研究生绑定通道</a>";
        return $formatter->textOutput($output);
    }

    public function display_name() {
        return "新用户绑定";
    }
}
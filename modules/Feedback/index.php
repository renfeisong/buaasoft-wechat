<?php
/**
 * Class Feedback
 *
 * @author Renfei Song
 */

class Feedback extends BaseModule {
    public function prepare() {
        global $wxdb; /* @var $wxdb wxdb */
        if (!$wxdb->schema_exists('feedback')) {
            $sql = <<<SQL
CREATE TABLE `feedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `openid` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `content` varchar(1000) COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
SQL;
            $wxdb->query($sql);
        }
    }
    public function can_handle_input(UserInput $input) {
        if ($input->inputType == InputType::Click && $input->eventKey == "FEEDBACK")
            return true;
    }
    public function raw_output(UserInput $input) {
        $formatter = new OutputFormatter($input->openid, $input->accountId);
        $url = ROOT_URL . 'modules/Feedback/submit-feedback.php?openid=' . $input->openid;
        $output = "<a href=\"" . $url . "\">轻按此链接来进行反馈</a>";
        return $formatter->textOutput($output);
    }
    public function display_name() {
        return '意见反馈';
    }
}
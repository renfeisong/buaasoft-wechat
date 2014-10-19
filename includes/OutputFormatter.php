<?php
/**
 * OutputFormatter Class
 *
 * @author Renfei Song
 * @since 1.0
 */

/**
 * Class OutputFormatter
 *
 * Instances of OutputFormatter format the XML-representation of messages of certain kinds. The representation
 * encompasses all the data needed as per the documentation.
 */
class OutputFormatter {

    private $openid;
    private $accountId;

    public function __construct($openid, $accountId) {
        $this->openid = $openid;
        $this->accountId = $accountId;
    }

    /**
     * Returns a string that responds a text message to the user.
     *
     * @param string $text Content of the message.
     * @return string A string that responds a text message to the user.
     */
    public function textOutput($text) {
        $template = "<xml><ToUserName><![CDATA[%s]]></ToUserName><FromUserName><![CDATA[%s]]></FromUserName><CreateTime>%s</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA[%s]]></Content></xml>";
        return sprintf($template, $this->openid, $this->accountId, time(), $text);
    }
} 
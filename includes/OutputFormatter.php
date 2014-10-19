<?php

class OutputFormatter {

    private $openid;
    private $accountId;

    public function __construct($openid, $accountId) {
        $this->openid = $openid;
        $this->accountId = $accountId;
    }

    public function textOutput($text) {
        $template = "<xml><ToUserName><![CDATA[%s]]></ToUserName><FromUserName><![CDATA[%s]]></FromUserName><CreateTime>%s</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA[%s]]></Content></xml>";
        return sprintf($template, $this->openid, $this->accountId, time(), $text);
    }
} 
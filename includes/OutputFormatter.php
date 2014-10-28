<?php
/**
 * OutputFormatter Class
 *
 * @author Renfei Song
 * @since 2.0.0
 */

/**
 * Class OutputFormatter
 *
 * Instances of OutputFormatter format the XML-representation of messages of certain kinds. The representation
 * encompasses all the data needed as per the documentation.
 *
 * @link https://github.com/renfeisong/buaasoft-wechat/wiki/OutputFormatter-Class-Reference
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
        $template =
            "<xml>
                <ToUserName><![CDATA[%s]]></ToUserName>
                <FromUserName><![CDATA[%s]]></FromUserName>
                <CreateTime>%s</CreateTime>
                <MsgType><![CDATA[text]]></MsgType>
                <Content><![CDATA[%s]]></Content>
            </xml>";
        return sprintf($template, $this->openid, $this->accountId, time(), $text);
    }

    public function singleNewsOutput($title, $description, $pic_url, $redirect_url) {
        $template =
            "<xml>
                <ToUserName><![CDATA[%s]]></ToUserName>
                <FromUserName><![CDATA[%s]]></FromUserName>
                <CreateTime>%s</CreateTime>
                <MsgType><![CDATA[news]]></MsgType>
                <ArticleCount>1</ArticleCount>
                <Articles>
                    <item>
                        <Title><![CDATA[%s]]></Title>
                        <Description><![CDATA[%s]]></Description>
                        <PicUrl><![CDATA[%s]]></PicUrl>
                        <Url><![CDATA[%s]]></Url>
                    </item>
                </Articles>
            </xml>";
        return sprintf($template, $this->openid, $this->accountId, time(), $title, $description, $pic_url, $redirect_url);
    }

    /**
     * Returns a string that responds a multi-news message to the user.
     *
     * @param array $articles Contents of news.
     * @return string A string that responds a multi-news message to the user.
     */
    public function multiNewsOutput($articles) {
        $template =
            "<xml>
                <ToUserName><![CDATA[%s]]></ToUserName>
                <FromUserName><![CDATA[%s]]></FromUserName>
                <CreateTime>%s</CreateTime>
                <MsgType><![CDATA[news]]></MsgType>
                <ArticleCount>1</ArticleCount>
                <Articles>"
        $item_template = 
                    "<item> 
                        <Title><![CDATA[%s]]></Title>
                        <Description><![CDATA[%s]]></Description>
                        <PicUrl><![CDATA[%s]]></PicUrl>
                        <Url><![CDATA[%s]]></Url>
                    </item>"
        $template_end = 
                "</Articles>
            </xml>";

        $result = sprintf($template, $this->openid, $this->accountId, time(), $title, $description, $pic_url, $redirect_url)

        foreach ($articles as $article) {
            $result .= sprintf($item_template, $article["title"], $article["description"], $article["pic_url"], $article["$redirect_url"]);
        }

        $result .= $template_end;

        return $result;
    }
}
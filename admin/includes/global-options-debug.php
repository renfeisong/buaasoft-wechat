<?php
    global $wxdb;
    $rows = $wxdb->get_results('select * from `user`', ARRAY_A);
?>
<h2>系统调试</h2>

<!-- Nav tabs -->
<ul class="nav nav-tabs" role="tablist">
    <li role="presentation" class="active"><a href="#manual" role="tab" data-toggle="tab">手动模式</a></li>
    <li role="presentation"><a href="#message" role="tab" data-toggle="tab">发送消息</a></li>
    <li role="presentation"><a href="#event" role="tab" data-toggle="tab">发送事件</a></li>
    <li role="presentation"><a href="#log" role="tab" data-toggle="tab">调试信息</a></li>
</ul>

<!-- Tab panes -->
<div class="tab-content">
    <!-- manual -->
    <div role="tabpanel" class="tab-pane fade in active" id="manual">
        <div class="send-panel">
            <div class="form-group">
                <div class="prompt">
                    <label for="postUrl-1">入口地址</label>
                </div>
                <div class="control">
                    <input class="form-control" type="text" name="postUrl-1" id="postUrl-1" value="http://localhost/index.php" required>
                </div>
            </div>
            <div class="form-group">
                <div class="prompt">
                    <label for="postRawData">POST 数据 (raw)</label>
                </div>
                <div class="control">
                    <textarea class="form-control monospace" name="postRawData" id="postRawData" rows="12"></textarea>
                </div>
            </div>
            <button class="button blue-button button-with-icon" name="send"><i class="fa fa-send"></i> 发送</button>
            <button class="button red-button button-with-icon" name="clear"><i class="fa fa-trash"></i> 清空</button>
        </div>
        <div class="receive-panel">
            <div>
                <label class="label gray-label">Status</label>
                <span class="status">N/A</span>
                <label class="label gray-label">Time</label>
                <span class="time">N/A</span>
            </div>
            <pre class="response prettyprint lang-xml linenums monospace"></pre>
        </div>
    </div>
    <script>
        $('#manual button[name="send"]').click(function() {
            var url = $('#postUrl-1').val();
            var data = $('#postRawData').val();
            var time = Date.now();
            $.ajax({
                type: 'POST',
                cache: false,
                contentType: 'raw',
                url: url,
                data: data,
                complete: function(jqXHR, textStatus) {
                    var timeDiff = (Date.now() - time) + ' ms';
                    $("#manual .response").removeClass('prettyprinted');
                    $("#manual .status").text(jqXHR.status + ' ' + jqXHR.statusText);
                    $("#manual .response").text(vkbeautify.xml(jqXHR.responseText));
                    $("#manual .time").text(timeDiff);
                    prettyPrint();
                }
            });
        });
        $('#manual button[name="clear"]').click(function() {
            $('#postRawData').val('');
            $('#manual .time').text('N/A');
            $('#manual .status').text('N/A');
            $('#manual .response').text('');
        });
    </script>
    <!-- message -->
    <div role="tabpanel" class="tab-pane fade" id="message">
        <div class="send-panel">
            <div class="form-group">
                <div class="prompt">
                    <label for="postUrl-2">入口地址</label>
                </div>
                <div class="control">
                    <input class="form-control" type="text" name="postUrl-2" id="postUrl-2" value="http://localhost/index.php" required>
                </div>
            </div>
            <div class="form-group">
                <div class="prompt">
                    <label for="sender-2">发送人</label>
                </div>
                <div class="control">
                    <select class="form-control" name="sender-2" id="sender-2">
                        <?php
                            foreach ($rows as $row) {
                                echo '<option value="'.$row['openid'].'">'.$row['userName'].' ('.$row['userId'].')</option>';
                            }
                        ?>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <div class="prompt">
                    <label for="textMessage">文本消息</label>
                </div>
                <div class="control">
                    <textarea class="form-control" name="textMessage" id="textMessage" rows="3"></textarea>
                </div>
            </div>
            <button class="button blue-button button-with-icon" name="send"><i class="fa fa-send"></i> 发送</button>
            <button class="button red-button button-with-icon" name="clear"><i class="fa fa-trash"></i> 清空</button>
        </div>
        <div class="receive-panel">
            <div>
                <label class="label gray-label">Status</label>
                <span class="status">N/A</span>
                <label class="label gray-label">Time</label>
                <span class="time">N/A</span>
            </div>
            <pre class="response prettyprint lang-xml linenums monospace"></pre>
        </div>
        <script>
            $('#message button[name="send"]').click(function() {
                var url = $('#postUrl-2').val();
                var openid = $('#sender-2').val();
                var timestamp = Math.round(new Date().getTime() / 1000);
                var msg = $('#textMessage').val();
                var time = Date.now();
                var xml = '<xml>' +
                    '<ToUserName><![CDATA[toUser]]></ToUserName>' +
                    '<FromUserName><![CDATA[' + openid + ']]></FromUserName>' +
                    '<CreateTime>' + timestamp + '</CreateTime>' +
                    '<MsgType><![CDATA[text]]></MsgType>' +
                    '<Content><![CDATA[' + msg + ']]></Content>' +
                    '<MsgId>1234567890123456</MsgId>' +
                    '</xml>';
                $.ajax({
                    type: 'POST',
                    cache: false,
                    contentType: 'raw',
                    url: url,
                    data: xml,
                    complete: function(jqXHR, textStatus) {
                        var timeDiff = (Date.now() - time) + ' ms';
                        $("#message .response").removeClass('prettyprinted');
                        $("#message .status").text(jqXHR.status + ' ' + jqXHR.statusText);
                        $("#message .response").text(vkbeautify.xml(jqXHR.responseText));
                        $("#message .time").text(timeDiff);
                        prettyPrint();
                    }
                });
            });
            $('#message button[name="clear"]').click(function() {
                $('#textMessage').val('');
                $('#message .time').text('N/A');
                $('#message .status').text('N/A');
                $('#message .response').text('');
            });
        </script>
    </div>
    <!-- event -->
    <div role="tabpanel" class="tab-pane fade" id="event">
        <div class="send-panel">
            <div class="form-group">
                <div class="prompt">
                    <label for="postUrl-3">入口地址</label>
                </div>
                <div class="control">
                    <input class="form-control" type="text" name="postUrl-3" id="postUrl-3" value="http://localhost/index.php" required>
                </div>
            </div>
            <div class="form-group">
                <div class="prompt">
                    <label for="sender-3">发送人</label>
                </div>
                <div class="control">
                    <select class="form-control" name="sender-3" id="sender-3">
                        <?php
                        foreach ($rows as $row) {
                            echo '<option value="'.$row['openid'].'">'.$row['userName'].' ('.$row['userId'].')</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <div class="prompt">
                    <label for="eventName">EventName</label>
                </div>
                <div class="control">
                    <select class="form-control" name="eventName" id="eventName">
                        <option value="subscribe">subscribe</option><!--null-->
                        <option value="CLICK">CLICK</option><!--EventKey-->
                        <option value="VIEW">VIEW</option><!--EventKey-->
                    </select>
                </div>
            </div>
            <div class="form-group not-subscribe">
                <div class="prompt">
                    <label for="eventKey">EventKey</label>
                </div>
                <div class="control">
                    <input class="form-control" type="text" name="eventKey" id="eventKey">
                </div>
            </div>
            <button class="button blue-button button-with-icon" name="send"><i class="fa fa-send"></i> 发送</button>
            <button class="button red-button button-with-icon" name="clear"><i class="fa fa-trash"></i> 清空</button>
        </div>
        <div class="receive-panel">
            <div>
                <label class="label gray-label">Status</label>
                <span class="status">N/A</span>
                <label class="label gray-label">Time</label>
                <span class="time">N/A</span>
            </div>
            <pre class="response prettyprint lang-xml linenums monospace"></pre>
        </div>
    </div>
    <script>
        function onEventChange() {
            var eventName = $('#eventName').val();
            console.log(eventName);
            if (eventName == 'subscribe') {
                $('#event .not-subscribe').fadeOut();
            } else {
                $('#event .not-subscribe').fadeIn();
            }
        }
        onEventChange();
        $('#eventName').change(onEventChange);
        $('#event button[name="send"]').click(function() {
            var url = $('#postUrl-3').val();
            var openid = $('#sender-3').val();
            var timestamp = Math.round(new Date().getTime() / 1000);
            var eventName = $('#eventName').val();
            var eventKey = $('#eventKey').val();
            var time = Date.now();
            var xml = '<xml>' +
                '<ToUserName><![CDATA[toUser]]></ToUserName>' +
                '<FromUserName><![CDATA[' + openid + ']]></FromUserName>' +
                '<CreateTime>' + timestamp + '</CreateTime>' +
                '<MsgType><![CDATA[event]]></MsgType>' +
                '<Event><![CDATA[' + eventName + ']]></Event>' +
                '<EventKey><![CDATA[' + eventKey + ']]></EventKey>' +
                '</xml>';
            $.ajax({
                type: 'POST',
                cache: false,
                contentType: 'raw',
                url: url,
                data: xml,
                complete: function(jqXHR, textStatus) {
                    var timeDiff = (Date.now() - time) + ' ms';
                    $("#event .response").removeClass('prettyprinted');
                    $("#event .status").text(jqXHR.status + ' ' + jqXHR.statusText);
                    $("#event .response").text(vkbeautify.xml(jqXHR.responseText));
                    $("#event .time").text(timeDiff);
                    prettyPrint();
                }
            });
        });
        $('#event button[name="clear"]').click(function() {
            $('#eventKey').val('');
            $('#event .time').text('N/A');
            $('#event .status').text('N/A');
            $('#event .response').text('');
        });
    </script>
    <!-- log -->
    <div role="tabpanel" class="tab-pane fade" id="log">

    </div>
</div>

<style>
    .receive-panel {
        margin: 15px 0;
        border-top: 1px solid #ddd;
        padding: 15px 0;
    }
    span.status {
        margin-right: 15px;
    }
    .monospace {
        font-family: Menlo, Courier, 'Liberation Mono', Consolas, Monaco, Lucida Console, monospace;
        white-space: pre-wrap;

    }
    pre.response {
        margin: 15px 0;
    }
    textarea.monospace {
        font-size: 13px;
    }
</style>
<script src="../includes/js/vkbeautify.0.99.00.beta.js"></script>
<script src="../includes/plugins/google-code-prettify/prettify.js"></script>
<link rel="stylesheet" href="../includes/plugins/google-code-prettify/prettify.css" media="all">
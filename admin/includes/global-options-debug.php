<?php
    global $wxdb; /* @var $wxdb wxdb */
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
                    <input class="form-control" type="text" name="postUrl-1" id="postUrl-1" value="http://<?php echo $_SERVER['SERVER_NAME'] ?>:<?php echo $_SERVER['SERVER_PORT']?>/index.php" required>
                </div>
            </div>
            <div class="form-group">
                <div class="prompt">
                    <label for="postRawData">POST 数据 (raw)</label><br>
                    <button class="button xs-button gray-button" name="sample">加载示例数据</button>
                </div>
                <div class="control">
                    <textarea class="form-control monospace" name="postRawData" id="postRawData" rows="16"></textarea>
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
            <p class="no-response" style="display: none">得不到任何响应，请检查服务器是否启动或地址是否正确。</p>
            <pre class="response prettyprint lang-xml linenums monospace"></pre>
        </div>
        <script>
            $('#manual button[name="sample"]').click(function() {
                var sampleData = '<xml>' +
                    '<ToUserName><![CDATA[toUser]]></ToUserName>' +
                    '<FromUserName><![CDATA[fromUser]]></FromUserName>' +
                    '<CreateTime>1348831860</CreateTime>' +
                    '<MsgType><![CDATA[text]]></MsgType>' +
                    '<Content><![CDATA[你好]]></Content>' +
                    '<MsgId>1234567890123456</MsgId>' +
                    '<InitiateMethod><![CDATA[debug]]></InitiateMethod>' +
                    '</xml>';
                $('#postRawData').val(vkbeautify.xml(sampleData));
            });

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
                    complete: function(jqXHR) {
                        if (jqXHR.status == '0') {
                            $('#manual p.no-response').show();
                            $("#manual .response").text(null);
                        } else {
                            $('#manual p.no-response').hide();
                        }
                        var timeDiff = (Date.now() - time) + ' ms';
                        $("#manual .response").removeClass('prettyprinted');
                        $("#manual .status").text(jqXHR.status + ' ' + jqXHR.statusText);
                        $("#manual .response").text(vkbeautify.xml(jqXHR.responseText));
                        $("#manual .time").text(timeDiff);
                        prettyPrint();
                    },
                    error: function() {
                        $("#manual .status").addClass('label red-label');
                    },
                    success: function() {
                        $("#manual .status").removeClass('label red-label');
                    }
                });
            });
            $('#manual button[name="clear"]').click(function() {
                $('#postRawData').val('');
                $('#manual .time').text('N/A');
                $('#manual .status').text('N/A');
                $('#manual .response').text('');
                $("#manual .status").removeClass('label red-label');
            });
        </script>
    </div>

    <!-- message -->
    <div role="tabpanel" class="tab-pane fade" id="message">
        <div class="send-panel">
            <div class="form-group">
                <div class="prompt">
                    <label for="postUrl-2">入口地址</label>
                </div>
                <div class="control">
                    <input class="form-control" type="text" name="postUrl-2" id="postUrl-2" value="http://<?php echo $_SERVER['SERVER_NAME'] ?>:<?php echo $_SERVER['SERVER_PORT']?>/index.php" required>
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
                    '<InitiateMethod><![CDATA[debug]]></InitiateMethod>' +
                    '<MsgId>1234567890123456</MsgId>' +
                    '</xml>';
                $.ajax({
                    type: 'POST',
                    cache: false,
                    contentType: 'raw',
                    url: url,
                    data: xml,
                    complete: function(jqXHR) {
                        if (jqXHR.status == '0') {
                            $('#message p.no-response').show();
                            $("#message .response").text(null);
                        } else {
                            $('#message p.no-response').hide();
                        }
                        var timeDiff = (Date.now() - time) + ' ms';
                        $("#message .response").removeClass('prettyprinted');
                        $("#message .status").text(jqXHR.status + ' ' + jqXHR.statusText);
                        $("#message .response").text(vkbeautify.xml(jqXHR.responseText));
                        $("#message .time").text(timeDiff);
                        prettyPrint();
                    },
                    error: function() {
                        $("#message .status").addClass('label red-label');
                    },
                    success: function() {
                        $("#message .status").removeClass('label red-label');
                    }
                });
            });
            $('#message button[name="clear"]').click(function() {
                $('#textMessage').val('');
                $("#message .status").removeClass('label red-label');
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
                    <input class="form-control" type="text" name="postUrl-3" id="postUrl-3" value="http://<?php echo $_SERVER['SERVER_NAME'] ?>:<?php echo $_SERVER['SERVER_PORT']?>/index.php" required>
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
                    '<InitiateMethod><![CDATA[debug]]></InitiateMethod>' +
                    '</xml>';
                $.ajax({
                    type: 'POST',
                    cache: false,
                    contentType: 'raw',
                    url: url,
                    data: xml,
                    complete: function(jqXHR) {
                        if (jqXHR.status == '0') {
                            $('#event p.no-response').show();
                            $("#event .response").text(null);
                        } else {
                            $('#event p.no-response').hide();
                        }
                        var timeDiff = (Date.now() - time) + ' ms';
                        $("#event .response").removeClass('prettyprinted');
                        $("#event .status").text(jqXHR.status + ' ' + jqXHR.statusText);
                        $("#event .response").text(vkbeautify.xml(jqXHR.responseText));
                        $("#event .time").text(timeDiff);
                        prettyPrint();
                    },
                    error: function() {
                        $("#event .status").addClass('label red-label');
                    },
                    success: function() {
                        $("#event .status").removeClass('label red-label');
                    }
                });
            });
            $('#event button[name="clear"]').click(function() {
                $('#eventKey').val('');
                $("#event .status").removeClass('label red-label');
                $('#event .time').text('N/A');
                $('#event .status').text('N/A');
                $('#event .response').text('');
            });
        </script>
    </div>

    <!-- log -->
    <?php
        function add_row($key, $value) {
            echo '<tr><td>' . $key . '</td><td>' . $value . '</td></tr>';
        }
    ?>
    <div role="tabpanel" class="tab-pane fade" id="log">
        <table class="table">
            <tr><td style="width: 200px">参数</td><td>值</td></tr>
            <?php
            global $modules;
            $sql = 'SELECT table_schema "table", Round(Sum(data_length + index_length) / 1024, 1) "size" FROM information_schema.tables GROUP BY table_schema';
            $size = $wxdb->get_results($sql, OBJECT_K);

            add_row('Apache 版本', apache_get_version());
            add_row('PHP 版本', phpversion());
            add_row('PHP 内存使用', round(memory_get_usage(true)/1048576, 2) . 'M');
            add_row('PHP 内存限制', ini_get('memory_limit'));
            add_row('POST 大小限制', ini_get('post_max_size'));
            add_row('最大上传限制', ini_get('upload_max_filesize'));
            add_row('最大执行时间限制', ini_get('max_execution_time') . 's');
            add_row('PHP 模块', implode(', ', get_loaded_extensions()));
            add_row('默认时区', date_default_timezone_get());
            add_row('Error Log', ini_get('error_log'));
            add_row('Error Reporting Level', ini_get('error_reporting'));
            add_row('Display Errors', ini_get('display_errors'));
            add_row('加载的模块', count($modules));
            add_row('ABSPATH', ABSPATH);
            add_row('Module 目录状态', is_writable(ABSPATH . 'modules') ? '可写' : '不可写');
            add_row('MySQL 数据库大小', $size['weixin']->size . "KB");
            ?>
        </table>
    </div>
</div>

<style>
    .tab-content {
        margin-top: 15px;
    }
    .receive-panel {
        margin: 15px 0;
        border-top: 1px solid #ddd;
        padding: 15px 0;
    }
    span.status {
        margin-right: 15px;
    }
    .monospace {
        font-family: Menlo, Courier, 'Liberation Mono', Consolas, Monaco, 'Lucida Console', monospace;
        white-space: pre-wrap;

    }
    pre.response {
        margin: 15px 0;
    }
    textarea.monospace {
        font-size: 13px;
    }
    p.no-response {
        margin-top: 15px;
    }
</style>
<script src="../includes/js/vkbeautify.0.99.00.beta.js"></script>
<script src="../includes/plugins/google-code-prettify/prettify.js"></script>
<link rel="stylesheet" href="../includes/plugins/google-code-prettify/prettify.css" media="all">
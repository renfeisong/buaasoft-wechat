<h1>欢迎访问软件学院微信后台管理系统 <small style="color:#666;font-size: 70%;">2.0.0-rc1 (rev#213)</small></h1>
<p style="line-height: 1.8">
    当前登陆用户：<?php echo current_user_name() ?><br>
    系统状态：<span style="color: #1b926c"><i class="fa fa-check-circle"></i> 良好</span><br>
    已加载的模块数：<?php global $modules; echo count($modules); ?>
</p>
<p>
    您可以<a href="https://github.com/renfeisong/buaasoft-wechat/issues">报告问题或提出建议</a>
</p>

<div style="font-size: 12px;color:#999;"><?php echo date('r') ?></div>
<?php
/**
 * InputType Class
 *
 * @author Renfei Song
 * @since 2.0.0
 */

abstract class InputType {
    const Text = 0; // 文本
    const Image = 1; // 图片
    const Voice = 2; // 语音
    const Video = 3; // 视频
    const Location = 4; // 地理位置
    const Link = 5; // 链接
    const Click = 6; //（自定义菜单）click 类型按钮
    const Subscribe = 7; // 订阅
    const Unsubscribe = 8; // 取消订阅
    const Scan = 9; // 带参数二维码扫描
    const LocationReport = 10; // 地理位置自动上报
    const View = 11; //（自定义菜单）view 类型点击

    // 以下还没有在 UserInput / MessageReceiver 中实现
    const ScanCodePush = 12; //（自定义菜单）扫一扫
    const ScanCodeWaitMsg = 13; //（自定义菜单）扫一扫消息接收中
    const PicSysPhoto = 14; //（自定义菜单）完成拍照
    const PicPhotoOrAlbum = 15; //（自定义菜单）完成拍照或选择照片
    const PicWeixin = 16; //（自定义菜单）完成微信相册选图
    const LocationSelect = 17; //（自定义菜单）完成地理位置选择

    const Unsupported = 0xDEADBEEF;
}
<?php
//namespace Qcloud_cos;

class Conf
{
    const PKG_VERSION = '1.0.0'; 

    const API_IMAGE_END_POINT = 'http://web.image.myqcloud.com/photos/v1/';
    const API_VIDEO_END_POINT = 'http://web.video.myqcloud.com/videos/v1/';
    const API_COSAPI_END_POINT = 'http://web.file.myqcloud.com/files/v1/';
    //请到http://console.qcloud.com/cos去获取你的appid、sid、skey
    const APPID = '10014575';
    const SECRET_ID = 'AKIDoamMaUSC1srKaiQ9VhALpfC6QZoRI3Xc';
    const SECRET_KEY = 'c0h1h9pO9htXYkNdqJbCqiumzemZgtI8';


    public static function getUA() {
        return 'QcloudPHP/'.self::PKG_VERSION.' ('.php_uname().')';
    }
}


//end of script

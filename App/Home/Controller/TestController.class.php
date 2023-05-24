<?php

namespace Home\Controller;

class TestController extends CommonController{

    public function index(){
        session('uid','3');
        session('uId','2');
        dump(session('uid'));
        dump(session('uId'));
    }


    public function generateQrcode($qr,$size=8,$format='json'){
        $ch = curl_init();
        $size = 8;
        $format = 'json';
        $qr = 'http://www.baidu.com';
        $url = 'http://apis.baidu.com/3023/qr/qrcode?format='.$format.'&size='.$size.'&qr='.$qr;
        $header = array(
            'apikey: ab2591fb2ed86718f0556035a04ebf5c',
        );
        // 添加apikey到header
        curl_setopt($ch, CURLOPT_HTTPHEADER  , $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // 执行HTTP请求
        curl_setopt($ch , CURLOPT_URL , $url);
        $res = curl_exec($ch);

        $result = json_decode($res);

        return $result->url;

    }

    public function test(){
        dump(APP_PATH.'Home/Conf/config.php');
        $test = require APP_PATH.'Home/Conf/config.php';
        dump();

        dump($test);

        $info = $this->generateQrcode('http://www.baidu.com');
        echo $info;

        $this->show("<img src='".$info."' style='width:50px;height:50px;'/>");
    }

    public function sendMessage(){
        $ch = curl_init();
        $content = urlencode('测试短信内容');
        $mobile = '15044025583';
        $url = 'http://apis.baidu.com/sms_net/sms_net_sdk/sms_net_sdk?content='.$content.'&mobile='.$mobile;
        $header = array(
            'apikey: ab2591fb2ed86718f0556035a04ebf5c',
        );
        // 添加apikey到header
        curl_setopt($ch, CURLOPT_HTTPHEADER  , $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // 执行HTTP请求
        curl_setopt($ch , CURLOPT_URL , $url);
        $res = curl_exec($ch);

        var_dump(json_decode($res));
    }

    public function testAjax(){
        $this->display('index');

    }
    
    public function ajaxReturn(){
         dump(I('post.'));
    }
}
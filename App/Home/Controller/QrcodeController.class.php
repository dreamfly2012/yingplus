<?php

namespace Home\Controller;

use Think\Exception;

class QrcodeController extends CommonController
{
    public function generateQrcode($qr = 'http://www.yingplus.cc/', $size = 8, $format = 'json')
    {
        $ch = curl_init();
        $url = 'http://apis.baidu.com/3023/qr/qrcode?format=' . $format . '&size=' . $size . '&qr=' . $qr;

        $header = array(
            'apikey: ' . C('apistore_baidu_key'),
        );
        // 添加apikey到header
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // 执行HTTP请求
        curl_setopt($ch, CURLOPT_URL, $url);
        $res = curl_exec($ch);

        $result = json_decode($res);
        //echo $url;

        return $result->url;

    }

    public function showQrcode()
    {
        $url = I('request.url', null, 'urldecode');
        Vendor('phpqrcode.phpqrcode');
        \QRcode::png($url);
    }

    //通过附件表,查找附件路径,获取二维码文本值
    public function decodeQrcode()
    {
        Vendor('readerqrcode.lib.QrReader');
        $attachmentid = I('request.attachmentid', null, 'intval'); //'./uploads/showQrcode.png'
        $attachmentModel = D('Attachment');
        $path = $attachmentModel->getFieldById($attachmentid, 'path');
        $qrcode = new \QrReader($path);
        $info = @$qrcode->text();
        if ($info == false) {
            $code = 1;
            $info = '二维码解析错误';
            $message = '二维码图片格式不正确';
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
        $code = 0;
        $message = '扫描的二维码值';
        $return = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);

    }
}
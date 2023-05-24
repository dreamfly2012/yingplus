<?php

namespace Home\Controller;

use Think\Controller;
use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;

class CaptchaController extends Controller
{
    //构建ajax返回数据
    public function buildReturn($info, $code, $message)
    {
        return array(
            'data' => array(
                'code' => $code,
                'message' => $message,
                'info' => $info,
            ),
        );
    }

    //发送手机验证码
    public function send()
    {
        $telephone = I('request.telephone', null);
        $rand = rand(C('PHONE_MIN'), C('PHONE_MAX'));
        //参数错误
        if (empty($telephone)) {
            $info = null;
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        if (strlen($telephone) != 11) {
            $info = null;
            $code = 1;
            $message = '手机号不正确';
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $captchaBlock = D('CaptchaBlock');

        if (APP_DEBUG) {
            session('captcha', 123456);
        } else {
            session('captcha', $rand); //将产生的随机数设置到session中
        }

        //$content = C('PHONE_CAPTCHA_MESSAGE_PREFIX').$rand.C('PHONE_CAPTCHA_MESSAGE_POSTFIX'); //发送短信的内容
        //$content = iconv("GB2312", "UTF-8", $content);
        $ip = get_client_ip(); //得到客户端的IP
        $date = date('Y-m-d'); //记录当天时间
        $ip_overflow = $captchaBlock->checkMessageCountByIp($ip, $date);
        $phone_overflow = $captchaBlock->checkMessageCountByTelephone($telephone, $date);
        if ($ip_overflow || $phone_overflow) {
            $info = null;
            $code = 2;
            $message = '操作过于频繁,明天再试~';
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        if (APP_DEBUG) {
            $this->SendSMS_debug();
            //表示发送成功
            $info = null;
            $code = 0;
            $message = '验证码发送成功';
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        } else {
            if ($this->SendSMS($telephone, $rand)) {
                //表示发送成功
                $info = null;
                $code = 0;
                $message = '验证码发送成功';
                $return = $this->buildReturn($info, $code, $message);
                $this->ajaxReturn($return);
            } else {
                //表示发送失败
                $info = null;
                $code = 1;
                $message = '验证码发送失败';
                $return = $this->buildReturn($info, $code, $message);
                $this->ajaxReturn($return);
            }
        }
    }

    //验证手机验证码
    public function check()
    {
        $telephone = I('request.telephone', null);
        $captcha = I('request.captcha', null);
        //参数错误
        if (empty($telephone) || empty($captcha)) {
            $info = null;
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $session_captcha = session('captcha');
        if ($session_captcha == $captcha) {
            $info = null;
            $code = 0;
            $message = '验证码正确';
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        } else {
            $info = null;
            $code = 1;
            $message = '验证码错误';
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
    }

    //生成随机验证码
    public function captchaCode()
    {
        $config = array(
            'imageH' => 30, // 验证码图片高度
            'imageW' => 120, // 验证码图片宽度
            'codeSet' => '0123456789',
            'fontSize' => 14, // 验证码字体大小(px)
            'useImgBg' => false, // 使用背景图片
            'useCurve' => true, // 是否画混淆曲线
            'useNoise' => false, // 是否添加杂点
            'length' => 4, // 验证码位数
            'fontttf' => '2.ttf', // 验证码字体，不设置随机获取
        );
        $Verify = new \Think\Verify($config);
        $Verify->entry();
    }

    //检查图片验证码是否正确
    public function checkVerify($code, $id = '')
    {
        $verify = new \Think\Verify();

        return $verify->check($code, $id);
    }

    /**
     * Name:SendSMS
     * Describe:发送验证码
     * Return:true或false.
     */
    public function SendSMS($Phone, $code)
    {
        AlibabaCloud::accessKeyClient('8DlyZXhKBJ5k7pQr', 'xzHgrU76JeHd5KBFjW4hTj1FJAU2G7')
                ->regionId('cn-hangzhou') // replace regionId as you need
                ->asGlobalClient();

        try {
            $result = AlibabaCloud::rpcRequest()
                ->product('Dysmsapi')
                ->version('2017-05-25')
                ->action('SendSms')
                ->method('POST')
                ->options([
                                'query' => [
                                    'TemplateCode' => 'SMS_156282252',
                                    'SignName' => '梦回故里',
                                    'PhoneNumbers' => $Phone,
                                    'TemplateParam' => '{"code":"'.$code.'"}',
                                ],
                            ])
                ->request();
        } catch (ClientException $e) {
            echo $e->getErrorMessage().PHP_EOL;
        } catch (ServerException $e) {
            echo $e->getErrorMessage().PHP_EOL;
        }
        $result = $result->toArray();

        if ($result['Code'] == 'OK') {
            return true;
        } else {
            return false;
        }
    }

    public function SendSMS_debug()
    {
        return true;
    }
}

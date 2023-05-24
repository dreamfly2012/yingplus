<?php
namespace Home\Controller;
use Think\Controller;
class CaptchacodeController extends CommonController 
{
    public function send()
    {
    	$telephone = I('request.telephone',null);
        $type = I('request.type','register');
    	
    	
    	if (empty($telephone)) {
    		$info    = null;
	        $code    = -1;
	        $message = '手机号不能为空';
	        $return  = $this->buildReturn($info, $code, $message);
	        $this->ajaxReturn($return);
    	}

        

        if(!preg_match("/^1[34578]{1}\d{9}$/",$telephone)){  
            $info    = null;
            $code    = -4;
            $message = '手机号格式不正确';
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        } 

        

        $captchaModel = D('Captcha');
        $captcha = rand(111111,999999);
        $captcha = 111111;
        $info = $captchaModel->add(array('telephone'=>$telephone,'code'=>$captcha,'type'=>$type));

        if (empty($info)) {
            $info    = null;
            $code    = -6;
            $message = '验证码不正确';
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
        
    	
		
        $info['captcha'] = $captcha;
        $code    = 0;
        $message = '验证码发送成功';
        $return  = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    		
    	
    }

    
}
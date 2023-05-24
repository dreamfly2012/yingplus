<?php
namespace Home\Controller;

use Think\Controller;

class LoginController extends CommonController
{
    public function login()
    {
        $telephone   = I('request.telephone', null);
        $password    = I('request.password', null, 'md5');
        $return_path = I('request.return_path', 'index');

        if (empty($telephone) || empty($password)) {
            $info    = null;
            $code    = -1;
            $message = '用户名密码不能为空';
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        } else {
            $userModel              = D('User');
            $condition['telephone'] = $telephone;
            $condition['password']  = $password;

            $info = $userModel->where($condition)->find();
            if ($info) {
                $usertokenModel = M('UserToken');
                $token_info     = $usertokenModel->where(array('uid' => $info['id']))->find();
                if (empty($token_info)) {
                    $token = md5($telephone . strval(TIMESTAMP) . strval(rand(0, 999999)));
                    $usertokenModel->add(array('uid' => $info['id'], 'token' => $token));
                } else {
                    $token = $token_info['token'];
                }
                unset($info['password']);
                $info['token'] = $token;
                $code          = 0;
                $message       = '登录成功';
                $return        = $this->buildReturn($info, $code, $message);
                $this->ajaxReturn($return);
            } else {
                $info    = null;
                $code    = -2;
                $message = '用户名密码错误';
                $return  = $this->buildReturn($info, $code, $message);
                $this->ajaxReturn($return);
            }
        }
    }

    public function forgetpwd()
    {
        $telephone = I('request.telephone', null);
        $captcha   = I('request.captcha', null);
        $password  = I('request.password', null);

        if (empty($telephone)) {
            $info    = null;
            $code    = -1;
            $message = '手机号不能为空';
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        if (empty($captcha)) {
            $info    = null;
            $code    = -2;
            $message = '验证码不能为空';
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        if (empty($password)) {
            $info    = null;
            $code    = -3;
            $message = '密码不能为空';
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        if (!preg_match("/^1[34578]{1}\d{9}$/", $telephone)) {
            $info    = null;
            $code    = -4;
            $message = '手机号格式不正确';
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $match = '/^[\\~!@#$%^&*()-_=+|{}\[\],.?\/:;\'\"\d\w]{6,18}$/';

        if (!preg_match($match, $password)) {
            $info    = null;
            $code    = -5;
            $message = '密码长度6-18位,不要包含特殊字符';
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $captchaModel = D('Captcha');
        $info         = $captchaModel->where(array('telephone' => $telephone, 'code' => $captcha, 'type' => 'forgetpwd'))->find();

        if (empty($info)) {
            $info    = null;
            $code    = -6;
            $message = '验证码不正确';
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $userModel              = D('User');
        $condition['telephone'] = $telephone;
        $info                   = $userModel->where($condition)->find();
        if (empty($info)) {
            $info    = null;
            $code    = -7;
            $message = '该手机号没有注册，请注册';
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);

        }
        $save_data['telephone'] = $telephone;
        $save_data['password']  = md5($password);
        //$save_data['nickname'] = md5(uniqid());
        $userModel->where(array('telephone' => $telephone))->save($save_data);

        $usertokenModel = D('UserToken');

        $token = md5($telephone . strval(TIMESTAMP) . strval(rand(0, 999999)));
        $usertokenModel->where(array('uid' => $info['id']))->save(array('token' => $token));

        unset($info['password']);
        $info['token'] = $token;
        $code          = 0;
        $message       = '找回密码成功';
        $return        = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }
}

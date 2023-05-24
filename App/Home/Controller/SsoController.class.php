<?php

namespace Home\Controller;

use Think\Controller;

class SsoController extends Controller
{
    //SSO 单点登录
    //分布登录信息
    public function login($uid, $password)
    {
        $uid_info = md5(md5($password) . 'yingplus');
        $this->assign('uid', $uid);
        $this->assign('uid_info', $uid_info);
        $this->display('pc/sso_login');
    }

    //分布登出信息
    public function logout()
    {
        $this->display('pc/sso_logout');
    }

    //接收同步登录
    public function acceptlogin()
    {
        $uid      = I('request.uid', null, 'intval');
        $uid_info = I('request.uid_info', null);
        if (!empty($uid)) {
            $UserModel           = D('User');
            $password            = $UserModel->getFieldById($uid, 'password');
            $encryption_password = md5(md5($password) . 'yingplus');
            if ($encryption_password == $uid_info) {
                session('uid', $uid);
            }
        }
    }

    //接收同步退出
    public function acceptlogout()
    {
        session('uid', null);
        cookie('uid', null);
        cookie('uid_info', null);

    }
}

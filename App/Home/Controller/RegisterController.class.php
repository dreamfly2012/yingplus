<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/8/13
 * Time: 9:32
 */

namespace Home\Controller;

class RegisterController extends CommonController
{

    //从系统得到一个唯一昵称
    public function getNickNameFromSystem()
    {
        $NickName = D('Nickname');
        $nickname = $NickName->getNickName();
        return $nickname;
    }

    //验证手机号是否存在，验证这个手机号是否被注册
    public function checkPhoneExist()
    {
        $telephone = I('request.telephone', null, 'intval');
        if(empty($telephone)){
            $info = null;
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->ajaxReturn($info,$code,$message);
            $this->ajaxReturn($return);
        }
        $userModel      = D('User');
        $result    = $userModel->where(array('telephone'=>$telephone))->find();
        if (empty($result)) {
            $info = false;
            $code = 0;
            $message = '电话号码不存在';
            $result = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $info = true;
        $code = 0;
        $message = '电话号码已存在';
        $result = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    //注册
    public function register()
    {
        $telephone = I('request.telephone',null);
        $password = I('request.password',null);
        $captcha = I('captcha', null, '');
        $session_captcha = session('captcha');
        //参数错误
        if(empty($telephone)||empty($password)||empty($captcha)){
            $info = null;
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info,$code,$message);
            $this->ajaxReturn($return);
        }

        //验证码错误
        if ($captcha != $session_captcha) {
            $info = null;
            $code = -2;
            $message = C('REGISTER_CAPTCHA_ERROR');
            $return = $this->buildReturn($info,$code,$message);
            $this->ajaxReturn($return);
        }

        //用户已经存在
        $User = D('User');
        if($User->checkExistPhone($telephone)){
            $info = null;
            $code = -3;
            $message = C('TELEPHONE_EXIST');
            $return = $this->buildReturn($info,$code,$message);
            $this->ajaxReturn($return);
        }

        $UserProfile = D('UserProfile');
        $User->regtime = time();
        $User->regip = get_client_ip(0,true);
        $User->lastlogintime = time();
        $User->lastloginip = get_client_ip(0,true);
        $User->telephone = $telephone;
        //在这需要分配一个昵称
        $User->nickname = $this->getNickNameFromSystem();
        $id = $User->add();
        $password = $User->getFieldById($id,'password');
        
        //说明用户注册成功，将用户字段进行填充
        $userBehavior = D('UserBehavior');
        $userprofile = array('uid'=>$id);
        $UserProfile->add($userprofile);
        $userBehavior->add(array('uid',$id));
        $this->shareRegisterDo();
        session('uid',$id);
        $this->encryptionUserInfo($id,$password);
        $this->AsyncUserInfo($id);

        $info = null;
        $code = 0;
        $message = C('REGISTER_SUCCESS');
        $return  = $this->buildReturn($info,$code,$message);
        $this->ajaxReturn($return);
    }
}
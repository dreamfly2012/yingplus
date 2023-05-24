<?php

namespace Home\Controller;

use Think\Controller;

class CommonController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        header('Access-Control-Allow-Origin:*');
        $input = $_REQUEST;
        $data = "http://".$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"].PHP_EOL;
        $data .= var_export($input, true).PHP_EOL;
        file_put_contents(APP_PATH.'access.log', $data, FILE_APPEND);
    }

    public function _empty()
    {
        $info    = null;
        $code    = -1;
        $message = '非法访问';
        $return  = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    public function getUid()
    {
        //获取sign
        $sign = I('request.sign');
        $userinfo = base64_decode($sign);
        $userinfo_arr  = explode("|", $userinfo);//telephone|password
        $uid = $this->getUserInfoByTelephonePassword($userinfo_arr['telephone'], $userinfo_arr['password']);
        return $uid;
    }

    public function get_uid_by_token($token)
    {
        $usertokenModel = M('UserToken');
        $tokeninfo = $usertokenModel->where(array('token'=>$token))->find();
        $uid = $tokeninfo['uid'];
        return $uid;
    }

    public function getUserInfoByTelephonePassword($telephone, $password)
    {
        $userModel = D('User');
        $condition['telephone'] = $telephone;
        $condition['password'] = $password;

        $info = $userModel->where($condition)->find();
        return $info['id'];
    }

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
}

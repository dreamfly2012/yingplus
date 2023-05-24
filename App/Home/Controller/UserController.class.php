<?php
/**
 * Created by PhpStorm.
 * User: roak
 * Date: 2015/8/11
 * Time: 15:47
 */
namespace Home\Controller;

class UserController extends CommonController
{
    public function index()
    {
        $uid              = session('uid');
        $User             = D('User');
        $userInfo         = $User->getUserInfoById($uid);
        $UserProfile      = D('UserProfile');
        $userInfo_Profile = $UserProfile->getUserInfoByUid($uid);
        $userAllInfo      = array_merge($userInfo, $userInfo_Profile);
        $this->assign('userAllInfo', $userAllInfo);
        $this->display();
    }

    //获取用户基本profile信息
    public function get()
    {
        $type = I('request.type', null);
        switch ($type) {
            case 'profile':
                $this->getprofile();
                break;
            case 'detail':
                $this->detail();
                break;
            default:
                break;
        }
    }

    //设置用户基本信息
    public function set()
    {
        if (!$this->checkLogin()) {
            $info    = null;
            $code    = 2;
            $message = C('no_login');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $type = I('request.type', null);
        switch ($type) {
            case 'profile':
                $this->setprofile();
                break;
            case 'detail':
                $this->detail();
                break;
            default:
                break;
        }
    }

    //获取用户关注星吧

    //获取用户profile
    public function getprofile()
    {
        //没有登录授权
        if (!$this->checkLogin()) {
            $info    = null;
            $code    = 2;
            $message = C('no_login');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        //昵称,真实姓名
        $userProfileModel = D('UserProfile');
        $uid              = $this->getUid();
        $realname         = $userProfileModel->getFieldByUid($uid, 'realname');
        $data['realname'] = $realname;

        //性别
        $gender         = $userProfileModel->getFieldByUid($uid, 'gender');
        $data['gender'] = $gender;

        //地址
        $current_province         = $userProfileModel->getFieldByUid($uid, 'birthprovince');
        $current_city             = $userProfileModel->getFieldByUid($uid, 'birthcity');
        $address                  = $userProfileModel->getFieldByUid($uid, 'address');
        $data['current_province'] = $current_province;
        $data['current_city']     = $current_city;
        $data['address']          = $address;
        $districtModel            = D('District');
        $provinces                = $districtModel->getAllProvince(0);
        $data['provinces']        = $provinces;

        $default_province = empty($current_province) ? 1 : $current_province;
        $cities           = $districtModel->getCityByProvince($default_province);
        $data['cities']   = $cities;

        //生日设置
        $current_year  = $userProfileModel->getFieldByUid($uid, 'birthyear');
        $current_month = $userProfileModel->getFieldByUid($uid, 'birthmonth');
        $current_day   = $userProfileModel->getFieldByUid($uid, 'birthday');
        $this->assign('current_year', $current_year);
        $this->assign('current_month', $current_month);
        $this->assign('current_day', $current_day);

        $year  = array();
        $month = array();
        $day   = array();
        $now_y = date('Y');
        for ($i = 1900; $i <= $now_y; $i++) {
            $year[] = $i;
        }
        for ($i = 1; $i <= 12; $i++) {
            $month[] = $i;
        }
        for ($i = 1; $i <= 31; $i++) {
            $day[] = $i;
        }

        $this->assign('year', $year);
        $this->assign('month', $month);
        $this->assign('day', $day);

        //个人介绍
        $selfdesc         = $userProfileModel->getFieldByUid($uid, 'selfdesc');
        $data['selfdesc'] = $selfdesc;
        $info             = $data;
        $code             = 1;
        $message          = '用户概况';
        $return           = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    //保存用户profile
    public function setprofile()
    {
        $data     = I('post.');
        $result   = 'true';
        $nickname = I('post.nickname', null);
        $uid      = $this->getUid();

        if (!empty($nickname)) {
            $userModel = D('User');
            $count     = $userModel->where(array('id' => array('neq' => $uid), 'nickname' => $nickname))->count();
            //用户昵称已存在
            if ($count > 0) {
                $info    = null;
                $code    = 2;
                $message = C('NICKNAME_EXIST');
                $return  = $this->buildReturn($info, $code, $message);
                $this->ajaxReturn($return);
            }

            //语句写法问题，解决unique无法保存
            $result = $userModel->execute("update __PREFIX__user set nickname = '" . $nickname . "' where `id` = " . $uid);

            if ($result != -1) {
                $point = R('Point/createPersonMessage', array(1));
                if ($point) {
                    R('Point/addUserTotalPoint', array($point));
                }
                $info    = null;
                $code    = 0;
                $message = C('SETTING_UPDATE_SUCCESS');
                $return  = $this->buildReturn($info, $code, $message);
                $this->ajaxReturn($return);
            } else {
                $info    = null;
                $code    = 0;
                $message = C('SETTING_UPDATE_FAILED');
                $return  = $this->buildReturn($info, $code, $message);
                $this->ajaxReturn($return);
            }
        }

        $userProfileModel = D('UserProfile');

        //$allow = $userProfileModel->getDbFields();
        $allow = array('realname', 'gender', 'selfdesc', 'birthprovince', 'birthcity', 'birthdist', 'birthyear', 'birthmonth', 'birthday', 'address');
        //保存用户信息，在数据库的字段就进行更新
        foreach ($data as $key => $val) {
            if (in_array($key, $allow)) {
                if ($key == 'realname') {
                    if (preg_match('/\d+/', $val) || empty($allow)) {
                        $info    = null;
                        $code    = 3;
                        $message = C('REALNAME_IS_INVALID');
                        $return  = $this->buildReturn($info, $code, $message);
                        $this->ajaxReturn($return);
                    }
                }
                if ($key == 'selfdesc' && $val == "") {
                    $info    = null;
                    $code    = 4;
                    $message = C('CAN_NOT_EMPTY');
                    $return  = $this->buildReturn($info, $code, $message);
                    $this->ajaxReturn($return);
                }
                $point = $this->selectWord($key);
                if ($point) {
                    R('Point/addUserTotalPoint', array($point));
                }
                $userProfileModel->where(array('uid' => $uid))->setField(array($key => $val));
            }

        }

        //昵称不能为空
        if (!is_null($nickname) && $nickname == "") {
            $info    = null;
            $code    = 3;
            $message = C('CAN_NOT_EMPTY');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $info    = null;
        $code    = 3;
        $message = C('SETTING_UPDATE_SUCCESS');
        $return  = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    //设置积分
    public function selectWord($data)
    {
        switch ($data) {
            case 'realname':
                $point = R('Point/createPersonMessage', array(2));
                return $point;
                break;
            case 'gender':
                $point = R('Point/createPersonMessage', array(3));
                return $point;
                break;
            case 'selfdesc':
                $point = R('Point/createPersonMessage', array(6));
                return $point;
                break;
            case 'birthcity':
                $point = R('Point/createPersonMessage', array(4));
                return $point;
                break;
            case 'birthday':
                $point = R('Point/createPersonMessage', array(5));
                return $point;
                break;

        }
    }

    //设置密码
    public function updatepassword()
    {
        $password = I('request.password', null);
        $uid      = session('uid');

        if (empty($uid)) {
            $info    = null;
            $code    = 2;
            $message = C('no_login');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        if (strlen($password) < 6) {
            $info    = null;
            $code    = 1;
            $message = '密码长度过短';
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $userModel       = D('User');
        $condition['id'] = $uid;
        $password     = md5($password);
        $userModel->where($condition)->setField(array('password' => $password));
        $info    = 'success';
        $code    = 0;
        $message = '修改密码成功';
        $return  = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    //更新用户昵称
    public function updatenickname()
    {
        $uid = $this->getUid();
        $nickname                     = I('request.nickname', null, '');
        if(empty($uid)){
            $info = null;
            $code = 2;
            $message = C('no_login');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        if(strlen($nickname)<2||strlen($nickname)>18){
            $info = null;
            $code = 1;
            $message = '昵称过短或过长';
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $userModel = D('User');
        $condition['id'] = $uid;
        $userModel->where($condition)->setField(array('nickname'=>$nickname));
        $info = null;
        $code = 0;
        $message = '修改昵称成功';
        $return = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    //更新电话号码
    public function updatetelepone()
    {
        $uid = $this->getUid();
        $telephone                     = I('request.telephone', null, '');
        if(empty($uid)){
            $info = null;
            $code = 2;
            $message = C('no_login');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        if(strlen($telephone)!=11){
            $info = null;
            $code = 1;
            $message = '电话号码不正确';
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $userModel = D('User');
        $bool = $userModel->where(array('telephone'=>$telephone))->find();
        if($bool){
            $info = null;
            $code = 1;
            $message = '电话号码已经存在';
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        
        $condition['id'] = $uid;
        $userModel->where($condition)->setField(array('telephone'=>$telephone));
        $info = null;
        $code = 0;
        $message = '修改电话号码成功';
        $return = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }
    

    //更新用户真实姓名
    public function updaterealname()
    {
        $uid = $this->getUid();
        $realname                     = I('request.realname', null, '');
        if(empty($uid)){
            $info = null;
            $code = 2;
            $message = C('no_login');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        if(strlen($realname)<2||strlen($realname)>18){
            $info = null;
            $code = 1;
            $message = '用户名过短或过长';
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $userProfileModel = D('UserProfile');
        $condition['uid'] = $uid;
        $userProfileModel->where($condition)->setField(array('realname'=>$realname));
        $info = null;
        $code = 0;
        $message = '修改姓名成功';
        $return = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }
    //检查用户名是否存在
    public function checkNickNameExist($nickName)
    {
        $user   = D('User');
        $result = $user->getUserByNickName($nickName);
        if (empty($result) || $result == '') {
            $result = true;
        } else {
            $result = false;
        }
        return $result;
    }
    /**
     * Name:checkUserIsBindWeibo
     * Describe:��֤�û��Ƿ��Ѿ���΢��
     * Return:true��false,true��ʾ�Ѿ��󶨣�false��ʾδ��
     */
    public function checkUserIsBindWeibo()
    {
        $User     = D('User');
        $uid      = session('uid');
        $userInfo = $User->getUserInfoById($uid);
        $result   = false;
        if (!empty($userInfo['weiboid'])) {
            $result = true;
        }
        return $this->ajaxReturn($result, 'json');
    }
    /**
     * Name:checkUserIsBindQQ
     * Describe:��֤�û��Ƿ��Ѿ���QQ
     * Return:true��false,true��ʾ�Ѿ��󶨣�false��ʾδ��
     */
    public function checkUserIsBindQQ()
    {

        $User     = D('User');
        $uid      = session('uid');
        $userInfo = $User->getUserInfoById($uid);
        $result   = false;
        if (!empty($userInfo['qqid'])) {
            $result = true;
        }
        return $this->ajaxReturn($result, 'json');
    }
    /**
     * Name:checkNickNameExist
     * Describe:��֤�ǳ��Ƿ����
     * Return:true��false
     */
    public function bindQQ()
    {

    }
}

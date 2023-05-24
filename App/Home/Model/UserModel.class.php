<?php
/**
 * Created by PhpStorm.
 * User: dreamfly
 * Date: 2015/8/7
 * Time: 17:51
 */

namespace Home\Model;

class UserModel extends CommonModel
{
    protected $_auto = array(
        array('status', '1'), // 新增的时候把status字段设置为1
        array('password', 'md5', 3, 'function'), // 对password字段在新增和编辑的时候使md5函数处理
        array('regtime', 'time', 1, 'function'), //对regtime字段在新增时写入当前时间戳
        array('lastlogintime', 'time', 1, 'function'), //对lastlogintime字段在新增时写入当前时间戳
        array('regip', 'get_client_ip', 1, 'function'), // 对regip字段在新增时写入用户注册IP地址
        array('lastloginip', 'get_client_ip', 1, 'function'), //对lastloginip字段在新增时写入用户登录IP地址
    );

    protected $_validate = array(
        array('telephone', 'require', '手机号必须填写'),
        array('password', 'require', '密码必须填写'),
        array('telephone', '', '此手机号已经被注册', 0, 'unique', 1),
        array('nickname', '', '用户昵称已经被注册', 0, 'unique', 1),
        array('repassword', 'password', '密码不一致', 0, 'confirm'),
    );

    public function loginByTelephone($telephone, $password)
    {
        $result = $this->field('id')->where(array('telephone' => $telephone, 'password' => $password))->select();
        return $result[0]['id'];
    }

    public function getUserNicknameById($id)
    {
        $result = $this->field('nickname')->where(array('id' => $id))->select();
        return $result[0]['nickname'];
    }

    //判断手机号是否存在
    public function checkExistPhone($telephone)
    {
        $result = $this->where(array('telephone' => $telephone))->find();
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    //获取用户id根据用户昵称
    public function getUidByNickname($nickname)
    {
        $result = $this->field('id')->where(array('nickname' => $nickname))->find();
        return $result['id'];
    }

    //检查用户密码是否正确
    public function checkTelephonePassword($telephone, $password)
    {
        $result = $this->field('id')->where(array('telephone' => $telephone, 'password' => $password))->find();

        if (empty($result)) {
            return false;
        } else {
            return $result['id'];
        }
    }

    public function getUserByPhone($telephone)
    {
        $result = $this->field('id')->where(array('telephone' => $telephone))->select();
        return $result[0]['id'];
    }
    public function getUserByNickName($nickName)
    {
        $result = $this->field('id')->where(array('nickname' => $nickName))->select();
        return $result[0]['id'];
    }
    public function getUserIdByQqId($qqid)
    {

        $result = $this->field('id')->where(array('qq' => $qqid))->select();
        return $result[0]['id'];
    }
    public function getUserIdByWeiboId($weiboid)
    {
        $result = $this->field('id')->where(array('weibo' => $weiboid))->select();
        return $result[0]['id'];
    }
    public function addUser($userArr)
    {
        $uid = $this->add($userArr);
        return $uid;
    }
    public function updateUserInfo($uid, $update_user_info)
    {
        $this->where(array('id' => $uid))->save($update_user_info);
    }

    //检查该手机号是否已经被关联了,关联的手机号应该是多个的
    //只有当telephone和connectedtelephone都不为空时，关联说明该用户可以直接进行注册
    public function checkPhoneIsConnected($telephone)
    {
        $result = $this->field('telephone')->where(array('connectedtelephone'))->select();
        return $result[0]['telephone'];
    }
    //查找所有的已经关联的手机号
    public function getAllConnectedTelephone()
    {
        return $this->field(array('id', 'telephone', 'connectedtelephone', 'weiboid', 'qqid'))->select();
    }
    //通过用户的ID得到用户的相关信息
    public function getUserInfoById($id)
    {
        $result = $this->field('id', 'nickname', 'password', 'telephone', 'email', 'weiboid', 'qqid')->where(array('id' => $id, 'status' => 0))->select();
        return $result[0];
    }
}

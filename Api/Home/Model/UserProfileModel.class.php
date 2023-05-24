<?php
/**
 * Created by PhpStorm.
 * User: dreamfly
 * Date: 2015/8/7
 * Time: 17:17
 */

namespace Home\Model;

class UserProfileModel extends CommonModel{
    public function getUserPhotoById($uid){
        $result = $this->field('photo')->where(array('uid'=>$uid))->select();
        return $result[0]['photo'];
    }

    public function saveUserProfile($userProfile_info){
        $id = $this->add($userProfile_info);
        return $id;
    }

    public function getAllInfoById(){
        $result = $this->select();
        return $result;
    }

    public function getUserInfoByUid($uid){
        $result = $this->where(array('uid'=>$uid))->select();
        return $result[0];
    }

    public function updateUserInfo($uid,$update_user_info){
        $this->where(array('uid'=>$uid))->save($update_user_info);
    }

    public function getUserDescById($uid){
        $result = $this->where(array('uid'=>$uid))->getField('selfdesc');
        return $result;
    }

    public function getInvitesByUid($uid){
        $result = $this->where(array('uid'=>$uid))->getField('invites');
        return $result;
    }


}
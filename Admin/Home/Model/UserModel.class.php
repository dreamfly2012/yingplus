<?php
namespace Home\Model;

class UserModel extends CommonModel{

    //根据用户ID得到用户的昵称
    public function getUserNickNameByUID($uid){
        $result = $this->field('nickname')
            ->where(array('id'=>$uid))
            ->select();
        return $result[0]['nickname'];
    }

    //得到所有的用户
    public function getAllUser($Page){

        return $this->field(array('id','nickname'))
                     ->where(array('status'=>0))
                     ->limit($Page->firstRow.','.$Page->listRows)
                     ->select();
    }

    public function getCountUser(){
        return $this->where(array('status'=>0))
                     ->count();
    }

    //根据昵称得到用户ID
    public function getUserIDByNickName($nickname){
        $result = $this->field('id')
                       ->where(array('nickname'=>$nickname))
                       ->select();
        return $result[0]['id'];
    }
}
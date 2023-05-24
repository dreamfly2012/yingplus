<?php
namespace Home\Model;

use Think\Model;
class CommonModel extends Model{
    //根据id得到用户nickname
    public function getAdminNickName($admin,$id){
        $data['id']=$id;
        $result = $admin->field('nickname')
            ->where($data)
            ->find();
        return $result['nickname'];
    }

    public function getUserNickname($user,$id){
        $data['id']=$id;
        $name = $user->field('nickname')->where($data)->find();
        return $name['nickname'];
    }
}
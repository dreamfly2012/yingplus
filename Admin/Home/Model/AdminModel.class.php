<?php
namespace Home\Model;

class AdminModel extends CommonModel{
    //检查用户密码
    public function checkPassword($username,$password)
    {
        $data['username']=$username;
        $data['password']=$password;
        $result = $this->field('id')->where($data)->find();
        if (empty($result)) {
            return false;
        } else {
            return $result['id'];
        }
    }

    /*//根据id得到用户nickname
    public function getNickName($id){
        $data['id']=$id;
        $result = $this->field('nickname')
            ->where($data)
            ->find();
        return $result['nickname'];
    }*/
}
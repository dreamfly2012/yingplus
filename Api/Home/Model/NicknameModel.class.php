<?php

namespace Home\Model;

class NicknameModel extends CommonModel{
    public function getIdByNickName($nickname){
        $result = $this->field('id')->where(array('nickname'=>$nickname))->find();
        return $result['id'];
    }

    public function getNickName(){
        $result = $this->field(array('id','nickname'))->where(array('isuse'=>0))->limit(1)->find();
        $this->where(array('id'=>$result['id']))->save(array('isuse' => 1));
        return $result['nickname'];
    }
}
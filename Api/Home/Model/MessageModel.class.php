<?php

namespace Home\Model;

class MessageModel extends CommonModel{
    //获取所有未删除信息
    public function getAllMessage($touid,$p){
        $result = $this->where(array('touid'=>$touid,'status'=>0))->order(array('addtime'=>'desc'))->page($p.','.C('MESSAGE_SHOW_COUNT'))->select();
        return $result;
    }
    public function getReadMessage($touid,$p){
        $result = $this->where(array('touid'=>$touid,'isread'=>1,'status'=>0))->order(array('addtime'=>'desc'))->page($p.','.C('MESSAGE_SHOW_COUNT'))->select();
        return $result;
    }

    public function getUnreadMessage($touid,$p){
        $result = $this->where(array('touid'=>$touid,'isread'=>0,'status'=>0))->order(array('addtime'=>'desc'))->page($p.','.C('MESSAGE_SHOW_COUNT'))->select();
        return $result;
    }

    public function getUnreadMessageCountByUid($uid){
        $result = $this->where(array('touid'=>$uid,'isread'=>0,'status'=>0))->count();
        return $result;
    }


    //删除消息为已读
    public function deleteMessageByUidId($uid,$id){
        $result = $this->where(array('touid'=>$uid,'id'=>$id))->setField(array('status'=>1));
        return $result;
    }
}

 ?>

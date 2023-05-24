<?php

namespace Home\Model;

class ActivityResponseUserModel extends CommonModel{
    public function checkActivityResponseToSelf($uid,$response_id){
        $result = $this->where(array('uid'=>$uid,'response_id'=>$response_id))->find();
        return $result;
    }


    public function getUnreadMessageByUidAid($uid,$aid){
        $result = $this->join('yj_activity_response ON yj_activity_response_user.response_id = yj_activity_response.id')
                        ->where(array('yj_activity_response_user.uid'=>$uid,'yj_activity_response_user.isread'=>0,'yj_activity_response.aid'=>$aid))
                        ->order(array('yj_activity_response.addtime'=>'asc'))->select();
        return $result;
    }

}
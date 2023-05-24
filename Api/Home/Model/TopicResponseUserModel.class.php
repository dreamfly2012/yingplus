<?php

namespace Home\Model;

class TopicResponseUserModel extends CommonModel{
    public function checkTopicResponseToSelf($uid,$response_id){
        $result = $this->where(array('uid'=>$uid,'response_id'=>$response_id))->find();
        return $result;
    }

    /**
     * 获取未读信息数量
     */
    public function getUnreadMessageCountByUidTid($uid,$tid){
        $result = $this->join('yj_topic_response ON yj_topic_response_user.response_id = yj_topic_response.id')
            ->where(array('yj_topic_response_user.uid'=>$uid,'yj_topic_response_user.isread'=>0,'yj_topic_response.tid'=>$tid))
            ->order(array('yj_topic_response.addtime'=>'asc'))->count();
        return $result;
    }



    /**
     * @param $uid
     * @param $id
     * @return mixed
     * 返回话题中用户未读消息
     */
    public function getUnreadMessageByUidTid($uid,$tid){
        $result = $this->join('yj_topic_response ON yj_topic_response_user.response_id = yj_topic_response.id')
                        ->where(array('yj_topic_response_user.uid'=>$uid,'yj_topic_response_user.isread'=>0,'yj_topic_response.tid'=>$tid))
                        ->order(array('yj_topic_response.addtime'=>'asc'))->select();
        return $result;
    }


    /**
     * @param $id
     * @param $uid
     * @return bool
     * 更新一条未读信息变成已读
     */
    public function updateUnreadMessageByIdUid($response_id,$uid){
        $result = $this->where(array('uid'=>$uid,'isread'=>0,'response_id'=>$response_id))
            ->setField(array('isread'=>1));
        return $result;
    }


    /**
     * @param $id
     * @param $uid
     * @return mixed
     * 获取最早一条未读信息
     */
    public function getLatestUnreadMessageByUidTid($uid,$tid){
        $result = $this->join('yj_topic_response ON yj_topic_response_user.response_id = yj_topic_response.id')
            ->where(array('yj_topic_response_user.uid'=>$uid,'yj_topic_response_user.isread'=>0,'yj_topic_response.tid'=>$tid))
            ->order(array('yj_topic_response.addtime'=>'asc'))->find();
        return $result;
    }

}
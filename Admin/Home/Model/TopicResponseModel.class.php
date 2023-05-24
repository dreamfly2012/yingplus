<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/9/29
 * Time: 15:00
 */

namespace Home\Model;


class TopicResponseModel extends CommonModel
{

    public function fetchPartakeTopicNum($uid){
        $sql = "select count(*) as num from yj_topic_response t where t.uid = ".$uid." group by t.tid";
        return $this->query($sql);
    }

    public function getAllTopicResponseList($map,$Page){
        return $this->where($map)
            ->limit($Page->firstRow.','.$Page->listRows)
            ->order('addtime desc')
            ->select();
    }

    public function getTopicResponseCount($map){
        return $this->where($map)->count();
    }

    public function updateResponse($id){
        $arr = array('status'=>1);
        $this->where(array('id'=>$id))->save($arr);
    }
}
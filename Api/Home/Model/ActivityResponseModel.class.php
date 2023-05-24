<?php

namespace Home\Model;

class ActivityResponseModel extends CommonModel{
    /**
     * @param $tid
     * @return mixed
     * 获取活动评论信息，根据活动id
     */
    public function getInfoByAid($aid){
        $result = $this->where(array('aid'=>$aid))->select();
        return $result;
    }

    /**
     * @param $id
     * @return mixed
     * 通过评论id获取用户评论
     */
    public function getInfoById($id){
        $result = $this->where(array('id'=>$id))->find();
        return $result;
    }


    /**
     * 获取新添加的活动评论
     *  @param $aid 活动id
     *  @param $lastid 评论框当前最大的id           
     *  @return 用户实时评论
     */
    public function getInfoByLastId($aid, $lastid){
        $condition['aid'] = $aid;
        $condition['id'] = array('gt',$lastid);
        $condition['status'] = 0;
        $result = $this->where($condition)->select();
        return $result;
    }

   


    /**
     * @param $id
     * @param $uid
     * @return bool
     * 更新一条未读信息变成已读
     */
    public function updateUnreadMessageByIdUid($id,$uid){
        $result = $this->where(array('touid'=>$uid,'id'=>$id))->setField(array('isread'=>1));
        return $result;
    }

    /**
     * @param $uid
     * @param $aid
     * @return bool
     * 更新指定活动下所有用户未读信息为已读
     */
    public function updateUnreadMessageByUidAid($uid,$aid){
        $result = $this->where(array('touid'=>$uid,'aid'=>$aid))->setField(array('isread'=>1));
        return $result;
    }

    /**
     * @param $id
     * @param $uid
     * @return mixed
     * 获取最早一条未读信息
     */
    public function getLatestUnreadMessageByUidAid($uid,$aid){
        $result = $this->where(array('touid'=>$uid,'aid'=>$aid,'isread'=>0))->order(array('id'=>'asc'))->find();
        return $result;
    }

    /**
     * @param $condition
     * 根据条件获取活动
     */
    public function getActivityByCondition($condition,$start,$end){
        $result = $this->where($condition)->limit($start,$end)->select();
        return $result;
    }

    //删除活动回复
    public function deleteResponse($response_id){
        $uid = session('uid');
        $result = $this->where(array('id'=>$response_id,'uid'=>$uid))->setField(array('status'=>1));
        return $result;
    }


}
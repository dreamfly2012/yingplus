<?php
/**
 * Created by PhpStorm.
 * User: dreamfly
 * Date: 2015/8/7
 * Time: 16:34
 */
namespace Home\Model;

class TopicResponseModel extends CommonModel{
    /**
     * @param $tid
     * @return mixed
     * 根据话题id获取评论信息
     */
    public function getInfoByTid($tid){
        $result = $this->where(array('tid'=>$tid,'status'=>0))
            ->order('addtime desc')
            ->limit(0,3)
            ->select();
        return $result;
    }
    public function getInfoByTidToDetail($tid){
        $result = $this->where(array('tid'=>$tid,'status'=>0))
            ->order('addtime desc')
            ->limit(0,5)
            ->select();
        return $result;
    }

    public function getMoreInfoByTid($firstid,$tid){
        $condition['tid'] = $tid;
        $condition['id'] = array('lt',$firstid);
        $condition['status'] = 0;
        $result = $this->where($condition)
            ->order('addtime desc')
            ->limit(0,3)
            ->select();
        return $result;
    }


    /**
     * @param $id
     * @return mixed
     * 通过评论id获取用户评论
     */
    public function getInfoById($id){
        $result = $this->where(array('id'=>$id,'status'=>0))->order(array('id'=>'asc'))->find();
        return $result;
    }


    public function getInfoByLastId($tid,$lastid){
        $condition['tid'] = $tid;
        $condition['id'] = array('gt',$lastid);
        $condition['status'] = 0;
        $result = $this->where($condition)->select();
        return $result;
    }

    /**
     * @param $condition
     * 根据条件获取话题
     */
    public function getTopicByCondition($condition,$start,$end){
        $result = $this->where($condition)->limit($start,$end)->select();
        return $result;
    }

    //删除话题回复
    public function deleteResponse($response_id){
        $uid = session('uid');
        $result = $this->where(array('id'=>$response_id,'uid'=>$uid))->setField(array('status'=>1));
        return $result;
    }

    //得到指定话题数量
    public function getTopicResponseCountByTid($tid){
        return $this->where(array('tid'=>$tid,'status'=>0))->count();
    }

    //得到指定话题最新的回复时间
    public function getTipicLastResponseTimeByTid($tid){
        $result = $this->field(array('addtime'))->where(array('tid'=>$tid,'status'=>0))->order('addtime desc')->limit(1)->select();
        return $result[0]['addtime'];
    }
}
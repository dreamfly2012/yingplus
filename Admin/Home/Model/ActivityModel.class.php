<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/9/19
 * Time: 17:21
 */

namespace Home\Model;


class ActivityModel extends CommonModel
{
    //得到所有的活动
    public function getActivities($wheresql,$Page,$usertype){
        if($usertype == 0){
            return $this->table('yj_activity as a')
                         ->field(array('id','fid','uid','holdprovince','holdcity','subject','notice','participatemethod','type','addtime','status'))
                         ->where($wheresql)
                         ->order('status asc')
                         ->limit($Page->firstRow.','.$Page->listRows)
                         ->select();
        }elseif($usertype == 1){
            return $this->table('yj_activity as a')->join('yj_activity_response as response')
                ->field(array('a.id','a.fid','a.uid','holdprovince','holdcity','subject','notice','participatemethod','type','a.addtime','a.status'))
                ->where($wheresql)
                ->order('status asc')
                ->limit($Page->firstRow.','.$Page->listRows)
                ->select();
        }else{
            return $this->table('yj_activity as a')->join('yj_activity_encroll as encroll')
                         ->field(array('a.id','a.fid','a.uid','holdprovince','holdcity','subject','notice','participatemethod','type','addtime','a.status'))
                         ->where($wheresql)
                         ->order('status asc')
                         ->limit($Page->firstRow.','.$Page->listRows)
                         ->select();
        }

    }

    //得到活动的数量
    public function getCountActivity($wheresql,$usertype){
        if($usertype == 0){
            return $this->table('yj_activity as a')
                         ->where($wheresql)
                         ->order('status asc')
                         ->count();
        }elseif($usertype == 1){
            return $this->table('yj_activity as a')->join('yj_activity_response as response')
                ->where($wheresql)
                ->order('status asc')
                ->count();
        }else{
            return $this->table('yj_activity as a')->join('yj_activity_encroll as encroll')
                         ->where($wheresql)
                         ->order('status asc')
                         ->count();
        }
    }
    //得到活动主题
    public function getActivitySubject($aid){
        return $this->getFieldById($aid,'subject');
    }
    //得到活动的类型
    public function getActivityType($aid){
        return $this->getFieldById($aid,'type');
    }
    //得到活动的地点：举办活动的省份
    public function getActivityHoldProvince($aid){
        return $this->getFieldById($aid,'holdprovince');
    }
    //得到活动的地点：举办活动的城市
    public function getActivityHoldCity($aid){
        return $this->getFieldById($aid,'holdcity');

    }

    //得到指定用户创建活动数
    public function getActivityNumByUid($wheresql){
        return $this->where($wheresql)
                     ->count();
    }

    //更新活动
    public function updateActivity($id,$arr){
        $this->where(array('id'=>$id))
             ->save($arr);
    }

    public function getActivityById($aid){
        $result = $this->field()
                       ->where(array('id'=>$aid))
                       ->select();
        return $result[0];
    }

}
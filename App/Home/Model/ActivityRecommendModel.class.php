<?php

namespace Home\Model;

class ActivityRecommendModel extends CommonModel{
    /**
     * 查看推荐互动是否存在
     */
    public function checkExist($condition){
        $result = $this->where($condition)->find();
        return $result;
    }


    //判断是否超过限制数量
    public function checkExistNum($condition){
        $num = $this->where($condition)->count();
        $exceed_num = C('ACTIVITY_RECOMMEND_NUM');
        if($num>$exceed_num){
            return true;
        }else{
            return false;
        }

    }


    /**
     * @return mixed
     * 获取推荐活动列表
     */
    public function getAllInfo($fid){
        $result = $this->where(array('fid'=>$fid))->select();
        return $result;
    }
}
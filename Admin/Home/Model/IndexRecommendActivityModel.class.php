<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/10/31
 * Time: 16:51
 */

namespace Home\Model;


class IndexRecommendActivityModel extends CommonModel
{

    public function getAllHomeRecommendActivity(){
        return $this->where(array('status'=>0))
                     ->select();
    }

    //得到已被推荐活动的数量
    public function getHomeRACount(){
        return $this->where(array('status'=>0))
                     ->count();
    }

    //更新推荐活动
    public function updateHRA($id,$arr){
        $this->where(array('id'=>$id))
             ->save($arr);
    }

    public function getRecommendActivity($id){
        $result = $this->where(array('id'=>$id))->select();
        return $result[0];
    }
}
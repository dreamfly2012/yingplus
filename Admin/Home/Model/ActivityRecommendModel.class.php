<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/9/21
 * Time: 14:01
 */

namespace Home\Model;

/**
 * Class ActivityRecommendModel
 * @package Home\Model
 * @discribe 该类用于推荐活动部分
 */
class ActivityRecommendModel extends CommonModel
{

    //得到推荐的活动：0->取消推荐 1->设为推荐
    public function getRecommendActivity($Page){

        return $this->field(array('id','fid','uid','aid','addtime','status'))
                     ->limit($Page->firstRow.','.$Page->listRows)
                     ->select();
    }

    public function getActivityCount(){

        return $this->where(array('isrecommend'=>1,'status'=>0))
                     ->count();
    }

    public function updateRecommend($id,$arr){
        $this->where(array('id'=>$id))
             ->save($arr);
    }
}
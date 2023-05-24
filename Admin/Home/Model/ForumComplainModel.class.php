<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/9/19
 * Time: 16:51
 */

namespace Home\Model;


class ForumComplainModel extends CommonModel
{

    public function findDateByCondition($wheresql,$Page){
        return $this->field(array('id','uid','messageid','touid','involveid','content','addtime','type','isshenhe'))
                     ->where($wheresql)
                     ->order(array('isshenhe'=>'desc','addtime'=>'desc'))
                     ->limit($Page->firstRow.','.$Page->listRows)
                     ->select();
    }

    public function getCountByCondition($wheresql){
        return $this->where($wheresql)
                     ->count();
    }

    public function updateComplainStatus($id,$arr){
        $this->where(array('id'=>$id))
             ->save($arr);
    }
}
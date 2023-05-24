<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/9/26
 * Time: 9:20
 */

namespace Home\Model;

/**
 * Class ForumAgentModel
 * @package Home\Model
 * @discribe :该类用于处理申请经纪人
 */
class ForumAgentModel extends CommonModel
{

    //得到申请经纪人列表
    public function getApplyAgentList($Page){
        return $this->field(array('id','uid','fid','addtime','status'))
                     ->where(array('status'=>0))
                     ->order('addtime desc')
                     ->limit($Page->firstRow.','.$Page->listRows)
                     ->select();
    }
    //得到申请经纪人的数量
    public function getCountAgent(){
        return $this->where(array('status'=>0))
                    ->count();
    }

    public function updateReportStatus($id,$arr){
        $this->where(array('id'=>$id))
             ->save($arr);
    }
}
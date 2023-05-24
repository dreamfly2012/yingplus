<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/9/18
 * Time: 14:54
 */

namespace Home\Model;


use Home\Model\CommonModel;

class ForumReportModel extends CommonModel
{

    //得到举报经纪人的列表
    public function getReportAgentList($type,$status,$Page){
        return $this->field(array('id','uid','touid','reason','content','addtime','isshenhe'))
             ->where(array('type'=>$type,'status'=>$status,'isshenhe'=>1))
             ->order('addtime desc')
             ->limit($Page->firstRow.','.$Page->listRows)
             ->select();
    }

    //更新举报经纪人状态：属实(0) | 不属实(1)
    public function updateReportStatus($reportID,$date){

        return $this->where(array('id'=>$reportID))
             ->save($date);
    }

    //得到举报经纪人的总数
    public function getReportCount($type,$status){
        return $this->where(array('type'=>$type,'status'=>$status,'isshenhe'=>1))
                     ->count();
    }
}
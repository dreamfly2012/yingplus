<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/9/29
 * Time: 15:34
 */

namespace Home\Model;


class ActivityEnrollModel extends CommonModel
{

    public function getEncrollActivityByUid($uid){
        $sql = "select count(*) as num from yj_activity_encroll a where a.uid = ".$uid." group by a.aid;";
        return $this->query($sql);
    }

    public function getAllFensiByAid($aid){
        return $this->where(array('aid'=>$aid,'status'=>0))->select();
    }
}
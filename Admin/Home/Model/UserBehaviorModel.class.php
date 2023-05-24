<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/9/30
 * Time: 10:59
 */

namespace Home\Model;


class UserBehaviorModel extends CommonModel
{

    public function getUserBehavior($Page,$wheresql){
        return $this->table('yj_user as u')
                     ->join('yj_user_behavior as b')
                     ->where($wheresql)
                     ->limit($Page->firstRow.','.$Page->listRows)
                     ->select();
    }

    public function getCountUserBehavior($wheresql){
        return $this->table('yj_user as u')
                     ->join('yj_user_behavior as b')
                     ->where($wheresql)
                     ->count();
    }
}
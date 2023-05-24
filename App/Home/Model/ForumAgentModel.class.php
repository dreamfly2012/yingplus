<?php

namespace Home\Model;

class ForumAgentModel extends CommonModel{


    public function findUserByUidFid($uid,$fid){

        $result = $this->where(array('uid'=>$uid,'fid'=>$fid,'status'=>0))->select();
        return $result;
    }
}
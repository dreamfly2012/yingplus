<?php

namespace Home\Model;

class ForumSignModel extends CommonModel{
    /**
     * @param $fid
     * @param $uid
     * @return bool
     * 验证用户是否是吧内成员
     * 返回用户是否存在
     * true:存在
     * false:不存在
     */
    public function checkExist($fid,$uid){
        $result = $this->where(array('fid'=>$fid,'uid'=>$uid))->getField('uid');
        if($result){
            return true;
        }else{
            return false;
        }
    }

    /**
     * @param $fid
     * @param $uid
     * @return bool
     * insert into the form sign table
     *
     * 返回插入成功与否
     */
    public function insertUser($fid,$uid,$lingtime,$prelogintime){
        $result = $this->add(array('fid'=>$fid,'uid'=>$uid,'count'=>1,'lcount'=>1,'addtime'=>time(),'lingtime'=>$lingtime,'prelogintime'=>$prelogintime));
        return $result;
    }

    /**
     * @param $fid
     * @param $uid
     * @param $lingtime
     * @param $prelogintime
     * @return int
     * 更新签到表,修改签到总次数，上次签到时间，签到零点
     */
    public function updateUser($fid,$uid,$lingtime,$prelogintime){
        $result1 = $this->where(array('fid'=>$fid,'uid'=>$uid))->setField(array('lingtime'=>$lingtime,'prelogintime'=>$prelogintime));

        $result2 = $this->where(array('fid'=>$fid,'uid'=>$uid))->setInc('count');

        return $result1&$result2;
    }


    public function getLingTime($fid,$uid){
        $result = $this->where(array('fid'=>$fid,'uid'=>$uid))->getField('lingtime');
        return $result;
    }

    //获取用户上次签到的时间，和连续签到的时间
    public function getTimeInfo($fid,$uid){
        $result = $this->field(array('prelogintime','lingtime'))->where(array('fid'=>$fid,'uid'=>$uid))->find();
        return $result;
    }

    public function updateLCount($fid,$uid,$bool){
        if($bool){
            $this->where(array('fid'=>$fid,'uid'=>$uid))->setInc('lcount');
        }else{
            $this->where(array('fid'=>$fid,'uid'=>$uid))->setField(array('lcount'=>1));
        }
    }

    public function getUserAllSigns($uid,$fid){

        $result = $this->where(array('uid'=>$uid,'fid'=>$fid))
                       ->select();
        return $result;
    }
}
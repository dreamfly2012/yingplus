<?php

namespace Home\Model;

class ForumUserModel extends CommonModel{
    public function getOnlineTimeByForumIdAndUserId($fid,$uid){
        $result = $this->where(array('fid'=>$fid,'uid'=>$uid))->getField('onlinetime');
        return $result;
    }

    //是否关注过工作室
    public function checkExist($fid,$uid){
        $result = $this->where(array('fid'=>$fid,'uid'=>$uid))->getField('uid');
        if($result){
            return true;
        }else{
            return false;
        }
    }

    //是否是曾经关注现在未关注的
    public function OnceFollowed($fid,$uid){
        $result = $this->where(array('fid'=>$fid,'uid'=>$uid,'status'=>1))->getField('uid');
        if($result){
            return true;
        }else{
            return false;
        }
    }

    //加入工作室
    public function insertUser($fid,$uid,$firsttime,$addtime){
        $result = $this->add(array('fid'=>$fid,'uid'=>$uid,'firsttime'=>$firsttime,'addtime'=>$addtime));
        return $result;
    }

    //加入工作室(取消后重新关注)
    public function updateUser($fid,$uid,$addtime){
        $result = $this->where(array('fid'=>$fid,'uid'=>$uid))->setField(array('status'=>0,'addtime'=>$addtime));
        return $result;
    }

    //获取用户关注工作室数量
    public function followForumNum($uid){
        $result = $this->where(array('uid'=>$uid,'status'=>0))->count();
        return $result;
    }

    public function checkFollowIsOverflow($uid){
        $count = $this->followForumNum($uid);
        
        $over = C('FORUM_FOLLOW_COUNT');
        if($count>=$over){
            return true;
        }else{
            return false;
        }
    }

    /**
     * @param $fid
     * @param $uid
     * @return mixed
     * 获取用户权限信息
     */
    public function getAccessInfo($fid,$uid){
        $result = $this->where(array('fid'=>$fid,'uid'=>$uid))->select();
        return $result[0];
    }

    public function getFavorsByUid($uid,$fid){
        $result = $this->where(array('fid'=>$fid,'uid'=>$uid))->getField(('favors'));
        return $result;
    }

    public function getTopicsByUid($uid,$fid){
        $result = $this->where(array('fid'=>$fid,'uid'=>$uid))->getField(('createtopicnum'));
        return $result;
    }

    public function getAbsenceactivitiesByUid($uid,$fid){
        $result = $this->where(array('fid'=>$fid,'uid'=>$uid))->getField(('absenceactivities'));
        return $result;
    }

    public function getCancelactivitynumByUid($uid,$fid){
        $result = $this->where(array('fid'=>$fid,'uid'=>$uid))->getField(('cancelactivitynum'));
        return $result;
    }

    public function getLastabsenceactivityByUid($uid,$fid){
        $result = $this->where(array('fid'=>$fid,'uid'=>$uid))->getField(('lastabsenceactivity'));
        return $result;
    }

    public function getActivitiesByUid($uid,$fid){
        $result = $this->where(array('fid'=>$fid,'uid'=>$uid))->getField(('createactivitynum'));
        return $result;
    }

    //获取指定工作室所有经纪人   
    public function getAdminUserInfo($fid){
        $result = $this->where(array('fid'=>$fid,'isadmin'=>1))->select();
        return $result;
    }

    //获取当前用户所管理的工作室
    public function getAdminFidByUid($uid){
        $result = $this->field('fid')->where(array('uid'=>$uid,'isadmin'=>1,'status'=>0))->find();
        return $result;
    }

    //获取当前用户所在工作室
    public function getForumsByUid($uid){
        $result = $this->field('fid')->where(array('uid'=>$uid,'status'=>0))->select();
        return $result;
    }

    /**
     * @param $uid
     * @return mixed
     * 获取用户关注的星吧
     */
    public function getFavourForumByUid($uid){
        $result = $this->where(array('uid'=>$uid))->select();
        return $result;
    }

    public function checkIsInforum($uid,$fid){
        $result = $this->where(array('uid'=>$uid,'fid'=>$fid,'status'=>0))->find();
        return $result;
    }

}
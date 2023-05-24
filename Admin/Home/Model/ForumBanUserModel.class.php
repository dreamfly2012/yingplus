<?php
/**
 * Created by PhpStorm.
 * User: roak
 * Date: 2015/9/21
 * Time: 16:28
 */

namespace Home\Model;

/**
 * Class ForumBanUserModel
 * @package Home\Model
 * @discribe 粉丝封禁的相关操作
 */
class ForumBanUserModel extends CommonModel
{

    //得到所有被封禁的用户
    public function getBanUser($wheresql,$Page,$fid){
        if(empty($fid)){
            $result =  $this->field(array('id','uid','reason','option','totaltime','bantime','status'))
                ->where($wheresql)
                ->order('bantime','desc')
                ->limit($Page->firstRow.','.$Page->listRows)
                ->select();
        }else{
            $wheresql .= ' and a.uid = b.uid and a.fid ='.$fid;
            $result = $this->table('yj_forum_user as a')->join('yj_forum_ban_user as b')
                           ->field(array('b.id','b.uid','b.reason','b.option','b.totaltime','b.bantime'))
                           ->where($wheresql)
                           ->order('b.bantime','desc')
                           ->limit($Page->firstRow.','.$Page->listRows)
                           ->select();
        }
        return $result;
    }

    //查找封禁列表的所有用户
    public function getCountBanUser($wheresql,$fid){
        if(empty($fid)){
            return $this->where($wheresql)
                ->count();
        }else{
            $wheresql .= ' and a.uid = b.uid and a.fid ='.$fid;
            $count = $this->table('yj_forum_user as a')->join('yj_forum_ban_user as b')
                         ->where($wheresql)
                         ->count();
            return $count;
        }
    }

    public function updateBanUserStatus($id,$arr){
        return $this->where(array('id'=>$id))
                     ->save($arr);
    }

    public function getBanUsers(){
        return $this->field(array('uid','status'))
                     ->select();
    }

    public function saveBanUser($uid,$arr){

        if($this->where(array('uid'=>$uid))->count()){
            $this->where(array('uid'=>$uid))->save($arr);
        }else{
            $arr['uid'] = $uid;
            $this->add($arr);
        }
    }
}
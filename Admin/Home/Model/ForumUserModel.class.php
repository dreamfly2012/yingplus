<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/9/24
 * Time: 14:54
 */

namespace Home\Model;


/**
 * Class ForumUserModel
 * @package Home\Model
 * @discribe 该类用于关注明星的相关操作
 */
class ForumUserModel extends CommonModel
{

    //查找关注某个明星的所有粉丝
    public function getAttendStarUserBySid($fid){

        return $this->field(array('uid'))
                     ->where(array('fid'=>$fid))
                     ->select();
    }
    //得到经纪人的列表
    public function getAgentList($wheresql,$Page){
        return $this->field(array('id','fid','uid','becometime','isadmin'))
                     ->where($wheresql)
                     ->order('becometime desc')
                     ->limit($Page->firstRow.','.$Page->listRows)
                     ->select();
    }

    //得到经纪人数
    public function getCountAgent($wheresql){
        return $this->where($wheresql)
                     ->count();
    }

    public function updateAgent($id,$arr){
        $this->where(array('id'=>$id))
             ->save($arr);
    }

    public function getIdByUid($uid){
        $result = $this->field(array('id'))->where(array('uid'=>$uid))->select();
        return $result[0]['id'];
    }

    public function isForumUser($fid,$uid){
        return $this->where(array('fid'=>$fid,'uid'=>$uid))->select();
    }
}
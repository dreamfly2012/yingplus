<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/9/21
 * Time: 14:25
 */

namespace Home\Model;


class ForumModel extends CommonModel
{

    //得到活动所属于的星吧
    public function getActivityForum($fid){

        $forum = $this->field(array('name'))
             ->where(array('id'=>$fid))
             ->select();
        return $forum[0]['name'];
    }

    //根据明星姓名得到明星ID
    public function getForumIDByName($starname){
        $fid = $this->field(array('id'))
             ->where(array('name'=>$starname))
             ->select();
        return $fid[0]['id'];
    }

    //得到星吧名称
    public function getForumNameById($fid){

        $forum = $this->field(array('name'))
                      ->where(array('id'=>$fid))
                      ->select();
        return $forum[0]['name'];
    }
    //查询获取星吧的信息
    public function getForumManage($array,$Page){
        $condition = $this->condiction($array);
        $jobManage = $this->where($condition)
                     ->order('addtime desc')
                     ->limit($Page->firstRow.','.$Page->listRows)
                     ->select();
        return $jobManage;
    }

    public function getForumManageCount($array){
        $condition = $this->condiction($array);
        $count = $this->where($condition)->count();
        return $count;
    }

    public function addUrl($array){
        foreach($array as $key=>$value){
            $array[$key]['url'] = U('Home/JobManage/jobMassage',array('id'=>$value['id']));
        }
        return $array;
    }

    public function condiction($array){
        if($array['name']){
            $arr['name']=$array['name'];//array('like','%'.$array['name'].'%');
        }
        if($array['chinesename']){
            $arr['chinesename']=$array['chinesename'];
        }
        if($array['groupname']){
            $arr['groupname']= $array['groupname'];
        }
        if($array['representative']){
            $arr['representative']=$array['representative'];
        }

        return $arr;
    }

    public function getForumMessage($id){
        $data['id'] = $id;
        $forum = $this->where($data)->find();
        return $forum;
    }

    public function setForumMassage($id,$array){
        $data['id']=$id;
        $this->where($data)->save($array);
    }

    public function addForumMassage($data){
        $this->add($data);
    }
    public function getForumCount($Page){
        $result=$this->order('addtime desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        return $result;
    }
    /*查询管理员推荐的工作室*/
    public function forumPromote($Page){
        $condition['is_admin_promote'] = 1;
        $result = $this->where($condition)
                       ->limit($Page->firstRow.','.$Page->listRows)
                       ->select();
        return $result;
    }
    public function forumPromoteCount(){
        $condition['is_admin_promote'] = 1;
        $result = $this->where($condition)->count();
        return $result;
    }
    public function saveMessage($id,$array){
        $condit['id'] = $id;
        $this->where($condit)->save($array);
    }
    
    public function deleteData($id){
         $data['id']=$id;
         $this->where($data)->delete();        
    }
    public function selectAttachId($id){
         $data['id']=$id;
        $model = $this->where($data)->find();
        return $model;
    }
}
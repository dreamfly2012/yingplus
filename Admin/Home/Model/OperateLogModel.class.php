<?php
namespace Home\Model;

 class OperateLogModel extends CommonModel{
     public function getOperateLog($array,$Page){
         $condition = $this->condition($array);
         $model = $this->field('yj_operate_log.*')
                       ->join('yj_user on yj_operate_log.admin_id = yj_user.id')
                       ->join('yj_forum on yj_operate_log.fid = yj_forum.id')
                       ->where($condition)
                       ->limit($Page->firstRow.','.$Page->listRows)
                       ->select();
         //$result = $this->changeDataId($model);
         return $model;
     }
     public function getCount($array){
         $condition = $this->condition($array);
         $model = $this->join('yj_user on yj_operate_log.admin_id = yj_user.id')
             ->join('yj_forum on yj_operate_log.fid = yj_forum.id')
             ->where($condition)
             ->count();
         return $model;
     }
     public function condition($array){
        if($array['nickname']){
            $arr['yj_user.nickname']=array('like','%'.$array['nickname'].'%');
        }
         if($array['name']){
             $arr['yj_forum.name']=array('like','%'.$array['name'].'%');
         }
         if($array['startime']&&$array['endtime']){
             $arr['addtime']=array('between',array($array['startime'],$array['endtime']));
         }
         return $arr;
     }


     public function changeDataId($array){
         foreach($array as $key=>$value){
             $array[$key]['admin_name'] = $this->getNickname($value['admin_id']);
             $array[$key]['nickname'] = $this->getNickname($value['uid']);
             $array[$key]['forum_name'] = $this->getForumName($value['fid']);
         }
         return $array;
     }
     public function getNickname($id){
         $user = M('User');
         $data['id']=$id;
         $name = $user->field('nickname')->where($data)->find();
         return $name['nickname'];
     }

     public function getForumName($id){
         $forum = M('Forum');
         $data['id']=$id;
         $name = $forum->field('name')->where($data)->find();
         return $name['name'];
     }
     public function getLimitOperate($Page){
         $result=$this->limit($Page->firstRow.','.$Page->listRows)->select();
         return $result;
     }



 }
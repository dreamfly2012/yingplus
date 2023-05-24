<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/12/28
 * Time: 14:23
 */

namespace Home\Model;


class ActivityMovieVideoModel extends CommonModel
{

    public function getAllVideoes($Page){
        return $this->where(array('status'=>0))
                     ->limit($Page->firstRow.','.$Page->listRows)
                     ->order('addtime desc')
                     ->select();
    }

    public function getVideoCount(){
        return $this->where(array('status'=>0))->count();
    }

    public function getVideoById($id){
        $video = $this->where(array('id'=>$id))->select();
        return $video[0];
    }

    public function getMaxHot($fid){
        $result = $this->where(array('fid'=>$fid,'status'=>0))
                       ->order('`order` desc')
                       ->limit(1)
                       ->select();
        return $result[0]['order'];
    }

    public function updateVideo($id){
        $arr = array('status'=>1);
        $this->where(array('id'=>$id))->save($arr);
    }
}
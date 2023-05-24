<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/10/30
 * Time: 13:51
 */

namespace Home\Model;



class HelpProfileModel extends CommonModel
{


    public function getAllHelpProfile($Page){
        return $this->where(array('status'=>0))
                     ->limit($Page->firstRow.','.$Page->listRows)
                     ->select();
    }

    public function getCount(){
        return $this->where(array('status'=>0))
                     ->count();
    }

    public function getHelpById($id){
        $result = $this->where(array('id'=>$id))
                       ->select();
        return $result[0];
    }

    public function update($id,$arr){
        $this->where(array('id'=>$id))
             ->save($arr);
    }
}
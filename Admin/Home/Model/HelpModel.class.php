<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/10/30
 * Time: 13:36
 */

namespace Home\Model;


class HelpModel extends CommonModel
{

    public function getHelpById($id){
        $result = $this->where(array('id'=>$id))
                       ->select();
        return $result[0];
    }

    public function updateHelp($id,$arr){
        $this->where(array('id'=>$id))
             ->save($arr);
    }

}
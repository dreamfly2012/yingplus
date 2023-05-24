<?php
/**
 * Created by PhpStorm.
 * User: dreamfly
 * Date: 2015/8/13
 * Time: 11:03
 */

namespace Home\Model;

class AttachmentModel extends CommonModel{
    public function getAttachmentById($id){
        $result = $this->where(array('id'=>$id))->select();
        return $result[0];
    }
}
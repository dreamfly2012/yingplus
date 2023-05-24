<?php

namespace Home\Model;

class ActivityTagModel extends CommonModel{
    public function getNameById($id){
        $result = $this->field('tagname')->where(array('id'=>$id))->find();
        return $result['tagname'];
    }
}
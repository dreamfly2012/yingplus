<?php

namespace Home\Model;

class ActivityTypeModel extends CommonModel{
    public function getAllType(){
        $result = $this->select();
        return $result;
    }

    public function getNameById($id){
        $result = $this->field('name')->where(array('id'=>$id))->find();
        return $result['name'];
    }
}
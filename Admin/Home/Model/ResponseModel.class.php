<?php

namespace Home\Model;

class ResponseModel extends CommonModel
{
    public function add($condition,$data){
        return $this->where($condition)->add($data);
    }

    public function getActivityResponseCount($map){
        return $this->where($map)->count();
    }

    public function delete($id){
        return $this->where(array('id'=>$id))->setField(array('status'=>1));
    }
}
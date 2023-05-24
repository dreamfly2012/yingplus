<?php

namespace Home\Model;
class IndexRecommendTopicModel extends CommonModel{
    public function getTopic(){
        $result = $this->where(array('status'=>0))->order(array('order'=>'desc'))->select();
        return $result;
    }
}
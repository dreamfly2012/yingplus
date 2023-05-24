<?php

namespace Home\Model;
class IndexRecommendActivityModel extends CommonModel{
    public function getActivity(){
        $result = $this->join('yj_activity ON yj_index_recommend_activity.aid = yj_activity.id')->where(array('yj_index_recommend_activity.status'=>0,'yj_activity.status'=>array('neq',1)))->order(array('order'=>'desc'))->select();
        return $result;
    }
}
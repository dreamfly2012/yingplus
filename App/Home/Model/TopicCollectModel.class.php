<?php

namespace Home\Model;

class TopicCollectModel extends CommonModel{
	public function collectTopic($tid,$uid){
		if($this->checkHasCollect($tid,$uid)){
			$result = $this->where(array('tid'=>$tid,'uid'=>$uid))->setField(array('status'=>0));
			return $result;
		}else{
			$result = $this->add(array('tid'=>$tid,'uid'=>$uid,'status'=>0));
			return $result;
		}
		
	}

	public function uncollectTopic($tid,$uid){
		$result = $this->where(array('tid'=>$tid,'uid'=>$uid))->setField(array('status'=>1));
		return $result;
	}

    //话题是否被收藏
	public function checkIsCollect($tid,$uid){
		$result = $this->where(array('tid'=>$tid,'uid'=>$uid,'status'=>0))->find();
		return $result;
	}

	//话题是否被收藏过
	public function checkHasCollect($tid,$uid){
		$result = $this->where(array('tid'=>$tid,'uid'=>$uid))->find();
		return $result;
	}

    /**
     * @param $condition
     * 根据条件获取话题
     */
    public function getTopicByCondition($condition,$start,$end){
        $result = $this->where($condition)->limit($start,$end)->select();
        return $result;
    }
}
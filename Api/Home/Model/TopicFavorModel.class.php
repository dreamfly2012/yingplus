<?php

namespace Home\Model;

class TopicFavorModel extends CommonModel{
	public function checkIsFavor($tid,$uid){
		$result = $this->where(array('tid'=>$tid,'uid'=>$uid,'status'=>1))->find();
		return $result;
	}

	//话题是否被收藏过
	public function checkHasFavor($tid,$uid){
		$result = $this->where(array('tid'=>$tid,'uid'=>$uid))->find();
		return $result;
	}
}
<?php

namespace Home\Model;

class ActivityFavorModel extends CommonModel{
	//活动是否被点赞
	public function checkIsFavor($tid,$uid){
		$result = $this->where(array('aid'=>$tid,'uid'=>$uid,'status'=>1))->find();
		return $result;
	}

	//活动是否曾经被点赞过
	public function checkHasFavor($tid,$uid){
		$result = $this->where(array('aid'=>$tid,'uid'=>$uid))->find();
		return $result;
	}
}
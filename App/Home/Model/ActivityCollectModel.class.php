<?php

namespace Home\Model;

class ActivityCollectModel extends CommonModel{
	public function collectActivity($aid,$uid){
		if($this->checkHasActivity($aid,$uid)){
			$result = $this->where(array('aid'=>$aid,'uid'=>$uid))->setField(array('status'=>0));
			return $result;
		}else{
			$result = $this->add(array('aid'=>$aid,'uid'=>$uid,'status'=>0));
			return $result;
		}
		
	}

	public function uncollectActivity($aid,$uid){
		$result = $this->where(array('aid'=>$aid,'uid'=>$uid))->setField(array('status'=>1));
		return $result;
	}

	public function checkIsCollect($aid,$uid){
		$result = $this->where(array('aid'=>$aid,'uid'=>$uid,'status'=>0))->find();
		return $result;
	}

	public function checkHasActivity($aid,$uid){
		$result = $this->where(array('aid'=>$aid,'uid'=>$uid))->find();
		
		return $result;
	}

    /**
     * @param $condition
     * 根据条件获取活动
     */
    public function getActivityByCondition($condition,$start,$end){
        $result = $this->where($condition)->limit($start,$end)->select();
        return $result;
    }
}
<?php

namespace Home\Model;

class ActivityEnrollModel extends CommonModel {
	public function getParticipateActivities($uid) {
		$result = $this->where(array('uid' => $uid))->select();
		return $result;
	}

	//查询已经报名用户
	public function getUserHasEnroll($aid) {
		$result = $this->where(array('aid' => $aid, 'status' => 0))->select();
		return $result;
	}

	//查询用户是否报名该活动
	public function checkIsEnroll($uid, $aid) {
		$result = $this->where(array('uid' => $uid, 'aid' => $aid, 'status' => 0))->find();
		return $result;
	}

	/**
	 * @param $condition
	 * 根据条件获取活动
	 */
	public function getActivityByCondition($condition, $start, $end) {
		$result = $this->where($condition)->limit($start, $end)->select();
		return $result;
	}
}
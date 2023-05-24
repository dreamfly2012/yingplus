<?php

namespace Home\Model;

class SeminarFavorModel extends CommonModel {
	public function checkIsFavor($sid, $uid) {
		$result = $this->where(array('sid' => $sid, 'uid' => $uid, 'status' => 1))->find();
		return $result;
	}

	//话题是否被收藏过
	public function checkHasFavor($sid, $uid) {
		$result = $this->where(array('sid' => $sid, 'uid' => $uid))->find();
		return $result;
	}
}
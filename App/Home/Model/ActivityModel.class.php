<?php
/**
 * Created by PhpStorm.
 * User: dreamfly
 * Date: 2015/8/12
 * Time: 13:39
 */

namespace Home\Model;

class ActivityModel extends CommonModel {
	/**
	 * @param $fid
	 * @return mixed
	 * 获取星吧下所有活动
	 */
	public function getAllInfoByFid($fid) {
		$result = $this->where(array('fid' => $fid))->select();
		return $result;
	}

	/**
	 * 获取用户创建的活动
	 * @param $uid
	 * @return mixed
	 */
	public function getSelfActivityByUid($uid, $start, $page_num) {
		$result = $this->where(array('uid' => $uid, 'status' => 0))->order(array('holdstart' => 'desc'))->limit($start, $page_num)->select();
		return $result;
	}

	public function getFidByAid($aid) {
		$result = $this->field('fid')->where(array('id' => $aid))->find();
		return $result['fid'];
	}

	//获取管理员推荐活动
	//    public function getAdminRecommendByFid($fid){
	//        $result = $this->where(array('fid'=>$fid,'istrash'=>0,'isadminrecommend'=>1,'status'=>array('neq',1)))->find();
	//        return $result;
	//    }

	//获取经纪人推荐活动
	//    public function getForumAdminRecommendByFid($fid){
	//        $result = $this->where(array('fid'=>$fid,'istrash'=>0,'isrecommend'=>1,'status'=>array('neq',1)))->find();
	//        return $result;
	//    }

	/**
	 * 获取所有活动的城市地址
	 */
	public function getActivityAddress() {
		$result = $this->field('holdcity')->select();
		return $result;
	}
	/**
	 * @param $id
	 * @return mixed
	 * 根据id获取活动信息
	 */
	public function getActivityInfoById($id) {
		$result = $this->where(array('id' => $id))->find();
		return $result;
	}

	/**
	 * 获取平台热门活动
	 * @return mixed
	 */
	public function getHotPlatformActivity() {
		$where['isadminrecommend'] = array('eq', '1');
		$where['hot'] = array('gt', C('HOT_ACTIVITY_BASE'));
		$where['_logic'] = 'or';
		$map['_complex'] = $where;
		$map['audit'] = 1;
		$map['status'] = array('neq', 1);
		$result = $this->where($map)->order(array('isadminrecommend' => 'desc', 'hot' => 'desc'))->limit(C('INDEX_HOT_ACTIVITY_COUNT'))->select();
		return $result;
	}

	/**
	 * 获取热门活动
	 * @return mixed
	 */
	public function getHotPromoteByFid($fid) {
		$result = $this->where(array('fid' => $fid, 'ishot' => 1))->select();
		return $result;
	}

	/**
	 * @param $condition
	 * 根据条件获取活动
	 */
	public function getActivityByCondition($condition, $start, $end) {
		$result = $this->where($condition)->order("FIND_IN_SET(`status`,'4,3,2,5,6,7'),case when `status`=2 then holdend  else holdstart end")->limit($start, $end)->select();
		return $result;
	}

	public function getActivityCountByCondition($condition) {
		$result = $this->where($condition)->count();
		return $result;
	}

	public function getProcessingActivities($fid) {
		$result = $this->where(array('fid' => $fid, 'status' => 4))->order(array('addtime' => 'asc'))->select();
		return $result;
	}

	public function getEnrollingActivities($fid) {
		$result = $this->where(array('fid' => $fid, 'status' => 2))->order(array('addtime' => 'asc'))->select();
		return $result;
	}

	public function getBeginingActivities($fid) {
		$result = $this->where(array('fid' => $fid, 'status' => 3))->order(array('addtime' => 'asc'))->select();
		return $result;
	}

	public function getEndActivities($fid) {
		$result = $this->where(array('fid' => $fid, 'status' => 5))->order(array('addtime' => 'desc'))->select();
		return $result;
	}

	public function getCanceldActivities($fid) {
		$result = $this->where(array('fid' => $fid, 'status' => 6))->order(array('addtime' => 'asc'))->select();
		return $result;
	}

	public function getNotHoldActivities($fid) {
		$result = $this->where(array('fid' => $fid, 'status' => 7))->order(array('addtime' => 'asc'))->select();
		return $result;
	}

	public function getAuditActivity($fid) {
		$result = $this->where(array('fid' => $fid, 'audit' => 0))->order(array('addtime' => 'desc'))->select();
		return $result;
	}

	public function getOtherHotActivity($id, $fid, $length) {
		$condition['id'] = array('neq', $id);
		$condition['status'] = 0;
		$condition['fid'] = array('eq', $fid);
		$condition['hot'] = array('gt', C('HOT_TOPIC_BASE'));
		$result = $this->where($condition)->order(array('hot' => 'desc'))->limit($length)->select();
		return $result;
	}

	public function getOtherCurrentActivity($id, $fid, $length) {
		$condition['id'] = array('neq', $id);
		$condition['holdend'] = array('gt', time());
		$condition['fid'] = array('eq', $fid);
		$result = $this->where($condition)->order(array('holdend' => 'desc'))->limit($length)->select();
		return $result;
	}

}
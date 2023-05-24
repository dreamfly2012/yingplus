<?php
/**
 * Created by PhpStorm.
 * User: dreamfly
 * Date: 2015/8/7
 * Time: 11:49
 */

namespace Home\Model;

class TopicModel extends CommonModel {
	public function getAllInfoByFid($fid) {
		$result = $this->where(array('fid' => $fid))->select();
		return $result;
	}

	public function getNormalInfoByFid($fid) {
		$result = $this->where(array('fid' => $fid, 'status' => 0))->select();
		return $result;
	}

	public function getTopicInfoById($id) {
		$result = $this->where(array('id' => $id))->select();
		return $result[0];
	}

	/**
	 * @param $id 排除的话题id
	 * @param $length 限制选取的长度
	 * @param $fid 话题所属星吧
	 * @return 返回其他热门话题
	 */
	public function getOtherHotTopic($id, $fid, $length) {
		$condition['id'] = array('neq', $id);
		$condition['status'] = 0;
		$condition['fid'] = array('eq', $fid);
		$condition['hot'] = array('gt', C('HOT_TOPIC_BASE'));
		$result = $this->where($condition)->order(array('hot' => 'desc'))->limit($length)->select();
		return $result;
	}

	/**
	 * 删除话题
	 */
	public function deleteTopicById($tid) {
		$result = $this->where(array('id' => $tid))->setField(array('status' => 1));
		return $result;
	}

	/**
	 * 恢复话题
	 */
	public function recoveryTopicById($tid) {
		$result = $this->where(array('tid' => $tid))->setField(array('status' => 0));
		return $result;
	}

	/**
	 * 获取平台热门话题(所有话题)
	 * 算法按照创建时间进行排序
	 * @return mixed
	 */
	public function getHotPlatformTopic() {
		//$where['hot'] = array('gt', C('INDEX_TOPIC_HOT_VALUE'));
		$where['status'] = array('eq', 0);
		$result = $this->where($where)->order(array('addtime' => 'desc'))->limit(0, C('INDEX_HOT_TOPIC_COUNT'))->select();
		return $result;
	}

	public function getHotPlatformTopicList($start) {
		//$where['hot'] = array('gt', C('INDEX_TOPIC_HOT_VALUE'));
		$where['status'] = array('eq', 0);
		$result = $this->where($where)->order(array('addtime' => 'desc'))->limit($start * C('INDEX_HOT_TOPIC_COUNT'), C('INDEX_HOT_TOPIC_COUNT'))->select();
		return $result;
	}

	/**
	 * 获取热门话题(热门话题，热度话题)
	 * @return mixed
	 */
	public function getHotPromoteByFid($fid) {
		$condition['hot'] = array('gt', C('HOT_TOPIC_BASE'));
		$result = $this->where(array('fid' => $fid, 'hot'))->select();
		return $result;
	}

	/**
	 * @param $condition
	 * 根据条件获取话题
	 */
	public function getTopicByCondition($condition, $start, $end) {
		$result = $this->where($condition)->limit($start, $end)->order(array('addtime' => 'desc'))->select();
		return $result;
	}

	//获取其他话题
	public function getOtherCurrentTopic($id, $fid, $length) {
		$condition['id'] = array('neq', $id);
		$condition['fid'] = array('eq', $fid);
		$result = $this->where($condition)->order(array('addtime' => 'desc'))->limit($length)->select();
		return $result;
	}
}
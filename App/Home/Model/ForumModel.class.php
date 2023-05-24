<?php

namespace Home\Model;
use Think\Model;

class ForumModel extends CommonModel {
	/**
	 * @param $id
	 * @return mixed
	 * 获取星吧名称
	 */
	public function getForumNameById($id) {
		$result = $this->where(array('id' => $id))->getField('name');
		return $result;
	}

	public function getFansgroupById($id) {
		$result = $this->where(array('id' => $id))->getField('fansgroup');
		return $result;
	}
	/**
	 * @param $id
	 * @return mixed
	 * 根据id获取星吧信息
	 */
	public function getForumInfoById($id) {
		$result = $this->where(array('id' => $id))->find();
		return $result;
	}

	public function getMaxId() {
		$result = $this->where(array('status' => 1))->max('id');
		return $result;
	}

	public function getMinId() {
		$result = $this->where(array('status' => 1))->min('id');
		return $result;
	}

	//随机获取星吧
	public function getRandomForum() {
		$maxid = $this->getMaxId();
		$rand = rand(1, $maxid - 4);
		$map['id'] = array('gt', $rand);
		$map['status'] = 1;
		$result = $this->where($map)->limit(C('INDEX_FORUM_NUM'))->select();
		return $result;
	}

	//获取管理员推荐星吧
	public function getAdminPromoteForum() {
		$map['is_admin_promote'] = 1;
		$map['status'] = 1;
		$result = $this->where($map)->limit(C('INDEX_FORUM_PROMOTE_NUM'))->order(array('displayorder' => 'desc'))->select();
		return $result;
	}

	public function getFollowForumByUid($uid) {
		$forumUserModel = D('ForumUser');
		$result = $forumUserModel->field('fid')->where(array('uid' => $uid))->select();
		return $result;
	}

	public function getInfoByKeyword($keyword, $p, $pagenum) {
		$map['name'] = array('like', '%' . $keyword . '%');
		$map['chinesename'] = array('like', '%' . $keyword . '%');
		$map['koreaname'] = array('like', '%' . $keyword . '%');
		$map['englishname'] = array('like', '%' . $keyword . '%');
		$map['pinyin'] = array('like', '%' . $keyword . '%');
		$map['aliasname'] = array('like', '%' . $keyword . '%');
		$map['stagename'] = array('like', '%' . $keyword . '%');
		$map['fansgroup'] = array('like', '%' . $keyword . '%');
		$map['groupname'] = array('like', '%' . $keyword . '%');
		$map['_logic'] = 'OR';
		$result = $this->where($map)->order(array('id' => 'asc'))->limit(($p - 1) * $pagenum, $pagenum)->select();
		return $result;
	}

	//获取热门星吧
	public function getHotForum() {
		$result = $this->where(array('is_admin_promote' => 1))->select();
		return $result;
	}
}
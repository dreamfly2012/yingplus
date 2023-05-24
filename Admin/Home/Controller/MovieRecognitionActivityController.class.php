<?php

namespace Home\Controller;

class MovieRecognitionActivityController extends CommonController {
	//认领包场看电影
	public function index() {
		$forum = D('Forum');
		$forums = $forum->where(array('status' => 1))->select();
		$this->assign('forums', $forums);

		$recognitionActivityModel = D('RecognitionActivity');
		$count = $recognitionActivityModel->where(array('status' => array('eq', 0)))->count();
		$Page = new \Think\Page($count, C('PAGE_LISTROWS'));
		parent::setPageConfig($Page);
		$show = $Page->show();
		$activityList = $recognitionActivityModel->where(array('status' => array('eq', 0)))->select();
		$this->assign('page', $show);
		$this->assign('activityList', $activityList);

		$this->display('index');

	}

	public function filterActivity() {
		$fid = I('request.fid', null, 'intval');
		$this->assign('fid', $fid);
		$recognitionActivityModel = D('RecognitionActivity');
		$forum = D('Forum');
		$forums = $forum->where(array('status' => 1))->select();
		$this->assign('forums', $forums);
		$count = $recognitionActivityModel->where(array('fid' => $fid, 'status' => array('eq', 0)))->count();
		$Page = new \Think\Page($count, C('PAGE_LISTROWS'));
		parent::setPageConfig($Page);
		$show = $Page->show();
		$activityList = $recognitionActivityModel->where(array('fid' => $fid, 'status' => array('eq', 0)))
			->limit($Page->firstRow . ',' . $Page->listRows)
			->order('holdstart desc')
			->select();
		$this->assign('page', $show);
		$this->assign('activityList', $activityList);
		$this->display('index');
	}

	public function addActivity() {
		$forum = D('Forum');
		$forums = $forum->where(array('status' => 1))->select();
		$this->assign('forums', $forums);
		$movieOptionalPlaceModel = D('MovieOptionalPlace');
		$pids = $movieOptionalPlaceModel->field('pid')->where(array('status' => 0))->group('pid')->select();
		$provinces = array();
		foreach ($pids as $key => $val) {
			$provinces[$key]['id'] = $val['pid'];
			$provinces[$key]['name'] = getDistrictNameById($val['pid']);
		}
		$this->assign('provinces', $provinces);
		$this->display('addActivity');
	}

	//获取工作室上线中的电影
	public function getMoviesByFid() {
		$fid = I('request.fid', null, 'intval');
		$forumMovieModel = D('ForumMovie');
		$movies = $forumMovieModel->where(array('fid' => $fid, 'status' => 1))->select();
		$this->ajaxReturn($movies);
	}

	//获取省份下的可选城市
	public function getCitiesByPid() {
		$pid = I('request.pid', null, 'intval');
		$movieOptionalPlaceModel = D('MovieOptionalPlace');
		$cids = $movieOptionalPlaceModel->field('selfid')->where(array('status' => 0, 'pid' => $pid))->select();
		$cities = array();
		foreach ($cids as $key => $val) {
			$cities[$key]['id'] = $val['selfid'];
			$cities[$key]['name'] = getDistrictNameById($val['selfid']);
		}
		$this->ajaxReturn($cities);
	}

	//获取城市下的可选影院
	public function getCinemasByCid() {
		$cid = I('request.cid', null, 'intval');
		$moviePlaceModel = D('MoviePlace');
		$cinemas = $moviePlaceModel->where(array('cid' => $cid))->select();
		$this->ajaxReturn($cinemas);
	}

	public function add() {
		$fid = I('post.fid', null, 'intval');
		$mid = I('post.mid', null, 'intval');
		$cinema = I('post.cinema', null, 'intval');
		$province = I('post.province', null, 'intval');
		$city = I('post.city', null, 'intval');
		$detailaddress = I('post.detailaddress', null);
		$holdstart_date = I('post.holdstart_date', null);
		$holdstart_time = I('post.holdstart_time', null);
		$holdstart = strtotime($holdstart_date . $holdstart_time);

		$enrollendtime = I('post.enrollendtime', null, 'strtotime');
		$price = I('post.price', null);
		$enrolltotal = I('post.enrolltotal', null, 'intval');

		$rules = array(
			array('holdstart', 'require', '开始时间必须填写'),
			array('enrollendtime', 'require', '报名截止时间必须填写'),
			array('price', 'require', '价钱必须填写'),
			array('price', 'currency', '价钱不是合法值'),
			array('enrolltotal', 'require', '人数必须填写'),
			array('enrolltotal', 'integer', '人数不是合法值'),

		);

		$arr = array(
			'fid' => $fid,
			'mid' => $mid,
			'cinema' => $cinema,
			'province' => $province,
			'city' => $city,
			'holdstart' => $holdstart,
			'enrollendtime' => $enrollendtime,
			'price' => $price,
			'enrolltotal' => $enrolltotal,
			'detailaddress' => $detailaddress,
		);
		$recognitionActivityModel = D('RecognitionActivity');
		if (is_null($cinema)) {
			$this->error('影院必须填写');
		}

		if (!$recognitionActivityModel->validate($rules)->create($arr)) {
			$this->error($recognitionActivityModel->getError());
		} else {
			$recognitionActivityModel->add($arr);
			$this->redirect('MovieRecognitionActivity/index');
		}
	}

	//活动编辑页面
	public function editActivity() {
		$id = I('request.id', null, 'intval');
		$forum = D('Forum');
		$forums = $forum->where(array('status' => 1))->select();
		$this->assign('forums', $forums);
		$movieOptionalPlaceModel = D('MovieOptionalPlace');
		$pids = $movieOptionalPlaceModel->field('pid')->where(array('status' => 0))->group('pid')->select();
		$provinces = array();
		foreach ($pids as $key => $val) {
			$provinces[$key]['id'] = $val['pid'];
			$provinces[$key]['name'] = getDistrictNameById($val['pid']);
		}
		$this->assign('provinces', $provinces);

		$recognitionActivityModel = D('RecognitionActivity');
		$activity = $recognitionActivityModel->where(array('id' => $id))->find();
		$this->assign('activity', $activity);
		$this->display('editActivity');
	}

	public function edit() {
		$id = I('post.id', null, 'intval');
		$fid = I('post.fid', null, 'intval');
		$mid = I('post.mid', null, 'intval');
		$cinema = I('post.cinema', null, 'intval');
		$province = I('post.province', null, 'intval');
		$city = I('post.city', null, 'intval');
		$detailaddress = I('post.detailaddress', null);
		$holdstart_date = I('post.holdstart_date', null);
		$holdstart_time = I('post.holdstart_time', null);
		$holdstart = strtotime($holdstart_date . $holdstart_time);
		$enrollendtime = I('post.enrollendtime', null, 'strtotime');
		$price = I('post.price', null);
		$enrolltotal = I('post.enrolltotal', null, 'intval');

		$rules = array(
			array('holdstart', 'require', '开始时间必须填写'),
			array('enrollendtime', 'require', '报名截止时间必须填写'),
			array('price', 'require', '价钱必须填写'),
			array('price', 'currency', '价钱不是合法值'),
			array('enrolltotal', 'require', '人数必须填写'),
			array('enrolltotal', 'integer', '人数不是合法值'),
		);

		$arr = array(
			'id' => $id,
			'fid' => $fid,
			'mid' => $mid,
			'cinema' => $cinema,
			'province' => $province,
			'city' => $city,
			'holdstart' => $holdstart,
			'enrollendtime' => $enrollendtime,
			'price' => $price,
			'enrolltotal' => $enrolltotal,
			'detailaddress' => $detailaddress,
		);
		$recognitionActivityModel = D('RecognitionActivity');
		if (is_null($cinema)) {
			$this->error('影院必须填写');
		}

		if (!$recognitionActivityModel->validate($rules)->create($arr)) {
			$this->error($recognitionActivityModel->getError());
		} else {
			$recognitionActivityModel->where(array('id' => $id))->save($arr);
			$this->success('修改成功');
		}
	}

	public function delActivity() {
		$id = I('request.id', null, 'intval');
		$recognitionActivityModel = D('RecognitionActivity');
		$recognitionActivityModel->where(array('id' => $id))->setField(array('status' => 1));
		$this->redirect('MovieRecognitionActivity/index');
	}

	public function deleteAllActivity() {
		$arr = I('post.test');
		$recognitionActivityModel = D('RecognitionActivity');
		for ($i = 0; $i < count($arr); $i++) {
			$recognitionActivityModel->where(array('id' => $arr[$i]))->setField(array('status' => 1));
		}
	}
}
<?php

namespace Home\Controller;

class MoviePlaceController extends CommonController {
	//电影影院管理
	public function index() {
		$moviePlaceModel = D('MoviePlace');
		$movieOptionalPlaceModel = D('MovieOptionalPlace');
		$pids = $movieOptionalPlaceModel->field('pid')->where(array('status'=>0))->group('pid')->select();
		$provinces = array();
		foreach($pids as $key=>$val){
			$provinces[$key]['id'] = $val['pid'];
			$provinces[$key]['name'] = getDistrictNameById($val['pid']);
		}
		$this->assign('provinces',$provinces);
		$count = $moviePlaceModel->where(array('status' => 0))->count();
		$Page = new \Think\Page($count, C('PAGE_LISTROWS'));
		parent::setPageConfig($Page);
		$show = $Page->show();
		$places = $moviePlaceModel->where(array('status' => 0))->select();
		$this->assign('page', $show);
		$this->assign('places', $places);
		$this->display('index');
	}

	public function filterCinema() {
		$pid = I('request.pid', null, 'intval');
		$cid = I('request.cid',null,'intval');
		$this->assign('pid', $pid);
		$this->assign('cid', $cid);
		$moviePlaceModel = D('MoviePlace');
		$districtModel = D('District');
		$provinces = $districtModel->getAllProvinces(); //得到所有省的数据
		$this->assign('provinces', $provinces);
		$count = $moviePlaceModel->where(array('cid'=>$cid))->count();
		$Page = new \Think\Page($count, C('PAGE_LISTROWS'));
		parent::setPageConfig($Page);
		$show = $Page->show();
		$places = $moviePlaceModel->where(array('cid'=>$cid))
			->limit($Page->firstRow . ',' . $Page->listRows)
			->order('addtime desc')
			->select();
		$this->assign('page', $show);
		$this->assign('places', $places);
		$this->display('index');
	}

	public function addCinema() {
		$districtModel = D('District');
		$provinces = $districtModel->getAllProvinces(); //得到所有省的数据
		$this->assign('provinces', $provinces);
		$this->display('addPlace');
	}

	public function add() {
		$title = I('post.title', null, '');
		$pid = I('post.pid', null, '');
		$cid = I('post.cid', null, '');
		$addtime = time();
		$arr = array(
			'title' => $title,
			'pid' => $pid,
			'cid' => $cid,
			'addtime' => $addtime
		);
		$moviePlaceModel = D('MoviePlace');
		$moviePlaceModel->add($arr);
		$this->redirect('MoviePlace/index');
	}

	public function editCinema() {
		$id = I('request.id', null, 'intval');
		$moviePlaceModel = D('MoviePlace');
		$cinema = $moviePlaceModel->where(array('id' => $id))->find();
		$this->assign('cinema', $cinema);
		$districtModel = D('District');
		$provinces = $districtModel->getAllProvinces(); //得到所有省的数据
		$this->assign('provinces', $provinces);
		$this->display('editPlace');
	}

	public function edit() {
		$id = I('post.id', null, 'intval');
		$title = I('post.title', null, '');
		$pid = I('post.pid', null, '');
		$cid = I('post.cid', null, '');
		$arr = array(
			'title' => $title,
			'pid' => $pid,
			'cid' => $cid
		);

		$moviePlaceModel = D('MoviePlace');
		$moviePlaceModel->where(array('id' => $id))->save($arr);
		$this->redirect('MoviePlace/index');
	}

	public function delCinema() {
		$id = I('request.id', null, 'intval');
		$moviePlaceModel = D('MoviePlace');
		$moviePlaceModel->where(array('id' => $id))->setField(array('status' => 1));
		$this->redirect('MoviePlace/index');
	}

	public function deleteAllCinema() {
		$arr = I('post.test');
		$moviePlaceModel = D('MoviePlace');
		for ($i = 0; $i < count($arr); $i++) {
			$moviePlaceModel->where(array('id' => $arr[$i]))->setField(array('status' => 1));
		}
	}

	//获取省份下的可选城市
	public function getCitiesByPid(){
		$pid = I('request.pid',null,'intval');
		$movieOptionalPlaceModel = D('MovieOptionalPlace');
		$cids = $movieOptionalPlaceModel->field('selfid')->where(array('status'=>0,'pid'=>$pid))->select();
		$cities = array();
		foreach($cids as $key=>$val){
			$cities[$key]['id'] = $val['selfid'];
			$cities[$key]['name'] = getDistrictNameById($val['selfid']);
		}
		$this->ajaxReturn($cities);
	}

}
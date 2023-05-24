<?php

namespace Home\Controller;

class PlaceController extends CommonController {
	public function get() {
		$type = I('request.type', null);
		switch ($type) {
		case 'cinema':
			$this->getCinemaByPlace();
			break;
		case 'city':
			$this->getCityByProvince();
		}
	}

	//获取影院信息
	public function getCinemaByPlace() {
		$pid = I('request.pid', null, 'intval');
		$cid = I('request.cid', null, 'intval');

		if (empty($cid) || empty($pid)) {
			$info = null;
			$code = -1;
			$message = C('parameter_invalid');
			$return = $this->buildReturn($info, $code, $message);
			$this->ajaxReturn($return);
		}

		$moviePlaceModel = D('MoviePlace');
		$cinemas = $moviePlaceModel->where(array('pid' => $pid, 'cid' => $cid, 'status' => 0))->select();
		$info = $cinemas;
		$code = 0;
		$message = '影院信息';
		$return = $this->buildReturn($info, $code, $message);
		$this->ajaxReturn($return);
	}

	//根据省份返回城市
	/**
	 *
	 */
	public function getCityByProvince() {
		$pid = I('request.pid', null);
		if (empty($pid)) {
			$info = null;
			$code = -1;
			$message = C('parameter_invalid');
			$return = $this->buildReturn($info, $code, $message);
			$this->ajaxReturn($return);
		}
		$districtModel = D("District");
		$cities = $districtModel->getCityByProvince($pid);
		$info = $cities;
		$code = 0;
		$message = "城市信息";
		$return = $this->buildReturn($info, $code, $message);
		$this->ajaxReturn($return);
	}
}
<?php

namespace Home\Controller;

class DistrictController extends CommonController {
	//根据省份获取城市
	public function getCityByProvince() {
		$districtModel = D('District');
		$pid = I('request.pid',null,'intval');

		if(empty($pid)){
			$info = null;
			$code = -1;
			$message = C('parameter_invalid');
			$return = $this->buildReturn($info,$code,$message);
			$this->ajaxReturn($return);
		}

		$cities = $districtModel->where(array('upid'=>$pid,'level'=>2))->select();
		$info = $cities;
		$code = 0;
		$message = '城市信息';
		$return  = $this->buildReturn($info,$code,$message);
		$this->ajaxReturn($return);
	}

	//根据城市获取省份
	public function getProvinceByCity(){
		$districtModel = D('District');
		$cid = I('request.cid',null,'intval');

		if(empty($cid)){
			$info = null;
			$code = -1;
			$message = C('parameter_invalid');
			$return = $this->buildReturn($info,$code,$message);
			$this->ajaxReturn($return);
		}

		$upid = $districtModel->getFieldById($cid,'upid');
		$province = $districtModel->where(array('upid'=>$upid))->find();
		$info = $province;
		$code = 0;
		$message = '省份信息';
		$return  = $this->buildReturn($info,$code,$message);
		$this->ajaxReturn($return);
	}

	

	
}
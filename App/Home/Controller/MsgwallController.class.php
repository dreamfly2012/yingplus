<?php

namespace Home\Controller;

class MsgwallController extends CommonController
{
	public function adddo(){
		$data['content'] = I('request.content','','trim');
		$data['addtime'] = time();
		$data['ip'] = get_client_ip();
		$msgwallModel = D('Msgwall');
		$bool = $msgwallModel->add($data);
		if($bool){
			$this->ajaxReturn(array('msg'=>'true'));
		}else{
			$this->ajaxReturn(array('msg'=>'false'));
		}


	}
}
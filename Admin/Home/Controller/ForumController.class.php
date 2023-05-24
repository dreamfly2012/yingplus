<?php

namespace Home\Controller;

class ForumController extends CommonController{
	public function info(){
		$fid = I('request.fid',null,'intval');
		
		if(empty($fid)){
			$info = null;
			$code = -1;
			$message = C('parameter_invalid');
			$return = $this->buildReturn($info, $code, $message);
			$this->ajaxReturn($return);
		}

		$condition['fid'] = $fid;
		$condition['status'] = 1;
		$forumModel = D('Forum');
		$forum = $forumModel->where($condition)->find();
		return $forum;
	}

	public function getinfo(){
		$forum = $this->info();
		$info = $fourm;
		$code = 0;
		$message = '工作室信息';
		$return = $this->buildReturn($info, $code, $message);
		$this->ajaxReturn($return);
	}

	public function listing($p,$number){
		$condition['status'] = 1;
		$forumModel = D('Forum');
		$forums = $forumModel->where($condition)->limit(($p-1)*$number,$number)->select();
		return $forums;
	}

	public function getlisting(){
		$p = I('request.p',1,'intval');
		$number = I('request.number',10,'intval');
		$forums = $this->listing($p,$number);
		$info = $forums;
		$code = 0;
		$message = '工作室信息';
		$return = $this->buildReturn($info, $code, $message);
		$this->ajaxReturn($return);
	}

}
<?php

namespace Home\Controller;

class ResponseController extends CommonController {
	//列表
	public function listing($p,$number,$pid,$type,$order='id',$sort='desc'){
		$count = 0;

		if (empty($type) || empty($pid)) {
			$info = null;
			$code = -1;
			$message = C('parameter_invalid');
			$return = $this->buildReturn($info, $code, $message);
			$this->ajaxReturn($return);
		}
		
		$ResponseModel = D('Response');

		$count = $ResponseModel->where(array('status' => 0, 'pid' => $pid,'type'=>$type))->count();
		$responses = $ResponseModel->where(array('status' => 0, 'pid' => $pid,'type'=>$type))->order(array($order => $sort))->limit(($p-1)*$number,$number)->select();
		
		$Page = new \Think\AjaxPage($count,$number,'ajax_response_page');
		$show = $Page->show();

		foreach ($responses as $key => $val) {
			$responses[$key]['userurl'] = U('Index/space', array('uid' => $val['uid']));
			$responses[$key]['userphoto'] = buildImgUrl(getUserPhotoById($val['uid']));
			$responses[$key]['username'] = getUserNicknameById($val['uid']);
			$responses[$key]['total'] = $count;
			$rid = $responses[$key]['rid'];
			if(!empty($rid)){
				$to_response = $ResponseModel->where(array('id'=>$rid))->find();
				$to_response_content = $to_response['content'];
				$responses[$key]['content'] = $responses[$key]['content'].'<br>'.$to_response_content;
			}
		}
		$info['data'] = $responses;
		$info['page'] = $show;
		return $info;

	}

	//获取评论列表
	public function getlisting(){
		$type = I('request.type', null,'intval');
		$pid = I('request.pid', null, 'intval');
		$number = I('request.number', 10, 'intval');
		$number = ($number>50) ? 50 : $number;
		$p = I('request.p',1,'intval');
		$responses = $this->listing($p,$number,$pid,$type);
		$info = $responses;
		$code = 0;
		$message = '讨论回复';
		$return = $this->buildReturn($info, $code, $message);
		$this->ajaxReturn($return);
	}

	//分页
	public function reseponsepage(){
		$p = I('request.p',1,'intval');
		$pid = I('request.pid');
		$number = I('request.number',10,'intval');
		$type = I('request.type',1,'intval');
		$responses = $this->listing($p,$number,$pid,$type);
        $this->assign('responses',$responses);
        $this->display('pc/response_page');
	}

	//获取回复
	public function get() {
		$type = I('request.type', null,'intval');
		$pid = I('request.pid', null, 'intval');
		$number = I('request.number', 10, 'intval');
		$number = ($number>50) ? 50 : $number;
		$lastid = I('request.lastid', null, 'intval');
		$p = I('request.p',1,'intval');
		$count = 0;
		if (empty($type) || empty($pid)) {
			$info = null;
			$code = -1;
			$message = C('parameter_invalid');
			$return = $this->buildReturn($info, $code, $message);
			$this->ajaxReturn($return);
		}
		
		$ResponseModel = D('Response');
		if (is_null($lastid)) {
			$responses = $ResponseModel->where(array('status' => 0, 'pid' => $pid,'type'=>$type))->order(array('id' => 'desc'))->limit(($p-1)*$number,$number)->select();
			$count = $ResponseModel->where(array('status' => 0, 'pid' => $pid))->count();
		} else {
			$responses = $ResponseModel->where(array('status' => 0, 'pid' => $pid, 'type'=>$type,'id' => array('gt', $lastid)))->order(array('id' => 'asc'))->limit(($p-1)*$number,$number)->select();
			$count = $ResponseModel->where(array('status' => 0, 'pid' => $pid, 'type'=>$type,'id' => array('gt', $lastid)))->count();
		}

		foreach ($responses as $key => $val) {
			$responses[$key]['userurl'] = U('Index/space', array('uid' => $val['uid']));
			$responses[$key]['userphoto'] = buildImgUrl(getUserPhotoById($val['uid']));
			$responses[$key]['username'] = getUserNicknameById($val['uid']);
			$responses[$key]['total'] = $count;
		}
		$info = $responses;
		$code = 0;
		$message = '讨论回复';
		$return = $this->buildReturn($info, $code, $message);
		$this->ajaxReturn($return);
	}

	public function temp_add(){
		$type = I('request.type', 1,'intval');
		$fid = I('request.fid', 16, 'intval');
		$pid = I('request.pid',null,'intval');
		$rid = I('request.rid',0,'intval');
		$content = I('request.content', null);
		$nickname = I('request.username',null);
		$addtime = time();

		if(empty($nickname)){
			$info = 'parameter_invalid';
			$code = 1;
			$message = '用户名不能为空';
			$return = $this->buildReturn($info,$code,$message);
			$this->ajaxReturn($return);
		}

		if (empty($type) || empty($fid) || empty($pid) ||empty($content)) {
			$info = null;
			$code = -1;
			$message = C('parameter_invalid');
			$return = $this->buildReturn($info, $code, $message);
			$this->ajaxReturn($return);
		}


		$userModel = D('User');
		$user = $userModel->where(array('telephone'=>$nickname))->find();
		$uid = $user['id'];

		$ResponseModel = D('Response');
		$data = array(
			'pid' => $pid,
			'fid' => $fid,
			'uid' => $uid,
			'rid' => $rid,
			'type'=> $type,
			'addtime' => $addtime,
			'content' => htmlspecialchars_decode($content)
		);

		$response_id = $ResponseModel->add($data);

		if($response_id>0){
			$code = 0;
			$message = '添加成功';
			$return = $this->buildReturn($info, $code, $message);
			$this->ajaxReturn($return);
		}else{
			$code = 1;
			$message = '添加失败';
			$return = $this->buildReturn($info, $code, $message);
			$this->ajaxReturn($return);
		}


	}

	//添加回复
	public function add() {
		$type = I('request.type', null,'intval');
		$fid = I('request.fid', null, 'intval');
		$pid = I('request.pid',null,'intval');
		$rid = I('request.rid',null,'intval');
		$content = I('request.content', null);
		$category = I('request.cateogry',null);
		$addtime = time();
		$uid = $this->getUid();

		if (!$this->checkLogin()) {
			$info = null;
			$code = 2;
			$message = C('no_login');
			$return = $this->buildReturn($info, $code, $message);
			$this->ajaxReturn($return);
		}

		
		if (empty($type) || empty($fid) || empty($pid) ||empty($content)) {
			$info = null;
			$code = -1;
			$message = C('parameter_invalid');
			$return = $this->buildReturn($info, $code, $message);
			$this->ajaxReturn($return);
		}

		$ResponseModel = D('Response');
		$data = array(
			'pid' => $pid,
			'fid' => $fid,
			'uid' => $uid,
			'rid' => $rid,
			'type'=> $type,
			'addtime' => $addtime,
			'content' => htmlspecialchars_decode($content)
		);

		$response_id = $ResponseModel->add($data);
		$data['id'] = $response_id;
		$data['username'] = getUserNicknameById($uid);
		$data['userphoto'] = buildImgUrl(getUserPhotoById($uid));
		$data['format_time'] = date('Y-m-d H:i',$addtime);

		$userModel = D('User');
		$responseUserModel = D('ResponseUser');
		preg_match_all("#@[^ |@]*#", $content, $match);
		
		if (!empty($match[0])) {
			foreach ($match[0] as $key => $val) {
				$username = str_replace('@', '', $val);
				$user = $userModel->where(array('nickname'=>$username))->find();
				$touid = $user['id'];
				
				if (!empty($touid) && $response_id) {
					$user_data['response_id'] = $response_id;
					$user_data['uid'] = $touid;
					$user_data['type'] = $type;
					$responseUserModel->add($user_data);
				}
			}
		}

		if($category=='pc_html'){

			$response = $ResponseModel->where(array('id'=>$response_id))->select();
			if(!empty($rid)){
				$to_response = $ResponseModel->where(array('id'=>$rid))->find();
				$to_response_content = $to_response['content'];
				$response[0]['content'] = $response[0]['content'].'<br>'.$to_response_content;
			}

			$response[0]['userurl'] = U('Index/space', array('uid' => $val['uid']));
			$response[0]['userphoto'] = buildImgUrl(getUserPhotoById($val['uid']));
			$response[0]['username'] = getUserNicknameById($val['uid']);
			$response[0]['total'] = 1;
			$res['data'] = $response; 
			$this->assign('responses',$res);

			$info = $this->fetch('pc/response_page');
			$code = 0;
			$message = '添加成功';
			$return = $this->buildReturn($info, $code, $message);
			$this->ajaxReturn($return);
		}else{
			$info = $data;
			$code = 0;
			$message = '添加成功';
			$return = $this->buildReturn($info, $code, $message);
			$this->ajaxReturn($return);
		}

		
	}

	//删除回复
	public function delete(){
		$id = I('request.id',null,'intval');
		$uid = $this->getUid();
		if(empty($id)){
			$info = null;
			$code = -1;
			$message = C('parameter_invalid');
			$return = $this->buildReturn($info,$code,$message);
			$this->ajaxReturn($return);
		}
		if(!$this->checkLogin()){
			$info = null;
			$code = 2;
			$message = C('no_login');
			$return = $this->buildReturn($info,$code,$message);
			$this->ajaxReturn($return);
		}

		$responseModel = D('Response');
		$result = $responseModel->where(array('id'=>$id,'uid'=>$uid))->setField(array('status'=>1));
		if($result==false){
			$info = null;
			$code = 1;
			$message = "删除失败";
			$return = $this->buildReturn($info,$code,$message);
			$this->ajaxReturn($return);
		}

		$info = null;
		$code = 0;
		$message = '删除成功';
		$return = $this->buildReturn($info,$code,$message);
		$this->ajaxReturn($return);
	}

	//恢复回复
	public function restore(){
		$id = I('request.id',null,'intval');
		$uid = $this->getUid();
		if(empty($id)){
			$info = null;
			$code = -1;
			$message = C('parameter_invalid');
			$return = $this->buildReturn($info,$code,$message);
			$this->ajaxReturn($return);
		}
		if(empty($uid)){
			$info = null;
			$code = -1;
			$message = C('no_login');
			$return = $this->buildReturn($info,$code,$message);
			$this->ajaxReturn($return);
		}

		$responseModel = D('Response');
		$result = $responseModel->where(array('id'=>$id,'uid'=>$uid))->setField(array('status'=>0));

		if($result==false){
			$info = null;
			$code = 1;
			$message = "还原失败";
			$return = $this->buildReturn($info,$code,$message);
			$this->ajaxReturn($return);
		}

		$info = null;
		$code = 0;
		$message = '还原成功';
		$return = $this->buildReturn($info,$code,$message);
		$this->ajaxReturn($return);
	}

}
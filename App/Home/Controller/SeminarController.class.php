<?php

namespace Home\Controller;

//专题页
class SeminarController extends CommonController {
	//展示专题页
	public function index() {
		$mid = I('request.mid', '1', 'intval');
		$this->assign('mid', $mid);
		$this->display('Index/movie_index');

	}

	//获取首页信息
	public function getIndex() {
		$mid = I('request.mid', null, 'intval');
		if (empty($mid)) {
			$info = null;
			$code = -1;
			$message = C('parameter_invalid');
			$return = $this->buildReturn($info, $code, $message);
			$this->ajaxReturn($return);
		}

		$barrages = $this->getBarrage($mid);
		$data['barrages'] = $barrages;
		$box_office = $this->getBoxoffice($mid);
		$data['box_office'] = $box_office;
		$releasetime = $this->getRelasetime($mid);
		$data['releasetime'] = $releasetime;
		
		$forumMovieModel = D('ForumMovie');
		$movie = $forumMovieModel->where(array('id' => $mid))->find();
		$movie['img'] = buildImgUrl(getAttachmentUrlById($movie['poster']));
		$mobile_banner_arr = explode(',',$movie['mobile_banner']);
		$mobile_banner_url_arr = array();
		foreach($mobile_banner_arr as $key=>$val){
			if(!empty($val)){
				$mobile_banner_url_arr[] = buildImgUrl(getAttachmentUrlById($val));
			}
		}
		
		$movie['mobile_banner_info'] = $mobile_banner_url_arr;
		$data['movie'] = $movie;
		$info = $data;
		$code = 0;
		$message = "首页基本信息";
		$return = $this->buildReturn($info, $code, $message);
		$this->ajaxReturn($return);
	}

	//获取seminar相关信息
	public function getSeminar() {
		$mid = I('request.mid', null, 'intval');
		if (empty($mid)) {
			$info = null;
			$code = -1;
			$message = C('parameter_invalid');
			$return = $this->buildReturn($info, $code, $message);
			$this->ajaxReturn($return);
		}
		$seminarModel = D('Seminar');
		$seminarFavorModel = D('SeminarFavor');
		$forumModel = D('Forum');
		$seminar = $seminarModel->where(array('mid' => $mid))->select();
		$uid = $this->getUid();
		foreach ($seminar as $key => $val) {
			$seminar[$key]['isfavor'] = is_null($seminarFavorModel->checkIsFavor($val['id'], $uid)) ? 0 : 1;
			$seminar[$key]['baochang_num'] = getMovieActivityNumByFidMid($val['fid'], $mid);
			$photo = $forumModel->getFieldById($val['fid'], "photo");
			$seminar[$key]['forum_img'] = buildImgUrl(getAttachmentUrlById($photo));
		}

		$info = $seminar;
		$code = 0;
		$message = "专题页基本信息";
		$return = $this->buildReturn($info, $code, $message);
		$this->ajaxReturn($return);

	}

	//点赞
	public function favorDo() {
		$sid = I('request.sid', null, 'intval');
		$uid = $this->getUid();
		$seminarFavorModel = D('SeminarFavor');
		$seminarModel = D('Seminar');

		if (!$this->checkLogin()) {
			$info = null;
			$code = 2;
			$message = C('no_login');
			$return = $this->buildReturn($info,$code,$message);
			$this->ajaxReturn($return);
		} 

		if(empty($sid)){
			$info = null;
			$code = -1;
			$message = C('parameter_invalid');
			$return = $this->buildReturn($info,$code,$message);
			$this->ajaxReturn($return);
		}


		$bool = $seminarFavorModel->checkHasFavor($sid, $uid);
		if ($bool) {
			$result = $seminarFavorModel->checkIsFavor($sid, $uid);
			if ($result) {
				$seminarFavorModel->where(array('sid' => $sid, 'uid' => $uid))->setField(array('status' => 0));
				$seminarModel->where(array('id' => $sid))->setDec('favors', 1);
				$info = 2;
				$code = 0;
				$message = '取消点赞';
				$return = $this->buildReturn($info,$code,$message);
				$this->ajaxReturn($return);
			} else {
				$seminarFavorModel->where(array('sid' => $sid, 'uid' => $uid))->setField(array('status' => 1));
				$seminarModel->where(array('id' => $sid))->setInc('favors', 1);
				$info = 1;
				$code = 0;
				$message = '点赞成功';
				$return = $this->buildReturn($info,$code,$message);
				$this->ajaxReturn($return);
			}

		} else {
			$result = $seminarFavorModel->add(array('sid' => $sid, 'uid' => $uid));

			if ($result) {
				//统计处理
				$seminarModel->where(array('id' => $sid))->setInc('favors', 1);
				$info = 1;
				$code = 0;
				$message = '点赞成功';
				$return = $this->buildReturn($info,$code,$message);
				$this->ajaxReturn($return);
				$this->ajaxReturn(array('status' => 1, 'content' => '点赞成功'));
			} else {
				$this->ajaxReturn(array('status' => 3, 'content' => '点赞失败'));
			}

		}

		
	}

	//获取弹幕
	public function getComment() {
		$mid = I('request.mid', null, 'intval');
		$uid = $this->getUid();
		if (empty($mid)) {
			$info = null;
			$code = -1;
			$message = C('parameter_invalid');
			$return = $this->buildReturn($info, $code, $message);
			$this->ajaxReturn($return);
		}
		$seminarBarrageModel = D('SeminarBarrage');
		$comments = $seminarBarrageModel->where(array('mid' => $mid, 'status' => 0))->select();
		foreach ($comments as $key => $val) {
			$comments[$key]['self'] = ($val['uid'] == $uid) ? 1 : 0;
		}
		$info = $comments;
		$code = 0;
		$message = '弹幕信息';
		$return = $this->buildReturn($info, $code, $message);
		$this->ajaxReturn($return);
	}

	//获取弹幕最大时间
	public function getCommentMaxtime() {
		$mid = I('request.mid', null, 'intval');
		if (empty($mid)) {
			$info = null;
			$code = -1;
			$message = C('parameter_invalid');
			$return = $this->buildReturn($info, $code, $message);
			$this->ajaxReturn($return);
		}
		$seminarBarrageModel = D('SeminarBarrage');
		$comments = $seminarBarrageModel->where(array('mid' => $mid, 'status' => 0))->order(array('order' => 'desc'))->find();
		$info = $comments['order'];
		$code = 0;
		$message = '弹幕信息';
		$return = $this->buildReturn($info, $code, $message);
		$this->ajaxReturn($return);
	}

	//添加弹幕
	public function addComment() {
		//没有登录
		if (!$this->checkLogin()) {
			$info = null;
			$code = 2;
			$message = C('no_login');
			$return = $this->buildReturn($info, $code, $message);
			$this->ajaxReturn($return);
		}

		$content = I('request.content', null);
		$order = I('request.order', null);
		$mid = I('request.mid', null, 'intval');
		$uid = $this->getUid();

		//参数错误
		if (empty($content) || empty($mid)) {
			$info = null;
			$code = -1;
			$message = C('parameter_invalid');
			$return = $this->buildReturn($info, $code, $message);
			$this->ajaxReturn($return);
		}

		$data['content'] = $content;
		$data['order'] = $order;
		$data['mid'] = $mid;
		$data['uid'] = $uid;
		$data['addtime'] = time();

		$seminarBarrageModel = D("SeminarBarrage");
		$id = $seminarBarrageModel->add($data);
		if ($id) {
			$info = $id;
			$code = 0;
			$message = '添加弹幕成功';
			$return = $this->buildReturn($info, $code, $message);
			$this->ajaxReturn($return);
		} else {
			$info = $seminarBarrageModel->getError();
			$code = 1;
			$message = '添加评论失败';
			$return = $this->buildReturn($info, $code, $message);
			$this->ajaxReturn($return);
		}

	}

	//包场城市信息
	public function getCity() {
		$mid = I('request.mid', null, 'intval');
		$fid = I('request.fid', null, 'intval');
		if (empty($fid) || empty($mid)) {
			$info = null;
			$code = -1;
			$message = C('parameter_invalid');
			$return = $this->buildReturn($info, $code, $message);
			$this->ajaxReturn($return);
		}
		$activityModel = D('Activity');
		$cities = $activityModel->where(array('fid' => $fid, 'audit' => 1, 'status' => 0))->field('holdcity')->select();
		$cities = array_unique_fb($cities);
		foreach ($cities as $key => $val) {
			foreach ($val as $kkey => $vval) {
				$cities[$key]['name'] = getPlaceNameById($vval);
			}
		}
		$info = $cities;
		$code = 0;
		$message = '包场活动城市信息';
		$return = $this->buildReturn($info, $code, $message);
		$this->ajaxReturn($return);
	}

	//模糊查询省份
	public function getBlurryProvince() {
		$keyword = I('request.keyword', null);
		$fid = I('request.fid');
		$mid = I('request.mid');
		if (empty($keyword)) {
			$info = null;
			$code = -1;
			$message = C('parameter_invalid');
			$return = $this->buildReturn($info, $code, $message);
			$this->ajaxReturn($return);
		}
		$districtModel = D('District');
		$activityModel = D('Activity');
		$map['name'] = array('like', $keyword . '%');
		$map['pinyin'] = array('like', $keyword . '%');
		$map['_logic'] = 'OR';
		$condition['_complex'] = $map;
		$condition['level'] = 1;

		$provinces = $districtModel->field('id,name')->where($condition)->select();

		//检查活动中是否有这些省份
		//		foreach ($provinces as $key => $val) {
		//			$activity = $activityModel->where(array('audit' => 1, 'status' => array('neq', 1), 'holdprovince' => $val['id']))->find();
		//			if (empty($activity)) {
		//				unset($provinces[$key]);
		//			}
		//		}

		foreach ($provinces as $key => $val) {
			$provinces[$key]['fid'] = $fid;
			$provinces[$key]['mid'] = $mid;
		}

		$info = $provinces;
		$code = 0;
		$message = '查询到的省份';

		$return = $this->buildReturn($info, $code, $message);
		$this->ajaxReturn($return);
	}

	//查询省份结果
	public function getCinema() {
		$mid = I('request.mid', null, 'intval');
		$fid = I('request.fid', null, 'intval');
		$pid = I('request.pid', null, 'intval');
		//参数错误
		if (empty($fid) || empty($mid) || empty($pid)) {
			$info = null;
			$code = -1;
			$message = C('parameter_invalid');
			$return = $this->buildReturn($info, $code, $message);
			$this->ajaxReturn($return);
		}

		$activityModel = D('Activity');
		$cinemas = $activityModel->where(array('fid' => $fid, 'audit' => 1, 'movie' => $mid, 'holdprovince' => $pid, 'status' => array('neq', 1)))->field('cinemaid,holdprovince,holdcity,enrolltotal')->select();
		foreach ($cinemas as $key => $val) {
			$cinemas[$key]['provincename'] = getPlaceNameById($val['holdprovince']);
			$cinemas[$key]['cityname'] = getPlaceNameById($val['holdcity']);
			$cinemas[$key]['bought_tickets'] = getBoughtTicketById();
		}

		$info = $cinemas;
		$code = 0;
		$message = '包场活动城市信息';
		$return = $this->buildReturn($info, $code, $message);
		$this->ajaxReturn($return);

	}

	//包场省份信息
	public function getProvince() {
		$mid = I('request.mid', null, 'intval');
		$fid = I('request.fid', null, 'intval');
		if (empty($fid) || empty($mid)) {
			$info = null;
			$code = -1;
			$message = C('parameter_invalid');
			$return = $this->buildReturn($info, $code, $message);
			$this->ajaxReturn($return);
		}
		$activityModel = D('Activity');
		$provinces = $activityModel->where(array('fid' => $fid, 'audit' => 1, 'status' => 0))->field('holdprovince')->select();
		$provinces = array_unique_fb($provinces);
		foreach ($provinces as $key => $val) {
			foreach ($val as $kkey => $vval) {
				$provinces[$key]['name'] = getPlaceNameById($vval);
				$provinces[$key]['id'] = $vval;
				$provinces[$key]['fid'] = $fid;
				$provinces[$key]['mid'] = $mid;
			}
		}

		$info = $provinces;
		$code = 0;
		$message = '包场活动城市信息';

		$return = $this->buildReturn($info, $code, $message);
		$this->ajaxReturn($return);
	}

	//获取工作室包场活动信息
	public function getForumActivityInfo() {
		$fid = I('request.fid', null, 'intval');
		$mid = I('request.mid', null, 'intval');
		if (empty($fid) || empty($mid)) {
			$info = null;
			$code = -1;
			$message = C('parameter_invalid');
			$return = $this->buildReturn($info, $code, $message);
			$this->ajaxReturn($return);
		}
		$activityModel = D('Activity');
		$forumMovieModel = D('ForumMovie');
		$activities = $activityModel->where(array('category'=>1,'fid' => $fid, 'mid' => $mid, 'audit' => 1, 'status' => array('neq', 1)))->select();
		foreach ($activities as $key => $val) {
			$activities[$key]['cinemaname'] = getCinemaNameById($val['cinemaid']);
			$activities[$key]['movieimg'] = buildImgUrl(getAttachmentUrlById($val['movie']));
			$activities[$key]['city'] = getPlaceNameById($val['holdcity']);
			$activities[$key]['enrollnum'] = getBoughtTicketByAid($val['id']);
			$activities[$key]['money'] = getTicketPriceByAid($val['id']);
			$activities[$key]['href'] = U('index/moviedetail', array('aid' => $val['id']));
		}
		$info = $activities;

		$code = 0;
		$message = '包场活动信息';

		if (empty($info)) {
			$message = U('Forum/index', array('fid' => $fid));
		}
		$return = $this->buildReturn($info, $code, $message);
		$this->ajaxReturn($return);
	}

	//包场活动信息
	public function activityInfo() {
		$fid = I('request.fid', null, 'intval');
		$mid = I('request.mid', null, 'intval');
		$pid = I('request.pid', null, 'intval');
		if (empty($fid)) {
			$info = null;
			$code = -1;
			$message = C('parameter_invalid');
			$return = $this->buildReturn($info, $code, $message);
			$this->ajaxReturn($return);
		}
		$activityModel = D('Activity');
		$activities = $activityModel->where(array('fid' => $fid, 'mid' => $mid, 'holdprovince' => $pid, 'audit' => 1, 'status' => 0))->select();
		foreach ($activities as $key => $val) {
			$activities[$key]['cinemaname'] = getCinemaNameById($val['cinemaid']);
			$activities[$key]['city'] = getPlaceNameById($val['holdcity']);
			$activities[$key]['enrollnum'] = getBoughtTicketByAid($val['id']);
			$activities[$key]['money'] = getTicketPriceByAid($val['id']);
			$activities[$key]['href'] = U('MovieActivity/detail', array('aid' => $val['id']));
		}
		$info = $activities;

		$code = 0;
		$message = '包场活动信息';

		if (empty($info)) {
			$message = U('Forum/index', array('fid' => $fid));
		}
		$return = $this->buildReturn($info, $code, $message);
		$this->ajaxReturn($return);
	}

	//获取弹幕
	public function getBarrage($mid) {
		$seminarBarrageModel = D('SeminarBarrage');
		$barrages = $seminarBarrageModel->where(array('mid' => $mid, 'status' => 0))->select();
		return $barrages;
	}

	//获取影片上映时间
	public function getRelasetime($id) {
		$forumMovieModel = D('ForumMovie');
		$releasetime = $forumMovieModel->where(array('id' => $id))->getFieldById($id, 'releasetime');
		return $releasetime;
	}

	//获取累计票房
	public function getBoxoffice($mid) {
		$forumMovieModel = D('ForumMovie');
		$box_title = $forumMovieModel->getFieldById($mid, 'box_office_id');
		$total_box_office = $this->BoxOffice($box_title);
		return $total_box_office;
	}
}
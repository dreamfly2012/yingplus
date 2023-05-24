<?php

namespace Home\Controller;

class OnlineActivityController extends CommonController {
	public function index() {
		$this->display('index');
	}


	public function get(){
		$aid = I('request.aid',null,'intval');
		$uid = $this->getUid();
		if(empty($aid)){
			$info = null;
			$code = -1;
			$message = C('parameter_invalid');
			$return = $this->buildReturn($info, $code, $message);
			$this->ajaxReturn($return);
		}

		$activity = $activityModel->where(array('id' => $aid, 'category' => 2))->find();
		if(empty($activity)){
			$info = null;
			$code = 1;
			$message = '活动不存在';
			$return = $this->buildReturn($info, $code, $message);
			$this->ajaxReturn($return);
		}

		

	}

	//详情页
	public function detail() {
		$aid = I('request.aid', null, 'intval');
		$uid = $this->getUid();
		$activityModel = D('Activity');
		$activity = $activityModel->where(array('id' => $aid, 'category' => 2))->find();
		if (empty($activity)) {
			$this->display('Public/not_found');
			die;
		}

		$this->assignDetailInfo($aid);
		$fid = $this->getFidByAid($aid);
		$forumModel = D('Forum');
		$forum = $forumModel->getForumInfoById($fid);
		$this->assign('forum', $forum);

		//其他热门活动话题
		$hot_length = C('OTHER_HOT_ACTIVITY_SHOW');
		$other_activities = $activityModel->getOtherCurrentActivity($aid, $fid, $hot_length);
		$other_activities_count = count($other_activities);
		$topicModel = D('Topic');
		$other_topics = $topicModel->getOtherCurrentTopic(0, $fid, $hot_length - $other_activities_count);
		$this->assign('other_activities', $other_activities);
		$this->assign('other_topics', $other_topics);

		$category = intval($activity['type']);
		if ($category == 8) {
			$ismultiselect = $activityModel->where(array('id' => $aid))->getField('ismultiselect');

			$this->assign('ismultiselect', $ismultiselect);

			$question_img = $activityModel->where(array('id' => $aid))->getField('question_img');
			$answer = $activityModel->where(array('id' => $aid))->getField('answer');
			$results = array();
			if (checkHasVote($uid, $aid)) {
				$answer = (array) json_decode($answer);
				$total = 0;

				foreach ($answer as $key => $val) {
					$total = $total + $val;
				}

				foreach ($answer as $key => $val) {
					$results[$key]['value'] = $val;
					$results[$key]['percent'] = $val * 100 / $total;
				}
				$this->assign('results', $results);
			}

			if (!$this->checkIsPostQuestionImg($question_img)) {
				$question = $activityModel->where(array('id' => $aid))->getField('question');
				$questions = json_decode($question);
				$this->assign('questions', $questions);

				$this->display('detail_vote_text');
			} else {
				$question_img = trim($question_img, ',');
				$question_img_arr = explode(',', $question_img);

				$question = $activityModel->where(array('id' => $aid))->getField('question');
				$questions = json_decode($question);

				$wrap_questions = array(); //封装问题
				foreach ($questions as $key => $val) {
					$wrap_questions[$key]['text'] = $val;
					$wrap_questions[$key]['image'] = is_null(getAttachmentUrlById($question_img_arr[$key])) ? '/Public/default/img/online_huodong/vote_default_img.png' : getAttachmentUrlById($question_img_arr[$key]);
				}
				//添加图片id
				$i = 0;
				foreach ($answer as $key => $val) {
					$results[$key]['image'] = is_null(getAttachmentUrlById($question_img_arr[$i])) ? '/Public/default/img/online_huodong/vote_default_img.png' : getAttachmentUrlById($question_img_arr[$i]);
					$i++;
				}

				$this->assign('results', $results);
				$this->assign('wrap_questions', $wrap_questions);

				$this->display('detail_vote_image');
			}

		} else if ($category == 9) {
			//线上征集活动
			$activityOnlineModel = D('ActivityOnline');
			$posters = $activityOnlineModel->where(array('status' => 0, 'aid' => $aid))->order(array('id' => 'desc'))->limit(0, 9)->select();
			$poster_count = count($posters) == 9 ? 9 : count($posters);
			$poster_more = $poster_count == 9 ? 1 : 0;
			$this->assign('pageid', 2);
			$this->assign('poster_more', $poster_more);
			$this->assign('poster_count', $poster_count);
			$this->assign('posters', $posters);
			$this->display('detail_collect');
		}

	}

	//征集活动分页
	public function getMoreCollcet() {
		$start = I('request.p', null, 'intval');
		$activityOnlineModel = D('ActivityOnline');
		$posters = $activityOnlineModel->where(array('status' => 0, 'aid' => $aid))->order(array('id' => 'desc'))->limit(($start - 1) * 9, 9)->select();
		$poster_count = count($posters) == 9 ? 9 : count($posters);
		$poster_more = $poster_count > 0 ? 1 : 0;
		$pageid = $start + 1;
		$this->assign('pageid', $pageid);
		$this->assign('poster_more', $poster_more);
		$this->assign('poster_count', $poster_count);
		$this->assign('posters', $posters);
		$content = $this->fetch('detail_collect_page');
		if ($poster_more) {
			$this->ajaxReturn(array('status' => 0, 'pageid' => $pageid, 'info' => $content));
		} else {
			$this->ajaxReturn(array('status' => 1, 'info' => '亲,没有更多图片了'));
		}
	}

	//判断投票问题是否有上传图片问题
	public function checkIsPostQuestionImg($img) {
		$img = trim($img, ',');
		$img_arr = explode(',', $img);
		foreach ($img_arr as $val) {
			if ($val != 0) {
				return true;
			}
		}
		return false;
	}

	//征集点赞
	public function favorPoster() {
		if ($this->checkLogin()) {
			$uid = $this->getUid();
			$aid = I('request.aid', null, 'intval');
			$oid = I('request.oid', null, 'intval');
			$activityOnlineFavorModel = D('ActivityOnlineFavor');
			$activityOnlineModel = D('ActivityOnline');
			$info = $activityOnlineFavorModel->where(array('uid' => $uid, 'oid' => $oid))->find();
			if (empty($info)) {
				$activityOnlineFavorModel->add(array('aid' => $aid, 'uid' => $uid, 'oid' => $oid));
				$activityOnlineModel->where(array('id' => $oid))->setInc('favor');
			}
			$this->ajaxReturn(array('status' => 0, 'info' => '点赞成功'));
		} else {
			$this->ajaxReturn(array('status' => 1, 'info' => 'no login'));
		}
	}
	//征集海报删除
	public function deletePoster() {
		if ($this->checkLogin()) {
			$uid = $this->getUid();
			$aid = I('request.aid', null, 'intval');
			$oid = I('request.oid', null, 'intval');
			if ($this->checkPrivilege('isadmin') || $this->checkPosterOwner($uid, $oid) || $this->checkPosterActivityOwner($uid, $aid)) {
				$activityOnlineModel = D('ActivityOnline');
				$activityOnlineModel->where(array('id' => $oid))->setField(array('status' => 1));
				$this->ajaxReturn(array('status' => 0, 'info' => '删除成功'));
			} else {
				$this->ajaxReturn(array('status' => 2, 'info' => '没有权限'));
			}

		} else {
			$this->ajaxReturn(array('status' => 1, 'info' => 'no login'));
		}
	}

	public function checkPosterOwner($uid, $oid) {
		$activityOnlineModel = D('ActivityOnline');
		$self_uid = $activityOnlineModel->where(array('id' => $oid))->getField('uid');
		if ($self_uid == $uid) {
			return true;
		} else {
			return false;
		}
	}

	public function checkPosterActivityOwner($uid, $aid) {
		$self_uid = getActivityUidById($aid);
		if ($uid == $self_uid) {
			return true;
		} else {
			return false;
		}
	}

	//投票
	public function submitVote() {
		$aid = I('request.aid', null, 'intval');
		$uid = $this->getUid();
		$ismultiselect = I('request.ismultiselect', null, 'intval');
		$choices = I('request.choices');
		$choices = trim($choices, ',');
		if(empty($uid)){
			$info = 'no_login';
			$code = 2;
			$meesage = C('no_login');
			$return = $this->buildReturn($info, $code, $message);
			$this->ajaxReturn($return);
		}
		if(empty($aid)||is_null($ismultiselect)||is_null($choices)){
			$info = 'parameter_invalid';
			$code = -1;
			$meesage = C('parameter_invalid');
			$return = $this->buildReturn($info, $code, $message);
			$this->ajaxReturn($return);
		}



		$activityOnlineModel = D('ActivityOnline');
		$activityModel = D('Activity');
		$answer = $activityModel->where(array('id' => $aid))->getField('answer');
		$question = $activityModel->where(array('id' => $aid))->getField('question');
		$info = $activityOnlineModel->where(array('uid' => $uid, 'type' => 1, 'aid' => $aid, 'status' => 0))->find();
		$arr = array('uid' => $uid, 'addtime' => time(), 'type' => 1, 'ismultiselect' => $ismultiselect, 'choices' => $choices, 'aid' => $aid);

		$answer_arr = (array) json_decode($answer);

		if (empty($info)) {
			$activityOnlineModel->add($arr);
			$question_arr = (array)json_decode($question);
			$choices_arr = explode(',', $choices);

			foreach ($answer_arr as $key => $val) {
				if (in_array($key, $choices_arr)) {
					$answer_arr[$key] = $answer_arr[$key] + 1;
				}
			}
			$answer = json_encode($answer_arr);
			$activityModel->where(array('id' => $aid))->setField(array('answer' => $answer));
		}
		$info = 'success';
		$code = 0;
		$message = '投票成功';
		$return = $this->buildReturn($info, $code, $message);
		$this->ajaxReturn($return);
	}

	//详情页相关信息赋值
	public function assignDetailInfo($aid) {
		$uid = $this->getUid();

		$activityModel = D('Activity');
		$activity_info = $activityModel->getActivityInfoById($aid);
		$this->assign('activity', $activity_info);
		$fid = $activity_info['fid'];

		$forumModel = D('Forum');
		$forum_info = $forumModel->getForumInfoById($fid);
		$this->assign('forum', $forum_info);

		$forumUserModel = D('ForumUser');
		$admin_info = $forumUserModel->getAdminUserInfo($fid);
		$this->assign('forum_admin', $admin_info);

		//获取未读信息
		$activityResponseModel = D('ActivityResponse');
		$activityResponseUserModel = D('ActivityResponseUser');
		$unreadmessage = $activityResponseUserModel->getUnreadMessageByUidAid($uid, $aid);
		$unreadmessage_count = count($unreadmessage);
		$lastunreadmessage = $unreadmessage[0];
		$lastunreadmessage['content'] = strip_tags($lastunreadmessage['content']);

		$this->assign('unreadmessage_count', $unreadmessage_count);
		$this->assign('lastunreadmessage', $lastunreadmessage);

		//个性样式加载
		$this->assign('pathname', $forum_info['pathname']);

		//获取回复信息

		$activity_responses = $activityResponseModel->where(array('aid' => $aid, 'status' => 0))->order(array('addtime' => 'asc'))->select();
		$activity_responses_count = count($activity_responses);
		$basetime = $activity_responses[$activity_responses_count - 1]['addtime'];

		//处理回复中时间间隔显示
		for ($i = $activity_responses_count - 1; $i >= 0; $i--) {
			$time = $activity_responses[$i]['addtime'];
			$time_info = showDate($basetime, $time);
			$topic_responses[$i]['show_date'] = $time_info['return_time'];
			$basetime = $time_info['basetime'];
		}

		$this->assign('activity_responses', $activity_responses);
	}

	

	//上传征集海报
	public function uploadCollectPoster() {
		$attachmentid = I('request.attachmentid',null);
		$desc = I('request.desc',null);
		$aid = I('request.aid',null);
		$uid = $this->getUid();
		if(iconv_strlen($s, 'utf-8')>20){
			$info =null;
			$code = -1;
			$message = '描述不能超过20';
			$return = $this->buildReturn($info, $code, $message);
			$this->ajaxReturn($return);
		}
		$attachmentModel = D('Attachment');
		$attachment = $attachmentModel->where(array('id'=>$attachmentid))->find();
		
		$poster_img = array(
			'img_url' => $attachment['remote_url'],
			'desc' => $desc,
			'width' => $attachment['width'],
			'height' => $attachment['height'],
			'attachmentid' => $attachmentid,
			'thumb_id' => $attachmentid,
			'aid' => $aid,
			'uid' => $uid,
			'addtime' => time(),
		);
		$activityOnlineModel = D('ActivityOnline');
		$id = $activityOnlineModel->add($poster_img);
		$info = $id;
		$code = 0;
		$message = '上传征集图片成功';
		$return = $this->buildReturn($info, $code, $message);
		$this->ajaxReturn($return);
	}

	//活动主图上传
	public function generateActivityImg() {
		if ($this->checkLogin()) {
			$uid = $this->getUid();
			$upload = new \Think\Upload();
			$upload->maxSize = 5230000;
			$upload->exts = C('MOVIE_ACTIVITY_UPLOAD_EXT');
			$upload->savePath = C('UPLOAD_TEMP_PATH');
			$width = 200;
			$height = 200;
			$info = $upload->upload();
			if (!$info) {
				$this->ajaxReturn(json_encode(array('status' => 1, 'info' => '图片没有上传或者超过5M')), 'EVAL');
			} else {
				//找到文件的路径
				$img_add = './Uploads/' . $info['activity_img']['savepath'] . $info['activity_img']['savename'];
				$image = new \Think\Image();
				$image->open($img_add);
				if ($image->width() < $width) {
					$this->ajaxReturn(json_encode(array('status' => 1, 'info' => '图片宽度小于' . $width)), 'EVAL');
				} else if ($image->height() < $height) {
					$this->ajaxReturn(json_encode(array('status' => 1, 'info' => '图片高度小于' . $height)), 'EVAL');
				} else {
					$save_time = time();
					$img_add = './Uploads/OnlineActivity/Img/' . $save_time . '.' . $info['activity_img']['ext'];

					$imgInfo = $image->thumb(218, 218 * $image->height() / $image->width())->save($img_add);

					$attachmentModel = D('Attachment');
					$data_thumb['filename'] = '活动页面主图';
					$data_thumb['width'] = $imgInfo->width();
					$data_thumb['path'] = $img_add;
					$data_thumb['isimage'] = 1;
					$data_thumb['uid'] = $uid;
					$thumb_id = $attachmentModel->add($data_thumb);

					$this->ajaxReturn(json_encode(array('status' => 0, 'info' => 'success', 'path' => buildImgUrl($img_add), 'thumb_id' => $thumb_id)), 'EVAL');
				}

			}
		} else {
			$this->ajaxReturn(json_encode(array('status' => 2, 'info' => '没有登录')), 'EVAL');
		}
	}

	public function uploadActivityImg() {
		$attachmentModel = D('Attachment');
		$x = I('request.x', 270);
		$y = I('request.y', 270);
		$w = I('request.w', 0);
		$h = I('request.h', 0);

		$primaryimg = I('primary', null, '');

		$uid = $this->getUid();
		$thumb = I('request.thumb', null, '');
		if ($thumb == '') {
			$this->error(C('上传失败'));
		}
		$save_add = time();
		$thumb_path = './Uploads/OnlineActivity/Img/' . $save_add . '.jpg';
		$image = new \Think\Image();
		$image->open('.' . $primaryimg);

		$image->crop($w, $h, $x, $y)->save($thumb_path);

		$width = $image->width();
		$height = $image->height();

		$data_thumb['thumb_path'] = '投票征集缩略图海报';
		$data_thumb['width'] = $width;
		$data_thumb['height'] = $height;
		$data_thumb['path'] = $thumb_path;
		$data_thumb['isimage'] = 1;
		$data_thumb['uid'] = $uid;
		$thumb_id = $attachmentModel->add($data_thumb);
		//返回0，正常结果
		$this->ajaxReturn(array('status' => 0, 'info' => $thumb_id));
	}

	

	//添加线上活动
	public function add() {
		$category = 2; //线上活动
		$type = I('request.type', null, 'intval');
		$type = ($type == 0) ? 9 : 8; //9征集
		$subject = I('request.title', null);
		$content = I('request.desc', null);
		$img = I('request.img', null, 'intval');
		$fid = I('request.fid', null, 'intval');
		$uid = $this->getUid();
		$encrollend_date = I('request.encrollend_date', null);
		$encrollend_custom_date = I('request.encrollend_custom_date', null, 'strtotime');
		$encrollendtime = ($encrollend_date == 'custom') ? $encrollend_custom_date : (time() + $encrollend_date);
		$question_img = I('request.question_img', null);

		$question = I('request.question', null);

		$answer = array();
		$i = 0;
		foreach ($question as $key => $val) {
			$answer[$i] = 0;
			$question_arr[$key] = $val;
			$i++;
		}

		
		$question = json_encode($question_arr);
		$answer = json_encode($answer);
		
		$ismultiselect = I('request.option_set', null, 'intval');
		$data = array('type' => $type, 'category' => $category, 'subject' => $subject, 'img' => $img, 'content' => $content, 'fid' => $fid, 'uid' => $uid, 'encrollendtime' => $encrollendtime, 'question' => $question, 'answer' => $answer, 'question_img' => $question_img, 'ismultiselect' => $ismultiselect, 'addtime' => time(),'audit'=>1,'ip'=>get_client_ip());

		if(empty($uid)){
			$info = 'no_login';
			$code = 2;
			$message = C('no_login');
			$return = $this->buildReturn($info, $code, $message);
			$this->ajaxReturn($return);
		}


		if (empty($fid)||empty($subject)||empty($content)) {
			$info = 'parameter_invalid';
			$code = -1;
			$meesage = 'parameter_invalid';
			$return = $this->buildReturn($info, $code, $message);
			$this->ajaxReturn($return);
		}

		if(empty($question)&&$type==8){
			$info = 'error';
			$code = 1;
			$meesage = '问题选项不能为空';
			$return = $this->buildReturn($info, $code, $message);
			$this->ajaxReturn($return);
		}
			
			
		$activityModel = D('Activity');
		$id = $activityModel->add($data);
		if(empty($id)){
			$info = $activityModel->getError();
			$code = 1;
			$message = '创建失败';
			$return = $this->buildReturn($info, $code, $message);
			$this->ajaxReturn($return);
		}

		if(isMobile()){
			$url = U('Mobile/activity', array('aid' => $id));
		}else{
			$url = U('Pc/activity', array('aid' => $id));
		}
		
		$info['id'] = $id;
		$info['url'] = $url;
		$code = 0;
		$message = '创建成功';
		$return = $this->buildReturn($info, $code, $message);
		$this->ajaxReturn($return);
	}
}

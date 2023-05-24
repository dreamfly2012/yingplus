<?php

namespace Home\Controller;

class PersonCenterController extends CommonController {
	public function __construct() {
		parent::__construct();
		if (!$this->checkLogin()) {
			$this->error(C('NO_LOGIN'), U('Index/index'));
		}
	}

	public function index() {
		$uid = $this->getUid();
		$type = I('request.type', 'create');
		$this->assign('type', $type);
		$joinforums = $this->getJoinForumsByUid($uid);
		$this->assign('joinforums', $joinforums);

		switch ($type) {
		case 'create':
			$this->getCreateInfo();
			break;
		case 'collect':
			$this->getCollectInfo();
			break;
		case 'message':
			$this->getMessageInfo();
			break;
		default:
			break;
		}

		$this->display('my_home');
	}

	//清空消息提醒
	public function clearNewsNotice() {
		$uid = $this->getUid();
		$type = I('post.type');
		$category = I('post.category');
		$this->ajaxReturn(array('status' => 1, 'content' => C('CLEAR_NEWS_NOTICE_SUCCESS')));
	}

	//更新消息已读
	public function updateMessage() {
		$id = I('post.message_id');
		$type = I('request.message_type');
		$uid = $this->getUid();
		$messageModel = D('Message');
		$topicResponseUserModel = D('TopicResponseUser');
		$activityResponseUserModel = D('ActivityResponseUser');
		switch ($type) {
		case 'system':
			$messageModel->where(array('id' => $id, 'touid' => $uid))->setField(array('isread' => 1));
			break;
		case 'topic_response':
			$topicResponseUserModel->where(array('id' => $id, 'uid' => $uid))->setField(array('isread' => 1));
			break;
		case 'activity_response':
			$activityResponseUserModel->where(array('id' => $id, 'uid' => $uid))->setField(array('isread' => 1));
			break;
		default:
			break;
		}

	}

	public function getPointMessage() {
		$this->getPointMessagePage();
	}

	public function getPointMessagePage() {
		$UserPointModel = D('UserPoint');
		$page_num = I('request.p', 1);
		$uid = $this->getUid();
		$list = $UserPointModel->where(array('uid' => $uid))->order(array('addtime' => 'desc'))->page($page_num . ',5')->select();
		$this->assign('point_list', $list); // 赋值数据集
		$count = $UserPointModel->where(array('uid' => $uid))->count(); // 查询满足要求的总记录数
		$Page = new \Think\Page($count, 5); // 实例化分页类 传入总记录数和每页显示的记录数
		$Page->setConfig('prev', '上一页');
		$Page->setConfig('next', '下一页');
		$Page->setConfig('theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
		$show = $Page->show(); // 分页显示输出
		$this->assign('page', $show); // 赋值分页输出
	}

	public function getPointMessagePageList() {
		$this->getPointMessagePage();
		$this->display('my_point_message');
	}

	//获取参与话题的回复
	public function getTopicResponse() {
		$this->getTopicResponsePage();
	}

	public function getTopicResponsePage() {
		$topicResponseUserModel = D('TopicResponseUser');
		$page_num = I('request.p', 1);
		$uid = $this->getUid();
		$list = $topicResponseUserModel->where(array('uid' => $uid, 'isread' => 0))->order(array('id' => 'desc'))->page($page_num . ',' . C('MESSAGE_SHOW_COUNT'))->select();
		$this->assign('responses', $list); // 赋值数据集
		$count = $topicResponseUserModel->where(array('uid' => $uid, 'isread' => 0))->count(); // 查询满足要求的总记录数
		$Page = new \Think\Page($count, C('MESSAGE_SHOW_COUNT')); // 实例化分页类 传入总记录数和每页显示的记录数
		$Page->setConfig('prev', '上一页');
		$Page->setConfig('next', '下一页');
		$Page->setConfig('theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
		$show = $Page->show(); // 分页显示输出
		$this->assign('page', $show); // 赋值分页输出
	}

	public function getTopicResponsePageList() {
		$this->getTopicResponsePage();
		$this->display('my_topic_response');
	}

	//获取参与活动的回复
	public function getActivityResponse() {
		$this->getActivityResponsePage();
	}

	public function getActivityResponsePage() {
		$activityResponseUserModel = D('ActivityResponseUser');
		$page_num = I('request.p', 1);
		$uid = $this->getUid();
		$list = $activityResponseUserModel->where(array('uid' => $uid, 'isread' => 0))->order(array('id' => 'desc'))->page($page_num . ',' . C('MESSAGE_SHOW_COUNT'))->select();
		$this->assign('responses', $list); // 赋值数据集
		$count = $activityResponseUserModel->where(array('uid' => $uid, 'isread' => 0))->count(); // 查询满足要求的总记录数
		$Page = new \Think\Page($count, C('MESSAGE_SHOW_COUNT')); // 实例化分页类 传入总记录数和每页显示的记录数
		$Page->setConfig('prev', '上一页');
		$Page->setConfig('next', '下一页');
		$Page->setConfig('theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
		$show = $Page->show(); // 分页显示输出
		$this->assign('page', $show); // 赋值分页输出
	}

	public function getActivityResponsePageList() {
		$this->getActivityResponsePage();
		$this->display('my_activity_response');
	}

	//批量删除话题@回复消息
	public function batchDeleteTopicResponse() {
		$message_ids = I('message_ids', null);
		$message_ids_arr = explode(':', $message_ids);
		$TopicResponseUserModel = D('TopicResponseUser');
		$uid = $this->getUid();
		foreach ($message_ids_arr as $key => $val) {
			$TopicResponseUserModel->where(array('uid' => $uid, 'id' => $val))->setField(array('isread' => 1));
		}
		$this->ajaxReturn(array('status' => 1, 'info' => 'ok'));
	}
	//批量删除活动@回复消息
	public function batchDeleteActivityResponse() {
		$message_ids = I('message_ids', null);
		$message_ids_arr = explode(':', $message_ids);
		$ActivityResponseUserModel = D('ActivityResponseUser');
		$uid = $this->getUid();
		foreach ($message_ids_arr as $key => $val) {
			$ActivityResponseUserModel->where(array('uid' => $uid, 'id' => $val))->setField(array('isread' => 1));
		}
		$this->ajaxReturn(array('status' => 1, 'info' => 'ok'));
	}

	//获取所有信息
	public function getReadMessage() {
		$this->getReadMessagePage();
	}

	public function getReadMessagePage() {
		$messageModel = D('Message');
		$page_num = I('request.p', 1);
		$uid = $this->getUid();
		$list = $messageModel->getReadMessage($uid, $page_num);
		$this->assign('messages', $list); // 赋值数据集
		$count = $messageModel->where(array('touid' => $uid, 'status' => 0, 'isread' => 1))->count(); // 查询满足要求的总记录数
		$Page = new \Think\Page($count, C('MESSAGE_SHOW_COUNT')); // 实例化分页类 传入总记录数和每页显示的记录数
		$Page->setConfig('prev', '上一页');
		$Page->setConfig('next', '下一页');
		$Page->setConfig('theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
		$show = $Page->show(); // 分页显示输出
		$this->assign('page', $show); // 赋值分页输出
	}

	public function getAllMessagePageList() {
		$this->getReadMessagePage();
		$this->display('my_all_message');
	}

	//获取未读消息
	public function getUnreadMessage() {
		$this->getUnreadMessagePage();
	}

	public function getUnreadMessagePage() {
		$messageModel = D('Message');
		$page_num = I('request.p', 1);
		$uid = $this->getUid();
		$list = $messageModel->getUnreadMessage($uid, $page_num);
		$this->assign('ureadMessages', $list); // 赋值数据集
		$count = $messageModel->where(array('touid' => $uid, 'status' => 0, 'isread' => 0))->count(); // 查询满足要求的总记录数
		$Page = new \Think\Page($count, C('MESSAGE_SHOW_COUNT')); // 实例化分页类 传入总记录数和每页显示的记录数
		$Page->setConfig('prev', '上一页');
		$Page->setConfig('next', '下一页');
		$Page->setConfig('theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
		$show = $Page->show(); // 分页显示输出
		$this->assign('page', $show); // 赋值分页输出
	}

	public function getUnreadMessagePageList() {
		$this->getUnreadMessagePage();
		$this->display('my_unread_message');
	}

	//个人信息
	public function profileSetting() {
		//昵称,真实姓名
		$userProfileModel = D('UserProfile');
		$uid = $this->getUid();
		$realname = $userProfileModel->getFieldByUid($uid, 'realname');
		$this->assign('realname', $realname);

		//性别
		$gender = $userProfileModel->getFieldByUid($uid, 'gender');
		$this->assign('gender', $gender);

		//地址
		$current_province = $userProfileModel->getFieldByUid($uid, 'birthprovince');
		$current_city = $userProfileModel->getFieldByUid($uid, 'birthcity');
		$address = $userProfileModel->getFieldByUid($uid, 'address');
		$this->assign('current_province', $current_province);
		$this->assign('current_city', $current_city);
		$this->assign('address', $address);
		$districtModel = D('District');
		$provinces = $districtModel->getAllProvince(0);
		$this->assign('provinces', $provinces);

		$default_province = empty($current_province) ? 1 : $current_province;
		$cities = $districtModel->getCityByProvince($default_province);
		$this->assign('cities', $cities);

		//生日设置
		$current_year = $userProfileModel->getFieldByUid($uid, 'birthyear');
		$current_month = $userProfileModel->getFieldByUid($uid, 'birthmonth');
		$current_day = $userProfileModel->getFieldByUid($uid, 'birthday');
		$this->assign('current_year', $current_year);
		$this->assign('current_month', $current_month);
		$this->assign('current_day', $current_day);

		$year = array();
		$month = array();
		$day = array();
		$now_y = date('Y');
		for ($i = 1900; $i <= $now_y; $i++) {
			$year[] = $i;
		}
		for ($i = 1; $i <= 12; $i++) {
			$month[] = $i;
		}
		for ($i = 1; $i <= 31; $i++) {
			$day[] = $i;
		}

		$this->assign('year', $year);
		$this->assign('month', $month);
		$this->assign('day', $day);

		//个人介绍
		$selfdesc = $userProfileModel->getFieldByUid($uid, 'selfdesc');
		$this->assign('selfdesc', $selfdesc);

		$this->display('profile_setting');
	}

	/**
	 * ajax 根据省份返回城市
	 */
	public function getCityByProvince() {
		$pid = I('request.pid', null);
		$districtModel = D("District");
		$cities = $districtModel->getCityByProvince($pid);
		$this->ajaxReturn($cities);
	}

	//用户设置信息保存
	public function profileSettingDo() {
		$data = I('post.');
		$result = 'true';
		$nickname = I('post.nickname', null);
		$uid = session('uid');

		if (!empty($nickname)) {
			$userModel = D('User');

			$count = $userModel->where(array('id' => array('neq' => $uid), 'nickname' => $nickname))->count();

			if ($count > 0) {
				$this->ajaxReturn(array('status' => 0, 'content' => C('NICKNAME_EXIST')));
			} else {
				//语句写法问题，解决unique无法保存
				$result = $userModel->execute("update __PREFIX__user set nickname = '" . $nickname . "' where `id` = " . $uid);

				if ($result != -1) {
					// $point=R('Point/createPersonMessage',array(1));
					// if($point){
					//  R('Point/addUserTotalPoint',array($point));
					// }
					$this->ajaxReturn(array('status' => 1, 'content' => C('SETTING_UPDATE_SUCCESS')));
				} else {
					$this->ajaxReturn(array('status' => 2, 'content' => C('SETTING_UPDATE_FAILED')));
				}

			}
		}

		$userProfileModel = D('UserProfile');

		//$allow = $userProfileModel->getDbFields();
		$allow = array('realname', 'gender', 'selfdesc', 'birthprovince', 'birthcity', 'birthdist', 'birthyear', 'birthmonth', 'birthday', 'address');
		//保存用户信息，在数据库的字段就进行更新

		foreach ($data as $key => $val) {
			if (in_array($key, $allow)) {
				if ($key == 'realname') {
					if (preg_match('/\d+/', $val) || empty($allow)) {
						$this->ajaxReturn(array('status' => 0, 'content' => C('REALNAME_IS_INVALID')));
					}
				}
				if ($key == 'selfdesc') {
					if ($val == "") {
						$this->ajaxReturn(array('status' => 0, 'content' => C('CAN_NOT_EMPTY')));
					}
				}
				// $point=$this->selectWord($key);
				// if($point){
				//     R('Point/addUserTotalPoint',array($point));
				//  }
				$result = $userProfileModel->where(array('uid' => $uid))->setField(array($key => $val));
			}

		}

		if (!is_null($nickname) && $nickname == "") {
			$this->ajaxReturn(array('status' => 0, 'content' => C('CAN_NOT_EMPTY')));
		}

		if (!strpos($result, 'false')) {

			$this->ajaxReturn(array('status' => 1, 'content' => C('SETTING_UPDATE_SUCCESS')));
		} else {
			$this->ajaxReturn(array('status' => 0, 'content' => C('SETTING_UPDATE_FAILED')));
		}
	}

	public function selectWord($data) {
		switch ($data) {
		case 'realname':
			$point = R('Point/createPersonMessage', array(2));
			return $point;
			break;
		case 'gender':
			$point = R('Point/createPersonMessage', array(3));
			return $point;
			break;
		case 'selfdesc':
			$point = R('Point/createPersonMessage', array(6));
			return $point;
			break;
		case 'birthcity':
			$point = R('Point/createPersonMessage', array(4));
			return $point;
			break;
		case 'birthday':
			$point = R('Point/createPersonMessage', array(5));
			return $point;
			break;

		}
	}

	//获取我创建的活动
	public function getCreateActivity() {
		$this->getCreateActivityPage();
	}

	public function getCreateActivityPage() {
		$activityModel = D('Activity');
		$uid = $this->getUid();
		$start = I('request.p', 1);
		$page_num = C('PERSON_CENTER_PAGE_NUM');
		$condition['uid'] = $uid;
		$condition['status'] = array('neq', 1);
		$activities = $activityModel->getActivityByCondition($condition, $page_num * ($start - 1), $page_num);
		$count = $activityModel->where($condition)->count();
		$Page = new \Think\AjaxPage($count, $page_num, 'get_create_activity', 'p');
		$Page->setConfig('theme', '%upPage%  %first%  %prePage%  %linkPage%  %nextPage% %end% %downPage%');
		$page = $Page->show();
		$this->assign('page', $page);
		$this->assign('activities', $activities);
	}

	public function getCreateActivityList() {
		$this->getCreateActivityPage();
		$this->display('my_create_activity');
	}

	/**
	 * 获取报名的活动
	 */
	public function getEncrollActivity() {
		$this->getEncrollActivityPage();
	}

	public function getEncrollActivityPage() {
		$activityEncrollModel = D('ActivityEncroll');
		$uid = $this->getUid();
		$start = I('request.p', 1);
		$page_num = C('PERSON_CENTER_PAGE_NUM');
		$condition['status'] = 0;
		$condition['uid'] = $uid;
		$activities = $activityEncrollModel->getActivityByCondition($condition, $page_num * ($start - 1), $page_num);
		$count = $activityEncrollModel->where($condition)->count();
		$Page = new \Think\AjaxPage($count, $page_num, 'get_encroll_activity', 'p');
		$Page->setConfig('theme', '%upPage%  %first%  %prePage%  %linkPage%  %nextPage% %end% %downPage%');
		$page = $Page->show();
		$this->assign('page', $page);
		$this->assign('activities', $activities);
	}

	public function getEncrollActivityList() {
		$this->getEncrollActivityPage();
		$this->display('my_encroll_activity');
	}

	/**
	 * 获取收藏的活动
	 */
	public function getCollectActivity() {
		$this->getCollectActivityPage();
	}

	/**
	 *
	 */
	public function getCollectActivityPage() {
		$activityCollectModel = D('ActivityCollect');
		$uid = $this->getUid();
		$start = I('request.p', 1);
		$page_num = C('PERSON_CENTER_PAGE_NUM');
		$condition['uid'] = $uid;
		$condition['status'] = 0;
		$activities = $activityCollectModel->getActivityByCondition($condition, $page_num * ($start - 1), $page_num);
		$count = $activityCollectModel->where($condition)->count();
		$Page = new \Think\AjaxPage($count, $page_num, 'get_collect_activity', 'p');
		$Page->setConfig('theme', '%upPage%  %first%  %prePage%  %linkPage%  %nextPage% %end% %downPage%');
		$page = $Page->show();
		$this->assign('page', $page);

		$this->assign('activities', $activities);
	}

	public function getCollectActivityList() {
		$this->getCollectActivityPage();
		$this->display('my_collect_activity');
	}

	/**
	 * 获取参与讨论的活动
	 */
	public function getParticipatedActivity() {
		$this->getParticipatedActivityPage();
	}

	/**
	 * 分页获取参与回复的活动
	 */
	public function getParticipatedActivityPage() {
		$activityResponseModel = D('ActivityResponse');
		$uid = $this->getUid();
		$start = I('request.p', 1);
		$page_num = C('PERSON_CENTER_PAGE_NUM');
		$condition['uid'] = $uid;
		$condition['status'] = 0;
		$activities = $activityResponseModel->distinct(true)->field('aid')->getActivityByCondition($condition, $page_num * ($start - 1), $page_num);
		$count = $activityResponseModel->where($condition)->count('distinct aid');
		$Page = new \Think\AjaxPage($count, $page_num, 'get_participate_activity', 'p');
		$Page->setConfig('theme', '%upPage%  %first%  %prePage%  %linkPage%  %nextPage% %end% %downPage%');
		$page = $Page->show();
		$this->assign('page', $page);
		$this->assign('activities', $activities);
	}

	public function getParticipatedActivityList() {
		$this->getParticipatedActivityPage();
		$this->display('my_participated_activity');
	}

	/**
	 * 获取创建的话题
	 */
	public function getCreateTopic() {
		$this->getCreateTopicPage();
	}

	public function getCreateTopicPage() {
		$topicModel = D('Topic');
		$uid = $this->getUid();
		$start = I('request.p', 1);
		$page_num = C('PERSON_CENTER_PAGE_NUM');
		$condition['uid'] = $uid;
		$condition['status'] = 0;
		$topics = $topicModel->getTopicByCondition($condition, $page_num * ($start - 1), $page_num);
		$count = $topicModel->where($condition)->count();
		$Page = new \Think\AjaxPage($count, $page_num, 'get_create_topic', 'p');
		$Page->setConfig('theme', '%upPage%  %first%  %prePage%  %linkPage%  %nextPage% %end% %downPage%');
		$page = $Page->show();
		$this->assign('page', $page);
		$this->assign('topics', $topics);
	}

	public function getCreateTopicList() {
		$this->getCreateTopicPage();
		$this->display('my_create_topic');
	}

	/**
	 * 收藏的话题
	 */
	public function getCollectTopic() {
		$this->getCollectTopicPage();
	}

	public function getCollectTopicPage() {
		$topiccollectModel = D('TopicCollect');
		$uid = $this->getUid();
		$start = I('request.p', 1);
		$page_num = C('PERSON_CENTER_PAGE_NUM');
		$condition['uid'] = $uid;
		$condition['status'] = 0;
		$topics = $topiccollectModel->getTopicByCondition($condition, $page_num * ($start - 1), $page_num);
		$count = $topiccollectModel->where($condition)->count();
		$Page = new \Think\AjaxPage($count, $page_num, 'get_collect_topic', 'p');
		$Page->setConfig('theme', '%upPage%  %first%  %prePage%  %linkPage%  %nextPage% %end% %downPage%');
		$page = $Page->show();
		$this->assign('page', $page);
		$this->assign('topics', $topics);
	}

	public function getCollectTopicList() {
		$this->getCollectTopicPage();
		$this->display('my_collect_topic');
	}

	/**
	 * 参与回复的话题
	 */
	public function getParticipatedTopic() {
		$this->getParticipatedTopicPage();
	}

	public function getParticipatedTopicPage() {
		$topicresponseModel = D('TopicResponse');
		$uid = $this->getUid();
		$start = I('request.p', 1);
		$page_num = C('PERSON_CENTER_PAGE_NUM');
		$condition['uid'] = $uid;
		$condition['status'] = 0;
		$topics = $topicresponseModel->distinct(true)->field('tid')->getTopicByCondition($condition, $page_num * ($start - 1), $page_num);
		$count = $topicresponseModel->where($condition)->count('distinct tid');
		$Page = new \Think\AjaxPage($count, $page_num, 'get_participate_topic', 'p');
		$Page->setConfig('theme', '%upPage%  %first%  %prePage%  %linkPage%  %nextPage% %end% %downPage%');
		$page = $Page->show();
		$this->assign('page', $page);
		$this->assign('topics', $topics);
	}

	public function getParticipateTopicList() {
		$this->getParticipatedTopicPage();
		$this->display('my_participated_topic');
	}

	/**
	 **获取用户加入的星吧
	 ** @return Array
	 **/
	public function getJoinForumsByUid($uid) {
		$forumUserModel = D('ForumUser');
		$forumModel = D('Forum');
		$forumids = $forumUserModel->getForumsByUid($uid);
		$forumInfo = array();
		foreach ($forumids as $key => $val) {
			$fid = $val['fid'];
			$forum = $forumModel->getForumInfoById($fid);
			$forumInfo[$key] = $forum;
		}

		return $forumInfo;
	}

	//图像修改处理
	public function photoChange() {
		$this->display('pc/change_photo');
	}

	public function photoChangeDo() {
		//上传新photo,删除原来的photo,修改用户userprofile表的字段为上传附件id
		$upload = new \Think\Upload(); // 实例化上传类
		$upload->maxSize = C('UPLOAD_PHOTO_SIZE'); // 设置附件上传大小
		$upload->exts = array('jpg', 'gif', 'png', 'jpeg'); // 设置附件上传类型
		$upload->savePath = './Public/Uploads/'; // 设置附件上传目录
		$info = $upload->upload();
		if (!$info) {
			// 上传错误提示错误信息
			$this->error($upload->getError());
		} else {
			// 上传成功
			$this->success('上传成功！');
		}
	}

	//个人设置中头像裁剪的方法
	public function produceAvatar() {
		$attachmentModel = D('Attachment');
		$userProfileModel = D('UserProfile');
		$x = I('x', null, 0);
		$y = I('y', null, 0);
		$w = I('w', null, 0);
		$h = I('h', null, 0);
		$uid = session('uid');
		$avatar = I('avatar', null, '');
		if ($avatar == '') {
			$this->error(C('UPLOAD_AVATAR_ERROR'));
		}
		$avatar = '.' . $avatar;
		$image = new \Think\Image();
		$image->open($avatar);
		$save_avatar_add = time();
		$save_avatar_add = './Uploads/User/' . $save_avatar_add . '.jpg';

		$data['filename'] = '头像';
		$data['path'] = $save_avatar_add;
		$data['isimage'] = 1;
		$data['uid'] = $uid;
		$id = $attachmentModel->add($data);
		if ($id > 0) {
			$image->crop($w, $h, $x, $y)->save($save_avatar_add);
			$userProfileModel->where(array('uid' => $uid))->setField(array('photo' => $id));
			// $point=R('Point/createPersonMessage',array(7));
			// if($point){
			//    R('Point/addUserTotalPoint',array($point));
			// }
			$this->redirect('PersonCenter/profileSetting');
		} else {
			$this->error();
		}

	}

	//缩略预览图上传
	public function uploadImg() {
		$upload = new \Think\Upload();
		$upload->maxSize = C('UPLOAD_AVATAR_SIZE');
		$upload->exts = C('UPLOAD_AVATAR_EXT');
		$upload->savePath = C('UPLOAD_AVATAR_PATH');
		$info = $upload->upload();
		if (!$info) {
			$this->error($upload->getError());
		} else {
			//找到文件的路径
			$img_add = './Uploads' . $info['file']['savepath'] . $info['file']['savename'];
			$image = new \Think\Image();
			$image->open($img_add);
			$name = substr($info['file']['savename'], 0, -4);
			$thumb_add = './Uploads/thumb/' . $name . '_thumb.' . $info['file']['ext'];
			$thumb_img = $image->thumb(C('UPLOAD_AVATAR_THUMB_WIDTH'), C('UPLOAD_AVATAR_THUMB_HEIGHT'))->save($thumb_add);
			$img_width = $thumb_img->width();
			$img_height = $thumb_img->height();
			$thumb_add_img = '/Uploads/thumb/' . $name . '_thumb.' . $info['file']['ext'];
			$this->assign('thumb_add_img', $thumb_add_img);
			$this->assign('img_width', $img_width);
			$this->assign('img_height', $img_height);
			$this->display('img');
		}
	}

	//绑定第三方，手机绑定

	//发送验证码
	public function sendCaptchaDo() {
		$telephone = I('post.telephone');
		$bindBlock = D('BindBlock');
		$rand = rand(C('PHONE_MIN'), C('PHONE_MAX')); //产生随机数
		//session('captcha', $rand); //将产生的随机数设置到session中
		session('captcha', 123456);
		$content = C('PHONE_CAPTCHA_MESSAGE_PREFIX') . $rand . C('PHONE_CAPTCHA_MESSAGE_POSTFIX'); //发送短信的内容
		//$content = iconv("GB2312", "UTF-8", $content);
		$ip = get_client_ip(); //得到客户端的IP
		$date = date('Y-m-d'); //记录当天时间
		$ip_overflow = $bindBlock->checkMessageCountByIp($ip, $date);
		$phone_overflow = $bindBlock->checkMessageCountByTelephone($telephone, $date);
		$userModel = D('User');
		$exist = $userModel->where(array('telephone' => $telephone))->find();
		if ($exist) {
			$this->ajaxReturn(array('status' => 0, 'content' => '手机号已存在'));
		} else {
			if ($ip_overflow || $phone_overflow) {
				//可以发送验证码
				if (APP_DEBUG) {

					if ($this->SendSMS_debug($telephone, $content)) {
						//表示发送成功
						$this->ajaxReturn(array('status' => 1, 'content' => '验证码发送成功'));
					} else {
						//表示发送失败
						$this->ajaxReturn(array('status' => 0, 'content' => '系统繁忙,请稍后再试~'));
					}
				} else {
					if ($this->SendSMS($telephone, $content)) {
						//表示发送成功
						$this->ajaxReturn(array('status' => 1, 'content' => '验证码发送成功'));
					} else {
						//表示发送失败
						$this->ajaxReturn(array('status' => 0, 'content' => '系统繁忙,请稍后再试~'));
					}
				}

			} else {
				$this->ajaxReturn(array('status' => 0, 'content' => '您今天的请求过于频繁，请改天再试~'));
			}
		}

	}

	//绑定手机
	public function bindPhone() {
		$telephone = I('post.telephone');
		$password = I('post.password', null, 'md5');
		$captcha = I('post.captcha');
		$uid = $this->getUid();

		if (empty($telephone) || empty($password) || empty($captcha)) {
			$this->ajaxReturn(array('status' => 0, 'content' => '请填写所有信息'));
		} else {
			$session_captcha = session('captcha');
			if ($captcha == $session_captcha) {
				$userModel = D("User");
				$userModel->where(array('id' => $uid))->setField(array('telephone' => $telephone, 'password' => $password));
				//TODO:如果存在cookie,重新设置cookie
				// $point = R('Point/userBang',array(8));
				// if($point){
				//     R('Point/addUserTotalPoint',array($point));
				// }
				$this->ajaxReturn(array('status' => 1, 'content' => '绑定成功'));
			} else {
				$this->ajaxReturn(array('status' => 2, 'content' => '验证码错误'));
			}
		}
	}

	//重新绑定手机
	public function bindOtherPhone() {
		$telephone = I('post.telephone');
		$captcha = I('post.captcha');
		$uid = $this->getUid();

		if (empty($telephone) || empty($captcha)) {
			$this->ajaxReturn(array('status' => 0, 'content' => '请填写所有信息'));
		} else {
			$session_captcha = session('captcha');
			if ($captcha == $session_captcha) {
				$userModel = D("User");
				$exist = $userModel->where(array('telephone' => $telephone))->find();
				if ($exist) {
					$this->ajaxReturn(array('status' => 2, 'content' => '此手机号已绑定,请换其他手机号'));
				} else {
					$userModel->where(array('id' => $uid))->setField(array('telephone' => $telephone));
					//TODO:如果存在cookie,重新设置cookie
					$this->ajaxReturn(array('status' => 1, 'content' => '绑定成功'));
				}

			} else {
				$this->ajaxReturn(array('status' => 2, 'content' => '验证码错误'));
			}
		}
	}

	//获取我创建的活动和话题
	public function getCreateInfo() {
		$this->getCreateInfoPage();
	}
	public function getCreateInfoPage() {
		$activityModel = D('Activity');
		$topicModel = D('Topic');
		$uid = $this->getUid();
		$start = I('request.p', 1);
		$page_num = C('PERSON_CENTER_PAGE_NUM');

		$condition['uid'] = $uid;
		$condition['status'] = array('neq', 1);
		$activities = $activityModel->where($condition)->select();
		foreach ($activities as $key => $val) {
			$activities[$key]['is_activity'] = true;
		}

		$meta['uid'] = $uid;
		$meta['status'] = 0;
		$topics = $topicModel->where($meta)->select();
		foreach ($topics as $key => $val) {
			$topics[$key]['is_activity'] = false;
		}

		$info = array_merge_recursive($activities, $topics);

		foreach ($info as $key => $value) {
			$addtime[$key] = $value['addtime'];
		}

		array_multisort($addtime, $info);
		rsort($info);
		$count = count($info);
		$istart = $page_num * ($start - 1);
		$iend = ($page_num * $start > $count) ? $count : $page_num * $start;
		for ($i = $istart; $i < $iend; $i++) {
			$data[] = $info[$i];
		}

		$Page = new \Think\AjaxPage($count, $page_num, 'get_create_info', 'p');
		$Page->setConfig('theme', '%upPage%  %first%  %prePage%  %linkPage%  %nextPage% %end% %downPage%');
		$page = $Page->show();

		$this->assign('page', $page);
		$this->assign('data', $data);
	}
	public function getCreateInfoList() {
		$this->getCreateInfoPage();
		$this->display('my_create_info');
	}

	//获取收藏的活动和话题
	public function getCollectInfo() {
		$this->getCollectInfoPage();
	}

	public function getCollectInfoPage() {
		$activityCollectModel = D('ActivityCollect');
		$topicCollectModel = D('TopicCollect');
		$uid = $this->getUid();
		$start = I('request.p', 1);
		$page_num = C('PERSON_CENTER_PAGE_NUM');

		$condition['uid'] = $uid;
		$condition['status'] = 0;
		$activities = $activityCollectModel->where($condition)->select();
		foreach ($activities as $key => $val) {
			$activities[$key]['is_activity'] = true;
			$activities[$key]['subject'] = getActivitySubjectById($val['aid']);
		}

		$meta['uid'] = $uid;
		$meta['status'] = 0;
		$topics = $topicCollectModel->where($meta)->select();
		foreach ($topics as $key => $val) {
			$topics[$key]['is_activity'] = false;
			$topics[$key]['subject'] = getTopicSubjectById($val['tid']);
		}

		$info = array_merge_recursive($activities, $topics);

		foreach ($info as $key => $value) {
			$addtime[$key] = strtotime($value['updatetime']);
		}
		array_multisort($addtime, $info);
		rsort($info);

		$count = count($info);
		$istart = $page_num * ($start - 1);
		$iend = ($page_num * $start > $count) ? $count : $page_num * $start;
		for ($i = $istart; $i < $iend; $i++) {
			$data[] = $info[$i];
		}

		$Page = new \Think\AjaxPage($count, $page_num, 'get_collect_info', 'p');
		$Page->setConfig('theme', '%upPage%  %first%  %prePage%  %linkPage%  %nextPage% %end% %downPage%');
		$page = $Page->show();

		$this->assign('page', $page);
		$this->assign('data', $data);
	}
	public function getCollectInfoList() {
		$this->getCollectInfoPage();
		$this->display('my_collect_info');
	}

	//获取系统消息，@消息
	public function getMessageInfo() {
		$this->getMessageInfoPage();
	}
	public function getMessageInfoPage() {
		$messageModel = D('Message');
		$topicResponseUserModel = D('TopicResponseUser');
		$activityResponseUserModel = D('ActivityResponseUser');
		$topicResponseModel = D('TopicResponse');
		$activityResponseModel = D('ActivityResponse');

		$uid = $this->getUid();
		$start = I('request.p', 1);
		$page_num = 6;

		$condition['touid'] = $uid;
		$condition['status'] = 0;
		$messages = $messageModel->where($condition)->select();
		foreach ($messages as $key => $val) {
			$messages[$key]['message_type'] = 'system';
		}

		$meta['uid'] = $uid;
		$meta['status'] = 0;
		$topics = $topicResponseUserModel->where($meta)->select();
		foreach ($topics as $key => $val) {
			$topics[$key]['addtime'] = $topicResponseModel->getFieldById($val['response_id'], 'addtime');
			$topics[$key]['subject'] = getTopicSubjectById($topicResponseModel->getFieldById($val['response_id'], 'tid'));
			$topics[$key]['content'] = $topicResponseModel->getFieldById($val['response_id'], 'content');
			$topics[$key]['fid'] = $topicResponseModel->getFieldById($val['response_id'], 'fid');
			$topics[$key]['message_type'] = 'topic_response';
		}

		$neta['uid'] = $uid;
		$neta['status'] = 0;
		$activities = $activityResponseUserModel->where($neta)->select();
		foreach ($activities as $key => $val) {
			$activities[$key]['addtime'] = $activityResponseModel->getFieldById($val['response_id'], 'addtime');
			$activities[$key]['subject'] = getActivitySubjectById($activityResponseModel->getFieldById($val['response_id'], 'aid'));
			$activities[$key]['content'] = $activityResponseModel->getFieldById($val['response_id'], 'content');
			$activities[$key]['fid'] = $activityResponseModel->getFieldById($val['response_id'], 'fid');
			$activities[$key]['message_type'] = 'activity_response';
		}

		$info = array_merge_recursive($messages, $activities, $topics);

		foreach ($info as $key => $value) {
			$addtime[$key] = strtotime($value['addtime']);
		}
		array_multisort($addtime, $info);
		rsort($info);

		$count = count($info);
		$istart = $page_num * ($start - 1);

		$iend = ($page_num * $start > $count) ? $count : $page_num * $start;
		for ($i = $istart; $i < $iend; $i++) {
			$data[] = $info[$i];
		}

		$Page = new \Think\AjaxPage($count, $page_num, 'get_message_info', 'p');
		$Page->setConfig('theme', '%upPage%  %first%  %prePage%  %linkPage%  %nextPage% %end% %downPage%');
		$page = $Page->show();

		$this->assign('page', $page);
		$this->assign('data', $data);
	}
	public function getMessageInfoList() {
		$this->getMessageInfoPage();
		$this->display('my_message_info');
	}

}
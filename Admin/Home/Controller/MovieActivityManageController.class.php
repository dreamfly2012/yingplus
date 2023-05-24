<?php
/**
 * Created by PhpStorm.
 * User: roak
 * Date: 2015/9/19
 * Time: 17:51
 */

namespace Home\Controller;

/**
 * Class ActivityManageController
 * @package Home\Controller
 * @discribe 此类用户活动的管理
 */
class MovieActivityManageController extends CommonController {

	public function getActivityFensi() {
		$aid = I('post.aid', null, '');

		$ActivityEnroll = D('ActivityEnroll');

		$result = $ActivityEnroll->getAllFensiByAid($aid);

		$result = $this->getFensiData($result);

		$this->ajaxReturn($result, 'json');

	}

	public function getFensiData($result) {
		$User = D('User');
		$UserProfile = D('UserProfile');
		$Attachment = D('Attachment');
		foreach ($result as $key => $val) {
			$result[$key]['nickname'] = $User->getFieldById($val['uid'], 'nickname');
			$result[$key]['photo'] = $UserProfile->getPhotoByUid($val['uid']);

			if (is_numeric($result[$key]['photo'])) {
				$photo = $Attachment->getImgPathById($result[$key]['photo']);
				$result[$key]['photo'] = substr($photo, 1);
			}
		}
		return $result;
	}

	//查找活动详细信息
	public function findDetailActivity() {
		$aid = I('get.id', null, '');
		$Activity = D('Activity');
		$District = D('District');
		$activity = $Activity->getActivityById($aid);
		$activity = $this->getOneActivityData($activity);
		// 为省市准备数据
		$provinces = $District->getAllProvinces(); //得到所有省的数据
		$this->assign('activity', $activity);
		$this->assign('provinces', $provinces);
		$this->display('showDetailActivity');

	}

	//所有活动列表
	public function activityList() {
		$Activity = D('Activity');
		$District = D('District');
		$Forum = D('Forum');
		$fid = I('request.fid', null, ''); //工作室ID

		$province = I('request.province', null, ''); //活动举办的省份
		$city = I('request.city', null, ''); //活动举办的城市
		$begintime = I('request.begintime', null, ''); //活动添加时间开始范围
		$endtime = I('request.endtime', null, ''); //活动添加时间结束范围
		$ishot = I('request.ishot', null, ''); //是否是热门的活动
		$isrecommend = I('request.isrecommend', null, ''); //是否是推荐的活动
		$isdigest = I('request.isdigest', null, ''); //是否是加精的活动
		$activitykeys = I('request.activitykeys', null, '');
		$arr = array('fid' => $fid, 'addtime' => array('between', array($begintime, $endtime)),
			'holdprovince' => $province, 'holdcity' => $city,
			'isrecommend' => $isrecommend, 'isdigest' => $isdigest,
			'activitykeys' => $activitykeys, 'ishot' => $ishot, 'category' => 1,
		);

		$count = $Activity->where($this->buildQueryCondition($arr))->count();
		$Page = new \Think\Page($count, C('PAGE_LISTROWS'));
		parent::setPageConfig($Page);
		$show = $Page->show();
		$activityList = $Activity->where($this->buildQueryCondition($arr))->order(array('addtime' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$activityList = $this->getActivityData($activityList);

		// 为省市准备数据
		$movieOptionalPlaceModel = D('MovieOptionalPlace');
		$pids = $movieOptionalPlaceModel->field('pid')->where(array('status' => 0))->group('pid')->select();
		$provinces = array();
		foreach ($pids as $key => $val) {
			$provinces[$key]['id'] = $val['pid'];
			$provinces[$key]['name'] = getDistrictNameById($val['pid']);
		}
		$this->assign('provinces', $provinces);

		//查找所有星吧
		$forums = $Forum->select();
		$this->assign('forums', $forums);
		$this->assign('activityList', $activityList);
		$this->assign('page', $show);
		$this->assign('count', $count);
		$this->assign('inputinfo', $arr);
		$this->display('activity');
	}

	//影院获取
	public function getCinemaByPlace() {
		$cid = I('request.cid', null, 'intval');
		$pid = I('request.pid', null, 'intval');
		$moviePlaceModel = D('MoviePlace');
		$cinemas = $moviePlaceModel->where(array('pid' => $pid, 'cid' => $cid, 'status' => 0))->select();

		if (empty($cinemas)) {
			$this->ajaxReturn(array('status' => 2, 'info' => null));
		} else {
			$this->ajaxReturn(array('status' => 1, 'info' => $cinemas));
		}

	}

	//推荐活动列表展示
	public function recommendActivityList() {

		//得到所有尚未查看，设为推荐的活动
		$ActivityRecommend = D('ActivityRecommend');
		$count = $ActivityRecommend->getActivityCount();
		$Page = new \Think\Page($count, C('PAGE_LISTROWS'));
		parent::setPageConfig($Page);
		$show = $Page->show();
		$recommendActivity = $ActivityRecommend->getRecommendActivity($Page);
		$recommendActivity = $this->getRecommendActivityDate($recommendActivity);
		$this->assign('recommendActivity', $recommendActivity);
		$this->assign('page', $show);
		$this->display('recommendActivityList');
	}

	//推荐活动审核处理
	public function approveActivity() {
		$ActivityRecommend = D('ActivityRecommend');
		$Activity = D('Activity');
		$touid = I('get.touid', null, '');
		$id = I('get.id', null, '');
		$istrue = I('get.istrue', null, '');
		$aid = $ActivityRecommend->getFieldById($id, 'aid');
		//得到fid
		$fid = $ActivityRecommend->getFieldById($id, 'fid');
		if ($istrue == 1) {
			parent::commonSend(C('ACTIVITY_RECOMMEND_TRUE'), $touid, '推荐活动审核消息', $fid);
			$arr['isrecommend'] = 1;
			$arr['status'] = 0;
		} else {
			parent::commonSend(C('ACTIVITY_RECOMMEND_FALSE'), $touid, '推荐活动审核消息', $fid);
			$arr['isrecommend'] = 0;
			$arr['status'] = 0;
		}
		$ActivityRecommend->updateRecommend($id, $arr);
		//更新Activity表中活动的推荐
		$activity_arr['isrecommend'] = 1;
		$Activity->updateActivity($aid, $activity_arr);
		$this->recommendActivityList();
	}

	//活动编辑后保存
	public function saveActivity() {
		$Forum = D('Forum');
		$Attachment = D('Attachment');
		$Activity = D('Activity');
		$id = I('post.aid', null, '');
		$frounname = I('post.frounname', null, ''); //活动所属工作室
		$fid = $Forum->getForumIDByName($frounname);
		$uid = I('post.uid', null, '');

		$province = I('post.province', null, ''); //活动举办的城市
		$city = I('post.city', null, ''); //活动举办的城市
		$detailaddress = I('post.detailaddress', null, ''); //活动举办的详细地址
		$cinemaid = I('post.cinemaid', null, 'intval'); //影院id

		$enrollendtime = I('post.enrollendtime', null, ''); //活动报名截止时间

		$enrollendtime = strtotime($enrollendtime);

		$holdstart = I('post.holdstart', null, ''); //电影开始时间
		$holdstart = strtotime($holdstart);

		$enrolltotal = I('post.enrolltotal', null, ''); //报名人数上线
		$audit = I('post.audit', null, 'intval');
		$subject = I('post.subject', null);

		$hot = I('post.hot', null, ''); //活动热度
		$isadminrecommend = I('post.isadminrecommend', null, '');
		$updatetime = date('Y-m-d H:i:s', time());

		$arr = array('fid' => $fid, 'holdprovince' => $province, 'subject' => $subject,
			'holdcity' => $city, 'cinemaid' => $cinemaid, 'enrollendtime' => $enrollendtime, 'holdstart' => $holdstart, 'detailaddress' => $detailaddress,
			'enrolltotal' => $enrolltotal, 'updatetime' => $updatetime, 'hot' => $hot, 'audit' => $audit,
			'isadminrecommend' => $isadminrecommend,
		);

		$price = I('post.price', '');

		$activityMovieTicketModel = D('ActivityMovieTicket');
		$activity = $activityMovieTicketModel->where(array('aid' => $id))->find();
		if ($activity) {
			$activityMovieTicketModel->where(array('aid' => $id))->save(array('price' => $price));
		} else {
			$activityMovieTicketModel->add(array('price' => $price, 'aid' => $id));
		}

		$upload = new \Think\Upload();
		$upload->maxSize = 3145728;
		$upload->exts = array('jpg', 'gif', 'png');
		$upload->savePath = 'Activity/';
		$attachment_arr['fid'] = $fid;
		$attachment_arr['aid'] = $id;
		$attachment_arr['uid'] = $uid;
		$attachment_arr['isimage'] = 1;

		$info = $upload->uploadOne($_FILES['file_0']);
		if ($info) {
			$attachment_arr['filename'] = $info['name'];
			$attachment_arr['filesize'] = $info['size'];
			$path = '/uploads/' . $info['savepath'] . $info['savename'];
			$attachment_arr['path'] = $path;
			$imgid = $Attachment->saveAttachment($attachment_arr);
			//更新活动海报图片
			$arr['img'] = $imgid;
		}

		$info = $upload->uploadOne($_FILES['file_1']);
		if ($info) {
			$attachment_arr['filename'] = $info['name'];
			$attachment_arr['filesize'] = $info['size'];
			$path = '/uploads/' . $info['savepath'] . $info['savename'];
			$attachment_arr['path'] = $path;
			$imgid = $Attachment->saveAttachment($attachment_arr);
			//更新活动海报图片
			$arr['sourceimg'] = $imgid;
		}

		$info = $upload->uploadOne($_FILES['file_2']);
		if ($info) {
			$attachment_arr['filename'] = $info['name'];
			$attachment_arr['filesize'] = $info['size'];
			$path = '/uploads/' . $info['savepath'] . $info['savename'];
			$attachment_arr['path'] = $path;
			$imgid = $Attachment->saveAttachment($attachment_arr);
			//更新活动海报图片
			$arr['indexsmallimg'] = $imgid;
		}

		$info = $upload->uploadOne($_FILES['file_3']);
		if ($info) {
			$attachment_arr['filename'] = $info['name'];
			$attachment_arr['filesize'] = $info['size'];
			$path = '/uploads/' . $info['savepath'] . $info['savename'];
			$attachment_arr['path'] = $path;
			$imgid = $Attachment->saveAttachment($attachment_arr);
			//更新活动海报图片
			$arr['indexbigimg'] = $imgid;
		}
		$messagesend = $Activity->getFieldById($id, 'messagesend');

		if ($audit == 1) {
			//审核通过，发送消息，同时活动发起人变成已报名
			$activityEnrollModel = D('ActivityEnroll');
			$meta['aid'] = $id;
			$meta['uid'] = $uid;
			$meta['telephone'] = $Activity->getFieldById($id, 'telephone');
			$meta['addtime'] = time();
			$meta['ticketnum'] = 1;
			$exist = $activityEnrollModel->where(array('aid' => $id, 'uid' => $uid))->find();

			if (empty($exist)) {
				$activityEnrollModel->add($meta);
				$url = "http://" . $_SERVER['HTTP_HOST'] . "/index.php/home/MovieActivity/detail/aid/" . $id;
				$content = '恭喜你成功发起包场为了让更多的伙伴参与进来，贴吧，微博，QQ群都是不错的宣传渠道哦~ 号召更多的伙伴一起为爱豆加油吧！';
				$touid = $uid;
				$subject = '电影包场活动';
				$this->commonSend($content, $touid, $subject, $fid);
			}

		} else if ($audit == 2) {
			if ($messagesend == 0) {
				$content = '很抱歉，由于不可抗拒因素，您的活动未通过审核';
				$touid = $uid;
				$subject = '电影包场活动';
				$this->commonSend($content, $touid, $subject, $fid);
			}

		}

		$Activity->where(array('id' => $id))->save($arr);
		$this->redirect('showActivity', array('id' => $id));
	}

	//展示某个活动
	public function showActivity() {
		$aid = I('get.id', null, '');
		$Activity = D('Activity');
		$District = D('District');
		$activity = $Activity->getActivityById($aid);
		$activity = $this->getOneActivityData($activity);
		// 为省市准备数据
		$provinces = $District->getAllProvinces(); //得到所有省的数据
		$this->assign('activity', $activity);
		$this->assign('provinces', $provinces);



		$this->display('showActivity');
	}

	//首页推荐活动管理
	public function homeRecommendActivityList() {
		$this->display('homeRecommendActivity');
	}

	//根据省ID得到城市列表
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

	//封装推荐活动的数据
	public function getRecommendActivityDate($recommendActivity) {
		$Activity = D('Activity');
		$Forum = D('Forum');
		$District = D('District');
		$User = D('User');
		foreach ($recommendActivity as $key => $value) {
			$recommendActivity[$key]['aid'] = $value['aid'];
			//活动标题
			$recommendActivity[$key]['subject'] = $Activity->getActivitySubject($value['aid']);
			//活动创建者
			$recommendActivity[$key]['creater'] = $User->getUserNickNameByUID($value['uid']);
			//活动所属星吧
			$recommendActivity[$key]['forumname'] = $Forum->getActivityForum($value['fid']);
			//活动类型
			$recommendActivity[$key]['type'] = $Activity->getActivityType($value['aid']);
			$recommendActivity[$key]['type'] = C('ACTIVITY_TYPE_' . $recommendActivity[$key]['type']);
			//活动地点，只显示活动所属城市
			$recommendActivity[$key]['holdprovince'] = $District->getProvince($Activity->getActivityHoldProvince($value['aid']));
			$recommendActivity[$key]['holdcity'] = $District->getCity($Activity->getActivityHoldCity($value['aid']));
			//活动添加时间
			$recommendActivity[$key]['addtime'] = date('Y-m-d H:i:s', $value['addtime']);
		}
		return $recommendActivity;
	}

	//封装数据已为前端页面进行展示
	public function getActivityData($activityList) {

		$District = D('District');
		$User = D('User');
		$Forum = D('Forum');
		foreach ($activityList as $key => $value) {
			$activityList[$key]['holdprovince'] = $District->getProvince($value['holdprovince']);
			$activityList[$key]['holdcity'] = $District->getCity($value['holdcity']);
			$activityList[$key]['holdname'] = $User->getUserNickNameByUID($value['uid']);
			$activityList[$key]['forumname'] = $Forum->getFieldById($value['fid'], 'name');
			$activityList[$key]['type'] = C('ACTIVITY_TYPE_' . $activityList[$key]['type']);
			$activityList[$key]['addtime'] = date('Y-m-d H:i:s', $activityList[$key]['addtime']);
			$activityList[$key]['status'] = C('ACTIVITY_STATUS_' . $activityList[$key]['status']);
		}
		return $activityList;
	}

	//封装某个活动信息用于前端展示
	public function getOneActivityData($activity) {

		$Forum = D('Forum');
		$User = D('User');
		$Attachment = D('Attachment');
		$activity['forumname'] = $Forum->getFieldById($activity['fid'], 'name');
		$activity['nickname'] = $User->getFieldById($activity['uid'], 'nickname');
		$activity['holdstart'] = date('Y-m-d H:i:s', $activity['holdstart']);
		$activity['holdend'] = date('Y-m-d H:i:s', $activity['holdend']);
		$activity['enrollendtime'] = date('Y-m-d H:i:s', $activity['enrollendtime']);
		$activity['enrollstartime'] = date('Y-m-d H:i:s', $activity['enrollstartime']);
		$activity['img'] = $Attachment->getFieldById($activity['img'], 'path');
		$activity['sourceimg'] = $Attachment->getFieldById($activity['sourceimg'], 'path');
		$activity['indexsmallimg'] = $Attachment->getFieldById($activity['indexsmallimg'], 'path');
		$activity['indexbigimg'] = $Attachment->getFieldById($activity['indexbigimg'], 'path');
		return $activity;
	}

	//为活动查询准备sql语句的条件部分
	public function buildQueryCondition($arr) {
		if (!empty($arr['fid'])) {
			$data['fid'] = $arr['fid'];
		}

		if (!empty($arr['province'])) {
			$data['holdprovince'] = $arr['holdprovince'];
		}
		if (!empty($arr['city'])) {
			$data['holdcity'] = $arr['holdcity'];
		}
		if (!empty($arr['begintime']) && !empty($arr['endtime'])) {
			$data['addtime'] = array('between', array($arr['begintime'], $arr['endtime']));
		}

		if (!empty($arr['ishot'])) {
			$data['ishot'] = 1;
		}
		if (!empty($arr['isrecommend'])) {
			$data['isrecommend'] = 1;
		}
		if (!empty($arr['isdigest'])) {
			$data['isdigest'] = 1;
		}

		if (!empty($arr['activitykeys'])) {
			$data['subject'] = array('like', '%' . $arr['activitykeys'] . '%');
		}
		$data['category'] = 1;
		return $data;
	}
}
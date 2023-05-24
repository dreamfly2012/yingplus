<?php

namespace Home\Controller;

class ActivityMovieController extends CommonController {
	//组团看电影
	public function index() {
		$forum = D('Forum');
		$forumMovie = D('ForumMovie');
		$forums = $forum->where(array('status' => 1))->select();
		$this->assign('forums', $forums);

		$count = $forumMovie->where(array('status' => array('neq', 2)))->count();
		$Page = new \Think\Page($count, C('PAGE_LISTROWS'));
		parent::setPageConfig($Page);
		$show = $Page->show();
		$movieList = $forumMovie->where(array('status' => array('neq', 2)))->select();
		//$videoList = $this->getVideoData($videoList);
		$this->assign('page', $show);
		$this->assign('movieList', $movieList);

		$this->display('index');
	}

	public function filterMovie() {
		$fid = I('request.fid', null, 'intval');
		$this->assign('fid', $fid);
		$forumMovieModel = D('ForumMovie');
		$forum = D('Forum');
		$forums = $forum->where(array('status' => 1))->select();
		$this->assign('forums', $forums);
		$condition['status'] = array('neq', 2);
		$condition['_string'] = 'FIND_IN_SET(' . $fid . ', fid)';
		$meta['_string'] = 'FIND_IN_SET(' . $fid . ', fid)';
		$count = $forumMovieModel->where($condition)->count();

		$Page = new \Think\Page($count, C('PAGE_LISTROWS'));
		parent::setPageConfig($Page);
		$show = $Page->show();
		$movieList = $forumMovieModel->where($meta)
			->limit($Page->firstRow . ',' . $Page->listRows)
			->order('releasetime desc')
			->select();

		$this->assign('page', $show);
		$this->assign('movieList', $movieList);
		$this->display('index');
	}

	public function addMovie() {
		$forum = D('Forum');
		$forums = $forum->where(array('status' => 1))->select();
		$this->assign('forums', $forums);
		$this->display('addMovie');
	}

	public function add() {
		$title = I('post.title', null, '');
		$fid = I('post.fid', null, '');
		$rule = I('post.rule', null, '');
		$box_office_id = I('post.box_office_id', null, '');
		$desc = I('post.desc', null, '');
		$releasetime = strtotime(I('post.releasetime'));
		$status = I('post.status', 0, '');
		$fid = implode(',', $fid);
		$arr = array(
			'fid' => $fid,
			'title' => $title,
			'rule' => $rule,
			'desc' => $desc,
			'box_office_id' => $box_office_id,
			'releasetime' => $releasetime,
			'status' => $status,
		);
		if (empty($fid) || empty($title) || empty($rule) || empty($desc)) {
			$this->error('请填写工作室，电影名称，活动规则，电影简介');
		}

		$Attachment = D('Attachment');
		$upload = new \Think\Upload();
		$upload->maxSize = 51457288;
		$upload->exts = array('jpg', 'gif', 'png');
		$upload->savePath = 'MovieActivity/';
		$attachment_arr['fid'] = $fid;
		$attachment_arr['isimage'] = 1;

		$info = $upload->uploadOne($_FILES['file_0']);
		if ($info) {
			$attachment_arr['filename'] = $info['name'];
			$attachment_arr['filesize'] = $info['size'];
			$attachment_arr['path'] = '/uploads/' . $info['savepath'] . $info['savename'];
			$imgid = $Attachment->saveAttachment($attachment_arr);
			//更新活动海报图片
			$arr['poster'] = $imgid;
		}

		$mobile_banner_info = $upload->upload(array($_FILES['file_1']));
		if ($mobile_banner_info) {
			$imgids = "";
			foreach ($mobile_banner_info as $key => $val) {
				$attachment_arr['filename'] = $val['name'];
				$attachment_arr['filesize'] = $val['size'];
				$attachment_arr['path'] = '/uploads/' . $val['savepath'] . $val['savename'];
				$imgid = $Attachment->saveAttachment($attachment_arr);
				$imgids .= $imgid . ',';
			}
			$arr['mobile_banner'] = $imgids;
		}

		$forumMovieModel = D('ForumMovie');
		$forumMovieModel->add($arr);
		$this->redirect('ActivityMovie/index');
	}

	public function editMovie() {
		$id = I('request.id', null, 'intval');
		$forum = D('Forum');
		$forums = $forum->where(array('status' => 1))->select();
		$this->assign('forums', $forums);

		$forumMovieModel = D('ForumMovie');
		$movie = $forumMovieModel->where(array('id' => $id))->find();
		$mobile_banners = trim($movie['mobile_banner'], ',');
		$mobile_banners_arr = explode(',', $mobile_banners);
		$this->assign('mobile_banners', $mobile_banners_arr);
		$this->assign('movie', $movie);
		$this->assign('fid',$movie['fid']);
		$this->display('editMovie');
	}

	public function edit() {
		$id = I('post.id', null, 'intval');
		$title = I('post.title', null, '');
		$fid = I('post.fid', null, '');
		$rule = I('post.rule', null, '');
		$desc = I('post.desc', null, '');
		$box_office_id = I('post.box_office_id', null, '');
		$releasetime = strtotime(I('post.releasetime'));
		$status = I('post.status', 0, '');
		$fid = implode(',', $fid);
		$arr = array(
			'id' => $id,
			'fid' => $fid,
			'title' => $title,
			'rule' => $rule,
			'box_office_id' => $box_office_id,
			'desc' => $desc,
			'releasetime' => $releasetime,
			'status' => $status,
		);
		if (empty($fid) || empty($title) || empty($rule) || empty($desc)) {
			$this->error('请填写工作室，电影名称，活动规则，电影简介');
		}
		$forumMovieModel = D('ForumMovie');
		$Attachment = D('Attachment');

		$upload = new \Think\Upload();
		$upload->maxSize = 3145728;
		$upload->exts = array('jpg', 'gif', 'png');
		$upload->savePath = 'MovieActivity/';
		$attachment_arr['fid'] = $fid;
		$attachment_arr['isimage'] = 1;

		$info = $upload->uploadOne($_FILES['file_0']);
		if ($info) {
			$attachment_arr['filename'] = $info['name'];
			$attachment_arr['filesize'] = $info['size'];
			$path = '/uploads/' . $info['savepath'] . $info['savename'];
			$attachment_arr['path'] = $path;
			$imgid = $Attachment->saveAttachment($attachment_arr);
			//更新活动海报图片
			$arr['poster'] = $imgid;
		}

		$mobile_banner = $forumMovieModel->where(array('id' => $id))->getFieldById($id, 'mobile_banner');

		$mobile_banner_info = $upload->upload(array($_FILES['file_1']));
		if ($mobile_banner_info) {
			$imgids = "";
			foreach ($mobile_banner_info as $key => $val) {
				$attachment_arr['filename'] = $val['name'];
				$attachment_arr['filesize'] = $val['size'];
				$attachment_arr['path'] = '/uploads/' . $val['savepath'] . $val['savename'];
				$imgid = $Attachment->saveAttachment($attachment_arr);
				$imgids .= $imgid . ',';
			}
			$arr['mobile_banner'] = $mobile_banner . $imgids;
		}

		$forumMovieModel->where(array('id' => $id))->save($arr);
		$this->redirect('ActivityMovie/index');
	}

	public function deleteMobileBanner() {
		$ids = I('request.ids', null);
		$id = I('request.id', null);
		$ids_arr = explode(',', $ids);

		$forumMovieModel = D('ForumMovie');
		$mobile_banner = $forumMovieModel->getFieldById($id, 'mobile_banner');
		$mobile_banner_arr = explode(',', $mobile_banner);
		foreach ($mobile_banner_arr as $key => $val) {
			if (in_array($val, $ids_arr)) {
				unset($mobile_banner_arr[$key]);
			}
		}
		$mobile_banner = implode(',', $mobile_banner_arr);
		$mobile_banner = $mobile_banner . ",";
		$forumMovieModel->where(array('id' => $id))->setField(array('mobile_banner' => $mobile_banner));
		$this->ajaxReturn(array('code' => 0, 'info' => 'success', 'message' => '删除成功'));

	}

	public function delMovie() {
		$id = I('request.id', null, 'intval');
		$forumMovieModel = D('ForumMovie');
		$forumMovieModel->where(array('id' => $id))->setField(array('status' => 2));
		$this->redirect('ActivityMovie/index');
	}

	public function deleteAllMovie() {
		$arr = I('post.test');
		$forumMovieModel = D('ForumMovie');
		for ($i = 0; $i < count($arr); $i++) {
			$forumMovieModel->where(array('id' => $arr[$i]))->setField(array('status' => 2));
		}
	}

}
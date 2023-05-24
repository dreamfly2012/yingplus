<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/1/19
 * Time: 10:53
 */

namespace Home\Controller;

class MovieActivityController extends CommonController {
	//包场活动视频管理
	public function index() {
		$forumMovieModel = D('ForumMovie');
		$movies = $forumMovieModel->where(array('status' => array('eq', 1)))->select();
		$this->assign('movies', $movies);

		$activityMovieVideo = D('ActivityMovieVideo');
		$count = $activityMovieVideo->getVideoCount();
		$Page = new \Think\Page($count, C('PAGE_LISTROWS'));
		parent::setPageConfig($Page);
		$show = $Page->show();
		$videoList = $activityMovieVideo->getAllVideoes($Page);
		$videoList = $this->getVideoData($videoList);
		$this->assign('page', $show);
		$this->assign('videoList', $videoList);
		$this->display('MovieActivity/index');
	}

	public function filterVideo() {
		$mid = I('request.mid', null, 'intval');
		$this->assign('mid', $mid);
		$forumMovieModel = D('ForumMovie');
		$movies = $forumMovieModel->where(array('status' => array('eq', 1)))->select();
		$this->assign('movies', $movies);
		$activityMovieVideo = D('ActivityMovieVideo');
		$count = $activityMovieVideo->where(array('status' => 0, 'mid' => $mid))->count();
		$Page = new \Think\Page($count, C('PAGE_LISTROWS'));
		parent::setPageConfig($Page);
		$show = $Page->show();
		$videoList = $activityMovieVideo->where(array('status' => 0, 'mid' => $mid))
			->limit($Page->firstRow . ',' . $Page->listRows)
			->order('addtime desc')
			->select();
		$videoList = $this->getVideoData($videoList);
		$this->assign('page', $show);
		$this->assign('videoList', $videoList);
		$this->display('MovieActivity/index');
	}

	public function addVideo() {
		$forumMovieModel = D('ForumMovie');
		$movies = $forumMovieModel->where(array('status' => array('eq', 1)))->select();
		$this->assign('movies', $movies);
		$this->display('MovieActivity/addVideo');
	}

	public function add() {
		$title = I('post.title', null, '');
		$mid = I('post.mid', null, '');
		$url = I('post.url', null, '');
		$mobileurl = I('post.mobileurl', null, '');
		$synopsis = I('post.synopsis', null, '');
		$addition = I('post.addition', null, '');
		$favors = I('post.favors', null, '');
		$addtime = time();
		$arr = array(
			'mid' => $mid,
			'title' => $title,
			'url' => $url,
			'mobileurl' => $mobileurl,
			'synopsis' => $synopsis,
			'addition' => $addition,
			'favors' => $favors,
			'addtime' => $addtime,
		);
		if (empty($url) || empty($title)) {
			$this->error('标题，链接不能为空');
		}
		$activityMovieVideo = D('ActivityMovieVideo');
		$upload = new \Think\Upload(); // 实例化上传类
		$upload->maxSize = 3145728; // 设置附件上传大小
		$upload->exts = array('jpg', 'gif', 'png', 'jpeg'); // 设置附件上传类型
		$upload->savePath = 'Video/'; // 设置附件上传目录
		$info = $upload->uploadOne($_FILES['cover']);
		if ($info) {
			$path = '/Uploads/' . $info['savepath'] . $info['savename'];
			$arr['cover'] = $path;
		}
		$activityMovieVideo->add($arr);
		$this->redirect('MovieActivity/index');
	}

	public function showVideo() {
		$id = I('get.id', null, '');
		$forumMovieModel = D('ForumMovie');
		$movies = $forumMovieModel->where(array('status' => array('eq', 1)))->select();
		$this->assign('movies', $movies);
		$activityMovieVideo = D('ActivityMovieVideo');
		$mid = $activityMovieVideo->where(array('id' => $id))->getField('mid');
		$video = $activityMovieVideo->where(array('id' => $id))->find();
		$this->assign('video', $video);
		$this->assign('mid', $mid);
		$this->display('MovieActivity/editVideo');
	}

	public function updateVideo() {
		$id = I('post.id', null, '');
		$title = I('post.title', null, '');
		$mid = I('post.mid', null, '');
		$url = I('post.url', null, '');
		$mobileurl = I('post.mobileurl', null, '');
		$synopsis = I('post.synopsis', null, '');
		$addition = I('post.addition', null, '');
		$favors = I('post.favors', null, '');
		$order = I('post.order', null, '');
		$arr = array(
			'id' => $id,
			'mid' => $mid,
			'title' => $title,
			'synopsis' => $synopsis,
			'addition' => $addition,
			'favors' => $favors,
			'url' => $url,
			'mobileurl' => $mobileurl,
			'order' => $order,
		);
		if (empty($url) || empty($title)) {
			$this->error('标题，链接不能为空');
		}
		$activityMovieVideo = D('ActivityMovieVideo');
		$upload = new \Think\Upload(); // 实例化上传类
		$upload->maxSize = 5242880; // 设置附件上传大小
		$upload->exts = array('jpg', 'gif', 'png', 'jpeg'); // 设置附件上传类型
		$upload->savePath = ''; // 设置附件上传目录
		$info = $upload->uploadOne($_FILES['cover']);
		if ($info) {
			$path = '/uploads/' . $info['savepath'] . $info['savename'];
			$arr['cover'] = $path;
		}
		$activityMovieVideo->save($arr);
		$this->redirect('MovieActivity/index');
	}

	public function deleteAllVideo() {
		$arr = I('post.test');
		$activityMovieVideo = D('ActivityMovieVideo');
		for ($i = 0; $i < count($arr); $i++) {
			$activityMovieVideo->updateVideo($arr[$i]);
		}
	}

	public function delVideo() {
		$id = I('get.id', null, '');
		$arr = array('id' => $id, 'status' => 1);
		$activityMovieVideo = D('ActivityMovieVideo');
		$activityMovieVideo->save($arr);
		$this->redirect('MovieActivity/index');
	}
	public function getVideoData($videoList) {
		$Forum = D('Forum');
		foreach ($videoList as $key => $val) {
			$videoList[$key]['forumname'] = $Forum->getFieldById($val['fid'], 'name');
			$videoList[$key]['time'] = date('Y-m-d H:i:s', $val['addtime']);
		}
		return $videoList;
	}
}
<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/12/28
 * Time: 14:22
 */

namespace Home\Controller;

class VideoManageController extends CommonController {

	public function index() {
		$Video = D('Video');
		$forum = D('Forum');
		$forums = $forum->where(array('status' => 1))->select();
		$this->assign('forums', $forums);
		$count = $Video->getVideoCount();
		$Page = new \Think\Page($count, C('PAGE_LISTROWS'));
		parent::setPageConfig($Page);
		$show = $Page->show();
		$videoList = $Video->getAllVideoes($Page);
		$videoList = $this->getVideoData($videoList);
		$this->assign('page', $show);
		$this->assign('videoList', $videoList);
		$this->display('Video/index');
	}

	public function filterVideo() {
		$fid = I('request.fid', null, 'intval');
		$this->assign('fid', $fid);
		$Video = D('Video');
		$forum = D('Forum');
		$forums = $forum->where(array('status' => 1))->select();
		$this->assign('forums', $forums);
		$count = $Video->where(array('status' => 0, 'fid' => $fid))->count();
		$Page = new \Think\Page($count, C('PAGE_LISTROWS'));
		parent::setPageConfig($Page);
		$show = $Page->show();
		$videoList = $Video->where(array('status' => 0, 'fid' => $fid))
			->limit($Page->firstRow . ',' . $Page->listRows)
			->order('addtime desc')
			->select();
		$videoList = $this->getVideoData($videoList);
		$this->assign('page', $show);
		$this->assign('videoList', $videoList);
		$this->display('Video/index');
	}

	public function addVideo() {
		$forum = D('Forum');
		$forums = $forum->where(array('status' => 1))->select();
		$this->assign('forums', $forums);
		$this->display('Video/addVideo');
	}

	public function add() {
		$title = I('post.title', null, '');
		$source = I('post.source', null, '');
		$fid = I('post.fid', null, '');
		$url = I('post.url', null, '');
		$mobileurl = I('post.mobileurl', null, '');
		$flashurl = I('post.flashurl', null, '');
		$synopsis = I('post.synopsis', null, '');
		$addition = I('post.addition', null, '');
		$addition2 = I('post.addition2', null, '');
		$addition3 = I('post.addition3', null, '');
		$favors = I('post.favors', null, '');
		$addtime = time();
		$arr = array(
			'fid' => $fid,
			'title' => $title,
			'source' => $source,
			'url' => $url,
			'mobileurl' => $mobileurl,
			'flashurl' => $flashurl,
			'synopsis' => $synopsis,
			'addition' => $addition,
			'addition2' => $addition2,
			'addition3' => $addition3,
			'favors' => $favors,
			'addtime' => $addtime,
		);
		$Video = D('Video');
		$upload = new \Think\Upload(); // 实例化上传类
		$upload->maxSize = 3145728; // 设置附件上传大小
		$upload->exts = array('jpg', 'gif', 'png', 'jpeg'); // 设置附件上传类型
		$upload->savePath = 'Video/'; // 设置附件上传目录
		$info = $upload->uploadOne($_FILES['cover']);
		if ($info) {
			$path = '/uploads/' . $info['savepath'] . $info['savename'];
			$arr['cover'] = $path;
		}
		$Video->add($arr);
		$this->redirect('VideoManage/index');
	}

	public function showVideo() {
		$id = I('get.id', null, '');
		$Video = D('Video');
		$fid = $Video->getFieldById($id, 'fid');
		$video = $Video->getVideoById($id);
		$hot = $Video->getMaxHot($fid);
		$forum = D('Forum');
		$forums = $forum->where(array('status' => 1))->select();
		$this->assign('forums', $forums);
		$this->assign('video', $video);
		$this->assign('hot', $hot);
		$this->display('Video/editVideo');
	}

	public function updateVideo() {
		$id = I('post.id', null, '');
		$title = I('post.title', null, '');
		$source = I('post.source', null, '');
		$fid = I('post.fid', null, '');
		$url = I('post.url', null, '');
		$mobileurl = I('post.mobileurl', null, '');
		$flashurl = I('post.flashurl', null, '');
		$synopsis = I('post.synopsis', null, '');
		$addition = I('post.addition', null, '');
		$addition2 = I('post.addition2', null, '');
		$addition3 = I('post.addition3', null, '');
		$favors = I('post.favors', null, '');
		$order = I('post.order', null, '');
		$arr = array(
			'id' => $id,
			'fid' => $fid,
			'title' => $title,
			'source' => $source,
			'synopsis' => $synopsis,
			'addition' => $addition,
			'addition2' => $addition2,
			'addition3' => $addition3,
			'favors' => $favors,
			'url' => $url,
			'mobileurl' => $mobileurl,
			'flashurl' => $flashurl,
			'order' => $order,
		);
		$Video = D('Video');
		$upload = new \Think\Upload(); // 实例化上传类
		$upload->maxSize = 3145728; // 设置附件上传大小
		$upload->exts = array('jpg', 'gif', 'png', 'jpeg'); // 设置附件上传类型
		$upload->savePath = 'Video/'; // 设置附件上传目录
		$info = $upload->uploadOne($_FILES['cover']);
		if ($info) {
			$path = '/uploads/' . $info['savepath'] . $info['savename'];
			$arr['cover'] = $path;
		}
		$Video->save($arr);
		$this->redirect('VideoManage/index');
	}

	public function deleteAllVideo() {
		$arr = I('post.test');
		$Video = D('Video');
		for ($i = 0; $i < count($arr); $i++) {
			$Video->updateVideo($arr[$i]);
		}
	}

	public function delVideo() {
		$id = I('get.id', null, '');
		$arr = array('id' => $id, 'status' => 1);
		$Video = D('Video');
		$Video->save($arr);
		$this->redirect('VideoManage/index');
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
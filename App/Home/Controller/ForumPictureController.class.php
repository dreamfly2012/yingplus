<?php

namespace Home\Controller;

class ForumPictureController extends CommonController{
	//照片墙
    public function listing($fid,$p,$number)
    {
        ($number > 50) ? $number = 50 : '';
     
        if (empty($fid)) {
            $info    = null;
            $code    = -1;
            $message = C('parameter_invalid');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $forumPictureModel   = D('ForumPicture');
        $condition['fid']    = $fid;
        $condition['status'] = 0;
        $count = $forumPictureModel->where($condition)->count();
        $pictures            = $forumPictureModel->where($condition)->order(array('id'=>'desc'))->limit(($p-1)*$number,$number)->select();
        foreach($pictures as $key=>$val){
            $pictures[$key]['url'] = getAttachmentUrlById($val['attachmentid']);
        }
        $Page = new \Think\Page($count,$number);
        $show = $Page->show();
        $info['data'] = $pictures;
        $info['count'] = $count;
        $info['page'] = $show;
        return $info;
    }

    //获取照片墙信息
    public function getlisting()
    {
        $fid    = I('request.fid', null, 'intval');
        $p = I('request.p',1,'intval');
        $number = I('request.number', 10, 'intval');
        $pictures = $this->listing($fid,$p,$number);
        $info     = $pictures;
        $code     = 0;
        $message  = '照片墙信息';
        $return   = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    //上传照片墙
    public function add()
    {
        $attachmentid = I('request.attachmentid', null, 'intval');
        $fid          = I('request.fid', null, 'intval');
        $uid          = $this->getUid();

        if (empty($uid)) {
            $info    = null;
            $code    = 2;
            $message = C('no_login');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        if (empty($attachmentid) || empty($fid)) {
            $info    = null;
            $code    = -1;
            $message = C('parameter_invalid');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $condition['fid'] = $fid;
        $condition['uid'] = $uid;
        $condition['attachmentid'] = $attachmentid;
        $condition['addtime'] = time();

        $forums = $this->getUserForum($uid);
        $bool = $this->checkInForum($fid, $forums);
        if (!$bool) {
            $info = 'not_in_forum';
            $code = 1;
            $message = '请先加入工作室';
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $forumPictureModel = D('ForumPicture');
        $id = $forumPictureModel->add($condition);
        $info = $id;
        $code = 0;
        $message = '添加照片墙成功';
        $return = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }
}
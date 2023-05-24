<?php

namespace Home\Controller;

class CollectController extends CommonController
{

    //收藏（TODO:验证请求pid不存在）
    public function collect()
    {
        $uid  = $this->getUid();
        $type = I('request.type', null, 'intval');
        $pid  = I('request.pid', null, 'intval');

        //没有登录
        if (empty($uid)) {
            $info    = null;
            $code    = 2;
            $message = C('no_login');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        //参数错误
        if (empty($pid) || empty($type)) {
            $info    = null;
            $code    = -1;
            $message = C('parameter_invalid');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $type_arr = array(1, 2, 3);
        if (!in_array($type, $type_arr)) {
            $info    = null;
            $code    = -1;
            $message = C('parameter_invalid');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $this->checkcollectValid($pid, $type);

        $collectModel        = D('Collect');
        $condition['type']   = $type;
        $condition['pid']    = $pid;
        $condition['uid']    = $uid;
        $condition['status'] = 0;
        $exist               = $collectModel->where($condition)->find();

        if ($exist) {
            $info    = null;
            $code    = 1;
            $message = '已经被收藏';
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $meta['uid']  = $uid;
        $meta['pid']  = $pid;
        $meta['type'] = $type;
        $bool         = $collectModel->where($meta)->find();

        if ($bool) {
            $this->collectTotal($pid, $type, 'add');
            $collectModel->where($meta)->setField(array('status' => 0));
            $info    = null;
            $code    = 0;
            $message = '收藏成功';
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
        $meta['addtime'] = time();
        $this->collectTotal($pid, $type, 'add');
        $collectModel->add($meta);
        $info    = null;
        $code    = 0;
        $message = '收藏成功';
        $return  = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    //取消收藏
    public function cancelcollect()
    {
        $uid  = $this->getUid();
        $type = I('request.type', null, 'intval');
        $pid  = I('request.pid', null, 'intval');

        //没有登录
        if (empty($uid)) {
            $info    = null;
            $code    = 2;
            $message = C('no_login');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        //参数错误
        if (empty($pid) || empty($type)) {
            $info    = null;
            $code    = -1;
            $message = C('parameter_invalid');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $type_arr = array(1, 2, 3);
        if (!in_array($type, $type_arr)) {
            $info    = null;
            $code    = -1;
            $message = C('parameter_invalid');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $this->checkcollectValid($pid, $type);

        $collectModel      = D('Collect');
        $condition['type'] = $type;
        $condition['pid']  = $pid;
        $condition['uid']  = $uid;
        $exist             = $collectModel->where($condition)->find();

        if (!$exist) {
            $info    = null;
            $code    = 1;
            $message = '没有收藏';
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        //取消收藏
        $meta['uid']    = $uid;
        $meta['pid']    = $pid;
        $meta['type']   = $type;
        $meta['status'] = 1;
        $bool           = $collectModel->where($meta)->find();

        if ($bool) {
            $info    = null;
            $code    = 1;
            $message = '已经取消过收藏';
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
        $this->collectTotal($pid, $type, 'sub');
        unset($meta['status']);
        $collectModel->where($meta)->setField(array('status' => 1));
        $info    = null;
        $code    = 0;
        $message = '取消收藏成功';
        $return  = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    //收藏总数统计
    public function collectTotal($pid, $type, $method)
    {
        if ($type == 1) {
            $topicModel = D('Topic');
            if ($method == 'add') {
                $topicModel->where(array('id' => $pid))->setInc('collects');
            } else {
                $topicModel->where(array('id' => $pid))->setDec('collects');
            }

        } elseif ($type == 2) {
            $activityModel = D('Activity');
            if ($method == 'add') {
                $activityModel->where(array('id' => $pid))->setInc('collects');
            } else {
                $activityModel->where(array('id' => $pid))->setDec('collects');
            }
        } elseif ($type == 3) {
            $videoModel = D('Video');
            if ($method == 'add') {
                $videoModel->where(array('id' => $pid))->setInc('collects');
            } else {
                $videoModel->where(array('id' => $pid))->setDec('collects');
            }
        }
    }

    //查看收藏主题是否存在
    public function checkcollectValid($pid, $type){
    	if ($type == 1) {
            $topicModel = D('Topic');
            $condition['id'] = $pid;
            $condition['status'] = array('neq',1);
            $bool =  $topicModel->where($condition)->find();
            if(empty($bool)){
            	$info = null;
            	$code = 1;
            	$mssage = '话题不存在';
            	$return = $this->buildReturn($info, $code, $message);
            	$this->ajaxReturn($return);
            }

        } elseif ($type == 2) {
            $activityModel = D('Activity');
            $condition['id'] = $pid;
            $condition['status'] = array('neq',1);
            $condition['audit'] = 1;
            $bool =  $activityModel->where($condition)->find();
            if(empty($bool)){
            	$info = null;
            	$code = 1;
            	$mssage = '活动不存在';
            	$return = $this->buildReturn($info, $code, $message);
            	$this->ajaxReturn($return);
            }
        } elseif ($type == 3) {
            $videoModel = D('Video');
            $condition['id'] = $pid;
            $condition['status'] = array('neq',1);
            $bool =  $activityModel->where($condition)->find();
            if(empty($bool)){
            	$info = null;
            	$code = 1;
            	$mssage = '视频不存在';
            	$return = $this->buildReturn($info, $code, $message);
            	$this->ajaxReturn($return);
            }
        }
    }
}

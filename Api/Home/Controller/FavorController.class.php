<?php

namespace Home\Controller;

class FavorController extends CommonController
{
    //点赞 取消点赞x
    public function favor()
    {
        $pid  = I('request.pid', null, 'intval');
        $type = I('request.type', null, 'intval');
        $type = I('request.token', null);
        $uid  = $this->get_uid_by_token($token);

        //未登录
        if (empty($uid)) {
            $info    = null;
            $code    = 0;
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

        $type_arr = array(1, 2, 3, 4, 5, 6, 7, 8, 9);
        if (!in_array($type, $type_arr)) {
            $info    = null;
            $code    = -1;
            $message = C('parameter_invalid');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $this->checkfavorValid($pid, $type);

        $favorModel          = D('Favor');
        $condition['pid']    = $pid;
        $condition['uid']    = $uid;
        $condition['type']   = $type;
        $condition['stauts'] = 0;
        $exist               = $favorModel->where($condition)->find();
        if ($exist) {
            //取消点赞
            $meta['uid']  = $uid;
            $meta['pid']  = $pid;
            $meta['type'] = $type;
            $count = $this->favorTotal($pid, $type, 'sub');

            $favorModel->add($meta);
            $info    = $count;
            $code    = 1;
            $message = '取消点赞成功';
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        } else {
            $meta['uid']  = $uid;
            $meta['pid']  = $pid;
            $meta['type'] = $type;
            $bool         = $favorModel->where($meta)->find();

            if ($bool) {
                $count = $this->favorTotal($pid, $type, 'add');
                $favorModel->where($meta)->setField(array('status' => 0));
                $info  = $count;
                $code    = 2;
                $message = '点赞成功';
                $return  = $this->buildReturn($info, $code, $message);
                $this->ajaxReturn($return);
            } else {
                $meta['addtime'] = time();
                $favorModel->add($meta);
               
                $code            = 2;
                $message         = '点赞成功';
                $count = $this->favorTotal($pid, $type, 'add');
                $info  = $count;
                $return = $this->buildReturn($info, $code, $message);
                $this->ajaxReturn($return);
            }
        }
    }
    //点赞总数统计
    public function favorTotal($pid, $type, $method)
    {
        if ($type == 1) {
            $topicModel = D('Topic');
            if ($method == 'add') {
                $topicModel->where(array('id' => $pid))->setInc('favors');
            } else {
                $topicModel->where(array('id' => $pid))->setDec('favors');
            }
            $count_info = $topicModel->where(array('id' => $pid))->field('favors')->select();
            return $count_info['favors'];
        } elseif ($type == 2) {
            $activityModel = D('Activity');
            if ($method == 'add') {
                $activityModel->where(array('id' => $pid))->setInc('favors');
            } else {
                $activityModel->where(array('id' => $pid))->setDec('favors');
            }
            $count_info = $activityModel->where(array('id' => $pid))->field('favors')->select();
            return $count_info['favors'];
        } elseif ($type == 3) {
            $videoModel = D('Video');
            if ($method == 'add') {
                $videoModel->where(array('id' => $pid))->setInc('favors');
            } else {
                $videoModel->where(array('id' => $pid))->setDec('favors');
            }
            $count_info = $videoModel->where(array('id' => $pid))->field('favors')->select();
            return $count_info['favors'];
        } elseif ($type == 4) {
            $forumStoryModel = D('ForumStory');
            if ($method == 'add') {
                $forumStoryModel->where(array('id' => $pid))->setInc('favors');
            } else {
                $forumStoryModel->where(array('id' => $pid))->setDec('favors');
            }
            $count_info = $forumPassageModel->where(array('id' => $pid))->field('favors')->select();
            return $count_info['favors'];
        } elseif ($type == 5) {
            $forumPassageModel = D('ForumPassage');
            if ($method == 'add') {
                $forumPassageModel->where(array('id' => $pid))->setInc('favors');
            } else {
                $forumPassageModel->where(array('id' => $pid))->setDec('favors');
            }
            $count_info = $forumPassageModel->where(array('id' => $pid))->field('favors')->select();
            return $count_info['favors'];
        } elseif ($type == 6) {
            $responseModel = D('Response');
            if ($method == 'add') {
                $responseModel->where(array('id' => $pid))->setInc('favors');
            } else {
                $responseModel->where(array('id' => $pid))->setDec('favors');
            }
            $count_info = $responseModel->where(array('id' => $pid))->field('favors');
            return $count_info['favors'];
        } elseif ($type == 7) {
            $activityOnlineModel = D('ActivityOnline');
            if ($method == 'add') {
                $activityOnlineModel->where(array('id' => $pid))->setInc('favors');
            } else {
                $activityOnlineModel->where(array('id' => $pid))->setDec('favors');
            }
            $count_info = $activityOnlineModel->where(array('id' => $pid))->field('favors')->select();
            return $count_info['favors'];
        } elseif ($type == 9) {
            $activityOnlineModel = D('ActivityOnline');
            if ($method == 'add') {
                $activityOnlineModel->where(array('id' => $pid))->setInc('favors');
            } else {
                $activityOnlineModel->where(array('id' => $pid))->setDec('favors');
            }
            $count_info = $activityOnlineModel->where(array('id' => $pid))->field('favors')->select();
            return $count_info['favors'];
        }
    }

    //查看点赞数是否存在
    public function checkfavorValid($pid, $type)
    {
        if ($type == 1) {
            $topicModel          = D('Topic');
            $condition['id']     = $pid;
            $condition['status'] = array('neq', 1);
            $bool                = $topicModel->where($condition)->find();
            if (empty($bool)) {
                $info    = null;
                $code    = 1;
                $message = '话题不存在';
                $return  = $this->buildReturn($info, $code, $message);
                $this->ajaxReturn($return);
            }

        } elseif ($type == 2) {
            $activityModel       = D('Activity');
            $condition['id']     = $pid;
            $condition['status'] = array('neq', 1);
            $condition['audit']  = 1;
            $bool                = $activityModel->where($condition)->find();
            if (empty($bool)) {
                $info    = null;
                $code    = 1;
                $message = '活动不存在';
                $return  = $this->buildReturn($info, $code, $message);
                $this->ajaxReturn($return);
            }
        } elseif ($type == 3) {
            $videoModel          = D('Video');
            $condition['id']     = $pid;
            $condition['status'] = array('neq', 1);
            $bool                = $videoModel->where($condition)->find();
            if (empty($bool)) {
                $info    = null;
                $code    = 1;
                $message = '视频不存在';
                $return  = $this->buildReturn($info, $code, $message);
                $this->ajaxReturn($return);
            }
        } elseif ($type == 4) {
            $forumStoryModel     = D('ForumStory');
            $condition['id']     = $pid;
            $condition['status'] = array('neq', 1);
            $bool                = $forumStoryModel->where($condition)->find();
            if (empty($bool)) {
                $info    = null;
                $code    = 1;
                $message = '故事不存在';
                $return  = $this->buildReturn($info, $code, $message);
                $this->ajaxReturn($return);
            }
        } elseif ($type == 5) {
            $forumPassageModel   = D('ForumPassage');
            $condition['id']     = $pid;
            $condition['status'] = array('neq', 1);
            $bool                = $forumPassageModel->where($condition)->find();
            if (empty($bool)) {
                $info    = null;
                $code    = 1;
                $message = '一段话不存在';
                $return  = $this->buildReturn($info, $code, $message);
                $this->ajaxReturn($return);
            }
        } elseif ($type == 6) {
            $responseModel       = D('Response');
            $condition['id']     = $pid;
            $condition['status'] = array('neq', 1);
            $bool                = $responseModel->where($condition)->find();
            if (empty($bool)) {
                $info    = null;
                $code    = 1;
                $message = '话题回复不存在';
                $return  = $this->buildReturn($info, $code, $message);
                $this->ajaxReturn($return);
            }
        } elseif ($type == 7) {
            $activityOnlineModel = D('ActivityOnline');
            $condition['id']     = $pid;
            $condition['status'] = array('neq', 1);
            $bool                = $activityOnlineModel->where($condition)->find();
            if (empty($bool)) {
                $info    = null;
                $code    = 1;
                $message = '征集不存在';
                $return  = $this->buildReturn($info, $code, $message);
                $this->ajaxReturn($return);
            }
        } elseif ($type == 9) {
            $activityOnlineModel = D('ActivityOnline');
            $condition['id']     = $pid;
            $condition['status'] = array('neq', 1);
            $bool                = $activityOnlineModel->where($condition)->find();
            if (empty($bool)) {
                $info    = null;
                $code    = 1;
                $message = '征集不存在';
                $return  = $this->buildReturn($info, $code, $message);
                $this->ajaxReturn($return);
            }
        }
    }

    //检查是否favor
    public function checkfavor()
    {
        $type = I('request.type', null, 'intval');
        $pid  = I('request.pid', null);
        $uid  = $this->getUid();

        if (empty($type) || empty($pid)) {
            $info    = null;
            $code    = -1;
            $message = C('parameter_invalid');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $favorModel          = D('Favor');
        $condition['pid']    = $pid;
        $condition['uid']    = $uid;
        $condition['type']   = $type;
        $condition['stauts'] = 0;
        $exist               = $favorModel->where($condition)->find();
        if ($exist) {
            $info    = true;
            $code    = 0;
            $message = '已经点过赞';
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $info    = false;
        $code    = 0;
        $message = '没有点过赞';
        $return  = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

}

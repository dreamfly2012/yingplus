<?php

namespace Home\Controller;

class CollectController extends CommonController
{

    /**
     * Collect function
     *
     * 收藏（TODO:验证请求pid不存在）
     *
     * @return void
     */
    public function collect()
    {

        $type  = I('request.type', 1, 'intval');
        $token = I('request.token', null, '');
        $pid   = I('request.pid', null, 'intval');
        $info = null;

        if (empty($token)) {
            $code    = -1;
            $message = '用户没有登录,请登录';
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $uid = $this->get_uid_by_token($token);

        //没有登录
        if (empty($uid)) {
            $info    = null;
            $code    = -2;
            $message = '没有登录,请登录';
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        //参数错误
        if (empty($pid) || empty($type)) {
            $info    = null;
            $code    = -3;
            $message = '参数错误';
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $type_arr = array(1, 2, 3);
        if (!in_array($type, $type_arr)) {
            $info    = null;
            $code    = -4;
            $message = '类型不对';
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
            $collectModel->where(array('type' => $type, 'pid' => $pid, 'uid' => $uid))->setField(array('status' => 1));
            $this->collectTotal($pid, $type, 'sub');
            $info    = $exist;
            $code    = 2;
            $message = '取消收藏成功';
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $meta['uid']    = $uid;
        $meta['pid']    = $pid;
        $meta['type']   = $type;
        $meta['status'] = 1;
        $bool           = $collectModel->where($meta)->find();

        if ($bool) {
            $this->collectTotal($pid, $type, 'add');
            $collectModel->where($meta)->setField(array('status' => 0));
            $info    = null;
            $code    = 1;
            $message = '收藏成功';
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $neta['uid']  = $uid;
        $neta['pid']  = $pid;
        $neta['type'] = $type;

        $bool = $collectModel->where($neta)->find();

        if (!$bool) {
            $this->collectTotal($pid, $type, 'add');
            $neta['addtime'] = time();
            $info            = $collectModel->add($neta);
            $code            = 1;
            $message         = '收藏成功';
            $return          = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
    }
    /**
     * 收藏列表
     * 类型 1话题;2活动;3视频
     * @return [type] [description]
     */
    public function collectlist()
    {
        $token         = I('request.token');
        $uid           = $this->get_uid_by_token($token);
        $collectModel  = D('Collect');
        $activityModel = D('Activity');
        $topicModel    = D('Topic');
        $videoModel    = D('Video');
        $list          = $collectModel->where(array('uid' => $uid, 'status' => 0))->select();
        foreach ($list as $k => $v) {
            $type = $v['type'];
            if ($type == 1) {
                $topic                 = $topicModel->where(array('id' => $v['pid']))->find();
                $topic['content_desc'] = mb_substr(strip_tags(htmlspecialchars_decode($topic['content'])), 0, 200, 'UTF-8');
                $list[$k]['info']      = $topic;
            } elseif ($type == 2) {
                $activity                 = $activityModel->where(array('id' => $v['pid']))->find();
                $activity['content_desc'] = mb_substr(strip_tags(htmlspecialchars_decode($activity['content'])), 0, 200, 'UTF-8');
                $list[$k]['info']         = $activity;
            } elseif ($type == 3) {
                $list[$k]['info'] = $videoModel->where(array('id' => $v['pid']))->find();
            }
        }
        $info    = $list;
        $code    = 0;
        $message = '收藏列表';
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
    public function checkcollectValid($pid, $type)
    {
        if ($type == 1) {
            $topicModel          = D('Topic');
            $condition['id']     = $pid;
            $condition['status'] = array('neq', 1);
            $bool                = $topicModel->where($condition)->find();
            if (empty($bool)) {
                $info   = null;
                $code   = 1;
                $message = '话题不存在';
                $return = $this->buildReturn($info, $code, $message);
                $this->ajaxReturn($return);
            }

        } elseif ($type == 2) {
            $activityModel       = D('Activity');
            $condition['id']     = $pid;
            $condition['status'] = array('neq', 1);
            $condition['audit']  = 1;
            $bool                = $activityModel->where($condition)->find();
            if (empty($bool)) {
                $info   = null;
                $code   = 1;
                $message = '活动不存在';
                $return = $this->buildReturn($info, $code, $message);
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
        }
    }
}

<?php

namespace Home\Controller;

use Think\Controller;

class CommentController extends CommonController
{

    public function __construct()
    {
        parent::__construct();
    }
    /**
     * 获取评论
     *
     * */
    public function getList()
    {
        $page          = I('request.p', 1, 'intval');
        $pid           = I('request.pid', 1, 'intval');
        $type          = I('request.type', 1, 'intval');
        $count         = I('request.count', 5, 'intval');
        $responseModel = D('Response');

        $total = $responseModel->where(array('type' => $type, 'pid' => $pid))->count();
        $Page  = new \Think\Page($total, $count);
        $list  = $responseModel->where(array('type' => $type, 'pid' => $pid))->order(array('addtime' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $k => $v) {
            $list[$k]['username']   = getUserNicknameById($v['uid']);
            $list[$k]['formattime'] = date('Y-m-d H:i', $v['addtime']);
            $userphoto              = getUserPhotoById($v['uid']);
            if (strpos($userphoto, 'http') === false) {
                $userphoto = 'http://' . $_SERVER['SERVER_NAME'] . '/' . $userphoto;
            }
            $list[$k]['userphoto'] = $userphoto;
        }

        $info    = $list;
        $code    = 0;
        $message = '回复列表';
        $return  = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    /**
     * 添加评论
     * fid 工作室id
     * pid 话题，活动，视屏等id
     * rid  回复id
     * type 类型 1话题 2活动 3视频 4工作室
     * */
    public function add()
    {
        $token = isset($_REQUEST['token']) ? $_REQUEST['token'] : null;

        $uid = $this->get_uid_by_token($token);

        if (empty($uid)) {
            $info    = 'not login';
            $code    = -1;
            $message = '没有登录';
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        } else {
            $data['fid']     = I('request.fid', 1, 'intval');
            $data['pid']     = I('request.pid', 1, 'intval');
            $data['type']    = I('request.type', 1, 'intval');
            $data['uid']     = $uid;
            $data['rid']     = I('request.rid', 0, 'intval');
            $data['content'] = I('request.content');
            $data['addtime'] = time();
            $responseModel   = D('Response');
            //dump($data);
            $result          = $responseModel->add($data);
            //dump($responseModel->getLastSql());

            $data['username']   = getUserNicknameById($uid);
            $data['formattime'] = date('Y-m-d H:i', time());
            $userphoto          = getUserPhotoById($uid);
            if (strpos($userphoto, 'http') === false) {
                $userphoto = 'http://' . $_SERVER['SERVER_NAME'] . '/' . $userphoto;
            }

            $response               = array();
            $response['username']   = $data['username'];
            $response['formattime'] = date('Y-m-d H:i', $data['addtime']);
            $response['userphoto'] = $userphoto;

            $info    = $response;
            $code    = 0;
            $message = '添加成功';
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
    }
}

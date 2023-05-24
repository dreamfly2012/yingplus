<?php

namespace Home\Controller;

//搜索接口
class SearchController extends CommonController
{

    public function index(){
        $info    = null;
        $code    = -1;
        $message = C('parameter_invalid');
        $return  = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    //用户搜索接口
    public function user()
    {
        $uid = I('request.uid', null, 'intval');
        $fid = I('request.fid', null, 'intval');
        $keyword = I('request.keyword',null);
        $p = I('request.p',1,'intval');
        $number = I('request.number',10,'intval');
        if($number>10){
            $number = 10;
        }

        if (empty($uid)&&empty($keyword)) {
            $info    = null;
            $code    = -1;
            $message = C('parameter_invalid');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        if(!empty($uid)){

            $userModel = D('User');
            $info      = $userModel->where(array('id'=>$uid))->find();
            if (!empty($fid)) {
                $forumUserModel     = D('ForumUser');
                $forum_info         = $forumUserModel->getAccessInfo($fid, $uid);
                $info['forum_info'] = $forum_info;
            }
            unset($info['password']); //移除用户密码字段
            $code    = 0;
            $message = C('success');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        if(!empty($keyword)){
            $userModel = D('User');
            $condition['nickname'] = array('like','%'.$keyword.'%');
            $info      = $userModel->where($condition)->order(array('id'=>'asc'))->limit(($p-1)*$number,$number)->select();
            unset($info['password']); //移除用户密码字段
            $code    = 0;
            $message = C('success');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        
	}

    //工作室搜索接口
    public function forum()
    {
        $fid     = I('request.fid', null, 'intval');
        $keyword = I('request.keyword', null, 'trim');

        if (empty($fid)) {
            $forumModel = D('Forum');
            if (empty($keyword)) {
                $info    = null;
                $code    = -1;
                $message = C('parameter_invalid');
                $return = $this->buildReturn($return);
            } 

            $forums = $forumModel->getInfoByKeyword($keyword);
            foreach ($forums as $key => $val) {
                $forums[$key]['href']  = U('Forum/getinfo', array('fid' => $val['id']));
                $forums[$key]['photo'] = getAttachmentUrlById($val['photo']);
            }
            if (empty($forums)) {
                $info    = null;
                $code    = 0;
                $message = '没有匹配明星';
            } else {
                $info = $forums;
                $code    = 0;
                $message = '匹配到的工作室';
            }

            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        } 

        $forumModel = D('Forum');
        $info       = $forumModel->getForumInfoById($fid);

        $info['topic_num']    = getTopicNum($fid);
        $info['activity_num'] = getTopicNum($fid);
        $info['fans_num']     = getFansNum($fid);
        $info['photo_url']    = buildImgUrl(getAttachmentUrlById($val['photo']));

        $code    = 0;
        $message = C('success');
        $return  = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
        
    }

    //话题搜索接口
    public function topic(){
    	$tid = I('request.tid', null, 'intval');
        $keyword = I('request.keyword', null, 'trim');
        $p = I('request.p',1,'intval');
        $number = I('request.number',10,'intval');
        if($number>10){
            $number = 10;
        }
        if (empty($tid)&&empty($keyword)) {
            $info    = null;
            $code    = -1;
            $message = C('parameter_invalid');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        if(!empty($tid)) {
            $topicModel = D('Topic');
            $condition['id'] = $tid;
            $condition['status'] = 0;
            $info       = $topicModel->where($condition)->find();
            $code       = 0;
            $message    = '话题详细信息';
            $return     = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        if(!empty($keyword)){
            $topicModel = D('Topic');
            $condition['subject'] = array('like','%'.$keyword.'%');
            $condition['status'] = 0;
            $topics      = $topicModel->where($condition)->order(array('id'=>'asc'))->limit(($p-1)*$number,$number)->select();
            $info = $topics;
            $code       = 0;
            $message    = '话题详细信息';
            $return     = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
    }

       
    //活动搜索接口
    public function activity(){
    	$aid = I('request.aid', null, 'intval');
        $keyword = I('request.keyword', null, 'trim');
        $p = I('request.p',1,'intval');
        $number = I('request.number',10,'intval');
        if($number>10){
            $number = 10;
        }
	    if (empty($aid)&&empty($keyword)) {
	        $info    = null;
	        $code    = -1;
	        $message = C('parameter_invalid');
	        $return  = $this->buildReturn($info, $code, $message);
	        $this->ajaxReturn($return);
	    } 

        if(!empty($aid)){
            $activityModel = D('Activity');
            $condition['id'] = $aid;
            $condition['status'] = array('neq',1);
            $info          = $activityModel->where($condition)->find();
            $code          = 0;
            $message       = C('success');
            $return        = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        if(!empty($keyword)){
            $activityModel = D('Activity');
            $condition['subject'] = array('like','%'.$keyword.'%');
            $condition['status'] = array('neq',1);
            $activities          = $activityModel->where($conditon)->order(array('id'=>'asc'))->limit(($p-1)*$number,$number)->select();
            $info = $activities;
            $code          = 0;
            $message       = C('success');
            $return        = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

       	
	}
}

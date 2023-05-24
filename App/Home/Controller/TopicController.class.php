<?php

namespace Home\Controller;

class TopicController extends CommonController
{

    public function index()
    {
        $info    = null;
        $code    = -1;
        $message = C('parameter_invalid');
        $return  = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    public function get()
    {
        $type = I('request.type', null);
        switch ($type) {
            case 'hot':
                $start      = I('request.p', 0, 'intval');
                $topicModel = D('Topic');
                $topics     = $topicModel->getHotPlatformTopicList($start);
                foreach ($topics as $key => $val) {
                    $content = htmlspecialchars_decode($val['content']);
                    preg_match_all("/<img([^>]*)>/", $content, $matches);
                    $imglist = "";

                    foreach ($matches[0] as $kkey => $vval) {
                        preg_match_all("#src=('|\")([^'\"]*)('|\")#", $vval, $matchinfo);
                        $imglist[$kkey]['src'] = $matchinfo[2][0];

                    }
                    $topics[$key]['image_list']  = $imglist;
                    $topics[$key]['url']         = U('Index/topic', array('id' => $val['id']));
                    $topics[$key]['content']     = htmlspecialchars_decode($val['content']);
                    $topics[$key]['contentText'] = htmlspecialchars_decode(strip_tags($val['content']));
                }
                $info    = $topics;
                $code    = 0;
                $message = '热门话题';
                $return  = $this->buildReturn($info, $code, $message);
                break;
            case 'detail':
                $id                = I('request.id', 1, 'intval');
                $topicModel        = D('Topic');
                $topic             = $topicModel->where(array('id' => $id))->find();
                $topic['username'] = getUserNicknameById($topic['uid']);
                $topic['content']  = htmlspecialchars_decode($topic['content']);
                $info              = $topic;
                $code              = 0;
                $message           = '话题详细信息';
                $return            = $this->buildReturn($info, $code, $message);
                $this->ajaxReturn($return);
                break;
            default:
                break;
        }
        $this->ajaxReturn($return);
    }

    public function listing($fid,$p,$number,$order='id',$sort='desc')
    {
        $topicModel = D('Topic');
        $condition['fid'] = $fid;
        $condition['p'] = $p;
        $condition['number'] = $number;
        $condition['status'] = 0;
        $topics     = $topicModel->where($condition)->order(array('isadmintop'=>'desc','admin_digest'=>'desc',$order => $sort))->limit(($p - 1) * $number, $number)->select();
        $count = $topicModel->where($condition)->count();
        $Page = new \Think\AjaxPage($count,$number,'topic_page_url');
        $show = $Page->show();
        $info['data'] = $topics;
        $info['page'] = $show;
        return $info;
    }

    public function getlisting()
    {
        $number                         = I('request.number', '10', 'intval');
        $fid                            = I('request.fid', null, 'intval');
        $p                              = I('request.p', '1', 'intval');
        $data['status']                 = array('neq', 1);

        if(empty($fid)){
            $info    = 'parameter_invalid';
            $code    = 0;
            $message = C('parameter_invalid');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
        $topics  = $this->listing($fid,$p,$number);
        $info    = $topics;
        $code    = 0;
        $message = '话题列表';
        $return  = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    //话题详情信息(工作室)
    public function detail()
    {
        //获取话题所属星吧信息
        $tid = I('request.tid', null, 'intval');
        if (empty($tid)) {
            $info    = null;
            $code    = -1;
            $message = C('parameter_invalid');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
        $topicModel = D('Topic');
        $condition['status'] = 0;
        $condition['id'] = $tid;
        $topic_info = $topicModel->where($condition)->find();

        if (empty($topic_info)) {
            $info    = null;
            $code    = -2;
            $message = '话题不存在';
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $data['topic'] = $topic_info;
        $fid           = $topic_info['fid'];
        $forumModel    = D('Forum');
        $forum_info    = $forumModel->getForumInfoById($fid);
        $data['forum'] = $forum_info;

        //获取热门活动和话题
        $hot_activities         = $this->getHotActivities($fid);
        $data['hot_activities'] = $hot_activities;
        $hot_topics             = $this->getHotTopics($fid);
        $data['hot_topics']     = $hot_topics;

        //分享链接
        $uid         = session('uid');
        $encode_uid  = base64_encode(base64_encode($uid));
        $url         = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "?share_id=" . $encode_uid;
        $data['url'] = $url;

        //其他热门话题
        $hot_length           = C('OTHER_HOT_TOPIC_SHOW');
        $other_topics         = $topicModel->getOtherHotTopic($id, $fid, $hot_length);
        $data['other_topics'] = $other_topics;

        $info = $data;
        $code = 0;
        $message = '话题详情';
        $return = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    //话题信息
    public function info(){
        //获取话题所属星吧信息
        $tid = I('request.tid', null, 'intval');
        if (empty($tid)) {
            $info    = null;
            $code    = -1;
            $message = C('parameter_invalid');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
        $topicModel = D('Topic');
        $condition['status'] = 0;
        $condition['id'] = $tid;
        $topic = $topicModel->where($condition)->find();

        if (empty($topic)) {
            $info    = null;
            $code    = -2;
            $message = '话题不存在';
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        return $topic;
    }

    //获取话题详情
    public function getinfo(){
        $info = $this->info();
        $code = 0;
        $message = '话题信息';
        $return = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    //更新话题
    public function update()
    {
        //更新话题单独更新
        $tid     = I('request.tid', null);
        $uid     = $this->getUid();
        $content = I('request.content', null);
        $subject = I('request.subject', null);
        //权限未登录
        if (empty($uid)) {
            $info    = null;
            $code    = 2;
            $message = C('no_login');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
        if (empty($tid)) {
            $info    = null;
            $code    = -1;
            $message = C('parameter_invalid');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        if (!empty($content) && strlen($content) < 10 * 3) {
            $info    = null;
            $code    = 1;
            $message = "内容长度不能小于10个中文字符或者30个英文字符";
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
        if (!empty($subject) && strlen($subject) < 10 * 3) {
            $info    = null;
            $code    = 1;
            $message = "话题标题内容不能少于10个中文字符或者30个英文字符";
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $topicModel     = D('Topic');
        $arr['uid']     = $uid;
        $arr['subject'] = $subject;
        $arr['content'] = $content;
        $result         = $topicModel->where(array('id' => $tid))->setField($arr);

        if ($result !== false) {
            $info    = $tid;
            $code    = 0;
            $message = '更新话题成功';
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        } else {
            $info    = null;
            $code    = 1;
            $message = '更新话题失败' . $topicModel->getError();
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

    }

    //热度算法
    public function updateTopicHot($tid, $method)
    {
        //hot_start hot
        $topicModel    = D('Topic');
        $now           = time();
        $old_hot       = $topicModel->getFieldById($tid, 'hot');
        $old_hot_start = $topicModel->getFieldById($tid, 'hot_start');
        if ($now - $old_hot_start > 60 * 60 * 24 * 7) {
            $hot_start = $now;
            $hot       = 1;
            $topicModel->where(array('id' => $tid))->save(array('hot' => $hot, 'hot_start' => $hot_start));
        } else {
            if ($method == 'add') {
                $hot = $old_hot + 1;
            } else {
                $hot = $old_hot - 1;
            }
            $hot = $hot < 0 ? 0 : $hot;

            $topicModel->where(array('id' => $tid))->save(array('hot' => $hot));
        }
    }

    public function formatResponseTime($time)
    {
        $now      = time();
        $interval = $now - $time;
        if ($interval < 60) {
            return $interval . '秒之前';
        } elseif ($interval < 60 * 60) {
            return ($interval / 60) . '分钟之前';
        } elseif ($interval < 60 * 60 * 24) {
            return ($interval / (60 * 60)) . '小时之前';
        } else {
            return date('Y-m-d H:i:s', $time);
        }
    }

    //对间隔时间过长的信息展示发送时间
    public function showDate($basetime, $time)
    {
        $return_time = null;
        $lingTime    = strtotime("today");
        if ($basetime - $time > C('TOPIC_INTERVAL_TIME_SHOW')) {
            $basetime = $time;
            if ($time < $lingTime) {
                $return_time = date('m-d H:i:s', $time);
            } else {
                $return_time = date('H:i:s', $time);
            }
        }
        return array('basetime' => $basetime, 'returntime' => $return_time);
    }

    //添加话题
    public function add()
    {
        $uid     = $this->getUid();
        $fid     = I('request.fid', 0, 'intval');
        $subject = I('request.subject', null);
        $content = I('request.content', null);
        if (empty($uid)) {
            $info    = null;
            $code    = 2;
            $message = C('no_login');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        //参数错误
        if (empty($subject) || empty($content) || empty($fid)) {
            $info    = null;
            $code    = -1;
            $message = C('parameter_invalid');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $privilege = checkIsInForum($uid, $fid);

        //权限不够
        if (empty($privilege)) {
            $info    = array('result' => C('FORUM_FOLLOW_FIRST'));
            $code    = 3;
            $message = C('no_auth');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        //状态受限
        // if (checkUserBanCreate($uid, $fid)) {
        //     $forumBanUserModel = D('ForumBanUser');
        //     $ban_info = $forumBanUserModel->where(array('fid' => $fid, 'uid' => $uid))->find();
        //     $info = $ban_info;
        //     $code = 3;
        //     $message = C('auth_limit');
        //     $return = $this->buildReturn($info, $code, $message);
        //     $this->ajaxReturn($return);
        // }

        $topicModel        = D('Topic');
        $data['subject']   = $subject;
        $data['content']   = $content;
        $data['fid']       = $fid;
        $data['ip']        = get_client_ip();
        $data['uid']       = $uid;
        $data['addtime']   = time();
        $data['hot_start'] = time();
        $id                = $topicModel->add($data);

        $forumModel     = D('Forum');
        $forumUserModel = D('ForumUser');
        $forumModel->where(array('id' => $data['fid']))->setInc('topics');
        $forumUserModel->where(array('fid' => $data['fid'], 'uid' => $data['uid']))->setInc('createtopicnum');

        $info    = array('tid' => $id, 'fid' => $fid,'url'=>U('Pc/topic',array('tid'=>$id)));
        $code    = 0;
        $message = C('CREATE_TOPIC_SUCCESS');
        $return  = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    //删除话题
    public function delete()
    {
        $uid        = $this->getUid();
        $tid        = I('request.tid', null, 'intval');

        if(empty($uid)){
        	$info    = null;
            $code    = 2;
            $message = C('no_login');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
       

        //参数错误
        if (empty($tid)) {
            $info    = null;
            $code    = -1;
            $message = C('parameter_invalid');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

       
        $topicModel = D('Topic');
        $topic_uid = $topicModel->getFieldById($tid, 'uid');
        $fid       = $topicModel->getFieldById($tid, 'fid');
        $isadmin = $this->checkPrivilege('isadmin');
        
        
        //没有权限
        if ((!$isadmin) && ($topic_uid != $uid)) {
            $info    = null;
            $code    = -1;
            $message = C('no_auth');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $bool = $topicModel->where(array('id'=>$tid,'status'=>1))->find();

        //话题被删除了
        if($bool){
        	$info = null;
        	$code = 1;
        	$message = '话题处于删除状态';
        	$return = $this->buildReturn($info, $code, $message);
        	$this->ajaxReturn($return);
        }
        
        //管理员删除
        if ($isadmin) {
        	$forumModel = D('Forum');
        	$forumUserModel = D('ForumUser');
            
            $topicModel->where(array('id' => $tid))->setField(array('status' => 1, 'admin_id' => $uid));
            $forumUserModel->where(array('fid' => $fid, 'uid' => $uid))->setDec('createtopicnum');
            $forumUserModel->where(array('fid' => $fid, 'uid' => $uid))->setInc('deletetopicnum');
            
    		$messageModel        = D("Message");
            $meta['fid']         = $fid;
            $meta['uid']         = $uid;
            $meta['touid']       = $topic_uid;
            $meta['subject']     = "您的话题被管理员删除了";
            $meta['content']     = '您的话题被删除';
            $meta['involiveid']  = $tid;
            $meta['involvetype'] = 1;
            $meta['isinvolve']   = 0;
            $meta['iscomplaint'] = 0;
            $meta['addtime']     = time();
            $messageModel->add($meta);
            $info = null;
            $code = 0;
            $message = '删除话题成功';
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
        

        if ($topic_uid == $uid) {
            $forumModel = D('Forum');
            $forumUserModel = D('ForumUser');

            $topicModel->where(array('id' => $tid, 'uid' => $uid))->setField(array('status' => 1, 'admin_id' => $uid));
			$forumUserModel->where(array('fid' => $fid, 'uid' => $uid))->setDec('createtopicnum');
            $forumUserModel->where(array('fid' => $fid, 'uid' => $uid))->setInc('deletetopicnum');
           	
			$info    = null;
            $code    = 0;
            $message = '删除话题成功';
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);

        }
	}


    /**
     * 话题举报
     * @param tid 话题id
     * @param reason 举报理由
     * @param content 举报内容
     * return
     **/
    public function report()
    {
        $uid     = $this->getUid();
        $tid     = I('request.tid', null, 'intval');
        $reason  = I('request.reason', null);
        $content = I('request.content', null);

        //参数错误
        if (empty($tid) || empty($reason) || empty($content)) {
            $info    = null;
            $code    = -1;
            $message = C('parameter_invalid');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $topicModel        = D('Topic');
        $data['uid']       = $uid;
        $data['fid']       = $topicModel->getFieldById($tid, 'fid');
        $data['touid']     = $topicModel->getFieldById($tid, 'uid');
        $data['involveid'] = $tid;
        $data['reason']    = $reason;
        $data['content']   = $content;
        $data['type']      = 2;
        $data['addtime']   = time();

        $forumReportModel = D('ForumReport');
        $forumReportModel->add($data);

        $info    = null;
        $code    = 0;
        $message = C('TOPIC_REPORT_SUCCESS');
        $return  = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);

    }

    //话题加精
    
    public function digest()
    {
        $tid  = I('request.tid', null, 'intval');
        $uid = $this->getUid();
        if(empty($uid)){
            $info = null;
            $code = 2;
            $meesage = C('no_login');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
        
        //参数错误
        if (empty($tid)) {
            $info    = null;
            $code    = -1;
            $message = C('parameter_invalid');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        //权限不够
        if (!$this->checkPrivilege('isadmin')) {
            $info    = null;
            $code    = -1;
            $message = C('no_auth');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $topicModel = D('Topic');
        $condition['id'] = $tid;
        $condition['status'] = 0;
        $exist = $topicModel->where($condition)->find();
        if(!$exist){
            $info = null;
            $code = -2;
            $meesage = '话题不存在';
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $condition['isdigest'] = 1;
        $bool = $topicModel->where($condition)->find();

        if($bool){
            $info = null;
            $code = 1;
            $message = '话题已经处于加精状态';
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        unset($condition['isdigest']);
        $topicModel->where($condition)->setField(array('isdigest' => 1));
        
        $forumUserModel = D('ForumUser');
        $fid = $topicModel->getFieldById($tid,'fid');
        $uid = $topicModel->getFieldById($tid,'uid');
        $meta['uid'] = $uid;
        $meta['fid'] = $fid;
        $forumUserModel->where($meta)->setInc('digesttopicnum');
       
        $info    = null;
        $code    = 0;
        $message = '话题加精成功';
        $return  = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    //取消加精
    public function canceldigest(){

    }

    /**
     *
     * 话题置顶
     * @param tid int 话题id
     * @param type int 操作类型 1置顶,2取消置顶
     **/
    public function settop()
    {
        $uid  = $this->getUid();
        $tid  = I('request.tid', null, 'intval');
        $fid  = $this->getFidByTid($tid);
        $type = I('request.type', null, 'intval');

        //参数错误
        if (empty($tid) || empty($type)) {
            $info    = null;
            $code    = -1;
            $message = C('parameter_invalid');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        //权限检测
        if (!$this->checkPrivilege('isadmin')) {
            $info    = null;
            $code    = -1;
            $message = C('no_auth');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $topicModel = D('Topic');
        $topic_uid  = $topicModel->getFieldById($tid, 'uid');
        $count      = $topicModel->where(array('status' => 0, 'fid' => $fid, 'istop' => 1))->count();
        if ($count >= C('SET_TOPIC_TOP_NUM') && $type == 1) {
            $info    = null;
            $code    = 1;
            $message = C('SET_TOPIC_TOP_OVERFLOW');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
        $istop = $type == 1 ? 1 : 0;
        $topicModel->where(array('id' => $tid))->setField(array('istop' => $istop));
        if ($type == 1) {
            //统计信息
            $forumAdminStatisticsModel = D('ForumAdminStatistics');
            $messageModel              = D('Message');
            $data['fid']               = $fid;
            $data['uid']               = $uid;
            $data['type']              = 0;
            $data['addtime']           = time();
            $data['operate']           = '话题置顶';
            $forumAdminStatisticsModel->add($data);

            $meta['fid']         = $fid;
            $meta['uid']         = $uid;
            $meta['touid']       = $topic_uid;
            $meta['subject']     = C('COMMON_MESSAGE_SET_TOPIC_TOP');
            $meta['content']     = '您的话题被置顶';
            $meta['involiveid']  = $tid;
            $meta['involvetype'] = 1;
            $meta['isinvolve']   = 1;
            $meta['iscomplaint'] = 0;
            $meta['addtime']     = time();
            $messageModel->add($meta);

            $info    = null;
            $code    = 0;
            $message = C('TOPIC_TOP_SUCCESS');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        } else {
            $info    = null;
            $code    = 0;
            $message = C('TOPIC_UNTOP_SUCCESS');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
    }

    /**
     * 话题点赞
     * @param tid int 话题id
     */
    public function favor()
    {
        $tid             = I('request.tid', null, 'intval');
        $fid             = $this->getFidByTid($tid);
        $uid             = $this->getUid();
        $topicModel      = D('Topic');
        $forumUserModel  = D('ForumUser');
        $topicFavorModel = D('TopicFavor');

        //参数错误
        if (empty($tid)) {
            $info    = null;
            $code    = -1;
            $message = C('parameter_invalid');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $bool = $topicFavorModel->checkHasFavor($tid, $uid);
        if ($bool) {
            $result = $topicFavorModel->checkIsFavor($tid, $uid);
            if ($result) {
                //取消点赞
                $topicFavorModel->where(array('tid' => $tid, 'uid' => $uid))->setField(array('status' => 0));
                $topicModel->where(array('id' => $tid))->setDec('favors', 1);
                $forumUserModel->where(array('fid' => $fid, 'uid' => $uid))->setDec('favors', 1);
                $this->updateTopicHot($tid, 'delete');
                $info    = null;
                $code    = 0;
                $message = C('TOPIC_CANCEL_FAVOR_SUCCESS');
                $return  = $this->buildReturn($info, $code, $message);
                $this->ajaxReturn($return);
            } else {
                //点赞
                $topicFavorModel->where(array('tid' => $tid, 'uid' => $uid))->setField(array('status' => 1));
                $topicModel->where(array('id' => $tid))->setInc('favors', 1);
                $forumUserModel->where(array('fid' => $fid, 'uid' => $uid))->setInc('favors', 1);

                $pointController = new PointController();
                $point           = $pointController->topicFavorAddPoint($tid);
                $this->updateTopicHot($tid, 'add');

                $info    = null;
                $code    = 0;
                $message = C('TOPIC_FAVOR_SUCCESS');
                $return  = $this->buildReturn($info, $code, $message);
                $this->ajaxReturn($return);
            }

        } else {
            $topicFavorModel->add(array('tid' => $tid, 'uid' => $uid));

            //加积分处理
            $pointController = new PointController();
            $point           = $pointController->topicFavorAddPoint($tid);
            $this->updateTopicHot($tid, 'add');
            $topicModel->where(array('id' => $tid))->setInc(array('favors' => 1));
            $forumUserModel->where(array('fid' => $fid, 'uid' => $uid))->setInc('favors', 1);

            $info    = null;
            $code    = 0;
            $message = C('TOPIC_FAVOR_SUCCESS');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
    }

}

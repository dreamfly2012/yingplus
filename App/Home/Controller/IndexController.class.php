<?php

namespace Home\Controller;

class IndexController extends CommonController
{
    public function __construct()
    {
        parent::__construct();
    }

    //首页
    public function index()
    {
        if (isMobile()) {
            //$this->redirect('Pc/index');
            R('Mobile/index');
        } else {
            R('Pc/index');
        }
    }

    //登陆页面
    public function login()
    {
        $this->display('login');
    }

    //退出页面
    public function logout()
    {
        session_destroy();
        session('uId', null);
        session('uid', null);
        cookie('uid', null);
        cookie('uid_info', null);
        header('location:'.getenv('HTTP_REFERER'));
    }

    //发起包场页面
    public function launch()
    {
        $fid = I('request.fid', null, 'intval');
        $mid = I('request.mid', null, 'intval');
        $forumMovieModel = D('ForumMovie');
        $releasetime = $forumMovieModel->where(array('mid' => $mid, 'status' => 1))->getField('releasetime');
        $now = time();
        $limit_start = $now + 10 * 60 * 60 * 24;
        $limit_start = ($limit_start < $releasetime) ? $releasetime : $limit_start;
        $limit_end = $releasetime + 30 * 60 * 60 * 24;
        $this->assign('limit_start', $limit_start);
        $this->assign('limit_end', $limit_end);

        //省份城市赋值
        $districtModel = D('District');
        $provinces = $districtModel->where(array('level' => 1))->select();
        $this->assign('provinces', $provinces);
        $cities = $districtModel->where(array('level' => 2))->select();
        $this->assign('cities', $cities);

        $this->display('launch');
    }

    //注册页面
    public function register()
    {
        $this->display('register');
    }

    //忘记密码
    public function forgetpassword()
    {
        $this->display('forgetpassword');
    }

    //活动页面
    public function activity()
    {
        $this->display('activity');
    }

    //话题页面
    public function topic()
    {
        $this->display('topic');
    }

    //工作室页面
    public function forum()
    {
        $this->display('forum');
    }

    //我的个人中心
    public function personcenter()
    {
        $this->display('personcenter');
    }

    //搜索页面
    public function search()
    {
        $this->display('search');
    }

    //包场专题页
    public function movie()
    {
        $mid = I('request.mid', '1', 'intval');
        $this->assign('mid', $mid);
        $this->display('movie_index');
    }

    //包场详情页
    public function moviedetail()
    {
        $aid = I('request.aid', null, 'intval');
        $fid = $this->getFidByAid($aid);
        $activityModel = D('Activity');
        $holdstart = $activityModel->getFieldById($aid, 'holdstart');
        $now = time();
        $this->assign('fid', $fid);
        if ($now < $holdstart) {
            $this->display('movie_detail');
        } else {
            $this->display('movie_detail_1');
        }
    }

    //包场反馈页面
    public function moviefeedback()
    {
        $id = I('request.aid', null, 'intval');
        $activityModel = D('Activity');
        $holdstart = $activityModel->getFieldById($id, 'holdstart');
        $now = time();
        if ($now > $holdstart) {
            $this->display('movie_feedback');
        }
    }

    //包场列表页
    public function movielist()
    {
        $this->display('movie_list');
    }

    //支付页面
    public function pay()
    {
        $this->display('pay');
    }

    //微信扫码支付页面
    public function payweixin()
    {
        $aid = I('request.aid', null);
        $trade_no = I('request.trade_no', null);
        $pay_url = I('request.pay_url');
        $pay_url = urldecode($pay_url);
        $this->assign('aid', $aid);
        $this->assign('trade_no', $trade_no);
        $this->assign('pay_url', $pay_url);
        $this->display('pay_weixin_order');
    }

    //支付成功页面
    public function paysuccess()
    {
        $aid = I('request.aid', null);
        $code = I('request.code', null);
        $url = 'http://yingplus.80shihua.com/'.U('Index/scansuccess', array('aid' => $aid, 'code' => $code));

        $this->assign('url', urlencode($url));
        $this->display('pc/buy_success');
    }

    //扫码页面
    public function scan()
    {
        $this->display('scan');
    }

    //扫码成功页面(TODO:手机端调取这个函数)
    public function scansuccess()
    {
        $this->display('scan_success');
    }

    public function index2()
    {
        $data = array();
        //获取平台推荐活动
        $indexrecommendActivityModel = D('IndexRecommendActivity');
        $activityModel = D('Activity');
        $recommend_activities = $indexrecommendActivityModel->getActivity();

        foreach ($recommend_activities as $key => $val) {
            $activity = $activityModel->where(array('id' => $val['aid']))->find();
            $recommend_activities[$key]['detail'] = $activity;
        }

        $this->assign('recommend_activities', $recommend_activities);

        //获取4个关注星吧=》推荐星吧=》随机星吧
        $forumModel = D('Forum');
        $forumUserModel = D('ForumUser');
        if ($this->login) {
            $forums_1_info = $forumUserModel->getFavourForumByUid($this->uid);
            $forums_1 = array();
            foreach ($forums_1_info as $key => $val) {
                $fid = $val['fid'];
                $info = $forumModel->getForumInfoById($fid);
                $forums_1[$key] = $info;
            }

            $forums_2 = $forumModel->getAdminPromoteForum();

            $forums = array_merge($forums_1, $forums_2);

            $temp = array();
            $temp_forums = array();

            foreach ($forums as $key => $val) {
                if (!in_array($val['id'], $temp)) {
                    $temp_forums[] = $val;
                    array_push($temp, $val['id']);
                }
            }

            $fourms = array();
            $forums = $temp_forums;
        } else {
            $forums_2 = $forumModel->getAdminPromoteForum();
            //$forums_3 = $forumModel->getRandomForum();

            $forums = $forums_2;
        }

        $data['recommend_forums'] = $forums;

        //$this->assign('recommend_forums',$forums);

        //获取平台热门活动
        $activityModel = D('Activity');
        $hot_activities = $activityModel->getHotPlatformActivity();
        $data['hot_activities'] = $hot_activities;
        //$this->assign('hot_activities',$hot_activities);

        //获取平台推荐话题
        $IndexRecommendTopicModel = D('IndexRecommendTopic');
        $recommend_topics = $IndexRecommendTopicModel->getTopic();
        $data['recommend_topics'] = $recommend_topics;
        //$this->assign('recommend_topics',$recommend_topics);

        //获取热门话题
        $topicModel = D('Topic');
        $topicResponseModel = D('TopicResponse');
        $topicresponseUserModel = D('TopicResponseUser');
        $topics = $topicModel->getHotPlatformTopic();
        foreach ($topics as $key => $val) {
            $tid = $val['id'];
            $response = $topicResponseModel->getInfoByTid($tid);
            $topics[$key]['response'] = $response;

            $content = htmlspecialchars_decode($val['content']);

            preg_match_all('/<img([^>]*)>/', $content, $matches);

            $imglist = '';

            foreach ($matches[0] as $kkey => $vval) {
                preg_match_all("#src=('|\")([^'\"]*)('|\")#", $vval, $matchinfo);
                $imglist[$kkey]['src'] = $matchinfo[2][0];
            }

            $topics[$key]['image_list'] = $imglist;

            //获取未读回复信息
            /**$unreadmessage = $topicresponseUserModel->getUnreadMessageByUidTid($this->getUid(), $tid);
        $unreadmessage_count = count($unreadmessage);
        $lastunreadmessage = $unreadmessage[0];

        $topics[$key]['unreadmessage_count'] = $unreadmessage_count;
         */
        }
        $data['topics'] = $topics;
        $this->ajaxReturn(array('success' => 1, 'info' => $data));
        //$this->assign('topics',$topics);
        //$this->display('index');
    }

    //ajax获取分页热门话题
    public function HotTopicList()
    {
        $start = I('request.p');
        $topicModel = D('Topic');
        $topicResponseModel = D('TopicResponse');
        $topicresponseUserModel = D('TopicResponseUser');
        $topics = $topicModel->getHotPlatformTopicList($start);
        foreach ($topics as $key => $val) {
            $tid = $val['id'];
            $response = $topicResponseModel->getInfoByTid($tid);
            $topics[$key]['response'] = $response;

            $content = htmlspecialchars_decode($val['content']);

            preg_match_all('/<img([^>]*)>/', $content, $matches);

            $imglist = '';

            foreach ($matches[0] as $kkey => $vval) {
                preg_match_all("#src=('|\")([^'\"]*)('|\")#", $vval, $matchinfo);
                $imglist[$kkey]['src'] = $matchinfo[2][0];
            }

            $topics[$key]['image_list'] = $imglist;

            //获取未读回复信息
            /**$unreadmessage = $topicresponseUserModel->getUnreadMessageByUidTid($this->getUid(), $tid);
        $unreadmessage_count = count($unreadmessage);
        $lastunreadmessage = $unreadmessage[0];

        $topics[$key]['unreadmessage_count'] = $unreadmessage_count;
         */
        }

        $data['topics'] = $topics;
        $this->assign('topics', $topics);
        $content = $this->fetch('Index/topic_list');

        if (empty($topics)) {
            $this->ajaxReturn(array('status' => 0, 'content' => '亲，没有更多话题了！'));
        } else {
            $this->ajaxReturn(array('status' => 1, 'content' => $data));
        }
    }

    public function getTopicJishi()
    {
        $tid = I('request.t');
        $topicModel = D('Topic');
        $topicResponseModel = D('TopicResponse');
        $topicresponseUserModel = D('TopicResponseUser');
        $topic = $topicModel->getTopicInfoById($tid);
        $response = array_reverse($topicResponseModel->getInfoByTid($tid));
        $topic['response'] = $response;

        //获取未读回复信息
        $unreadmessage = $topicresponseUserModel->getUnreadMessageByUidTid($this->getUid(), $tid);
        $unreadmessage_count = count($unreadmessage);
        $lastunreadmessage = $unreadmessage[0];

        $topic['unreadmessage_count'] = $unreadmessage_count;
        $topic['lastunreadmessage'] = $lastunreadmessage;

        $this->assign('topic', $topic);
        $content = $this->fetch('Index/main_jishi');
        $this->ajaxReturn(array('status' => 1, 'content' => $content));
    }

    public function getTopicJishiMore()
    {
        $tid = I('request.tid');
        $firstid = I('request.firstid');
        //$topicModel = D('Topic');
        $topicResponseModel = D('TopicResponse');
        //$topicresponseUserModel = D('TopicResponseUser');
        //$topic = $topicModel->getTopicInfoById($tid);
        $response = array_reverse($topicResponseModel->getMoreInfoByTid($firstid, $tid));
        $topic['response'] = $response;

        //获取未读回复信息
        // $unreadmessage = $topicresponseUserModel->getUnreadMessageByUidTid($this->getUid(), $tid);
        // $unreadmessage_count = count($unreadmessage);
        // $lastunreadmessage = $unreadmessage[0];

        // $topic['unreadmessage_count'] = $unreadmessage_count;
        // $topic['lastunreadmessage'] = $lastunreadmessage;

        $this->assign('topic', $topic);
        $content = $this->fetch('Index/more_topic');
        $this->ajaxReturn(array('status' => 1, 'content' => $content));
    }
}

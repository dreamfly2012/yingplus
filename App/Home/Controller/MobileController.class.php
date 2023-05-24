<?php

namespace Home\Controller;

class MobileController extends CommonController
{
    public function __construct()
    {
        parent::__construct();

        $fid = I('request.fid', null, 'intval');
        $tid = I('request.tid', null, 'intval');
        $aid = I('request.aid', null, 'intval');

        if (is_null($fid)) {
            if (!is_null($tid)) {
                $fid = $this->getFidByTid($tid);
            } elseif (!is_null($aid)) {
                $fid = $this->getFidByAid($aid);
            }
        }

        if (empty($fid)) {
            $fid = 16;
        }
        $this->setFid($fid);

        $this->common_info($fid);
    }

    //页面公有元素
    public function common_info($fid)
    {
        $welfareEventModel = D('WelfareEvent');
        $promote_events = $welfareEventModel->where(array('status' => 0, 'promote' => 1))->select();
        foreach ($promote_events as $key => $val) {
            $img_list = array();
            $attachment_arr = explode(',', $val['attachment']);
            foreach ($attachment_arr as $kkey => $vval) {
                if ($vval) {
                    $img_list[] = getAttachmentUrlById($vval);
                }
            }

            $promote_events[$key]['img_url'] = $img_list[1];
        }
        $this->assign('promote_events', $promote_events);

        //电影
        $seminar_url = '';
        if ($fid == 16) {
            $seminar_url = 'http://xiayouqiaomu.80shihua.com/';
        } elseif ($fid == 19) {
            $seminar_url = 'http://yekongque.yin80shihuagplus.com/';
        } elseif ($fid == 2) {
            $seminar_url = 'http://threeontheroad.80shihua.com/';
        }

        $this->assign('seminar_url', $seminar_url);
        $movie = R('MovieActivity/movieInfoByFid', array($fid));
        $this->assign('movie', $movie);
        $releasetime = $movie['releasetime'];
        $daojishi = $releasetime - time();
        $this->assign('daojishi', $daojishi);

        //活动
        //dump($movie);
        $condition['movie'] = $movie['id'];
        $condition['audit'] = 1;
        $condition['status'] = array('neq', 1);
        $activityModel = D('Activity');
        $activities = $activityModel->where($condition)->select();
        $this->assign('activities', $activities);
        $activity_count = count($activities);
        $this->assign('activity_count', $activity_count);

        $this->assign('fid', $fid);
    }

    //首页
    public function index()
    {
        $sso = I('request.sso');
        $this->assign('title', '可能是最好的明星粉丝社区');
        $this->display('mobile/index');

        // $fid = 16;
        // $sso = I('request.sso');
        // $this->redirect('Mobile/forum',array('fid'=>$fid,'sso'=>$sso));
    }

    //登陆页面
    public function login()
    {
        $this->display('mobile/login');
    }

    //退出页面
    public function logout()
    {
        $old_url = getenv('HTTP_REFERER');
        $index = stripos($old_url, '?');
        if ($index) {
            $old_url = substr($old_url, 0, $index);
        }

        session_destroy();
        session('uid', null);
        cookie('uid', null);
        cookie('uid_info', null);
        if ($old_url) {
            header('location:'.$old_url.'?sso=logout');
        } else {
            $this->redirect('Mobile/index', array('sso' => 'logout'));
        }
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
        $this->display('mobile/register');
    }

    //忘记密码
    public function forgetpassword()
    {
        $this->display('mobile/forgetpassword');
    }

    //上传视频
    public function uploadfeedbackvideo()
    {
        $fid = I('request.fid', 16, 'intval');
        $aid = I('request.aid', null);
        $data['aid'] = $aid;
        $data['fid'] = $fid;
        R('VideoUpload/moviefeedbackindex', array($data));
    }

    //上传视频
    public function uploadvideo()
    {
        $fid = I('request.fid', 16, 'intval');
        $data['fid'] = $fid;
        R('VideoUpload/movieindex', array($data));
    }

    //获取创建包场活动
    public function getCreateMovieActivityUrl()
    {
        $fid = I('request.fid', null, 'intval');
        $uid = $this->getUid();
        if (empty($uid)) {
            $info = 'no_login';
            $code = 2;
            $message = C('no_login');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        if (empty($fid)) {
            $info = 'parameter_invalid';
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
        $forums = $this->getUserForum($uid);
        $bool = $this->checkInForum($fid, $forums);

        if (!$bool) {
            $info = 'not_in_forum';
            $code = 1;
            $message = '请先加入'.getFansgroupById($fid).'+';
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $url = U('Mobile/createMovieActivity');
        $info = $url;
        $code = 0;
        $message = '创建包场活动表单';
        $return = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    //创建包场活动
    public function createMovieActivity()
    {
        $fid = I('request.fid', 16, 'intval');
        $this->assign('fid', $fid);
        //省份，城市赋值
        $districtModel = D('District');
        $provinces = $districtModel->getAllProvince(0);
        $this->assign('provinces', $provinces);
        $cities = $districtModel->getCityByProvince(1);
        $this->assign('cities', $cities);

        //电影信息
        $forumMovieModel = D('ForumMovie');
        //获取电影id
        $now = time();

        $condition['status'] = array('eq', 1);
        $condition['releasetime'] = array('gt', $now);
        $condition['_string'] = 'FIND_IN_SET('.$fid.', fid)';
        $movie = $forumMovieModel->where($condition)->find();
        $releasetime = $movie['releasetime'];

        $limit_start = $now + 10 * 60 * 60 * 24;
        $limit_start = ($limit_start < $releasetime) ? $releasetime : $limit_start;
        $limit_end = $releasetime + 30 * 60 * 60 * 24;
        $this->assign('mid', $movie['id']);
        $this->assign('limit_start', $limit_start);
        $this->assign('limit_end', $limit_end);

        $this->display('mobile/create_movie_activity');
    }

    //创建公益页面
    public function createWelfareEvent()
    {
        $this->display('mobile/create_welfare_event');
    }

    //留下手机号
    public function leftMovieActivityTelephone()
    {
        $backurl = I('request.backurl');
        $this->assign('backurl', $backurl);
        $this->display('mobile/leave_movie_activity_telephone');
    }

    public function parameter()
    {
        return '?'.$_SERVER['QUERY_STRING'];
    }

    public function httphost()
    {
        return 'http://yingplus.80shihua.com';
    }

    //活动页面
    public function activity()
    {
        $aid = I('request.aid', null, 'intval');

        if (!isMobile()) {
            header('Location:'.$this->httphost().U('Pc/activity', array('aid' => $aid)));
            exit;
        }

        if (empty($aid)) {
            $info = null;
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
        $this->assign('response_type', 2);
        $this->assign('pid', $aid);

        $activityModel = D('Activity');
        $activity = $activityModel->where(array('id' => $aid, 'status' => array('neq', 1)))->find();
        $category = $activity['category'];
        $type = $activity['type'];
        $question_img = $activityModel->where(array('id' => $aid))->getField('question_img');
        $this->assign('activity', $activity);

        if (is_null($category)) {
            die('404');
        }

        if ($category == 0) {
            $this->display('mobile/line_activity');
            die;
        }

        if ($category == 1) {
            //包场活动聊天信息
            $responses = R('Response/listing', array(1, 30, $activity['id'], 2));
            $activity_responses = $responses['data'];
            $activity_responses = array_reverse($activity_responses);
            $this->assign('activity_responses', $activity_responses);

            //报名信息
            $activityEnrollModel = D('ActivityEnroll');
            $enrollInfo = $activityEnrollModel->where(array('aid' => $aid, 'status' => 0))->select();
            $this->assign('enrollInfo', $enrollInfo);

            $this->display('mobile/movie_activity');
            die;
        }

        if ($category == 2) {
            if ($type == 8) {
                $wrap_questions = R('Activity/onlineWrapQuestion', array($aid));
                $wrap_results = R('Activity/onlineWrapResults', array($aid));
                $this->assign('wrap_questions', $wrap_questions);
                $this->assign('wrap_results', $wrap_results);

                if (R('Activity/checkIsPostQuestionImg', array($question_img))) {
                    $this->display('mobile/online_activity_vote_image');
                    die;
                } else {
                    $question = $activityModel->where(array('id' => $aid))->getField('question');
                    $questions = json_decode($question);
                    //dump($questions);
                    $this->assign('questions', $questions);
                    $this->display('mobile/online_activity_vote_text');
                    die;
                }
                die;
            }

            if ($type == 9) {
                $activityOnlineModel = D('ActivityOnline');
                $p = I('request.p', 1, 'intval');
                $number = 100;
                $count = $activityOnlineModel->where(array('status' => 0, 'aid' => $aid))->count();
                $posters = $activityOnlineModel->where(array('status' => 0, 'aid' => $aid))->order(array('id' => 'desc'))->limit(($p - 1) * $number, $number)->select();
                $Page = new \Think\Page($count, $number);
                $show = $Page->show();
                $this->assign('poster_page', $show);
                $this->assign('poster_count', $count);
                $this->assign('posters', $posters);
                $this->display('mobile/online_activity_collection');
                die;
            }

            $this->display('mobile/online_activity_collection');
            die;
        }
    }

    //活动列表
    public function activitylist()
    {
        $tab = I('request.tab', 1, 'intval'); //1包场电影,2线上活动,3公益活动
        $fid = I('request.fid', 16, 'intval');
        if (!isMobile()) {
            $this->redirect('Pc/activitylist', array('tab' => $tab, 'fid' => $fid));
        }

        $this->assign('fid', $fid);
        if ($tab == 1) {
            $activityModel = D('Activity');
            $activities = $activityModel->where(array('status' => array('neq', 1), 'fid' => $fid, 'category' => 1, 'audit' => 1))->order(array('id' => 'desc'))->select();
            $this->assign('activities', $activities);
            //dump($activities);
            $this->display('mobile/movie_activity_list');
            die;
        } elseif ($tab == 2) {
            $activityModel = D('Activity');
            $activities = $activityModel->where(array('status' => array('neq', 1), 'fid' => $fid, 'category' => 2, 'audit' => 1))->order(array('id' => 'desc'))->select();
            $this->assign('activities', $activities);
            $this->display('mobile/online_activity_list');
            die;
        } elseif ($tab == 3) {
            //公益应援
            $events = R('WelfareEvent/listing', array($fid, 1, 100, 'id', 'desc'));
            $this->assign('events', $events['data']);
            $this->display('mobile/welfare_event_list');
            die;
        } else {
            $activityModel = D('Activity');
            $activities = $activityModel->where(array('status' => array('neq', 1), 'fid' => $fid, 'category' => 1, 'audit' => 1))->order(array('id' => 'desc'))->select();
            $this->assign('activities', $activities);
            $this->display('mobile/online_activity_list');
        }
    }

    // 征集
    public function getZhengJiUrl()
    {
        $fid = I('request.fid', 16, 'intval');
        $uid = $this->getUid();
        if (empty($uid)) {
            $info = 'no_login';
            $code = 2;
            $message = C('no_login');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
        if (empty($fid)) {
            $info = 'parameter_invalid';
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
        $forums = $this->getUserForum($uid);
        $bool = $this->checkInForum($fid, $forums);
        if (!$bool) {
            $info = 'not_in_forum';
            $code = 1;
            $message = '请先加入'.getFansgroupById($fid).'+';
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $url = U('Mobile/addZhengJiUrl');
        $info = $url;
        $code = 0;
        $message = '征集上传';
        $return = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    //添加征集
    public function addZhengJiUrl()
    {
        $uid = $this->getUid();
        $attachmentid = I('request.attachmentid', null);
        $aid = I('request.aid', null);

        if (empty($uid)) {
            $info = 'no_login';
            $code = 2;
            $message = C('no_login');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        if (empty($attachmentid)) {
            $info = 'parameter_invalid';
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $attachmentModel = D('Attachment');
        $attachment = $attachmentModel->where(array('id' => $attachmentid))->find();
        $remote_url = $attachment['remote_url'];

        $this->assign('attachmentid', $attachmentid);
        $this->assign('aid', $aid);
        $this->assign('img', $remote_url);

        $this->display('mobile/add_zhengji');
    }

    //话题页面
    public function topic()
    {
        $tid = I('request.tid', '1');
        if (!isMobile()) {
            header('Location:'.$this->httphost().U('Pc/topic', array('tid' => $tid)));
            exit;
        }
        $topicModel = D('Topic');
        $topic = $topicModel->where(array('id' => $tid, 'status' => 0))->find();
        $topic['content'] = htmlspecialchars_decode($topic['content']);
        $this->assign('topic', $topic);
        if (empty($topic)) {
            $this->display('mobile/404.html');
        } else {
            $this->display('mobile/topic');
        }
    }

    //话题列表
    public function topiclist()
    {
        $fid = I('request.fid', 16, 'intval');
        $topicModel = D('Topic');
        $condition['fid'] = $fid;
        $condition['status'] = 0;
        $order = 'id';
        $sort = 'desc';
        $number = 15;
        $p = 1;
        $count = $topicModel->where($condition)->count();
        $Page = new \Think\AjaxPage($count, $number, 'ajax_hot_topic_page');
        $show = $Page->show(); // 分页显示输出
        $topics = $topicModel->where($condition)->order(array('isadmintop' => 'desc', 'admin_digest' => 'desc', $order => $sort))->limit(($p - 1) * $number, $number)->select();

        foreach ($topics as $k => $v) {
            $content = htmlspecialchars_decode($v['content']);
            preg_match_all('/<img([^>]*)>/', $content, $matches);
            $imglist = '';
            foreach ($matches[0] as $kkey => $vval) {
                preg_match_all("#src=('|\")([^'\"]*)('|\")#", $vval, $matchinfo);
                $src = $matchinfo[2][0];
                $bool = strpos($src, 'http://img.baidu.com/');
                if ($bool !== false) {
                } else {
                    $imglist[$kkey]['src'] = $src;
                }
            }

            $topics[$k]['image_list'] = $imglist;
        }
        $this->assign('fid', $fid);

        $this->assign('topic_page', $show);
        $this->assign('topics', $topics);

        $this->display('mobile/topiclist');
    }

    //回复页面
    public function response()
    {
        $fid = I('request.fid', 16, 'intval');
        $type = I('request.type', 1, 'intval');
        $pid = I('request.pid', 1, 'intval');
        $responseModel = D('Response');
        $condition['type'] = $type;
        $condition['pid'] = $pid;
        $condition['status'] = 0;

        $this->assign('type', $type);
        $this->assign('pid', $pid);
        $this->assign('fid', $fid);
        $responses = $responseModel->where($condition)->order(array('id' => 'desc'))->select();
        $this->assign('responses', $responses);
        $this->display('mobile/response');
    }

    //创建投票活动
    public function create_vote_activity()
    {
        $fid = I('request.fid', 16, 'intval');
        $uid = $this->getUid();
        if (empty($uid)) {
            $info = 'no_login';
            $code = 2;
            $message = C('no_login');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
        if (empty($fid)) {
            $info = 'parameter_invalid';
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
        $forums = $this->getUserForum($uid);
        $bool = $this->checkInForum($fid, $forums);
        if (!$bool) {
            $info = 'not_in_forum';
            $code = 1;
            $message = '请先加入'.getFansgroupById($fid).'+';
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
        $this->assign('fid', $fid);
        $this->display('mobile/create_online_vote_activity');
    }

    //创建征集活动
    public function create_collect_activity()
    {
        $fid = I('request.fid', 16, 'intval');
        $uid = $this->getUid();
        if (empty($uid)) {
            $info = 'no_login';
            $code = 2;
            $message = C('no_login');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
        if (empty($fid)) {
            $info = 'parameter_invalid';
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
        $forums = $this->getUserForum($uid);
        $bool = $this->checkInForum($fid, $forums);
        if (!$bool) {
            $info = 'not_in_forum';
            $code = 1;
            $message = '请先加入'.getFansgroupById($fid).'+';
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $this->assign('fid', $fid);
        $this->display('mobile/create_online_collect_activity');
    }

    //创建话题表单
    public function getCreateTopicUrl()
    {
        $fid = I('request.fid', 16, 'intval');
        $uid = $this->getUid();
        if (empty($uid)) {
            $info = 'no_login';
            $code = 2;
            $message = C('no_login');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
        if (empty($fid)) {
            $info = 'parameter_invalid';
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
        $forums = $this->getUserForum($uid);
        $bool = $this->checkInForum($fid, $forums);
        if (!$bool) {
            $info = 'not_in_forum';
            $code = 1;
            $message = '请先加入'.getFansgroupById($fid).'+';
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $url = U('Mobile/createTopic');
        $info = $url;
        $code = 0;
        $message = '创建话题表单';
        $return = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    //创建表单
    public function createTopic()
    {
        $fid = I('request.fid', 16, 'intval');
        $this->assign('fid', $fid);
        $this->display('mobile/create_topic');
    }

    //工作室页面
    public function forum()
    {
        $fid = I('get.fid', '16', 'intval');
        if (!isMobile()) {
            header('Location:'.$this->httphost().U('Pc/forum', array('fid' => $fid)));
            exit;
        }
        $forumModel = D('Forum');
        $forum = $forumModel->where(array('id' => $fid))->find();
        $this->assign('forum', $forum);
        $this->common_info($fid);

        //视频展示
        $video_list = R('Video/listing', array($fid, 1, 6, 'id', 'desc'));
        //dump($video_list);
        $this->assign('video_page', $video_list['page']);
        $this->assign('videos', $video_list['data']);

        //工作室推荐活动
        $activityModel = D('Activity');
        $online_top_activity = $activityModel->where(array('isadminrecommend' => 1, 'status' => array('neq', 1), 'category' => 2, 'fid' => $fid))->find();
        $online_top_activity_type = $online_top_activity['type'];
        if ($online_top_activity_type == 8) {
            $img = R('Activity/checkIsPostQuestionImg', array($online_top_activity['question_img']));

            if ($img) {
                $online_top_activity['vote_type'] = 'image';
                $online_wrap_questions = R('Activity/onlineWrapQuestion', array($online_top_activity['id']));
                $this->assign('online_wrap_questions', $online_wrap_questions);
            //dump($online_wrap_questions);
            } else {
                $question = $online_top_activity['question'];
                $questions = json_decode($question);
                $this->assign('questions', $questions);
                $online_top_activity['vote_type'] = 'text';
            }
        } else {
            $activityOnlineModel = D('ActivityOnline');
            $p = 1;
            $number = 3;
            $posters = $activityOnlineModel->where(array('status' => 0, 'aid' => $online_top_activity['id']))->order(array('id' => 'desc'))->limit(($p - 1) * $number, $number)->select();
            $this->assign('posters', $posters);
        }
        $this->assign('online_top_activity', $online_top_activity);

        //线上活动
        $online_activities = $activityModel->where(array('status' => array('neq', 1), 'audit' => 1, 'category' => 2, 'fid' => $fid))->order(array('id' => 'desc'))->limit(10)->select();
        $this->assign('online_activities', $online_activities);

        //热门话题
        $topicModel = D('Topic');
        $hot_topics = $topicModel->where(array('status' => 0, 'fid' => $fid))->order(array('isadmintop' => 'desc', 'admin_digest' => 'desc', 'id' => 'desc'))->limit(5)->select();
        foreach ($hot_topics as $k => $v) {
            $content = htmlspecialchars_decode($v['content']);
            preg_match_all('/<img([^>]*)>/', $content, $matches);
            $imglist = '';
            foreach ($matches[0] as $kkey => $vval) {
                preg_match_all("#src=('|\")([^'\"]*)('|\")#", $vval, $matchinfo);
                $src = $matchinfo[2][0];
                $bool = strpos($src, 'http://img.baidu.com/');
                if ($bool !== false) {
                } else {
                    $imglist[$kkey]['src'] = $src;
                }
            }

            $hot_topics[$k]['image_list'] = $imglist;
        }

        $this->assign('hot_topics', $hot_topics);

        //热门公益
        $welfareEventModel = D('WelfareEvent');
        $events = $welfareEventModel->where(array('status' => 0, 'fid' => $fid))->order(array('id' => 'desc'))->select();
        foreach ($events as $key => $val) {
            $img_list = array();
            $attachment_arr = explode(',', $val['attachment']);
            foreach ($attachment_arr as $kkey => $vval) {
                if ($vval) {
                    $img_list[] = getAttachmentUrlById($vval);
                }
            }

            $events[$key]['img_url'] = $img_list[0];
        }
        $this->assign('events', $events);

        $title = getFansgroupById($fid);
        $this->assign('title', $title);
        $this->assign('fid', $fid);
        $this->display('mobile/forum');
    }

    //活动反馈
    public function movieActivityFeedbackImage()
    {
        $aid = I('request.aid', null, 'intval');

        //反馈信息获取
        $activityMovieFeedbackModel = D('ActivityMovieFeedback');
        $feedbacks = $activityMovieFeedbackModel->where(array('status' => 0, 'aid' => $aid, 'isvideo' => 0))->order(array('isvideo' => 'desc', 'id' => 'asc'))->select();
        $attachmentModel = D('Attachment');
        foreach ($feedbacks as $key => $val) {
            $attachmentid = $val['attachmentid'];
            $attachment = $attachmentModel->where(array('id' => $attachmentid))->find();
            $feedbacks[$key]['url'] = $attachment['remote_url'];
            $feedbacks[$key]['image'] = $attachment['remote_url'];
            $feedbacks[$key]['width'] = $attachment['width'];
            $feedbacks[$key]['height'] = $attachment['height'];
        }
        $this->assign('aid', $aid);
        //dump($feedbacks);

        $this->assign('feedbacks', $feedbacks);
        $this->display('mobile/movie_feedback_image');
    }

    public function movieActivityFeedbackVideo()
    {
        $aid = I('request.aid', null, 'intval');
        //反馈信息获取
        $activityMovieFeedbackModel = D('ActivityMovieFeedback');
        $feedbacks = $activityMovieFeedbackModel->where(array('status' => 0, 'aid' => $aid, 'isvideo' => 1))->order(array('isvideo' => 'desc', 'id' => 'asc'))->select();
        $attachmentModel = D('Attachment');
        foreach ($feedbacks as $key => $val) {
            $attachmentid = $val['attachmentid'];

            $attachment = $attachmentModel->where(array('id' => $attachmentid))->find();
            $vid = $attachment['videoid'];
            if (!empty($vid)) {
                $yunVideoModel = D('YunVideo');
                $yunInfo = $yunVideoModel->where(array('id' => $vid))->find();
                //$video_cover = "/Public/default/img/video-default-img.png";

                if ($yunInfo['videocover'] == '/Public/default/img/video-default-img.png') {
                    $data = R('VideoUpload/getVideoInfo', array($yunInfo['name']));

                    if ($data) {
                        if (!empty($data['data']['video_cover'])) {
                            $video_cover = $data['data']['video_cover'];
                            $yunVideoModel->where(array('id' => $vid))->setField(array('videocover' => $video_cover));
                        }
                    }
                }
            }

            $feedbacks[$key]['url'] = $yunInfo['url'];
            $feedbacks[$key]['image'] = $yunInfo['videocover'];
            $feedbacks[$key]['width'] = 300;
            $feedbacks[$key]['height'] = 200;
        }
        $this->assign('aid', $aid);
        $this->assign('feedbacks', $feedbacks);
        $this->display('mobile/movie_feedback_video');
    }

    //反馈视频展示页面
    public function showfeedbackvideo()
    {
        $id = I('request.id', null, 'intval');
        $activityMovieFeedbackModel = D('ActivityMovieFeedback');
        $attachmentModel = D('Attachment');
        $feedback = $activityMovieFeedbackModel->where(array('id' => $id))->find();
        $attachmentid = $feedback['attachmentid'];
        $attachment = $attachmentModel->where(array('id' => $attachmentid))->find();
        $vid = $attachment['videoid'];
        if (!empty($vid)) {
            $yunVideoModel = D('YunVideo');
            $yunInfo = $yunVideoModel->where(array('id' => $vid))->find();
            $this->assign('video', $yunInfo);
        }

        $aid = $feedback['aid'];
        //反馈信息获取
        $feedbacks = $activityMovieFeedbackModel->where(array('status' => 0, 'aid' => $aid, 'isvideo' => 1))->order(array('isvideo' => 'desc', 'id' => 'asc'))->select();
        foreach ($feedbacks as $key => $val) {
            $attachmentid = $val['attachmentid'];

            $attachment = $attachmentModel->where(array('id' => $attachmentid))->find();
            $vid = $attachment['videoid'];
            if (!empty($vid)) {
                $yunVideoModel = D('YunVideo');
                $yunInfo = $yunVideoModel->where(array('id' => $vid))->find();
                //$video_cover = "/Public/default/img/video-default-img.png";

                if ($yunInfo['videocover'] == '/Public/default/img/video-default-img.png') {
                    $data = R('VideoUpload/getVideoInfo', array($yunInfo['name']));

                    if ($data) {
                        if (!empty($data['data']['video_cover'])) {
                            $video_cover = $data['data']['video_cover'];
                            $yunVideoModel->where(array('id' => $vid))->setField(array('videocover' => $video_cover));
                        }
                    }
                }
            }

            $feedbacks[$key]['url'] = $yunInfo['url'];
            $feedbacks[$key]['image'] = $yunInfo['videocover'];
            $feedbacks[$key]['width'] = 300;
            $feedbacks[$key]['height'] = 200;
        }
        $this->assign('aid', $aid);
        $this->assign('feedbacks', $feedbacks);
        $this->display('mobile/show_feedback_video');
    }

    //视频展示页面
    public function showvideo()
    {
        $id = I('request.id', null, 'intval');
        $videoModel = D('Video');
        $video = $videoModel->where(array('status' => 0, 'id' => $id))->find();
        if (!empty($video['videoid'])) {
            $yunVideoModel = D('YunVideo');
            $yunInfo = $yunVideoModel->where(array('id' => $video['videoid']))->find();
            $video['mobileurl'] = $yunInfo['url'].'.f30.mp4';
            $video['cover'] = $yunInfo['videocover'];
            $video['url'] = $yunInfo['url'];
        }
        //dump($video);
        $this->assign('video', $video);

        //视频展示

        $video_list = R('Video/listing', array($video['fid'], 1, 6, 'id', 'desc'));
        //dump($video_list);
        $this->assign('video_page', $video_list['page']);
        $this->assign('videos', $video_list['data']);

        $this->display('mobile/show_video');
    }

    public function chat()
    {
        $type = I('request.type', null, 'intval');
        $pid = I('request.pid', null, 'intval');

        $p = 1;
        $number = 10;
        $order = 'id';
        $sort = 'desc';
        $responses = R('Response/listing', array($p, $number, $pid, $type, $order, $sort));
        $data = $responses['data'];
        //dump($data);
        $data = array_reverse($data, true);
        //dump($data);
        //dump($responses);
        $this->assign('pid', $pid);
        $this->assign('type', $type);
        $this->assign('responses', $data);
        $this->display('mobile/chat');
    }

    public function searchUser()
    {
        $keyword = I('request.keyword', null);
        $info = U('Mobile/searchResult', array('keyword' => $keyword));
        $code = 0;
        $message = '搜索结果页';
        $return = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    public function searchResult()
    {
        $keyword = I('request.keyword', null);
        if (!empty($keyword)) {
            $userModel = D('User');
            $condition['nickname'] = array('like', '%'.$keyword.'%');
            $info = $userModel->where($condition)->order(array('id' => 'asc'))->limit(($p - 1) * $number, $number)->select();
            unset($info['password']); //移除用户密码字段
            $this->assign('lists', $info);
        }
        $this->display('mobile/search_result');
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

    public function baiwansenlin()
    {
        //时间
        $welfareEventModel = D('WelfareEvent');
        $event = $welfareEventModel->where(array('id' => 6))->find();
        $this->assign('event', $event);

        //征集到的钱
        $donateOrderModel = D('DonateOrder');
        $collect_money = $donateOrderModel->where(array('order_status' => 1))->sum('order_fee');
        $users = $donateOrderModel->distinct(true)->field('uid')->select();
        $collect_user_num = count($users);

        if (empty($collect_money)) {
            $this->assign('tree_num', 0);
            $this->assign('collect_money', 0);
            $this->assign('collect_user_num', 0);
        } else {
            $tree_num = sprintf('%.1f', $collect_money / 10);
            $this->assign('tree_num', $tree_num);
            $this->assign('collect_money', money_num_format($collect_money));
            $this->assign('collect_user_num', $collect_user_num);
        }
        $this->display('mobile/baiwansenlin');
    }

    //捐赠列表
    public function donatelist()
    {
        $donateOrderModel = D('DonateOrder');
        $orderlist = $donateOrderModel->where(array('order_status' => 1))->order(array('pay_time' => 'desc'))->limit(5)->select();
        $count = $donateOrderModel->where(array('order_status' => 1))->count();
        $number = 1000;
        foreach ($orderlist as $key => $val) {
            $orderlist[$key]['username'] = getUserNicknameById($val['uid']);
            $orderlist[$key]['addtime'] = date('Y-m-d', $val['add_time']);
            $orderlist[$key]['treenum'] = sprintf('%.1f', $val['order_fee'] / 10);
        }
        $Page = new \Think\AjaxPage($count, $number, 'ajax_page_donate_order');
        $this->assign('order_page', $Page->show());
        $this->assign('orderlist', $orderlist);
        $this->display('mobile/donatelist');
    }

    public function donationform()
    {
        $event['name'] = '吴亦凡出道四周年公益应援————为爱播种，拒绝荒漠';
        $this->assign('aid', 1);
        $this->assign('event', $event);
        $this->display('mobile/pay_welfare_event');
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
        $this->display('buy_success');
    }

    //扫码页面
    public function scan()
    {
        $this->display('scan');
    }

    //扫码成功页面
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
}

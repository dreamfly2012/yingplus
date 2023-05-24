<?php

namespace Home\Controller;

class PcController extends CommonController
{
    public function __construct()
    {
        parent::__construct();
        if (isMobile()) {
            if (ACTION_NAME == 'baiwansenlin') {
                $this->redirect('Mobile/baiwansenlin');
            } else {
                $this->redirect('Mobile/index');
            }
        }
    }

    public function center()
    {
        $this->display('pc/center');
    }

    //旁边挂件
    public function aside()
    {
        $fid = $this->getFid();
        $this->common_info($fid);
    }

    //页面共有元素
    public function common_info($fid)
    {
        //公益应援
        $events = R('WelfareEvent/listing', array($fid, 1, 5, 'id', 'desc'));
        $this->assign('events', $events['data']);
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
        $wuyifan_seminar_url = 'http://xiayouqiaomu.80shihua.com/';
        $this->assign('wuyifan_seminar_url', $wuyifan_seminar_url);
        $liuyifei_seminar_url = 'http://yekongque.80shihua.com/';
        $this->assign('liuyifei_seminar_url', $liuyifei_seminar_url);
        $zhonghanliang_seminar_url = 'http://threeontheroad.80shihua.com/';
        $this->assign('zhonghanliang_seminar_url', $zhonghanliang_seminar_url);

        $movie = R('MovieActivity/movieInfoByFid', array($fid));
        $this->assign('movie', $movie);
        $releasetime = $movie['releasetime'];
        $daojishi = $releasetime - time();
        $this->assign('daojishi', $daojishi);

        $condition['movie'] = $movie['id'];
        $condition['audit'] = 1;
        $condition['status'] = array('neq', 1);
        $activityModel = D('Activity');
        $activities = $activityModel->where($condition)->select();
        $this->assign('activities', $activities);
        $activity_count = count($activities);
        $this->assign('activity_count', $activity_count);

        //线上活动
        $online_activities = $activityModel->where(array('status' => array('neq', 1), 'category' => 2, 'audit' => 1, 'fid' => $fid))->order(array('id' => 'desc'))->limit(5)->select();
        $this->assign('online_activities', $online_activities);

        //话题列表
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

        $this->assign('topic_page', $show);
        $this->assign('topics', $topics);
    }

    //首页
    public function index()
    {
        $forum['fansgroup'] = '可能是最好的影视粉丝社区';
        $this->assign('forum', $forum);
        $this->display('pc/index');
    }

    public function get_address_by_ip($ip)
    {
        $info = file_get_contents('http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=json&ip='.$ip);
        if ($info == '-2') {
            return '火星';
        } else {
            $obj = json_decode($info);

            return $obj->country.$obj->province.$obj->city.$obj->district;
        }
    }

    //留言墙
    public function msgwall()
    {
        $p = I('request.p', '1');
        $msgwallModel = D('Msgwall'); // 实例化User对象
        $count = $msgwallModel->where('status=0')->count(); // 查询满足要求的总记录数
        $Page = new \Think\Page($count, 10); // 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show = $Page->show(); // 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $list = $msgwallModel->where('status=0')->order(array('addtime' => 'desc'))->limit($Page->firstRow.','.$Page->listRows)->select();
        foreach ($list as $k => $v) {
            $list[$k]['address'] = $this->get_address_by_ip($v['ip']);
        }
        //dump($list);
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display('pc/msgwall');
    }

    //登陆页面
    public function login()
    {
        $this->display('pc/login');
    }

    //注册页面
    public function register()
    {
        $this->display('pc/register');
    }

    //忘记密码
    public function forgetpassword()
    {
        $this->display('pc/forgetpassword');
    }

    //退出页面
    public function logout()
    {
        $old_url = getenv('HTTP_REFERER');
        $parameter = '';
        $index = stripos($old_url, '?');
        $length = strlen($old_url);
        if ($index) {
            $old_url = substr($old_url, 0, $index);
            $parameter = substr($old_url, $index + 1, $length - $index);
        }

        session_destroy();
        session('uid', null);
        cookie('uid', null);
        cookie('uid_info', null);
        if ($old_url) {
            header('location:'.$old_url.'?sso=logout&'.$parameter);
        } else {
            $this->redirect('pc/index', array('sso' => 'logout'));
        }
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
        $this->display('pc/show_video');
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

        $this->display('pc/launch');
    }

    public function createonlineactivity()
    {
        $fid = I('request.fid', null, 'intval');
        $uid = $this->getUid();
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
            $message = '请先加入工作室';
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $this->assign('fid', $fid);

        $template = $this->fetch('pc/create_online_activity');
        $info = $template;
        $code = 0;
        $message = '创建包场活动表单';
        $return = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    public function upload_video_form()
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
            $message = '请先加入工作室';
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        R('VideoUpload/index', array($fid));
    }

    public function createmovieactivity()
    {
        $fid = I('request.fid', null, 'intval');
        $uid = $this->getUid();
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
            $message = '请先加入工作室';
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

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

        $template = $this->fetch('pc/create_movie_activity');
        $info = $template;
        $code = 0;
        $message = '创建包场活动表单';
        $return = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    //活动页面
    public function activity()
    {
        $aid = I('request.aid', null, 'intval');
        if (empty($aid)) {
            $info = null;
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
        $this->assign('response_type', 2);
        $this->assign('pid', $aid);
        $this->aside();

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
            $this->display('line_activity');
            die;
        }

        if ($category == 1) {
            //包场活动聊天信息
            $responses = R('Response/listing', array(1, 10, $activity['id'], 2));
            $activity_responses = $responses['data'];
            $activity_responses = array_reverse($activity_responses);
            $this->assign('activity_responses', $activity_responses);

            //报名信息
            $activityEnrollModel = D('ActivityEnroll');
            $enrollInfo = $activityEnrollModel->where(array('aid' => $aid, 'status' => 0))->select();
            $this->assign('enrollInfo', $enrollInfo);

            //反馈信息获取
            $activityMovieFeedbackModel = D('ActivityMovieFeedback');
            $feedbacks = $activityMovieFeedbackModel->where(array('status' => 0, 'aid' => $aid))->order(array('isvideo' => 'desc', 'id' => 'asc'))->select();
            $attachmentModel = D('Attachment');
            foreach ($feedbacks as $key => $val) {
                $attachmentid = $val['attachmentid'];
                $attachment = $attachmentModel->where(array('id' => $attachmentid))->find();

                if ($attachment['isvideo'] == 0) {
                    $feedbacks[$key]['url'] = $attachment['remote_url'];
                    $feedbacks[$key]['image'] = $attachment['remote_url'];
                    $feedbacks[$key]['width'] = $attachment['width'];
                    $feedbacks[$key]['height'] = $attachment['height'];
                } elseif ($attachment['isvideo'] == 1) {
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

                        $feedbacks[$key]['url'] = $yunInfo['url'];
                        $feedbacks[$key]['image'] = $yunInfo['videocover'];
                        $feedbacks[$key]['width'] = $yunInfo['width'];
                        $feedbacks[$key]['height'] = $yunInfo['height'];
                    }
                }
            }

            $this->assign('feedbacks', $feedbacks);

            $seo['description'] = subtxt(strip_tags($activity['content']), 30);
            $seo['keywords'] = getForumNameById($activity['fid']).','.getFansGroupById($activity['fid']).',包场,电影包场,粉丝包场';
            $this->assign('seo', $seo);
            $this->display('pc/movie_activity');
            die;
        }

        if ($category == 2) {
            $responses = R('Response/listing', array(1, 10, $aid, 2, 'id', 'desc'));
            $this->assign('responses', $responses);
            if ($type == 8) {
                $wrap_questions = R('Activity/onlineWrapQuestion', array($aid));
                $wrap_results = R('Activity/onlineWrapResults', array($aid));
                $this->assign('wrap_questions', $wrap_questions);
                $this->assign('wrap_results', $wrap_results);
                //dump($wrap_results);
                if (R('Activity/checkIsPostQuestionImg', array($question_img))) {
                    $seo['description'] = subtxt(strip_tags($activity['content']), 30);
                    $seo['keywords'] = getForumNameById($activity['fid']).','.getFansGroupById($activity['fid']).',投票征集';
                    $this->assign('seo', $seo);
                    $this->display('pc/online_activity_vote_image');
                    die;
                } else {
                    $question = $activityModel->where(array('id' => $aid))->getField('question');
                    $questions = json_decode($question);
                    $this->assign('questions', $questions);
                    $seo['description'] = subtxt(strip_tags($activity['content']), 30);
                    $seo['keywords'] = getForumNameById($activity['fid']).','.getFansGroupById($activity['fid']).',投票征集';
                    $this->assign('seo', $seo);
                    $this->display('pc/online_activity_vote_text');
                    die;
                }
                die;
            }

            if ($type == 9) {
                $activityOnlineModel = D('ActivityOnline');
                $p = I('request.p', 1, 'intval');
                $number = 15;
                $count = $activityOnlineModel->where(array('status' => 0, 'aid' => $aid))->count();
                $posters = $activityOnlineModel->where(array('status' => 0, 'aid' => $aid))->order(array('id' => 'desc'))->limit(($p - 1) * $number, $number)->select();
                $Page = new \Think\Page($count, $number);
                $show = $Page->show();
                $this->assign('poster_page', $show);
                $this->assign('poster_count', $count);
                $this->assign('posters', $posters);
                $seo['description'] = subtxt(strip_tags($activity['content']), 30);
                $seo['keywords'] = getForumNameById($activity['fid']).','.getFansGroupById($activity['fid']).',线上投票活动';
                $this->assign('seo', $seo);
                $this->display('pc/online_activity_collection');
                die;
            }

            $seo['description'] = subtxt(strip_tags($activity['content']), 30);
            $seo['keywords'] = getForumNameById($activity['fid']).','.getFansGroupById($activity['fid']).',线上投票活动';
            $this->assign('seo', $seo);

            $this->display('pc/online_activity_collection');
            die;
        }
    }

    //活动列表页
    public function activitylist()
    {
        //电影
        $fid = 16;
        $seminar_url = 'http://www.xiayouqiaomu.com/';
        $this->assign('seminar_url', $seminar_url);
        $movie = R('MovieActivity/movieInfoByFid', array($fid));
        $this->assign('movie', $movie);
        $releasetime = $movie['releasetime'];
        $daojishi = $releasetime - time();
        $this->assign('daojishi', $daojishi);

        //所有活动
        $condition['fid'] = $fid;
        $condition['audit'] = 1;
        $condition['status'] = array('neq', 1);
        $activityModel = D('Activity');
        $recommend_activities = $activityModel->where($condition)->order(array('isadminrecommend' => 'desc', 'addtime' => 'desc'))->limit(0, 10)->select();
        $recommend_activity_count = $activityModel->where($condition)->count();
        $RecommendActivityPage = new \Think\AjaxPage($recommend_activity_count, 10, 'ajax_recommend_activity_page');
        $this->assign('recommend_activities', $recommend_activities);
        $this->assign('recommend_activities_page', $RecommendActivityPage->show());

        //电影活动
        $meta['fid'] = $fid;
        $meta['audit'] = 1;
        $meta['category'] = 1;
        $meta['status'] = array('neq', 1);
        $activityModel = D('Activity');

        $activities = $activityModel->where($meta)->order(array('id' => 'desc'))->limit(10)->select();
        $activity_count = $activityModel->where($meta)->count();
        $MoviePage = new \Think\AjaxPage($activity_count, 10, 'ajax_movie_activity_page');
        $this->assign('movie_activity_page', $MoviePage->show());
        $this->assign('activities', $activities);
        $this->assign('activity_count', $activity_count);

        //线上活动
        $online_count = $activityModel->where(array('status' => array('neq', 1), 'category' => 2, 'audit' => 1, 'fid' => $fid))->count();
        $online_activities = $activityModel->where(array('status' => array('neq', 1), 'category' => 2, 'audit' => 1, 'fid' => $fid))->limit(10)->order(array('id' => 'desc'))->select();
        $this->assign('online_count', $online_count);
        $OnlinePage = new \Think\AjaxPage($online_count, 10, 'ajax_online_activity_page');
        $this->assign('online_page', $OnlinePage->show());
        $this->assign('online_activities', $online_activities);

        //公益应援
        $events = R('WelfareEvent/listing', array($fid, 1, 10, 'id', 'desc'));
        $welfareEventModel = D('WelfareEvent');
        $event_count = $welfareEventModel->where(array('status' => 0, 'fid' => $fid))->count();
        $EventPage = new \Think\Page($event_count, 10, 'ajax_welfare_event_page');
        $this->assign('events', $events['data']);
        $this->assign('event_page', $EventPage->show());

        $this->assign('fid', $fid);
        $this->display('pc/activity_list');
    }

    //所有活动按推荐排列分页
    public function recommendActivityPage()
    {
        $activityModel = D('Activity');
        $p = I('request.p', 1, 'intval');
        $fid = I('request.fid', null, 'intval');
        $number = 10;
        $condition['fid'] = $fid;
        $condition['status'] = array('neq', 1);
        $condition['audit'] = 1;
        $activities = $activityModel->where($condition)->order(array('isadminrecommend' => 'desc', 'addtime' => 'desc'))->limit(($p - 1) * $number, $number)->select();

        $count = $activityModel->where($condition)->count();
        $Page = new \Think\AjaxPage($count, $number, 'ajax_recommend_activity_page');
        $this->assign('recommend_activities', $activities);
        $this->assign('recommend_activities_page', $Page->show());
        $this->display('pc/ajax_recommend_activity_page');
    }

    //包场分页
    public function movieActivityPage()
    {
        $activityModel = D('Activity');
        $p = I('request.p', 1, 'intval');
        $fid = I('request.fid', null, 'intval');
        $number = 10;
        $condition['category'] = 1;
        $condition['fid'] = $fid;
        $condition['status'] = array('neq', 1);
        $condition['audit'] = 1;
        $activities = $activityModel->where($condition)->limit(($p - 1) * $number, $number)->select();
        $count = $activityModel->where($condition)->count();
        $Page = new \Think\AjaxPage($count, $number, 'ajax_movie_activity_page');
        $this->assign('activities', $activities);
        $this->assign('movie_activity_page', $Page->show());
        $this->display('pc/ajax_movie_activity_page');
    }

    //线上活动分页
    public function onlineActivityPage()
    {
        $activityModel = D('Activity');
        $p = I('request.p', 1, 'intval');
        $fid = I('request.fid', null, 'intval');
        $number = 10;
        $condition['category'] = 2;
        $condition['fid'] = $fid;
        $condition['status'] = array('neq', 1);
        $condition['audit'] = 1;
        $activities = $activityModel->where($condition)->limit(($p - 1) * $number, $number)->select();
        $count = $activityModel->where($condition)->count();
        $Page = new \Think\AjaxPage($count, $number, 'ajax_online_activity_page');
        $this->assign('online_activities', $activities);
        $this->assign('online_page', $Page->show());
        $this->display('pc/ajax_online_activity_page');
    }

    //公益分页
    public function welfareEventPage()
    {
        $welfareEventModel = D('WelfareEvent');
        $p = I('request.p', 1, 'intval');
        //TODO:公益分页
        $fid = I('request.fid', null, 'intval');
        $number = 10;
        $condition['status'] = 0;
        $events = $welfareEventModel->where($condition)->limit(($p - 1) * $number, $number)->select();
        $count = $welfareEventModel->where($condition)->count();
        $Page = new \Think\AjaxPage($count, $number, 'ajax_welfare_event_page');
        $this->assign('events', $events);
        $this->assign('event_page', $Page->show());
        $this->display('pc/ajax_welfare_event_page');
    }

    //文字投票结果展示
    public function online_vote_text_result()
    {
        $aid = I('request.aid', null, 'intval');
        $wrap_results = R('Activity/onlineWrapResults', array($aid));
        $this->assign('wrap_results', $wrap_results);
        $this->display('pc/vote_text_result');
    }

    //图片投票结果展示
    public function online_vote_image_result()
    {
        $aid = I('request.aid', null, 'intval');
        $wrap_results = R('Activity/onlineWrapResults', array($aid));
        $this->assign('wrap_results', $wrap_results);
        $this->display('pc/vote_img_result');
    }

    //微博分享
    public function share_weibo_img()
    {
        $img_data = I('request.img_data');
        $id = I('request.id');
        $weibo_code = session('weibo_login');

        if (isset($weibo_code)) {
            $img_data = str_replace('data:image/png;base64,', '', $img_data);
            $img_data = str_replace(' ', '+', $img_data);
            $data = base64_decode($img_data);

            $activityModel = D('Activity');
            $subject = $activityModel->where(array('id' => $id))->getField('subject');
            $url = 'http://www.yingplus.cc/'.U('Pc/activity', array('aid' => $id));
            $name = uniqid();
            $img_path = './uploads/'.$name.'.png';
            file_put_contents($img_path, $data);

            $code = $weibo_code;
            $weiboLogin = new WeiboLoginController($code, C('SINA_CLIENT_ID'), C('SINA_CLIENT_SECRET'), C('SINA_REDIRECT_URL'));
            $content = '我参加了#梅格妮+#的线上活动#'.$subject.'#，赶紧来围观（'.$url.'）！';
            $pic = '@'.realpath(realpath(__ROOT__).'/uploads/'.$name.'.png').';type=image/png;';

            $weiboLogin->sendWeibo($content, $pic);
        }
    }

    //益起来
    public function welfare_comeon()
    {
        $this->display('pc/yiqilai');
    }

    public function zhonghanliang_welfare_comeon()
    {
        $this->display('pc/zhonghanliang_yiqilai');
    }

    //百万森林
    public function baiwansenlin()
    {
        //回复
        $responses = R('Response/listing', array(1, 10, 1, 5));
        $this->assign('responses', $responses);

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

        $orderlist = $donateOrderModel->where(array('order_status' => 1))->order(array('pay_time' => 'desc'))->limit(5)->select();
        $count = $donateOrderModel->where(array('order_status' => 1))->count();
        $number = 5;
        foreach ($orderlist as $key => $val) {
            $orderlist[$key]['username'] = getUserNicknameById($val['uid']);
            $orderlist[$key]['addtime'] = date('Y-m-d', $val['add_time']);
            $orderlist[$key]['treenum'] = sprintf('%.1f', $val['order_fee'] / 10);
        }
        $Page = new \Think\AjaxPage($count, $number, 'ajax_page_donate_order');
        $this->assign('order_page', $Page->show());
        $this->assign('orderlist', $orderlist);

        $this->assign('fid', 16);
        $this->assign('pid', 1);
        $this->assign('response_type', 5);
        $this->display('pc/baiwansenlin');
    }

    //支付名单
    public function donatepage()
    {
        $p = I('request.p', 1, 'intval');
        $number = 5;
        $donateOrderModel = D('DonateOrder');
        $orderlist = $donateOrderModel->where(array('order_status' => 1))->order(array('pay_time' => 'desc'))->limit(($p - 1) * $number, $number)->select();
        $count = $donateOrderModel->where(array('order_status' => 1))->count();

        foreach ($orderlist as $key => $val) {
            $orderlist[$key]['username'] = getUserNicknameById($val['uid']);
            $orderlist[$key]['addtime'] = date('Y-m-d', $val['add_time']);
            $orderlist[$key]['treenum'] = sprintf('%.1f', $val['order_fee'] / 10);
        }
        $Page = new \Think\AjaxPage($count, $number, 'ajax_page_donate_order');
        $this->assign('order_page', $Page->show());
        $this->assign('orderlist', $orderlist);
        $this->display('pc/baiwansenlin_order_page');
    }

    //捐赠
    public function donationform()
    {
        $event['name'] = '吴亦凡出道四周年公益应援————为爱播种，拒绝荒漠';
        $this->assign('aid', 1);
        $this->assign('event', $event);
        $this->display('pc/pay_welfare_event');
    }

    //公益发起页面
    public function launch_public_welfare()
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

        $this->display('pc/launch_public_welfare');
    }

    //公益页面
    public function welfare()
    {
        $this->display('welfare');
    }

    //创建话题
    public function createtopic()
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
            $message = '请先加入工作室';
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $this->assign('fid', $fid);

        $template = $this->fetch('pc/create_topic');
        $info = $template;
        $code = 0;
        $message = '创建话题表单';
        $return = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    //话题页面
    public function topic()
    {
        $tid = I('request.tid', 1, 'intval');

        if (empty($tid)) {
            $info = null;
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $topicModel = D('Topic');
        $topic = $topicModel->where(array('id' => $tid, 'status' => array('neq', 1)))->find();
        
        if (empty($topic)) {
            die('话题不存在');
        }
        $fid = $topic['fid'];
        $forum = D('forum')->where(array('id'=>$fid))->find();
        $this->assign('forum',$forum);
        $this->assign('response_type', 1);
        $this->assign('pid', $tid);
        $this->aside();

        $responses = R('Response/listing', array(1, 10, $topic['id'], 1));
        $this->assign('responses', $responses);

        $this->assign('fid', $fid);
        $topic['content'] = htmlspecialchars_decode($topic['content']);
        $this->assign('topic', $topic);

        $seo['description'] = subtxt(strip_tags(htmlspecialchars_decode($topic['content'])), 30);
        $seo['keywords'] = getForumNameById($fid).','.getFansGroupById($fid).',粉丝社区,粉丝话题';
        $this->assign('seo', $seo);
        $this->display('pc/topic');
    }

    //征集
    public function addZhengji()
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
        $template = $this->fetch('pc/add_zhengji');
        $info = $template;
        $code = 0;
        $message = '征集上传';
        $return = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    //热门话题列表
    public function hot_topic_list()
    {
        $fid = I('request.fid', null, 'intval');
        $p = I('request.p', 1, 'intval');
        $number = I('request.number', 15, 'intval');
        $number = ($number > 30) ? 30 : $number;

        if (empty($fid)) {
            $info = 'parameter_invalid';
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $topicModel = D('Topic');
        $condition['fid'] = $fid;
        $condition['status'] = 0;
        $order = 'id';
        $sort = 'desc';

        $count = $topicModel->where($condition)->count();
        $Page = new \Think\AjaxPage($count, $number, 'ajax_hot_topic_page');
        $show = $Page->show(); // 分页显示输出
        $topics = $topicModel->where($condition)->order(array('isadmintop' => 'desc', 'admin_digest' => 'desc', $order => $sort))->limit(($p - 1) * $number, $number)->select();
        $this->assign('topic_page', $show);
        $this->assign('topics', $topics);

        $this->display('pc/aside_hot_topic_page');
    }

    //视频列表
    public function video_list()
    {
        $fid = I('request.fid', null, 'intval');
        $p = I('request.p', 1, 'intval');
        $number = I('request.number', 6, 'intval');
        $order = I('request.order', 'id');
        $sort = I('request.sort', 'desc');
        ($number > 50) ? $number = 50 : '';

        $video_list = R('Video/listing', array($fid, $p, $number, $order, $sort));
        $this->assign('video_page', $video_list['page']);
        $this->assign('videos', $video_list['data']);
        $this->display('pc/video_page');
    }

    //工作室页面
    public function forum()
    {
        $fid = I('request.fid', 16, 'intval');

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

        //工作室人员
        $forumUserModel = D('ForumUser');
        $userlist = $forumUserModel->where(array('status' => 0, 'fid' => $fid))->select();
        $this->assign('userlist', $userlist);

        //工作室聊天信息
        $responses = R('Response/listing', array(1, 10, $fid, 4));
        $forum_responses = $responses['data'];
        $forum_responses = array_reverse($forum_responses);
        foreach ($forum_responses as $key => $val) {
            $forum_responses[$key]['username'] = getUserNicknameById($val['uid']);
        }
        $this->assign('forum_responses', $forum_responses);

        //照片墙
        $pictures = R('ForumPicture/listing', array($fid, 1, 100));
        $pictures_data = $pictures['data'];
        $pictures_num = count($pictures_data);
        $picture_pages = ceil($pictures_num / 11);
        $this->assign('picture_pages', $picture_pages);
        $this->assign('pictures', $pictures_data);
        $this->assign('fid', $fid);
        $this->display('pc/forum');
    }

    //我的个人中心
    public function personcenter()
    {
        $this->display('pc/personcenter');
    }

    //搜索页面
    public function search()
    {
        $this->display('pc/search');
    }

    //包场专题页
    public function movie()
    {
        $mid = I('request.mid', '1', 'intval');
        $this->assign('mid', $mid);
        $this->display('pc/movie_index');
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
            $this->display('pc/movie_detail');
        } else {
            $this->display('pc/movie_detail_1');
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
            $this->display('pc/movie_feedback');
        }
    }

    //包场列表页
    public function movielist()
    {
        $this->display('pc/movie_list');
    }

    //留下电话号码页面
    public function leave_movie_activity_telephone()
    {
        $this->display('pc/leave_movie_activity_telephone');
    }

    //支付页面
    public function pay()
    {
        $this->display('pc/pay');
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
        $this->display('pc/pay_weixin_order');
    }

    //支付成功页面
    public function paysuccess()
    {
        $this->display('pc/buy_success');
    }

    //扫码页面
    public function scan()
    {
        $this->display('pc/scan');
    }

    //扫码成功页面
    public function scansuccess()
    {
        $this->display('pc/scan_success');
    }
}

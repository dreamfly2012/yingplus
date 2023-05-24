<?php
/**
 * Created by PhpStorm.
 * User: dreamfly
 * Date: 2015/8/11
 * Time: 9:38
 */

namespace Home\Controller;

use Home\Model\ActivityModel;

class ActivityController extends CommonController
{
    //首页
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
        $aid = I('request.aid', null, 'intval');
        if (empty($aid)) {
            $info    = null;
            $code    = -1;
            $message = C('parameter_invalid');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $activityModel = D('Activity');
        $activity      = $activityModel->where(array('id' => $aid, 'status' => array('neq', 1)))->find();
        $info          = $activity;
        $code          = 0;
        $message       = '活动详细信息';
        $return        = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    //活动列表
    public function listing(){
        $p             = I('request.p', 1, 'intval');
        $number        = I('request.number', 10, 'intval');
        $number        = ($number > 50) ? 50 : $number;
        $category      = I('request.category', 0, 'intval');
        $order         = I('request.order', 'desc');
        $activityModel = D('Activity');
        $activities = $activityModel->where(array('status' => array('neq', 1), 'category' => $category))->order(array('id' => $order))->limit(($p - 1) * $number, $number)->select();
        return $activities;
    }

    //活动列表
    public function getlisting()
    {
        $activities = $this->listing();
        $info       = $activities;
        $code       = 0;
        $message    = '批量获取活动信息';
        $return     = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    public function hot(){
        $activityModel  = D('Activity');
        $activities = $activityModel->getHotPlatformActivity();
        foreach ($hot_activities as $key => $val) {
            $hot_activities[$key]['img']       = buildImgUrl(getAttachmentUrlById($val['img']));
            $hot_activities[$key]['url']       = U('Index/activity', array('id' => $val['id']));
            $hot_activities[$key]['forumname'] = getForumNameById($val['fid']);
            $hot_activities[$key]['forumurl']  = U('Index/forum', array('id' => $val['fid']));
        }
        return $activities;
    }

    //热门活动
    public function gethot()
    {
        $activities = $this->hot();
        $info    = $activities;
        $code    = 0;
        $message = '网站热门活动';
        $return  = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    //首页推荐活动
    public function indexrecommend()
    {
        $indexrecommendActivityModel = D('IndexRecommendActivity');
        $activityModel               = D('Activity');
        $recommend_activities        = $indexrecommendActivityModel->getActivity();

        foreach ($recommend_activities as $key => $val) {
            $activity                                    = $activityModel->where(array('id' => $val['aid']))->find();
            $recommend_activities[$key]['detail']        = $activity;
            $recommend_activities[$key]['indexsmallimg'] = buildImgUrl(getAttachmentUrlById($val['indexsmallimg']));
            $recommend_activities[$key]['indexbigimg']   = buildImgUrl(getAttachmentUrlById($val['indexbigimg']));
            $recommend_activities[$key]['url']           = U('Index/activity', array('id' => $val['aid']));
        }
        return $recommend_activities;
    }
    //首页推荐活动
    public function getindexrecommend()
    {
        $recommend_activities = $this->indexrecommend();
        $info                 = $recommend_activities;
        $code                 = 0;
        $message              = '网站推荐活动';
        $return               = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    //工作室推荐活动
    public function forumrecommend()
    {
        //获取经纪人推荐活动
        $fid = I('request.fid', null, 'intval');
        if (empty($fid)) {
            $info    = null;
            $code    = -1;
            $message = C('parameter_invalid');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
        $activityRecommendModel = D('ActivityRecommend');
        $recommend_activities   = $activityRecommendModel->join('yj_activity ON yj_activity_recommend.aid = yj_activity.id')->where(array('yj_activity_recommend.fid' => $fid, 'yj_activity_recommend.isrecommend' => 0, 'yj_activity_recommend.status' => 0, 'yj_activity.status' => array('neq', 1)))->select();
        foreach ($recommend_activities as $key => $value) {
            $recommend_activities[$key]['href'] = U('Index/activity', array('id' => $value['id']));
            $recommend_activities[$key]['img']  = buildImgUrl(getAttachmentUrlById($value['img']));
        }
    }
    //工作室推荐活动
    public function getforumrecommend(){
        $activities = $this->forumrecommend();
        $info    = $activities;
        $code    = 0;
        $message = '工作室推荐活动';
        $return  = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    public function detail()
    {
        $id = I('request.id', null, 'intval');
        if (empty($id)) {
            $info    = null;
            $code    = -1;
            $message = C('parameter_invalid');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $activityModel     = D('Activity');
        $info              = $activityModel->where(array('id' => $id))->find();
        $info['place']     = getPlaceNameById($info['holdprovince']) . getPlaceNameById($info['holdcity']) . $info['detailaddress'];
        $info['enrollnum'] = getActivityEnrollCount($info['id']);
        $info['img']       = buildImgUrl(getAttachmentUrlById($info['id']));
        $info['userphoto'] = buildImgUrl(getUserPhotoById($info['uid']));
        $info['username']  = getUserNicknameById($info['uid']);
        $code              = 0;
        $message           = '活动详情';
        $return            = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    //活动筛选
    public function filter()
    {
        $data         = array();
        $lingtime     = strtotime('today');
        $last_weekend = strtotime('last monday');
        $weekend      = strtotime('next sunday');
        $date         = I('request.date', null);
        $place        = I('request.place', null);
        $type         = I('request.type', null);
        $forum        = I('request.forum', null);
        $status       = I('request.status', null);
        $digest       = I('request.digest', null);
        $recommend    = I('request.recommend', null);
        session('activity_date', $date);
        session('activity_place', $place);
        session('activity_type', $type);
        session('activity_forum', $forum);
        session('activity_status', $status);
        session('activity_digest', $digest);
        session('activity_recommend', $recommend);

        if ($date == null) {

        } elseif ($date == 'today') {
            $data['holdstart'] = array('between', array($lingtime, $lingtime + 24 * 60 * 60));
        } elseif ($date == 'tomorrow') {
            $data['holdstart'] = array('between', array($lingtime + 24 * 60 * 60, $lingtime + 48 * 60 * 60));
        } elseif ($date == 'weekend') {
            $data['holdstart'] = array('between', array($last_weekend, $weekend));
        } else {
            $data['holdstart'] = array('between', strtotime($date), strtotime($date) + 24 * 60 * 60);
        }

        $place == null ? : $data['holdcity'] = $place;

        $type == null ?: $data['type'] = $type;

        $forum == null ?: $data['fid'] = $forum;

        $status == null ?: $data['status'] = $status;

        if ($digest == null) {
            $data['isdigest'] = 0;
        } elseif ($digest == 1) {
            $data['isdigest'] = 1;
        } else {
            $data['isdigest'] = 0;
        }

        if ($recommend == null) {
            $data['isrecommend'] = 0;
        } elseif ($recommend == 1) {
            $data['isrecommend'] = 1;
        } else {
            $data['isrecommend'] = 0;
        }

        $data['status'] = array('neq', 1);
        $data['audit']  = array('eq', 1);

        $activityModel = D('Activity');
        
        $p    = I('request.p', 1,'intval');
        $number = I('request.number',10,'intval');
        $number = ($number>50) ? 50 : $number;
        $order = I('request.order','desc');

        $activities = $activityModel->where($data)->order(array('id'=>$order))->limit($number * ($p - 1), $number)->select();
        return $activities;
    }
    //活动筛选
    public function getfilter(){
        $activities = $this->filter();
        $info = $activities;
        $code = 0;
        $message = '筛选活动';
        $return = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }
    
    //活动报名
    public function enroll()
    {
        //参加活动不需要加入工作室
        $aid = I('request.aid',null,'intval');
        $telephone = I('request.telephone',null);
        $number = I('request.number',1,'intval');
        $uid = $this->getUid();
        
        if(empty($uid)){
            $info = null;
            $code = 2;
            $message = C('no_login');
            $return = $this->buildReturn($info,$code,$message);
            $this->ajaxReturn($return);
        }

        if(empty($aid)||empty($telephone)){
            $info = null;
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
       
        $activityModel = D('Activity');
        $enrolltotal   = $activityModel->getFieldById($aid, 'enrolltotal');
        $enrollnum     = $activityModel->getFieldById($aid, 'enrollnum');
        
        if (!empty($enrolltotal) && ($enrollnum+$number>$enrolltotal)) {
            $info = null;
            $code = 1;
            $message = C('PARTICIPATE_ACTIVITY_OVERFLOW');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }    
            
        $activityEnrollModel = D('ActivityEnroll');
        $exist            = $activityEnrollModel->where(array('aid' => $aid, 'uid' => $uid, 'status'=>0))->find();
        if ($exist) {
            $info = null;
            $code = 1;
            $message = '已经报名过';
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        } 

        $bool = $activityEnrollModel->where(array('aid'=>$aid,'uid'=>$uid))->find();
        if($bool){
            $activityEnrollModel->where(array('aid' => $aid, 'uid' => $uid))->setField(array('telephone'=>$telephone,'status'=>0));
            $activityModel->where(array('id' => $aid))->setInc('enrollnum',$number);
            $info = null;
            $code = 0;
            $message = '报名成功';
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $activityEnrollModel->add(array('aid' => $aid, 'uid' => $uid, 'telephone'=>$telephone,'addtime'=>time()));
        $activityModel->where(array('id' => $aid))->setInc('enrollnum',$number);
        $info = null;
        $code = 0;
        $message = '报名成功';
        $return = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    //取消活动报名
    public function cancelenroll()
    {
        $uid       = $this->getUid();
        $aid       = I('request.aid',null,'intval');
        $telephone = I('request.telephone',null);

        if(empty($uid)){
            $info = null;
            $code = 2;
            $message = C('no_login');
            $return = $this->buildReturn($info,$code,$message);
            $this->ajaxReturn($return);
        }

        if(empty($aid)||empty($telephone)){
            $info = null;
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $activityEnrollModel = D('ActivityEnroll');
        $activityModel = D('Activity');
        $activity            = $activityEnrollModel->where(array('uid' => $uid, 'aid' => $aid,'status'=>0))->find();
        if ($activity) {
            $number = $activity['ticketnum'];
            $activityEnrollModel->where(array('uid' => $uid, 'aid' => $aid))->save(array('status' => 1,'telephone'=>$telephone));
            $activityModel->where(array('id' => $aid))->setDec('enrollnum',$number);
            $info = null;
            $code = 0;
            $message = '取消报名成功';
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $bool = $activityEnrollModel->where(array('id' => $aid,'uid'=>$uid))->find();

        $info = null;
        $code = 1;
        $message = '你没有报名活动';
        $return = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    //查看报名信息TODO:
    public function watchEnroll()
    {
        $aid                 = I('request.aid', null, 'intval');
        $activityEnrollModel = D('ActivityEnroll');

        $enrollInfo = $activityEnrollModel->where(array('aid' => $aid, 'status' => 0))->select();
        $this->assign('aid', $aid);
        $this->assign('enrollInfo', $enrollInfo);

        $content = $this->fetch('enroll_before_activity_manage');
        $this->ajaxReturn(array('status' => 1, 'content' => $content));
    }

    //发送活动信息弹出
    public function sendActivityNotice()
    {
        $aid = I('post.data', null, 'intval');
        $this->assign('aid', $aid);

        $content = $this->fetch('send_notice_activity');
        $this->ajaxReturn(array('status' => 1, 'content' => $content));
    }

    //发送活动消息确定
    public function sendActivityNoticeDo()
    {
        $content             = I('post.content', null);
        $aid                 = I('post.aid');
        $activityenrollModel = D('ActivityEnroll');
        $messageModel        = D('Message');

        $enrollInfo = $activityenrollModel->where(array('aid' => $aid, 'status' => 0))->select();

        foreach ($enrollInfo as $key => $val) {
            $fid             = $this->getFidByAid($aid);
            $data['fid']     = $fid;
            $data['touid']   = $enrollInfo[$key]['uid'];
            $data['aid']     = $aid;
            $data['content'] = $content;
            $data['addtime'] = time();
            $data['uid']     = $this->getUid();
            $data['subject'] = '活动消息';
            $messageModel->add($data);
        }
        $this->ajaxReturn(array('status' => 0, 'content' => '发送通知成功'));

    }

    //活动点赞
    public function favorDo()
    {
        $aid                = I('request.aid', null, 'intval');
        $val                = I('request.val', null);
        $fid                = $this->getFidByAid($val);
        $uid                = $this->getUid();
        $activityModel      = D('Activity');
        $forumUserModel     = D('ForumUser');
        $activityFavorModel = D('ActivityFavor');

        if (!$this->checkLogin()) {
            $this->ajaxReturn(array('status' => 0, 'message' => C('NO_LOGIN')));
        } else {
            $bool = $activityFavorModel->checkHasFavor($aid, $uid);
            if ($bool) {
                $result = $activityFavorModel->checkIsFavor($aid, $uid);
                if ($result) {
                    $activityFavorModel->where(array('aid' => $aid, 'uid' => $uid))->setField(array('status' => 0));
                    $activityModel->where(array('id' => $aid))->setDec('favors', 1);
                    $forumUserModel->where(array('fid' => $fid, 'uid' => $uid))->setDec('favors', 1);
                    $this->ajaxReturn(array('status' => 1, 'content' => C('ACTIVITY_CANCEL_FAVOR_SUCCESS')));
                } else {
                    //给用户添加积分
                    //                    $userPointTypeModel = D("UserPointType");
                    //                    $userPointTypeData =  $userPointTypeModel->getDataByPointType(2);
                    //                    $pointController = new PointController();
                    //                    $result = $pointController->favorPoint($aid);
                    $activityFavorModel->where(array('aid' => $aid, 'uid' => $uid))->setField(array('status' => 1));
                    $activityModel->where(array('id' => $aid))->setInc('favors', 1);

                    $forumUserModel->where(array('fid' => $fid, 'uid' => $uid))->setInc('favors', 1);
                    $this->ajaxReturn(array('status' => 1, 'content' => C('ACTIVITY_FAVOR_SUCCESS'), 'result' => $result));
                }

            } else {
                $result = $activityFavorModel->add(array('aid' => $aid, 'uid' => $uid));

                if ($result) {
                    //给用户添加积分
                    //                    $pointController = new PointController();
                    //                    $point = $pointController->favorPoint($aid);
                    //统计处理
                    $activityFavorModel->where(array('id' => $aid))->setInc('favors', 1);
                    $forumUserModel->where(array('fid' => $fid, 'uid' => $uid))->setInc('favors', 1);
                    $this->ajaxReturn(array('status' => 1, 'content' => C('ACTIVITY_FAVOR_SUCCESS')));
                } else {
                    $this->ajaxReturn(array('status' => 2, 'content' => C('ACTIVITY_FAVOR_FAILED')));
                }

            }

        }
    }

    //活动审核
    public function auditdetail()
    {
        $aid = I('request.aid', null, 'intval');
        $this->assignDetailInfo($aid);
        $this->display('auditdetail');
    }

    //获取活动

    //分配活动详情页相关信息
    public function assignDetailInfo($aid)
    {
        //获取话题所属星吧信息
        $uid = $this->getUid();

        $activityModel = D('Activity');
        $activity_info = $activityModel->getActivityInfoById($aid);
        $this->assign('activity', $activity_info);
        $fid = $activity_info['fid'];

        $forumModel = D('Forum');
        $forum_info = $forumModel->getForumInfoById($fid);
        $this->assign('forum', $forum_info);

        $hot_activities = $this->getHotActivities($fid);
        $this->assign('hot_activities', $hot_activities);

        $hot_topic = $this->getHotTopics($fid);
        $this->assign('hot_topics', $hot_topic);

        $forumUserModel = D('ForumUser');
        $admin_info     = $forumUserModel->getAdminUserInfo($fid);
        $this->assign('forum_admin', $admin_info);

        $activityResponseModel     = D('ActivityResponse');
        $activityResponseUserModel = D('ActivityResponseUser');

        //获取活动报名者和创建者的回复
        $holdstart = $activityModel->getFieldById($aid, 'holdstart');

        //获取回复信息
        $activity_responses       = $activityResponseModel->where(array('aid' => $aid, 'status' => 0))->order(array('addtime' => 'asc'))->select();
        $activity_responses_count = count($activity_responses);
        $basetime                 = $activity_responses[$activity_responses_count - 1]['addtime'];

        //处理回复中时间间隔显示
        for ($i = $activity_responses_count - 1; $i >= 0; $i--) {
            $time                             = $activity_responses[$i]['addtime'];
            $time_info                        = showDate($basetime, $time);
            $topic_responses[$i]['show_date'] = $time_info['return_time'];
            $basetime                         = $time_info['basetime'];
        }

        $this->assign('activity_responses', $activity_responses);
    }

    //分配活动直播框相关信息
    public function assignLiveInfo($aid)
    {
    }

    //活动收藏/取消收藏
    public function collectActivity()
    {
        $aid = I('request.aid', null, 'intval');
        $val = I('request.val', null, 'intval'); //0表示收藏;1表示取消收藏
        if (!$this->checkLogin()) {
            $this->ajaxReturn(array('status' => 0, 'info' => C('NO_LOGIN')));
        } else {
            $activityCollectModel = D('ActivityCollect');
            $uid                  = $this->getUid();

            if ($val == 0) {
                $result = $activityCollectModel->collectActivity($aid, $uid);
                if ($result) {
                    $this->ajaxReturn(array('status' => 1, 'info' => C('COLLECT_ACTIVITY_SUCCESS')));
                } else {
                    $this->ajaxReturn(array('status' => 2, 'info' => C('COLLECT_ACTIVITY_FAILED')));
                }
            } else {
                $result = $activityCollectModel->uncollectActivity($aid, $uid);
                if ($result) {
                    $this->ajaxReturn(array('status' => 1, 'info' => C('UN_COLLECT_ACTIVITY_SUCCESS')));
                } else {
                    $this->ajaxReturn(array('status' => 2, 'info' => C('UN_COLLECT_ACTIVITY_FAILED')));
                }
            }

        }
    }

    //活动删除
    public function deleteActivity()
    {
        $aid = I('request.data', null);
        $this->assign('aid', $aid);
        $content = $this->fetch('delete_activity');
        $this->ajaxReturn(array('status' => 1, 'content' => $content));
    }
    //草稿删除
    public function deleteStrash()
    {
        $aid = I('request.data', null);
        $this->assign('aid', $aid);
        $content = $this->fetch('delete_cao_gao');
        $this->ajaxReturn(array('status' => 1, 'content' => $content));
    }
    //活动删除确定
    public function deleteActivityDo()
    {
        if ($this->checkPrivilege('isadmin')) {
            $aid                       = I('post.aid', null, 'intval');
            $admin_uid                 = $this->getUid();
            $activityModel             = D('Activity');
            $forumUserModel            = D('ForumUser');
            $forumModel                = D('Forum');
            $userbehaviorModel         = D('UserBehavior');
            $forumadminstatisticsModel = D('ForumAdminStatistics');
            $messageModel              = D('Message');
            $uid                       = $activityModel->getFieldById($aid, 'uid');
            $fid                       = $this->getFidByAid($aid);
            $content                   = I('post.content', null);

            //统计记录
            $forumModel->where(array('id' => $fid))->setDec('activities');
            $activityModel->where(array('id' => $aid))->setField(array('status' => 1));

            $forumUserModel->where(array('uid' => $uid, 'fid' => $fid))->setInc('deleteactivitynum');
            $userbehaviorModel->where(array('uid' => $uid))->setInc('activitydelnum');
            $forumUserModel->where(array('uid' => $uid, 'fid' => $fid))->setDec('createactivitynum');
            $userbehaviorModel->where(array('uid' => $uid))->setDec('activitycreatenum');

            //日志记录
            $data['uid']     = $admin_uid;
            $data['fid']     = $fid;
            $data['addtime'] = time();
            $data['type']    = 1;
            $data['operate'] = '删除活动，活动id:' . $aid;
            $forumadminstatisticsModel->add($data);

            //消息
            $activity_subject            = $activityModel->getFieldById($aid, 'subject');
            $message_data['fid']         = $fid;
            $message_data['uid']         = $admin_uid;
            $message_data['touid']       = $uid;
            $message_data['username']    = getUserNicknameById($admin_uid);
            $message_data['tousername']  = getUserNicknameById($uid);
            $message_data['isinvolve']   = 1;
            $message_data['involveid']   = $aid;
            $message_data['involvetype'] = 2;
            $message_data['subject']     = '活动删除信息';
            $message_data['content']     = "《" . $activity_subject . "》" . "被经纪人(" . getUserNicknameById($admin_uid) . ")删除了";
            $message_data['reason']      = $content;
            $message_data['iscomplaint'] = 1;
            $message_data['addtime']     = time();
            $messageModel->add($message_data);

            $href = U('Forum/index', array('fid' => $fid));

            $this->ajaxReturn(array('status' => 1, 'content' => C('DELETE_ACTIVITY_SUCCESS'), 'href' => $href));

        } else {
            $aid           = I('post.aid', null, 'intval');
            $activityModel = D('Activity');
            $activity_uid  = $activityModel->where(array('id' => $aid))->getFieldById($aid, 'uid');
            $uid           = $this->getUid();
            if ($uid == $activity_uid) {
                $activityModel             = D('Activity');
                $forumUserModel            = D('ForumUser');
                $forumModel                = D('Forum');
                $userbehaviorModel         = D('UserBehavior');
                $forumadminstatisticsModel = D('ForumAdminStatistics');
                $messageModel              = D('Message');
                $uid                       = $activityModel->getFieldById($aid, 'uid');
                $fid                       = $this->getFidByAid($aid);
                $content                   = I('post.content', null);

                //统计记录
                $forumModel->where(array('id' => $fid))->setDec('activities');
                $activityModel->where(array('id' => $aid))->setField(array('status' => 1));

                $forumUserModel->where(array('uid' => $uid, 'fid' => $fid))->setInc('deleteactivitynum');
                $userbehaviorModel->where(array('uid' => $uid))->setInc('activitydelnum');
                $forumUserModel->where(array('uid' => $uid, 'fid' => $fid))->setDec('createactivitynum');
                $userbehaviorModel->where(array('uid' => $uid))->setDec('activitycreatenum');

                //消息(TODO:报名者发送信息)
                //                $activity_subject = $activityModel->getFieldById($aid,'subject');
                //                $message_data['fid'] = $fid;
                //                $message_data['uid'] = $uid;
                //                $message_data['touid'] = $uid;
                //                $message_data['isinvolve'] = 1;
                //                $message_data['involveid'] = $aid;
                //                $message_data['involvetype'] = 2;
                //                $message_data['subject'] = '活动删除信息';
                //                $message_data['content'] = "《".$activity_subject."》". "被经纪人(".getUserNicknameById($admin_uid).")删除了";
                //                $message_data['reason'] = $content;
                //                $message_data['iscomplaint'] = 1;
                //                $message_data['addtime'] = time();
                //                $messageModel->add($message_data);

                $href = U('Forum/index', array('fid' => $fid));

                $this->ajaxReturn(array('status' => 1, 'content' => C('DELETE_ACTIVITY_SUCCESS'), 'href' => $href));
            } else {
                $this->ajaxReturn(array('status' => 0, 'content' => C('NO_AUTH')));
            }

        }
    }

    //活动草稿删除
    public function deleteActivityTrashDo()
    {
        $aid           = I('post.aid', null, 'intval');
        $uid           = $this->getUid();
        $activityModel = D('Activity');
        $activityModel->where(array('uid' => $uid, 'id' => $aid))->setField(array('status' => 1));
        $this->ajaxReturn(array('status' => 1, 'content' => '删除草稿成功'));
    }

    //加精活动处理
    public function digestActivityDo()
    {
        $aid               = I('request.aid', null, 'intval');
        $uid               = $this->getUid();
        $userBehaviorModel = D('UserBehavior');
        $activityModel     = D('Activity');
        $forumUserModel    = D('ForumUser');

        if ($this->checkPrivilege('isadmin')) {
            $fid   = $activityModel->getFieldById($aid, 'fid');
            $type  = $activityModel->getFieldById($aid, 'isdigest');
            $count = $activityModel->where(array('fid' => $fid, 'isdigest' => 1, 'status' => array('neq', 1)))->count();
            if ($count >= C('ACTIVITY_DIGEST_NUM') && ($type == 0)) {
                $this->ajaxReturn(array('status' => 2, 'content' => C('ACTIVITY_DIGEST_OVERFLOW')));
            } else {
                if ($type == 1) {
                    $activityModel->where(array('id' => $aid))->setField(array('isdigest' => 0));
                    $userBehaviorModel->where(array('uid' => $uid))->setDec('activitydigestnum');
                    $forumUserModel->where(array('fid' => $fid, 'uid' => $uid))->setDec('digestactivitynum');

                    $this->ajaxReturn(array('status' => 2, 'content' => C('ACTIVITY_UNDIGEST_SUCCESS')));
                } else {
                    $activityModel->where(array('id' => $aid))->setField(array('isdigest' => 1));
                    $userBehaviorModel->where(array('uid' => $uid))->setInc('activitydigestnum');
                    $forumUserModel->where(array('fid' => $fid, 'uid' => $uid))->setInc('digestactivitynum');
//                    $point = R('Point/activityPlus',array($aid));
                    //                    if($point){
                    //                        R('Point/addUserTotalPoint',array($point));
                    //                    }
                    $this->ajaxReturn(array('status' => 1, 'content' => C('ACTIVITY_DIGEST_SUCCESS')));
                }
            }

        } else {
            $this->ajaxReturn(array('status' => 0, 'content' => C('NO_AUTH')));
        }
    }

    //举报活动
    public function reportActivity()
    {
        $aid = I('request.data', null);
        $this->assign('aid', $aid);
        $content = $this->fetch('report_activity');
        if ($this->checkLogin()) {
            $this->ajaxReturn(array('status' => 1, 'content' => $content));
        } else {
            $this->ajaxReturn(array('status' => 0, 'content' => ''));
        }

    }

    //活动举报处理
    public function reportActivityDo()
    {
        $activityModel = D('Activity');
        $aid           = I('post.aid', null, 'intval');
        $fid           = $activityModel->getFieldById($aid, 'fid');
        $touid         = $activityModel->getFieldById($aid, 'uid');
        if (!$this->checkLogin()) {
            $this->ajaxReturn(array('status' => 0, 'message' => C('NO_LOGIN')));
        } else {
            $uid = $this->getUid();

            $data['reason']    = I('post.reason', null);
            $data['uid']       = $uid;
            $data['fid']       = $fid;
            $data['touid']     = $touid;
            $data['involveid'] = $aid;
            $data['content']   = I('post.content', null);
            $data['type']      = 3;
            $data['addtime']   = time();

            $forumReportModel = D('ForumReport');

            $result = $forumReportModel->add($data);

            if ($result) {
                $this->ajaxReturn(array('status' => 1, 'message' => C('ACTIVITY_REPORT_SUCCESS')));
            } else {
                $this->ajaxReturn(array('status' => 2, 'message' => C('ACTIVITY_REPORT_FAILED')));
            }
        }
    }

    /**
     * 更新未读信息变成已读
     */
    public function updateRead()
    {
        $id  = I('post.id', null);
        $uid = session('uid');
        if (!empty($id)) {
            $activityResponseModel = D('ActivityResponse');
            $activityResponseModel->updateUnreadMessageByIdUid($id, $uid);
            $message = $activityResponseModel->getLatestUnreadMessageByUidAid($uid, $id);
            if ($message) {
                $this->ajaxReturn(array('status' => 1, 'info' => $message, 'message' => C('UPDATE_UNREAD_MESSAGE_SUCCESS')));
            } else {
                $this->ajaxReturn(array('status' => 0, 'info' => $message, 'message' => C('UPDATE_UNREAD_MESSAGE_FAILED')));
            }
        }
    }

    /**
     * 批量更新未读信息为已读
     * $ids like "2,3,4,5,6"
     */
    public function updateReadAll()
    {
        $id                    = I('post.id', null);
        $uid                   = session('uid');
        $activityResponseModel = D('ActivityResponse');
        $result                = $activityResponseModel->updateUnreadMessageByUidAid($uid, $id);

        if ($result) {
            $this->ajaxReturn(array('status' => 1, 'message' => C('UPDATE_UNREAD_MESSAGE_SUCCESS')));
        } else {
            $this->ajaxReturn(array('status' => 0, 'message' => C('UPDATE_UNREAD_MESSAGE_FAILED')));
        }
    }

    public function ajaxResponse()
    {
        $uid = $this->getUid();
        if (!$this->login) {
            $return['status'] = false;
            $this->ajaxReturn($return);
        } else {
            $data['fid']    = I('post.fid', null, 'intval');
            $data['aid']    = I('post.aid', null, 'intval');
            $data['uid']    = $uid;
            $data['islive'] = I('post.islive', 0, 'intval');
            $content        = I('post.content', null, '');
            preg_match_all("#@[^ |@]*#", $content, $match);

            $data['content']  = $content;
            $data['addtime']  = time();
            $userModel        = D("User");
            $data['username'] = $userModel->getFieldById($uid, 'nickname');

            $activityModel             = D('Activity');
            $activityResponseModel     = D('ActivityResponse');
            $activityresponseUserModel = D('ActivityResponseUser');
            $activityEnrollModel       = D('ActivityEnroll');
            $activityCollecctModel     = D('ActivityCollect');
            $id                        = $activityResponseModel->add($data);

            $activityModel->where(array('id' => $data['aid']))->setInc('hot');

            //消息处理create,encroll,collect,participate
            $activityModel->where(array('id' => $data['aid']))->setField(array('hasresponseid' => $id));
            $activityEnrollModel->where(array('aid' => $data['aid'], 'uid' => $uid))->setField(array('hasresponseid' => $id));
            $activityCollecctModel->where(array('aid' => $data['aid']))->setField(array('hasresponseid' => $id));
            $activityResponseModel->where(array('aid' => $data['aid']))->setField(array('hasresponseid' => $id));

            if (!empty($match[0])) {
                foreach ($match[0] as $key => $val) {
                    $username = str_replace('@', '', $val);
                    $touid    = $userModel->getUidByNickname($username);
                    if (!empty($touid) && $id) {
                        $user_data['response_id'] = $id;
                        $user_data['uid']         = $touid;
                        $activityresponseUserModel->add($user_data);
                    }
                }
            }

            if ($id) {
                //给用户添加积分
                //                $userPointTypeModel = D("UserPointType");
                //                $userPointTypeData =  $userPointTypeModel->getDataByPointType(1);
                //                $pointController = new PointController();
                //                $result = $pointController->publishActivityRestore($data['aid']);
                $this->ajaxReturn(array('status' => true));
            } else {
                $this->ajaxReturn(array('status' => false));
            }
        }

    }

    //非直播状态回复信息获取
    public function getLiveActivityInfo()
    {

        $aid           = I('post.aid', null, 'intval');
        $uid           = $this->getUid();
        $lastid        = I('post.lastid', null, 'intval');
        $activityModel = D('Activity');
        $activity      = $activityModel->getActivityInfoById($aid);
        $this->assign('activity', $activity);
        $ActivityResponseModel     = D('ActivityResponse');
        $ActivityResponseUserModel = D('ActivityResponseUser');
        $responses                 = $ActivityResponseModel->getInfoByLastId($aid, $lastid);

        if (!empty($responses)) {
            //获取回复信息
            foreach ($responses as $k => $v) {
                $responses[$k]['nickname']       = getUserNicknameById($v['uid']);
                $responses[$k]['photo']          = getUserPhotoById($v['uid']);
                $responses[$k]['format_addtime'] = $this->formatResponseTime($v['addtime']);
            }
            $this->assign('responses', $responses);

            //获取未读信息
            $unreadmessage                = $ActivityResponseUserModel->getUnreadMessageByUidAid($uid, $aid);
            $unreadmessage_count          = count($unreadmessage);
            $lastunreadmessage            = $unreadmessage[0];
            $lastunreadmessage['content'] = strip_tags($lastunreadmessage['content']);
            $this->assign('unreadmessage_count', $unreadmessage_count);
            $this->assign('lastunreadmessage', $lastunreadmessage);

            $unreadcontent = "";
            if ($unreadmessage_count != 0) {
                $unreadcontent = $this->fetch('activity_unreadInfo');
            }

            $content = $this->fetch('Activity/activitylive');
            $this->ajaxReturn(array('status' => true, 'info' => $content, 'unreadinfo' => $unreadcontent));
        } else {
            $this->ajaxReturn(array('status' => false));
        }
    }

    //直播时创建者报名者信息获取
    public function getLiveAuthorActivityInfo()
    {
        $aid                       = I('post.aid', null, 'intval');
        $lastid                    = I('post.lastid', null, 'intval');
        $activityModel             = D('Activity');
        $activityResponseModel     = D('ActivityResponse');
        $activityResponseUserModel = D('ActivityResponseUser');

        //获取活动报名者和创建者的回复
        $holdstart = $activityModel->getFieldById($aid, 'holdstart');
        $uid       = $activityModel->getFieldById($aid, 'uid');

        $activityEnrollModel = D('ActivityEnroll');
        $uids                = $activityEnrollModel->field('uid')->where(array('aid' => $aid, 'status' => 0))->select();
        $uids_arr            = array();
        foreach ($uids as $key => $val) {
            array_push($uids_arr, $val['uid']);
        }
        array_push($uids_arr, $uid);

        $condition['uid']     = array('in', $uids_arr);
        $condition['aid']     = $aid;
        $condition['islive']  = 1;
        $condition['status']  = 0;
        $condition['addtime'] = array('gt', $holdstart);
        $condition['id']      = array('gt', $lastid);

        $responses = $activityResponseModel->where($condition)->order(array('addtime' => 'asc'))->select();

        foreach ($responses as $k => $v) {
            $responses[$k]['nickname']       = getUserNicknameById($v['uid']);
            $responses[$k]['photo']          = getUserPhotoById($v['uid']);
            $responses[$k]['format_addtime'] = $this->formatResponseTime($v['addtime']);
        }
        $this->assign('responses', $responses);

        //获取未读信息
        $unreadmessage                = $activityResponseUserModel->getUnreadMessageByUidAid($uid, $aid);
        $unreadmessage_count          = count($unreadmessage);
        $lastunreadmessage            = $unreadmessage[0];
        $lastunreadmessage['content'] = strip_tags($lastunreadmessage['content']);

        $this->assign('unreadmessage_count', $unreadmessage_count);

        $content = $this->fetch('Activity/activitylive_author');
        if (empty($responses)) {
            $this->ajaxReturn(array('status' => false, 'content' => 'null'));
        } else {
            $this->ajaxReturn(array('status' => true, 'content' => $content));
        }
    }
    //直播时参与者信息获取(包括创建者发布的讨论)
    public function getLiveParticipateActivityInfo()
    {
        $aid                       = I('post.aid', null, 'intval');
        $lastid                    = I('post.lastid', null, 'intval');
        $activityModel             = D('Activity');
        $activityResponseModel     = D('ActivityResponse');
        $activityResponseUserModel = D('ActivityResponseUser');

        //获取活动报名者和创建者的回复
        $holdstart   = $activityModel->getFieldById($aid, 'holdstart');
        $uid         = $activityModel->getFieldById($aid, 'uid');
        $session_uid = session('uid');

        $activityEnrollModel = D('ActivityEnroll');
        $uids                = $activityEnrollModel->field('uid')->where(array('aid' => $aid, 'status' => 0))->select();
        $uids_arr            = array();
        foreach ($uids as $key => $val) {
            array_push($uids_arr, $val['uid']);
        }
        array_push($uids_arr, $uid);

        $condition['islive']  = 0;
        $condition['aid']     = $aid;
        $condition['status']  = 0;
        $condition['addtime'] = array('gt', $holdstart);
        $condition['id']      = array('gt', $lastid);
        $responses            = $activityResponseModel->where($condition)->order(array('addtime' => 'asc'))->select();
        foreach ($responses as $k => $v) {
            $responses[$k]['nickname']       = getUserNicknameById($v['uid']);
            $responses[$k]['photo']          = getUserPhotoById($v['uid']);
            $responses[$k]['format_addtime'] = $this->formatResponseTime($v['addtime']);
        }
        $this->assign('responses', $responses);
        //获取未读信息
        $unreadmessage = $activityResponseUserModel->getUnreadMessageByUidAid($session_uid, $aid);

        $unreadmessage_count          = count($unreadmessage);
        $lastunreadmessage            = $unreadmessage[0];
        $lastunreadmessage['content'] = strip_tags($lastunreadmessage['content']);

        $unreadcontent = "";
        $this->assign('lastunreadmessage', $lastunreadmessage);
        $this->assign('unreadmessage_count', $unreadmessage_count);

        if ($unreadmessage_count != 0) {
            $unreadcontent = $this->fetch('activitylive_participate_unreadInfo');
        }

        $content = $this->fetch('Activity/activitylive_participate');
        if (empty($responses)) {
            $this->ajaxReturn(array('status' => false, 'content' => 'null'));
        } else {
            $this->ajaxReturn(array('status' => true, 'content' => $content, 'unreadinfo' => $unreadcontent));
        }
    }

    /**
     * 设置推荐,将推荐信息插入推荐列表页
     */
    public function recommendDo()
    {
        if (!$this->checkPrivilege('isadmin')) {
            $this->ajaxReturn(array('status' => 0, 'content' => C('NO_AUTH')));
        } else {
            $aid                    = I('request.aid');
            $data['aid']            = I('request.aid');
            $data['isrecommend']    = 0;
            $data['status']         = 0;
            $data['fid']            = $this->getFidByAid($data['aid']);
            $activityRecommendModel = D('ActivityRecommend');
            if ($activityRecommendModel->checkExist($data)) {
                //推荐存在
                $this->ajaxReturn(array('status' => 2, 'content' => C('ACTIVITY_RECOMMEND_EXIST')));
            } else {
                //推荐不存在

                //推荐数量超过限制数量
                if ($activityRecommendModel->checkExistNum(array('fid' => $data['fid'], 'isrecommend' => 0, 'status' => 0))) {

                    //被收录查看之前是否存在该活动
                    $activity = $activityRecommendModel->where(array('aid' => $data['aid']))->find();
                    if ($activity) {
                        $activityRecommendModel->where(array('id' => $activity['id']))->setField(array('isrecommend' => 1, 'status' => 0));
                    } else {
                        $data['isrecommend'] = 1;
                        $data['addtime']     = time();
                        $activityRecommendModel->add($data);
                    }
                    $content = $this->fetch('Activity/recommend_collect_activity');
                    $this->ajaxReturn(array('status' => 3, 'content' => $content));

                } else {
                    //设置推荐
                    $activity = $activityRecommendModel->where(array('aid' => $data['aid']))->find();

                    if ($activity) {
                        $activityRecommendModel->where(array('id' => $activity['id']))->setField(array('isrecommend' => 0, 'status' => 0));
                    } else {
                        $data['isrecommend'] = 0;
                        $data['addtime']     = time();
                        $activityRecommendModel->add($data);
                    }
                    $act            = M('Activity');
                    $array['id']    = $aid;
                    $model          = $act->where($array)->find();
                    $forumUserModel = D('ForumUser');
                    $userBehavior   = D('UserBehavior');
                    $forumUserModel->where(array('fid' => $model['fid'], 'uid' => $model['uid']))->setInc('recommendactivitynum');
                    $userBehavior->where(array('uid' => $model['uid']))->setInc('activityapplynum');
                    $this->ajaxReturn(array('status' => 1, 'content' => C('ACTIVITY_RECOMMEND_SUCCESS')));
                }
            }

        }

    }

    /**
     * @param $time
     * @return bool|string
     * 格式化日期
     */
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

    //取消活动
    public function cancelActivity()
    {
        $aid           = I('post.data', null);
        $activityModel = D('ActivityModel');
        $holdstart     = $activityModel->where(array('id' => $aid))->getFieldById($aid, 'holdstart');
        $content       = $this->fetch('cancel_activity');
        $this->assign('aid', $aid);
        $now = time();

        if ($now + 3600 * 24 > $holdstart) {
            $this->ajaxReturn(array('status' => 0, 'content' => 'CANCEL_ACIVITY_NOT_ALLOW'));
        } else {
            $this->ajaxReturn(array('status' => 1, 'content' => $content));
        }

    }

    //取消活动处理
    public function cancelActivityDo()
    {
        $aid    = I('request.aid', null, 'intval');
        $fid    = $this->getFidByAid($aid);
        $reason = I('request.reason');
        $login  = $this->login;
        if (!$login) {
            $this->ajaxReturn(array('status' => 0, 'content' => C('NO_LOGIN')));
        } else {
            $uid                 = $this->getUid();
            $activityModel       = D('ActivityModel');
            $activityCancel      = D('ActivityCancel');
            $activityEnrollModel = D('ActivityEnroll');
            $forumUserModel      = D('ForumUser');
            $messageModel        = D('Message');

            try {
                //统计记录
                $enroll_info = $activityEnrollModel->where(array('aid' => $aid, 'status' => 0))->select();
                foreach ($enroll_info as $key => $val) {
                    $data['uid']         = $this->getUid();
                    $data['fid']         = $fid;
                    $data['involiveid']  = $aid;
                    $data['isinvolve']   = 1;
                    $data['involvetype'] = 2;
                    $data['touid']       = $val['uid'];
                    $data['subject']     = C('COMMON_MESSAGE_ACTIVITY_CANCEL');
                    $data['content']     = $reason;
                    $data['addtime']     = time();
                    $messageModel->add($data);
                }

                $activity_uid = $activityModel->where(array('id' => $aid))->getFieldById($aid, 'uid');
                if ($activity_uid == $uid) {
                    $activityCancel->add(array('aid' => $aid, 'fid' => $fid, 'addtime' => time(), 'reason' => $reason));
                    $activityModel->where(array('id' => $aid))->setField(array('status' => 6));
                    $forumUserModel->where(array('fid' => $fid, 'uid' => $data['uid']))->setInc('cancelactivitynum'); //取消也算是活动总数
                    $this->ajaxReturn(array('status' => 1, 'content' => C('CANCEL_ACTIVITY_SUCCESS')));
                } else {
                    $this->ajaxReturn(array('status' => 2, 'content' => C('NO_AUTH')));
                }

            } catch (Exception $e) {
                \Think\Log::write($e->getMessage());
                $this->ajaxReturn(array('status' => 3, 'content' => C('CANCEL_ACTIVITY_ENROLL_FAILED')));

            }

        }
    }

    //活动存草稿
    public function saveActivityDraft()
    {
        //没有草稿自动添加,
        //图片上传后会自动保存
        $fid = I('post.fid');
        $uid = $this->getUid();
        if (!$this->checkLogin()) {
            $this->ajaxReturn(array('status' => 0, 'content' => C('NO_LOGIN')));
            die();
        }
        if (!checkIsInForum($uid, $fid)) {
            $this->ajaxReturn(array('status' => 0, 'content' => C('NO_AUTH_ACCESS')));
            die();
        }

        $data            = $this->getPostData();
        $data['istrash'] = 1;

        $activityTagModel = D('ActivityTag');
        $tags             = I('post.tags');
        $tags_arr         = explode(',', $tags);
        $tag_ids          = "";
        foreach ($tags_arr as $key => $val) {
            if (!empty($val)) {
                //不存在添加，存在+1
                $result = $activityTagModel->where(array('tagname' => $val))->find();

                if ($result) {
                    $activityTagModel->where(array('id' => $result['id']))->setInc('count');
                    $tag_ids .= $result['id'] . ',';
                } else {
                    $id = $activityTagModel->add(array('tagname' => $val));
                    $tag_ids .= $id . ',';
                }
            }
        }
        $data['tag'] = $tag_ids;

        $activityModel = D('Activity');

        $activity = $activityModel->where(array('fid' => $fid, 'uid' => $uid, 'istrash' => 1))->find();
        if (empty($activity)) {
            $activityModel->add($data);
        } else {
            $activityModel->where(array('id' => $activity['id']))->save($data);
        }

        $this->ajaxReturn(array('status' => 1, 'content' => C('ACTIVITY_SAVEDRAFT_SUCCESS')));

    }

    //图片上传
    //缩略预览图上传
    public function uploadImg()
    {
        $upload           = new \Think\Upload();
        $upload->maxSize  = C('ACTIVITY_UPLOAD_SIZE');
        $upload->exts     = C('ACTIVITY_UPLOAD_EXT');
        $upload->savePath = C('UPLOAD_TEMP_PATH');
        $width            = C('ACTIVITY_IMG_WIDHT');
        $height           = C('ACTIVITY_IMG_HEIGHT');
        $info             = $upload->upload();
        if (!$info) {
            $this->ajaxReturn(json_encode(array('status' => 0, 'content' => '图片的大小超过2M')), 'EVAL');
        } else {
            //找到文件的路径
            $img_add = './Uploads/' . $info['file']['savepath'] . $info['file']['savename'];
            $image   = new \Think\Image();
            $image->open($img_add);
            if ($image->width() < $width) {
                $this->ajaxReturn(json_encode(array('status' => 0, 'content' => '图片宽度小于' . $width)), 'EVAL');
            } else if ($image->height() < $height) {
                $this->ajaxReturn(json_encode(array('status' => 0, 'content' => '图片高度小于' . $height)), 'EVAL');
            } else {
                $name          = substr($info['file']['savename'], 0, -4);
                $thumb_add     = './Uploads/temp/' . $name . '_thumb.' . $info['file']['ext'];
                $thumb_img     = $image->thumb(C('ACTIVITY_UPLOAD_WIDTH'), C('ACTIVITY_UPLOAD_HEIGHT'))->save($thumb_add);
                $img_width     = $thumb_img->width();
                $img_height    = $thumb_img->height();
                $thumb_add_img = '/Uploads/temp/' . $name . '_thumb.' . $info['file']['ext'];
                $this->assign('thumb_add_img', $thumb_add_img);
                $this->assign('img_width', $img_width);
                $this->assign('img_height', $img_height);
                $this->assign('primaryimg', $img_add);
                $content = $this->fetch('upload_img');
                $this->ajaxReturn($content, 'EVAL');

            }

        }
    }
    //从前端发送ajax请求，参数分别是x,y,w,h和所要截图的路径，截图完成后返回截图的地址，
    //将图片追加到显示框
    public function produceAvatar()
    {
        $attachmentModel = D('Attachment');
        $x               = I('x', 270);
        $y               = I('y', 270);
        $w               = I('w', 0);
        $h               = I('h', 0);

        $primaryimg = I('primary', null, '');
        $uid        = session('uid');
        $avatar     = I('avatar', null, '');
        if ($avatar == '') {
            $this->error(C('UPLOAD_AVATAR_ERROR'));
        }
        $save_avatar_add = time();
        //用户上传海报需要利用原图生成两张缩略图(现在改为直接使用原图，后台审核时如果设为推荐，工作人员在原图上进行修改)用于首页推荐活动的展示
        $data_thumb_1['filename'] = '首页活动小海报';
        $data_thumb_1['path']     = $primaryimg;
        $data_thumb_1['isimage']  = 1;
        $data_thumb_1['uid']      = $uid;

        $data_thumb_2['filename'] = '首页活动大海报';
        $data_thumb_2['path']     = $primaryimg;
        $data_thumb_2['isimage']  = 1;
        $data_thumb_2['uid']      = $uid;

        //原图
        $data_source['filename'] = '活动原图';
        $data_source['path']     = $primaryimg;
        $data_source['isimage']  = 1;
        $data_source['uid']      = $uid;

        $thumb_id1 = $attachmentModel->add($data_thumb_1);
        $thumb_id2 = $attachmentModel->add($data_thumb_2);
        $source_id = $attachmentModel->add($data_source);

        $avatar = '.' . $avatar;
        $image  = new \Think\Image();
        $image->open($avatar);

        $save_avatar_activity = '/Uploads/Activity/Img/' . $save_avatar_add . '.jpg';

        $save_avatar_add = './Uploads/Activity/Img/' . $save_avatar_add . '.jpg';

        $data['filename'] = '活动海报';
        $data['path']     = $save_avatar_add;
        $data['isimage']  = 1;
        $data['uid']      = $uid;

        $id = $attachmentModel->add($data);

        $image->crop($w, $h, $x, $y)->save($save_avatar_add);

        $width  = $image->width();
        $height = $image->height();

        $activity_img = array(
            'img_url'   => $save_avatar_activity,
            'width'     => $width,
            'height'    => $height,
            'id'        => $id,
            'thumb_id1' => $thumb_id1,
            'thumb_id2' => $thumb_id2,
            'source_id' => $source_id,
        );
        $this->ajaxReturn($activity_img, 'json');

    }

    //获取创建活动url
    public function getCreateActivity()
    {
        $fid = I('post.fid');
        $uid = $this->getUid();
        if (checkIsInForum($uid, $fid)) {
            if (checkUserBan($uid, $fid)) {
                $forumBanUserModel = D('ForumBanUser');
                $ban_info          = $forumBanUserModel->where(array('fid' => $fid, 'uid' => $uid))->find();
                $this->assign('ban_info', $ban_info);
                $content = $this->fetch('ban_activity_message');
                $this->ajaxReturn(array('status' => 0, 'content' => $content));
            } else {
                $this->ajaxReturn(array('status' => 1, "content", "href" => U('Activity/createActivity', array('fid' => $fid))));
            }
        } else {
            $this->ajaxReturn(array('status' => 2, 'content' => C('FORUM_FOLLOW_FIRST')));
        }
    }

    //线上活动投票
    public function submitVote()
    {
        $aid           = I('request.aid', null, 'intval');
        $ismultiselect = I('request.ismultiselect', null, 'intval');
        $choices       = I('request.choices', null);
        $uid           = $this->getUid();

        if (empty($uid)) {
            $info    = null;
            $code    = 2;
            $message = C('no_login');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        if (empty($aid) || empty($ismultiselect) || empty($choices)) {
            $info    = null;
            $code    = -1;
            $message = C('parameter_invalid');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxResponse($return);
        }

        $activityOnlineModel = D('ActivityOnline');
        $activityModel       = D('Activity');
        $choices             = trim($choices, ',');
        $answer              = $activityModel->where(array('id' => $aid))->getField('answer');
        $question            = $activityModel->where(array('id' => $aid))->getField('question');
        $info                = $activityOnlineModel->where(array('uid' => $uid, 'type' => 1, 'aid' => $aid, 'status' => 0))->find();
        $arr                 = array('uid' => $uid, 'addtime' => time(), 'type' => 1, 'ismultiselect' => $ismultiselect, 'choices' => $choices, 'aid' => $aid);
        $answer              = (array) json_decode($answer);

        if (empty($info)) {
            $activityOnlineModel->add($arr);
            $question    = json_decode($question);
            $choices_arr = explode(',', $choices);

            foreach ($question as $key => $val) {
                if (in_array($val, $choices_arr)) {
                    $answer[$val] = $answer[$val] + 1;
                }
            }
            $answer = json_encode($answer);
            $activityModel->where(array('id' => $aid))->setField(array('answer' => $answer));
        }
        $this->ajaxReturn(array('status' => 0, 'info' => '投票成功'));

    }

    /**
     * 创建活动，显示创建活动页面
     */
    public function createActivity()
    {
        if (!$this->checkLogin()) {
            $this->redirect('Index/index');
        }
        $fid = $this->getFid();
        if (empty($fid)) {
            $this->redirect('Index/index');
        }
        C('TOKEN_ON', true);
        $districtModel = D('District');
        $provinces     = $districtModel->getAllProvince(0);
        $this->assign('provinces', $provinces);

        $default_province = empty($current_province) ? 1 : $current_province;
        $cities           = $districtModel->getCityByProvince($default_province);
        $this->assign('cities', $cities);

        $activityTypeModel = D('ActivityType');
        $activity_types    = $activityTypeModel->order('id asc')->select();
        $this->assign('activity_types', $activity_types);
        $this->display('create_activity');
    }

    //草稿编辑页面
    public function editActivity()
    {
        if (!$this->checkLogin()) {
            $this->redirect('Index/index');
        }

        $aid           = I('request.aid', null, 'intval');
        $uid           = $this->getUid();
        $activityModel = D('Activity');
        $activity      = $activityModel->where(array('uid' => $uid, 'id' => $aid))->find();
        if ($activity) {
            C('TOKEN_ON', true);
            $districtModel = D('District');
            $provinces     = $districtModel->getAllProvince(0);
            $this->assign('provinces', $provinces);

            $default_province = empty($activity['holdprovince']) ? 1 : $activity['holdprovince'];
            $cities           = $districtModel->getCityByProvince($default_province);
            $this->assign('cities', $cities);

            //dump($activity);

            $activityTagModel = D('ActivityTag');
            $tags             = $activity['tag'];
            $tags_arr         = explode(',', $tags);
            $tag_str          = "";
            $tags_str_arr     = array();
            foreach ($tags_arr as $key => $val) {
                if (!empty($val)) {
                    //不存在添加，存在+1
                    $result = $activityTagModel->where(array('id' => $val))->find();

                    $activityTagModel->where(array('id' => $result['id']))->setInc('count');
                    $tag_str .= $result['tagname'] . ',';
                    $tags_str_arr[] = $result['tagname'];

                }
            }
            $this->assign('tags_str_arr', $tags_str_arr);
            $activity['tags'] = $tag_str;

            $activityTypeModel = D('ActivityType');
            $activity_types    = $activityTypeModel->select();
            $this->assign('activity_types', $activity_types);
            $this->assign('activity', $activity);
            $this->display('edit_activity');
        } else {
            $this->display('Public/not_found');
            die;
        }

    }
    //编辑活动公告
    public function editNoticeDo()
    {
        $id            = I('post.aid', null, 'intval');
        $activityModel = D('Activity');
        $activity_uid  = $activityModel->getFieldById($id, 'uid');
        $uid           = $this->getUid();
        if ($activity_uid == $uid) {
            $notice = I('post.notice');
            $activityModel->where(array('id' => $id))->setField(array('notice' => $notice));
            $this->ajaxReturn(array('status' => 1, 'info' => '修改成功'));
        } else {
            $this->ajaxReturn(array('status' => 0, 'info' => C('NO_AUTH')));
        }
    }

    //创建活动
    public function createActivityDo()
    {
        if (!$this->checkLogin()) {
            $this->error(C('NO_AUTH_ACCESS'));
        }
        $userforums = $this->getUserForum($this->getUid());

        $fid = I('post.fid', null, 'intval');

        if (!in_array($fid, $userforums)) {
            $this->error(C('NO_AUTH_ACCESS'));
        }

        $activityModel = new ActivityModel();

        $subject        = I('post.subject', null, '');
        $content        = I('post.content', null, '');
        $subject_length = strlen($subject);
        $content_length = strlen($content);
        if ($subject_length < 5) {
            $this->error('活动标题不能小于5个字');
        }
        if ($subject_length > 40) {
            $this->error('活动标题不能大于40个字');
        }
        if ($content_length == 0) {
            $this->error('活动内容不能为空');
        }

        //特殊字段 img tag
        $data           = $this->getPostData();
        $forumUserModel = D('ForumUser');
        $forumModel     = D("Forum");
        $forumModel->where(array('id' => $fid))->setInc('activities');

        $forumUserModel->where(array('fid' => $fid, 'uid' => $this->getUid()))->setInc('createactivitynum');
        $userBehaviorModel = D('UserBehavior');
        $userBehaviorModel->where(array('uid' => $this->getUid()))->setInc('activitycreatenum');
        $activityTagModel = D('ActivityTag');
        $tags             = I('post.tags');
        $tags_arr         = explode(',', $tags);
        $tag_ids          = "";
        foreach ($tags_arr as $key => $val) {
            if (!empty($val)) {
                //不存在添加，存在+1
                $result = $activityTagModel->where(array('tagname' => $val))->find();

                if ($result) {
                    $activityTagModel->where(array('id' => $result['id']))->setInc('count');
                    $tag_ids .= $result['id'] . ',';
                } else {
                    $id = $activityTagModel->add(array('tagname' => $val));
                    $tag_ids .= $id . ',';
                }
            }
        }
        $data['tag'] = $tag_ids;
        $data['img'] = I('post.img');

        /* if(empty($data['img'])){
        //如果用户没有上传活动海报图片，我们将yj_attachment的200-xxx字段作为默认活动海报图片
        switch(I('type')){

        case 1:
        //粉丝见面会
        $data['sourceimg'] = 352; //默认活动海报的原图
        $data['img'] = 353; //默认活动海报的显示图
        $data['indexsmallimg'] = 354; //默认活动海报首页显示的推荐小图
        $data['indexbigimg'] = 355; //默认活动海报首页显示的推荐大图图
        break;

        case 2:
        //粉丝见面会
        $data['sourceimg'] = 356; //默认活动海报的原图
        $data['img'] = 357; //默认活动海报的显示图
        $data['indexsmallimg'] = 358; //默认活动海报首页显示的推荐小图
        $data['indexbigimg'] = 359; //默认活动海报首页显示的推荐大图图
        break;

        case 3:
        //粉丝见面会
        $data['sourceimg'] = 360; //默认活动海报的原图
        $data['img'] = 361; //默认活动海报的显示图
        $data['indexsmallimg'] = 362; //默认活动海报首页显示的推荐小图
        $data['indexbigimg'] = 363; //默认活动海报首页显示的推荐大图图
        break;

        case 4:
        //粉丝见面会
        $data['sourceimg'] = 364; //默认活动海报的原图
        $data['img'] = 365; //默认活动海报的显示图
        $data['indexsmallimg'] = 366; //默认活动海报首页显示的推荐小图
        $data['indexbigimg'] = 367; //默认活动海报首页显示的推荐大图图
        break;

        case 5:
        //粉丝见面会
        $data['sourceimg'] = 368; //默认活动海报的原图
        $data['img'] = 369; //默认活动海报的显示图
        $data['indexsmallimg'] = 370; //默认活动海报首页显示的推荐小图
        $data['indexbigimg'] = 371; //默认活动海报首页显示的推荐大图图
        break;

        case 6:
        //粉丝见面会
        $data['sourceimg'] = 372; //默认活动海报的原图
        $data['img'] = 373; //默认活动海报的显示图
        $data['indexsmallimg'] = 374; //默认活动海报首页显示的推荐小图
        $data['indexbigimg'] = 375; //默认活动海报首页显示的推荐大图图
        break;
        }

        }else{
        $data['sourceimg'] = I('post.source_id');
        $data['indexsmallimg'] = I('post.thumb_img_1');
        $data['indexbigimg'] = I('post.thumb_img_2');
         */

        $data['sourceimg']     = I('post.source_id');
        $data['indexsmallimg'] = I('post.thumb_img_1');
        $data['indexbigimg']   = I('post.thumb_img_2');
        //'ACTIVITY_UPLOAD_WIDTH'=>1280,
        //'ACTIVITY_UPLOAD_HEIGHT'=>768,

        if (!$activityModel->create($data)) {
            $this->error($activityModel->getError());
        } else {
            if ($aid = $activityModel->add($data)) {
//                $count=R('Point/createActivity',array($aid));
                //                if($count){
                //                   R('Point/addUserTotalPoint',array($count));
                //                }
                //$this->ajaxReturn(array('status'=>1,'href'=>U('Activity/detail',array('aid'=>$aid)),'point'=>$count));
                $this->redirect('Activity/detail', array('aid' => $aid));
            } else {
                $this->error($activityModel->getError());
            }
        }
    }

    //获取创建活动数据
    public function getPostData()
    {
        $holdprovince              = I('post.holdprovince', null, 'intval');
        $holdcity                  = I('post.holdcity', null, 'intval');
        $detailaddress             = I('post.detailaddress');
        $holdstart_date            = I('post.holdstart_date');
        $holdstart_time            = I('post.holdstart_time');
        $holdend_date              = I('post.holdend_date');
        $holdend_time              = I('post.holdend_time');
        $enrollstart_date          = I('post.enrollstart_date', null);
        $enrollstart_time          = I('post.enrollstart_time', null);
        $enrollend_date            = I('post.enrollend_date', null);
        $enrollend_time            = I('post.enrollend_time', null);
        $subject                   = I('post.subject');
        $notice                    = I('post.notice');
        $data['holdstart']         = strtotime($holdstart_date . $holdstart_time);
        $data['holdend']           = strtotime($holdend_date . $holdend_time);
        $data['enrollstartime']    = strtotime($enrollstart_date . $enrollstart_time);
        $data['enrollendtime']     = strtotime($enrollend_date . $enrollend_time);
        $data['uid']               = session('uid');
        $data['fid']               = I('post.fid', null, 'intval');
        $data['holdprovince']      = $holdprovince;
        $data['holdcity']          = $holdcity;
        $data['type']              = I('post.type');
        $data['subject']           = $subject;
        $data['notice']            = $notice;
        $data['participatemethod'] = I('post.participatemethod');
        $data['content']           = I('post.content');
        $data['addtime']           = time();
        $data['detailaddress']     = $detailaddress;
        $data['enrolltotal']       = I('post.enrolltotal');

        return $data;
    }

    //封装线上活动投票问题
    public function onlineWrapQuestion($id)
    {
        $activityModel = D('Activity');

        $question_img     = $activityModel->where(array('id' => $id))->getField('question_img');
        $question_img     = trim($question_img, ',');
        $question_img_arr = explode(',', $question_img);

        $question  = $activityModel->where(array('id' => $id))->getField('question');
        $questions = json_decode($question);

        $wrap_questions = array();
        foreach ($questions as $key => $val) {
            $wrap_questions[$key]['text']  = $val;
            $wrap_questions[$key]['image'] = is_null(getAttachmentUrlById($question_img_arr[$key])) ? '/Public/default/img/online_huodong/vote_default_img.png' : getAttachmentUrlById($question_img_arr[$key]);
        }

        return $wrap_questions;
    }

    public function onlineWrapResults($id)
    {
        $activityModel = D('Activity');
        $answer        = $activityModel->where(array('id' => $id))->getField('answer');
        $question_text        = $activityModel->where(array('id' => $id))->getField('question');
        $results       = array();

        $question_img     = $activityModel->where(array('id' => $id))->getField('question_img');
        $question_img     = trim($question_img, ',');
        $question_img_arr = explode(',', $question_img);
        
        $answer_arr = (array) json_decode($answer);
        //dump($answer_arr);
        $question_text_arr = (array) json_decode($question_text);
        $total  = 0;

        foreach ($answer_arr as $key => $val) {
            $total = $total + $val;
        }

        $i = 0;

        foreach ($answer_arr as $key => $val) {
            $results[$key]['value']   = $val;
            $results[$key]['text'] = $question_text_arr[$i];
            $image_url = getAttachmentUrlById($question_img_arr[$i]);
            $default_image_url = '/Public/default/img/online_huodong/vote_default_img.png';
            $results[$key]['image'] = is_null($image_url) ? $default_image_url : $image_url;
            $results[$key]['percent'] = intval($val * 100 / $total);
            $i++;
        }
        return $results;

    }

    //判断投票问题是否有上传图片问题
    public function checkIsPostQuestionImg($img)
    {
        $img     = trim($img, ',');
        $img_arr = explode(',', $img);
        foreach ($img_arr as $val) {
            if ($val != 0) {
                return true;
            }
        }
        return false;
    }
}

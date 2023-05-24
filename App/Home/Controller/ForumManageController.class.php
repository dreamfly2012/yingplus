<?php

namespace Home\Controller;

class ForumManageController extends CommonController{
    public function __construct(){
        parent::__construct();
        
        if($this->checkLogin()){
            $forumUserModel = D('ForumUser');
            $uid = session('uid');
            $fid = session('admin_fid');

            $forum_access = $forumUserModel->getAccessInfo($fid,$uid);
            session('user_access',$forum_access );
            if(!$this->checkPrivilege('isadmin')){
                $this->error(C('NO_AUTH'));
            }
        }else{

            $this->showLogin();
        }

    }

    //活动创建审核处理
    public function auditActivity(){
        $activitiyModel = D('Activity');
        $aid = I('post.data');
        $activity = $activitiyModel->where(array('id'=>$aid))->find();
        $this->assign('activity',$activity);
        $this->updateActivityStatus($activity['id']);
        $content = $this->fetch('audit_create_activity');
        $this->ajaxReturn(array('status'=>1,'content'=>$content));
    }

    //活动创建审核确认
    public function auditActivityDo(){
        $activityModel = D('Activity');
        $forumAdminStatisticsModel = D('ForumAdminStatistics');
        $aid = I('request.aid');
        $fid = $this->getFid();
        $type = I('request.type');
        $content = I('request.content',null);



        if($type=='yes'){
            $type='1';
            $activityModel->where(array('id'=>$aid))->setField(array('audit'=>$type));
            
            //经纪人统计信息
            $dist['fid'] = $fid;
            $dist['uid'] = $this->getUid();
            $dist['type'] = 1;
            $dist['addtime'] = time();
            $dist['operate'] = '审核活动';
            $forumAdminStatisticsModel->add($dist);
            $activityUid = $activityModel->getFieldById($aid,'uid');
            ///dump($activityUid);
            $point=R('Point/activityHasPass',array($aid=>$aid,$uid=>$activityUid));
            if($point){
                   R('Point/addUserTotalPoint',array($point=>$point,$uid=>$activityUid)); 
            }
           // dump($point);
            $this->ajaxReturn(array('status'=>1,'content'=>'审核成功','point'=>$point,'activityUid'=>$activityUid));

        }elseif($type=='no'){
            $this->assign('aid',$aid);
            $content = $this->fetch('audit_create_activity_reject');
            $this->ajaxReturn(array('status'=>2,'content'=>$content));
        }elseif($type=='confirm-no'){
            $type='2';
            //发送消息
            $messageModel = D('Message');
            $data['fid'] = $fid;
            $data['uid'] = $this->getUid();
            $data['subject'] = C('COMMON_MESSAGE_ACTIVITY_AUDIT_REJECT');
            $data['content'] = $content;
            $data['touid'] = $activityModel->getFieldById($aid,'uid');
            $data['isinvolve'] = 1;
            $data['involvetype'] = 2;
            $data['involiveid'] = $aid;
            $data['username'] = getUserNicknameById($data['uid']);
            $data['tousername'] = getUserNicknameById($data['touid']);
            $data['addtime'] = time();
            $data['iscomplaint'] = 1;
            $messageModel->add($data);

            //统计信息
            $dist['fid'] = $fid;
            $dist['uid'] = $this->getUid();
            $dist['type'] = 1;
            $dist['addtime'] = time();
            $dist['operate'] = '审核活动';
            $forumAdminStatisticsModel->add($dist);
            
            $activityModel->where(array('id'=>$aid))->setField(array('audit'=>$type));            
            $this->ajaxReturn(array('status'=>3,'content'=>'审核成功'));
        }
    }

    //活动收录删除
    public function activityRecommendDelete(){
        $aid = I('post.aid',null,'intval');
        $activityRecommendModel = D('ActivityRecommend');
        if($this->checkPrivilege('isadmin')){
            $activityRecommendModel->where(array('aid'=>$aid))->setField(array('status'=>1));
            $this->ajaxReturn(array('status'=>1,'content'=>C('ACTIVITY_RECOMMEND_CANCEL_COLLECT_SUCCESS')));
        }else{
            $this->ajaxReturn(array('status'=>0,'content'=>C('NO_AUTH')));
        }

    }

    //活动推荐取消
    public function activityRecommendCancel(){
        $activityRecommendModel = D('ActivityRecommend');
        $aid = I('post.aid',null,'intval');
        $act = M('Activity');
        $array['id'] = $aid;
        $model = $act->where($array)->find();
        $forumUserModel = D('ForumUser');
        $userBehavior = D('UserBehavior');
        $forumUserModel->where(array('fid'=>$model['fid'],'uid'=>$model['uid']))->setDec('recommendactivitynum');
        $userBehavior->where(array('uid'=>$model['uid']))->setDec('activityapplynum');
        $activityRecommendModel->where(array('aid'=>$aid))->setField(array('isrecommend'=>1,'status'=>0));
        $this->ajaxReturn(array('status'=>1,'content'=>C('ACTIVITY_RECOMMEND_CANCEL_SUCCESS')));
    }

    //活动推荐确认
    public function activityRecommendDo(){
        $activityRecommendModel = D('ActivityRecommend');
        $aid = I('post.aid',null,'intval');
        $fid = $this->getFidByAid($aid);
        $count = $activityRecommendModel->where(array('fid'=>$fid,'isrecommend'=>0))->count();
        if($count>=C('ACTIVITY_RECOMMEND_NUM')){
            $this->ajaxReturn(array('status'=>0,'content'=>C('ACTIVITY_RECOMMEND_OVERFLOW')));
        }else{
            $act = M('Activity');
            $array['id'] = $aid;
            $model = $act->where($array)->find();
            $forumUserModel = D('ForumUser');
            $userBehavior = D('UserBehavior');
            $forumUserModel->where(array('fid'=>$model['fid'],'uid'=>$model['uid']))->setInc('recommendactivitynum');
            $userBehavior->where(array('uid'=>$model['uid']))->setInc('activityapplynum');
            $activityRecommendModel->where(array('aid'=>$aid))->setField(array('isrecommed'=>0,'status'=>1));
            $this->ajaxReturn(array('status'=>1,'content'=>C('ACTIVITY_RECOMMEND_SUCCESS')));
        }

    }

    //活动举报审核处理
    public function auditReportActivity(){
        $activityModel = D('Activity');
        $activityResponseModel = D('ActivityResponse');
        $forumReportModel = D('ForumReport');
        $fr_id = I('post.data');
        $report_uid = $forumReportModel->getFieldById($fr_id,'uid');
        $aid = $forumReportModel->getFieldById($fr_id,'involveid');
        $activity = $activityModel->where(array('id'=>$aid))->find();
        $responses = $activityResponseModel->where(array('aid'=>$activity['id']))->select();
        $this->assign('responses',$responses);
        $this->assign('activity',$activity);
        $this->assign('report_uid',$report_uid);
        $this->assign('report_id',$fr_id);
        $content = $this->fetch('audit_report_activity');
        $this->ajaxReturn(array('status'=>1,'content'=>$content));
    }


    //活动举报审核选择
    public function auditReportActivitySelect(){
        $type = I('post.type');
        $aid = I('post.aid');
        $report_id = I('post.report_id');
        $this->assign('type',$type);
        $this->assign('aid',$aid);
        $this->assign('report_id',$report_id);
        $content = $this->fetch('audit_report_activity_reason');
        $this->ajaxReturn(array('status'=>1,'content'=>$content));
    }

    //活动举报审核确认
    public function auditReportActivityDo(){
        $activityModel = D('Activity');
        $forumReportModel = D('ForumReport');
        $messageModel = D('Message');
        $report_id = I('post.report_id');
        $delete = I('post.delete',null);
        $type = I('post.type');
        $aid = I('post.aid',null,'intval');
        $fid = $this->getFidByAid($aid);
        $content = I('post.reason');
        $uid = $this->getUid();
        $touid = $activityModel->getFieldById($aid,'uid');
        $report_uid = $forumReportModel->getFieldById($report_id,'uid');


        $data['fid'] = $fid;
        $data['uid'] = $uid;
        $data['touid'] = $touid;
        $data['involiveid'] = $aid;
        $data['involvetype'] = 1;
        $data['isinvolve'] = 1;
        $data['username'] = getUserNicknameById($uid);
        $data['tousername'] = getUserNicknameById($touid);
        $data['addtime'] = time();
        $data['content'] = $content;


        //发送消息
        if($type=='yes'){
            if($delete){
                //删除话题,发送内容给话题创建者
                $activityModel->where(array('id'=>$aid))->setField(array('status'=>1,'admin_id'=>$uid,'deleteTime'=>date('Y-m-d H:i:s',time())));
                $data['subject'] = '删除活动信息';
                $data['iscomplaint'] = 0;
                $messageModel->add($data);
            }
            //发送信息给举报者,覆盖消息
            $data['subject'] = '举报审核信息';
            $data['content'] = '您举报的内容已被处理';
            $data['touid'] = $report_uid;
            $data['tousername'] = getUserNicknameById($report_uid);
            $messageModel->add($data);
        }else{
            //发送给举报者
            $data['subject'] = '举报审核信息';
            $data['content'] = '您举报的内容不符';
            $data['iscomplaint'] = 0;
            $data['touid'] = $report_uid;
            $data['tousername'] = getUserNicknameById($report_uid);
            $messageModel->add($data);
        }


        $forumReportModel->where(array('id'=>$report_id))->setField(array('isshenhe'=>0));

        //统计信息
        $forumAdminStatistics = D('ForumAdminStatistics');
        $forumAdminStatistics->add(array('fid'=>$fid,'uid'=>$uid,'type'=>3,'addtime'=>time(),'operate'=>'处理活动举报'));

        if($type=='yes'){
            $this->ajaxReturn(array('status'=>1,'content'=>'审核成功','rid'=>$report_id));
        }else{
            $this->ajaxReturn(array('status'=>0,'content'=>'审核成功','rid'=>$report_id));
        }
    }

    //话题举报审核处理
    public function auditReportTopic(){
        $topicModel = D('Topic');
        $topicResponseModel = D('TopicResponse');
        $forumReportModel = D('ForumReport');
        $fr_id = I('post.data');
        $report_uid = $forumReportModel->getFieldById($fr_id,'uid');
        $tid = $forumReportModel->getFieldById($fr_id,'involveid');
        $topic = $topicModel->where(array('id'=>$tid))->find();
        $responses = $topicResponseModel->where(array('tid'=>$topic['id']))->select();
        $this->assign('responses',$responses);
        $this->assign('topic',$topic);
        $this->assign('report_uid',$report_uid);
        $this->assign('report_id',$fr_id);
        $content = $this->fetch('audit_report_topic');
        $this->ajaxReturn(array('status'=>1,'content'=>$content));
    }

    //话题举报审核选择
    public function auditReportTopicSelect(){
        $type = I('post.type');
        $tid = I('post.tid');
        $report_id = I('post.report_id');
        $this->assign('type',$type);
        $this->assign('tid',$tid);
        $this->assign('report_id',$report_id);
        $content = $this->fetch('audit_report_topic_reason');
        $this->ajaxReturn(array('status'=>1,'content'=>$content));
    }

    //话题举报审核处理确认
    public function auditReportTopicDo(){
        $topicModel = D('Topic');
        $forumReportModel = D('ForumReport');
        $report_id = I('post.report_id');
        $delete = I('post.delete',null);
        $type = I('post.type');
        $tid = I('post.tid',null,'intval');
        $fid = $this->getFidByTid($tid);
        $content = I('post.reason');
        $uid = $this->getUid();

        $touid = $topicModel->getFieldById($tid,'uid');
        $report_uid = $forumReportModel->getFieldById($report_id,'uid');
        $messageModel = D('Message');

        $data['fid'] = $fid;
        $data['uid'] = $uid;
        $data['touid'] = $touid;
        $data['involiveid'] = $tid;
        $data['involvetype'] = 1;
        $data['isinvolve'] = 1;
        $data['username'] = getUserNicknameById($uid);
        $data['tousername'] = getUserNicknameById($touid);
        $data['content'] = $content;
        $data['addtime'] = time();


        //发送消息
        if($type=='yes'){
            if($delete){
                //删除话题,发送内容给话题创建者
                $topicModel->where(array('id'=>$tid))->setField(array('status'=>1,'admin_id'=>$uid,'deleteTime'=>date('Y-m-d H:i:s',time())));
                $data['subject'] = '删除话题信息';
                $data['iscomplaint'] = 0;
                $messageModel->add($data);
            }
            //发送信息给举报者,覆盖消息
            $data['subject'] = '举报审核信息';
            $data['content'] = '您举报的内容已被处理';
            $data['touid'] = $report_uid;
            $data['tousername'] = getUserNicknameById($report_uid);
            $messageModel->add($data);
        }else{
            //发送给举报者
            $data['subject'] = '举报审核信息';
            $data['content'] = '您举报的内容不符';
            $data['iscomplaint'] = 0;
            $data['touid'] = $report_uid;
            $data['tousername'] = getUserNicknameById($report_uid);
            $messageModel->add($data);
        }


        $forumReportModel->where(array('id'=>$report_id))->setField(array('isshenhe'=>0));

        //统计信息
        $forumAdminStatistics = D('ForumAdminStatistics');
        $forumAdminStatistics->add(array('fid'=>$fid,'uid'=>$uid,'type'=>3,'addtime'=>time(),'operate'=>'处理话题举报'));

        if($type=='yes'){
            $this->ajaxReturn(array('status'=>1,'content'=>'审核成功','rid'=>$report_id));
        }else{
            $this->ajaxReturn(array('status'=>0,'content'=>'审核成功','rid'=>$report_id));
        }


    }

    public function index(){
        $fid = session('admin_fid');
        $type = I('request.type','create_activity');
        switch($type){
            case 'create_activity':
                $this->activityCreateManage();
                break;
            case 'recommend_activity':
                $this->activityRecommendManage();
                break;
            case 'cancel_activity':
                $this->activityCancelledManage();
                break;
            case 'absence_activity':
                $this->activityAbsenceManage();
                break;
            case 'report_topic':
                $this->topicReportManage();
                break;
            case 'report_activity':
                $this->activityReportManage();
                break;
            case 'agent_attendance':
                $this->forumAdminAttendanceManage();
                break;
            case 'operate_help':
                $this->help();
                break;
        }

        $this->assign('category',$type);
        $this->getForumBasicInfo($fid);
        $this->display('workroom');

    }

    //获取工作室基本信息
    public function getForumBasicInfo($fid){
    	$forumModel = D('Forum');
    	$forum = $forumModel->getForumInfoById($fid);
    	$this->assign('forum',$forum);
    }


    //活动创建审核
    public function activityCreateManage(){
        $this->activityCreateManagePage();
    }

    public function activityCreateManagePage(){
        $activityModel = D('Activity');
        $fid = session('admin_fid');
        $activities_count = $activityModel->where(array('fid'=>$fid,'audit'=>0))->count();
        $Page = new \Think\AjaxPage($activities_count,C('FORUM_MANAGE_NUM'),'activity_create_page');
        $Page->setConfig('theme','%upPage% %first%  %prePage%  %linkPage%  %nextPage% %downPage% %end%');
        $activities = $activityModel->where(array('fid'=>$fid,'audit'=>0))->order(array('addtime'=>'desc'))->limit($Page->firstRow.','.$Page->listRows)->select();
        $page = $Page->show();
        $this->assign('activities',$activities);
        $this->assign('page',$page);
    }

    public function activityCreateManageList(){
        $this->activityCreateManagePage();
        $this->display('activity_create_list');
    }



    //推荐活动管理
    public function activityRecommendManage(){
        $this->activityRecommendManagePage();
    }

    public function activityRecommendManagePage(){
        $activityRecommendModel = D('ActivityRecommend');
        $fid = session('admin_fid');
        $activities_count = $activityRecommendModel->where(array('fid'=>$fid))->count();
        $Page = new \Think\AjaxPage($activities_count,C('FORUM_MANAGE_NUM'),'activity_recommend_page');
        $Page->setConfig('theme','%upPage% %first%  %prePage%  %linkPage%  %nextPage% %downPage% %end%');
        $activities = $activityRecommendModel->where(array('fid'=>$fid))->order(array('isrecommend'=>'asc','status'=>'desc'))->limit($Page->firstRow.','.$Page->listRows)->select();
        $page = $Page->show();
        $this->assign('activities',$activities);
        $this->assign('page',$page);
    }

    public function activityRecommendManageList(){
        $this->activityRecommendManagePage();
        $this->display('activity_recommend_list');
    }


    //活动取消处理
    public function activityCancelledManage(){
        $this->activityCancelledManagePage();
    }

    public function activityCancelledManagePage(){
        $nickname = I('post.nickname',null,'');
        $starttime = I('post.starttime',null,'strtotime');
        $endtime = I('post.endtime',null,'strtotime');
        $startnum = I('post.startnum',null,'intval');
        $endnum = I('post.endnum',null,'intval');
        $fid = session('admin_fid');

        $starttime = empty($starttime) ? 0 : $starttime;
        $endtime = empty($endtime) ? time() : $endtime;
        $startnum = empty($startnum) ? 0 : $startnum;
        $endnum = empty($endnum) ? 100 : $endnum;
        
        if(empty($nickname)){
            $activityCancelModel = D('ActivityCancel');

            $activities= $activityCancelModel->field('uid,count(uid) as num')->where(array('fid'=>$fid))->group('uid')->having("count('uid')>=$startnum and count('uid')<=$endnum")->order('num')->select();
            $uids  = array();
            foreach($activities as $key=>$val){
                array_push($uids,$val['uid']);
            }
            if(empty($uids)){
                $activities = null;
                $count = 0;
            }else{
                $condition['addtime'] = array('between',array($starttime,$endtime));
                $condition['uid'] = array('in',$uids);
                $condition['fid'] = array('eq',$fid);

                $activities = $activityCancelModel->where($condition)->group('uid')->select();
                $count = $activityCancelModel->where($condition)->group('uid')->count();
            }
            


            $Page   = new \Think\AjaxPage($count,C('ACTIVITY_CANCEL_PAGE_NUM'),'activity_cancel_page');
            $this->assign('activities',$activities);
            $page = $Page->show();
            $this->assign('page',$page);
        
        }else{
            $activityCancelModel = D('ActivityCancel');
            $userModel = D('User');
            $uid = $userModel->getFieldByNickname($nickname,'id');
            //$activities= $activityCancelModel->field('uid,count(uid) as num')->where(array('fid'=>$fid))->group('uid')->having("count('uid')>=$startnum and count('uid')<=$endnum")->order('num')->select();

            $condition['addtime'] = array('between',array($starttime,$endtime));
            $condition['uid'] = array('eq',$uid);
            $condition['fid'] = array('eq',$fid);
            $activities_count = $activityCancelModel->where($condition)->group('uid')->select();
            $count = $activityCancelModel->where($condition)->group('uid')->count();
            $Page   = new \Think\AjaxPage($count,C('ACTIVITY_CANCEL_PAGE_NUM'),'activity_absence_page');
            $this->assign('activities',$activities_count);
            $page = $Page->show();
            $this->assign('page',$page);
        }
    }

    public function activityCancelledManageList(){
        $this->activityCancelledManagePage();
        $this->display('activity_cancel_list');
    }

    //话题举报审核
    public function topicReportManage(){
        $this->topicReportManagePage();
    }

    public function topicReportManagePage(){
        $forumReportModel = D('ForumReport');
        $fid = session('admin_fid');
        $topics_count = $forumReportModel->where(array('fid'=>$fid,'isshenhe'=>1,'type'=>2))->count();
        $Page = new \Think\AjaxPage($topics_count,C('TOPIC_REPORT_PAGE_NUM'),'topic_report_page');
        $reports = $forumReportModel->where(array('fid'=>$fid,'isshenhe'=>1,'type'=>2))->order(array('addtime'=>'desc'))->limit($Page->firstRow.','.$Page->listRows)->select();
        $page = $Page->show();
        $this->assign('reports',$reports);
        $this->assign('page',$page);
    }

    public function topicReportManageList(){
        $this->topicReportManagePage();
        $this->display('topic_report_list');
    }


    //活动举报审核
    public function activityReportManage(){
        $this->activityReportManagePage();
    }

    public function activityReportManagePage()
    {
        $forumReportModel = D("ForumReport");
        $fid = session('admin_fid');
        $activities_count = $forumReportModel->where(array('fid' => $fid, 'isshenhe' => 1, 'type' => 3))->count();
        $Page = new \Think\AjaxPage($activities_count, C('ACTIVITY_REPORT_PAGE_NUM'), 'activity_report_page');
        $reports = $forumReportModel->where(array('fid' => $fid, 'isshenhe' => 1, 'type' => 3))->order(array('addtime' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $page = $Page->show();
        $this->assign('reports', $reports);
        $this->assign('page', $page);
    }

    public function activityReportMangageList(){
        $this->activityReportManagePage();
        $this->display('activity_report_list');
    }


    //粉丝缺席活动管理
    public function activityAbsenceManage(){
        $this->activityAbsenceManagePage();
    }


    public function activityAbsenceManagePage(){
        $nickname = I('post.nickname',null,'');
        $starttime = I('post.starttime',null,'strtotime');
        $endtime = I('post.endtime',null,'strtotime');
        $startnum = I('post.startnum',null,'intval');
        $endnum = I('post.endnum',null,'intval');
        $fid = session('admin_fid');

        $starttime = empty($starttime) ? 0 : $starttime;
        $endtime = empty($endtime) ? time() : $endtime;
        $startnum = empty($startnum) ? 0 : $startnum;
        $endnum = empty($endnum) ? 100 : $endnum;
        
        if(empty($nickname)){
            $activityAbsenceModel = D('ActivityAbsence');

            $activities= $activityAbsenceModel->field('uid,count(uid) as num')->where(array('fid'=>$fid))->group('uid')->having("count('uid')>=$startnum and count('uid')<=$endnum")->order('num')->select();
            $uids  = array();
            foreach($activities as $key=>$val){
                array_push($uids,$val['uid']);
            }
            
            $Page   = new \Think\AjaxPage($count,C('ACTIVITY_ABSENCE_PAGE_NUM'),'activity_absence_page');
            $condition['addtime'] = array('between',array($starttime,$endtime));
            $condition['uid'] = array('in',$uids);
            $condition['fid'] = array('eq',$fid);
            $activities = $activityAbsenceModel->where($condition)->group('uid')->select();
            $this->assign('activities',$activities);
            $page = $Page->show();
            $this->assign('page',$page);
        
        }else{
            $activityAbsenceModel = D('ActivityAbsence');
            $userModel = D('User');
            $uid = $userModel->getFieldByNickname($nickname,'id');
            $activities= $activityAbsenceModel->field('uid,count(uid) as num')->where(array('fid'=>$fid))->group('uid')->having("count('uid')>=$startnum and count('uid')<=$endnum")->order('num')->select();
            $Page   = new \Think\AjaxPage($count,C('ACTIVITY_ABSENCE_PAGE_NUM'),'activity_absence_page');
            $condition['addtime'] = array('between',array($starttime,$endtime));
            $condition['uid'] = array('eq',$uid);
            $condition['fid'] = array('eq',$fid);
            $activities = $activityAbsenceModel->where($condition)->group('uid')->select();
            $this->assign('activities',$activities);
            $page = $Page->show();
            $this->assign('page',$page);
        }
    }

    public function activityAbsenceManageList(){
        $this->activityAbsenceManagePage();
        $this->display('activity_absence_list');
    }

    public function forumAdminAttendanceManage(){
        $forumUserModel = D('ForumUser');
        $forumAdminStatisticsModel = D('ForumAdminStatistics');
        $fid = session('admin_fid');
        $forumadmin = $forumUserModel->getAdminUserInfo($fid);
        $this->assign('forumadmin',$forumadmin);

        $lingtime = strtotime('today');

        $map_today_time['addtime'] = array(array('gt',$lingtime),array('lt',$lingtime+24*60*60));
        $today_info = $forumAdminStatisticsModel->field('fid,type,addtime,sum(type)as sum')->where($map_today_time)->where(array('fid'=>$fid))->group('type')->select();

        $topic_num = 0;
        $activity_num = 0;
        $fensi_num = 0;
        $report_num = 0;
        $complain_num = 0;

        foreach($today_info as $key=>$val){
            if($val['type']==0){
                $topic_num = $val['sum'];
            }elseif($val['type']==1){
                $activity_num = $val['sum'];
            }elseif($val['type']==2){
                $fensi_num = $val['sum'];
            }elseif($val['type']==3){
                $report_num = $val['sum'];
            }elseif($val['type']==4){
                $complain_num = $val['sum'];
            }
        }

        $this->assign('today_topic_num',$topic_num);
        $this->assign('today_activity_num',$activity_num);
        $this->assign('today_fensi_num',$fensi_num);
        $this->assign('today_report_num',$report_num);
        $this->assign('today_complain_num',$complain_num);


        $map_seven_time['addtime'] = array(array('gt',$lingtime-6*24*60*60),array('lt',$lingtime+24*60*60));
        $seven_info = $forumAdminStatisticsModel->field('fid,type,addtime,sum(type)as sum')->where($map_seven_time)->where(array('fid'=>$fid))->group('type')->select();

        foreach($seven_info as $key=>$val){
            if($val['type']==0){
                $topic_num = $val['sum'];
            }elseif($val['type']==1){
                $activity_num = $val['sum'];
            }elseif($val['type']==2){
                $fensi_num = $val['sum'];
            }elseif($val['type']==3){
                $report_num = $val['sum'];
            }elseif($val['type']==4){
                $complain_num = $val['sum'];
            }
        }

        $this->assign('seven_topic_num',$topic_num);
        $this->assign('seven_activity_num',$activity_num);
        $this->assign('seven_fensi_num',$fensi_num);
        $this->assign('seven_report_num',$report_num);
        $this->assign('seven_complain_num',$complain_num);


        $map_seven_time['addtime'] = array(array('gt',$lingtime-29*24*60*60),array('lt',$lingtime+24*60*60));
        $thirty_info = $forumAdminStatisticsModel->field('fid,type,addtime,sum(type)as sum')->where($map_seven_time)->where(array('fid'=>$fid))->group('type')->select();

        foreach($thirty_info as $key=>$val){
            if($val['type']==0){
                $topic_num = $val['sum'];
            }elseif($val['type']==1){
                $activity_num = $val['sum'];
            }elseif($val['type']==2){
                $fensi_num = $val['sum'];
            }elseif($val['type']==3){
                $report_num = $val['sum'];
            }elseif($val['type']==4){
                $complain_num = $val['sum'];
            }
        }

        $this->assign('thirty_topic_num',$topic_num);
        $this->assign('thirty_activity_num',$activity_num);
        $this->assign('thirty_fensi_num',$fensi_num);
        $this->assign('thirty_report_num',$report_num);
        $this->assign('thirty_complain_num',$complain_num);

        $this->attendance_detail_select();


    }

    public function attendance_detail_select(){
        $topic_num = 0;
        $activity_num = 0;
        $fensi_num = 0;
        $report_num = 0;
        $complain_num = 0;

        $forumAdminStatisticsModel = D('ForumAdminStatistics');
        $fid = session('admin_fid');
        $lingtime = strtotime('today');

        $uid = I('request.uid',null);
        $forumUserModel = D('ForumUser');
        $forumadmin = $forumUserModel->getAdminUserInfo($fid);

        if(is_null($uid)){
            $uid = $forumadmin[0]['uid'];
        }

        $admin_name = getUserNicknameById($uid);
        $this->assign('admin_name',$admin_name);





        $map_1_time['addtime'] = array(array('gt',$lingtime),array('lt',$lingtime+24*60*60));
        $day_1_info = $forumAdminStatisticsModel->field('fid,type,sum(type)as sum')->where($map_1_time)->where(array('fid'=>$fid,'uid'=>$uid))->group('type')->select();
        foreach($day_1_info as $key=>$val){
            if($val['type']==0){
                $topic_num = $val['sum'];
            }elseif($val['type']==1){
                $activity_num = $val['sum'];
            }elseif($val['type']==2){
                $fensi_num = $val['sum'];
            }elseif($val['type']==3){
                $report_num = $val['sum'];
            }elseif($val['type']==4){
                $complain_num = $val['sum'];
            }
        }
        $this->assign('person_one_time',$lingtime);
        $this->assign('person_one_topic_num',$topic_num);
        $this->assign('person_one_activity_num',$activity_num);
        $this->assign('person_one_fensi_num',$fensi_num);
        $this->assign('person_one_report_num',$report_num);
        $this->assign('person_one_complain_num',$complain_num);

        $topic_num = 0;
        $activity_num = 0;
        $fensi_num = 0;
        $report_num = 0;
        $complain_num = 0;

        $map_2_time['addtime'] = array(array('gt',$lingtime-24*60*60),array('lt',$lingtime));
        $day_2_info = $forumAdminStatisticsModel->field('fid,type,sum(type)as sum')->where($map_2_time)->where(array('fid'=>$fid,'uid'=>$uid))->group('type')->select();
        foreach($day_2_info as $key=>$val){
            if($val['type']==0){
                $topic_num = $val['sum'];
            }elseif($val['type']==1){
                $activity_num = $val['sum'];
            }elseif($val['type']==2){
                $fensi_num = $val['sum'];
            }elseif($val['type']==3){
                $report_num = $val['sum'];
            }elseif($val['type']==4){
                $complain_num = $val['sum'];
            }
        }
        $this->assign('person_two_time',$lingtime-24*60*60);
        $this->assign('person_two_topic_num',$topic_num);
        $this->assign('person_two_activity_num',$activity_num);
        $this->assign('person_two_fensi_num',$fensi_num);
        $this->assign('person_two_report_num',$report_num);
        $this->assign('person_two_complain_num',$complain_num);

        $topic_num = 0;
        $activity_num = 0;
        $fensi_num = 0;
        $report_num = 0;
        $complain_num = 0;

        $map_3_time['addtime'] = array(array('gt',$lingtime-2*24*60*60),array('lt',$lingtime-24*60*60));
        $day_3_info = $forumAdminStatisticsModel->field('fid,type,sum(type)as sum')->where($map_3_time)->where(array('fid'=>$fid,'uid'=>$uid))->group('type')->select();
        foreach($day_3_info as $key=>$val){
            if($val['type']==0){
                $topic_num = $val['sum'];
            }elseif($val['type']==1){
                $activity_num = $val['sum'];
            }elseif($val['type']==2){
                $fensi_num = $val['sum'];
            }elseif($val['type']==3){
                $report_num = $val['sum'];
            }elseif($val['type']==4){
                $complain_num = $val['sum'];
            }
        }
        $this->assign('person_three_time',$lingtime-2*24*60*60);
        $this->assign('person_three_topic_num',$topic_num);
        $this->assign('person_three_activity_num',$activity_num);
        $this->assign('person_three_fensi_num',$fensi_num);
        $this->assign('person_three_report_num',$report_num);
        $this->assign('person_three_complain_num',$complain_num);

        $topic_num = 0;
        $activity_num = 0;
        $fensi_num = 0;
        $report_num = 0;
        $complain_num = 0;

        $map_4_time['addtime'] = array(array('gt',$lingtime-3*24*60*60),array('lt',$lingtime-2*24*60*60));
        $day_4_info = $forumAdminStatisticsModel->field('fid,type,sum(type)as sum')->where($map_4_time)->where(array('fid'=>$fid,'uid'=>$uid))->group('type')->select();
        foreach($day_4_info as $key=>$val){
            if($val['type']==0){
                $topic_num = $val['sum'];
            }elseif($val['type']==1){
                $activity_num = $val['sum'];
            }elseif($val['type']==2){
                $fensi_num = $val['sum'];
            }elseif($val['type']==3){
                $report_num = $val['sum'];
            }elseif($val['type']==4){
                $complain_num = $val['sum'];
            }
        }
        $this->assign('person_four_time',$lingtime-3*24*60*60);
        $this->assign('person_four_topic_num',$topic_num);
        $this->assign('person_four_activity_num',$activity_num);
        $this->assign('person_four_fensi_num',$fensi_num);
        $this->assign('person_four_report_num',$report_num);
        $this->assign('person_four_complain_num',$complain_num);

        $topic_num = 0;
        $activity_num = 0;
        $fensi_num = 0;
        $report_num = 0;
        $complain_num = 0;

        $map_5_time['addtime'] = array(array('gt',$lingtime-4*24*60*60),array('lt',$lingtime-3*24*60*60));
        $day_5_info = $forumAdminStatisticsModel->field('fid,type,sum(type)as sum')->where($map_5_time)->where(array('fid'=>$fid,'uid'=>$uid))->group('type')->select();
        foreach($day_5_info as $key=>$val){
            if($val['type']==0){
                $topic_num = $val['sum'];
            }elseif($val['type']==1){
                $activity_num = $val['sum'];
            }elseif($val['type']==2){
                $fensi_num = $val['sum'];
            }elseif($val['type']==3){
                $report_num = $val['sum'];
            }elseif($val['type']==4){
                $complain_num = $val['sum'];
            }
        }
        $this->assign('person_five_time',$lingtime-4*24*60*60);
        $this->assign('person_five_topic_num',$topic_num);
        $this->assign('person_five_activity_num',$activity_num);
        $this->assign('person_five_fensi_num',$fensi_num);
        $this->assign('person_five_report_num',$report_num);
        $this->assign('person_five_complain_num',$complain_num);

        $topic_num = 0;
        $activity_num = 0;
        $fensi_num = 0;
        $report_num = 0;
        $complain_num = 0;

        $map_6_time['addtime'] = array(array('gt',$lingtime-5*24*60*60),array('lt',$lingtime-4*24*60*60));
        $day_6_info = $forumAdminStatisticsModel->field('fid,type,sum(type)as sum')->where($map_6_time)->where(array('fid'=>$fid,'uid'=>$uid))->group('type')->select();
        foreach($day_6_info as $key=>$val){
            if($val['type']==0){
                $topic_num = $val['sum'];
            }elseif($val['type']==1){
                $activity_num = $val['sum'];
            }elseif($val['type']==2){
                $fensi_num = $val['sum'];
            }elseif($val['type']==3){
                $report_num = $val['sum'];
            }elseif($val['type']==4){
                $complain_num = $val['sum'];
            }
        }
        $this->assign('person_six_time',$lingtime-5*24*60*60);
        $this->assign('person_six_topic_num',$topic_num);
        $this->assign('person_six_activity_num',$activity_num);
        $this->assign('person_six_fensi_num',$fensi_num);
        $this->assign('person_six_report_num',$report_num);
        $this->assign('person_six_complain_num',$complain_num);

        $topic_num = 0;
        $activity_num = 0;
        $fensi_num = 0;
        $report_num = 0;
        $complain_num = 0;

        $map_7_time['addtime'] = array(array('gt',$lingtime-6*24*60*60),array('lt',$lingtime-5*24*60*60));
        $day_7_info = $forumAdminStatisticsModel->field('fid,type,sum(type)as sum')->where($map_7_time)->where(array('fid'=>$fid,'uid'=>$uid))->group('type')->select();
        foreach($day_7_info as $key=>$val){
            if($val['type']==0){
                $topic_num = $val['sum'];
            }elseif($val['type']==1){
                $activity_num = $val['sum'];
            }elseif($val['type']==2){
                $fensi_num = $val['sum'];
            }elseif($val['type']==3){
                $report_num = $val['sum'];
            }elseif($val['type']==4){
                $complain_num = $val['sum'];
            }
        }
        $this->assign('person_seven_time',$lingtime-6*24*60*60);
        $this->assign('person_seven_topic_num',$topic_num);
        $this->assign('person_seven_activity_num',$activity_num);
        $this->assign('person_seven_fensi_num',$fensi_num);
        $this->assign('person_seven_report_num',$report_num);
        $this->assign('person_seven_complain_num',$complain_num);

    }

    public function attendanceDetailList(){
        $this->attendance_detail_select();
        $this->display('attendance_detail_select');
    }

    public function help(){
        $this->display('help');
    }
}
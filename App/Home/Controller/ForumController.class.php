<?php

namespace Home\Controller;

class ForumController extends CommonController
{

    public function __construct()
    {
        parent::__construct();
    }

    //工作室信息
    public function info(){
        $fid = I('request.fid', null, 'intval');
        if (empty($fid)) {
            $info    = null;
            $code    = -1;
            $message = C('parameter_invalid');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $forumModel = D('Forum');
        $forum      = $forumModel->where(array('id' => $fid, 'status' => 1))->find();
        return $forum;
    }

    //获取工作室相关信息
    public function getinfo()
    {
        $forum = $this->info();
        $info    = $forum;
        $code    = 0;
        $message = '工作室相关信息';
        $return  = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    //获取推荐工作室
    public function getRecommend()
    {
        $forumModel     = D('Forum');
        $forumUserModel = D('ForumUser');
        if ($this->login) {
            $forums_1_info = $forumUserModel->getFavourForumByUid($this->uid);
            $forums_1      = array();
            foreach ($forums_1_info as $key => $val) {
                $fid            = $val['fid'];
                $info           = $forumModel->getForumInfoById($fid);
                $forums_1[$key] = $info;
            }

            $forums_2 = $forumModel->getAdminPromoteForum();

            $forums = array_merge($forums_1, $forums_2);

            $temp        = array();
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
            $forums   = $forums_2;
        }

        foreach ($forums as $key => $val) {
            $forums[$key]['url']        = U('Index/forum', array('id' => $val['id']));
            $forums[$key]['indexphoto'] = buildImgUrl(getAttachmentUrlById($val['indexphoto']));
            $forums[$key]['photo']      = buildImgUrl(getAttachmentUrlById($val['photo']));
        }

        $info    = $forums;
        $code    = 0;
        $message = '网站推荐工作室';
        $return  = $this->buildReturn($info, $code, $message);

        $this->ajaxReturn($return);

    }

    //查看用户是否签到
    public function checksign()
    {
        $fid = I('request.fid', null, 'intval');
        $uid = I('reques.uid', null, 'intval');
        if (empty($fid) || empty($uid)) {
            $info    = null;
            $code    = -1;
            $message = C('parameter_invalid');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $result  = checkIsSignByFidUid($fid, $uid);
        $info    = $result;
        $code    = 0;
        $message = '用户是否签到';
        $return  = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    //查看用户是否在工作室
    public function checkinforum()
    {
        $aid = I('request.aid', null, 'intval');
        $tid = I('request.tid', null, 'intval');
        $fid = I('request.fid', null, 'intval');
        $uid = $this->getUid();
        //参数错误
        if (empty($fid) && empty($aid) && empty($tid)) {
            $info    = null;
            $code    = -1;
            $message = C('parameter_invalid');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        if (empty($uid)) {
            $info    = null;
            $code    = 2;
            $message = C('no_login');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        if (!empty($aid)) {
            $fid = $this->getFidByAid($aid);
        }

        if (!empty($tid)) {
            $fid = $this->getFidByTid($tid);
        }

        $result  = checkIsInForum($uid, $fid);
        $info    = array('status' => $result, 'fid' => $fid);
        $code    = 0;
        $message = '用户是否在工作室';
        $return  = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    //举报经纪人处理
    public function reportagent()
    {
        $agentid = I('request.agentid', null, 'intval');
        $fid     = I('request.fid', null, 'intval');
        $uid     = $this->getUid();
        $touid   = $agentid;
        $reason  = I('request.reason', null);
        $content = I('request.content', null);
        $addtime = time();

        if (empty($uid)) {
            $info    = null;
            $code    = 2;
            $message = C('no_login');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        if (empty($agentid) || empty($fid) || empty($reason) || empty($content)) {
            $info    = null;
            $code    = -1;
            $message = C('parameter_invalid');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $data['fid']     = $fid;
        $data['uid']     = $uid;
        $data['touid']   = $touid;
        $data['reason']  = $reason;
        $data['content'] = $content;
        $data['type']    = 0;
        $data['addtime'] = time();

        $forumReportModel = D('ForumReport');
        $result           = $forumReportModel->add($data);
        $href             = "<br/><a href='http://www.yingplus.cc/admin.php/Home/AgentManage/reportAgentList'>进入后台管理</a>";
        if ($result) {
            Vendor('phpmailer.sendMailCommon');
            $sendClass            = new \sendMailCommon();
            $sendClass->emailbody = I('post.content', null) . $href;
            $sendClass->sendMailCommonfun();
            $info    = null;
            $code    = 0;
            $message = '举报成功';
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        } else {
            $info    = null;
            $code    = 1;
            $message = '举报失败';
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
    }

    //查询封禁用户
    public function getbanuser()
    {
        $number        = I('request.number', 10, 'intval');
        $p             = I('request.p', 1, 'intval');
        $number        = ($number > 50) ? 50 : $number;
        $forumBanModel = D('ForumBan');
        $forumbans     = $forumBanModel->where(array('status' => 1))->order(array('id' => 'desc'))->limit(($p - 1) * $number, $number)->select();
        foreach ($forumbans as $key => $val) {
            $forumbans[$key]['username'] = getUserNicknameById($val['uid']);
        }
        $info    = $forumbans;
        $code    = 0;
        $message = "封禁用户";
        $return  = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    //封禁用户
    public function banuser()
    {
        $uid       = I('request.uid', null, 'intval');
        $fid       = I('request.fid', null, 'intval');
        $level     = I('request.level', null, 'intval');
        $reason    = I('request.reason', null);
        $totaltime = I('request.totaltime', 3600, 'intval');
        $bantime   = time();

        if (empty($uid) || empty($fid) || empty($level) || empty($reason) || empty($totaltime)) {
            $info    = null;
            $code    = -1;
            $message = C('parameter_invalid');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $level_arr = array(1, 2, 3);
        if (!in_array($level, $level_arr)) {
            $info    = null;
            $code    = -1;
            $message = C('parameter_invalid');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $data['fid']       = $fid;
        $data['uid']       = $uid;
        $data['level']     = $level;
        $data['reason']    = $reason;
        $data['totaltime'] = $totaltime;
        $data['bantime']   = $bantime;

        $forumBanModel = D('ForumBan');
        $messageModel  = D('Message');

        $privilege = $this->checkPrivilege('isadmin');

        if (!$privilege) {
            $info    = null;
            $code    = 3;
            $message = '没有权限';
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $meta['fid']         = $fid;
        $meta['uid']         = $this->getUid();
        $meta['touid']       = $uid;
        $meta['username']    = getUserNicknameById($meta['uid']);
        $meta['tousername']  = getUserNicknameById($meta['touid']);
        $meta['subject']     = C("COMMON_MESSAGE_BAN_USER");
        $meta['content']     = $this->BanUserTimeFormat($data['totaltime']);
        $meta['reason']      = $reason;
        $meta['addtime']     = $bantime;
        $meta['iscomplaint'] = 1;
        $messageModel->add($meta);

        $bool = $forumbanuserModel->where(array('fid' => $fid, 'uid' => $uid))->find();
        if ($bool) {
            $forumbanuserModel->where(array('id' => $info['id']))->save($data);
        } else {
            $forumbanuserModel->add($data);
        }

        $info    = null;
        $code    = 0;
        $message = '封禁用户成功';
        $return  = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    //申述处理
    public function complain()
    {
        $fid     = I('request.fid', null, 'intval');
        $uid     = $this->getUid();
        $content = I('request.content', null);
        $addtime = time();
        $type    = I('request.type', null, 'intval');
        if (empty($uid)) {
            $info    = null;
            $code    = 2;
            $message = C('no_login');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        if (empty($fid) || empty($content) || empty($type)) {
            $info    = null;
            $code    = -1;
            $message = C('parameter_invalid');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $type_arr = array(1, 2);

        if (!in_array($type, $type_arr)) {
            $info    = null;
            $code    = -1;
            $message = C('parameter_invalid');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $data['fid']     = $fid;
        $data['uid']     = $uid;
        $data['content'] = $content;
        $data['addtime'] = $addtime;
        $data['type']    = $type;

        $forumComplainModel = D('ForumComplain');
        $id                 = $forumComplainModel->add($data);
        $info               = $id;
        $code               = 0;
        $message            = '申述成功';
        $return             = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    //工作室管理员
    public function forumadmin()
    {
        $fid = I('request.fid', null, 'intval');
        if (empty($fid)) {
            $info    = null;
            $code    = -1;
            $message = C('parameter_invalid');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
        $forumUserModel = D('ForumUser');
        $admins         = $forumUserModel->getAdminUserInfo($fid);
        return $admins;
    }
    //工作室管理员
    public function getforumadmin()
    {
        $admins  = $this->forumadmin();
        $info    = $admins;
        $code    = 0;
        $message = '工作室管理员';
        $return  = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    //工作室列表
    public function listing()
    {
        $p          = I('request.p', 1, 'intval');
        $number     = I('request.number', 10, 'intval');
        $number     = ($number > 20) ? 20 : $number;
        $forumModel = D('Forum');
        $count = $forumModel->where(array('status'=>1))->count();
        $forums     = $forumModel->where(array('status'=>1))->order(array('id' => 'asc'))->limit(($p - 1) * $number, $number)->select();
        $Page         = new \Think\Page($count, $number);
        $show  = $Page->show();
        $info['data'] = $forums;
        $info['count'] = $count;
        $info['page'] = $show;
        return $info;
    }

    //工作室列表
    public function getlisting()
    {
        $forums  = $this->listing();
        $info    = $forums;
        $code    = 0;
        $message = '工作室列表信息';
        $return  = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    //加入工作室
    public function joinforum()
    {
        $uid            = session('uid');
        $fid            = I('request.fid', null, 'intval');
        //是否登录
        if (empty($uid)) {
            $info    = null;
            $code    = 2;
            $message = C('no_login');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        if (empty($fid)) {
            $info    = null;
            $code    = -1;
            $message = C('parameter_invalid');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        //超过限制
        // if ($forumuserModel->checkFollowIsOverflow($uid)) {
        //     $info    = null;
        //     $code    = 1;
        //     $message = C('FOLLOW_FORUM_OVERFLOW');
        //     $return  = $this->buildReturn($info, $code, $message);
        //     $this->ajaxReturn($return);
        // }

        $forumuserModel = D('ForumUser');
        $forumModel     = D('Forum');

        if (!$forumuserModel->checkExist($fid, $uid)) {
            //首次加入本吧
            $firsttime = time();
            //加入的时间
            $addtime = time();
            $result  = $forumuserModel->insertUser($fid, $uid, $firsttime, $addtime);
            //更新工作室粉丝数量
            $forumModel->where(array('id' => $fid))->setInc('fansnum');
            $info    = null;
            $code    = 0;
            $message = C('FOLLOW_FORUM_SUCCESS');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);

        } elseif ($forumuserModel->OnceFollowed($fid, $uid)) {
            //以前加入过本工作室更新addtime和关注次数
            $addtime = time();
            $result  = $forumuserModel->updateUser($fid, $uid, $addtime);
            //更新该用户关注取消的次数
            $forumuserModel->where(array('fid' => $fid, 'uid' => $uid))->setInc('noticecount', 1);
            //更新工作室粉丝数量
            $forumModel->where(array('id' => $fid))->setInc('fansnum');
            $info    = null;
            $code    = 0;
            $message = C('FOLLOW_FORUM_SUCCESS');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        } else {
            $message = '您已经加入工作室了';
            $code    = 1;
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

    }

    //退出工作室
    public function exitforum()
    {
        $uid            = session('uid');
        $fid            = I('request.fid', null, 'intval');

        //是否登录
        if (!$this->checkLogin()) {
            $info    = null;
            $code    = 2;
            $message = C('no_login');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        if(empty($fid)){
            $info    = 'parameter_invalid';
            $code    = -1;
            $message = C('parameter_invalid');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $forumuserModel = D('ForumUser');
        $forumModel     = D('Forum');

        //查看是否在本吧进行过签到行为
        $ForumSign = D('ForumSign');
        //签到数据清空
        if ($ForumSign->checkExist($fid, $uid)) {
            $ForumSign->where(array('uid' => $uid, 'fid' => $fid))->setField(array('count' => 0, 'lcount' => 0, 'addtime' => 0, 'prelogintime' => 0, 'lingtime' => 0));
        }
        if (checkIsForumAdminByUidFid($uid, $fid)) {
            //取消关注，取消经纪人资格
            $forumuserModel->where(array('fid' => $fid, 'uid' => $uid))->setField(array('status' => 1, 'isadmin' => 0));
        } else {
            $forumuserModel->where(array('fid' => $fid, 'uid' => $uid))->setField(array('status' => 1));
        }

        //更新工作室粉丝数量
        $forumModel->where(array('id' => $fid))->setDec('fansnum');
        $info    = null;
        $code    = 0;
        $message = '退出工作室成功';
        $return  = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    public function searchuser(){
        $keyword = I('request.keyword',null);
        $fid = I('request.fid',null,'intval');
        $forumUserModel = D('ForumUser');
        $condition['yj_forum_user.fid'] = $fid;
        $condition['yj_forum_user.status'] = 0;
        $meta['yj_user.nickname'] = array('like','%'.$keyword.'%');
        $userlist = $forumUserModel->where($condition)->join('yj_user ON yj_forum_user.uid = yj_user.id and yj_user.nickname like "%'.$keyword.'%"')->select();
        
        if(empty($userlist)){
            $info = 'empty';
            $code = 1;
            $message = '没有查询到';
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        foreach($userlist as $key=>$val){
            $userlist[$key]['userphoto'] = getUserNicknameById($uid);
            $userlist[$key]['username']  = buildImgUrl(getUserPhotoById($uid));
        }
        $info = $userlist;
        $code = 0;
        $message = '匹配用户';
        $return = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);

    }

    //工作室用户
    public function userlist(){
        $p = I('request.p',1,'intval');
        $number = I('request.number',10,'intval');
        ($number>50) ? $number=50 : '';
       
        $fid = I('request.fid',null,'intval');
        if(empty($fid)){
            $info = null;
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
        $forumUserModel = D('ForumUser');
        $condition['fid'] = $fid;
        $condition['status'] = 0;
        $count = $forumUserModel->where($condition)->count();
        $list = $forumUserModel->where($condition)->order(array('addtime'=>'desc'))->limit(($p-1)*$number,$number)->select();
        $Page       = new \Think\Page($count,$number);// 实例化分页类 
        $show       = $Page->show();// 分页显示输出
        $info['data'] = $list;
        $info['page'] = $show;
        $info['count'] = $count;
        return $info;
    }

    //获取工作室用户
    public function getuserlist(){
        $info = $this->userlist();
        $code = 0;
        $message = '工作室人员';
        $return = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    //经纪人申请
    public function applyagent()
    {
        $uid = $this->getUid();
        $fid = I('request.fid',null,'intval');
        if(empty($uid)){
            $info = 'no_login';
            $code = 2;
            $message = C('no_login');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        if(empty($fid)){
            $info = 'parameter_invalid';
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $forumUserModel  = D('ForumUser');
        $forumAgentModel = D('ForumAgent');
        $condition['uid'] = $uid;
        $condition['fid'] = $fid;
        $condition['status'] = 0;
        $result          = $forumUserModel->where(array('uid' => $uid, 'fid' => $fid, 'status' => 0))->find();
        if (!$result) {
            $info = 'not_in_forum';
            $code = 4;
            $message = '不在工作室中';
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }    
            
        $admin = $forumUserModel->where(array('uid' => $uid, 'isadmin' => 1, 'status' => 0))->find();
        if ($admin) {
            $info = 'is_agent';
            $code = 1;
            $message = '您已经是经纪人';
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        } 

        //检查是否已经在此工作室提交过一次请求，但还未审核
        $forumAgentModel = D('ForumAgent');
        $meta['uid'] = $uid;
        $meta['fid'] = $fid;
        $meta['status'] = 0;
        $agent = $forumAgentModel->where($meta)->find();
        if ($agent) {
            $info = null;
            $code = 1;
            $message = '您已经提交过申请,请耐心等待审核';
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        } 

        $href = "<br/><a href='http://".$_SERVER['HTTP_HOST']."/admin.php/Home/AgentManage/applyAgentList'>进入后台管理</a>";
        Vendor('phpmailer.sendMailCommon');
        $sendClass            = new \sendMailCommon();
        $message              = "有用户申请经纪人消息";
        $sendClass->emailbody = $message . $href;
        $sendClass->sendMailCommonfun();
        $data['uid'] = $uid;
        $data['fid'] = $fid;
        $data['addtime'] = time();
        $forumAgentModel->add($data);
        $info = 'success';
        $code = 0;
        $message = '你的申请已提交,请耐心等待审核';
        $return = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    //创建工作室申请
    public function applycreateforum()
    {
        $uid = $this->getUid();
        $forumname = I('request.forumname');

        if ($this->checkLogin()) {
            $data['forumname']      = I('post.forumname', null, '');
            $data['representative'] = I('post.representative', null, '');
            $data['uid']            = session('uid');
            $data['put_time']       = date('Y-m-d H:i:s', time());
            $forumCreateModel       = D('ForumCreate');
            if ($forumCreateModel->add($data)) {
                $this->ajaxReturn(array('status' => 1, 'content' => C('SUCCESS_SUBMIT_FORUM')));
            } else {
                $this->ajaxReturn(array('status' => 2, 'content' => C('FAILED_SUBMIT_FORUM')));
            }
        } else {
            $this->ajaxResturn(array('status' => 0, 'content' => ('NO_LOGIN')));
        }
    }

    //签到
    public function signforum()
    {
        $uid            = session('uid');
        $fid            = I('request.fid', null, 'intval');
        if (empty($uid)) {
            $info    = null;
            $code    = 2;
            $message = C('no_login');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
        
        if (empty($fid)) {
            $info    = null;
            $code    = -1;
            $message = C('parameter_invalid');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $forumSignModel = D('ForumSign');
        if (!$forumSignModel->checkExist($fid, $uid)) {
            $lingTime = strtotime("today") + 24 * 60 * 60;
            $now      = time();
            $id = $forumSignModel->insertUser($fid, $uid, $lingTime, $now);
            
            $info    = $id;
            $code    = 0;
            $message = C('SIGN_FORUM_SUCCESS');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $timeinfo     = $forumSignModel->getTimeInfo($fid, $uid);
        $lingTime     = $timeinfo['lingtime'];
        $prelogintime = $timeinfo['prelogintime'];
        $now          = time();
        if ($now < $lingTime) {
            $info    = null;
            $code    = 4;
            $message = C('SIGN_FORUM_EXIST');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        } else {
            $lingTime = strtotime("today") + 24 * 60 * 60;
            $forumSignModel->updateUser($fid, $uid, $lingTime, $now);
           
            //处理连续签到
            if ($now - $prelogintime < 24 * 60 * 60) {
                $forumSignModel->updateLCount($fid, $uid, 1);
            } else {
                $forumSignModel->updateLCount($fid, $uid, 0);
            }
            $info    = null;
            $code    = 4;
            $message = C('SIGN_FORUM_SUCCESS');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

    }

    //明星公益
    public function welfareevent(){
        $fid            = I('request.fid', null, 'intval');
        $number  = I('request.number',10,'intval');
        ($number>50) ? $number = 50 : '';
        $p = I('request.p',1,'intval');
        $uid = I('request.uid',null,'intval');
        $forumSignModel = D('ForumSign');

        if (empty($fid)) {
            $info    = null;
            $code    = -1;
            $message = C('parameter_invalid');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $welfareEventModel = D('WelfareEvent');
        if(!empty($uid)) {
            $condition['uid'] = $uid;
        }
        $condition['fid'] = $fid;
        $condition['status'] = 0;
        $count = $welfareEventModel->where($condition)->count();
        $events  = $welfareEventModel->where($condition)->order(array('id'=>'asc'))->limit(($p-1)*$number,$number)->select();
        $Page = new \Think\Page($count,$number);
        $show = $Page->show();
        $info['data'] = $events;
        $info['count'] = $count;
        $info['page'] = $show;
        return $info;
    }

    //明星公益信息
    public function getwelfareevent()
    {
        $events = $this->welfareevent();
        $info    = $events;
        $code    = 0;
        $message = '星公益';
        $return  = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    

   

}

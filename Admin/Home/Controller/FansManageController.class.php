<?php
/**
 * Created by PhpStorm.
 * User: roak
 * Date: 2015/9/18
 * Time: 14:18
 */

namespace Home\Controller;


/**
 * Class  FansManageController
 * @package Admin\Home\Controller
 * @description : 该类用于粉丝的管理
 */
class FansManageController extends CommonController
{

    //粉丝展示
    public function fansList(){
        $userModel = D('User');
        $count = $userModel->where(array('status'=>0))->count();
        $Page = new \Think\AjaxPage($count,C('PAGE_LISTROWS'),'getFansInfoList');
        $show = $Page->show();
        $userList = $userModel->where(array('status'=>0))->limit(0,C('PAGE_LISTROWS'))->select();
        $this->assign('userList',$userList);
        $this->assign('page',$show);
        $this->display('fansList');
    }

    public function getFansInfoList(){
        $userModel = D('User');
        $nickname = I('request.nickname',null,'');
        $begintime = I('request.begintime',null,'strtotime');
        $endtime = I('request.endtime',null,'strtotime');
        
        $p = I('request.p',1,'intval');
        $data['nickname'] = array('like','%'.$nickname.'%');
        empty($begintime)&&!empty($endtime) ? $data['regtime'] = array('lt',$endtime) : '';
        
        !empty($begintime)&&empty($endtime) ? $data['regtime'] = array('gt',$begintime) : '';

        !empty($begintime)&&!empty($endtime) ? $data['regtime'] = array(array('gt',$begintime),array('lt',$endtime),'and') : '';
        
        //$data['_logic'] = 'OR';
        $count = $userModel->where($data)->count();
        
        $Page = new \Think\AjaxPage($count,C('PAGE_LISTROWS'),'getFansInfoList');
        $show = $Page->show();
        $userList = $userModel->where($data)->limit(($p-1)*C('PAGE_LISTROWS'),$p*C('PAGE_LISTROWS'))->select();
       
        $this->assign('userList',$userList);
        $this->assign('page',$show);
        $this->display('fansShow');
    }

    //粉丝封禁列表展示
    public function fansBanList(){
        $ForumBanUser = D('ForumBanUser');
        $Forum = D('Forum');
        $User = D('User');
        $nickname = I('request.nickname',null,'');
        $begintime = I('request.begintime',null,'');
        $endtime = I('request.endtime',null,'');
        $totaltime = I('request.totaltime',null,'');
        $option = I('request.option',null,'');
        $starname = I('request.starname',null,'');
        $uid = $User->getUserIDByNickName($nickname);
        //TODO 先不考虑关注的明星
        $fid = $Forum->getForumIDByName($starname); //得到文本框填写明星的ID
        $arr = array('uid'=>$uid,'begintime'=>$begintime,'endtime'=>$endtime,'totaltime'=>$totaltime,'option'=>$option,'fid'=>$fid);
        $wheresql = $this->getWhereSqlAboutBanUser($arr);
        $count = $ForumBanUser->getCountBanUser($wheresql,$fid);
        $Page = new \Think\Page($count,C('PAGE_LISTROWS'));
        parent::setPageConfig($Page);
        $show = $Page->show();
        $forumBanUser = $ForumBanUser->getBanUser($wheresql,$Page,$fid);
        $forumBanUser = $this->getBanUserDate($forumBanUser);

        $this->assign('forumBanUser',$forumBanUser);
        $this->assign('page',$show);
        $arr['nickname'] = $nickname;
        $arr['starname'] = $starname;
        $this->assign('inputinfo',$arr);
        $this->assign('count',$count);
        $this->display('banUserList');
    }
    //封禁用户的处理操作
    public function banUserDispose(){

        $uid = I('post.uid',null,'');
        $fj1 = I('post.fj1',null,'');
        $fj2 = I('post.fj2',null,'');
        $reason = I('post.content',null,'');
        $time = I('post.time',null,'');
        if(!empty($fj1)){
            $option = '1';
        }
        if(!empty($fj2)){
            $option = '2';
        }
        if(!empty($fj1)&&!empty($fj2)){
            $option = '3';
        }
        $addtime = time();
        $arr = array(
            'reason' => $reason,
            'option' => $option,
            'totaltime' => $time,
            'bantime' => $addtime,
            'status' => 1
        );
        $ForumBanUser = D('ForumBanUser');
        $ForumBanUser->saveBanUser($uid,$arr);
        $this->fansList();
    }
    //展示粉丝申诉列表
    public function fansComplainList(){
        $User = D('User');
        $ForumComplain = D('ForumComplain');
        $nickname = I('request.nickname',null,'');
        $begintime = I('request.begintime',null,'');
        $endtime = I('request.endtime',null,'');
        //这是一个按条件进行查找信息的，所以要组织SQL语句但要适用于所有的操作
        //1、查找nickname对应的用户ID，该昵称有可能是申诉者，有可能是被申诉者
        $uid = $User->getUserIDByNickName($nickname);
        $arr = array('uid'=>$uid,'begintime'=>$begintime,'endtime'=>$endtime);
        $wheresql = $this->getWhereSqlByCondition($arr);
        $count = $ForumComplain->getCountByCondition($wheresql);
        $Page  = new \Think\Page($count,C('PAGE_LISTROWS'));
        parent::setPageConfig($Page);
        $show = $Page->show();  //分页显示输出
        $complainList = $ForumComplain->findDateByCondition($wheresql,$Page);
        $complainList = $this->getComplainDate($complainList,$User);
        $this->assign('complainList',$complainList);
        $this->assign('page',$show);
        $arr['nickname'] = $nickname;
        $this->assign('inputinfo',$arr);
        $this->display('complainList');
    }

    //粉丝申诉受理
    public function complainManage(){
        //test
        session('user_id',1);
        $ForumComplain = D('ForumComplain');
        $id = I('get.id',null,'');
        $istrue = I('get.istrue',null,'');
        $touid = I('get.touid',null,'');
        if($istrue == 1){
            //申诉属实
            //TODO 改变申诉的那个帖子或者活动
            parent::commonSend(C('COMPLAIN_TRUE_CONTENT'),$touid,'粉丝申诉消息');
        }else{
            parent::commonSend(C('COMPLAIN_FALSE_CONTENT'),$touid,'粉丝申诉消息');
        }
        //将申诉内容的状态进行改变
        $arr['isshenhe'] = 0;
        $ForumComplain->updateComplainStatus($id,$arr);
        $this->fansComplainList();
    }
    //粉丝封禁管理
    public function banUserManage(){
        $id = I('post.id',null,'');
        $status = I('post.status',null,'');
        $ForumBanUser = D('ForumBanUser');

        if($status == 1){
            $arr['status'] = 0;
        }elseif($status == 0){
            $arr['status'] = 1;
        }
        $updateResult = $ForumBanUser->updateBanUserStatus($id,$arr);
        if($updateResult == 1){
            $this->ajaxReturn(true);
        }else{
            $this->ajaxReturn(false);
        }

    }
    //粉丝申诉受理部分通过数组信息组织查询条件SQL语句
    public function getWhereSqlByCondition($arr){
        //将开始和结束日期进行转换
        $arr['begintime'] = strtotime($arr['begintime']);
        $arr['endtime'] = strtotime($arr['endtime']);
        $wheresql = " 1 = 1";
        if(!empty($arr['uid'])){
            $wheresql .= " and (uid = ".$arr['uid']." or touid = ".$arr['uid']." )";
        }
        if(!empty($arr['begintime'])){
            $wheresql .= " and addtime >= ".$arr['begintime'];
        }
        if(!empty($arr['endtime'])){
            $wheresql .= " and addtime <= ".$arr['endtime'];
        }
        $wheresql .= " and status = 0";
        return $wheresql;
    }
    //粉丝封禁部分通过数组信息组织查询条件SQL语句
    public function getWhereSqlAboutBanUser($arr){
        $arr['begintime'] = strtotime($arr['begintime']);
        $arr['endtime'] = strtotime($arr['endtime']);
        $wheresql = " 1 = 1";
        if(!empty($arr['uid'])){
            if(!empty($arr['fid'])){
                $wheresql .= " and b.uid = ".$arr['uid'];
            }else{
                $wheresql .= " and uid = ".$arr['uid'];
            }
        }
        if(!empty($arr['begintime'])){
            $wheresql .= " and bantime >= ".$arr['begintime'];
        }
        if(!empty($arr['endtime'])){
            $wheresql .= " and bantime <=".$arr['endtime'];
        }
        if(!empty($arr['totaltime'])){
            $wheresql .= " and totaltime = ".$arr['totaltime'];
        }
        if(!empty($arr['option'])){
            $wheresql .= " and `option` = ".$arr['option'];
        }
        return $wheresql;
    }
    //粉丝举报经纪人审核通过需要给举报人发送消息，该方法用于将消息保存到数据库
    public function saveMessageAboutReport(){
        $Message = D('message');
        //准备数据
        $uid = I('post.uid',null,'');
        $touid = I('post.touid',null,'');
        $flag = I('post.flag',null,0);
        if($flag == 0){
            $subject = C('REPORT_AGANT_TRUE_MESSAGE_TITLE');
            $content = C('REPORT_AGANT_TRUE_MESSAGE');
        }else{
            $subject = C('REPORT_AGANT_FALSE_MESSAGE_TITLE');
            $content = C('REPORT_AGANT_FALSE_MESSAGE');
        }
        $date = $this->getMessageArr($uid,$touid,$content,$subject);
        $Message->saveMessage($date);
    }
    //粉丝举报经纪人审核通过需要给经纪人发送消息，该方法用于将消息保存到数据库
    public function saveMessageAboutAgant(){
        $Message = D('message');
        $uid = I('post.uid',null,'');
        $touid = I('post.touid',null,'');
        $subject = C('REPORTED_AGANT_MESSAGE_TITLE');
        $content = I('post.content',C('REPORTED_AGANT_MESSAGE'),'');
        $date = $this->getMessageArr($uid,$touid,$content,$subject);
        $Message->saveMessage($date);
    }

    //处理举报经纪人：属实(0) | 不属实(1)
    public function disposeReport(){

        $reportID = I('post.id',null,'');
        $ForumReport = D('ForumReport');
        $date['isshenhe'] = 0;
        $result = $ForumReport->updateReportStatus($reportID,$date);
        if(!empty($result)){
            $this->ajaxReturn(true);
        }else{
            $this->ajaxReturn(false);
        }
    }

    //检验明星是否存在
    public function checkStar_exist(){
        $starname = I('post.starname',null,'');
        $Forum = D('Forum');
        $fid = $Forum->getForumIDByName($starname); //得到文本框填写明星的ID
        if(empty($fid)){
            $this->ajaxReturn(false,'json');
        }else{
            $this->ajaxReturn(true,'json');
        }
    }

    //封装封禁用户的数据
    public function getBanUserDate($UserList){
        $User = D('User');
        foreach($UserList as $key => $value){
            $UserList[$key]['nickname'] = $User->getUserNickNameByUID($UserList[$key]['uid']);
            //TODO 时间还没处理
            $UserList[$key]['bantime'] = date('Y-m-d',$value['bantime']);
            if($UserList[$key]['totaltime'] == 3600){
                $UserList[$key]['totaltime'] = '一小时';
            }elseif($UserList[$key]['totaltime'] == 86400){
                $UserList[$key]['totaltime'] = '一天';
            }else{
                $UserList[$key]['totaltime'] = '三天';
            }
            if($UserList[$key]['option'] == 1){
                $UserList[$key]['content'] = C('BANUSER_1');
            }elseif($UserList[$key]['option'] == 2){
                $UserList[$key]['content'] = C('BANUSER_2');
            }else{
                $UserList[$key]['content'] = C('BANUSER_3');
            }
        }
        return $UserList;
    }
    //封装申诉的数据
    public function getComplainDate($List,$User){
        foreach($List as $key => $value){
            $List[$key]['reportnickname'] = $User->getUserNickNameByUID($List[$key]['uid']);
            $List[$key]['reportednickname'] = $User->getUserNickNameByUID($List[$key]['touid']);
            $List[$key]['addtime'] = date('Y-m-d',$value['addtime']);
            if($List[$key]['type'] == 0){
                $Topic = D('Topic');
                $List[$key]['subject'] = $Topic->getTopicSubject($List[$key]['involveid']);
            }elseif($List[$key]['type'] == 1){
                $Activity = D('Activity');
                $List[$key]['subject'] = $Activity->getActivitySubject($List[$key]['involveid']);
            }
        }
        return $List;
    }

    //用于得到消息数组
    /**
     * @param $uid
     * @param $touid
     * @param $content
     * @param $subject
     * @return mixed
     */
    public function getMessageArr($uid,$touid,$content,$subject){
        $User = D('User');
        $addtime = time();
        $isread = 0;
        $status = 0;
        $issend = 0;
        //封装数据
        $date['uid'] = $uid;
        $date['username'] = $User->getUserNickNameByUID($uid);
        $date['touid'] = $touid;
        $date['tousername'] = $User->getUserNickNameByUID($touid);
        $date['subject'] = $subject;
        $date['content'] = $content;
        $date['addtime'] = $addtime;
        $date['isread']  = $isread;
        $date['issend']  = $issend;
        $date['status']  = $status;
        return $date;
    }
}
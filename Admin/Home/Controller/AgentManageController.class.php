<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/9/25
 * Time: 15:43
 */

namespace Home\Controller;

/**
 * Class AgentManageController
 * @package Home\Controller
 * @discribe :此类用于管理经纪人
 */
class AgentManageController extends CommonController
{

    //经纪人列表
    public function agentList(){
        $ForumUser = D('ForumUser');
        $forumModel = D('Forum');
        $forums = $forumModel->where(array('status'=>1))->select();
        $this->assign('forums',$forums);
        $nickname = I('request.nickname',null,'');
        $forumname = I('request.forumname',null,'');
        empty($forumname) ? '' : session('sforumname',$forumname);
        $arr = array('nickname'=>$nickname,'forumname'=>$forumname);
        $wheresql = $this->getWheresql($arr);
        $count = $ForumUser->getCountAgent($wheresql);
        $Page = new \Think\Page($count,C('PAGE_LISTROWS'));
        parent::setPageConfig($Page);
        $show = $Page->show();
        $agentList = $ForumUser->getAgentList($wheresql,$Page);
        $agentList = $this->getAgentListData($agentList);
        $this->assign('agentList',$agentList);
        $this->assign('page',$show);
        $this->assign('inputinfo',$arr);
        $this->assign('count',$count);
        $this->display('agentList');
    }

    //用于展示申请经纪人的列表
    public function applyAgentList(){

        $ForumAgent = D('ForumAgent');
        $count = $ForumAgent->getCountAgent();
        $Page = new \Think\Page($count,C('PAGE_LISTROWS'));
        parent::setPageConfig($Page);
        $show = $Page->show();
        $applyAgentList = $ForumAgent->getApplyAgentList($Page);
        $applyAgentList = $this->getApplyAngentData($applyAgentList);
        $this->assign('applyAgentList',$applyAgentList);
        $this->assign('page',$show);
        $this->display('applyAgentList');
    }

    public function applyAgent(){

        $id = I('get.id');
        $flag = I('get.flag');
        $Forum = D('Forum');
        $ForumUser = D('ForumUser');
        $ForumAgent = D('ForumAgent');
        $uid = $ForumAgent->getFieldById($id,'uid');
        if( $flag == 'yes'){

            //通过经纪人审核
            $ForumAgent-> where(array('id'=>$id))->setField('status',1);
            $fid = $ForumAgent->getFieldById($id,'fid');


            //通过经纪人审核后将ForumAgent表中的时间进行更新
            $time = time();
            $ForumUser-> where(array('fid'=>$fid,'uid'=>$uid))->setField('becometime',$time);
            $fid = $ForumAgent->getFieldById($id,'fid');
            parent::commonSend('亲爱的粉丝，你好！ 很高兴地告诉你，你已经顺利通过经纪人审核，成为'.$Forum->getForumNameById($fid).'工作室经纪人。欢迎加入影加建设的大队伍中！ 感谢您对影加的一片热忱和贡献，以及一直以来对我们的信任和支持，我们会和你一起加油的！',$uid,'申请经纪人通知',$fid);
            //改变该人的isadmin
            $ForumUser->where(array('fid'=>$fid,'uid'=>$uid))->setField('isadmin',1);
        }else{
            $ForumAgent-> where(array('id'=>$id))->setField('status',1);
            $fid = $ForumAgent->getFieldById($id,'fid');
            parent::commonSend('您好，很遗憾的通知您，您提交的'.$Forum->getForumNameById($fid).'工作室经纪人申请未被通过',$uid,'申请经纪人通知',$fid);
            //改变该人的isadmin
            $ForumUser->where(array('fid'=>$fid,'uid'=>$uid))->setField('isadmin',0);
        }
        $this->redirect('AgentManage/applyAgentList');

    }
    //用于展示举报经纪人的列表
    public function reportAgentList(){

        $ForumReport = D('ForumReport');
        $User = D('User');
        $type = 0;
        $status = 0;
        $count = $ForumReport->getReportCount($type,$status);
        $Page = new \Think\Page($count,C('PAGE_LISTROWS'));
        parent::setPageConfig($Page);
        $show = $Page->show();
        $reportList = $ForumReport->getReportAgentList($type,$status,$Page);
        $reportList = $this->getReportDate($reportList,$User);
        $this->assign('reportList',$reportList);
        $this->assign('page',$show);
        $this->display('reportAngentList');
    }

    //撤销经纪人资格
    public function removeAgent(){
        $ForumUser = D('ForumUser');
        $id = I('get.id',null,'');
        $touid = I('get.touid',null,'');
        //得到fid
        $fid = $ForumUser->getFieldById($id,'fid');
        parent::commonSend(C('AGENT_REMOVE_MESSAGE'),$touid,'经纪人撤销通知',$fid);
        $arr['isadmin'] = 0;
        $ForumUser->updateAgent($id,$arr);
        $this->agentList();
    }

    //恢复经纪人资格
    public function recoverAgent(){
        $ForumUser = D('ForumUser');
        $id = I('get.id',null,'');
        $touid = I('get.touid',null,'');
        //得到fid
        $fid = $ForumUser->getFieldById($id,'fid');
        parent::commonSend(C('AGENT_RECOVER_MESSAGE'),$touid,'恢复经纪人资格通知',$fid);
        $arr['isadmin'] = 1;
        $ForumUser->updateAgent($id,$arr);
        $this->agentList();
    }
    //举报经纪人操作
    public function reportAgent(){

        $ForumAgent = D('ForumAgent');
        $id = I('get.id',null,'');
        $istrue = I('get.istrue',null,'');
        $touserid = I('get.touserid',null,'');
        //得到fid
        $fid = $ForumAgent->getFieldById($id,'fid');
        if($istrue == 1){
            parent::commonSend(C('REPORT_AGANT_TRUE_MESSAGE'),$touserid,'举报经纪人消息',$fid);
        }else{
            parent::commonSend(C('REPORT_AGANT_FALSE_MESSAGE'),$touserid,'举报经纪人消息',$fid);
        }
        $ForumReport = D('ForumReport');
        $arr['isshenhe'] = 0;
        $ForumReport->updateReportStatus($id,$arr);
        $this->reportAgentList();
    }
    //封装经纪人列表的数据用于前台的显示
    public function getAgentListData($agentList){
        $User = D('User');
        $Forum = D('Forum');
        foreach($agentList as $key => $value){
            $agentList[$key]['nickname'] = $User->getUserNickNameByUID($value['uid']);
            $agentList[$key]['forumname'] = $Forum->getForumNameById($value['fid']);
            $agentList[$key]['becometime'] = date('Y-m-d H:i:s',$agentList[$key]['becometime']);
        }
        return $agentList;
    }


    /*//组织查询条件的sql
    public function getWheresql($arr){
        $User = D('User');
        $Forum = D('Forum');
        $uid = $User->getUserIDByNickName($arr['nickname']);
        $wheresql = ' 1=1 ';
        if(!empty($uid)){
            $wheresql .= ' and uid='.$uid;
        }
        if(!empty($arr['begintime'])){
            $begintime = strtotime($arr['begintime']);
            $wheresql .= ' and becometime >='.$begintime;
        }
        if(!empty($arr['endtime'])){
            $endtime = strtotime($arr['endtime']);
            $wheresql .= ' and becometime <='.$endtime;
        }
        if(!empty($arr['forumname'])){
            $fid = $Forum->getForumIDByName($arr['forumname']);
            $wheresql .= ' and fid ='.$fid;
        }
        return $wheresql;
    }*/

    //组织查询条件的sql
    public function getWheresql($arr){
        $User = D('User');
        $Forum = D('Forum');
        $uid = $User->getUserIDByNickName($arr['nickname']);
        if(!empty($uid)){
            //$wheresql .= ' and uid=
            $map['uid'] = array('eq',$uid);
        }
        if(!empty($arr['begintime'])){
            $begintime = strtotime($arr['begintime']);
            //$wheresql .= ' and becometime >='.$begintime;
            $map['becometime'] = array('egt',$begintime);
        }
        if(!empty($arr['endtime'])){
            $endtime = strtotime($arr['endtime']);
            //$wheresql .= ' and becometime <='.$endtime;
            $map['becometime'] = array('egt',$endtime);
        }
        if(!empty($arr['forumname'])){
            $fid = $Forum->getForumIDByName($arr['forumname']);
            //$wheresql .= ' and fid ='.$fid;
            $map['fid'] = array('eq',$fid);
        }
        $map['isadmin'] = array('eq',1);
        return $map;
    }
    //封装申请经纪人的数据用于前端展示
    public function getApplyAngentData($applyAgentList){
        $User = D('User');
        $Forum = D('Forum');
        foreach($applyAgentList as $key => $value){
            //$applyAgentList[$key]['nickname'] = $User->getUserNicknameById($value['uid']);
            $applyAgentList[$key]['nickname'] = $User->getFieldById($value['uid'],'nickname');
            $applyAgentList[$key]['forumname'] = $Forum->getForumNameById($value['fid']);
            $applyAgentList[$key]['addtime'] = date('Y-m-d H:i:s',$value['addtime']);
        }
        return $applyAgentList;
    }
    //封装数据
    public function getReportDate($List,$User){
        foreach($List as $key => $value){
            $List[$key]['reportnickname'] = $User->getUserNickNameByUID($List[$key]['uid']);
            $List[$key]['reportednickname'] = $User->getUserNickNameByUID($List[$key]['touid']);
            $List[$key]['addtime'] = date('Y-m-d H:i:s',$List[$key]['addtime']);
        }
        return $List;
    }
}
<?php

namespace Home\Controller;

class ResponseController extends CommonController
{
    public function showResponse()
    {
        $fid = I('request.fid', null, 'intval');
        $type = I('request.type',null,'intval');
        if(empty($type)){
            $info = null;
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info,$code,$message);
            $this->ajaxReturn($return);
        }
        if (!empty($fid) || $fid != '') {
            $map['fid'] = $fid;
        }
        
        $map['status']    = 0;
        $map['type']      = $type;
        $responseModel = D('Response');
        $forumModel    = D('Forum');
        $count            = $responseModel->where($map)->count();
        $Page             = new \Think\Page($count, C('PAGE_LISTROWS'));
        parent::setPageConfig($Page);
        $show                 = $Page->show();
        $responseList = $responseModel->where($map)->order(array('id'=>'desc'))->limit($Page->firstRow.','.$Page->listRows)->select();
        $forums               = $forumModel->select();
        $this->assign('forums', $forums);
        $this->assign('page', $show);
        $this->assign('count', $count);

        foreach($responseList as $key=>$val){
            $responseList[$key]['subject'] = $this->getResponseSubject($type,$val['pid']);
            $responseList[$key]['forumname'] = $forumModel->getFieldById($val['fid'],'fansgroup');
            $responseList[$key]['username'] = getUserNicknameById($val['uid']);
        }
        
        $this->assign('responseList', $responseList);
        $this->display('Response/response');
    }

    public function getResponseSubject($type,$id){
        switch($type){
            case '1':
                $topicModel = D('Topic');
                $subject = $topicModel->getFieldById($id,'subject');
                return $subject;
                break;
            case '2':
                $activityModel = D('Activity');
                $subject = $activityModel->getFieldById($id,'subject');
                return $subject;
                break;
            case '3':
                $videoModel = D('Video');
                $subject = $videoModel->getFieldById($id,'title');
                return $subject;
                break;
            case '4':
                $forumModel = D('Forum');
                $subject = $forumModel->getFieldById($id,'fansgroup');  
                return $subject;
                break;
            default:
                return '';
                break;
        }
    }

    public function delResponseById()
    {
        $id               = I('request.id', null, '');
        $responseModel = D('Response');
        $responseModel->delete($id);
        $this->redirect('activityResponse');
    }
    public function deleteAllResponse()
    {
        $arr              = I('post.test');
        $responseModel = D('Response');
        for ($i = 0; $i < count($arr); $i++) {
            $responseModel->delete($arr[$i]);
        }
    }

    public function getActivityResponseData($activityResponseList)
    {
        $Forum    = D('Forum');
        $User     = D('User');
        $Activity = D('Activity');
        foreach ($activityResponseList as $key => $val) {
            $activityResponseList[$key]['forumname']       = $Forum->getFieldById($val['fid'], 'name');
            $activityResponseList[$key]['activitysubject'] = $Activity->getFieldById($val['aid'], 'subject');
            $activityResponseList[$key]['nickname']        = $User->getFieldById($val['uid'], 'nickname');
            $activityResponseList[$key]['time']            = date('Y-m-d H:i:s', $val['addtime']);
        }
        return $activityResponseList;
    }
}

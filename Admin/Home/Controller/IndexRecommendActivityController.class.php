<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/10/31
 * Time: 17:10
 */

namespace Home\Controller;


class IndexRecommendActivityController extends CommonController
{

    public function homeRecommendActivityList(){
        $IndexRecommendActivity = D('IndexRecommendActivity');
        $homeRecommendActivityList = $IndexRecommendActivity->getAllHomeRecommendActivity();
        $homeRecommendActivityList = $this->getHomeRecommendActivityListData($homeRecommendActivityList);
        $this->assign('homeRecommendActivityList',$homeRecommendActivityList);
        $this->display('homeRecommendActivity');
    }
    //得到已被推荐到首页活动的数量
    public function getHomeRA(){
        $IndexRecommendActivity = D('IndexRecommendActivity');
        $count = $IndexRecommendActivity->getHomeRACount();
        if($count >= 3){
            $this->ajaxReturn(false,'json');
        }else{
            $this->ajaxReturn(true,'json');
        }
    }

    public function updateHomeRA(){
        $IndexRecommendActivity = D('IndexRecommendActivity');
        $id = I('get.id',null,'');
        $arr = array('status'=>1);
        $IndexRecommendActivity->updateHRA($id,$arr);
        $this->redirect('IndexRecommendActivity/homeRecommendActivityList');
    }

    //设置首页推荐
    public function homeRecommendActivity(){
        $IndexRecommendActivity = D('IndexRecommendActivity');
        $aid = I('post.aid',null,'');
        $arr = array(
            'aid' => $aid
        );
        $id = $IndexRecommendActivity->add($arr);
        if($id > 0){
            $this->ajaxReturn(true,'json');
        }else{
            $this->ajaxReturn(false,'json');
        }
    }

    public function showHomeRA(){
        $IndexRecommendActivity = D('IndexRecommendActivity');
        $id = I('request.id',null,'');
        $IndexRecommendActivity = $IndexRecommendActivity->getRecommendActivity($id);
        $this->assign('IndexRecommendActivity',$IndexRecommendActivity);
        $this->display('showHomeRA');
    }

    public function saveUpdateHomeRAYS(){
        $IndexRecommendActivity = D('IndexRecommendActivity');
        $id = I('request.id',null,'');
        $aid = $IndexRecommendActivity->getFieldById($id,'aid');
        $order = I('request.order',null,'');
        $arr = array('order'=>$order,'aid'=>$aid);
        $IndexRecommendActivity->updateHRA($id,$arr);
        $this->redirect('IndexRecommendActivity/homeRecommendActivityList');
    }

    public function getHomeRecommendActivityListData($homeRecommendActivityList){
        $Activity = D('Activity');
        $Forum = D('Forum');
        $User = D('User');
        $District = D('District');
        foreach($homeRecommendActivityList as $key => $value){
            $activity = $Activity->getActivityById($value['aid']);
            $homeRecommendActivityList[$key]['aid'] = $value['aid'];
            $homeRecommendActivityList[$key]['subject'] = $activity['subject'];
            $homeRecommendActivityList[$key]['forumname'] = $Forum->getFieldById($activity['fid'],'name');
            $homeRecommendActivityList[$key]['nickname'] = $User->getFieldById($activity['uid'],'nickname');
            $homeRecommendActivityList[$key]['holdstart'] = date('Y-m-d',$activity['holdstart']);
            $homeRecommendActivityList[$key]['holdend'] = date('Y-m-d',$activity['holdend']);
            $homeRecommendActivityList[$key]['holdprovince'] = $District->getProvince($activity['holdprovince']);
            $homeRecommendActivityList[$key]['holdcity'] = $District->getCity($activity['holdcity']);
            $homeRecommendActivityList[$key]['type'] = C('ACTIVITY_TYPE_'.$activity['type']);
            $homeRecommendActivityList[$key]['enrollendtime'] = date('Y-m-d',$activity['enrollendtime']);
        }
        return $homeRecommendActivityList;
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/11/25
 * Time: 10:03
 */
namespace Home\Controller;

class PointController extends CommonController{
    //构造函数
    public function __construct(){
        parent::__construct();
    }

    //添加用户表总积分
    public function addUserTotalPoint($point,$uid=0){
        $userModel = D('User');
        $userPointModel = D('UserPoint');
        $userPointLogModel = D('UserPointLog');

        $dayTime = $this->date();
        $dateCondition['addtime']=array('between',array($dayTime['dayBegin'],$dayTime['dayEnd']));
        if(empty($uid)){
            $uid = $this->getUid();
            $dateCondition['uid']=$uid;
        }else{
            $dateCondition['uid']=$uid;
        }

        $total_point = $userPointModel->where($dateCondition)->sum('point');
        if($total_point<227){
            $userModel->where(array('uid'=>$uid))->setInc('point',$point);
            $userPointLogModel->add(array('uid'=>$uid,'addtime'=>time(),'point'=>$point,'type'=>0));
        }

    }

    //用于共同加分的函数
    public function addPoint($type,$wid,$sql){          
         $userPointTypeModel = D("UserPointType");
         $userPointTypeData =  $userPointTypeModel->getDataByPointType($type);
         $uid= $this->getUid();
         $userCount = $this->count($uid,$type);

         if($userCount<$userPointTypeData['pointnumlimit']){
         	$userPointModel = D('UserPoint');
         	$data['uid']=$uid;
            if($wid){
               $data[$sql]=$wid; 
            }
         	$data['addtime']=time();
         	$data['ip'] = get_client_ip(0,true);
         	$data['type']=$type;
         	$data['point']=$userPointTypeData['point'];
         	$userPointModel->addUserPointData($data);  
            return $userPointTypeData['point'];     	 
         }
     }
    //得到用户开始与结束的时间戳
    public function date(){    	 
		$time['dayBegin'] = strtotime('today');//当天开始时间戳
		$time['dayEnd'] =strtotime('today')+3600*24-1;//当天结束时间戳		 
        return $time;
    }

    //得到用户某种类型当天积分的次数
    public function count($uid,$type){
        $dayTime = $this->date();
        $condition['uid']=$uid;
        $condition['type']=$type;
        $condition['addtime']=array('between',array($dayTime['dayBegin'],$dayTime['dayEnd']));
        $userPointModel = D('UserPoint');
        $count=$userPointModel->getDataByCondition($condition);
        return $count;
    }

    //用于创建活动加分的函数
    public function createActivity($aid){
    	$type=3;        
    	$count=$this->addPoint($type,$aid,'aid');
        return $count;
    }
    //用于创建话题加分的函数
    public function createTopic($tid){
    	$type=4;
     	$count=$this->addPoint($type,$tid,'tid');
        return $count;
    }
    //用于签到活动加分的函数
    public function createSign($fid){
    	$type=6;        
    	$count=$this->addPoint($type,$fid,'fid');
        return $count;
    }
    //用于填写个人资料的加分函数
    public function createPersonMessage($profileid){
    	$profileid=$profileid;
    	$type=7;
    	$count=$this->addUserPoint($type,$profileid,'profileid');
        return $count;
    }
    //用于填写个人资料的加分函数,根据修改的个人信息参数，进行判断与加分
    public function addUserPoint($type,$profileid,$sql){          
         $userPointTypeModel = D("UserPointType");
         $userPointTypeData =  $userPointTypeModel->getDataByPointType($type);
         $uid=$this->getUid();
         $userCount = $this->count($uid,$type);
         $dateCondition[$sql]=$profileid;  
         $dateCondition['uid']=$uid;    
         $dateCondition['type']=$type;  
         if($userCount<$userPointTypeData['pointnumlimit']){
         	$userPointModel = D('UserPoint');
         	$result=$userPointModel->getMessageByCondition($dateCondition);
         	if(!$result){
	         	$dateCondition['addtime']=time();
	         	$dateCondition['ip'] = get_client_ip(0,true);
	         	$dateCondition['point']=$userPointTypeData['point'];
	         	$userPointModel->addUserPointData($dateCondition);
                return $userPointTypeData['point'];
         	}
         	         	 
         }
     }

    //用于活动报名成功
    public function activityRegistration($aid){
        $type=8;
        $count=$this->addUserPoint($type,$aid,'aid');
        return $count;
    }
     //用于活动通过审核
    public function activityHasPass($aid,$uid){
        $type=9;
        $aid=$aid;
        $count=$this->addActivityUserPoint($type,$aid,'aid',$uid);
        return $count;
    }

    public function addActivityUserPoint($type,$profileid,$sql,$uid){          
         $userPointTypeModel = D("UserPointType");
         $userPointTypeData =  $userPointTypeModel->getDataByPointType($type);
         $userCount = $this->count($uid,$type);
         $dateCondition[$sql]=$profileid;  
         $dateCondition['uid']=$uid;    
         $dateCondition['type']=$type;  
         if($userCount<$userPointTypeData['pointnumlimit']){
            $userPointModel = D('UserPoint');
            $result=$userPointModel->getMessageByCondition($dateCondition);
            if(!$result){
                $dateCondition['addtime']=time();
                $dateCondition['ip'] = get_client_ip(0,true);
                $dateCondition['point']=$userPointTypeData['point'];
                $userPointModel->addUserPointData($dateCondition);
                return $userPointTypeData['point'];
            }
                         
         }
     }
    public function addPointAboutFavor($type,$aid){
        $userPointTypeModel = D("UserPointType");
        $userPointTypeData =  $userPointTypeModel->getDataByPointType($type);
        $uid= $this->getUid();
        $userCount = $this->count($uid,$type);
        $userPointModel = D('UserPoint');
        //查看是否已经为某个活动点赞了
        $condition['type'] = $type;
        $condition['aid'] = $aid;
        $isfavor = $userPointModel->isFavor($condition);
        if(empty($isfavor) && $userCount<$userPointTypeData['pointnumlimit']){
            $data['uid']=$uid;
            $data['addtime']=time();
            $data['ip'] = get_client_ip(0,true);
            $data['type']=$type;
            $data['aid'] = $aid;
            $data['point']=$userPointTypeData['point'];
            $userPointModel->addUserPointData($data);
            $this->addUserTotalPoint($userPointTypeData['point']);
            return $userPointTypeData['point'];
        }else{
            return 0;
        }
    }
    //用于点赞加分的函数
    public function favorPoint($aid){
        $type = 2;
        return $this->addPointAboutFavor($type,$aid);
    }
    public function addPointAboutFavorForTopic($type,$tid){
        $userPointTypeModel = D("UserPointType");
        $userPointTypeData =  $userPointTypeModel->getDataByPointType($type);
        $uid= $this->getUid();
        $userCount = $this->count($uid,$type);
        $userPointModel = D('UserPoint');
        //查看是否已经为某个活动点赞了
        $condition['type'] = $type;
        $condition['tid'] = $tid;
        $isfavor = $userPointModel->isFavor($condition);
        if(empty($isfavor) && $userCount<$userPointTypeData['pointnumlimit']){
            $data['uid']=$uid;
            $data['addtime']=time();
            $data['ip'] = get_client_ip(0,true);
            $data['type']=$type;
            $data['tid'] = $tid;
            $data['point']=$userPointTypeData['point'];
            $userPointModel->addUserPointData($data);
            $this->addUserTotalPoint($userPointTypeData['point']);
            return $userPointTypeData['point'];
        }else{
            return 0;
        }
    }

    public function topicFavorAddPoint($tid){
        $type = 2;
        return $this->addPointAboutFavorForTopic($type,$tid);
    }

    public function topicAddPoint($type,$tid){
        $userPointTypeModel = D("UserPointType");
        $userPointTypeData =  $userPointTypeModel->getDataByPointType($type);
        $uid= $this->getUid();
        $userCount = $this->count($uid,$type);
        $userPointModel = D('UserPoint');
        //发表话题检测该用户是否已经对同一个话题加过分了如果工作室的话题已经加过分了则不予加分
        $condition['type'] = $type;
        $condition['tid'] = $tid;
        $isExixtTopic = $userPointModel->isExixtTopic($condition);
        if(empty($isExixtTopic) && $userCount<$userPointTypeData['pointnumlimit']){
            $data['uid']=$uid;
            $data['tid'] = $tid;
            $data['addtime']=time();
            $data['ip'] = get_client_ip(0,true);
            $data['type']=$type;
            $data['point']=$userPointTypeData['point'];
            $userPointModel->addUserPointData($data);
            $this->addUserTotalPoint($userPointTypeData['point']);
            return $userPointTypeData['point'];
        }else{
            return 0;
        }

    }
    //用户对话题进行回复
    //用于活动加精
    public function activityPlus($aid){
        $type=10;        
        $count=$this->addUserPoint($type,$aid,'aid');
        return $count;
    }
     //用于话题加精
    public function topicPlus($tid){
        $type=11;
        $count=$this->addUserPoint($type,$tid,'tid');
        return $count;
    }

    //用于绑定其它账号8手机\9微博\10QQ
    public function userBang($profileid){
        $type=13;
        $profileid=$profileid;
        $bangCon = $this->lookUserBangCondition($type);
         if(!$bangCon){         
             $count=$this->addUserPoint($type,$profileid,'profileid');
        }      
    }

    public function lookUserBangCondition($type){
        $con['uid'] = 1;
        $con['type']= $type;
        $model = M('UserPoint');
        $count=$model->where($con)->count();
        if($count>=2){
            return 1;
        }
    }
    //其他粉丝通过你分享的链接注册
    public function sharePoint($uid){        
        $type=12;
        $count=$this->addsharePoint($uid,$type);
    }
     public function addsharePoint($uid,$type){          
         $userPointTypeModel = D("UserPointType");
         $userPointTypeData =  $userPointTypeModel->getDataByPointType($type);
         $userCount = $this->count($uid,$type);
         if($userCount<$userPointTypeData['pointnumlimit']){
            $userPointModel = D('UserPoint');
            $data['uid']=$uid;
            $data['addtime']=time();
            $data['ip'] = get_client_ip(0,true);
            $data['type']=$type;
            $data['point']=$userPointTypeData['point'];
            $userPointModel->addUserPointData($data); 
            return $userPointTypeData['point'];          
         }
     }

     //分享话题、活动或星吧

     //分享活动
     public function shareActivityMain(){
         $type=5;
         $dateCondition['aid']=I('post.aid');
         $profileid=I('post.profileid');       
         $count=$this->shareUserPoint($type,$dateCondition,$profileid);
         if($count){
            $this->addUserTotalPoint($count);
            $this->ajaxReturn(array('point'=>$count));
         }

     }
     //分享话题
     public function shareTopicMain(){
         $type=5;
         $dateCondition['tid']=I('post.tid');
         $profileid=I('post.profileid');       
         $count=$this->shareUserPoint($type,$dateCondition,$profileid);
         if($count){
            $this->addUserTotalPoint($count);
            $this->ajaxReturn(array('point'=>$count));
         }

     }
     //分享星吧
     public function shareForumMain(){
         $type=5;
         $dateCondition['fid']=I('post.fid');
         $profileid=I('post.profileid');       
         $count=$this->shareUserPoint($type,$dateCondition,$profileid);
         if($count){
            $this->addUserTotalPoint($count);
            $this->ajaxReturn(array('point'=>$count));
         }

     }


     public function shareUserPoint($type,$dateCondition,$profileid){          
         $userPointTypeModel = D("UserPointType");
         $userPointTypeData =  $userPointTypeModel->getDataByPointType($type);
         $uid=$this->getUid();
         $dayTime = $this->date();
         $userCount = $this->count($uid,$type);
         $dateCondition['profileid']=$profileid; 
         $dateCondition['uid']=$uid;   
         $dateCondition['type']=$type;   
         $dateCondition['addtime']=array('between',array($dayTime['dayBegin'],$dayTime['dayEnd']));                 
         if($userCount<$userPointTypeData['pointnumlimit']){
            $userPointModel = D('UserPoint');
            $result=$userPointModel->getMessageByCondition($dateCondition);
            if(!$result){
                $dateCondition['addtime']=time();
                $dateCondition['ip'] = get_client_ip(0,true);
                $dateCondition['point']=$userPointTypeData['point'];
                $userPointModel->addUserPointData($dateCondition);
                return $userPointTypeData['point'];
            }
                         
         }
     }


     //用户对话题进行回复
    //发表回复 积分上限10/天 加分次数上限10/天 操作无限制
    public function publishTopicRestore($tid){
        $type = 1;
        return $this->topicAddPoint($type,$tid);
    }

    public function activityAddPoint($type,$aid){
        $userPointTypeModel = D("UserPointType");
        $userPointTypeData =  $userPointTypeModel->getDataByPointType($type);
        $uid= $this->getUid();
        $userCount = $this->count($uid,$type);
        $userPointModel = D('UserPoint');
        //发表话题检测该用户是否已经对同一个话题加过分了如果工作室的话题已经加过分了则不予加分
        $condition['type'] = $type;
        $condition['aid'] = $aid;
        $isExixtTopic = $userPointModel->isExixtTopic($condition);
        if(empty($isExixtTopic) && $userCount<$userPointTypeData['pointnumlimit']){
            $data['uid']=$uid;
            $data['aid'] = $aid;
            $data['addtime']=time();
            $data['ip'] = get_client_ip(0,true);
            $data['type']=$type;
            $data['point']=$userPointTypeData['point'];
            $userPointModel->addUserPointData($data);
            return $userPointTypeData['point'];
        }else{
            return 0;
        }

    }
    public function publishActivityRestore($aid){
        $type = 1;
        return $this->activityAddPoint($type,$aid);
    }
}
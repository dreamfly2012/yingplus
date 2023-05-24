<?php
/*** Created by PhpStorm.*/
namespace Home\Controller;
use Think\Controller;
/**
 * Class TopicController
 * @package Home\Controller
 *  查找话题的控制器
 */
class TopicController extends  CommonController{
    public function indexTopic(){
        $array=I('post.array');
        $self = I('post.self');
        session('array',$array);
        session('self',$self);
        $model = D('Topic');
        $count = $this->countData($model,$array,$self);
        $Page = new \Think\AjaxPage($count,C('PAGE_LISTROWS'),'indexTopicPage');
        $show = $Page->show();
        $result = $this->chooseData($model,$array,$self,$Page);
        $this->assign('page',$show);
        $this->assign('result',$result);
        $this->display('showCondition');
    }
    public function indexTopicPage(){
        $array= session('array');
        $self = session('self');
        $model = D('Topic');
        $count = $this->countData($model,$array,$self);
        $Page = new \Think\AjaxPage($count,C('PAGE_LISTROWS'),'indexTopicPage');
        $show = $Page->show();
        $result = $this->chooseData($model,$array,$self,$Page);
        $this->assign('page',$show);
        $this->assign('result',$result);
        $this->display('showCondition');
    }

    public function showCondition(){
        $this->display('topicIndex');
    }
    public function chooseData($model,$array,$is_build,$Page){
        switch($is_build){
            case 0: return $model->search($array,$is_build,$Page);
                break;
            case 1: return $model->canSearch($array,$is_build,$Page);
                break;
        }
    }

    public function countData($model,$array,$is_build){
        switch($is_build){
            case 0: return $model->countda($array,$is_build);
                break;
            case 1: return $model->canCount($array,$is_build);
                break;
        }
    }
    public function LookRepairTopic(){
        $model = D('Topic');
        $topic =$model->deleteTopic(I('post.array'));//toChange(I('post.array'));
        echo json_encode($topic);
    }

    public function showRepairTopic(){
        $this->display('deleteIndex');
    }
    public function setStatus(){
        $id = I('post.id');
        $model = D('Topic');
        $data = $model->getMessageById($id);
        //$this->commonReduceCountNum($data,'topics','createtopicnum','topiccreatenum');
        //$this->commonreduceNum($data,'deletetopicnum','topicdelnum');
        $model->deal($id);
        $this->sendContentToUser($id);
        echo '{"code":"2"}';
    }

    //恢复话题内容发送消息
    public function sendContentToUser($id){
        //session('user_id',1);
        $model = D('Topic');
        $sendId = $model->getSendContentId($id);
        $this->commonSend(C('REPAIR_TOPIC_STATUS'),$sendId['uid'],'恢复话题内容消息');
        if($sendId['admin_id']){
            $this->commonSend(C('REPAIR_TOPIC_DELETE'),$sendId['admin_id'],'恢复话题内容消息');
        }
    }
    //发送消息
    public function sendDeleteContentToUser($content,$id){
        $model = D('Topic');
        $sendId = $model->getSendContentId($id);
        $this->commonSend($content,$sendId['uid'],'话题消息');
    }
    //共有的删除减少
    public function commonCountNum($data,$fieldfirst,$fieldsecond,$fieldthree){
       $forumModel = D('Forum');
       $forumUserModel = D('ForumUser');
       //$userBehavior = D('UserBehavior');
       $forumModel->where(array('id'=>$data['fid']))->setDec($fieldfirst);//topics
       $forumUserModel->where(array('fid'=>$data['fid'],'uid'=>$data['uid']))->setDec($fieldsecond);//createtopicnum
       //$userBehavior->where(array('uid'=>$data['uid']))->setDec($fieldthree);//'topiccreatenum'                        
   }
   //共有的删除增加
   public function commonAddNum($data,$fieldsecond,$fieldthree){        
       $forumUserModel = D('ForumUser');
       //$userBehavior = D('UserBehavior');       
       $forumUserModel->where(array('fid'=>$data['fid'],'uid'=>$data['uid']))->setInc($fieldsecond);//createtopicnum
       //$userBehavior->where(array('uid'=>$data['uid']))->setInc($fieldthree);//'topiccreatenum'                   
   }
    //查找话题的删除
    public function deleteStatus(){
        $id = I('post.id');
        $model = D('Topic');
        $data = $model->getMessageById($id);
        $this->commonCountNum($data,'topics','createtopicnum','topiccreatenum');
        $this->commonAddNum($data,'deletetopicnum','topicdelnum');
        $model->setTopicStatusDetele($id);
        $this->sendDeleteContentToUser(C('DELETE_TOPIC_ADMIN'),$id);
        echo '{"code":"1"}';
    }
    
    //管理员加精话题
    public function plusDigest(){
        $id = I('post.id');
        $model = D('Topic');
        $model->setDigest($id);
        $this->sendDeleteContentToUser(C('SET_DIGEST'),$id);
        echo '{"code":"1"}';
    }

    //管理员取消加精--将经纪人与自己的加精取消
    public function cancelDigest(){
        $id = I('post.id');
        $model = D('Topic');
        $data = $model->getMessageById($id);
        if($data['isdigest']){     
            $this->commonReduceDigest($data,'digesttopicnum','topicdigestnum');
        } 
        $model->cancelDigest($id);
        $this->sendDeleteContentToUser(C('CANCEL_DIGEST'),$id);
        echo '{"code":"1"}';
    }

    public function commonReduceDigest($data,$fieldsecond,$fieldthree){       
       $forumUserModel = D('ForumUser');
       //$userBehavior = D('UserBehavior');
       $forumUserModel->where(array('fid'=>$data['fid'],'uid'=>$data['uid']))->setDec($fieldsecond);//createtopicnum
       //$userBehavior->where(array('uid'=>$data['uid']))->setDec($fieldthree);//'topiccreatenum'                        
    }

    //管理员将话题置顶
    public function toTopicTop(){
        $id = I('post.id');
        $model = D('Topic');
        $model->setIstop($id);
        $this->sendDeleteContentToUser(C('SET_TOPICTOP'),$id);
        echo '{"code":"1"}';
    }
    //管理员取消话题置顶
    public function cancelTopicTop(){
        $id = I('post.id');
        $model = D('Topic');
        $model->cancelIstop($id);
        $this->sendDeleteContentToUser(C('CANCEL_TOPICTOP'),$id);
        echo '{"code":"1"}';
    }
    //批量置顶
    public function topAllTopic(){
        $data = I('post.test');//$_POST['list'];
        foreach($data as $v){
            $this->TopicTop($v);
        }
        echo '{"code":"1"}';
    }
    public function  TopicTop($id){
        $model = D('Topic');
        $model->setIstop($id);
        $this->sendDeleteContentToUser(C('SET_TOPICTOP'),$id);
    }
    //批量取消置顶
    public function toCancelAllTop(){
        $data = I('post.test');//$_POST['list'];
        foreach($data as $v){
            $this->cancelAllTopicTop($v);
        }
        echo '{"code":"1"}';
    }
    public function cancelAllTopicTop($id){
        $model = D('Topic');
        $model->cancelIstop($id);
        $this->sendDeleteContentToUser(C('CANCEL_TOPICTOP'),$id);
    }

    //批量加精
    public function plusAllTopic(){
        $data = I('post.test');//$_POST['list'];
        foreach($data as $v){
            $this->Digest($v);
        }
        echo '{"code":"1"}';
    }
    public function Digest($id){
        $model = D('Topic');
        $data = $model->getMessageById($id);        
        $model->setDigest($id);
        $this->sendDeleteContentToUser(C('SET_DIGEST'),$id);
    }

    //批量取消加精
    public function toCancelPlusAllTopic(){
        $data = I('post.test');//$_POST['list'];
        foreach($data as $v){
            $this->toCancelDigest($v);
        }
        echo '{"code":"1"}';
    }
    public function toCancelDigest($id){
        $model = D('Topic');
         $data = $model->getMessageById($id);
        if($data['isdigest']){     
            $this->commonReduceDigest($data,'digesttopicnum','topicdigestnum');
        } 
        $model->cancelDigest($id);
        $this->sendDeleteContentToUser(C('CANCEL_DIGEST'),$id);
    }


    //批量删除
    public function deleteAllTopic(){
        $data = I('post.test');//$_POST['list'];
        foreach($data as $v){
            $this->delete($v);
        }
        echo '{"code":"1"}';
    }
    public function delete($id){
        $model = D('Topic');
        $data = $model->getMessageById($id);
        $this->commonCountNum($data,'topics','createtopicnum','topiccreatenum');
        $this->commonAddNum($data,'deletetopicnum','topicdelnum');
        $model->setTopicStatusDetele($id);
        $this->sendDeleteContentToUser(C('DELETE_TOPIC_ADMIN'),$id);
    }
    //批量话题恢复
    public function repairAllTopic(){
        $data = I('post.test');//$_POST['list'];
        foreach($data as $v){
            $this->repair($v);
        }
        echo '{"code":"1"}';
    }
    public function repair($id){
        $model = D('Topic');
        $data = $model->getMessageById($id);
        $this->commonReduceCountNum($data,'topics','createtopicnum','topiccreatenum');
        $this->commonreduceNum($data,'deletetopicnum','topicdelnum');
        $model->deal($id);
        $this->sendContentToUser($id);
    }
    //共有的恢复的增加
    public function commonReduceCountNum($data,$fieldfirst,$fieldsecond,$fieldthree){
       $forumModel = D('Forum');
       $forumUserModel = D('ForumUser');
       //$userBehavior = D('UserBehavior');
       $forumModel->where(array('id'=>$data['fid']))->setInc($fieldfirst);//topics
       $forumUserModel->where(array('fid'=>$data['fid'],'uid'=>$data['uid']))->setInc($fieldsecond);//createtopicnum
       //$userBehavior->where(array('uid'=>$data['uid']))->setInc($fieldthree);//'topiccreatenum'                        
   }
   //共有的恢复减少
   public function commonreduceNum($data,$fieldsecond,$fieldthree){        
       $forumUserModel = D('ForumUser');
       //$userBehavior = D('UserBehavior');       
       $forumUserModel->where(array('fid'=>$data['fid'],'uid'=>$data['uid']))->setDec($fieldsecond);//createtopicnum
       //$userBehavior->where(array('uid'=>$data['uid']))->setDec($fieldthree);//'topiccreatenum'                   
   }
    public function testCount(){
        $model = M('Topic');
        echo $model->count();
    }

   public function editTopic($id){
      $topicModel = D('Topic');
      $topicResult = $topicModel->selectTopic($id);
      $this->assign('topic',$topicResult);
      $this->display('topicEdit');
   }

   public function editTopicPut(){
      $id = I('get.id');
      $data = $_POST['array'];
      $topicModel = D('Topic');     
      $topicModel->saveTopicById($id,$data);
      $this->showCondition();
   }

   public function addTopicContent(){
      $forumModel = M('Forum');
      $forumAllName = $forumModel->select();
      $this->assign('forumName',$forumAllName);
      $this->display('topicAdd');
   }

   public function addTopicContentPut(){
      $topicModel = D('Topic');
      $username = I('post.nickname');
      $array = I('post.array');
      $array['content'] = $_POST['content'];
      $uid = $this->getUserIdByName($username);
      if($topicModel->autoCheckToken($_POST)){
          if($uid){
             $array['uid'] = $uid;
             $array['addtime'] = time();
             $topicModel->addTopicData($array);
             $this->showCondition();
           }else{
              $this->error('用户不存在',"addTopicContent");
          }
      }
       $this->showCondition();     
   }
   public function getUserIdByName($name){
       $userModel = M('User');
       $condition['nickname']=$name;
       $userData = $userModel->where($condition)->find();
       return $userData['id'];
   }
     
}
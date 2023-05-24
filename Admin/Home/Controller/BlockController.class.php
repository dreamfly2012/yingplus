<?php
/*** Created by PhpStorm.*/
namespace Home\Controller;
use Think\Controller;
/**
 * Class TopicController
 * @package Home\Controller
 *  用户ip管理控制器
 */
class BlockController extends  CommonController{
      public function blockIndex(){
      	$blockModel = D('Block');
      	$count = $blockModel->count();
        $Page = new \Think\Page($count,'10');
        $blockModelData = $blockModel->limit($Page->firstRow.','.$Page->listRows)
                     ->select();  
        $show = $Page->show();
        $this->assign('page',$show);     
      	$this->assign('block',$blockModelData);
      	$this->display('blockIndex');
      }

      public function deleteDataById(){
      	 $id = I('post.id');
      	 $blockModel = D('Block');
      	 $blockModel->delectBlockId($id);
      	 $this->ajaxReturn(array('content'=>'1'));
     }

     public function addBlockDataIndex(){
         $this->display('addBlockIndex');  
     }

     public function addBlockData(){
         $array['uid'] = I('post.uid');
         $array['ip'] = I('post.ip');
         $blockModel = M('Block');
         if($blockModel->autoCheckToken($_POST)){
             $blockModel->add($array); 
          }
           $this->blockIndex();
      }

     public function userBlockSearch(){
     	$keyword = I('post.search',null,'trim');;
     	$userModel = D('Block');
        $result=$userModel->userShow($keyword);
        $this->ajaxReturn(array('user'=>$result));
     }
}
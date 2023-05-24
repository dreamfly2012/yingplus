<?php
/*** Created by PhpStorm.*/
namespace Home\Controller;
use Think\Controller;
/**
 * Class TopicController
 * @package Home\Controller
 *  查找话题的控制器
 */
class AdminMailListController extends  CommonController{
	public function showEmailList(){
		$adminEmailModel = D('AdminEmail');
		$adminEmailData = $adminEmailModel->select();
		$this->assign('adminData',$adminEmailData);
		$this->display('adminEmailIndex');
	}

	public function addAdminEmailData(){
		$arr['email'] = I('post.email_name');
		$adminEmailModel = D('AdminEmail');
		if($adminEmailModel->autoCheckToken($_POST)){
			$adminEmailModel->add($arr);
		}		
		$this->showEmailList();
	}

	public function updateAdminEmailData(){
		$con['id'] = I('post.email_update_id');
        $arr['email'] = I('post.email_update');
        $adminEmailModel = D('AdminEmail');
        $adminEmailModel->where(array('id'=>$con['id']))->save($arr);
        $this->showEmailList();
	}

	public function deleteAdminDate(){
		$id=I('request.id',null,'intval');
		$adminEmailModel = D('AdminEmail');
        $adminEmailModel->where(array('id'=>$id))->delete();
        $this->ajaxReturn(array('status'=>1));
	}
}
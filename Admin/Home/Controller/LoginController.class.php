<?php
namespace Home\Controller;
use Think\Controller;

 class LoginController extends  CommonController{

     public function index(){
         $this->display('login');
     }

     public function handleAdminLogin(){
         $username = I('post.username',null,'');
         $password = I('post.password',null,'md5');
         $adminModel = D('Admin');
         $rules = array(
             array('username','require','用户名不能为空！'),
             array('password','require','密码不能为空！'),
         );
         if(!$adminModel->validate($rules)->create()){
             $this->error($adminModel->getError());
         }else{
             $result = $adminModel->checkPassword($username,$password);
             if($result){
                 session('user_id',$result);
                 $this->success('登录成功',U('Index/index'));
             }else{
                 $this->error('登录失败');
             }
         }
     }
     //退出账号
     public function logout(){
         session_destroy();
         $this->redirect('index');
     }



 }
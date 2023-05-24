<?php
namespace Home\Controller;

use Think\Controller;
class JobRemandController extends CommonController{
    public function index(){
        $model = D('OperateLog');
        $count = $model->count();
        $Page = new \Think\Page($count,C('PAGE_LISTROWS'));
        parent::setPageConfig($Page);
        $show = $Page->show();
        $operate=$model->getLimitOperate($Page);
        $this->assign('page',$show);
        $this->assign('operate',$operate);
        $this->display('jobRemandIndex');
    }

    public function postData(){
        $array = I('post.array');
        session('operate',$array);
        $model = D('OperateLog');
        $count =$model->getCount($array);
        $Page = new \Think\AjaxPage($count,C('PAGE_LISTROWS'),'operate');
        $show = $Page->show();
        $operate = $model->getOperateLog($array,$Page);
        $this->assign('page',$show);
        $this->assign('operate',$operate);
        $this->display('show');
    }
    public function operate(){
        $array=session('operate');
        $model = D('OperateLog');
        $count =$model->getCount($array);
        $Page = new \Think\AjaxPage($count,C('PAGE_LISTROWS'),'operate');
        $show = $Page->show();
        $operate = $model->getOperateLog($array,$Page);
        $this->assign('page',$show);
        $this->assign('operate',$operate);
        $this->display('show');
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/10/30
 * Time: 13:35
 */

namespace Home\Controller;


class HelpController extends CommonController
{

    //ÎÄµµÖÐÐÄ
    public function index(){

        $HelpProfile = D('HelpProfile');

        $count = $HelpProfile->getCount();
        $Page = new \Think\Page($count,C('PAGE_LISTROWS'));
        parent::setPageConfig($Page);
        $show = $Page->show();
        $helpProfiles = $HelpProfile->getAllHelpProfile($Page);
        $helpProfiles = $this->getHelpProfileData($helpProfiles);
        $this->assign('helpProfiles',$helpProfiles);
        $this->assign('page',$show);
        $this->display();
    }

    public function help(){
        $Help = D('Help');
        $help = $Help->select();
        $this->assign('help',$help);
        $this->display('help');
    }
    public function addhelp(){
        $this->display('addHelp');
    }
    public function addHelpType(){
        $typename = I('post.typename',null,'');
        $Help = D('Help');
        $arr = array('typename'=>$typename);
        $Help->add($arr);
        $this->redirect('Help/help');
    }
    public function addProfile(){
        $Help = D('Help');

        $help = $Help->select();
        $this->assign('help',$help);
        $this->display('add');
    }

    public function updateHelp(){
        $Help = D('Help');
        $id = I('get.id',null,'');

        $help = $Help->getHelpById($id);
        $this->assign('help',$help);
        $this->display('updateHelp');
    }

    public function updateHelpType(){
        $Help = D('Help');
        $id = I('post.id',null,'');
        $typename = I('post.typename',null,'');
        $arr = array(
            'typename' => $typename
        );
        $Help->updateHelp($id,$arr);
        $this->redirect('Help/help');
    }

    public function addHelpProfile(){
        $HelpProfile = D('HelpProfile');
        $hid = I('post.hid',null,'');
        $title = I('post.title',null,'');
        $content = I('post.editorValue',null,'');
        $arr = array(
            'hid' => $hid,
            'title' => $title,
            'content' => $content
        );
        $HelpProfile->add($arr);
        $this->redirect('Help/index');
    }
    public function editHelp(){

        $id = I('get.id',null,'');
        $HelpProfile = D('HelpProfile');

        $Help = D('Help');

        $help = $Help->select();
        $helpProfile = $HelpProfile->getHelpById($id);
        $helpProfile = $this->getHelpData($helpProfile);
        $this->assign('helpProfile',$helpProfile);
        $this->assign('help',$help);
        $this->display('editHelp');
    }

    public function deleteProfile(){
        $HelpProfile = D('HelpProfile');
        $id = I('get.id',null,'');
        $arr = array(
           'status' =>1
        );

        $HelpProfile->update($id,$arr);
        $this->redirect('Help/index');
    }
    public function saveHelpProfile(){
        $HelpProfile = D('HelpProfile');
        $id = I('post.id',null,'');
        $hid = I('post.hid',null,'');
        $title = I('post.title',null,'');
        $content = I('post.editorValue',null,'');
        $arr = array(
            'hid' => $hid,
            'title' => $title,
            'content' => $content
        );
        $HelpProfile->update($id,$arr);
        $this->redirect('index');
    }
    public function getHelpProfileData($helpProfiles){
        $Help = D('Help');
        foreach($helpProfiles as $key => $value){
            $helpProfiles[$key]['addtime'] = date('Y-m-d H:i:s',$value['addtime']);
            $helpProfiles[$key]['typename'] = $Help->getFieldById($helpProfiles[$key]['hid'],'typename');
        }
        return $helpProfiles;
    }

    public function getHelpData($helpProfile){
        $Help = D('Help');
        $helpProfile['addtime'] = date('Y-m-d H:i:s',$helpProfile['addtime']);
        $helpProfile['typename'] = $Help->getFieldById($helpProfile['hid'],'typename');
        return $helpProfile;
    }


}
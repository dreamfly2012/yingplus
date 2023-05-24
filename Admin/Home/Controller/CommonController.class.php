<?php
namespace Home\Controller;

use Think\Controller;

class CommonController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->setTheme();
        //在此处判断用户是否已经登录，如没有则跳转到登录页面
        $this->checkUser();
    }

    //构建ajax返回数据
    public function buildReturn($info, $code, $message) {
        return array(
            'data' => array(
                'code' => $code,
                'message' => $message,
                'info' => $info,
            ),
        );
    }

    public function checkUser()
    {
        if (session('user_id') == null) {
            if (!(CONTROLLER_NAME == 'Login')) {
                $this->redirect('Login/index');
            }

        } else {
            $uid = session('user_id');
            $Admin = D('Admin');
            $nickname = $Admin->getFieldById($uid, 'nickname');
            $photo = $Admin->getFieldById($uid, 'photo');
            $this->assign('photo', $photo);
            $this->assign('nickname', $nickname);
        }
    }

    public function setTheme($theme = "")
    {
        empty($theme) ? $this->theme('default') : $this->theme($theme);
    }

    public function _empty()
    {
        echo '页面不存在！！';
    }

    //设置分页主题
    public function setPageConfig($Page)
    {
        $Page->setConfig('prev', '上一页');
        $Page->setConfig('next', '下一页');
        $Page->setConfig('first', '首页');
        $Page->setConfig('last', '末页');
        $Page->setConfig('theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE%');
    }

    //发送消息的方法
    public function commonSend($content, $touid, $subject = "", $fid = 0)
    {
        $admin = D('Admin');
        $user = D('User');
        $addtime = time();
        $data['uid'] = session('user_id');
        $data['username'] = $admin->getAdminNickName($admin, session('user_id'));
        $data['subject'] = $subject;
        $data['fid'] = $fid;
        $data['content'] = $content;
        $data['touid'] = $touid;
        $data['addtime'] = $addtime;
        $data['tousername'] = $user->getUserNickname($user, $touid);
        $model = D('Message');
        $model->saveMessage($data);
    }

    //图片上传的初始化工作
    public function upload()
    {
        $upload = new \Think\Upload();
        $upload->maxSize = 3145728;
        $upload->exts = array('jpg', 'gif', 'png');
        $upload->savePath = '/avatar/';
        return $upload;
    }

    public function sendUserContent()
    {
        Vendor('phpmailer.sendMailCommon');
        $sendClass = new \sendMailCommon();
        $sendClass->sendMailCommonfun();
    }


}
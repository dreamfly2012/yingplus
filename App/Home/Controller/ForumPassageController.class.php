<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/3/31
 * Time: 18:23
 */

namespace Home\Controller;

class ForumPassageController extends CommonController{
    public function add(){
        $uid = $this->getUid();
        $content = I('request.content',null);
        if(empty($uid)){
            $info = 'no_login';
            $code = 2;
            $message = C('no_login');
            $return = $this->buildReturn($info,$code,$message);
            $this->ajaxReturn($return);
        }

        if(empty($content)){
            $info = 'parameter_invalid';
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info,$code,$message);
            $this->ajaxReturn($return);
        }

        if(iconv_strlen($content,'utf-8')>60){
            $info = 'length_less';
            $code = 1;
            $message = '输入的内容过多';
            $return = $this->buildReturn($info,$code,$message);
            $this->ajaxReturn($return);
        }

        $forumPassageModel = D('ForumPassage');
        $data['uid'] = $uid;
        $data['content'] = $content;
        $data['addtime'] = time();
        $id = $forumPassageModel->add($data);
        $info['id'] = $id;
        $info['content'] = $content;
        $info['username'] = getUserNicknameById($uid);
        

        $code = session('weibo_login');
        if(!empty($code)){
            $name = uniqid();
            generateImg ( realpath(__ROOT__).'/Public/default/letter_img/source/tangxiaotian.jpg', $content,$name,30,500);
            $weibo = new WeiboLoginController($code, C('SINA_CLIENT_ID'), C('SINA_CLIENT_SECRET'), C('SINA_REDIRECT_URL'));
            $content = "#一句话,写给你喜欢却不能在一起的人#我参加了#夏有乔木 #之#唐小天#（#韩庚#饰）电影专题活动。写出你最后的告白http://www.xiayouqiaomu.com!";
            $pic = '@'.realpath(__ROOT__).'/Public/default/letter_img/'.$name.'.jpg;type=image/png;';
            $weibo->sendWeibo($content,$pic);
        }
        $code = 0;
        $message = '添加一段话成功';
        $return = $this->buildReturn($info,$code,$message);
        $this->ajaxReturn($return);
    }


    public function temp_add(){
        $nickname = I('request.username',null);
        $content = I('request.content',null);
        if(empty($nickname)){
            $info = 'parameter_invalid';
            $code = 1;
            $message = '用户名不能为空';
            $return = $this->buildReturn($info,$code,$message);
            $this->ajaxReturn($return);
        }

        if(empty($content)){
            $info = 'parameter_invalid';
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info,$code,$message);
            $this->ajaxReturn($return);
        }

        if(iconv_strlen($content,'utf-8')>60){
            $info = 'length_less';
            $code = 1;
            $message = '输入的内容过多';
            $return = $this->buildReturn($info,$code,$message);
            $this->ajaxReturn($return);
        }

        $forumPassageModel = D('ForumPassage');
        $userModel = D('User');
        $user = $userModel->where(array('nickname'=>$nickname))->find();
        $data['uid'] = $user['id'];
        $data['content'] = $content;
        $data['addtime'] = time();
        $id = $forumPassageModel->add($data);
        $info['id'] = $id;
        $info['content'] = $content;
        $info['username'] = getUserNicknameById($uid);
        
        $code = session('weibo_login');
        if(!empty($code)){
            $name = uniqid();
            generateImg ( realpath(__ROOT__).'/Public/default/letter_img/source/tangxiaotian.jpg', $content,$name);
            $code = $_SESSION['weibo_login'];
            $client_id = "255864200";
            $client_secret = "0525093ed8f51825bd8b44bf72d13d21";
            $redirect_uri = "http://www.yingplus.cc";
            $share_url = "http://".$_SERVER['HTTP_HOST']."/control/index.php?starID=".$starID."&lid=".$letterInfo['lid'].'&pid='.$letterInfo['pid'].'#lovewall';
            $weibo = new WeiboLoginController($code, C('SINA_CLIENT_ID'), C('SINA_CLIENT_SECRET'), C('SINA_REDIRECT_URL'));
            $text = "#一句话,写给你喜欢却不能在一起的人#我参加了#夏有乔木 #之#唐小天#（#韩庚#饰）电影专题活动。写出你最后的告白www.xiayouqiaomu.com!";
            $content = $text.$share_url;
            $pic = '@'.realpath('/Public/default/letter_img/'.$name.'.png').';type=image/png;';
            $weibo->sendWeibo($content,$pic);
        }
        $code = 0;
        $message = '添加一段话成功';
        $return = $this->buildReturn($info,$code,$message);
        $this->ajaxReturn($return);
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/8/14
 * Time: 9:57
 */

namespace Home\Controller;
class QQLoginController extends CommonController{

    var $code;
    var $client_id;
    var $client_secret;
    var $data;
    var $redirect_uri;
    var $access_token;
    var $openId;
    public function __construct($code,$client_id,$client_secret,$redirect_uri){
        $this->code = $code;
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->redirect_uri = $redirect_uri;
        $this->data = array('client_id'=>$this->client_id, 'client_secret'=>$this->client_secret, 'code'=>$this->code);
    }
    //这个方法用于获取access_token
    public function getAccess_token(){
        $url = 'https://graph.qq.com/oauth2.0/token?grant_type=authorization_code&client_id='.$this->data['client_id'].'&client_secret='.$this->data['client_secret'].'&code='.$this->data['code'].'&redirect_uri='.$this->redirect_uri;
        $access_token = file_get_contents($url);
        $access_token = explode("&",$access_token);
        $access_token = explode("=",$access_token[0]);
        $this->access_token = $access_token[1];
        return $access_token[1];
    }
    //得到openid
    public function getOpenId(){
        $access_token = $this->getAccess_token();
        $openidUrl = 'https://graph.qq.com/oauth2.0/me?access_token='.$access_token;
        $openid_info = file_get_contents($openidUrl);
        $openid_info = str_replace("callback","", $openid_info);
        $openid_info = str_replace("(","",$openid_info);
        $openid_info = str_replace(");","",$openid_info);
        $obj = json_decode($openid_info);
        return $obj->openid;
    }
    //得到Qq用户的信息
    public function getUserInfo(){
        $openid = $this->getOpenId();
        $access_token = $this->access_token;
        $user_info_url = 'https://graph.qq.com/user/get_user_info?access_token='. $access_token.'&oauth_consumer_key='.$this->data['client_id'].'&openid='.$openid;
        $user_info = file_get_contents($user_info_url);
        $user = json_decode($user_info);
        $userArr = array(
            'openId' => $openid,
            'nickname' => $user->nickname,
            'gender' => $user->gender,
            'province' => $user->province,
            'city' => $user->city,
            'figureurl' => $user->figureurl
        );
        return $userArr;
    }
}
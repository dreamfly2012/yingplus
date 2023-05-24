<?php
/**
 * Created by PhpStorm.
 * User: yinsheng
 * Date: 2015/8/14
 * Time: 14:38
 */

namespace Home\Controller;
class WeiboLoginController extends CommonController{

    var $code;
    var $client_id;
    var $client_secret;
    var $data;
    var $redirect_uri;
    public function __construct($code,$client_id,$client_secret,$redirect_uri){
        $this->code = $code;
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->redirect_uri = $redirect_uri;
        $this->data = array('client_id'=>$this->client_id, 'client_secret'=>$this->client_secret, 'code'=>$this->code);
    }
    //得到用户id
    public  function getInfo(){
        $url = "https://api.weibo.com/oauth2/access_token";
        $data = array(
            'client_id' => $this->data['client_id'] ,
            'client_secret' => $this->data['client_secret'] ,
            'grant_type'=>'authorization_code',
            'redirect_uri'=>$this->redirect_uri,
            'code' =>$this->data['code']
        );

        $info = WeiboLoginController::HttpPost($url,$data);

        $info = json_decode($info);

        return $info;
    }

    //得到用户所有的信息
    public  function getUserInfo(){

        $info = $this->getInfo();
        if(empty($info)){
            return null;
        }
        $uid = $info->uid;
        $access_token =$info->access_token;
        session('access_token',$access_token);
        $userUrl = 'https://api.weibo.com/2/users/show.json?source='.$this->client_id.'&access_token='.$access_token.'&uid='.$uid;

        $user_info = file_get_contents($userUrl);
        $user_info = mb_convert_encoding( $user_info, 'UTF-8', 'UTF-8,GBK,GB2312,BIG5' );
        $obj = json_decode($user_info);
        return $obj;
    }
    //这个函数用来根据url返回数据信息
    public static function HttpPost($url, $post = null) {
        if (is_array($post)) {
            ksort($post);
            $content = http_build_query($post);
            $content_length = strlen($content);
            $options = array(
                'http' => array(
                    'method' => 'POST',
                    'header' => "Content-type: application/x-www-form-urlencoded\r\n" . "Content-length: $content_length\r\n",
                    'content' => $content
                )
            );
            return file_get_contents($url, false, stream_context_create($options));
        }
    }
    //用户微博发送信息
    public function sendWeibo($content,$picture){
        $access_token = session('access_token');
        //$access_token  = isset($access_token) ? $access_token : null;

        if(empty($access_token)){
            return null;
        }

        $data = array(
            'status'=>$content,
            'pic'=>$picture,
            'access_token'=>$access_token
        );

        $userUrl = 'https://api.weibo.com/2/statuses/upload.json';

        $info = WeiboLoginController::HttpMultipartPost($userUrl ,$data);

    }

    public static function HttpMultipartPost($url, $post = null) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

        $result = curl_exec($ch);
        curl_close($ch);
        
        return $result;
    }
}
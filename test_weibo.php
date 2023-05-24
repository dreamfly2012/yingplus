<?php
/**
 * @author 尹盛
 * @name Weibo
 * @version 1.0
 * @time 2015/7/5
 * @deprecated 这个类主要用于处理微博登录的类
 */
class Weibo
{
    public $code;
    public $client_id;
    public $client_secret;
    public $data;
    public $redirect_uri;
    public function __construct($code, $client_id, $client_secret, $redirect_uri)
    {
        $this->code          = $code;
        $this->client_id     = $client_id;
        $this->client_secret = $client_secret;
        $this->redirect_uri  = $redirect_uri;
        $this->data          = array('client_id' => $this->client_id, 'client_secret' => $this->client_secret, 'code' => $this->code);
    }

    public function sendWeibo($access_token,$content, $picture)
    {
        $data = array(
            'status'       => $content,
            'pic'          => $picture,
            'access_token' => $access_token,
        );

        $userUrl = 'https://api.weibo.com/2/statuses/upload.json';

        $info = Weibo::HttpMultipartPost($userUrl, $data);

        //print_R($info);

    }

    //得到用户id
    public function getInfo()
    {
        $url  = "https://api.weibo.com/oauth2/access_token";
        $data = array(
            'client_id' => $this->data['client_id'], 'client_secret' => $this->data['client_secret'], 'grant_type' => 'authorization_code', 'redirect_uri' => $this->redirect_uri, 'code' => $this->data['code'],
        );

        $info = Weibo::HttpPost($url, $data);

        $info = json_decode($info);

        return $info;
    }

    //得到用户所有的信息
    public function getUserInfo()
    {

        $info = $this->getInfo();
        if (empty($info)) {
            return null;
        }
        $uid          = $info->uid;
        $access_token = $info->access_token;
        session_start();
        $_SESSION['access_token'] = $access_token;
        $userUrl = 'https://api.weibo.com/2/users/show.json?source='.$this->client_id.'&access_token='.$access_token.'&uid='.$uid;

        $user_info = file_get_contents($userUrl);
        
        $user_info = mb_convert_encoding($user_info, 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');
       
        $obj       = json_decode($user_info);
        return $obj;
    }
    
    //这个函数用来根据url返回数据信息
    public static function HttpPost($url, $post = null)
    {
        if (is_array($post)) {
            ksort($post);
            $content        = http_build_query($post);
            $content_length = strlen($content);
            $options        = array(
                'http' => array(
                    'method'  => 'POST',
                    'header'  =>
                    "Content-type: application/x-www-form-urlencoded\r\n" .
                    "Content-length: $content_length\r\n",
                    'content' => $content,
                ),
            );
            return file_get_contents($url, false, stream_context_create($options));
        }
    }

    public static function HttpMultipartPost($url, $post = null)
    {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        //curl_setopt($ch, CURLOPT_UPLOAD, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        //$headers = array("Content-Type:multipart/form-data"); // cURL headers for file uploading
        //$postfields = array("filedata" => "@$filedata", "filename" => $filename);
        curl_exec($ch);
        curl_close($ch);
    }

}

//分享图片生成函数
function generateImg($source, $text, $name, $fontsize = 12, $setwidth = 600)
{
    $font = 'letter_img/source/msyh.ttf';

    $img = 'letter_img/' . $name . '.jpg';
   
    $main = imagecreatefromjpeg($source);

    
    $width  = imagesx($main);
    $height = imagesy($main);

    $target = imagecreatetruecolor($width, $height);

    $white = imagecolorallocate($target, 255, 255, 255);
    $black = imagecolorallocate($target, 0, 0, 0);
    imagefill($target, 0, 0, $white);

    imagecopyresampled($target, $main, 0, 0, 0, 0, $width, $height, $width, $height);

    mb_internal_encoding("UTF-8"); // 设置编码

    $text = autowrap($fontsize, 0, $font, $text, $setwidth);

    imagettftext($target, $fontsize, 0, 60, 100, $black, $font, $text);
    imagepng($target, $img, 9 );
    imagedestroy($main);
    imagedestroy($target);

    return $img;
}

function autowrap($fontsize, $angle, $fontface, $string, $width)
{
    // 这几个变量分别是 字体大小, 角度, 字体名称, 字符串, 预设宽度
    $content = "";
    // 将字符串拆分成一个个单字 保存到数组 letter 中
    for ($i = 0; $i < mb_strlen($string); $i++) {
        $letter[] = mb_substr($string, $i, 1);
    }

    foreach ($letter as $l) {
        $teststr = $content . $l;
        $testbox = imagettfbbox($fontsize, $angle, $fontface, $teststr);
        // 判断拼接后的字符串是否超过预设的宽度
        if (($testbox[2] > $width) && ($content !== "")) {
            $content .= "\n";
        }
        $content .= $l;
    }

    return $content;
}

function singleWordToArray($str, $spaces = true)
{
    $n   = 0;
    $len = strlen($str);
    $tmp = array();
    while ($n < $len) {
        if (ord(substr($str, $n, 1)) > 0xa0) {
            $tmp[] = substr($str, $n, 2);
            $n++;
        } else {
            $char = trim(substr($str, $n, 1));
            if ($spaces) {
                $tmp[] = $char;
            } else {
                if ($char != '') {
                    $tmp[] = $char;
                }

            }
        }
        $n++;
    } //end while
    return $tmp;
}


function unicode_decode($unistr, $encoding = 'UTF-8', $prefix = '&#', $postfix = ';') {
    $arruni = explode($prefix, $unistr);
    $unistr = '';
    for($i = 1, $len = count($arruni); $i < $len; $i++) {
        if (strlen($postfix) > 0) {
            $arruni[$i] = substr($arruni[$i], 0, strlen($arruni[$i]) - strlen($postfix));
        } 
        $temp = intval($arruni[$i]);
        $unistr .= ($temp < 256) ? chr(0) . chr($temp) : chr($temp / 256) . chr($temp % 256);
    } 
    return iconv('UCS-2', $encoding, $unistr);
}

function replace_unicode_escape_sequence($match) {
  return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
}



echo 'start';
$code          = $_REQUEST['code'];
$content       = $_REQUEST['content'];

$content = preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $content );

$name = uniqid();
generateImg (realpath('letter_img/source/tangxiaotian.jpg'), $content,$name,30,500);
$client_id     = "255864200";
$client_secret = "0525093ed8f51825bd8b44bf72d13d21";
$redirect_uri  = "http://www.yingplus.cc";
$weibo         = new Weibo($code, $client_id, $client_secret, $redirect_uri);
$userinfo = $weibo->getUserInfo();
$weiboid = $userinfo->id;
$access_token = $_SESSION['access_token'];

$con = mysql_connect('localhost','root','xing123123');

if(!$con){
    die('connect error');
}

mysql_select_db('yingplus');


$sql = "select `id` from yj_user where `weibo` = '".$weiboid."'";

$result = mysql_query($sql);

$res = mysql_fetch_row($result);

$uid = $res[0];
$addtime = time();

//添加一句话
$sql2 = "insert into yj_forum_passage set `uid` = '".$uid."',`content`='".$content."',`addtime`='".$addtime."'";

mysql_query($sql2);



$text          = "#一句话,写给你喜欢却不能在一起的人#我参加了#夏有乔木 #之#唐小天#（#韩庚#饰）电影专题活动。写出你最后的告白http://www.xiayouqiaomu.com!";
$content       = "$text";
$pic = '@'.realpath('letter_img/'.$name.'.jpg').';type=image/png;';
//$pic[] = '@'.realpath('control/letter_img/8_1_fkhert_line4_row3.png').';type=image/png;';

//$picture = 'http://s6.sinaimg.cn/thumb180/6c0c0bb3tdf7bdeae9c65';
//$pic = file_get_contents($picture);
$weibo->sendWeibo($access_token,$content, $pic);

echo 'finished';

//https://api.weibo.com/oauth2/authorize?client_id=255864200&redirect_uri=http://www.yingplus.cc/test_weibo.php?response_type=code

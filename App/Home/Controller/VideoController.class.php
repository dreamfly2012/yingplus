<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/12/30
 * Time: 9:32
 */

namespace Home\Controller;

class VideoController extends CommonController
{

    public function index()
    {
        $uid = session('uid');
        $vid = I('post.vid', null, 'intval');
        $pid = I('post.pid', null, 'intval');
        $User = D('User');
        $UserProfile = D('UserProfile');

        //即时通讯
        $VideoModel = D('Video');
        $video = $VideoModel->getInfoById($vid);
        $VideoInstantMessage = D('VideoInstantMessage');
        $VideoSendMessage = D('VideoSendMessage');
        $messages = array_reverse($VideoInstantMessage->getMessage($vid));

        foreach ($messages as $k => $v) {
            $messages[$k]['nickname'] = $User->getFieldById($v['uid'], 'nickname');
            $messages[$k]['photo'] = $UserProfile->getUserPhotoById($v['uid']);
            $messages[$k]['format_addtime'] = $this->show_date($v['addtime']);
        }
        $self_messages = $VideoSendMessage->findSendSelfMessage($uid, $vid);
        $self_message_count = $VideoSendMessage->getCount($uid, $vid);
        $self_messages = $this->myselfMessageData($self_messages);
        $this->assign('self_message_count', $self_message_count);
        $this->assign('self_messages', $self_messages);
        $this->assign('messages', $messages);
        $this->assign('video', $video);
        $this->assign('pid', $pid);
        $content = $this->fetch('Video/instant');
        $this->ajaxReturn(array('status' => 1, 'content' => $content));
    }

    //显示上传表单
    public function show_form()
    {
        $uid = $this->getUid();
        if (empty($uid)) {
            $info = 'no_login';
            $code = 2;
            $message = C('no_login');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }


    }


    public function get()
    {
        $id = I('request.id', null, 'intval');
        $uid = $this->getUid();
        if (empty($id)) {
            $info = null;
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $videoModel = D('Video');
        $videoFavorModel = D('VideoFavor');
        $video = $videoModel->where(array('id' => $id))->find();
        $self_favor = $videoFavorModel->where(array('vid' => $id, 'uid' => $uid, 'status' => 1))->find();
        $video['self_favor'] = empty($self_favor) ? 0 : 1;
        $info = $video;
        $code = 0;
        $message = C('视频信息');
        $return = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    public function listing($fid, $p, $number, $order, $sort)
    {
        if (empty($fid)) {
            $info = 'parameter_invalid';
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }


        $order_arr = array('id', 'title', 'addtime', 'synopsis');
        if (!in_array($order, $order_arr)) {
            $info = 'parameter_invalid';
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
        $sort_arr = array('asc', 'desc');
        if (!in_array($sort, $sort_arr)) {
            $info = 'parameter_invalid';
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $videoModel = D('Video');
        $condition['fid'] = $fid;
        $condition['status'] = 0;
        $count = $videoModel->where($condition)->count();
        $Page = new \Think\AjaxPage($count, $number, 'ajax_video_page');
        $show = $Page->show(); // 分页显示输出
        $videos = $videoModel->where($condition)->order(array('order' => 'desc', $order => $sort))->limit(($p - 1) * $number, $number)->select();
        foreach ($videos as $key => $val) {
            $vid = $val['videoid'];
            if (!empty($vid)) {

                $yunVideoModel = D('YunVideo');
                $yunInfo = $yunVideoModel->where(array('id' => $vid))->find();
                //$video_cover = "/Public/default/img/video-default-img.png";

                if ($yunInfo['videocover'] == '/Public/default/img/video-default-img.png') {
                    $data = R('VideoUpload/getVideoInfo', array($yunInfo['name']));
                    if ($data) {
                        if (!empty($data['data']['video_cover'])) {
                            $video_cover = $data['data']['video_cover'];
                            $yunVideoModel->where(array('id' => $vid))->setField(array('videocover' => $video_cover));
                        }
                    }
                }
                $videos[$key]['cover'] = $yunInfo['videocover'];
            }
        }


        $info['data'] = $videos;
        $info['page'] = $show;
        $info['count'] = $count;
        return $info;
    }

    public function getlisting()
    {
        $fid = I('request.fid', null, 'intval');
        $p = I('request.p', 1, 'intval');
        $number = I('request.number', 6, 'intval');
        $order = I('request.order', 'id');
        $sort = I('request.sort', 'asc');
        ($number > 50) ? $number = 50 : '';

        $info = $this->listing($fid, $p, $number, $order, $sort);
        $code = 0;
        $message = '视频列表';
        $return = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    public function add()
    {
        $fid = I('request.fid', null, 'intval');
        $title = I('request.title', null);
        $synopsis = I('request.synopsis', null);
        $favors = I('request.favors', '0', 'intval');
        $cover = I('request.cover', null);//视频
        $videoid = I('request.videoid', null);
        $flashurl = I('request.flashurl', null);
        $addition = I('request.addition', null);
        $addition2 = I('request.addition2', null);
        $addition3 = I('request.addition3', null);
        $source = I('request.source', null);
        $data['addtime'] = time();

        if (empty($fid)) {
            $info = 'parameter_invalid';
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        empty($title) ? '' : $data['title'] = $title;
        empty($fid) ? '' : $data['fid'] = $fid;
        empty($synopsis) ? '' : $data['synopsis'] = $synopsis;
        empty($cover) ? '' : $data['cover'] = $cover;
        empty($videoid) ? '' : $data['videoid'] = $videoid;
        empty($flashurl) ? '' : $data['flashurl'] = $flashurl;
        empty($favors) ? '' : $data['favors'] = $favors;
        is_null($addition) ? '' : $data['addition'] = $addition;
        is_null($addition2) ? '' : $data['addition2'] = $addition2;
        is_null($addition3) ? '' : $data['addition3'] = $addition3;
        empty($source) ? '' : $data['source'] = $source;

        $videoModel = D('Video');
        $id = $videoModel->add($data);
        $info = $id;
        $code = 0;
        $message = '添加视频成功';
        $return = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);

    }

    public function delete()
    {
        $id = I('request.id', null, 'intval');
        if (empty($id)) {
            $info = null;
        }
    }

    public function getOnlineVideoInfo()
    {
        $url = I('request.url', null);

        $match_url = "/(youku\.com|tudou\.com|ku6\.com|56\.com|letv\.com|video\.sina\.com\.cn|(my\.)?tv\.sohu\.com|v\.qq\.com|iqiyi\.com)/";

        preg_match($match_url, $url, $matches);

        $video_source = isset($matches[1]) ? $matches[1] : '';

        if ($video_source == 'youku.com') {
            $pattern = '#id_([\w\-=]+)#';

            preg_match($pattern, $url, $match);

            $id = isset($match[1]) ? $match[1] : '';

            $info = file_get_contents('https://openapi.youku.com/v2/videos/show.json?client_id=b12923c4046f3c42&video_id=' . $id);

            $obj = json_decode($info);

            $data['cover'] = $obj->bigThumbnail;

            $data['title'] = $obj->title;

            $data['addition'] = $id;

            $data['source'] = 'youku';

        } else if ($video_source == 'tudou.com') {
            $pattern = '#albumplay/([^/]+)/([^\.]+)#';

            preg_match($pattern, $url, $match);

            $id = isset($match[2]) ? $match[2] : '';

            $category = isset($match[1]) ? $match[1] : '';

            $type = 3;

            if (empty($id)) {
                $pattern = '#listplay/([^/]+)/([^\.]+)#';

                preg_match($pattern, $url, $match);

                $id = isset($match[2]) ? $match[2] : '';

                $category = isset($match[1]) ? $match[1] : '';

                $type = 1;
            }

            if (empty($id)) {
                $pattern = '#programs/view/([^/]+)#';

                preg_match($pattern, $url, $match);

                $id = isset($match[1]) ? $match[1] : '';

                $category = isset($match[1]) ? $match[1] : '';

                $type = 0;
            }


            $info = file_get_contents('http://api.tudou.com/v6/video/info?app_key=e54ac32234c9eec9&itemCodes=' . $id);

            $obj = json_decode($info);

            $data['cover'] = $obj->results[0]->bigPicUrl;

            $data['title'] = $obj->results[0]->title;

            $data['addition'] = $type;

            $data['addition2'] = $category;

            $data['addition3'] = $id;

            $data['source'] = 'tudou';
        } else if ($video_source == 'qq.com') {
            $pattern = '#vid=([\w\-=]+)#';

            preg_match($pattern, $url, $match);

            $id = isset($match[1]) ? $match[1] : '';

            $info = file_get_contents('https://openapi.youku.com/v2/videos/show.json?client_id=b12923c4046f3c42&video_id=' . $id);

            $obj = json_decode($info);

            $data['cover'] = $obj->bigThumbnail;

            $data['title'] = $obj->title;

            $data['addition'] = $id;

            $data['source'] = 'youku';
        } else if ($video_source == 'iqiyi.com') {
            //data-player-videoid="a1741f0aac55f8442d64e8d91b6e2a46"
            //data-player-tvid="4372702809"
            $pattern =
            $pattern1 = '#data-player-videoid="([^"]+)"#';
            $pattern2 = '#data-player-tvid="([^"]+)"#';
            $pattern3 = '#<title>([^<]+)</title>#';
            //  Q.PageInfo.playInfo.imageUrl = "http:\/\/u8.qiyipic.com\/image\/20150817\/c9\/28\/uv_4006455905_m_601_m0.jpg";
            $pattern4 = '#Q.PageInfo.playInfo.imageUrl = "([^"]*)"#';
            $info = file_get_contents($url);
            $url2 = str_replace('www.iqiyi.com', 'm.iqiyi.com', $url);
            $info2 = $this->getMobileURLToData($url2);

            preg_match($pattern1, $info, $match1);
            preg_match($pattern2, $info, $match2);
            preg_match($pattern3, $info, $match3);
            preg_match($pattern4, $info2, $match4);

            $data['addition'] = $match1[1];
			$data['addition2'] = $match2[1];
            $data['title'] = $match3[1];
            $data['cover'] = $match4[1];
            $data['source'] = 'iqiyi';
        }
        $info = $data;
        $code = 0;
        $message = '视频标题';
        $return = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    //手机浏览器访问
    public function getMobileURLToData($url)
    {
        $HTTP_Server = $url;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $HTTP_Server);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Linux; U; Android 4.1.1; zh-cn; MI 2S Build/JRO03L) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30 XiaoMi/MiuiBrowser/1.0");
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }

    public function response()
    {
        $uId = session('uid');
        $vid = I('request.vid', null, 'intval');
        if (empty($uId)) {
            $return['status'] = false;
            echo json_encode($return);
        } else {
            $User = D('User');
            $VideoSendMessage = D('VideoSendMessage');
            $VideoInstantMessage = D('VideoInstantMessage');
            $data['uid'] = $uId;
            $data['vid'] = $vid;
            $content = isset($_POST['content']) ? $_POST['content'] : '';
            preg_match_all("#@[^\s|@]*#", $content, $match);
            $nickname = $User->getFieldById($uId, 'nickname');
            $data['username'] = $nickname;
            $data['content'] = $content;
            $data['addtime'] = time();
            $id = $VideoInstantMessage->addMessage($data);
            if (!empty($match[0])) {
                for ($i = 0; $i < count($match[0]); $i++) {
                    $username = $match[0][$i];
                    $username = explode('@', $username);
                    $touid = $User->getUserByNickName($username[1]);
                    if (!empty($touid)) {
                        $send_message['touid'] = $touid;
                        $send_message['addtime'] = time();
                        $send_message['mid'] = $id;
                        $send_message['is_read'] = 0;
                        $VideoSendMessage->saveSendMessage($send_message);
                    }
                }
            }
            if ($id) {
                echo json_encode(array('status' => true));
            } else {
                echo json_encode(array('status' => false));
            }
        }

    }

    public function updateRead()
    {
        $uId = session('uid');
        $id = I('post.id', '', '');
        $VideoSendMessage = D('VideoSendMessage');
        $VideoSendMessage->updateRead($id);
        $self_messages = $VideoSendMessage->findSendSelfMessage($uId);
        $self_message_count = $VideoSendMessage->getCount($uId);
        $self_messages = $this->myselfMessageData($self_messages);
        $this->assign('self_message_count', $self_message_count);
        $this->assign('self_messages', $self_messages);
        $message = $this->fetch('Video/selfMessage');
        $message_null = $this->fetch('Video/message_null');
        if (!empty($self_messages)) {
            $this->ajaxReturn(array('status' => true, 'message' => $message), 'json');
        } else {
            $this->ajaxReturn(array('status' => false, 'message_null' => $message_null), 'json');
        }
    }

    public function updateAllRead()
    {
        $uid = session('uid');
        if (!empty($uid)) {
            $VideoSendMessage = D('VideoSendMessage');
            $responses = $VideoSendMessage->updateAllRead($uid);
            if (!empty($responses)) {
                $message_null = $this->fetch('message_null');
                $this->ajaxReturn(array('status' => true, 'message_null' => $message_null), 'json');
            } else {
                $this->ajaxReturn(array('status' => false), 'json');
            }
        }
    }

    public function delMessage()
    {

        $id = I('post.id', '', '');
        $VideoInstantMessage = D('VideoInstantMessage');
        $result = $VideoInstantMessage->updateStatus($id);

        if (!empty($result)) {
            $this->ajaxReturn(array('status' => true), 'json');
        }
    }

    function moreMessage()
    {
        $firstid = isset($_POST['firstid']) ? intval($_POST['firstid']) : '';
        $vid = I('post.vid', null, 'intval');
        $User = D('User');
        $UserProfile = D('UserProfile');
        $VideoInstantMessage = D('VideoInstantMessage');
        $uid = session('uid');
        $this->assign('uId', $uid);
        $responses = array_reverse($VideoInstantMessage->getInfoByFirstId($firstid, $vid));
        if (!empty($responses)) {
            foreach ($responses as $k => $v) {
                $responses[$k]['nickname'] = $User->getFieldById($v['uid'], 'nickname');
                $responses[$k]['photo'] = $UserProfile->getUserPhotoById($v['uid']);
                $responses[$k]['format_addtime'] = $this->show_date($v['addtime']);
            }
            $this->assign('messages', $responses);
            $content = $this->fetch('Video/lastresponse');
            $info['status'] = true;
            $info['message'] = $content;
        } else {
            $info['status'] = false;
        }
        echo json_encode(array('info' => $info));
    }

    public function getLiveInfo()
    {
        $lastid = isset($_POST['lastid']) ? intval($_POST['lastid']) : '';
        $vid = I('request.vid', null, 'intval');
        $User = D('User');
        $UserProfile = D('UserProfile');
        $VideoInstantMessage = D('VideoInstantMessage');

        $uid = session('uid');
        $this->assign('uId', $uid);
        $info = array();
        if (!empty($uid)) {
            //如果用户已经登录，对于@自己的信息取出
            $VideoSendMessage = D('VideoSendMessage');
            $myselfMessage = $VideoSendMessage->findSendSelfMessage($uid, $vid);
            if (!empty($myselfMessage)) {
                $myselfMessage = $this->myselfMessageData($myselfMessage);
                $self_message_count = $VideoSendMessage->getCount($uid, $vid);
                $this->assign('self_messages', $myselfMessage);
                $this->assign('self_message_count', $self_message_count);
                $selfMessage = $this->fetch('Video/selfMessage');
                $info['self'] = 'self';
                $info['selfmessage'] = $selfMessage;
            } else {
                $selfMessage = $this->fetch('Video/message_null');
                $info['self'] = 'nomessage';
                $info['selfmessage'] = $selfMessage;
            }
        }
        $this->assign('vid', $vid);
        $responses = $VideoInstantMessage->getInfoByLastId($lastid, $vid);

        if (!empty($responses)) {
            foreach ($responses as $k => $v) {
                $responses[$k]['nickname'] = $User->getFieldById($uid, 'nickname');
                $responses[$k]['photo'] = $UserProfile->getUserPhotoById($uid);
                $responses[$k]['format_addtime'] = $this->show_date($v['addtime']);
            }
            $this->assign('messages', $responses);
            $content = $this->fetch('Video/lastresponse');
            $info['status'] = true;
            $info['message'] = $content;
        } else {
            $info['status'] = false;
        }
        echo json_encode(array('info' => $info));

    }

    function myselfMessageData($myselfMessage)
    {
        $VideoInstantMessage = D('VideoInstantMessage');
        foreach ($myselfMessage as $key => $value) {
            $myselfMessage[$key]['senderuid'] = $VideoInstantMessage->getFieldById($myselfMessage[$key]['mid'], 'uid');
            $myselfMessage[$key]['username'] = $VideoInstantMessage->getFieldById($myselfMessage[$key]['mid'], 'username');
            $myselfMessage[$key]['content'] = $VideoInstantMessage->getFieldById($myselfMessage[$key]['mid'], 'content');
            $myselfMessage[$key]['content'] = strip_tags($myselfMessage[$key]['content']);
            $myselfMessage[$key]['content'] = mb_substr($myselfMessage[$key]['content'], 0, 10, 'utf-8');
        }
        return $myselfMessage[0];
    }

    function show_date($time)
    {
        $return_time = null;
        $lingTime = strtotime("today");

        if ($time < $lingTime) {
            $return_time = date('m-d', $time);

        } else {
            $return_time = date('H:i:s', $time);
        }

        return $return_time;
    }

    function yj_show_date($time)
    {
        global $basetime;
        $return_time = null;
        $now = time();
        $lingTime = strtotime("today");
        if ($basetime - $time > 60 * 10) {
            $basetime = $time;
            if ($time < $lingTime) {
                $return_time = date('m-d H:i:s', $time);
            } else {
                $return_time = date('H:i:s', $time);
            }
        }

        return $return_time;

    }

    //视频点赞
    public function favorDo()
    {
        $vid = I('request.vid', null, 'intval');
        $val = I('request.val', null);
        $fid = $this->getFidByVid($vid);
        $uid = $this->getUid();
        $forumUserModel = D('ForumUser');
        $videoFavorModel = D('VideoFavor');
        $videoModel = D('Video');

        if (!$this->checkLogin()) {
            $this->ajaxReturn(array('status' => 0, 'message' => C('NO_LOGIN')));
        } else {
            $bool = $videoFavorModel->checkHasFavor($vid, $uid);
            if ($bool) {
                $result = $videoFavorModel->checkIsFavor($vid, $uid);
                if ($result) {
                    $videoFavorModel->where(array('vid' => $vid, 'uid' => $uid))->setField(array('status' => 0));
                    $videoModel->where(array('id' => $vid))->setDec('favors', 1);
                    $this->ajaxReturn(array('status' => 2, 'content' => C('VIDEO_CANCEL_FAVOR_SUCCESS')));
                } else {
                    $videoFavorModel->where(array('vid' => $vid, 'uid' => $uid))->setField(array('status' => 1));
                    $videoModel->where(array('id' => $vid))->setInc('favors', 1);
                    $this->ajaxReturn(array('status' => 1, 'content' => C('VIDEO_FAVOR_SUCCESS')));
                }

            } else {
                $result = $videoFavorModel->add(array('vid' => $vid, 'uid' => $uid));

                if ($result) {
                    //统计处理
                    $videoModel->where(array('id' => $vid))->setInc('favors', 1);
                    $this->ajaxReturn(array('status' => 1, 'content' => C('VIDEO_FAVOR_SUCCESS')));
                } else {
                    $this->ajaxReturn(array('status' => 3, 'content' => C('VIDEO_FAVOR_FAILED')));
                }

            }

        }
    }

    public function getFidByVid($vid)
    {
        $videoModel = D('Video');
        $fid = $videoModel->getFieldById($vid, 'fid');
        return $fid;
    }


}
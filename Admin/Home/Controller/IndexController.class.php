<?php
namespace Home\Controller;

class IndexController extends CommonController
{
    public function curl($url, $is_post = false, $data = null)
    {
        $refer    = "http://music.163.com/";
        $header[] = "Cookie: " . "appver=1.5.0.75771;";
        $ch       = curl_init();
        if ($is_post) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        } else {
            curl_setopt($ch, CURLOPT_POST, 0);

        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($ch, CURLOPT_REFERER, $refer);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    public function get_playlist_info($playlist_id)
    {
        $url = "http://music.163.com/api/playlist/detail?id=" . $playlist_id;
        return $this->curl($url);
    }

    public function get_music_url($music_name)
    {
        $url  = "http://music.163.com/api/search/pc";
        $data = "s=" . urlencode($music_name) . "&offset=0&limit=1&type=1";
        $info = $this->curl($url, true, $data);
        $obj  = json_decode($info, true);
        return $obj['result']['songs'][0]['mp3Url'];
    }

    public function music()
    {
        $this->display('music');
    }

    public function showMusic()
    {
        $musicID = cookie('musicID');
        $this->assign('musicID', $musicID);
        $this->display('Music/music');
    }

    public function addMusicID()
    {
        $musicID = I('post.musicID');
        cookie('musicID', null);
        cookie('musicID', $musicID);
        $this->redirect('showMusic');
    }

    public function getMusicData()
    {
        $musicID = cookie('musicID');
        if (empty($musicID) || $musicID == '') {
            $musicID = 22402138;
        }
        $musicdata = $this->get_playlist_info($musicID);
        $musicdata = json_decode($musicdata, true);
        $musicdata = $musicdata['result']['tracks'];
        $musicList = array();
        foreach ($musicdata as $key => $val) {
            $musicList[$key]['title']  = $val['name'];
            $musicList[$key]['artist'] = $val['album']['name'];
            $musicList[$key]['mp3']    = $this->get_music_url($val['name']);
            $musicList[$key]['picUrl'] = $val['album']['picUrl'] . '?param=100y100';
        }
        echo json_encode($musicList);
    }
    public function index()
    {
        //$this->display('index');
        $this->indexSub();
    }

    public function indexSub()
    {
        //获取活动数量,将要开始的活动
        $activityModel  = D('Activity');
        $activity_count = $activityModel->where(array('status' => array('neq', 1), 'audit' => array('neq', 0), 'istrash' => array('neq', 1)))->count();
        $this->assign('activity_count', $activity_count);
        $beginning_activity = $activityModel->where(array('status' => array('neq', 1), 'holdstart' => array('gt', time())))->order(array('holdstart' => 'desc'))->find();
        $this->assign('beginning_activity', $beginning_activity);

        //获取话题数量,最新的话题
        $topicModel  = D('Topic');
        $topic_count = $topicModel->where(array('status' => array('neq', 1)))->count();
        $this->assign('topic_count', $topic_count);
        $newtopic = $topicModel->where(array('status' => array('neq', 1)))->order(array('addtime' => 'desc'))->find();
        $this->assign('newtopic', $newtopic);

        //获取评论数量,最后评论
        $responseModel           = D('Response');
        $activity_response_count = $responseModel->where(array('status' => array('neq', 1), 'type' => 2))->count();
        $topic_response_count    = $responseModel->where(array('status' => array('neq', 1), 'type' => 1))->count();
        $response_count          = $activity_count + $topic_count;
        $this->assign('response_count', $response_count);
        $activity_last_response = $responseModel->where(array('status' => array('neq', 1), 'type' => 2))->order(array('addtime' => 'desc'))->find();
        $this->assign('activity_last_response', $activity_last_response);
        $topic_last_response = $responseModel->where(array('status' => array('neq', 1), 'type' => 1))->order(array('addtime' => 'desc'))->find();
        $this->assign('topic_last_response', $topic_last_response);

        //用户数量
        $userModel  = D('User');
        $user_count = $userModel->count();
        $this->assign('user_count', $user_count);
        $newuser = $userModel->order(array('id' => 'desc'))->find();
        $this->assign('newuser', $newuser);

        //签到统计
        // $forumSignModel = D('ForumSign');
        // $today = date('Y-m-d', strtotime('today'));
        // $week_data = $forumSignModel->where(array('updatetime' => array('gt', $today)))->count();
        // $week_data_key = "'今天'";
        // $this->assign('week_data_key', $week_data_key);
        // $week_data_value = $week_data;
        // $this->assign('week_data_value', $week_data_value);

        //活动报名统计
        $activityEnrollModel = D('ActivityEnroll');
        $enrollInfo          = $activityEnrollModel->field(array("count(uid)" => "count", 'aid'))->group('aid')->order(array('count' => 'desc'))->limit(12)->select();
        $activity_data_name  = "";
        $activity_data_value = "";
        foreach ($enrollInfo as $key => $val) {
            $activity_title = $activityModel->getFieldById($val['aid'], 'subject');
            $activity_data_name .= "'" . $activity_title . "',";
            $activity_data_value .= $val['count'] . ",";
        }

        $activity_data_name  = trim($activity_data_name, ',');
        $activity_data_value = trim($activity_data_value, ',');
        $this->assign('activity_data_name', $activity_data_name);
        $this->assign('activity_data_value', $activity_data_value);

        //加入工作室信息
        $forumUserModel = D('ForumUser');
        $forumusers     = $forumUserModel->where(array('status' => 0))->order(array('addtime' => 'desc'))->limit(15)->select();
        $this->assign('forumusers', $forumusers);

        //最新回复
        $responses = $responseModel->where(array('type' => 2))->order(array('addtime' => 'desc'))->limit(10)->select();
        $this->assign('responses', $responses);
        $this->display('index_sub');
    }

    //游戏管理
    public function Game()
    {
        $id = I('request.id', 1, 'intval');
        switch ($id) {
            case 1: //超级马里奥
                $this->display('Game/html5-mario/index');
                break;
            case 2: //街霸
                $this->display('Game/streetMaster/index');
                break;
            case 3: //皮卡丘大作战
                $src = 'http://imgc.abab.com/small_flash_channel/spare/598878/game.html?aflash';
                $this->assign('src', $src);
                $this->display('Game/online/index');
                break;
            case 4: //合金弹头
                $src = 'http://imgc.abab.com/small_flash_channel/shoot/hejindantshamsm.swf';
                $this->assign('src', $src);
                $this->display('Game/online/index');
                break;
            case 5: //植物大作战
                $src = 'http://imgc.abab.com/small_flash_channel/strategy/595693/game.html?aflash';
                $this->assign('src', $src);
                $this->display('Game/online/index');
                break;
            default:
                $this->display('Game/html5-mario/index');
                break;
        }
    }
}

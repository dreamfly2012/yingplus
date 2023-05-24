<?php

namespace Home\Controller;

class TopicController extends CommonController
{
    public function index()
    {
        $param = I('request.param', 'hot'); //置顶加精话题
        switch ($param) {
            case 'hot':
                $topicModel = D('Topic');
                $condition['isadmintop'] = 1;
                $condition['admin_digest'] = 1;
                $condition['_logic'] = 'or';
                $map['_complex'] = $condition;
                $map['status'] = 0;
                $topics = $topicModel->where($map)->select();

                foreach ($topics as $key => $val) {
                    $content = htmlspecialchars_decode($val['content']);
                    preg_match_all('/<img([^>]*)>/', $content, $matches);
                    $imglist = '';

                    foreach ($matches[0] as $kkey => $vval) {
                        preg_match_all("#src=('|\")([^'\"]*)('|\")#", $vval, $matchinfo);
                        $str = $matchinfo[2][0];
                        if (strpos($str, 'http') === false) {
                            $str = 'http://'.$_SERVER['SERVER_NAME'].'/'.$str;
                        }
                        $imglist[$kkey]['src'] = $str;
                    }

                    $topics[$key]['image_list'] = $imglist;
                    $topics[$key]['img'] = isset($imglist[0]) ? $imglist[0]['src'] : '';
                    $topics[$key]['username'] = getUserNicknameById($val['uid']);
                    $userphoto = getUserPhotoById($val['uid']);
                    if (strpos($userphoto, 'http') === false) {
                        $userphoto = 'http://'.$_SERVER['SERVER_NAME'].'/'.$userphoto;
                    }
                    $topics[$key]['userphoto'] = $userphoto;
                    $topics[$key]['formattime'] = date('Y-m-d H:i', $val['addtime']);
                    $topics[$key]['brief'] = htmlspecialchars_decode(mb_substr(strip_tags($val['content']), 0, 100, 'utf-8'));
                    $topics[$key]['formattitle'] = mb_substr($val['subject'], 0, 10, 'utf-8');
                    $topics[$key]['forumname'] = getForumNameById($val['fid']);
                    $topics[$key]['url'] = U('Index/topic', array('id' => $val['id']));
                    $content = htmlspecialchars_decode($val['content']);
                    $content = preg_replace_callback('/<img src="\/uploads/',
                        function ($matches) {
                            return '<img src="http://'.C('hostname').'/uploads/';
                        },
                        $content);
                    $topics[$key]['content'] = $content;
                    $topics[$key]['contentText'] = htmlspecialchars_decode(strip_tags($val['content']));
                }
                $info = $topics;
                $code = 0;
                $message = '热门话题';
                $return = $this->buildReturn($info, $code, $message);
                $this->ajaxReturn($return);
                break;
            case 'forum':
                $fid = I('request.fid', 1);
                $topicModel = D('Topic');
                $condition['isadmintop'] = 1;
                $condition['admin_digest'] = 1;
                $condition['fid'] = $fid;
                $condition['_logic'] = 'or';

                $map['_complex'] = $condition;
                $map['status'] = 0;
                $page = I('request.page', 1);
                $pagenum = 5;
                $topics = $topicModel->where($map)->order(array('addtime' => 'desc'))->limit(($page - 1) * $pagenum, $pagenum)->select();

                foreach ($topics as $key => $val) {
                    $content = htmlspecialchars_decode($val['content']);
                    preg_match_all('/<img([^>]*)>/', $content, $matches);
                    $imglist = '';

                    foreach ($matches[0] as $kkey => $vval) {
                        preg_match_all("#src=('|\")([^'\"]*)('|\")#", $vval, $matchinfo);
                        $str = $matchinfo[2][0];
                        if (strpos($str, 'http') === false) {
                            $str = 'http://'.$_SERVER['SERVER_NAME'].'/'.$str;
                        }
                        $imglist[$kkey]['src'] = $str;
                    }

                    $topics[$key]['image_list'] = $imglist;
                    $topics[$key]['img'] = isset($imglist[0]) ? $imglist[0]['src'] : '';
                    $topics[$key]['username'] = getUserNicknameById($val['uid']);
                    $userphoto = getUserPhotoById($val['uid']);
                    $selfdesc = getUserSelfDescById($val['uid']);
                    if (strpos($userphoto, 'http') === false) {
                        $userphoto = 'http://'.$_SERVER['SERVER_NAME'].'/'.$userphoto;
                    }
                    $topics[$key]['userphoto'] = $userphoto;
                    $topics[$key]['selfdesc'] = $selfdesc;
                    $topics[$key]['formattime'] = date('Y-m-d H:i', $val['addtime']);
                    $topics[$key]['brief'] = htmlspecialchars_decode(mb_substr(strip_tags($val['content']), 0, 100, 'utf-8'));
                    $topics[$key]['formattitle'] = mb_substr($val['subject'], 0, 10, 'utf-8');
                    $topics[$key]['forumname'] = getForumNameById($val['fid']);
                    $topics[$key]['url'] = U('Index/topic', array('id' => $val['id']));
                    $content = htmlspecialchars_decode($val['content']);
                    $content = preg_replace_callback('/<img src="\/uploads/',
                        function ($matches) {
                            return '<img src="http://'.C('hostname').'/uploads/';
                        },
                        $content);
                    $topics[$key]['content'] = $content;
                    $topics[$key]['contentText'] = htmlspecialchars_decode(strip_tags($val['content']));
                }
                $info = $topics;
                $code = 0;
                $message = '工作室话题';
                $return = $this->buildReturn($info, $code, $message);
                $this->ajaxReturn($return);
                break;
            case 'other':
                $topicModel = D('Topic');
                $condition['isadmintop'] = 0;
                $condition['admin_digest'] = 0;
                $condition['status'] = 0;
                $condition['_logic'] = 'and';
                $page = I('request.page', 1);
                $pagenum = 5;
                $topics = $topicModel->where($condition)->order(array('addtime' => 'desc'))->limit(($page - 1) * $pagenum, $pagenum)->select();

                foreach ($topics as $key => $val) {
                    $content = htmlspecialchars_decode($val['content']);
                    preg_match_all('/<img([^>]*)>/', $content, $matches);
                    $imglist = '';

                    foreach ($matches[0] as $kkey => $vval) {
                        preg_match_all("#src=('|\")([^'\"]*)('|\")#", $vval, $matchinfo);
                        $str = $matchinfo[2][0];
                        if (strpos($str, 'http') === false) {
                            $str = 'http://'.$_SERVER['SERVER_NAME'].'/'.$str;
                        }
                        $imglist[$kkey]['src'] = $str;
                    }

                    $topics[$key]['image_list'] = $imglist;
                    $topics[$key]['img'] = isset($imglist[0]) ? $imglist[0]['src'] : '';
                    $topics[$key]['username'] = getUserNicknameById($val['uid']);
                    $userphoto = getUserPhotoById($val['uid']);
                    if (strpos($userphoto, 'http') === false) {
                        $userphoto = 'http://'.$_SERVER['SERVER_NAME'].'/'.$userphoto;
                    }
                    $topics[$key]['userphoto'] = $userphoto;

                    $topics[$key]['formattime'] = date('Y-m-d H:i', $val['addtime']);
                    $topics[$key]['brief'] = mb_substr(strip_tags(htmlspecialchars_decode($val['content'])), 0, 100, 'utf-8');
                    $topics[$key]['formattitle'] = mb_substr($val['subject'], 0, 20, 'utf-8');
                    $topics[$key]['forumname'] = getForumNameById($val['fid']);
                    $topics[$key]['url'] = U('Index/topic', array('id' => $val['id']));
                    $content = htmlspecialchars_decode($val['content']);
                    $content = preg_replace_callback('/<img src="\/uploads/',
                        function ($matches) {
                            return '<img src="http://'.C('hostname').'/uploads/';
                        },
                        $content);
                    $topics[$key]['content'] = $content;
                    $topics[$key]['contentText'] = htmlspecialchars_decode(strip_tags($val['content']));
                }
                $info = $topics;
                $code = 0;
                $message = '其他话题';
                $return = $this->buildReturn($info, $code, $message);
                $this->ajaxReturn($return);
                break;
            default:
                $this->ajaxReturn(null);
        }
    }

    public function checkIsFavor()
    {
        $type = I('request.type', 1, 'intval');
        $token = I('request.token', null, '');
        $pid = I('request.pid', null, 'intval');
        $info = null;

        if (empty($token)) {
            $code = -1;
            $message = '用户没有登录,请登录';
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $uid = $this->get_uid_by_token($token);

        //没有登录
        if (empty($uid)) {
            $info = null;
            $code = -2;
            $message = '没有登录,请登录';
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        //参数错误
        if (empty($pid) || empty($type)) {
            $info = null;
            $code = -3;
            $message = '参数错误';
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $type_arr = array(1, 2, 3);
        if (!in_array($type, $type_arr)) {
            $info = null;
            $code = -4;
            $message = '类型不对';
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $collectModel = D('Collect');
        $condition['type'] = $type;
        $condition['pid'] = $pid;
        $condition['uid'] = $uid;
        $condition['status'] = 0;
        $exist = $collectModel->where($condition)->find();
        if ($exist) {
            $info = $exist;
            $code = 1;
            $message = '收藏过';
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        } else {
            $info = $exist;
            $code = -1;
            $message = '没有收藏过';
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
    }

    public function detail()
    {
        $param = I('request.param', '1');
        $topicModel = D('Topic');
        $userModel = D('User');
        $condition['id'] = $param;
        $condition['status'] = 0;
        $topic = $topicModel->where($condition)->find();
        $topic['username'] = $userModel->field('nickname')->where(array('id' => $topic['uid']))->find();
        $topic['formattime'] = date('Y-m-d H:i', $topic['addtime']);
        $content = htmlspecialchars_decode($topic['content']);
        $content = preg_replace_callback('/<img src="\/uploads/',
            function ($matches) {
                return '<img src="http://'.C('hostname').'/uploads/';
            },
            $content);
        $topic['content'] = $content;
        $info = $topic;
        $code = 0;
        $message = '话题详情';
        $return = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    public function getHotPlatformTopicList($start)
    {
        //$where['hot'] = array('gt', C('INDEX_TOPIC_HOT_VALUE'));
        $where['status'] = array('eq', 0);
        $result = $this->where($where)->order(array('addtime' => 'desc'))->limit($start * C('INDEX_HOT_TOPIC_COUNT'), C('INDEX_HOT_TOPIC_COUNT'))->select();

        return $result;
    }

    /**
     * 更新话题浏览次数，如果登录记录足迹.
     *
     * @return [type] [description]
     */
    public function updateViews()
    {
        $id = I('request.id', null, 'intval');
        $token = I('request.token', null);
        $topicModel = D('Topic');
        $topicModel->where(array('id' => $id))->setInc('views', 1);
        if (!empty($token)) {
            $data['uid'] = $this->get_uid_by_token($token);
            $data['pid'] = $id;
            $data['type'] = 1;
            $data['viewtime'] = time();
            $footprintModel = D('UserFootprint');
            $result = $footprintModel->add($data);
        }
    }
}

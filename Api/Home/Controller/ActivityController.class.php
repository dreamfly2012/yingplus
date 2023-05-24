<?php
namespace Home\Controller;

use Think\Controller;

class ActivityController extends CommonController
{
    public function index()
    {
        $param = I('request.param', 'hot'); //置顶加精话题
        switch ($param) {
            case 'hot':
                $activityModel                 = D('Activity');
                $condition['isadminrecommend'] = 1;
                $condition['admin_digest']     = 1;
                $condition['_logic']           = 'or';
                $meta['_logic']                = 'and';
                $meta['movie']                 = array('neq', 0);
                $activities                    = $activityModel->where($meta)->select();

                foreach ($activities as $key => $val) {
                    $content = htmlspecialchars_decode($val['content']);
                    preg_match_all("/<img([^>]*)>/", $content, $matches);
                    $imglist = "";

                    foreach ($matches[0] as $kkey => $vval) {
                        preg_match_all("#src=('|\")([^'\"]*)('|\")#", $vval, $matchinfo);
                        $imginfo = $matchinfo[2][0];
                        if (strpos($imginfo, 'http') === false) {
                            $imginfo               = 'http://' . $_SERVER['SERVER_NAME'] . '/' . $userphoto;
                            $imglist[$kkey]['src'] = $imginfo;
                        }

                    }
                    $activities[$key]['image_list'] = $imglist;
                    $img                            = getAttachmentById(getMoviePoster($val['movie']), 'path');
                    if (strpos($img, 'http') === false) {
                        $img = 'http://' . $_SERVER['SERVER_NAME'] . '/' . $img;
                    }
                    $activities[$key]['img']             = $img;
                    $activities[$key]['url']             = U('Index/activity', array('id' => $val['id']));
                    $activities[$key]['content']         = htmlspecialchars_decode($val['content']);
                    $activities[$key]['formatholdstart'] = date('Y-m-d H:i', $val['holdstart']);
                    $activities[$key]['contentText']     = htmlspecialchars_decode(strip_tags($val['content']));
                }
                $info    = $activities;
                $code    = 0;
                $message = '推荐活动';
                $return  = $this->buildReturn($info, $code, $message);
                $this->ajaxReturn($return);
                break;
            case 'other':
                $activityModel             = D('Activity');
                $condition['isadmintop']   = 0;
                $condition['admin_digest'] = 0;
                $condition['movie']        = array('neq', 0);
                $condition['_logic']       = 'and';
                $page                      = I('request.page', 1);
                $pagenum                   = 5;
                $activities                = $activityModel->where($condition)->order(array('addtime' => 'desc'))->limit(($page - 1) * $pagenum, $pagenum)->select();

                foreach ($activities as $key => $val) {
                    $content = htmlspecialchars_decode($val['content']);
                    preg_match_all("/<img([^>]*)>/", $content, $matches);
                    $imglist = "";

                    foreach ($matches[0] as $kkey => $vval) {
                        preg_match_all("#src=('|\")([^'\"]*)('|\")#", $vval, $matchinfo);
                        $imginfo = $matchinfo[2][0];
                        if (strpos($imginfo, 'http') === false) {
                            $imginfo               = 'http://' . $_SERVER['SERVER_NAME'] . '/' . $userphoto;
                            $imglist[$kkey]['src'] = $imginfo;
                        }

                    }
                    $activities[$key]['image_list'] = $imglist;
                    $img                            = getAttachmentById(getMoviePoster($val['movie']), 'path');
                    if (strpos($img, 'http') === false) {
                        $img = 'http://' . $_SERVER['SERVER_NAME'] . '/' . $img;
                        //$imglist[$kkey]['src'] = $imginfo;
                    }
                    $activities[$key]['img'] = $img;
                    //$activities[$key]['img']  = isset($imglist[0]) ? $imglist[0]['src'] : '';
                    $activities[$key]['url']             = U('Index/activity', array('id' => $val['id']));
                    $activities[$key]['formatholdstart'] = date('Y-m-d H:i', $val['holdstart']);
                    $activities[$key]['content']         = htmlspecialchars_decode($val['content']);
                    $activities[$key]['contentText']     = htmlspecialchars_decode(strip_tags($val['content']));
                }
                $info    = $activities;
                $code    = 0;
                $message = '其他活动';
                $return  = $this->buildReturn($info, $code, $message);
                $this->ajaxReturn($return);
                break;
            default:
                $this->ajaxReturn(null);
        }
    }

    public function detail()
    {
        $param                       = I('request.param', '1'); //
        $activityModel               = D('Activity');
        $forummovieModel = D('ForumMovie');
        $activitymovieticketModel = D('ActivityMovieTicket');
        $userModel                   = D('User');
        $condition['id']             = $param;
        $condition['movie']          = array('neq', 1);
        $condition['status']         = array('neq', 1);
        $activity                    = $activityModel->where($condition)->find();
        $activity['movie'] = $forummovieModel->where(array('id' => $activity['movie']))->find();
        $activity['username']        = $userModel->field('nickname')->where(array('id' => $activity['uid']))->find();
        $activity['formatholdstart'] = date('Y-m-d H:i', $activity['holdstart']);
        $activity['content']         = htmlspecialchars_decode($activity['content']);
        $activity['place'] =  getPlaceNameById($activity['holdprovince']) . getPlaceNameById($activity['holdcity']);
        $activity['ticket'] = $activitymovieticketModel->where(array('aid' => $activity['id']))->find();
        $info                        = $activity;
        $code                        = 0;
        $message                     = '活动详情';
        $return                      = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    public function getHotPlatformTopicList($start)
    {
        $where['status'] = array('eq', 0);
        $result          = $this->where($where)->order(array('addtime' => 'desc'))->limit($start * C('INDEX_HOT_TOPIC_COUNT'), C('INDEX_HOT_TOPIC_COUNT'))->select();
        return $result;
    }

}

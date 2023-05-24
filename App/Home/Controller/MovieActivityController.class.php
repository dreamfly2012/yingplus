<?php

namespace Home\Controller;

class MovieActivityController extends CommonController
{
    public function index()
    {
        $info = null;
        $code = -1;
        $message = C('parameter_invalid');
        $return = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    //创建包场活动
    public function add()
    {
        $type = I('request.type', null, 'intval');
        $fid = I('request.fid', null, 'intval');
        $uid = $this->getUid();
        if (empty($type) || empty($fid)) {
            $info = null;
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
        if (empty($uid)) {
            $info = null;
            $code = 2;
            $message = C('no_login');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
        switch ($type) {
            case '1':
                $this->createActivityDo();
                break;
            case '2':
                $this->createRecognitionActivityDo();
                break;
            default:
                break;
        }

    }

    //获取活动列表信息
    public function getActivityList()
    {
        $fid = I('request.fid', '1', 'intval');
        $mid = I('request.mid', '1', 'intval');
        $keyword = I('request.keyword', null);
        $activityModel = D('Activity');
        $districtModel = D('District');

        $activities = array();

        if (!empty($keyword)) {
            $map['name'] = array('like', $keyword . '%');
            $map['pinyin'] = array('like', $keyword . '%');
            $map['_logic'] = 'OR';
            $condition['_complex'] = $map;
            $condition['level'] = 2;
            $cities = $districtModel->field('id,name')->where($condition)->select();
            //检查活动中是否有这些城市
            foreach ($cities as $key => $val) {
                $city_activities = $activityModel->where(array('category' => 1, 'audit' => 1, 'status' => array('neq', 1), 'mid' => $mid, 'fid' => $fid, 'holdcity' => $val['id']))->select();
                $activities = array_merge($activities, $city_activities);
            }
        } else {
            $activities = $activityModel->where(array('category' => 1, 'audit' => 1, 'fid' => $fid, 'movie' => $mid))->select();
        }

        foreach ($activities as $key => $val) {
            $activities[$key]['imgsrc'] = buildImgUrl(getAttachmentUrlById($val['img']));
            $activities[$key]['href'] = U('Index/moviedetail', array('aid' => $val['id']));
            $activities[$key]['cinemaname'] = getCinemaNameById($val['cinemaid']);
            $activities[$key]['ticketprice'] = getTicketPriceByAid($val['id']);
            $activities[$key]['enrollnum'] = getBoughtTicketByAid($val['id']);
            $activities[$key]['holdstart_format'] = date('Y-m-d', $val['holdstart']);

        }
        $info = $activities;
        $code = 0;
        $message = "包场活动信息";
        $return = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    //获取活动信息
    public function getActivityInfo()
    {
        $id = I('request.id', null, 'intval');
        if (empty($id)) {
            $info = null;
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
        $activityModel = D('Activity');
        $forumMovieModel = D('ForumMovie');
        $activity = $activityModel->where(array('id' => $id))->find();
        if (empty($activity)) {
            $info = null;
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
        $activity['sponsor'] = getUserNicknameById($activity['uid']);
        $activity['holdstart_format'] = date("Y-m-d", $activity['holdstart']);
        $activity['detailaddress_format'] = getPlaceNameById($activity['holdprovince']) . getPlaceNameById($activity['holdcity']) . $activity['detailaddress'];
        $activity['cinemaname'] = getCinemaNameById($activity['cinemaid']);
        $activity['enrollnum'] = getBoughtTicketByAid($id);
        $activity['ticketprice'] = getTicketPriceByAid($id);
        $activity['activity_rule'] = $forumMovieModel->getFieldById($activity['movie'], 'rule');
        $activity['movie_title'] = $forumMovieModel->getFieldById($activity['movie'], 'title');
        $activity['detail_url'] = U('Pc/activity', array('aid' => $activity['id']));
        $activity['forumname'] = getFansgroupById($activity['fid']);
        $info = $activity;
        $code = 0;
        $message = '活动信息详情';
        $return = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    //获取活动类型(定向包场,反向包场)
    public function getActivityType()
    {
        $cid = I('request.cid', null, 'intval');
        $mid = I('request.mid', null, 'intval');
        $fid = I('request.fid', null, 'intval');
        if (empty($cid) || empty($mid) || empty($fid)) {
            $info = null;
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $moviePlaceModel = D('MoviePlace');
        $cinemas = $moviePlaceModel->where(array('cid' => $cid, 'status' => 0))->select();

        //查询城市是否有包场电影
        $recognitionActivityModel = D('RecognitionActivity');

        $activity = $recognitionActivityModel->where(array('status' => 0, 'city' => $cid, 'fid' => $fid, 'mid' => $mid))->find();

        //定向包场
        if ($activity) {
            $activity['type'] = 'recognition';
            $info = $activity;
            $code = 0;
            $message = '反向包场信息';
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        //常规包场
        if (empty($cinemas)) {
            $activity['type'] = 'normal';
            $activity['cinemas'] = null;
            $info = $activity;
            $code = 0;
            $message = "定向包场";
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }


        $activity['type'] = 'normal';
        $activity['cinemas'] = $cinemas;
        $info = $activity;
        $code = 0;
        $message = "定向包场";
        $return = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    //获取报名信息
    public function getEnrollInfo()
    {
        $id = I('request.id', null, 'intval');
        if (empty($id)) {
            $info = null;
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
        $activityMovieOrderModel = D('ActivityMovieOrder');
        $orderinfo = $activityMovieOrderModel->where(array('aid' => $id, 'order_status' => 1))->order(array('check_status' => 'desc'))->select();
        foreach ($orderinfo as $key => $val) {
            $orderinfo[$key]['username'] = getUserNicknameById($val['uid']);
            $orderinfo[$key]['pay_time_format'] = date('Y-m-d', $val['pay_time']);
        }
        $info = $orderinfo;
        $code = 0;
        $message = "订单信息";
        $return = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);

    }

    //获取兑换码相关信息
    public function getExchangeCodeInfo()
    {
        $aid = I('request.aid', null, 'intval');
        $code = I('request.code', null);
        if (empty($aid) || empty($code)) {
            $info = null;
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $activityMovieCodeModel = D('ActivityMovieCode');
        $info = $activityMovieCodeModel->where(array('aid' => $aid, 'code' => $code))->find();
        if (empty($info)) {
            $info = null;
            $code = 1;
            $message = "错误的二维码";
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $now = time();
        if ($now > $info['exchangetime']) {
            $info = null;
            $code = 1;
            $message = '兑换码已过期';
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $activityMovieOrderModel = D('ActivityMovieOrder');
        $orderinfo = $activityMovieOrderModel->where(array('id' => $info['oid']))->find();
        $orderinfo['username'] = getUserNicknameById($orderinfo['uid']);
        $info = $orderinfo;
        $code = 0;
        $message = '订单信息';
        $return = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    //获取活动状态信息(显示文字等)
    public function getActivityStatus()
    {
        $id = I('request.id', null, 'intval');
        if (empty($id)) {
            $info = null;
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
        $uid = $this->getUid();
        $activityModel = D('Activity');
        $activityEnrollModel = D('ActivityEnroll');
        $activity = $activityModel->field('holdstart,enrollendtime')->where(array('id' => $id))->find();
        if (empty($activity)) {
            $info = null;
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
        $enrollinfo = $activityEnrollModel->where(array('aid' => $id, 'uid' => $uid))->find();
        if (empty($enrollinfo) || $enrollinfo['status'] == 1) {
            $activity['isenroll'] = 0;//未报名
        } else {
            $activity['isenroll'] = 1;
        }
        $info = $activity;
        $code = 0;
        $message = C('parameter_invalid');
        $return = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    //获取订单相关讯息
    public function getOrderInfo()
    {
        $trade_no = I('request.trade_no', null);
        if (empty($trade_no)) {
            $info = null;
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $activityMovieOrder = D('ActivityMovieOrder');
        $order = $activityMovieOrder->where(array('trade_no' => $trade_no))->find();
        $info = $order;
        $code = 0;
        $message = '订单信息';
        $return = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    //工作室包场信息
    public function movieInfoByFid($fid){
        $forumMovieModel = D('ForumMovie');
        $condition['status'] = 1;
        $condition['_string'] = 'FIND_IN_SET(' . $fid . ', fid)';
        $movie = $forumMovieModel->where($condition)->find();
        return $movie;
    }

    //获取工作室包场信息
    public function getMovieInfoByFid(){
        $fid = I('request.fid',null,'intval');
        if(empty($fid)){
            $info = null;
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info,$code,$message);
            $this->ajaxReturn($return);
        }
        $info = $this->movieInfoByFid($fid);
        $code = 0;
        $message = '工作室电影包场信息';
        $return = $this->buildReturn($code,$message,$info);
        $this->ajaxReturn($return);
    }

    //确认到场
    public function confirmPresence()
    {
        $aid = I('request.aid', null, 'intval');
        $code = I('request.code', null);
        if (empty($aid) || empty($code)) {
            $info = null;
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $activityMovieCodeModel = D('ActivityMovieCode');
        $info = $activityMovieCodeModel->where(array('aid' => $aid, 'code' => $code))->find();
        if (empty($info)) {
            $info = null;
            $code = 1;
            $message = "错误的二维码";
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $now = time();
        if ($now > $info['exchangetime']) {
            $info = null;
            $code = 1;
            $message = '兑换码已过期';
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $activityMovieOrderModel = D('ActivityMovieOrder');
        $activityMovieOrderModel->where(array('id' => $info['oid']))->setField(array('check_status' => 1));
        $orderinfo = $activityMovieOrderModel->where(array('id' => $info['oid']))->find();
        $orderinfo['returnurl'] = U('Index/scan', array('aid' => $aid));

        $info = $orderinfo;
        $code = 0;
        $message = '订单信息';
        $return = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);

    }

    //获取电影信息
    public function getMovieInfo()
    {
        $id = I('request.id', null, 'intval');
        $aid = I('request.aid', null, 'intval');
        if (empty($id) && empty($aid)) {
            $info = null;
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
        $forumMovieModel = D('ForumMovie');
        if (!empty($id)) {
            $movie = $forumMovieModel->where(array('id' => $id))->find();
            $info = $movie;
            $code = 0;
            $message = "电影相关信息";
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
        if (!empty($aid)) {
            $activityModel = D('Activity');
            $id = $activityModel->getFieldById($aid, 'movie');
            $movie = $forumMovieModel->where(array('id' => $id))->find();
            $info = $movie;
            $code = 0;
            $message = "电影相关信息";
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

    }

    //反馈上传接口
    public function uploadFeedback()
    {
        $aid = I('request.aid', null, 'intval');
        $fid = $this->getFidByAid($aid);
        $uid = $this->getUid();

        $forums = $this->getUserForum($uid);
        $bool = $this->checkInForum($fid, $forums);
        if (!$bool) {
            $info = 'not_in_forum';
            $code = 1;
            $message = '请先加入工作室';
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        if (empty($uid)) {
            $code = 2;
            $info = null;
            $message = C('no_login');
            $return = $this->buildReturn($info,$code,$message);
            $this->ajaxReturn($return);
        }

        $activityModel = D('Activity');
        $holdstart = $activityModel->where(array('id'=>$aid))->getField('holdstart');
        $now = time();
        if($now<$holdstart){
            $info = null;
            $code = 1;
            $message = '请活动结束后上传';
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }


        $image_array = array('image/jpeg', 'image/png', 'image/gif');
        $video_array = array('video/mp4', 'video/3gp', 'video/avi', 'video/flv', 'video/rmvb');
        $info = $_FILES['file'];

        if ($info['error']) {
            $info = null;
            $code = -1;
            $message = '上传错误';
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
        if (in_array($info['type'], $image_array)) {
            R('UploadImg/upload');
        }

        if (in_array($info['type'], $video_array)) {
            R('VideoUpload/upload');
        }

        $info = null;
        $code = 2;
        $message = "视频图片格式不正确";
        $return = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);

    }

    //反馈添加接口(将数据添加到反馈表中)
    public function addUploadFeedback()
    {
        $aid = I('request.aid', null, 'intval');
        $title = I('request.title',null);
        $attachmentid = I('request.attachmentid', null, 'intval');
        $uid = $this->getUid();
        if(empty($uid)){
            $info = 'no_login';
            $code = 2;
            $message = C('no_login');
            $return = $this->buildReturn($info, $code, $message);
            $this->buildReturn($return);
        }

        if (empty($aid) || empty($attachmentid)) {
            $info = 'parameter_invalid';
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info, $code, $message);
            $this->buildReturn($return);
        }
        $attachmentModel = D('Attachment');
        $activityMovieFeedbackModel = D('ActivityMovieFeedback');
        $attachment = $attachmentModel->where(array('id' => $attachmentid))->find();
        $arr['aid'] = $aid;
        $arr['title'] = is_null($title) ? $attachment['filename'] : $title;
        $arr['uid'] = $attachment['uid'];
        $arr['attachmentid'] = $attachmentid;
        $arr['img_url'] = $attachment['path'];
        $arr['video_url'] = '';
        $arr['width'] = $attachment['width'];
        $arr['height'] = $attachment['height'];
        $arr['addtime'] = time();
        $arr['isvideo'] = $attachment['isvideo'];
        if ($id = $activityMovieFeedbackModel->add($arr)) {
            $info = array('id' => $id, 'isvideo' => $attachment['isvideo'], 'img_url_format' => $attachment['remote_url']);
            $code = 0;
            $message = '上传反馈成功';
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);

        } else {
            $info = $activityMovieFeedbackModel->getError();
            $code = 1;
            $message = '上传反馈失败';
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);

        }

    }

    //获取个人反馈
    public function getSelfFeedback()
    {
        $aid = I('request.aid', null, 'intval');
        $uid = $this->getUid();
        if (empty($aid)) {
            $info = null;
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
        $activityMovieFeedbackModel = D('ActivityMovieFeedback');
        $feedbacks = $activityMovieFeedbackModel->where(array('uid' => $uid, 'aid' => $aid, 'status' => 0))->select();
        foreach ($feedbacks as $key => $val) {
            $feedbacks[$key]['img_url_format'] = buildImgUrl($val['img_url']);
        }
        $info = $feedbacks;
        $code = 0;
        $message = '个人反馈信息';
        $return = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    //获取反馈视频
    public function getFeedback()
    {
        $aid = I('request.aid', null, 'intval');
        if (empty($aid)) {
            $info = null;
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
        $activityMovieFeedbackModel = D('ActivityMovieFeedback');
        $attachmentModel = D('Attachment');
        $feedbacks = $activityMovieFeedbackModel->where(array('aid' => $aid, 'status' => 0))->select();
        foreach ($feedbacks as $key => $val) {
            $remote_url = $attachmentModel->getFieldById($val['attachmentid'], 'remote_url');
            $feedbacks[$key]['img_url_format'] = ($val['isvideo'] == 1) ? buildImgUrl($val['img_url']) : $remote_url;
            $feedbacks[$key]['show_video_url'] = U('MovieActivity/showFeedbackVideo', array('id' => $val['id']));
        }
        $info = $feedbacks;
        $code = 0;
        $message = '反馈信息';
        $return = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    //测试接口
    public function test()
    {
        $method = I('reqeust.method', null);
        switch ($method) {
            case 'boxoffice':
                dump($this->BoxOffice(626153));
                break;
            default:
                break;
        }
    }

    //电影票房
    public function BoxOffice($id)
    {
        $url = "http://www.cbooo.cn/m/" . $id;
        $params = array();
        $paramstring = http_build_query($params);
        $content = $this->juhecurl($url, $paramstring);
        preg_match('#<span class="m-span">累计票房<br />([\d.]+)万</span>#', $content, $match);

        if (empty($match)) {
            return 0;
        } else {
            return intval($match[1]);
        }
    }

    /**
     * 聚合请求接口返回内容
     * @param  string $url [请求的URL地址]
     * @param  string $params [请求的参数]
     * @param  int $ipost [是否采用POST形式]
     * @return  string
     */
    public function juhecurl($url, $params = false, $ispost = 0)
    {
        $httpInfo = array();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'JuheData');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        if ($ispost) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch, CURLOPT_URL, $url);
        } else {
            if ($params) {
                curl_setopt($ch, CURLOPT_URL, $url . '?' . $params);
            } else {
                curl_setopt($ch, CURLOPT_URL, $url);
            }
        }
        $response = curl_exec($ch);
        if ($response === false) {
            \Think\Log::record('curl执行错误' . curl_error($ch), 'WARN');
            //echo "cURL Error: " . curl_error($ch);
            return false;
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $httpInfo = array_merge($httpInfo, curl_getinfo($ch));
        curl_close($ch);
        return $response;
    }

    //创建普通包场活动
    public function createActivity()
    {
        //需要登录
        if (!$this->checkLogin()) {
            $info = null;
            $code = 2;
            $message = C('no_login');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $uid = $this->getUid();
        $fid = I('request.fid', null);
        $mid = I('request.mid', null, 'intval');
        $forums = $this->getUserForum($uid);
        $bool = $this->checkInForum($fid, $forums);

        //权限验证
        if (!$bool) {
            $info = null;
            $code = 3;
            $message = C('no_auth');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        //电影信息
        $forumMovieModel = D('ForumMovie');
        $districtModel = D('District');
        $releasetime = $forumMovieModel->where(array('fid' => $fid, 'status' => 1))->getField('releasetime');
        $now = time();
        $limit_start = $now + 10 * 60 * 60 * 24;
        $limit_start = ($limit_start < $releasetime) ? $releasetime : $limit_start;
        $limit_end = $releasetime + 30 * 60 * 60 * 24;
        $data['limit_start'] = $limit_start;
        $data['limit_end'] = $limit_end;
        $data['fid'] = $fid;
        $data['mid'] = $mid;

        $content = $this->fetch('MovieActivity/create_activity');
        $this->ajaxReturn(array('status' => 0, 'info' => $content));

        $this->ajaxReturn($return);
    }

    //创建定向包场活动
    public function createRecognitionActivity()
    {
        //地址信息
        $uid = $this->getUid();
        $fid = I('request.fid', null);
        $rid = I('request.rid', null, 'intval');
        $forums = $this->getUserForum($uid);

        //电影信息
        $recognitionActivityModel = D('RecognitionActivity');
        $activityInfo = $recognitionActivityModel->where(array('id' => $rid))->find();
        $this->assign('activityInfo', $activityInfo);
        $bool = $this->checkInForum($fid, $forums);

        if (!$bool) {
            $this->ajaxReturn(array('status' => 1, 'info' => 'not in forum'));
        }
        $this->assign('fid', $fid);
        $this->assign('rid', $rid);
        $districtModel = D('District');
        $provinces = $districtModel->getAllProvince(0);
        $this->assign('provinces', $provinces);
        $cities = $districtModel->getCityByProvince(1);
        $this->assign('cities', $cities);
        $content = $this->fetch('MovieActivity/create_recognition_activity');
        $this->ajaxReturn(array('status' => 0, 'info' => $content));
    }

    //根据省份和城市判断是否有反向包场活动
    public function getCinemaByPlace()
    {
        $pid = I('request.pid', null, 'intval');
        $cid = I('request.cid', null, 'intval');
        $mid = I('request.mid', null, 'intval');

        if (empty($cid) || empty($pid)) {
            $info = null;
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $moviePlaceModel = D('MoviePlace');
        $cinemas = $moviePlaceModel->where(array('pid' => $pid, 'cid' => $cid, 'status' => 0))->select();
        $info = $cinemas;
        $code = 0;
        $message = '影院信息';
        $return = $this->buildReturn($info, $message, $return);
        $this->ajaxReturn($return);
    }

    //创建活动处理
    public function createActivityDo()
    {
        if (!$this->checkLogin()) {
            $info = null;
            $code = 2;
            $message = C('no_login');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $activityModel = D('Activity');
        $moviePlaceModel = D('MoviePlace');
        $uid = $this->getUid();
        $fid = I('request.fid', null, 'intval');
        $mid = I('request.mid', null, 'intval');
        $expecttime = I('request.expecttime',null,'strtotime');
        $holdprovince = I('reqeust.holdprovince', null, 'intval');
        $holdcity = I('request.holdcity', null, 'intval');
        $cinemaid = I('request.cinemaid');
        $cinemaname = $moviePlaceModel->getFieldById($cinemaid, 'title');
        $telephone = I('request.telephone', null);
        
        $captcha = I('request.captcha',null);
        $session_captcha = session('captcha');
        if(empty($captcha)||($captcha!=$session_captcha)){
            $info = null;
            $code = 4;
            $message = '验证码不正确';
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }


        if (empty($holdprovince)) {
            $districtModel = D('District');
            $holdprovince = $districtModel->getFieldById($holdcity, 'upid');
        }

        $forums = $this->getUserForum($uid);

        $bool = $this->checkInForum($fid, $forums);

        if (!$bool) {
            $info = null;
            $code = 3;
            $message = '请先加入工作室';
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        if (empty($fid) || empty($telephone) || empty($expecttime) || empty($holdprovince) || empty($holdcity)) {
            $info = null;
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $data['fid'] = $fid;
        $data['uid'] = $uid;
        $data['movie'] = $mid;
        $data['category'] = 1;//包场看电影
        $data['ip'] = get_client_ip();
        $data['cinemaid'] = $cinemaid;
        $data['cinemaname'] = $cinemaname;
        $data['expecttime'] = $expecttime;
        $data['holdprovince'] = $holdprovince;
        $data['holdcity'] = $holdcity;
        $data['subject'] = $cinemaname;
        $data['movie'] = $mid;
        $data['telephone'] = $telephone;
        $data['addtime'] = time();

        if (!$activityModel->create($data)) {
            $info = $activityModel->getError();
            $this->ajaxReturn(array('stauts' => 1, 'info' => $info));
        } else {
            if ($aid = $activityModel->add($data)) {
                $forumUserModel = D('ForumUser');
                $forumUserModel->where(array('fid' => $fid, 'uid' => $this->getUid()))->setInc('createactivitynum');
                $url = U('Pc/activity', array('aid' => $aid));
                $info = $url;
                $code = 0;
                $message = '影+工作人员将于两日内与您确认包场信息，请保持电话畅通。';
                $return = $this->buildReturn($info, $code, $message);
                $this->ajaxReturn($return);

            } else {
                $info = $activityModel->getError();
                $code = 1;
                $message = '创建包场活动失败';
                $return = $this->buildReturn($info, $code, $message);
                $this->ajaxReturn($return);
            }
        }
    }

    //创建定向活动处理
    public function createRecognitionActivityDo()
    {
        $uid = $this->getUid();
        $fid = I('request.fid', null, 'intval');
        $rid = I('request.rid', null, 'intval');
        $telephone = I('request.telephone',null,'intval');
        $bool = checkIsInForum($uid, $fid);

        if (!$this->checkLogin()) {
            $info = null;
            $code = 2;
            $message = C('no_login');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        if (empty($fid) || empty($rid)||empty($telephone)) {
            $info = null;
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        if (!$bool) {
            $info = null;
            $code = 3;
            $message = '请先加入工作室';
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $recognitionActivityModel = D('RecognitionActivity');
        $activityInfo = $recognitionActivityModel->where(array('id' => $rid))->find();

        if (empty($activityInfo)) {
            $info = null;
            $code = 1;
            $message = '包场不存在';
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $data['holdprovince'] = $activityInfo['province'];
        $data['holdcity'] = $activityInfo['city'];
        $data['movie'] = $activityInfo['mid'];
        $data['ip'] = get_client_ip();
        $data['telephone'] = $telephone;
        $data['detailaddress'] = $activityInfo['detailaddress'];
        $data['cinemaid'] = $activityInfo['cinema'];
        $data['enrolltotal'] = $activityInfo['enrolltotal'];
        $data['cinemaname'] = getCinemaNameById($activityInfo['cinema']);
        $data['subject'] = getCinemaNameById($activityInfo['cinema']);
        $data['holdstart'] = $activityInfo['holdstart'];
        $data['enrollendtime'] = $activityInfo['enrollendtime'];
        $data['addtime'] = time();
        $data['uid'] = $uid;
        $data['fid'] = $fid;
        $data['audit'] = 1; //定向包场直接审核通过
        $data['category'] = 1; //包场电影

        $activityModel = D('Activity');
        if (!$activityModel->create($data)) {
            $info = $activityModel->getError();
            $code = 1;
            $message = '包场活动创建失败';
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        if ($aid = $activityModel->add($data)) {
            $recognitionActivityModel->where(array('id' => $activityInfo['id']))->save(array('aid' => $aid));
            $url = U('MovieActivity/detail', array('aid' => $aid));
            //进行报名处理
            $activityEnrollInfo = D('ActivityEnroll');
            $meta['aid'] = $aid;
            $meta['uid'] = $uid;
            $meta['telephone'] = $telephone;
            $meta['addtime'] = time();
            $activityEnrollInfo->add($meta);
            //添加影票价格
            $activityMovieTicketModel = D('ActivityMovieTicket');
            $activityMovieTicketModel->add(array('aid' => $aid, 'price' => $activityInfo['price']));
            //发送系统消息
            $messageModel = D('message');
            $data['fid'] = $fid;
            $data['touid'] = $uid;
            $data['aid'] = $aid;
            $data['content'] = '恭喜你成功发起包场为了让更多的伙伴参与进来，贴吧，微博，QQ群都是不错的宣传渠道哦~ 号召更多的伙伴一起为爱豆加油吧！';
            $data['addtime'] = time();
            $data['uid'] = '0';
            $data['subject'] = '活动消息';
            $messageModel->add($data);
            $recognitionActivityModel->where(array('id' => $rid))->setField(array('status' => 2));
            $info = $url;
            $code = 0;
            $message = '成功';
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        } else {
            $info = $activityModel->getError();
            $code = -1;
            $message = '创建包场活动失败';
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

    }

    //包场活动详细信息
    public function detail()
    {
        $id = I('request.id', null, 'intval');
        $uid = $this->getUid();
        //参数错误
        if (empty($id)) {
            $info = null;
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $activityModel = D('Activity');
        $activity = $activityModel->where(array('id' => $id, 'category' => 1))->find();
        //活动不存在
        if (empty($activity)) {
            $info = null;
            $code = 1;
            $message = '活动不存在';
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
        $data['activity'] = $activity;

        //工作室信息
        $fid = $activity['fid'];
        $forumModel = D('Forum');
        $forum_info = $forumModel->getForumInfoById($fid);
        $data['forum'] = $forum_info;

        //经纪人信息
        $forumUserModel = D('ForumUser');
        $admin_info = $forumUserModel->getAdminUserInfo($fid);
        $data['forum_admin'] = $admin_info;

        //反馈信息
        $activityMovieFeedbackModel = D('ActivityMovieFeedback');
        $feedbacks = $activityMovieFeedbackModel->where(array('status' => 0, 'aid' => $aid))->order(array('isvideo' => 'desc', 'id' => 'asc'))->select();
        $data['feedbacks'] = $feedbacks;

        //预告视频
        $activityMovieVideoModel = D('ActivityMovieVideo');
        $videos = $activityMovieVideoModel->where(array('status' => 0, 'aid' => $aid))->select();
        $data['videos'] = $videos;

        //活动海报
        $activityMoviePosterModel = D('ActivityMoviePoster');
        $posters = $activityMoviePosterModel->where(array('status' => 0, 'aid' => $aid))->select();
        $data['posters'] = $posters;

        //个性样式加载
        $data['pathname'] = $forum_info['pathname'];

        //票房信息
        $mid = $activity['movie'];
        $forumMovieModel = D('ForumMovie');
        $box_title = $forumMovieModel->getFieldById($mid, 'box_office_id');
        $total_box_office = $this->BoxOffice($box_title);
        $data['total_box_office'] = $total_box_office;

        //获取已经报名人数
        $activityEnrollModel = D('ActivityEnroll');
        $enrollInfo = $activityEnrollModel->getUserHasEnroll($aid);
        $data['enrollInfo'] = $enrollInfo;

        //其他热门活动话题
        $hot_length = C('OTHER_HOT_ACTIVITY_SHOW');
        $other_activities = $activityModel->getOtherCurrentActivity($aid, $fid, $hot_length);
        $other_activities_count = count($other_activities);
        $topicModel = D('Topic');
        $other_topics = $topicModel->getOtherCurrentTopic(0, $fid, $hot_length - $other_activities_count);
        $data['other_activities'] = $other_activities;
        $data['other_topics'] = $other_topics;

        $info = $data;
        $code = 0;
        $message = '活动详细信息';
        $return = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    //详情页相关信息赋值
    public function assignDetailInfo($aid)
    {

    }

    //电影反馈图片处理上传
    public function uploadImg()
    {
        $aid = I('post.aid', null, 'intval');
        $uid = $this->getUid();
        $upload = new \Think\Upload();
        $upload->maxSize = C('MOVIE_ACTIVITY_UPLOAD_SIZE');
        $upload->exts = C('MOVIE_ACTIVITY_UPLOAD_EXT');
        $upload->savePath = C('UPLOAD_TEMP_PATH');
        $width = C('MOVIE_ACTIVITY_IMG_WIDHT');
        $height = C('MOVIE_ACTIVITY_IMG_HEIGHT');
        $info = $upload->upload();

        if (!$info) {
            $this->ajaxReturn(json_encode(array('status' => 0, 'info' => $upload->getError())), 'EVAL');
        } else {
            //找到文件的路径
            $img_add = './Uploads/' . $info['feedback_img']['savepath'] . $info['feedback_img']['savename'];

            $image = new \Think\Image();
            $image->open($img_add);
            if ($image->width() < $width) {
                $this->ajaxReturn(json_encode(array('status' => 0, 'info' => '图片宽度小于' . $width)), 'EVAL');
            } else if ($image->height() < $height) {
                $this->ajaxReturn(json_encode(array('status' => 0, 'info' => '图片高度小于' . $height)), 'EVAL');
            } else {
                $save_time = time();
                $img_add = './Uploads/MovieActivity/Img/' . $save_time . '.' . $info['feedback_img']['ext'];
                $imgInfo = $image->save($img_add);
                $img_width = $imgInfo->width();
                $img_height = $imgInfo->height();

                //用户上传海报需要利用原图生成缩略图
                $attachmentModel = D('Attachment');
                $data_thumb['filename'] = '电影反馈海报';
                $data_thumb['path'] = $img_add;
                $data_thumb['isimage'] = 1;
                $data_thumb['uid'] = $uid;
                $id = $attachmentModel->add($data_thumb);
                $activity_img = array(
                    'img_url' => $img_add,
                    'width' => $img_width,
                    'height' => $img_height,
                    'attachmentid' => $id,
                    'thumb_id' => $id,
                    'aid' => $aid,
                    'uid' => $uid,
                    'addtime' => time(),
                );

                $activityMovieFeedbackModel = D('ActivityMovieFeedback');
                $activityMovieFeedbackModel->add($activity_img);

                $this->ajaxReturn(json_encode(array('status' => 0, 'info' => '上传图片成功')), 'EVAL');
            }

        }
    }

    //海报上传
    public function uploadFeedBackImg()
    {
        $aid = I('request.aid', null, 'intval');
        $attachmentid = I('request.attachmentid',null,'intval');
        $uid = $this->getUid();

        $attachmentModel = D('Attachment');
        $attachment = $attachmentModel->where(array('id'=>$attachmentid))->find();

        if(empty($uid)){
            $info = 'no_login';
            $code = -1;
            $message = C('no_login');
            $return = $this->buildReturn($info,$code,$message);
            $this->ajaxReturn($return);
        }

        if(empty($aid)||empty($attachmentid)){
            $info = 'parameter_invalid';
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info,$code,$message);
            $this->ajaxReturn($return);
        }

        $activityModel = D('Activity');
        $activity = $activityModel->where(array('id'=>$aid))->find();
        $holdstart = $activity['holdstart'];
        $now = time();
        if($now<$holdstart){
            $info = 'error';
            $code = 1;
            $message = '包场活动未开始';
            $return = $this->buildReturn($info,$code,$message);
            $this->ajaxReturn($return);
        }


        $activity_img = array(
            'img_url' => $attachment['remote_url'],
            'width' => $attachment['width'],
            'height' => $attachment['width'],
            'attachmentid' => $attachmentid,
            'thumb_id' => $attachmentid,
            'aid' => $aid,
            'uid' => $uid,
            'addtime' => time(),
        );
        $activityMoviePosterModel = D('ActivityMoviePoster');
        $activityMoviePosterModel->add($activity_img);

        $this->ajaxReturn(json_encode(array('status' => 0, 'info' => '上传图片成功')), 'EVAL');



    }

    //删除活动海报
    public function deletePoster()
    {
        $ids = I('post.ids');
        $ids = trim($ids, ',');
        $ids_arr = explode(",", $ids);
        $activityMoviePosterModel = D('ActivityMoviePoster');
        foreach ($ids_arr as $key => $val) {
            $activityMoviePosterModel->where(array('id' => $val))->setField(array('status' => 1));
        }
        $this->ajaxReturn(array('status' => 0, 'info' => '删除成功'));
    }

    //删除活动反馈
    public function deleteFeedback()
    {
        $ids = I('post.ids');
        $ids = trim($ids, ',');
        $ids_arr = explode(",", $ids);
        $activityMovieFeedbackModel = D('ActivityMovieFeedback');
        foreach ($ids_arr as $key => $val) {
            $activityMovieFeedbackModel->where(array('id' => $val))->setField(array('status' => 1));
        }
        $info = null;
        $message = '删除成功';
        $code = 0;
        $return = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    //活动主封面上传
    public function uploadCoverImg()
    {
        $aid = I('post.aid', null, 'intval');
        $upload = new \Think\Upload();
        $upload->maxSize = C('MOVIE_ACTIVITY_UPLOAD_SIZE');
        $upload->exts = C('MOVIE_ACTIVITY_UPLOAD_EXT');
        $upload->savePath = C('UPLOAD_TEMP_PATH');
        $width = C('MOVIE_ACTIVITY_IMG_WIDHT');
        $height = C('MOVIE_ACTIVITY_IMG_HEIGHT');
        $info = $upload->upload();

        if (!$info) {
            $this->ajaxReturn(json_encode(array('status' => 0, 'info' => '图片的大小超过2M')), 'EVAL');
        } else {
            //找到文件的路径
            $img_add = './Uploads/' . $info['upload_cover_img']['savepath'] . $info['upload_cover_img']['savename'];

            $image = new \Think\Image();
            $image->open($img_add);
            if ($image->width() < $width) {
                $this->ajaxReturn(json_encode(array('status' => 0, 'info' => '图片宽度小于' . $width)), 'EVAL');
            } else if ($image->height() < $height) {
                $this->ajaxReturn(json_encode(array('status' => 0, 'info' => '图片高度小于' . $height)), 'EVAL');
            } else {
                $name = substr($info['upload_cover_img']['savename'], 0, -4);
                $thumb_add = './Uploads/temp/' . $name . '_thumb.' . $info['upload_cover_img']['ext'];
                $thumb_img = $image->thumb(C('MOVIE_ACTIVITY_UPLOAD_WIDTH'), C('MOVIE_ACTIVITY_UPLOAD_HEIGHT'))->save($thumb_add);
                $img_width = $thumb_img->width();
                $img_height = $thumb_img->height();
                $thumb_add_img = '/Uploads/temp/' . $name . '_thumb.' . $info['upload_cover_img']['ext'];
                $this->assign('thumb_add_img', $thumb_add_img);
                $this->assign('img_width', $img_width);
                $this->assign('img_height', $img_height);
                $this->assign('primaryimg', $img_add);
                $this->assign('aid', $aid);
                $content = $this->fetch('upload_cover_img');
                $this->ajaxReturn($content, 'EVAL');

            }

        }
    }

    //活动报名url获取
    public function getEnrollUrl()
    {
        $aid = I('request.aid', null, 'intval');
        $uid = $this->getUid();
        if (!$this->checkLogin()) {
            $info = 'no_login';
            $code = 2;
            $message = C('no_login');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
            
        }

        $activityModel = D('Activity');
        $enrollendtime = $activityModel->where(array('id' => $aid))->getField('enrollendtime');
        $now = time();
        if ($now > $enrollendtime) {
            $info = 'error';
            $code = 1;   
            $message = '活动已结束';
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $direct_pay = $activityModel->where(array('id' => $aid))->getField('direct_pay');
        if($direct_pay){
            //直接插入报名表，返回页面.
            //插入报名表
            $activityEnrollModel = D('ActivityEnroll');
            $ticketnum            = 1;
            $telephone            = '';
            $addtime              = time();
            $bool                 = $activityEnrollModel->where(array('aid' => $aid, 'uid' => $uid))->find();
            if ($bool) {
                $activityEnrollModel->where(array('uid' => $uid, 'aid' => $aid))->setField(array('telephone' => $telephone));
                //$activityEnrollModel->where(array('uid' => $uid, 'aid' => $aid))->setInc('ticketnum', $ticketnum);
            } else {
                $activityEnrollModel->add(array('uid' => $uid, 'aid' => $aid, 'ticketnum' => $ticketnum, 'telephone' => $telephone, 'addtime' => $addtime));
            }
            
            $url = U('Pc/activity', array('aid' => $aid));
            if(isMobile()){
                $url = U('Mobile/chat', array('pid' => $aid,'type'=>2));
            }

            $info = $url;
            $code = 0;
            $message = '支付页面';
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $info = U('MovieActivity/enrollActivity', array('aid' => $aid));
        $code = 0;
        $message = '支付页面';
        $return = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    //报名活动
    public function enrollActivity()
    {
        $aid = I('request.aid', null, 'intval');
        $uid = $this->getUid();
        
        if(empty($aid)){
            $info = 'parameter_invalid';
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
        
        $activityModel = D('Activity');
        $activityMovieTicketModel = D('ActivityMovieTicket');
        $activity = $activityModel->where(array('id' => $aid, 'category' => 1))->find();
        $ticket = $activityMovieTicketModel->where(array('aid' => $aid))->find();
        if (empty($activity)) {
            $info = 'parameter_invalid';
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $this->assign('ticket', $ticket);
        $this->assign('activity', $activity);
        if(isMobile()){
            $this->display('mobile/pay');
        }else{
            $this->display('pc/pay'); 
        }
        
    }

    //展示视频
    public function showVideo()
    {
        $id = I('request.id', null, 'intval');
        $activityMovieVideoModel = D('ActivityMovieVideo');
        $video = $activityMovieVideoModel->where(array('id' => $id))->find();
        $this->assign('video', $video);
        $this->display('Index/show_video');
    }

    //展示反馈视频
    public function showFeedbackVideo()
    {
        $id = I('request.id', null, 'intval');
        $activityMovieFeedbackModel = D('ActivityMovieFeedback');
        $video = $activityMovieFeedbackModel->where(array('id' => $id))->find();
        $video['url'] = $video['video_url'];
        $video['mobileurl'] = $video['video_url'];
        $this->assign('video', $video);
        $this->display('Index/show_feedback_video');
    }

    //REMOVE支付方式
    public function payMethod()
    {
        $aid = I('request.aid', null, 'intval');
        $amount = I('request.quantity', null, 'intval');
        $telephone = I('request.telephone', null, 'intval');
        $activityModel = D('Activity');
        $activityMovieTicketModel = D('ActivityMovieTicket');
        $ticket = $activityMovieTicketModel->where(array('aid' => $aid))->find();
        $price = $ticket['price'];
        $cinemaname = $activityModel->getFieldById($aid, 'cinemaname');
        $trade_no = $this->get_order_sn();
        $ordsubject = $cinemaname . '影加包场票';
        $ordtotal_fee = number_format($price * $amount, 2);
        $ordbody = $cinemaname . '订单描述';
        $ordshow_url = "http://" . $_SERVER['HTTP_HOST'] . U('MovieActivity/detail', array('aid' => $aid));

        $this->assign('trade_no', $trade_no);
        $this->assign('ordsubject', $ordsubject);
        $this->assign('ordtotal_fee', $ordtotal_fee);
        $this->assign('ordbody', $ordbody);
        $this->assign('ordshow_url', $ordshow_url);

        $this->assign('telephone', $telephone);
        $this->assign('amount', $amount);
        $this->assign('aid', $aid);
        $this->display('pay_method');
    }

    //检测订单是否支付
    public function checkHasPay() {
        $trade_no = I('request.trade_no', null);
        $activityMovieOrder = D('ActivityMovieOrder');
        $order = $activityMovieOrder->field('order_status')->where(array('trade_no' => $trade_no))->find();
        $order_status = $order['order_status'];
        if ($order_status == "1") {
            $this->ajaxReturn(array('info' => 0, 'message' => '订单已支付'));
        } else {
            $this->ajaxReturn(array('info' => 1, 'message' => '订单未支付'));
        }
    }

    public function baiwansenlin(){
         $this->display('mobile/baiwansenlin');
    }

    //支付
    public function payOrder()
    {
        //支付方式等信息
        $method = I('request.method', null, 'intval');
        $aid = I('request.aid', null, 'intval');
        $amount = I('request.quantity', null, 'intval');
        $telephone = I('request.activity_telephone', null);
        $uid = $this->getUid();
        if (empty($uid)) {
            $info = null;
            $code = 2;
            $message = C('no_login');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
        if (empty($aid) || empty($method) || empty($telephone)) {
            $info = null;
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }


        $activityModel = D('Activity');
        $activityMovieTicketModel = D('ActivityMovieTicket');
        $ticket = $activityMovieTicketModel->where(array('aid' => $aid))->find();
        $price = $ticket['price'];

        $total_fee = number_format($price * $amount, 2);

        //支付页面处理
        $cinemaname = $activityModel->getFieldById($aid, 'cinemaname');
        $trade_no = $this->get_order_sn();
        $ordsubject = $cinemaname . '影加包场票';
        $ordtotal_fee = $total_fee;
        $ordbody = $cinemaname . '订单描述';
        $ordshow_url = "http://" . $_SERVER['HTTP_HOST'] . U('MovieActivity/detail', array('aid' => $aid));

        //下订单
        $data = array(
            'trade_no' => $trade_no,
            'uid' => $uid,
            'aid' => $aid,
            'telephone' => $telephone,
            'pay_id' => $method,
            'goods_amount' => $amount,
            'order_fee' => $total_fee,
            'add_time' => time(),
        );

        //插入订单表
        $activityMovieOrder = D('ActivityMovieOrder');
        $id = $activityMovieOrder->add($data);

        if ($method == "1") {
            R('Pay/doalipay', array('trade_no' => $trade_no, 'ordsubject' => $ordsubject, 'ordtotal_fee' => $ordtotal_fee, 'ordbody' => $ordbody, 'ordshow_url' => $ordshow_url));
        } else {
            $pay_url = $this->getWxPayUrl($ordsubject, $cinemaname, $total_fee, $trade_no, $id);
            $pay_url = urlencode($pay_url);
            $info = U('Pc/payweixin', array('aid' => $aid, 'trade_no' => $trade_no, 'pay_url' => $pay_url));
            $code = 0;
            $message = '订单成功';
            $return = $this->buildReturn($info, $code, $message);
            //$this->ajaxReturn($return);
            header("Location:".$info);
        }
    }

    //显示微信支付
    public function getWxPayUrl($title, $cinemaname, $total_fee, $trade_no, $product_id)
    {
        Vendor('Wxpay.Api');
        Vendor('Wxpay.NativePay');

        $notify = new \NativePay();
        $total_fee = 100 * $total_fee;
        $notify_url = C('wxpay.wx_notify_url');
        $goods_tag = 'none';

        $input = new \WxPayUnifiedOrder();
        $input->SetBody($title);
        $input->SetAttach($cinemaname);
        $input->SetOut_trade_no($trade_no);
        $input->SetTotal_fee($total_fee); //金额，单位分
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 60 * 60));
        $input->SetGoods_tag($goods_tag); //代金券
        $input->SetNotify_url($notify_url);
        $input->SetTrade_type("NATIVE");
        $input->SetProduct_id($product_id);
        $result = $notify->GetPayUrl($input);
        $url = $result["code_url"];
        return $url;

    }

    /**
     * 得到新订单号
     * @return  string
     */
    public function get_order_sn()
    {
        /* 选择一个随机的方案 */
        mt_srand((double)microtime() * 1000000);

        return date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
    }

    //验证码验证
    public function checkCaptcha()
    {
        $captcha = I('request.captcha', null);
        $session_captcha = session('captcha');
        if (($captcha != $session_captcha) || empty($captcha)) {
            $this->ajaxReturn(array('status' => 1, 'info' => '验证码错误'));
        } else {
            $this->ajaxReturn(array('status' => 0, 'info' => 'success'));
        }
    }

    public function checkTelephone()
    {
        $telephone = I('post.telephone');
        if ($this->checkIsMobile($telephone)) {
            if ($this->sendMessage($telephone)) {
                $this->ajaxReturn(array('status' => 0, 'info' => '发送成功'));
            } else {
                $this->ajaxReturn(array('status' => 2, 'info' => '发送失败，请稍后再试~'));
            }
        } else {
            $this->ajaxReturn(array('status' => 1, 'info' => '手机号错误'));
        }

    }

    /**
     * Name:sendMessage
     * Describe:发送验证码
     * Return:true或false
     */
    public function sendMessage($telephone)
    {
        $registerBlock = D('RegisterBlock');
        $rand = rand(C('PHONE_MIN'), C('PHONE_MAX')); //产生随机数
        // session('movie_captcha', $rand);  //将产生的随机数设置到session中
        session('movie_captcha', 123456);
        $content = C('PHONE_CAPTCHA_MESSAGE_PREFIX') . $rand . C('PHONE_CAPTCHA_MESSAGE_POSTFIX'); //发送短信的内容
        //$content = iconv("GB2312", "UTF-8", $content);
        $ip = get_client_ip(); //得到客户端的IP
        $date = date('Y-m-d'); //记录当天时间
        $ip_overflow = $registerBlock->checkMessageCountByIp($ip, $date);
        $phone_overflow = $registerBlock->checkMessageCountByTelephone($telephone, $date);
        if ($ip_overflow || $phone_overflow) {
            //可以发送验证码
            if (APP_DEBUG) {

                if ($this->SendSMS_debug($telephone, $content)) {
                    //表示发送成功
                    return true;
                } else {
                    //表示发送失败
                    return false;
                }
            } else {
                if ($this->SendSMS($telephone, $content)) {
                    //表示发送成功
                    return true;
                } else {
                    //表示发送失败
                    return false;
                }
            }

        } else {
            return false;
        }
    }



    public function checkIsRightCaptcha($captcha)
    {
        $session_captcha = session('captcha');
        if (($captcha != $session_captcha) || empty($captcha)) {
            return false;
        } else {
            return true;
        }
    }

    public function checkHasBindMobile($uid, $telephone)
    {
        $userModel = D('User');
        $result = $userModel->where(array('id' => $uid, 'telephone' => $telephone))->find();
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

}

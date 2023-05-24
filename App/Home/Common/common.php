<?php

//人民币格式化
function money_num_format($num)
{
    if (!is_numeric($num)) {
        return false;
    }
    $rvalue = '';
    $num = explode('.', $num); //把整数和小数分开
    $rl = !isset($num['1']) ? '' : $num['1']; //小数部分的值
    $j = strlen($num[0]) % 3; //整数有多少位
    $sl = substr($num[0], 0, $j); //前面不满三位的数取出来
    $sr = substr($num[0], $j); //后面的满三位的数取出来
    $i = 0;
    while ($i <= strlen($sr)) {
        $rvalue = $rvalue.','.substr($sr, $i, 3); //三位三位取出再合并，按逗号隔开
        $i = $i + 3;
    }
    $rvalue = $sl.$rvalue;
    $rvalue = substr($rvalue, 0, strlen($rvalue) - 1); //去掉最后一个逗号
    $rvalue = explode(',', $rvalue); //分解成数组
    if ($rvalue[0] == 0) {
        array_shift($rvalue); //如果第一个元素为0，删除第一个元素
    }
    $rv = $rvalue[0]; //前面不满三位的数
    for ($i = 1; $i < count($rvalue); ++$i) {
        $rv = $rv.','.$rvalue[$i];
    }
    if (!empty($rl)) {
        $rvalue = $rv.'.'.$rl; //小数不为空，整数和小数合并
    } else {
        $rvalue = $rv; //小数为空，只有整数
    }

    return $rvalue;
}

//判断是否是手机设备
function isMobile()
{
    // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
    if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
        return true;
    }
    // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
    if (isset($_SERVER['HTTP_VIA'])) {
        // 找不到为flase,否则为true
        return stristr($_SERVER['HTTP_VIA'], 'wap') ? true : false;
    }
    // 脑残法，判断手机发送的客户端标志,兼容性有待提高
    if (isset($_SERVER['HTTP_USER_AGENT'])) {
        $agent = $_SERVER['HTTP_USER_AGENT'];
        if (strpos($agent, 'iPad')) {
            return false;
        }

        $clientkeywords = array('nokia',
            'sony',
            'ericsson',
            'mot',
            'samsung',
            'htc',
            'sgh',
            'lg',
            'sharp',
            'sie-',
            'philips',
            'panasonic',
            'alcatel',
            'lenovo',
            'iphone',
            'ipod',
            'blackberry',
            'meizu',
            'android',
            'netfront',
            'symbian',
            'ucweb',
            'windowsce',
            'palm',
            'operamini',
            'operamobi',
            'openwave',
            'nexusone',
            'cldc',
            'midp',
            'wap',
            'mobile',
        );
        // 从HTTP_USER_AGENT中查找手机浏览器的关键字
        if (preg_match('/('.implode('|', $clientkeywords).')/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
            return true;
        }
    }
    // 协议法，因为有可能不准确，放到最后判断
    if (isset($_SERVER['HTTP_ACCEPT'])) {
        // 如果只支持wml并且不支持html那一定是移动设备
        // 如果支持wml和html但是wml在html之前则是移动设备
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
            return true;
        }
    }

    return false;
}

//获取线上活动参与人数
function getOnlineVoteNum($id)
{
    $activityOnlineModel = D('ActivityOnline');
    $count = $activityOnlineModel->where(array('aid' => $id, 'status' => 0))->count();

    return $count;
}

//保存网上路径
function auto_save_image($str)
{
    /*$img_array = array();
    preg_match_all("/(src)=[\"|\'| ]{0,}(http:\/\/(.*)\.(gif|jpg|jpeg|bmp|png))[\"|\'| ]{0,}/isU", $body, $img_array);
    $img_array = array_unique($img_array[2]);*/ //也可以自动匹配
    set_time_limit(0);
    $save_path = uniqid().'.png';
    $imgPath = './uploads/';
    $file_path = $imgPath.$save_path;

    if (!is_dir($imgPath)) {
        @mkdir($imgPath, 0777);
    }

    $get_file = @file_get_contents($str);
    $fp = @fopen($file_path, 'w');
    @fwrite($fp, $get_file);
    @fclose($fp);

    return $file_path;
}

//获取当前页面url
function get_url()
{
    $sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
    $php_self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
    $path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
    $relate_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $php_self.(isset($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : $path_info);

    return $sys_protocal.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '').$relate_url;
}

//获取用户积分
function getUserPoint($uid)
{
    $userModel = D('User');
    $point = $userModel->getFieldById($uid, 'point');

    return $point;
}

//获取积分类型
function getUserPointType($type)
{
    $userPointTypeModel = D('UserPointType');
    $name = $userPointTypeModel->field('name')->where(array('type' => $type))->find();

    return $name['name'];
}

//获取报名人数
function getActivityEnrollCount($aid)
{
    $activityEnrollModel = D('ActivityEnroll');
    $count = $activityEnrollModel->where(array('aid' => $aid, 'status' => 0))->count();

    return $count;
}

//获取活动海报图片
function getActivityImgById($id)
{
    $activityModel = D('Activity');
    $attachmentModel = D('Attachment');
    $img = $activityModel->getFieldById($id, 'img');
    $remote_url = $attachmentModel->getFieldById($img, 'remote_url');
    $relate_url = empty($remote_url) ? 'Public/default/img/iconfont-defaultpic.png' : $remote_url;

    return $remote_url;
}

//获取用户头像,可能字段保存的是url;
function getUserPhotoById($id)
{
    $UserProfileModel = D('UserProfile');
    $photo = $UserProfileModel->getUserPhotoById($id);
    if (is_numeric($photo)) {
        $attachmentModel = D('Attachment');
        $info = $attachmentModel->getAttachmentById($photo);

        return $info['remote_url'];
    } else {
        if (empty($photo)) {
            $face = '/Public/default/images/logo2.jpg';
            $UserProfileModel->where(array('uid' => $id))->setField(array('photo' => $face));

            return $face;
        } else {
            return $photo;
        }
    }
}

//对活动举办时间进行加工
function processActivityTime($holdstar, $holdend)
{
    $time = 24 * 60 * 60;
    if ($holdend - $holdstar < $time) {
        $holdstar = date('Y-m-d H:i', $holdstar);
        $holdend = date('Y-m-d H:i', $holdend);
    } else {
        $holdstar = date('Y-m-d', $holdstar);
        $holdend = date('Y-m-d', $holdend);
    }

    return $holdstar.'&nbsp;～&nbsp;'.$holdend;
}
function getTopicNum($fid)
{
    $topicModel = D('Topic');
    $count = $topicModel->where(array('fid' => $fid, 'status' => array('neq', 1)))->count();

    return $count;
}

function getActivityNum($fid)
{
    $activityModel = D('Activity');
    $count = $activityModel->where(array('fid' => $fid, 'status' => array('neq', 1), 'audit' => 1))->count();

    return $count;
}

function getFansNum($fid)
{
    $forumUserModel = D('ForumUser');
    $num = $forumUserModel->where(array('status' => 0, 'fid' => $fid))->count();

    return $num;
}

//用户头像是否上传
function checkHasUserPhoto($uid)
{
    $UserProfileModel = D('UserProfile');
    $photo = $UserProfileModel->getFieldByUid($uid, 'photo');
    $result = empty($photo) ? false : true;

    return $result;
}

//生成图片路径
function buildImgUrl($url)
{
    $result = '';
    if (empty($url)) {
        $result = 'http://'.$_SERVER['HTTP_HOST'].'/Public/default/img/face/default_face.png';
    } else {
        $b = substr($url, 0, 1);
        if ($b == '.' || $b == '/') {
            $result = 'http://'.$_SERVER['HTTP_HOST'].'/'.$url;
        } else {
            $result = $url;
        }
    }

    return $result;
}

//获取今日签到数
function getTodaySigns($fid)
{
    $forumSignModel = D('ForumSign');
    $time = strtotime('today');
    $now = date('Y-m-d H:i:s', $time);
    $lingtime = date('Y-m-d H:i:s', $time + 24 * 60 * 60);
    $condition['fid'] = array('eq', $fid);
    $condition['updatetime'] = array(array('gt', $now), array('lt', $lingtime), 'and');
    $count = $forumSignModel->where($condition)->count();

    return $count;
}
//获取今日排名
function getTodaySignRank($fid)
{
    $forumModel = D('Forum');
    $forums = $forumModel->field('id')->where(array('status' => 1))->select();
    $rank_arr = array();
    foreach ($forums as $key => $val) {
        array_push($rank_arr, getTodaySigns($val['id']));
    }
    rsort($rank_arr);
    foreach ($rank_arr as $key => $val) {
        if ($val == getTodaySigns($fid)) {
            return $key + 1;
            break;
        }
    }
}

//获取工作室粉丝数
function getFensiNumByFid($fid)
{
    $forumModel = D('Forum');
    $fansnum = $forumModel->getFieldById($fid, 'fansnum');

    return $fansnum;
}

//获取粉丝数排名
function getFensiRank($fid)
{
    $forumModel = D('Forum');
    $forums = $forumModel->field('id,fansnum')->where(array('status' => 1))->select();
    $rank_arr = array();
    foreach ($forums as $key => $val) {
        array_push($rank_arr, ($val['fansnum']));
    }
    rsort($rank_arr);
    foreach ($rank_arr as $key => $val) {
        if ($val == getFensiNumByFid($fid)) {
            return $key + 1;
            break;
        }
    }
}

function getAidByResponseId($id)
{
    $activityResponseModel = D('ActivityResponse');
    $aid = $activityResponseModel->getFieldById($id, 'aid');

    return $aid;
}

function getTidByResponseId($id)
{
    $topicResponseModel = D('TopicResponse');
    $tid = $topicResponseModel->getFieldById($id, 'tid');

    return $tid;
}

//获取工作室活动数量
function getActivitiesByFid($fid)
{
    $forumModel = D('Forum');
    $activities = $forumModel->getFieldById($fid, 'activities');

    return $activities;
}
//获取活动数量排名
function getActivitiesRank($fid)
{
    $forumModel = D('Forum');
    $forums = $forumModel->field('id,activities')->where(array('status' => 1))->select();
    $rank_arr = array();
    foreach ($forums as $key => $val) {
        array_push($rank_arr, ($val['activities']));
    }
    rsort($rank_arr);
    foreach ($rank_arr as $key => $val) {
        if ($val == getActivitiesByFid($fid)) {
            return $key + 1;
            break;
        }
    }
}

//获取指定话题的回复数
function getTopicResponseCountByTid($tid)
{
    $topicResponseModel = D('TopicResponse');
    $count = $topicResponseModel->getTopicResponseCountByTid($tid);

    return $count;
}

//获取指定话题最新的回复时间
function getTipicLastResponseTimeByTid($tid)
{
    $topicResponseModel = D('TopicResponse');
    $lastResponseTime = $topicResponseModel->getTipicLastResponseTimeByTid($tid);
    $time = strtotime('today');
    if ($lastResponseTime > $time) {
        return date('H:i', $lastResponseTime);
    } else {
        if (empty($lastResponseTime)) {
            $topicModel = D('Topic');
            $createTime = $topicModel->getFieldById($tid, 'addtime');
            if ($createTime > $time) {
                return date('H:i', $createTime);
            } else {
                return date('m-d', $createTime);
            }
        } else {
            return date('m-d', $lastResponseTime);
        }
    }
}
//获取工作室话题数量
function getTopicsByFid($fid)
{
    $forumModel = D('Forum');
    $topics = $forumModel->getFieldById($fid, 'topics');

    return $topics;
}
//获取话题数量排名
function getTopicsRank($fid)
{
    $forumModel = D('Forum');
    $forums = $forumModel->field('id,topics')->where(array('status' => 1))->select();
    $rank_arr = array();
    foreach ($forums as $key => $val) {
        array_push($rank_arr, ($val['topics']));
    }
    rsort($rank_arr);
    foreach ($rank_arr as $key => $val) {
        if ($val == getTopicsByFid($fid)) {
            return $key + 1;
            break;
        }
    }
}

//获取用户当天是否签到
function checkIsSignByFidUid($fid, $uid)
{
    $forumSignModel = D('ForumSign');
    $condition['fid'] = $fid;
    $condition['uid'] = $uid;
    $time = strtotime('today');
    $now = date('Y-m-d H:i:s', $time);
    $lingtime = date('Y-m-d H:i:s', $time + 24 * 60 * 60);
    $condition['updatetime'] = array(array('gt', $now), array('lt', $lingtime), 'and');
    $info = $forumSignModel->where($condition)->find();
    if ($info) {
        return true;
    } else {
        return false;
    }
}

function getUserSelfDescById($id)
{
    $UserModel = D('UserProfile');
    $selfdesc = $UserModel->getFieldByUid($id, 'selfdesc');

    return $selfdesc;
}

//活动创建者
function getActivityUidById($aid)
{
    $activityModel = D('Activity');
    $result = $activityModel->getFieldById($aid, 'uid');

    return $result;
}

function getUserFavorForums($uid)
{
    $forumUserModel = D('ForumUser');
    $forumModel = D('Forum');
    $forums = $forumUserModel->field('fid')->where(array('uid' => $uid, 'status' => 0))->select();
    $forumname = '';
    foreach ($forums as $key => $val) {
        $img = $forumModel->getFieldById($val['fid'], 'photo');
        //dump($img);
        $forumname .= '<a href="'.U('Forum/index', array('fid' => $val['fid'])).'"><img src="'.getAttachmentUrlById($img).'"/></a>';
    }

    return $forumname;
}

//用户性别
function getUserGenderById($id)
{
    $UserModel = D('UserProfile');
    $gender = $UserModel->getFieldByUid($id, 'gender');
    if ($gender == 0) {
        $gender = '保密';
    } elseif ($gender == 1) {
        $gender = '男';
    } else {
        $gender = '女';
    }

    return $gender;
}

//查看用户是否是工作室经纪人
function checkIsForumAdmin($uid)
{
    $forumUserModel = D('ForumUser');
    $result = $forumUserModel->field('isadmin')->where(array('uid' => $uid))->select();
    foreach ($result as $key => $val) {
        if ($val['isadmin'] == 1) {
            return true;
        }
    }

    return false;
}

//查看用户是否是指定工作室经纪人
function checkIsForumAdminByUidFid($uid, $fid)
{
    $forumUserModel = D('ForumUser');
    $result = $forumUserModel->field('isadmin')->where(array('uid' => $uid, 'isadmin' => 1, 'fid' => $fid, 'status' => 0))->find();
    if ($result) {
        return true;
    } else {
        return false;
    }
}

//查看用户是否被封禁
function checkUserBan($uid, $fid)
{
    $time = time();
    $forumbanuserModel = D('ForumBanUser');
    $info = $forumbanuserModel->where(array('uid' => $uid, 'fid' => $fid))->find();
    if (empty($info)) {
        return false;
    } else {
        if ($info['status'] == 0) {
            return false;
        } else {
            if ($info['bantime'] + $info['totaltime'] < $time) {
                return false;
            } else {
                return true;
            }
        }
    }
}

//查看用户是否被封禁参与活动话题
function checkUserBanParticipate($uid, $fid)
{
    $time = time();
    $forumbanuserModel = D('ForumBanUser');
    $info = $forumbanuserModel->where(array('uid' => $uid, 'fid' => $fid, 'option' => array('neq' => 2)))->find();
    if (empty($info)) {
        return false;
    } else {
        if ($info['status'] == 0) {
            return false;
        } else {
            if ($info['bantime'] + $info['totaltime'] < $time) {
                return false;
            } else {
                return true;
            }
        }
    }
}
//查看用户是否被封禁创建活动话题
function checkUserBanCreate($uid, $fid)
{
    $time = time();
    $forumbanuserModel = D('ForumBanUser');
    $info = $forumbanuserModel->where(array('uid' => $uid, 'fid' => $fid, 'option' => 2))->find();
    if (empty($info)) {
        return false;
    } else {
        if ($info['status'] == 0) {
            return false;
        } else {
            if ($info['bantime'] + $info['totaltime'] < $time) {
                return false;
            } else {
                return true;
            }
        }
    }
}

//查看活动回复是否是@me
function checkActivityResponseToSelf($uid, $response_id)
{
    $activityresponseUserModel = D('ActivityResponseUser');
    $result = $activityresponseUserModel->checkActivityResponseToSelf($uid, $response_id);

    return $result;
}

//查看话题回复是否是@me
function checkTopicResponseToSelf($uid, $response_id)
{
    $topicresponseUserModel = D('TopicResponseUser');
    $result = $topicresponseUserModel->checkTopicResponseToSelf($uid, $response_id);

    return $result;
}

//获取话题添加时间通过id
function getTopicAddtimeById($tid)
{
    $topicModel = D('Topic');
    $addtime = $topicModel->getFieldById($tid, 'addtime');

    return $addtime;
}

function getFidByAid($aid)
{
    $activityModel = D('Activity');
    $fid = $activityModel->getFieldById($aid, 'fid');

    return $fid;
}

function getFidByTid($tid)
{
    $topicModel = D('Topic');
    $fid = $topicModel->getFieldById($tid, 'fid');

    return $fid;
}

function getActivityStatusById($id)
{
    $activityModel = D('Activity');
    $status = $activityModel->getFieldById($id, 'status');

    return $status;
}

function getActivityAuditStatus($id)
{
    $activityModel = D('Activity');
    $audit = $activityModel->getFieldById($id, 'audit');
    switch ($audit) {
        case 0:
            return '未审核';
            break;
        case 1:
            return '通过';
            break;
        case 2:
            return '未通过';
            break;
        default:
            return '';
            break;
    }
}

//根据话题id获取话题标题
function getTopicSubjectById($id)
{
    $topicModel = D('Topic');
    $subject = $topicModel->getFieldById($id, 'subject');

    return $subject;
}

//根据活动id获取活动标题
function getActivitySubjectById($id)
{
    $activityModel = D('Activity');
    $subject = $activityModel->getFieldById($id, 'subject');

    return $subject;
}

function getActivityEnrollNum($aid)
{
    $activityEncrollModel = D('ActivityEncroll');
    $count = $activityEncrollModel->where(array('aid' => $aid))->count();

    return $count;
}

//查看活动是否被推荐
function checkIsRecommend($aid)
{
    $activityRecommendModel = D('ActivityRecommend');
    $result = $activityRecommendModel->where(array('aid' => $aid, 'status' => 0, 'isrecommend' => 0))->find();
    if ($result) {
        return true;
    } else {
        return false;
    }
}

//根据活动id获取活动开始时间
function getActivityHoldStartById($id)
{
    $activityModel = D('Activity');
    $holdstart = $activityModel->getFieldById($id, 'holdstart');

    return $holdstart;
}

//根据活动id获取活动结束时间
function getActivityHoldEndById($id)
{
    $activityModel = D('Activity');
    $holdend = $activityModel->getFieldById($id, 'holdend');

    return $holdend;
}

/**
 * @param $basetime
 * @param $time
 *
 * @return array
 *               返回基准时间和当前评论应该显示的时间
 */
function showDate($basetime, $time)
{
    $return_time = null;
    $lingTime = strtotime('today');

    if ($basetime - $time > C('SHOW_TIME_INTERVAL')) {
        $basetime = $time;
        if ($time < $lingTime) {
            $return_time = date('m-d H:i:s', $time);
        } else {
            $return_time = date('H:i:s', $time);
        }
    }

    return array('basetime' => $basetime, 'return_time' => $return_time);
}

function BanUserTimeFormat($time)
{
    switch ($time) {
        case '3600':
            return '一小时';
            break;
        case '86400':
            return '一天';
            break;
        case '259200':
            return '三天';
            break;
        default:
            return '';
            break;
    }
}

/**
 * @return $nickname
 *                   根据用户id找到用户昵称
 */
function getUserNicknameById($id)
{
    $UserModel = D('User');
    $nickname = $UserModel->getFieldById($id, 'nickname');

    return $nickname;
}

/**
 * @param $id
 *
 * @return mixed
 *               获取用户真实姓名
 */
function getUserRealNameById($id)
{
    $UserProfileModel = D('UserProfile');
    $realname = $UserProfileModel->getFieldByUid($id, 'realname');

    return $realname;
}

/**
 * @param $uid
 * @param $forumid
 * 获取指定星吧指定用户总的签到数
 */
function getUserTotalSigns($uid, $fid)
{
    $ForumSignModel = D('ForumSign');
    $count = $ForumSignModel->field('count')->where(array('uid' => $uid, 'fid' => $fid))->find();

    return $count['count'];
}

/**
 * @param $uid
 * @param $fid
 * 获取指定星吧的指定用户连续签到数
 */
function getUserContinuationSigns($uid, $fid)
{
    $ForumSignModel = D('ForumSign');
    $lcount = $ForumSignModel->field('lcount')->where(array('uid' => $uid, 'fid' => $fid))->find();

    return $lcount['lcount'];
}

//获取回复
function getResponseCountByPidType($pid, $type)
{
    $responseModel = D('Response');
    $count = $responseModel->where(array('pid' => $pid, 'type' => $type, 'status' => 0))->count();

    return $count;
}

//查看活动是否有新回复
function checkActivityHasNewResponse($id)
{
    $activityModel = D('Activity');
    $lastpostid = $activityModel->where(array('id' => $id))->getField('lastpostid');
    $hasresponseid = $activityModel->where(array('id' => $id))->getField('hasresponseid');
    $lastpostid == $hasresponseid ? false : true;
}

//查看话题是否有新回复
function checkTopicHasNewResponse($id)
{
    $topicModel = D('Topic');
    $lastpostid = $topicModel->where(array('id' => $id))->getField('lastpostid');
    $hasresponseid = $topicModel->where(array('id' => $id))->getField('hasresponseid');
    $lastpostid == $hasresponseid ? false : true;
}

//获取在工作室中的排名
function getRankByFidUid($fid)
{
    $forumUserModel = D('ForumUser');
    $uid = session('uid');
    $id = $forumUserModel->field('id')->where(array('fid' => $fid, 'uid' => $uid))->find();
    $id = $id['id'];
    $result = $forumUserModel->where(array('fid' => $fid, 'status' => 0, 'id' => array('lt', $id)))->order(array('id' => 'asc'))->count();

    return $result + 1;
}

//获取工作室名字
function getForumNameById($id)
{
    $ForumModel = D('Forum');
    $forumname = $ForumModel->getForumNameById($id);

    return $forumname;
}
//获取工作室粉丝团昵称
function getFansgroupById($id)
{
    $ForumModel = D('Forum');
    $fansgroup = $ForumModel->getFansgroupById($id);

    return $fansgroup;
}
function getForumBanner($id)
{
    $ForumModel = D('Forum');
    $banner = $ForumModel->getFieldById($id, 'banner');

    return $banner;
}

function getUserDescById($id)
{
    $UserProfileModel = D('UserProfile');
    $desc = $UserProfileModel->getUserDescById($id);

    return $desc;
}

//获取活动相关信息
function getActivityStatusName($status)
{
    //状态描述
    //活动状态 0:正常 ;1:删除;2:报名中;3将要开始(报名结束活动开始之前);4进行中;5已结束;6已取消;7未举办;
    $name = '';
    switch ($status) {
        case 0:
            $name = '审核通过';
            break;
        case 1:
            $name = '删除';
            break;
        case 2:
            $name = '报名中';
            break;
        case 3:
            $name = '将要开始';
            break;
        case 4:
            $name = '进行中';
            break;
        case 5:
            $name = '已结束';
            break;
        case 6:
            $name = '已取消';
            break;
        case 7:
            $name = '未举办';
            break;
        default:
            $name = '';
            break;
    }

    return $name;
}

function getActivityIndexSmallImgById($id)
{
    $activityModel = D('Activity');
    $img = $activityModel->getFieldById($id, 'indexsmallimg');
    $path = getAttachmentUrlById($img);

    return $path;
}

function getActivityIndexBigImgById($id)
{
    $activityModel = D('Activity');
    $img = $activityModel->getFieldById($id, 'indexbigimg');
    $path = getAttachmentUrlById($img);

    return $path;
}

function getActivityTypeName($id)
{
    $activityTypeModel = D('ActivityType');
    $activityname = $activityTypeModel->getNameById($id);

    return $activityname;
}

function getOnlineTimeByForumIdAndUserId($fid, $uid)
{
    $ForumUserModel = D('ForumUser');
    $onlinetime = $ForumUserModel->getOnlineTimeByForumIdAndUserId($fid, $uid);

    return $onlinetime;
}

/**
 * @param $id
 * @param string $field
 *
 * @return mixed
 *               获取附件信息
 */
function getAttachmentById($id, $field = '')
{
    $attachmentModel = D('Attachment');
    $attachment = $attachmentModel->getAttachmentById($id);
    if (!empty($field)) {
        return $attachment[$field];
    } else {
        return $attachment;
    }
}

function generateQrcode($qr)
{
    $url = R('Qrcode/generateQrcode', array('qr' => $qr));

    return $url;
}

/**
 * 返回附件的url.
 */
function getAttachmentUrlById($id)
{
    $attachmentModel = D('Attachment');
    $attachment = $attachmentModel->getAttachmentById($id);
    if (empty($attachment['remote_url'])) {
        return $attachment['path'];
    } else {
        return $attachment['remote_url'];
    }
}

/**
 * @param $uid
 *
 * @return mixed
 *               获取用户邀约人数
 */
function getUserInvitesByUid($uid)
{
    $userProfileModel = D('UserProfile');
    $invites = $userProfileModel->getInvitesByUid($uid);

    return $invites;
}

/**
 * @param $uid
 * @param $fid
 *
 * @return mixed
 *               获取用户被点赞次数
 */
function getUserFavorsByUid($uid, $fid)
{
    $forumUserModel = D('ForumUser');
    $favors = $forumUserModel->getFavorsByUid($fid, $uid);

    return $favors;
}

/**
 * @param $uid
 * @param $fid
 *
 * @return 用户话题数
 *                         获取用户话题数
 */
function getUserTopicsByUid($uid, $fid)
{
    $forumUserModel = D('ForumUser');
    $topics = $forumUserModel->getTopicsByUid($uid, $fid);
    $topics = (empty($topics) || $topics < 0) ? 0 : $topics;

    return $topics;
}

/**
 * @param $uid 用户id
 * @param $fid 星吧id
 *
 * @return 用户创建活动数
 */
function getUserActivitiesByUid($uid, $fid)
{
    $forumUserModel = D('ForumUser');
    $activities = $forumUserModel->getActivitiesByUid($uid, $fid);
    $activities = (empty($activities) || $activities < 0) ? 0 : $activities;

    return $activities;
}

function getUserAbsenceactivitiesByUid($uid, $fid)
{
    $forumUserModel = D('ForumUser');
    $absenceactivities = $forumUserModel->getAbsenceactivitiesByUid($uid, $fid);

    return $absenceactivities;
}

function getLastabsenceactivityByUid($uid, $fid)
{
    $forumUserModel = D('ForumUser');
    $result = $forumUserModel->getLastabsenceactivityByUid($uid, $fid);

    return $result;
}

function getUserCancelActivityNum($uid, $fid)
{
    $forumUserModel = D('ForumUser');
    $result = $forumUserModel->getCancelactivitynumByUid($uid, $fid);

    return $result;
}

function getUnreadUserMessageCount($uid)
{
    $messageModel = D('Message');
    $message_count = $messageModel->getUnreadMessageCountByUid($uid);
    if ($message_count >= 10) {
        $message_count = '9+';
    }

    return $message_count;
}

//获取话题点赞数
function getTopicFavorNum($tid)
{
    $topicFavorModel = D('TopicFavor');
    $count = $topicFavorModel->where(array('tid' => $tid, 'status' => 1))->count();

    return $count;
}

//获取活动点赞数
function getActivityFavorNum($aid)
{
    $activityFavorModel = D('ActivityFavor');
    $count = $activityFavorModel->where(array('aid' => $aid, 'status' => 1))->count();

    return $count;
}

/**
 * @param $uid
 * @param $fid
 * 判断用户是否在工作室中
 */
function checkIsInForum($uid, $fid)
{
    $forumUserModel = D('ForumUser');

    $result = $forumUserModel->checkIsInforum($uid, $fid);

    if ($result) {
        return true;
    } else {
        return false;
    }
}

/**
 * @param $uid
 * @param $fid
 * 判断用户是否报名活动
 */
function checkIsEnroll($uid, $aid)
{
    $activityEnrollModel = D('ActivityEnroll');
    $result = $activityEnrollModel->checkIsEnroll($uid, $aid);
    if ($result) {
        return true;
    } else {
        return false;
    }
}

function getUserParticipateActivityCount($uid, $fid)
{
    $activityResponseModel = D('ActivityResponse');
    $response_activity_count = $activityResponseModel->where(array('uid' => $uid, 'fid' => $fid, 'status' => 0))->count('distinct(aid)');

    return $response_activity_count;
}
/**
 * @param $uid
 * @param $fid
 * 判断用户是收藏活动
 */
function checkIsCollectActivity($uid, $aid)
{
    $activityCollectModel = D('ActivityCollect');
    $result = $activityCollectModel->checkIsCollect($aid, $uid);
    if ($result) {
        return true;
    } else {
        return false;
    }
}
/**
 * @param $uid
 * @param $fid
 * 判断用户是收藏话题
 */
function checkIsCollectTopic($uid, $tid)
{
    $topicCollectModel = D('TopicCollect');
    $result = $topicCollectModel->checkIsCollect($tid, $uid);
    if ($result) {
        return true;
    } else {
        return false;
    }
}

function checkIsFavor($pid, $uid, $type)
{
    $favorModel = D('Favor');
    $result = $favorModel->where(array('pid' => $pid, 'uid' => $uid, 'type' => $type))->find();
    if ($result) {
        return true;
    } else {
        return false;
    }
}

//根据id获取地点名称
function getPlaceNameById($id)
{
    $districtModel = D('District');
    $name = $districtModel->getNameById($id);

    return $name;
}

//获取工作室实际粉丝数量
function getFollowerNumByFid($fid)
{
    $forumUserModel = D('ForumUser');
    $num = $forumUserModel->where(array('status' => 0, 'fid' => $fid))->count();

    return $num;
}

//查看活动是否投票
function checkHasVote($uid, $aid)
{
    $activityOnlineModel = D('ActivityOnline');
    $count = $activityOnlineModel->where(array('type' => 1, 'aid' => $aid, 'uid' => $uid, 'status' => 0))->count();
    if ($count) {
        return true;
    } else {
        return false;
    }
}

//TODO:检查用户权限
function checkPrivileges($privilege)
{
    $uid = session('uid');
    if (empty($uid)) {
        return false;
    }
    $forum_access = session('user_access');
    if (is_null($forum_access)) {
        return false;
    }
    foreach ($forum_access as $key => $val) {
        if ($privilege == $key) {
            if ($val == 1) {
                return true;
                break;
            } else {
                return false;
                break;
            }
        }
    }

    return true;
}

//第三方登陆方法：
//获取微博
function getWeiboByUid($uid)
{
    $userModel = D('User');
    $result = $userModel->getFieldById($uid, 'weibo');

    return $result;
}
//获取手机号
function getUserTelephoneById($id)
{
    $UserModel = D('User');
    $telephone = $UserModel->getFieldById($id, 'telephone');

    return $telephone;
}
//获取qq
function getQqByUid($uid)
{
    $userModel = D('User');
    $result = $userModel->getFieldById($uid, 'qq');

    return $result;
}

//是否有新消息函数
function checkActivityHasNews($aid)
{
    $activityModel = D('Activity');
    $hasresponseid = $activityModel->where(array('id' => $aid))->getFieldById($aid, 'hasresponseid');

    return $hasresponseid;
}

function checkEncrollActivityHasNews($aid)
{
    $uid = session('uid');
    $activityEncrollModel = D('ActivityEncroll');
    $encroll_info = $activityEncrollModel->field('hasresponseid')->where(array('uid' => $uid, 'aid' => $aid))->find();

    return $encroll_info['hasresponseid'];
}

function checkCollectActivityHasNews($aid)
{
    $uid = session('uid');
    $activityCollectModel = D('ActivityCollect');
    $collect_info = $activityCollectModel->field('hasresponseid')->where(array('uid' => $uid, 'aid' => $aid))->find();

    return $collect_info['hasresponseid'];
}

function checkParticipateActivityHasNews($aid)
{
    $uid = session('uid');
    $activityResponseModel = D('ActivityResponse');
    $participate_info = $activityResponseModel->field('hasresponseid')->where(array('uid' => $uid, 'aid' => $aid))->order(array('id' => 'desc'))->limit(1)->select();

    return $participate_info[0]['hasresponseid'];
}

function checkTopicHasNews($tid)
{
    $topicModel = D('Topic');
    $hasresponseid = $topicModel->where(array('id' => $tid))->getFieldById($tid, 'hasresponseid');

    return $hasresponseid;
}

function checkCollectTopicHasNews($tid)
{
    $uid = session('uid');
    $topicCollectModel = D('TopicCollect');
    $collect_info = $topicCollectModel->field('hasresponseid')->where(array('uid' => $uid, 'tid' => $tid))->find();

    return $collect_info['hasresponseid'];
}

function checkParticipateTopicHasNews($tid)
{
    $uid = session('uid');
    $topicResponseModel = D('TopicResponse');
    $participate_info = $topicResponseModel->field('hasresponseid')->where(array('uid' => $uid, 'tid' => $tid))->order(array('id' => 'desc'))->limit(1)->select();

    return $participate_info[0]['hasresponseid'];
}

//获取系统消息数量
function getSystemMessageCount()
{
    $uid = session('uid');
    $messageModel = D('Message');
    $count = $messageModel->where(array('touid' => $uid, 'isread' => 0, 'status' => 0))->count();
    if ($count >= 10) {
        $count = '9+';
    }

    return $count;
}

//获取活动消息数量
function getActivityMessageCount()
{
    $uid = session('uid');
    $activityResponseUserModel = D('ActivityResponseUser');
    $count = $activityResponseUserModel->where(array('uid' => $uid, 'isread' => 0))->count();
    if ($count >= 10) {
        $count = '9+';
    }

    return $count;
}

//获取话题消息数量
function getTopicMessageCount()
{
    $uid = session('uid');
    $topicResponseUserModel = D('TopicResponseUser');
    $count = $topicResponseUserModel->where(array('uid' => $uid, 'isread' => 0))->count();
    if ($count >= 10) {
        $count = '9+';
    }

    return $count;
}

//将用户信息进行base64编码
function getPrivateMessageURL($uid, $touid)
{
    $param = "uid=$uid&touid=$touid";
    $param = base64_encode($param);
    $param = base64_encode($param);
    $url = 'http://'.$_SERVER['HTTP_HOST'].':55151/index.php?param='.$param;

    return $url;
}

//电影相关函数
//工作室电影活动数量
function getMovieActivityNumByFidMid($fid, $mid)
{
    $activityModel = D('Activity');
    $count = $activityModel->where(array('fid' => $fid, 'movie' => $mid, 'category' => 1, 'audit' => 1))->count();

    return $count;
}

function getMovieOtherForum($title, $fid)
{
    $forumMovieModel = D('ForumMovie');
    $map['title'] = $title;
    $map['fid'] = array('neq', $fid);
    $movie = $forumMovieModel->where($map)->select();

    return $movie;
}

function getTicketPriceByAid($aid)
{
    $activityMovieTicketModel = D('ActivityMovieTicket');
    $ticket = $activityMovieTicketModel->field('price')->where(array('aid' => $aid))->find();

    return $ticket['price'];
}

function getMovieTitle($id)
{
    $forumMovieModel = D('ForumMovie');
    $movie = $forumMovieModel->field('title')->where(array('id' => $id))->find();

    return $movie['title'];
}

function getMovieReleasetime($id)
{
    $forumMovieModel = D('ForumMovie');
    $movie = $forumMovieModel->field('releasetime')->where(array('id' => $id))->find();

    return $movie['releasetime'];
}

function getMovieRuleByFid($fid)
{
    $forumMovieModel = D('ForumMovie');
    $movie = $forumMovieModel->field('rule')->where(array('fid' => $fid))->find();

    return $movie['rule'];
}

function getMovieRuleById($id)
{
    $forumMovieModel = D('ForumMovie');
    $movie = $forumMovieModel->field('rule')->where(array('id' => $id))->find();

    return $movie['rule'];
}

function getMovieDescByFid($fid)
{
    $forumMovieModel = D('ForumMovie');
    $movie = $forumMovieModel->field('desc')->where(array('fid' => $fid))->find();

    return $movie['desc'];
}

function getMoviePoster($mid)
{
    $forumMovieModel = D('ForumMovie');
    $movie = $forumMovieModel->field('poster')->where(array('id' => $mid))->find();

    return $movie['poster'];
}

function getMovieDescById($id)
{
    $forumMovieModel = D('ForumMovie');
    $movie = $forumMovieModel->field('desc')->where(array('id' => $id))->find();

    return $movie['desc'];
}

//日期相关函数
function getday($seconds)
{
    $day = intval($seconds / (3600 * 24));

    return $day < 0 ? 0 : $day;
}

//获取活动已经购买的票数
function getBoughtTicketByAid($aid)
{
    $activityEnrollModel = D('ActivityEnroll');
    $num = $activityEnrollModel->where(array('status' => 0, 'aid' => $aid))->sum('ticketnum');

    return is_null($num) ? 0 : $num;
}

//影加总票房
function getMovieActivityTotalPrice($fid)
{
    $activityModel = D('Activity');
    $activityEncrollModel = D('ActivityEncroll');
    $activityMovieTicketModel = D('ActivityMovieTicket');
    //TODO:电影票房统计
    $box_office = 0;

    $activities = $activityModel->where(array('fid' => $fid, 'category' => 1, 'audit' => 1))->select();

    foreach ($activities as $key => $val) {
        $ticket = $activityMovieTicketModel->where(array('aid' => $val['id']))->find();
        $count = $activityEncrollModel->where(array('aid' => $val['id'], 'status' => 0))->count();
        $total = $count * $ticket['price'];
        $box_office += $total;
    }

    if ($box_office > 10000) {
        $box_office = ($box_office / 10000).'万';
    } else {
        $box_office = $box_office.'元';
    }

    return $box_office;
}

//二维数组去重
function array_unique_fb($array2D)
{
    foreach ($array2D as $v) {
        $v = join(',', $v); //降维,也可以用implode,将一维数组转换为用逗号连接的字符串
        $temp[] = $v;
    }
    $temp = array_unique($temp); //去掉重复的字符串,也就是重复的一维数组
    foreach ($temp as $k => $v) {
        $temp[$k] = explode(',', $v); //再将拆开的数组重新组装
    }

    return $temp;
}
//截取字符串固定长度，超过补充省略号...
function subtxt($txt, $len, $bool = true)
{
    if ($bool == true) {
        return mb_strlen($txt, 'utf8') > $len ? mb_substr($txt, 0, $len, 'utf8').'..' : $txt;
    } else {
        return mb_strlen($txt, 'utf8') > $len ? mb_substr($txt, 0, $len, 'utf8').'.' : $txt;
    }
}

//随机生成字符串
function createRandomStr($length)
{
    $str = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'; //34个字符
    $strlen = 34;
    while ($length > $strlen) {
        $str .= $str;
        $strlen += 34;
    }
    $str = str_shuffle($str);

    return substr($str, 0, $length);
}

//电影院名称
function getCinemaNameById($id)
{
    $moviePlaceModel = D('MoviePlace');
    $title = $moviePlaceModel->where(array('id' => $id))->getField('title');

    return $title;
}

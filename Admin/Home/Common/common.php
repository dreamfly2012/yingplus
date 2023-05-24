<?php

//获取电影活动的主题
function getActivitySubject($aid)
{
    $activityModel = D('Activity');
    $subject = $activityModel->getFieldById($aid, 'subject');

    return $subject;
}

/**
 ** 截取中文字符串.
 **/
function msubstr($str, $start = 0, $length, $charset = 'utf-8', $suffix = true)
{
    if (function_exists('mb_substr')) {
        $slice = mb_substr($str, $start, $length, $charset);
    } elseif (function_exists('iconv_substr')) {
        $slice = iconv_substr($str, $start, $length, $charset);
    } else {
        $re['utf-8'] = '/[x01-x7f]|[xc2-xdf][x80-xbf]|[xe0-xef][x80-xbf]{2}|[xf0-xff][x80-xbf]{3}/';
        $re['gb2312'] = '/[x01-x7f]|[xb0-xf7][xa0-xfe]/';
        $re['gbk'] = '/[x01-x7f]|[x81-xfe][x40-xfe]/';
        $re['big5'] = '/[x01-x7f]|[x81-xfe]([x40-x7e]|xa1-xfe])/';
        preg_match_all($re[$charset], $str, $match);
        $slice = join('', array_slice($match[0], $start, $length));
    }
    $fix = '';
    if (strlen($slice) < strlen($str)) {
        $fix = '...';
    }

    return $suffix ? $slice.$fix : $slice;
}

/**获取视频封面**/
function getVideoCover($id)
{
    $videoModel = D('Video');
    $video = $videoModel->where(array('id' => $id))->find();
    $cover = $video['cover'];
    if (!empty($cover)) {
        return $cover;
    } else {
        $yunvideoModel = D('YunVideo');
        $vid = $video['videoid'];
        $cover = $yunvideoModel->getFieldById($vid, 'videocover');
        //dump($yunvideoModel->getLastSql());
        return $cover;
    }
}

//获取消息内容
function getMessageContent($id)
{
    $messageModel = D('Message');
    $content = $messageModel->getFieldById($id, 'content');

    return $content;
}

//获取评论类型名称
function getResponseTypeName($id)
{
    switch ($id) {
        case '1':
            return '话题';
            break;
        case '2':
            return '活动';
            break;
        case '3':
            return '视频';
            break;
        case '4':
            return '工作室';
            break;
        default:
            return '';
            break;
    }
}

/**
 * @param $id
 *
 * @return mixed
 *               获取用户头像
 */
function getUserPhotoById($id)
{
    $UserProfileModel = D('UserProfile');
    $photo = $UserProfileModel->getPhotoByUid($id);
    if (is_numeric($photo)) {
        $attachmentModel = D('Attachment');
        $info = $attachmentModel->getImgPathById($photo);

        return $info;
    } else {
        return $photo;
    }
}

//获取手机号
function getUserTelephoneById($id)
{
    $UserModel = D('User');
    $telephone = $UserModel->getFieldById($id, 'telephone');

    return $telephone;
}

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

//日期相关函数
function getday($seconds)
{
    $day = intval($seconds / (3600 * 24));

    return $day;
}

//根据活动id获取影院名称
function getCinemaNameByAid($id)
{
    $activityModel = D('Activity');
    $cinemaname = $activityModel->where(array('id' => $id))->getField('cinemaname');

    return $cinemaname;
}

//获取电影名字
function getMovieNameById($id)
{
    $forumMovieModel = D('ForumMovie');
    $title = $forumMovieModel->where(array('id' => $id))->getField('title');

    return $title;
}

//获取影院名称
function getCinemaNameById($id)
{
    $moviePlaceModel = D('MoviePlace');
    $title = $moviePlaceModel->where(array('id' => $id))->getField('title');

    return $title;
}

function getTicketPriceByAid($aid)
{
    $activityMovieTicketModel = D('ActivityMovieTicket');
    $ticket = $activityMovieTicketModel->field('price')->where(array('aid' => $aid))->find();

    return $ticket['price'];
}

function getUserNicknameById($id)
{
    $userModel = D('User');
    $result = $userModel->where(array('id' => $id))->find();

    return $result['nickname'];
}

function getFansGroupById($id)
{
    $forumModel = D('Forum');
    $result = $forumModel->where(array('id' => $id))->find();

    return $result['fansgroup'];
}

function getForumNameById($id)
{
    $forumModel = D('Forum');
    $result = $forumModel->where(array('id' => $id))->find();

    return $result['name'];
}

function getTime()
{
    return time();
}
function fetchPartakeTopicNum($uid)
{
    $TopicResponse = D('TopicResponse');
    $result = $TopicResponse->fetchPartakeTopicNum($uid);

    return count($result);
}

function createTopicNum($uid, $status)
{
    $Topic = D('Topic');

    return $Topic->getTopicNumByUid($uid, $status);
}

function getDigestTopicNumByUid($uid)
{
    $Topic = D('Topic');

    return $Topic->getDigestTopicNumByUid($uid);
}

function getPartakeActivityByUid($uid)
{
    $ActivityResponse = D('ActivityResponse');

    return count($ActivityResponse->getPartakeActivityByUid($uid));
}

function getEncrollActivityByUid($uid)
{
    $ActivityEncroll = D('ActivityEncroll');

    return count($ActivityEncroll->getEncrollActivityByUid($uid));
}

function getCreateActivityNumByUid($uid)
{
    $Activity = D('Activity');
    $wheresql = 'uid ='.$uid;

    return $Activity->getActivityNumByUid($wheresql);
}

function getDelActivityNumByUid($uid)
{
    $Activity = D('Activity');
    $wheresql = 'uid ='.$uid.' and status = 1';

    return $Activity->getActivityNumByUid($wheresql);
}

function getDigestActivityNumByUid($uid)
{
    $Activity = D('Activity');
    $wheresql = 'uid ='.$uid.' and isdigest= 1';

    return $Activity->getActivityNumByUid($wheresql);
}

function applyActivityNumByUid($uid)
{
    $Activity = D('Activity');
    $wheresql = 'uid ='.$uid.' and isrecommend= 1';

    return $Activity->getActivityNumByUid($wheresql);
}

function getBanUserStatus($uid)
{
    $data['uid'] = $uid;
    $model = M('ForumBanUser');
    $result = $model->where($data)->find();
    if ($result['status'] == 0) {
        return '【封禁】';
    } elseif ($result['status'] == 1) {
        return '【已封禁】';
    }
}

//获取地区名称
function getDistrictNameById($id)
{
    $districtModel = D('District');
    $name = $districtModel->where(array('id' => $id))->getField('name');

    return $name;
}

//查询该用户是否为工作室成员
function isForumUser($fid, $uid)
{
    $ForumUser = D('ForumUser');
    $result = $ForumUser->isForumUser($fid, $uid);
    if (!empty($result)) {
        return true;
    } else {
        return false;
    }
}

/**
 * 返回附件的url.
 */
function getAttachmentUrlById($id)
{
    $attachmentModel = D('Attachment');
    $attachment = $attachmentModel->where(array('id' => $id))->find();
    dump($attachment);
    if (empty($attachment['remote_url'])) {
        return $attachment['path'];
    } else {
        return $attachment['remote_url'];
    }
}

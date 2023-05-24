<?php

namespace Home\Controller;

class ActivityManageController extends CommonController
{

    public function getActivityFensi()
    {
        $aid = I('post.aid', null, '');

        $ActivityEnroll = D('ActivityEnroll');

        $result = $ActivityEnroll->getAllFensiByAid($aid);

        $result = $this->getFensiData($result);

        $this->ajaxReturn($result);

    }

    public function getFensiData($result)
    {
        $User        = D('User');
        $UserProfile = D('UserProfile');
        $Attachment  = D('Attachment');
        foreach ($result as $key => $val) {
            $result[$key]['nickname'] = $User->getFieldById($val['uid'], 'nickname');
            $result[$key]['photo']    = $UserProfile->getPhotoByUid($val['uid']);

            if (is_numeric($result[$key]['photo'])) {
                $photo                 = $Attachment->getImgPathById($result[$key]['photo']);
                $result[$key]['photo'] = substr($photo, 1);
            }
        }
        return $result;
    }
    //查找活动详细信息
    public function findDetailActivity()
    {

        $aid      = I('get.id', null, '');
        $Activity = D('Activity');
        $District = D('District');
        $activity = $Activity->getActivityById($aid);
        $activity = $this->getOneActivityData($activity);
        // 为省市准备数据
        $provinces = $District->getAllProvinces(); //得到所有省的数据
        $this->assign('activity', $activity);
        $this->assign('provinces', $provinces);
        $this->display('showDetailActivity');

    }

    //所有活动列表
    public function activityList()
    {
        $Activity  = D('Activity');
        $District  = D('District');
        $Forum     = D('Forum');
        $fid       = I('request.fid', null, ''); //工作室ID
        $province  = I('request.province', null, ''); //活动举办的省份
        $city      = I('request.city', null, ''); //活动举办的城市
        $begintime = I('request.begintime', null, ''); //活动举办时间
        $endtime   = I('request.endtime', null, ''); //活动举办时间
        $p         = I('request.p', 1);

        if (!empty($fid)) {
            $condition['fid'] = $fid;
        }

        if (!empty($province)) {
            $condition['holdprovince'] = $province;
        }
        if (!empty($city)) {
            $condition['holdcity'] = $city;
        }
        if (!empty($begintime) && empty($endtime)) {
            $condition['addtime'] = array('gt', $begintime);
        }

        if (!empty($endtime) && empty($begintime)) {
            $condition['addtime'] = array('lt', $endtime);
        }
        if (!empty($endtime) && !empty($begintime)) {
            $condition['addtime'] = array(array('gt', $begintime), array('gt', $endtime), 'and');
        }

        $number = C('PAGE_LISTROWS');
        
        $count = $Activity->where($condition)->count();
        $Page = new \Think\Page($count, $number);
        parent::setPageConfig($Page);
        $show = $Page->show();

        $activityList = $Activity->where($condition)->order(array('id' => 'asc'))->limit(($p - 1) * $number, $p * $number)->select();
        $activityList = $this->getActivityData($activityList);

        // 为省市准备数据
        $provinces = $District->getAllProvinces(); //得到所有省的数据
        //查找所有星吧
        $forums = $Forum->where(array('status' => 1))->select();
        $this->assign('forums', $forums);
        $this->assign('activityList', $activityList);
        $this->assign('page', $show);
        $this->assign('count', $count);
        $this->assign('provinces', $provinces);
        $this->assign('inputinfo', $arr);
        $this->display('activity');
    }
    //推荐活动列表展示
    public function recommendActivityList()
    {

        //得到所有尚未查看，设为推荐的活动
        $ActivityRecommend = D('ActivityRecommend');
        $count             = $ActivityRecommend->getActivityCount();
        $Page              = new \Think\Page($count, C('PAGE_LISTROWS'));
        parent::setPageConfig($Page);
        $show              = $Page->show();
        $recommendActivity = $ActivityRecommend->getRecommendActivity($Page);
        $recommendActivity = $this->getRecommendActivityDate($recommendActivity);
        $this->assign('recommendActivity', $recommendActivity);
        $this->assign('page', $show);
        $this->display('recommendActivityList');
    }

    //推荐活动审核处理
    public function approveActivity()
    {

        $ActivityRecommend = D('ActivityRecommend');
        $Activity          = D('Activity');
        $touid             = I('get.touid', null, '');
        $id                = I('get.id', null, '');
        $istrue            = I('get.istrue', null, '');
        $aid               = $ActivityRecommend->getFieldById($id, 'aid');
        //得到fid
        $fid = $ActivityRecommend->getFieldById($id, 'fid');
        if ($istrue == 1) {
            parent::commonSend(C('ACTIVITY_RECOMMEND_TRUE'), $touid, '推荐活动审核消息', $fid);
            $arr['isrecommend'] = 1;
            $arr['status']      = 0;
        } else {
            parent::commonSend(C('ACTIVITY_RECOMMEND_FALSE'), $touid, '推荐活动审核消息', $fid);
            $arr['isrecommend'] = 0;
            $arr['status']      = 0;
        }
        $ActivityRecommend->updateRecommend($id, $arr);
        //更新Activity表中活动的推荐
        $activity_arr['isrecommend'] = 1;
        $Activity->updateActivity($aid, $activity_arr);
        $this->recommendActivityList();
    }

    //活动编辑后保存
    public function saveActivity()
    {

        $Forum             = D('Forum');
        $Attachment        = D('Attachment');
        $Activity          = D('Activity', null, '');
        $id                = I('post.aid', null, '');
        $forumname         = I('post.forumname', null, ''); //活动所属工作室
        $fid               = $Forum->getForumIDByName($forumname);
        $uid               = I('post.uid', null, '');
        $status            = I('post.status', 0, ''); //活动状态
        $province          = I('post.province', null, ''); //活动举办的城市
        $city              = I('post.city', null, ''); //活动举办的城市
        $detailaddress     = I('post.detailaddress', null, ''); //活动举办的详细地址
        $enrollstartime    = I('post.enrollstartime', null, ''); //活动开始报名时间
        $enrollendtime     = I('post.enrollendtime', null, ''); //活动报名截止时间
        $holdstart         = I('post.holdstart', null, ''); //活动开始时间
        $holdend           = I('post.holdend', null, ''); //活动结束时间
        $enrollstartime    = strtotime($enrollstartime);
        $enrollendtime     = strtotime($enrollendtime);
        $holdstart         = strtotime($holdstart);
        $holdend           = strtotime($holdend);
        $enrolltotal       = I('post.enrolltotal', null, ''); //报名人数上线
        $subject           = I('post.subject', null, ''); //活动主题
        $notice            = I('post.notice', null, ''); //活动公告
        $content           = I('post.content', null, ''); //活动内容
        $participatemethod = I('post.participatemethod', null, ''); //参与方式
        $hot               = I('post.hot', null, ''); //活动热度
        $isadminrecommend  = I('post.isadminrecommend', null, '');
        $updatetime        = date('Y-m-d H:i:s', time());
        $arr               = array(
            'fid'               => $fid,
            'status'            => $status,
            'holdprovince'      => $province,
            'holdcity'          => $city,
            'enrollstartime'    => $enrollstartime,
            'enrollendtime'     => $enrollendtime,
            'detailaddress'     => $detailaddress,
            'holdstart'         => $holdstart,
            'holdend'           => $holdend,
            'subject'           => $subject,
            'notice'            => $notice,
            'participatemethod' => $participatemethod,
            'content'           => $content,
            'enrolltotal'       => $enrolltotal,
            'updatetime'        => $updatetime,
            'hot'               => $hot,
            'isadminrecommend'  => $isadminrecommend,
        );
        $upload                    = new \Think\Upload();
        $upload->maxSize           = 3145728;
        $upload->exts              = array('jpg', 'gif', 'png');
        $upload->savePath          = 'Activity/';
        $attachment_arr['fid']     = $fid;
        $attachment_arr['aid']     = $id;
        $attachment_arr['uid']     = $uid;
        $attachment_arr['isimage'] = 1;

        $info = $upload->uploadOne($_FILES['file_0']);
        if ($info) {
            $attachment_arr['filename'] = $info['name'];
            $attachment_arr['filesize'] = $info['size'];
            $path                       = '/Uploads/' . $info['savepath'] . $info['savename'];
            $attachment_arr['path']     = $path;
            $imgid                      = $Attachment->saveAttachment($attachment_arr);
            //更新活动海报图片
            $arr['img'] = $imgid;
        }

        $info = $upload->uploadOne($_FILES['file_1']);
        if ($info) {
            $attachment_arr['filename'] = $info['name'];
            $attachment_arr['filesize'] = $info['size'];
            $path                       = '/Uploads/' . $info['savepath'] . $info['savename'];
            $attachment_arr['path']     = $path;
            $imgid                      = $Attachment->saveAttachment($attachment_arr);
            //更新活动海报图片
            $arr['sourceimg'] = $imgid;
        }

        $info = $upload->uploadOne($_FILES['file_2']);
        if ($info) {
            $attachment_arr['filename'] = $info['name'];
            $attachment_arr['filesize'] = $info['size'];
            $path                       = '/Uploads/' . $info['savepath'] . $info['savename'];
            $attachment_arr['path']     = $path;
            $imgid                      = $Attachment->saveAttachment($attachment_arr);
            //更新活动海报图片
            $arr['indexsmallimg'] = $imgid;
        }

        $info = $upload->uploadOne($_FILES['file_3']);
        if ($info) {
            $attachment_arr['filename'] = $info['name'];
            $attachment_arr['filesize'] = $info['size'];
            $path                       = '/Uploads/' . $info['savepath'] . $info['savename'];
            $attachment_arr['path']     = $path;
            $imgid                      = $Attachment->saveAttachment($attachment_arr);
            //更新活动海报图片
            $arr['indexbigimg'] = $imgid;
        }

        $Activity->updateActivity($id, $arr);
        $this->redirect('ActivityManage/showActivity', array('id' => $id));
    }
    //展示某个活动
    public function showActivity()
    {
        $aid      = I('get.id', null, '');
        $Activity = D('Activity');
        $District = D('District');
        $activity = $Activity->getActivityById($aid);
        $activity = $this->getOneActivityData($activity);
        // 为省市准备数据
        $provinces = $District->getAllProvinces(); //得到所有省的数据
        $this->assign('activity', $activity);
        $this->assign('provinces', $provinces);
        $this->display('showActivity');
    }
    //首页推荐活动管理
    public function homeRecommendActivityList()
    {
        $this->display('homeRecommendActivity');
    }
    //根据省ID得到城市列表
    public function getCitiesByPid()
    {
        $District = D('District');
        $pid      = I('post.pid', null, '');
        $pid      = $pid == '' ? null : $pid;
        $cities   = $District->getCitiesByPid($pid);
        $this->ajaxReturn($cities, 'json');
    }
    //封装推荐活动的数据
    public function getRecommendActivityDate($recommendActivity)
    {
        $Activity = D('Activity');
        $Forum    = D('Forum');
        $District = D('District');
        $User     = D('User');
        foreach ($recommendActivity as $key => $value) {
            $recommendActivity[$key]['aid'] = $value['aid'];
            //活动标题
            $recommendActivity[$key]['subject'] = $Activity->getActivitySubject($value['aid']);
            //活动创建者
            $recommendActivity[$key]['creater'] = $User->getUserNickNameByUID($value['uid']);
            //活动所属星吧
            $recommendActivity[$key]['forumname'] = $Forum->getActivityForum($value['fid']);
            //活动类型
            $recommendActivity[$key]['type'] = $Activity->getActivityType($value['aid']);
            $recommendActivity[$key]['type'] = C('ACTIVITY_TYPE_' . $recommendActivity[$key]['type']);
            //活动地点，只显示活动所属城市
            $recommendActivity[$key]['holdprovince'] = $District->getProvince($Activity->getActivityHoldProvince($value['aid']));
            $recommendActivity[$key]['holdcity']     = $District->getCity($Activity->getActivityHoldCity($value['aid']));
            //活动添加时间
            $recommendActivity[$key]['addtime'] = date('Y-m-d H:i:s', $value['addtime']);
        }
        return $recommendActivity;
    }
    //封装数据已为前端页面进行展示
    public function getActivityData($activityList)
    {

        $District = D('District');
        $User     = D('User');
        $Forum    = D('Forum');
        foreach ($activityList as $key => $value) {
            $activityList[$key]['holdprovince'] = $District->getProvince($value['holdprovince']);
            $activityList[$key]['holdcity']     = $District->getCity($value['holdcity']);
            $activityList[$key]['holdname']     = $User->getUserNickNameByUID($value['uid']);
            $activityList[$key]['forumname']    = $Forum->getFieldById($value['fid'], 'name');
            $activityList[$key]['type']         = C('ACTIVITY_TYPE_' . $activityList[$key]['type']);
            $activityList[$key]['addtime']      = date('Y-m-d H:i:s', $activityList[$key]['addtime']);
            $activityList[$key]['status']       = C('ACTIVITY_STATUS_' . $activityList[$key]['status']);
        }
        return $activityList;
    }

    //封装某个活动信息用于前端展示
    public function getOneActivityData($activity)
    {

        $Forum                      = D('Forum');
        $User                       = D('User');
        $Attachment                 = D('Attachment');
        $activity['forumname']      = $Forum->getFieldById($activity['fid'], 'name');
        $activity['nickname']       = $User->getFieldById($activity['uid'], 'nickname');
        $activity['holdstart']      = date('Y-m-d H:i:s', $activity['holdstart']);
        $activity['holdend']        = date('Y-m-d H:i:s', $activity['holdend']);
        $activity['enrollendtime']  = date('Y-m-d H:i:s', $activity['enrollendtime']);
        $activity['enrollstartime'] = date('Y-m-d H:i:s', $activity['enrollstartime']);
        $activity['img']            = $Attachment->getFieldById($activity['img'], 'path');
        $activity['sourceimg']      = $Attachment->getFieldById($activity['sourceimg'], 'path');
        $activity['indexsmallimg']  = $Attachment->getFieldById($activity['indexsmallimg'], 'path');
        $activity['indexbigimg']    = $Attachment->getFieldById($activity['indexbigimg'], 'path');
        return $activity;
    }
    //为活动查询准备sql语句的条件部分
    public function getWhereSqlAboutActivity($arr)
    {
        $User     = D('User');
        $wheresql = ' 1=1 and istrash =0 and audit = 1 ';

        if (!empty($arr['fid'])) {
            $wheresql .= ' and a.fid = ' . $arr['fid'];
        }
        if (!empty($arr['nickname'])) {
            $uid = $User->getUserIDByNickName($arr['nickname']);
            $wheresql .= ' and a.uid = ' . $uid;
        }
        if (!empty($arr['usertype']) && $arr['usertype'] == 1) {
            $wheresql .= ' and a.id = response.aid ';
        }
        if (!empty($arr['usertype']) && $arr['usertype'] == 2) {
            $wheresql .= ' and a.id = enroll.aid and enroll.status = 0';
        }
        if (!empty($arr['status'])) {
            $wheresql .= ' and a.status = ' . $arr['status'];
        } elseif ($arr['status'] == '0') {
            $wheresql .= ' and a.status = ' . $arr['status'];
        }
        if (!empty($arr['type'])) {
            $wheresql .= ' and type = ' . $arr['type'];
        } elseif ($arr['type'] == '0') {
            $wheresql .= ' and type = ' . $arr['type'];
        }
        if (!empty($arr['province'])) {
            $wheresql .= ' and holdprovince = ' . $arr['holdprovince'];
        }
        if (!empty($arr['city'])) {
            $wheresql .= ' and holdcity = ' . $arr['holdcity'];
        }
        if (!empty($arr['begintime'])) {
            $begintime = strtotime($arr['begintime']);
            $wheresql .= ' and addtime >=' . $begintime;
        }
        if (!empty($arr['endtime'])) {
            $endtime = strtotime($arr['endtime']);
            $wheresql .= ' and addtime <=' . $endtime;
        }
        if (!empty($arr['ishot'])) {
            $wheresql .= ' and ishot = 1';
        }
        if (!empty($arr['isrecommend'])) {
            $wheresql .= ' and isrecommend = 1';
        }
        if (!empty($arr['isdigest'])) {
            $wheresql .= ' and isdigest = 1';
        }
        if (!empty($arr['beginnum']) && $arr['beginnum'] >= 0) {
            $wheresql .= ' and (enrollnum - attendnum) >=' . $arr['beginnum'];
        }
        if (!empty($arr['endnum']) && $arr['endnum'] >= 0 && $arr['endnum'] >= $arr['beginnum']) {
            $wheresql .= ' and (enrollnum - attendnum) <=' . $arr['endnum'];
        }
        if (!empty($arr['activitykeys'])) {
            $wheresql .= ' and subject like %' . $arr['activitykeys'] . '%';
        }
        $wheresql .= ' and status != 1';
        return $wheresql;
    }
}

<?php
namespace Home\Controller;

use Think\Controller;

class UserController extends CommonController
{
    public function info()
    {
        $token = I('request.token', null);
        if (empty($token)) {
            $info    = null;
            $code    = -1;
            $message = '用户未登录';
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $uid                    = $this->get_uid_by_token($token);
        $userModel              = M('User');
        $collectModel           = D('Collect');
        $collect_activity       = $collectModel->where(array('uid' => $uid, 'status' => 0, 'type' => 2))->select();
        $collect_activity_count = count($collect_activity);
        $collect_topic          = $collectModel->where(array('uid' => $uid, 'status' => 0, 'type' => 1))->select();
        $collect_topic_count    = count($collect_topic);

        $info                           = $userModel->where(array('id' => $uid))->find();
        $info['collect_activity_count'] = $collect_activity_count;
        $info['collect_topic_count']    = $collect_topic_count;
        $info['avatar'] = getUserPhotoById($uid);
        $code                           = 0;
        $message                        = '用户信息';
        $return                         = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    public function updatephoto()
    {
        $token = I('request.token', null);
        if (empty($token)) {
            $info    = null;
            $code    = -1;
            $message = '用户未登录';
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
        $uid                    = $this->get_uid_by_token($token);
        $img = R('UploadImg/getupload', array($uid));
        $userprofile = D('UserProfile');
        $userprofile->where(array('uid' => $uid))->save(array('photo'=>$img));
        die(json_encode(array('code'=>0,'msg'=>'更新头像成功')));
    }

    /**
     * 关注星吧列表
     * @return [type] [description]
     */
    public function followlist()
    {
        $token = I('request.token', null);
        if (empty($token)) {
            $info    = null;
            $code    = -1;
            $message = '用户未登录';
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $uid                    = $this->get_uid_by_token($token);
        $forumuserModel  = D('ForumUser');
        $forumModel = D('Forum');
        $starlist = $forumuserModel->where(array('uid'=>$uid,'status'=>0))->select();
        foreach ($starlist as $k => $v) {
            $foruminfo = $forumModel->where(array('id' => $v['fid']))->find();
            $userphoto = getAttachmentUrlById($foruminfo['photo']);
            $foruminfo['userphoto'] = $userphoto;
            $starlist[$k]['foruminfo'] = $foruminfo;
        }
        $info = $starlist;
        $code                           = 0;
        $message                        = '关注星吧列表信息';
        $return                         = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }
}

<?php

namespace Home\Controller;

class WelfareEventController extends CommonController
{
    public function index()
    {
        $info    = null;
        $code    = -1;
        $message = C('parameter_invalid');
        $return  = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    //合法性验证,防止多次添加
    public function checkvalid()
    {
        $uid = $this->getUid();
        $fid = I('request.fid', null, 'intval');

        if (empty($uid)) {
            $info    = null;
            $code    = 2;
            $message = C('no_login');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        if (empty($fid)) {
            $info    = null;
            $code    = -1;
            $message = C('parameter_invalid');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $welfareEventModel   = D('WelfareEvent');
        $condition['fid']    = $fid;
        $condition['uid']    = $uid;
        $condition['status'] = 0;
        $count               = $welfareEventModel->where($condition)->count();

        if ($count > 1) {
            $info    = null;
            $code    = 1;
            $message = '你提交的过于频繁';
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

    }

    //申请公益实践
    public function apply(){
        $telephone = I('request.telephone');
        $applyEvent = D('ApplyEvent');
        $data['telephone'] = $telephone;
        $data['addtime'] = time();
        $id = $applyEvent->add($data);
        $info = $id;
        $code = 0;
        $message = '申请公益成功';
        $return = $this->buildReturn($info,$code,$message);
        $this->ajaxReturn($return);
    }

    //添加公益事件
    public function add()
    {
        $fid        = I('request.fid', null, 'intval');
        $uid        = $this->getUid();
        $subject    = I('request.subject', null);
        $content    = I('request.content', null);
        $attachment = I('request.attachment');
        $addtime    = time();
        $begintime  = I('request.begintime', 0, 'intval');
        $endtime    = I('request.endtime', 0, 'intval');

        if (empty($uid)) {
            $info    = null;
            $code    = 2;
            $message = C('no_login');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        if (empty($fid) || empty($subject) || empty($content)) {
            $info    = null;
            $code    = -1;
            $message = C('parameter_invalid');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $this->checkvalid();

        $welfareEventModel  = D('WelfareEvent');
        $data['uid']        = $uid;
        $data['fid']        = $fid;
        $data['subject']    = $subject;
        $data['attachment'] = $attachment;
        $data['content']    = $content;
        $data['addtime']    = $addtime;
        $data['begintime']  = $begintime;
        $data['endtime']    = $endtime;
        $id                 = $welfareEventModel->add($data);
        if ($id) {
            $info    = $id;
            $code    = 0;
            $message = '添加公益事件成功';
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        } else {
            $info    = null;
            $code    = 1;
            $message = '添加公益事件失败';
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
    }

    //获取公益事件
    public function info()
    {
        $id = I('request.id', null, 'intval');
        if (empty($id)) {
            $info    = null;
            $code    = -1;
            $message = C('parameter_invalid');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $welfareEventModel           = D('WelfareEvent');
        $attachmentModel             = D('Attachment');
        $welfare                     = $welfareEventModel->where(array('id' => $id, 'status' => 0))->find();
        $welfare['begintime_format'] = date('y-m-d', $welfare['begintime']);
        $welfare['endtime_format']   = date('y-m-d', $welfare['endtime']);
        if (!empty($welfare)) {
            $attachment     = $welfare['attachment'];
            $attachment_arr = explode(',', $attachment);
            foreach ($attachment_arr as $key => $val) {
                $path                     = $attachmentModel->getFieldById($val, 'path');
                $welfare['img_url'][$key] = $path;
            }
        }
        return $welfare;
    }

    //获取公益事件
    public function getinfo()
    {
        $welfare = $this->info();
        $info    = $welfare;
        $code    = 0;
        $message = "公益事件详情";
        $result  = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($result);
    }

    //公益事件列表
    public function listing($fid,$p,$number,$order,$sort)
    {
       
        if (empty($fid)) {
            $info    = 'parameter_invalid';
            $code    = -1;
            $message = C('parameter_invalid');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $order_arr = array('id', 'subject', 'addtime', 'begintime', 'endtime');
        if (!in_array($order, $order_arr)) {
            $info    = 'parameter_invalid';
            $code    = -1;
            $message = C('parameter_invalid');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
        $sort_arr = array('asc', 'desc');
        if (!in_array($sort, $sort_arr)) {
            $info    = 'parameter_invalid';
            $code    = -1;
            $message = C('parameter_invalid');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $welfareEventModel   = D('WelfareEvent');
        $condition['fid']    = $fid;
        $condition['status'] = 0;
        $count               = $welfareEventModel->where($condition)->count();
        $Page                = new \Think\Page($count, $number);
        $show                = $Page->show(); // 分页显示输出
        $welfares            = $welfareEventModel->where($condition)->order(array($order => $sort))->limit(($p-1)*$number,$number)->select();
        
        foreach($welfares as $key=>$val){
            $img_list = array();
            $attachment_arr = explode(',',$val['attachment']);
            foreach($attachment_arr as $kkey=>$vval){
                if($vval){
                    $img_list[] = getAttachmentUrlById($vval); 
                }
            }
            
            $welfares[$key]['img_url'] = $img_list[0];
        }


        $info['data']        = $welfares;
        $info['page']        = $show;
        $info['count']       = $count;
        return $info;

    }

    public function getlisting()
    {
        $fid    = I('request.fid', null, 'intval');
        $p      = I('request.p', 1, 'intval');
        $number = I('request.number', 10, 'intval');
        ($number > 50) ? $number = 50 : '';
        $order  = I('request.order', 'id');
        $sort   = I('request.sort', 'asc');

        $info    = $this->listing($fid,$p,$number,$order,$sort);
        $code    = 0;
        $message = '公益事件列表';
        $return  = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    //更新公益事件update
    public function update()
    {
        $id        = I('request.id', null, 'intval');
        $subject   = I('request.subject', null);
        $content   = I('request.content', null);
        $begintime = I('request.begintime', 0, 'intval');
        $endtime   = I('request.endtime', 0, 'intval');

        if (empty($id)) {
            $info    = null;
            $code    = -1;
            $message = C('parameter_invalid');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
        $welfareEventModel                          = D('WelfareEvent');
        empty($subject) ? '' : $data['subject']     = $subject;
        empty($content) ? '' : $data['content']     = $content;
        empty($begintime) ? '' : $data['begintime'] = $begintime;
        empty($endtime) ? '' : $data['endtime']     = $endtime;
        $bool                                       = $welfareEventModel->where(array('id' => $id))->save($data);

        $info    = null;
        $code    = 0;
        $message = '更新公益事件成功';
        $return  = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);

    }

    public function delete()
    {
        $uid               = $this->getUid();
        $id                = I('request.id', null, 'intval');
        $welfareEventModel = D('WelfareEvent');

        if (empty($uid)) {
            $info    = null;
            $code    = 2;
            $message = C('no_login');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        if (empty($id)) {
            $info    = null;
            $code    = -1;
            $message = C('parameter_invalid');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        if ($this->IsSuperAdmin()) {
            $info    = null;
            $code    = 0;
            $message = '删除公益事件成功';
            $welfareEventModel->where(array('id' => $id))->setField(array('status' => 1));
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $welfare_uid = $welfareEventModel->getFieldById($id, 'uid');
        if ($uid == $welfare_uid) {
            $welfareEventModel->where(array('id' => $id))->setField(array('status' => 1));
            $code    = 0;
            $message = '删除公益事件成功';
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $code    = 1;
        $message = '没有权限';
        $return  = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);

    }

}

<?php

namespace Home\Controller;

class MessageController extends CommonController
{
    public function index()
    {
        die;
    }

    //消息展示
    public function listing()
    {
        if (!$this->checkLogin()) {
            $info    = null;
            $code    = 2;
            $message = C('no_login');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        } else {
            $messageModel = D('Message');
            $p            = I('request.p', 1, 'intval');
            $number       = I('request.number', 10, 'intval');
            $uid          = $this->getUid();
            $count        = $messageModel->where(array('touid' => $uid, 'isread' => 0, 'status' => 0))->count(); // 查询满足要求的总记录数
            $list         = $messageModel->where(array('touid' => $uid, 'isread' => 0, 'status' => 0))->order(array('id' => 'desc'))->limit(($p - 1) * $number, $number)->select();
            $Page         = new \Think\Page($count, $number); // 实例化分页类 传入总记录数和每页显示的记录数
            $Page->setConfig('prev', '上一页');
            $Page->setConfig('next', '下一页');
            $Page->setConfig('theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
            $show         = $Page->show(); // 分页显示输出
            $info['page'] = $show;
            $info['data'] = $list;
            return $info;
        }
    }

    //消息列表
    public function getlisting()
    {
        $info    = $this->listing();
        $code    = 0;
        $message = '消息列表信息';
        $return  = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    //具体一条消息
    public function info(){
        $id = I('request.id',null,'intval');
        $uid = $this->getUid();
        if(empty($uid)){
            $info = null;
            $code = 2;
            $message = C('no_login');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        if(empty($id)){
            $info = null;
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return); 
        }

        $messageModel = D('Message');
        $condition['id'] = $id;
        $condition['touid'] = $uid;
        $message = $messageModel->where($condition)->find();

        if(!$message){
            $info = null;
            $code = 1;
            $message = '消息不存在';
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
        return $message;
        
    }

    public function getinfo(){
        $info = $this->info();
        $code = 0;
        $message = '消息详情';
        $return = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    //删除消息
    public function delete()
    {
        $id  = I('request.id', null);
        $uid = $this->getUid();
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

        $condition['touid']  = $uid;
        $condition['id']     = $uid;
        $condition['status'] = 0;
        $messageModel        = D('Message');

        $exist = $messageModel->where($condition)->find();
        if (!$exist) {
            $info    = null;
            $code    = -1;
            $message = '消息不存在';
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $result = $messageModel->where($condition)->setField(array('status' => 1));

        $info    = $result;
        $code    = 0;
        $message = '删除成功';
        $return  = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    //更新消息已读
    public function update()
    {
        $id  = I('request.id', null);
        $isread = I('request.isread',null);
        $uid = $this->getUid();
        if (empty($uid)) {
            $info    = null;
            $code    = 2;
            $message = C('no_login');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        if (empty($id)||empty($isread)) {
            $info    = null;
            $code    = -1;
            $message = C('parameter_invalid');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $isread_arr = array('read','unread');

        if(!in_array($isread,$isread_arr)){
            $info = null;
            $code = -1;
            $message = C('parameter_invalid');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $messageModel = D('Message');
        $condition['touid'] = $uid;
        $condition['id'] = $id;

        ($isread=='read') ? $isread = 1 : $isread = 0; 
        $messageModel->where($condition)->setField(array('isread'=>$isread));
        $info = null;
        $code = 0;
        $message = '修改消息成功';
        $return = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    //添加消息
    public function add(){
        $fid = I('request.fid',null,'intval');
        $uid = $this->getUid();
        $touid = I('request.touid',null,'intval');
        $subject = I('request.subject',null);
        $content = I('request.content',null);

        if(empty($uid)){
            $info = null;
            $code = 2;
            $message = C('no_login');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        if(empty($fid)||empty($touid)||empty($subject)||empty($content)){
            $info = null;
            $code = -1;
            $message  =C('parameter_invalid');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $messageModel = D('Message');
        $data['fid'] = $fid;
        $data['uid'] = $uid;
        $data['touid'] = $touid;
        $data['subject'] = $subject;
        $data['content'] = $content;
        $data['addtime'] = time();

        $id = $messageModel->add($data);

        if(!$id){
            $info = null;
            $code = 1;
            $message = '添加消息失败';
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $info = $id;
        $code = 0;
        $message = '发送消息成功';
        $return = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

}

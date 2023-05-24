<?php

namespace Home\Controller;

class AttachmentController extends CommonController{
    public function index(){
        $info =null;
        $code = -1;
        $message = C('parameter_invalid');
        $return = $this->buildReturn($info,$code,$message);
        $this->ajaxReturn($return);
    }

    public function get(){
        $id = I('request.id',null,'intval');
        if(empty($id)){
            $info =null;
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info,$code,$message);
            $this->ajaxReturn($return);
        }

        $attachmentModel = D('Attachment');
        $info = $attachmentModel->where(array('id'=>$id))->find();
        $info['path_format'] = buildImgUrl($info['path']);
        
        $code = 0;
        $return = $this->buildReturn($info,$code,$message);
        $this->ajaxReturn($return);
    }

    //上传到云服务器
    public function upload(){
        $id = I('request.id',null,'intval');
        if(empty($id)){
            $info =null;
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info,$code,$message);
            $this->ajaxReturn($return);
        }

        $attachmentModel = D('Attachment');
        $path = $attachmentModel->getFieldById($id,'path');

        if(empty($path)){
            $info =null;
            $code = 1;
            $message = '错误的文件id';
            $return = $this->buildReturn($info,$code,$message);
            $this->ajaxReturn($return); 
        }

        $path = base64_encode($path);
       
        $info = R('Qcloud/upload',array($path));

        if($info['data']['code']==0){
            $attachmentModel->where(array('id'=>$id))->setField(array('remote'=>1,'remote_url'=>$info['data']['info']['access_url']));
            $code = 0;
            $message = '上传成功';
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $code = 1;
        $message = $info['data']['message'];
        $return = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
        
    }
}
<?php

namespace Home\Controller;

class UploadController extends CommonController{
	//反馈上传接口
    public function upload()
    {
        
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
}
<?php

namespace Home\Controller;

class UploadImgController extends CommonController
{
    /**
     * @param file 表单上传 MIME编码
     * 文件格式jpg,gif,png,bmp
     **/
    public function upload()
    {   
        $uid = $this->getUid();
        $upload = new \Think\Upload();
        $upload->maxSize = 10 * 1024 * 1024;
        $upload->exts = array('jpg','jpeg', 'gif', 'png', 'bmp');
        $upload->savePath = '';
        $width = I('request.width',null,'intval');
        $height = I('request.height',null,'intval');
        $info = $upload->upload();
        if (empty($uid)) {
            $info = null;
            $message = C('no_login');
            $code = 2;
            $return = $this->buildReturn($info, $code, $message);
            $return = json_encode($return);
            $this->ajaxReturn($return,'EVAL');
        }

        if (!$info) {
            $info = null;
            $code = 1;
            $message = $upload->getError();
            $return = $this->buildReturn($info, $code, $message);
            $return = json_encode($return);
            $this->ajaxReturn($return,'EVAL');
        }

        //找到文件的路径
        $img_add = './uploads/' . $info['file']['savepath'] . $info['file']['savename'];
        $image = new \Think\Image();
        $image->open($img_add);

        if (!empty($width) && ($image->width() < $width)) {
            $info = $image->width();
            $code = 1;
            $message = '图片宽度小于' . $width;
            $return = $this->buildReturn($info, $code, $message);
            $return = json_encode($return);
            $this->ajaxReturn($return,'EVAL');
        } else if (!empty($height) && ($image->height() < $height)) {
            $info = $image->height();
            $code = 1;
            $message = '图片高度小于' . $height;
            $return = $this->buildReturn($info, $code, $message);
            $return = json_encode($return);
            $this->ajaxReturn($return,'EVAL');
        } else {
            //生成缩略图
            $width = I('request.width',null,'intval');
            $height = I('request.height',null,'intval');
            $save_uniqid = uniqid();
            $path = '/uploads/' . $save_uniqid . '.png';
            $img_add = '.'.$path;
            $imgInfo = '';

            if(!empty($width)&&!empty($height)){
                $imgInfo = $image->thumb($width, $height)->save($img_add);
            }else{
                $imgInfo = $image->save($img_add);
            }

            
            $img_width = $imgInfo->width();
            $img_height = $imgInfo->height();

            $attachmentModel = D('Attachment');
            $data_thumb['filename'] = $info['file']['name'];
            $data_thumb['path'] = $img_add;
            $data_thumb['width'] = $img_width;
            $data_thumb['height'] = $img_height;
            $data_thumb['isimage'] = 1;
            $data_thumb['uid'] = $uid;
            $id = $attachmentModel->add($data_thumb);
            $upload_info = R('Qcloud/upload',array(base64_encode($path)));
            //dump($upload_info);
            $attachmentModel->where(array('id'=>$id))->setField(array('remote'=>1,'remote_url'=>$upload_info['data']['info']['access_url'])); 
            $info = $id;
            $code = 0;
            $message = '上传图片成功';
            $return = $this->buildReturn($info, $code, $message);
            $return = json_encode($return);
            $this->ajaxReturn($return,'EVAL');
        }
    }


    //截取征集海报
    public function crop() {
        $uid = $this->getUid();
        $attachmentid = I('request.attachmentid',null);
        $aid = I('request.aid',null);
        if(empty($uid)){
            $info = 'no_login';
            $code = 2;
            $message = C('no_login');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        if(empty($attachmentid)){
            $info = 'parameter_invalid';
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $attachmentModel = D('Attachment');
        $attachment = $attachmentModel->where(array('id'=>$attachmentid))->find();
        $remote_url = $attachment['remote_url'];

        $image = new \Think\Image();
        $local = auto_save_image($remote_url);
        $image->open($local);
        $thumb = $image->thumb(400,400)->save($local);
        
        $width = $thumb->width();
        $height = $thumb->height();

        $this->assign('thumb_add_img', '/'.$local);
        $this->assign('img_width', $width);
        $this->assign('img_height', $height);
        $this->assign('primary_img', $remote_url);
        $this->assign('aid', $aid);
        $content = $this->fetch('pc/crop_img');
        $info = $content;
        $code = 0;
        $message = '裁剪图片';
        $return = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    //截取图片
    //上传征集海报
    public function cropdo() {
        $uid = $this->getUid();
        $attachmentid = I('request.attachmentid',null);

        if(empty($uid)){
            $info = 'no_login';
            $code = 2;
            $message = C('no_login');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $attachmentModel = D('Attachment');
        $attachment = $attachmentModel->where(array('id'=>$attachmentid))->find();
        $aid = I('request.aid', null, 'intval');
        $desc = I('request.desc');
        $x = I('request.x', 0);
        $y = I('request.y', 0);
        $w = I('request.w', 270);
        $h = I('request.h', 270);

        $primary = I('request.primary', null, '');
        $thumb = I('request.thumb', null, '');
        $thumb = substr($thumb,1);
        
        $thumb_image = new \Think\Image();
        $thumb_image->open($thumb);
        $thumb_width = $thumb_image->width();
        $thumb_height = $thumb_image->height();

        $primary_image = new \Think\Image();
        $primary = auto_save_image($primary);
        $primary_image->open($primary);
        $primary_width = $primary_image->width();
        $primary_height = $primary_image->height();

        $save_uniqid = uniqid();
        $path = '/uploads/' . $save_uniqid . '.png';
        $img_add = '.'.$path;
        
        $imgInfo = $primary_image->crop($w * $primary_width / $thumb_width, $h * $primary_height / $thumb_height, $x*$primary_width / $thumb_width, $y*$primary_height / $thumb_height)->save($img_add);
        
       

        $attachmentModel = D('Attachment');
        $data_thumb['filename'] = $primary;
        $data_thumb['path'] = $path;
        $data_thumb['width'] = $imgInfo->width();
        $data_thumb['height'] = $imgInfo->height();
        $data_thumb['isimage'] = 1;
        $data_thumb['uid'] = $uid;
        $id = $attachmentModel->add($data_thumb);
        $upload_info = R('Qcloud/upload',array(base64_encode($path)));
        //dump($upload_info);
        $attachmentModel->where(array('id'=>$id))->setField(array('remote'=>1,'remote_url'=>$upload_info['data']['info']['access_url'])); 
        $info = $id;
        $code = 0;
        $message = '裁剪图片成功';
        $return = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    public function base64_to_jpeg($base64_string, $output_file)
    {
        $ifp = fopen($output_file, "wb");

        $data = explode(',', $base64_string);

        fwrite($ifp, base64_decode($data[1]));
        fclose($ifp);

        return $output_file;
    }
}

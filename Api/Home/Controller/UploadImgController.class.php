<?php

namespace Api\Controller;

class UploadImgController extends CommonController
{
    /**
     * @param file 表单上传 MIME编码
     * 文件格式jpg,gif,png,bmp
     **/
    public function upload()
    {
        $upload           = new \Think\Upload();
        $upload->maxSize  = 10 * 1024 * 1024;
        $upload->exts     = array('jpg', 'gif', 'png', 'bmp', 'jpeg');
        $upload->savePath = './uploads/';
        $width            = 2000;
        $height           = 2000;
        $info             = $upload->upload();
        $uid              = 1;
        
        if (!$info) {
            $info    = null;
            $code    = 1;
            $message = $upload->getError();
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        //找到文件的路径
        $img_add = './Uploads/' . $info['file']['savepath'] . $info['file']['savename'];
        
        $image   = new \Think\Image();
       
       
        $image->open($img_add);
        if ($image->width() > $width) {
            $info    = $image->width();
            $code    = 3;
            $message = '图片宽度大于' . $width;
            $return  = $this->buildReturn($info, $code, $message);
            $return  = json_encode($return);
            $this->ajaxReturn($return, 'EVAL');
        } elseif ($image->height() > $height) {
            $info    = $image->height();
            $code    = 3;
            $message = '图片高度大于' . $height;
            $return  = $this->buildReturn($info, $code, $message);
            $return  = json_encode($return);
            $this->ajaxReturn($return, 'EVAL');
        } else {
            //生成缩略图
            $save_uniqid = uniqid();
            $path        = '/Uploads/uploads/' . $save_uniqid . '.png';
            $img_add     = '.' . $path;
            //$imgInfo = $image->thumb(100, 100)->save($img_add);
            $imgInfo    = $image->save($img_add);
            $img_width  = $imgInfo->width();
            $img_height = $imgInfo->height();

            $attachmentModel        = D('Attachment');
            $data_thumb['filename'] = $info['file']['name'];
            $data_thumb['path']     = $img_add;
            $data_thumb['width']    = $img_width;
            $data_thumb['height']   = $img_height;
            $data_thumb['isimage']  = 1;
            $data_thumb['uid']      = $uid;
            $id                     = $attachmentModel->add($data_thumb);

            $upload_info            = R('Qcloud/upload', array(base64_encode($path)));
            die(json_encode(array('status'=>0, 'msg' => $upload_info['message'], 'data' => $upload_info['data'])));
            //$attachmentModel->where(array('id' => $id))->setField(array('remote' => 1, 'remote_url' => $upload_info['data']['info']['access_url']));
        }
    }


    public function getupload($uid)
    {
        $upload           = new \Think\Upload();
        $upload->maxSize  = 10 * 1024 * 1024;
        $upload->exts     = array('jpg', 'gif', 'png', 'bmp', 'jpeg');
        $upload->savePath = './uploads/';
        $width            = 2000;
        $height           = 2000;
        $info             = $upload->upload();
        $uid              = $uid;
        
        if (!$info) {
            $info    = null;
            $code    = 1;
            $message = $upload->getError();
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        //找到文件的路径
        $img_add = './Uploads/' . $info['file']['savepath'] . $info['file']['savename'];
        
        $image   = new \Think\Image();
       
       
        $image->open($img_add);
        if ($image->width() > $width) {
            $info    = $image->width();
            $code    = 3;
            $message = '图片宽度大于' . $width;
            $return  = $this->buildReturn($info, $code, $message);
            $return  = json_encode($return);
            $this->ajaxReturn($return, 'EVAL');
        } elseif ($image->height() > $height) {
            $info    = $image->height();
            $code    = 3;
            $message = '图片高度大于' . $height;
            $return  = $this->buildReturn($info, $code, $message);
            $return  = json_encode($return);
            $this->ajaxReturn($return, 'EVAL');
        } else {
            //生成缩略图
            $save_uniqid = uniqid();
            $path        = '/Uploads/uploads/' . $save_uniqid . '.png';
            $img_add     = '.' . $path;
            //$imgInfo = $image->thumb(100, 100)->save($img_add);
            $imgInfo    = $image->save($img_add);
            $img_width  = $imgInfo->width();
            $img_height = $imgInfo->height();

            $attachmentModel        = D('Attachment');
            $data_thumb['filename'] = $info['file']['name'];
            $data_thumb['path']     = $img_add;
            $data_thumb['width']    = $img_width;
            $data_thumb['height']   = $img_height;
            $data_thumb['isimage']  = 1;
            $data_thumb['uid']      = $uid;
            $id                     = $attachmentModel->add($data_thumb);

            $upload_info            = R('Qcloud/upload', array(base64_encode($path)));
            return $upload_info['data'];
            //$attachmentModel->where(array('id' => $id))->setField(array('remote' => 1, 'remote_url' => $upload_info['data']['info']['access_url']));
        }
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

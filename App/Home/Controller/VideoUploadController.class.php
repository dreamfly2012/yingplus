<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/2/14
 * Time: 17:26
 */

namespace Home\Controller;

class VideoUploadController extends CommonController
{
    //上传视频
    public function index($fid)
    {
        $data['timestamp'] = time();
        $data['token']     = md5('unique_salt' . time());
        $data['swf']       = '/Public/plupload/Moxie.swf';
        $data['xap']       = '/Public/plupload/Moxie.xap';
        $data['uploader']  = U('VideoUpload/upload');
        $data['call_back_button'] = I('request.call_back_button',null);
        $data['aid'] = I('request.aid',null);

        $this->assign('upload_form_data', $data);
        $this->assign('rand', rand(11111, 99999));
        $this->assign('fid',$fid);
        
        $template = $this->fetch('pc/upload_video');
        $info = $template;
        $code = 0;
        $message = '视频上传表单';
        $return = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }


    //上传视频
    public function moviefeedbackindex($data)
    {
        $data['timestamp'] = time();
        $data['token']     = md5('unique_salt' . time());
        $data['swf']       = '/Public/plupload/Moxie.swf';
        $data['xap']       = '/Public/plupload/Moxie.xap';
        $data['uploader']  = U('VideoUpload/upload');
        $data['call_back_button'] = I('request.call_back_button',null);
        $data['aid'] = $data['aid'];

        $this->assign('upload_form_data', $data);
        $this->assign('rand', rand(11111, 99999));
        $this->assign('fid',$data['fid']);
        $this->assign('aid',$data['aid']);
        
        $this->display('mobile/upload_feedback_video');
    }


    //上传视频
    public function movieindex($data)
    {
        $data['timestamp'] = time();
        $data['token']     = md5('unique_salt' . time());
        $data['swf']       = '/Public/plupload/Moxie.swf';
        $data['xap']       = '/Public/plupload/Moxie.xap';
        $data['uploader']  = U('VideoUpload/upload');
        $data['call_back_button'] = I('request.call_back_button',null);
        
        $this->assign('upload_form_data', $data);
        $this->assign('rand', rand(11111, 99999));
        $this->assign('fid',$data['fid']);
        
        $this->display('mobile/upload_video');
    }


    //视频上传处理
    public function upload()
    {
        vendor('QcloudVideo.Auth');
        vendor('QcloudVideo.Video');
        $upload           = new \Think\Upload(); // 实例化上传类
        $upload->maxSize  = 100 * 1024 * 1024; // 设置附件上传大小
        $upload->exts     = array('mp4', 'mp3', 'flv', '3gp', 'rmvb', 'avi'); // 设置附件上传类型
        $upload->savePath = './video/'; // 设置附件上传目录
        $uid              = $this->getUid();
        if (empty($uid)) {
            $info    = null;
            $code    = 2;
            $message = C('no_login');
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        // Validate the file type
        $info = $upload->upload();
        if (!$info) {
            $info    = null;
            $code    = -1;
            $message = $upload->getError();
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        //将数据保存到数据库
        $yunVideoModel    = D('YunVideo');
        $data['addtime']  = time();
        $filepath         = trim($info['file']['savepath'], '.');

        $data['localurl'] = './uploads/' . $filepath . $info['file']['savename'];

        if ($id = $yunVideoModel->add($data)) {
            $attachmentModel        = D('Attachment');
            $data_thumb['filename'] = $info['file']['savename'];
            $data_thumb['path']     = '';
            $data_thumb['width']    = 100;
            $data_thumb['height']   = 100;
            $data_thumb['isvideo']  = 1;
            $data_thumb['videoid']  = $id;
            $data_thumb['uid']      = $uid;
            $attachmentid           = $attachmentModel->add($data_thumb);
            $info                   = $attachmentid;
            $code                   = 0;
            $message = '上传视频成功';
            $return                 = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
           

        } else {
            $info    = null;
            $code    = 1;
            $message = '上传视频失败';
            $return  = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

    }


    //转换格式
    public function changeGeShiToMp4($file)
    {
        $time  = time();
        $exstr = "ffmpeg -i " . $file . " -y -ab 56 -ar 22050 -r 15 -sameq ../../uploads/" . $time . ".flv";
        exec($exstr);
        return $time . ".flv";
    }

    //上传到云服务器
    public function uploadToYun()
    {
        vendor('QcloudVideo.Auth');
        vendor('QcloudVideo.Video');
        $attachmentModel        = D('Attachment');
        $attachmentid  = I('request.attachmentid', null, 'intval');
        $videoid = $attachmentModel->getFieldById($attachmentid,'videoid');
        if(empty($videoid)){
        	$info = null;
        	$code = -1;
        	$message = C('parameter_invalid');
        	$return = $this->buildReturn($info,$code,$message);
        	$this->ajaxReturn($return);
        }

    	$yunVideoModel = D('YunVideo');
        
        //查询视频路径，将视频上传到云上
        $local_url  = $yunVideoModel->getFieldById($videoid, 'localurl');
        $pos        = strripos($local_url, '.');
        $houzhui    = substr($local_url, $pos);
        $bucketName = "yingplus";
        $video_name = uniqid() . $houzhui;
        $dstPath    = "/" . $video_name;
        \Video::setTimeout(6000);

        $result = \Video::upload_slice($local_url, $bucketName, $dstPath);
        //var_dump($result);
        $video_url = $result['data']['access_url'];
        if ($result['message'] == '成功'||$result['message'] == 'SUCCESS') {
            //视频上传完成后，查询该视频，以便得到具体信息
            $count = 0;
            $data  = $this->getVideoInfo($video_name);

            while (empty($data['data']['video_cover']) && $count < 20) {
                $count++;
                $data = $this->getVideoInfo($video_name);
            }
            if ($data) {
                if (empty($data['data']['video_cover'])) {
                    $video_cover = "/Public/default/img/video-default-img.png";
                } else {
                    $video_cover = $data['data']['video_cover'];
                }
                $video_size = $data['data']['filesize'];
                $arr['videocover'] = $video_cover;
                $arr['size'] = $video_size;
                $arr['url'] = $video_url;
                $arr['status'] = 1;
                $arr['name'] = $video_name;
                $meta['img_url'] = $video_cover;
                $meta['video_url'] = $video_url;
				$yunVideoModel->where(array('id' => $videoid))->save($arr);
				
				$info = $videoid;
	            $code = 0;
	            $message = '转码成功';
	            $return = $this->buildReturn($info, $code, $message);
	            $this->ajaxReturn($return);
			}

			$info = null;
            $code = 1;
            $message = '转码失败';
            $return = $this->buildReturn($info,$code,$message);
            $this->ajaxReturn($return);
		}

    }

    public function getVideoInfo($videoName)
    {
        if (empty($videoName)) {
            return fasle;
        }
        $appid      = "10014575";
        $bucket     = "yingplus";
        $secret_id  = "AKIDYIgSmJ8ENEL2JUgZNkbq7FhWzNPnudPf";
        $secret_key = "p3n9Amw0SegUnkyXWXiFGiYpKe6ihkxV";
        $expired    = time() + 60 * 60 * 24 * 90;
        $current    = time();
        $rdm        = rand();
        $srcStr     = 'a=' . $appid . '&b=' . $bucket . '&k=' . $secret_id . '&e=' . $expired . '&t=' . $current . '&r=' . $rdm . '&f=';
        $signStr    = base64_encode(hash_hmac('SHA1', $srcStr, $secret_key, true) . $srcStr);
        $url        = "http://web.video.myqcloud.com/files/v1/10014575/yingplus/" . $videoName . "?op=stat&sign=" . $signStr;
        $data       = $this->getCurl($url);
        if (empty($data)) {
            return false;
        }
        return $data;
    }

    public function getCurl($url)
    {
        //初始化
        $curl = curl_init();
        //设置抓取的url
        curl_setopt($curl, CURLOPT_URL, $url);
        //设置头文件的信息作为数据流输出
        curl_setopt($curl, CURLOPT_HEADER, 0);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //执行命令
        $data = curl_exec($curl);
        //关闭URL请求
        curl_close($curl);
        $data = json_decode($data, true);
        return $data;
    }
}

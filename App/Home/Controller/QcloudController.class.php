<?php

namespace  Home\Controller;

class QcloudController extends CommonController{
	public function __construct(){
		parent::__construct();
		vendor('Qcloud_cos.Cosapi');
	}
	public function index(){
		die;
	}

	public function upload($path){
		$path = base64_decode($path);
		$path_arr = explode('/',$path);
		$length = count($path_arr);
		$filename = $path_arr[$length-1];
		if(empty($filename)){
			$info = null;
			$code = -1;
			$message = C('parameter_invalid');
			$return = $this->ajaxReturn($info,$code,$message);
			$this->ajaxReturn($info,$code,$message);
		}
		$bucketName = C('qcloud.bucket');

		$srcPath = '.'.$path;
		$dstPath = "/" . uniqid() . '.png';

		\Cosapi::setTimeout(10);
		$uploadRet = \Cosapi::upload($srcPath, $bucketName, $dstPath);
		
		$info = $uploadRet['data'];
		$code = $uploadRet['code'];
		$message = $uploadRet['message'];
		$return = $this->buildReturn($info,$code,$message);
		return $return;
	}

	public function listfolder(){
		$bucketName = C('qcloud.bucket');

       	\Cosapi::setTimeout(100);

        $listRet = \Cosapi::listFolder($bucketName, "/");

        return $listRet;
	}

	public function get($path){
		//$path = I('request.path',null,'base64_decode');
		$path = base64_decode($path);
		$dstPath = $path;
		if(empty($dstPath)){
			$info = null;
			$code = -1;
			$message = C('parameter_invalid');
			$return = $this->buildReturn($info,$code,$message);
			$this->ajaxReturn($return);
		}

		$bucketName = C('qcloud.bucket');

        \Cosapi::setTimeout(10);

        $statRet = \Cosapi::stat($bucketName, $dstPath);
		$info = $statRet['data'];
        $message = $statRet['message'];
        $code = $statRet['code'];
        $return = $this->buildReturn($info,$code,$message);
		return $return;
	}

}
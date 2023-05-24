<?php
/**
 * 上传附件和上传视频
 * User: gmg137
 * Date: 14-09-05
 * Time: 上午11:21
 */
include "Qcloud_cos/Cosapi.php";
include "Uploader.class.php";

/* 上传配置 */
$base64 = "upload";
switch (htmlspecialchars($_GET['action'])) {
    case 'uploadimage':
        $config = array(
            "pathFormat" => $CONFIG['imagePathFormat'],
            "maxSize" => $CONFIG['imageMaxSize'],
            "allowFiles" => $CONFIG['imageAllowFiles']
        );
        $fieldName = $CONFIG['imageFieldName'];
        break;
    case 'uploadscrawl':
        $config = array(
            "pathFormat" => $CONFIG['scrawlPathFormat'],
            "maxSize" => $CONFIG['scrawlMaxSize'],
            "allowFiles" => $CONFIG['scrawlAllowFiles'],
            "oriName" => "scrawl.png"
        );
        $fieldName = $CONFIG['scrawlFieldName'];
        $base64 = "base64";
        break;
    case 'uploadvideo':
        $config = array(
            "pathFormat" => $CONFIG['videoPathFormat'],
            "maxSize" => $CONFIG['videoMaxSize'],
            "allowFiles" => $CONFIG['videoAllowFiles']
        );
        $fieldName = $CONFIG['videoFieldName'];
        break;
    case 'uploadfile':
    default:
        $config = array(
            "pathFormat" => $CONFIG['filePathFormat'],
            "maxSize" => $CONFIG['fileMaxSize'],
            "allowFiles" => $CONFIG['fileAllowFiles']
        );
        $fieldName = $CONFIG['fileFieldName'];
        break;
}


$up = new Uploader($fieldName, $config, $base64);

$fileinfo = $up->getFileInfo();

$srcPath = $up->getFilePath();;

$filename = uniqid().'.png';

/* 生成上传实例对象并完成上传 */
$cosup =Cosapi::upload($srcPath, 'yingjia', '/'.$filename);

$arr = array(
    'state'=>'SUCCESS',
    'url'=>$cosup['data']['access_url'],
    'title'=>substr($cosup['data']['resource_path'],1),
    "original" => $fileinfo['original'],       //原始文件名
    "type" => $fileinfo['type'],            //文件类型
    "size" => $fileinfo['size'],           //文件大小
);

/**
 * 得到上传文件所对应的各个参数,数组结构
 * array(
 *     "state" => "",          //上传状态，上传成功时必须返回"SUCCESS"
 *     "url" => "",            //返回的地址
 *     "title" => "",          //新文件名
 *     "original" => "",       //原始文件名
 *     "type" => ""            //文件类型
 *     "size" => "",           //文件大小
 * )
 */

/* 返回数据 */
return json_encode($arr);

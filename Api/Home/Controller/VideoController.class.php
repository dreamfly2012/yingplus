<?php
namespace Home\Controller;
use Think\Controller;
class VideoController extends CommonController {
    public function index(){
    	$param = I('request.param','hot');//推荐视频
    	switch($param){
    		case 'hot':
                $videoModel = D('Video');
                $yunvideoModel = D('YunVideo');
                $condition['status'] = 0;
                //$condition['_logic'] = 'and';
                $page = I('request.page',1);
                $pagenum = 5;
                $videos     = $videoModel->where($condition)->order(array('order'=>'desc'))->limit(($page-1)*$pagenum,$pagenum)->select();
                
                foreach ($videos as $key => $val) {
                    $vid = $val['videoid'];
                    if(!empty($vid)){
                        $yunVideoModel = D('YunVideo');
                        $yunInfo = $yunVideoModel->where(array('id' => $vid))->find();
                        //$video_cover = "/Public/default/img/video-default-img.png";

                        if ($yunInfo['videocover'] == '/Public/default/img/video-default-img.png') {
                            $data = R('VideoUpload/getVideoInfo', array($yunInfo['name']));
                            if ($data) {
                                if (!empty($data['data']['video_cover'])) {
                                    $video_cover = $data['data']['video_cover'];
                                    $yunVideoModel->where(array('id' => $vid))->setField(array('videocover' => $video_cover));
                                }
                            }
                        }
                        $videos[$key]['cover'] = $yunInfo['videocover'];
                    }
                }
                $info    = $videos;
                $code    = 0;
                $message = '热门视频';
                $return  = $this->buildReturn($info, $code, $message);
                $this->ajaxReturn($return);
                break; 
            case 'other':
                $videoModel = D('Video');
                $yunvideoModel = D('YunVideo');
                $condition['status'] = 0;
                //$condition['_logic'] = 'and';
                $page = I('request.page',1);
                $pagenum = 5;
                $videos     = $videoModel->where($condition)->order(array('order'=>'desc'))->limit(($page-1)*$pagenum,$pagenum)->select();
                
                foreach ($videos as $key => $val) {
                    $vid = $val['videoid'];
                    if(!empty($vid)){
                        $yunVideoModel = D('YunVideo');
                        $yunInfo = $yunVideoModel->where(array('id' => $vid))->find();
                        //$video_cover = "/Public/default/img/video-default-img.png";

                        if ($yunInfo['videocover'] == '/Public/default/img/video-default-img.png') {
                            $data = R('VideoUpload/getVideoInfo', array($yunInfo['name']));
                            if ($data) {
                                if (!empty($data['data']['video_cover'])) {
                                    $video_cover = $data['data']['video_cover'];
                                    $yunVideoModel->where(array('id' => $vid))->setField(array('videocover' => $video_cover));
                                }
                            }
                        }
                        $videos[$key]['cover'] = $yunInfo['videocover'];
                    }
                }
                $info    = $videos;
                $code    = 0;
                $message = '热门视频';
                $return  = $this->buildReturn($info, $code, $message);
                $this->ajaxReturn($return);
                break;   
		    default:
		    	$this->ajaxReturn(null);	
    	}
    }

    public function detail(){
        $param = I('request.param','1');//
        $videoModel = D('Topic');
        $userModel = D('User');
        $condition['id'] = $param;
        $condition['status'] = 0;
        $video     = $videoModel->where($condition)->find();
        $video['username'] = $videoModel->field('nickname')->where(array('id'=>$video['uid']))->find();
        $video['formattime'] = date('Y-m-d H:i',$video['addtime']);
        $video['content'] = htmlspecialchars_decode($video['content']);
        $info    = $video;
        $code    = 0;
        $message = '视频详情';
        $return  = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

	public function listing($fid, $p, $number, $order, $sort)
    {
        if (empty($fid)) {
            $info = 'parameter_invalid';
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }


        $order_arr = array('id', 'title', 'addtime', 'synopsis');
        if (!in_array($order, $order_arr)) {
            $info = 'parameter_invalid';
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
        $sort_arr = array('asc', 'desc');
        if (!in_array($sort, $sort_arr)) {
            $info = 'parameter_invalid';
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $videoModel = D('Video');
        $condition['fid'] = $fid;
        $condition['status'] = 0;
        $count = $videoModel->where($condition)->count();
        $Page = new \Think\AjaxPage($count, $number, 'ajax_video_page');
        $show = $Page->show(); // 分页显示输出
        $videos = $videoModel->where($condition)->order(array('order' => 'desc', $order => $sort))->limit(($p - 1) * $number, $number)->select();
        foreach ($videos as $key => $val) {
            $vid = $val['videoid'];
            if (!empty($vid)) {

                $yunVideoModel = D('YunVideo');
                $yunInfo = $yunVideoModel->where(array('id' => $vid))->find();
                //$video_cover = "/Public/default/img/video-default-img.png";

                if ($yunInfo['videocover'] == '/Public/default/img/video-default-img.png') {
                    $data = R('VideoUpload/getVideoInfo', array($yunInfo['name']));
                    if ($data) {
                        if (!empty($data['data']['video_cover'])) {
                            $video_cover = $data['data']['video_cover'];
                            $yunVideoModel->where(array('id' => $vid))->setField(array('videocover' => $video_cover));
                        }
                    }
                }
                $videos[$key]['cover'] = $yunInfo['videocover'];
            }
        }


        $info['data'] = $videos;
        $info['page'] = $show;
        $info['count'] = $count;
        return $info;
    }


    public function getlisting()
    {
        $fid = I('request.fid', null, 'intval');
        $p = I('request.p', 1, 'intval');
        $number = I('request.number', 6, 'intval');
        $order = I('request.order', 'id');
        $sort = I('request.sort', 'asc');
        ($number > 50) ? $number = 50 : '';

        $info = $this->listing($fid, $p, $number, $order, $sort);
        $code = 0;
        $message = '视频列表';
        $return = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }
    	
    
}
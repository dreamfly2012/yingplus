<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends CommonController {
    public function index(){
    	$param = I('request.param','banner');
    	switch($param){
    		case 'banner':
    			$arr = array(
		    		'0'=>array('url'=>'http://www.yekongque.cc/','banner'=>'/Public/mobile/images/yekongque_bg_1.png'),
		    		'1'=>array('url'=>'http://www.threeontheroad.com/','banner'=>'/Public/mobile/images/sanrenxing_bg_1.png'),
		    		'2'=>array('url'=>'http://www.xiayouqiaomu.com/','banner'=>'/Public/mobile/images/xiayouqiaomu_bg_1.png')
		    	);
		    	$this->ajaxReturn($arr);
		    	break;
		    default:
		    	$this->ajaxReturn(null);	
    	}
    	
    }
}
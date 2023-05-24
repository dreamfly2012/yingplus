<?php

namespace Home\Controller;
//yemiantiaozhuan
class ActressController extends CommonController{
	public function index(){
		$url= "http://www.yingplus.cc/gongyi.php/Home/Actress/index";
		header('Location:'.$url);
	}
}
<?php

namespace Home\Controller;

class StatisticsController extends CommonController{
	public function index(){
		$this->fansGender();
	}

	//粉丝性别比
	public function fansGender(){
		$userProfileModel = D('UserProfile');
		$genders = $userProfileModel->field('gender')->select();
		$unknowncount = 0;
		$malecount = 0;
		$femalecount = 0;
		
		foreach($genders as $key=>$val){
			if($val['gender']=='0'){
				$unknowncount++;
			}elseif($val['gender']=='1'){
				$malecount++;
			}elseif($val['gender']=='2'){
				$femalecount++;
			}
		}
		
		$this->assign('unknowncount',$unknowncount);
		$this->assign('malecount',$malecount);
		$this->assign('femalecount',$femalecount);
		$this->display('fansGender');
	}

	//粉丝年龄比
	public function FansAge(){
		
	}

	//热门访问页
	public function hotView(){
		$accessUrlModel = D('AccessUrl');
		$info = $accessUrlModel->field('uid,url,addtime,count(url) as count')->group('url')->order(array("count('url') desc"))->limit(10)->select();
		$this->assign('info',$info);
		$data_count = ""; 
		$data_label = "";
		foreach($info as $key=>$val){
			$data_count .= $val['count'].',';
			$data_label .= "'".$val['url']."',";
		}
		$data_count = trim($data_count,',');
		$data_label = trim($data_label,',');
		$this->assign('data_label',$data_label);
		$this->assign('data_count',$data_count);
		$this->display('hotView');
	}

	//


}

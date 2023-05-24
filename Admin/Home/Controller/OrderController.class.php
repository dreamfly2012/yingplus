<?php

namespace Home\Controller;

class OrderController extends CommonController {
	
	public function index() {
		$activityMovieOrderModel = D('ActivityMovieOrder');
		$externalMovieOrderModel = D('ExternalMovieOrder');
		$externalCinemaModel = D('ExternalCinema');
		
		$type = I('request.type','group');

		if($type=='group'){
			$count = $activityMovieOrderModel->where(array('order_status' =>1))->count();
			$Page = new \Think\Page($count, C('PAGE_LISTROWS'));
			parent::setPageConfig($Page);
			$show = $Page->show();
			$orderList = $activityMovieOrderModel->where(array('order_status' =>1))->order(array('pay_time'=>'desc'))->page($_GET['p'].','.C('PAGE_LISTROWS'))->select();
			$this->assign('page', $show);
			$this->assign('orderList', $orderList);
			$this->display('group_order');
		}else{
			$count = $externalMovieOrderModel->where(array('order_status' =>1))->count();
			$Page = new \Think\Page($count, C('PAGE_LISTROWS'));
			parent::setPageConfig($Page);
			$show = $Page->show();
			$orderList = $externalMovieOrderModel->where(array('order_status' =>1))->order(array('pay_time'=>'desc'))->page($_GET['p'].','.C('PAGE_LISTROWS'))->select();
			foreach($orderList as $key=>$val){
				$orderList[$key]['cinemaname'] = $externalCinemaModel->getFieldById($val['mid'],'name');
				$orderList[$key]['buylink'] = $externalCinemaModel->getFieldById($val['mid'],'link');
			}
			$this->assign('page', $show);
			$this->assign('orderList', $orderList);
			$this->display('through_order');
		}
	}

	public function handle(){
		$id = I('request.id',null);
		if(is_null($id)){
			$this->error('参数错误');
		}else{
			$externalMovieOrderModel = D('ExternalMovieOrder');
			$externalCinemaModel = D('ExternalCinema');
			$order = $externalMovieOrderModel->where(array('id' =>$id))->find();
			
			$order['cinemaname'] = $externalCinemaModel->getFieldById($order['mid'],'name');
			$order['buylink'] = $externalCinemaModel->getFieldById($order['mid'],'link');
			
			$this->assign('order', $order);
			$this->display('handle');
		}
	}

	public function updateOrder(){
		$id = I('request.id',null);
		if(is_null($id)){
			$this->error('参数错误');
		}else{
			$externalMovieOrderModel = D('ExternalMovieOrder');
			$data['handle_status'] = I('request.handle_status');

			$externalMovieOrderModel->where(array('id' =>$id))->save($data);

			$this->redirect('Order/index',array('type'=>'through'));
		}
	}

	

}
<?php

namespace Home\Controller;

class WelfareEventController extends CommonController{
	//非法请求入口
	public function index(){
		die('方法不存在');
	}

	//展示公益列表
	public function showEvent(){
		$fid = I('request.fid',1,'intval');
		$number = I('request.number','10','intval');
		$p = I('request.p','1','intval');
		$welfareEventModel = D('WelfareEvent');
		$attachmentModel = D('Attachment');
		$condition['fid'] = $fid;
		$condition['status'] = 0;
		$count = $welfareEventModel->where($condition)->count();
		$events = $welfareEventModel->where($condition)->order(array('id'=>'asc'))->limit(($p-1)*$number,$number)->select();
		foreach($events as $key=>$val){
			$attachment_arr = explode(',',$val['attachment']);
			$img = "";
			foreach($attachment_arr as $kkey=>$vval){
				if($vval){
					$remote_url = $attachmentModel->getFieldById($vval,'remote_url');
					$img .= "<a href='$remote_url' class='fancybox'><img src='$remote_url' /></a>";
				}
			}
			$events[$key]['img_list'] = $img;
		}

		$forums = R('Forum/listing',array(1,100));
		$this->assign('forums',$forums);
		$Page       = new \Think\Page($count,$number);// 
		$show       = $Page->show();// 分页显示输出
		$this->assign('events',$events);
		$this->assign('page',$show);
		$this->display('show_event');
	}

	//申请公益列表
	public function applylist(){
		$applyEventModel = D('ApplyEvent');
		$lists = $applyEventModel->order(array('status'=>'asc','addtime'=>'desc'))->select();
		$this->assign('lists',$lists);
		$this->display('applylist');
	}

	//设置申请已联系
	public function applyHandle(){
		$id = I('request.id',null);
		$applyEventModel = D('ApplyEvent');
		$applyEventModel->where(array('id'=>$id))->setField(array('status'=>1));
		$this->success('修改成功！');
	}

	//添加公益
	public function add(){
		$forums = R('Forum/listing',array(1,100));

		$this->assign('forums',$forums);

		$this->display('add');
	}

	//添加公益
	public function addDo(){
		$subject = I('request.subject',null);
		$content = I('request.content',null);
		$url = I('request.url',null);
		$begintime = I('request.begintime',null,'strtotime');
		$endtime = I('request.endtime',null,'strtotime');
		$status = I('request.status',null,'intval');
		$promote = I('request.promote',null,'intval');
		$fid = I('request.fid',null,'intval');
		$attachment = I('request.attachment',null);

		$data['subject'] = $subject;
		$data['content'] = $content;
		$data['url'] = $url;
		$data['begintime'] = $begintime;
		$data['endtime'] = $endtime;
		$data['status'] = $status;
		$data['fid'] = $fid;
		$data['addtime'] = time();
		$data['promote']= $promote;

		if(!empty($attachment)){
			$data['attachment'] = $attachment;
		}


		$welfareEventModel = D('WelfareEvent');
		$result = $welfareEventModel->add($data);

		if($result!=false){
			$this->success('添加成功');
		}else{
			$this->error('添加失败');
		}
	}


	//编辑保单
	public function edit(){
		$id = I('request.id',null,'intval');
		if(empty($id)){
			$info = null;
			$code = -1;
			$message = C('parameter_invalid');
			$return = $this->buildReturn($info, $code, $message);
			$this->ajaxReturn($return);
		}

		$welfareEventModel = D('WelfareEvent');
		$attachmentModel = D('Attachment');
		$event = $welfareEventModel->where(array('id'=>$id))->find();
		$attachments = $event['attachment'];
		$attachments_arr = explode(',',$attachments);
		$img_list = array();
		foreach($attachments_arr as $key=>$val){
			if($val){
				$remote_url = $attachmentModel->getFieldById($val,'remote_url');
				$img_list[$val] = $remote_url;
			}
		}
		$this->assign('img_list',$img_list);
		$this->assign('event',$event);
		$this->display('edit');
	}

	//编辑提交
	public function editDo(){
		$id = I('request.id',null,'intval');
		$subject = I('request.subject',null);
		$content = I('request.content',null);
		$url = I('request.url',null);
		$begintime = I('request.begintime',null,'strtotime');
		$endtime = I('request.endtime',null,'strtotime');
		$status = I('request.status',null,'intval');
		$promote = I('request.promote',null,'intval');
		$attachment = I('request.attachment',null);

		$data['id'] = $id;
		$data['subject'] = $subject;
		$data['content'] = $content;
		$data['url'] = $url;
		$data['begintime'] = $begintime;
		$data['endtime'] = $endtime;
		$data['status'] = $status;
		$data['promote']= $promote;
		if(!empty($attachment)){
			$data['attachment'] = $attachment;
		}

		$welfareEventModel = D('WelfareEvent');
		
		$result = $welfareEventModel->where(array('id'=>$id))->save($data);

		if($result!==false){
			$this->success('修改成功');
		}else{
			$this->error('修改失败');
		}
	}

	//回收站显示
	public function recycleEvent(){
		$fid = I('request.fid',1,'intval');
		$number = I('request.number','10','intval');
		$p = I('request.p','1','intval');
		$welfareEventModel = D('WelfareEvent');
		$attachmentModel = D('Attachment');
		$condition['fid'] = $fid;
		$condition['status'] = 1;
		$count = $welfareEventModel->where($condition)->count();
		$events = $welfareEventModel->where($condition)->order(array('id'=>'asc'))->limit(($p-1)*$number,$number)->select();
		foreach($events as $key=>$val){
			$attachment_arr = explode(',',$val['attachment']);
			$img = "";
			foreach($attachment_arr as $kkey=>$vval){
				if($vval){
					$remote_url = $attachmentModel->getFieldById($vval,'remote_url');
					$img .= "<a href='$remote_url' class='fancybox'><img src='$remote_url' /></a>";
				}
			}
			$events[$key]['img_list'] = $img;
		}

		$forums = R('Forum/listing',array(1,100));
		$this->assign('forums',$forums);
		$Page       = new \Think\Page($count,$number);// 
		$show       = $Page->show();// 分页显示输出
		$this->assign('events',$events);
		$this->assign('page',$show);
		$this->display('recycle');
	}

	//恢复删除的公益事件
	public function revertEvent(){
		$ids     = I('request.ids', null);
        $ids_arr = explode(',', $ids);
        foreach ($ids_arr as $key => $val) {
            $welfareEventModel = D('WelfareEvent');
            $welfareEventModel->where(array('id' => $val))->setField(array('status' => 0));
        }
        $info    = null;
        $code    = 0;
        $message = '还原成功';
        $return  = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
	}

	//删除公益事件
	public function deleteEvent(){
		$ids     = I('request.ids', null);
        $ids_arr = explode(',', $ids);
        foreach ($ids_arr as $key => $val) {
            $welfareEventModel = D('WelfareEvent');
            $welfareEventModel->where(array('id' => $val))->setField(array('status' => 1));
        }
        $info    = null;
        $code    = 0;
        $message = '删除成功';
        $return  = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
	}
}
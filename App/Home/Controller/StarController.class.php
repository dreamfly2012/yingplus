<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/11/17
 * Time: 14:02
 */

namespace Home\Controller;

class StarController extends CommonController{
    public function index(){
        $starID = I('request.starID',null,'intval');
        $this->redirect('Forum/index',array('fid'=>$starID));
    }
}
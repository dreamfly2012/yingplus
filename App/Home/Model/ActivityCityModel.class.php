<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/10/21
 * Time: 14:56
 */
namespace Home\Model;

class ActivityCityModel extends CommonModel{
    //获取所有举办活动的城市
    public function getActivityAddress(){
        $result = $this->select();
        return $result;
    }
}
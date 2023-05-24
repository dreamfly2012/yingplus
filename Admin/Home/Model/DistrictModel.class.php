<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/9/24
 * Time: 21:34
 */

namespace Home\Model;

/**
 * Class DistrictModel
 * @package Home\Model
 * @discribe 本类用于省市的相关处理
 */
class DistrictModel extends CommonModel
{

    //得到所有省的集合
    public function getAllProvinces(){
        return $this->field(array('id','name'))
                     ->where(array('upid'=>0))
                     ->select();
    }
    //得到省份的名字
    public function getProvince($pid){
        $city = $this->field(array('name'))
            ->where(array('id'=>$pid))
            ->select();
        return $city[0]['name'];
    }
    //得到城市的名字
    public function getCity($cid){
        $city = $this->field(array('name'))
                     ->where(array('id'=>$cid))
                     ->select();
        return $city[0]['name'];
    }
    //得到指定省的城市
    public function getCitiesByPid($pid){
        return $this->field(array('id','name'))
                     ->where(array('upid'=>$pid))
                     ->select();
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: dreamfly
 * Date: 2015/8/15
 * Time: 14:36
 */


namespace Home\Model;

class DistrictModel extends CommonModel{
    //获取指定省份下的城市
    public function getCityByProvince($province){
        $result = $this->where(array('upid'=>$province,'level'=>2))->select();
        return $result;
    }

    public function getAllProvince(){
        $result = $this->where(array('upid'=>0,'level'=>1))->select();
        return $result;
    }

    /**
     * @param $id
     * @return mixed
     * 根据id获取省份城市地区的名称
     */
    public function getNameById($id){
        $result = $this->where(array('id'=>$id))->getField('name');
        return $result;
    }

    /**
     * @param $id
     * @return mixed
     * 根据id获取当前的level
     * 1 省份
     * 2 城市
     * 3 地区
     */
    public function getLevelById($id){
        $result = $this->where(array('id'=>$id))->getField('level');
        return $result;
    }


    /**
     * @param $id
     * @return mixed
     * 获取所有孩子节点
     */
    public function getChildrenById($id){
        $result = $this->where(array('upid'=>$id))->select();
        return $result;
    }


}
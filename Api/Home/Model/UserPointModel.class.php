<?php
   namespace Home\Model;

	class UserPointModel extends CommonModel{
        //根据转进参数，查询count
        public function getDataByCondition($condition){
            $userPointData = $this->where($condition)->count();
            return $userPointData;
        } 

        public function addUserPointData($data){
        	$this->add($data);
        }

        public function getMessageByCondition($condition){
        	$userPointData = $this->where($condition)->find();
            return $userPointData;
        }  


        public function isExixtTopic($condition){
            return $this->where($condition)->select();
        }

        public function isFavor($condition){
            return $this->where($condition)->select();
        }

	}
?>
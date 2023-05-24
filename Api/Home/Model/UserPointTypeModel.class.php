<?php
   namespace Home\Model;

	class UserPointTypeModel extends CommonModel{
         //根据类型查询数据
		public function getDataByPointType($type){
           $condition['type'] = $type;
           $pointTypeData = $this->where($condition)->find();
		   return $pointTypeData;
		}

		
	}
?>
<?php
namespace Home\Model;

class ConfigModel extends CommonModel{
	  //将config文件的所有数据加进数据库
	  public function insertData($data){
          $this->add($data);          
	  }
	  //判别名字是否唯一
	  public function distinct($name){
	  	$condition['name']=$name;
	  	 $result = $this->where($condition)->select();
	  	 return $result;
	  }
	  //用于更新变量
	  public function updateConfig($name,$value,$id){	  	
          $condition['name']=$name;
          $condition['value'] = $value;
          $con['id'] = $id;
          $this->where($con)->save($condition);
	  }

	  //用于删除数据
	  public function deleteById($id){
	  	  $data['id']=$id;
	  	  $this->where($data)->delete();
	  }
}

?>
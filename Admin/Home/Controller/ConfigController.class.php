<?php

namespace Home\Controller;
use Think\Controller;

class ConfigController extends CommonController{
	/**用于将config文件的内容添加到数据库里去**/
    public function configTest(){
        $test = include (APP_PATH."Home/Conf/config.php");
        foreach($test as $key=>$value){
        	$arr =null;
            $arr['name'] = $key;
            $arr['value'] = $value;           
            $config = D('Config');
            $onlyName = $config->distinct($arr['name']);
            if(empty($onlyName)){
               $config->insertData($arr);

            }                       
        }
         echo '{"code":1}';        
    }

    /**用于更新数据库里的变量**/
    public function configUpdate(){
        $name = I('post.config_name');
        $value = I('post.config_value');
        $id =  I('post.config_id');
        $config = D('Config');
        $config->updateConfig($name,$value,$id);
        $this->showSqlData();
    }    
    /*用于生成config文件*/
    public function createConfig(){
        $model = M('Config');
        $data = $model->field('name,value')->select();
        $result = $this->file($data);
         $sf=APP_PATH."Home/Conf/config33.php"; //文件名
         $fp=fopen($sf,"w"); //写方式打开文件
         if($fp){
            fwrite($fp,$result); //存入内容
            fclose($fp);
            echo '{"code":1}';
         }else{
            echo '{"code":0}';
         }         
    }

    public function file($data){   	 
		$str_tmp="<?php\r\n";  
		$str_end="?>";  
		$str_tmp.='return array(';
        $str_tmp.="\r\n";
		foreach ($data as $key => $value) {
			$str_tmp.="'".$data[$key]['name']."'".'=>'."'".$data[$key]['value']."',";
			$str_tmp.="\r\n";
		}	
		$str_tmp.=');';	
        $str_tmp.="\r\n";	 
		$str_tmp.=$str_end; //加入结束符
        return $str_tmp;
    }

    /**用于展示数据库里的变量**/
    public function showSqlData(){
       $model = M('Config');      
       $count = $model->count();
       $Page = new \Think\Page($count,'10');
       $data = $model ->limit($Page->firstRow.','.$Page->listRows)
                     ->select();
       $show = $Page->show();
       $this->assign('page',$show);
       $this->assign('data',$data);
       $this->display('configIndex');
    }     
     /**用于删除数据库里的变量**/
     public function daleteData(){
        $id = I('post.id');
        $model = D('Config');
        $model->deleteById($id);
         echo '{"code":"2"}';
     }

     /**用于新增数据库里的变量**/
     public function addMysqlVal(){
        $config = D('Config');
        $arr['name'] = I('post.config_name');
        $arr['value'] = I('post.config_value');
        $onlyName = $config->distinct($arr['name']);
        if(empty($onlyName)){
            $config->insertData($arr);
        }else{
            echo "<script>alert('变量名已经存在')</script>";
        }
        $this->showSqlData();         
     }
}
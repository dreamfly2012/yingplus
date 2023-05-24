<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/9/26
 * Time: 14:17
 */

namespace Home\Model;

/**
 * Class AttachmentModel
 * @package Home\Model
 * @discribe : 处理图片或附件
 */
class AttachmentModel extends CommonModel
{
    public function getPathById($id){
        $data['id'] = $id;
        $model = $this->where($data)->find();
        return $model;
    }

    public function getImgPathById($id){
        $result = $this->field(array('path'))->where(array('id'=>$id))->select();
        return $result[0]['path'];
    }
    public function setAttachPath($id,$data){
        $condition['id']=$id;
        $this->where($condition)->save($data);
    }
    public function saveAttachment($arr){
        return $this->add($arr);
    }
    public function addAttachPath($data){
        $result = $this->add($data);
        if($result){
            return $result;
        }
    }

    public function deleteAttachData($id){
         $data['id']=$id;
         $this->where($data)->delete();        
    }
}
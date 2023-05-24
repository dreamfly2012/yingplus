<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/10/9
 * Time: 9:41
 */

namespace Home\Model;


class UserProfileModel extends CommonModel
{

    public function getPhotoByUid($uid){
        $photoArr = $this->field(array('photo'))
                         ->where(array('uid'=>$uid))
                         ->select();
        return $photoArr[0]['photo'];
    }

}
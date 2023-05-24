<?php
/**
 * Created by PhpStorm.
 * User: yinsheng
 * Date: 2015/8/13
 * Time: 17:40
 */

namespace Home\Model;


class CaptchaBlockModel extends CommonModel{
    /**
     * checkMessageCount
     * Describe:
     * Param:$clientIP:IP,$date:date
     * Return:true/false
     */
    public function checkMessageCountByIp($clientIP,$date){
        $result = $this->field(array('id','count'))->where(array('ip'=>$clientIP,'date'=>$date))->select();
        if(!empty($result[0]['id'])){
            if($result[0]['count'] >= C('IP_COUNT')){
                return true;
            }else{
                $this->where(array('ip'=>$clientIP))->setInc('count',1);
                return false;
            }
        }else{
            $data['ip'] = $clientIP;
            $data['count'] = 1;
            $data['date'] = $date;
            $this->add($data);
            return false;
        }
    }

    public function checkMessageCountByTelephone($telephone,$date){
        $result = $this->field(array('id','count'))->where(array('ip'=>$telephone,'date'=>$date))->select();
        if(!empty($result[0]['id'])){
            if($result[0]['count'] >= C('PHONE_COUNT')){
                return true;
            }else{
                $this->where(array('telephone'=>$telephone))->setInc('count',1);
                return false;
            }
        }else{
            $data['telephone'] = $telephone;
            $data['count'] = 1;
            $data['date'] = $date;
            $this->add($data);
            return false;
        }
    }
}
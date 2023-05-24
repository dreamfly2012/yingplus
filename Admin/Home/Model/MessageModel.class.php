<?php
/**
 * Created by PhpStorm.
 * User: roak
 * Date: 2015/9/19
 * Time: 10:31
 */

namespace Home\Model;

/**
 * Class MessageModel
 * @package Home\Model
 * @discribe :该类用于将消息处理
 */
class MessageModel extends CommonModel
{
    //保存消息
    public function saveMessage($date){

        $this->add($date);
    }
}
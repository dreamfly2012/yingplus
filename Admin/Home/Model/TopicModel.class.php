<?php
namespace Home\Model;

class TopicModel extends CommonModel{
    //做为创建话题者的查询函数
    public function search($array,$var,$Page){
        $condition = $this->condition($array,$var);
        $result = $this->field('subject,yj_topic.id,istop,isadmintop,yj_topic.isdigest,admin_digest,yj_topic.status,yj_user.nickname,uid,addtime')
                  ->join('yj_user on yj_user.id = yj_topic.uid')
                  ->where($condition)
                  ->order('addtime desc')
                  ->limit($Page->firstRow.','.$Page->listRows)
                  ->select();
        return $result;
    }
    //做为参与话题者的查询函数
    public function CanSearch($array,$var,$Page){
        $condition = $this->condition($array,$var);
        $result = $this->field('yj_topic.subject,istop,isadmintop,yj_topic.id,yj_topic.isdigest,admin_digest,yj_topic.status,yj_user.nickname,yj_topic.addtime,yj_topic.uid')
            ->join('yj_topic_response on yj_topic.id = yj_topic_response.tid')
            ->join('yj_user on yj_user.id = yj_topic.uid')
            ->where($condition)
            ->order('addtime desc')
            ->distinct('true')
            ->limit($Page->firstRow.','.$Page->listRows)
            ->select();
        return $result;
    }

    public function Condition($array,$var){
        if($var==0){
            if($array['nickname']){
                $arr['yj_user.nickname']=$array['nickname'];
            }
        }else if($var==1){
            if($array['nickname']) {
                $data['nickname'] = $array['nickname'];
                $user = M('User');
                $model = $user->field('id')->where($data)->find();
                $arr['yj_topic_response.uid'] = $model['id'];
            }
        }
        if($array['status']){
            $arr['yj_topic.status']=$array['status'];
        }else{
            $arr['yj_topic.status']=0;
        }
        if($array['plus']==1){
            $plus['yj_topic.isdigest']=$array['plus'];   //管理员加精或吧主加精
            $plus['yj_topic.admin_digest']=$array['plus'];
            $plus['_logic'] = 'or';
        }else{
            $arr['yj_topic.isdigest']=0; //管理员与吧主都没加精过
            $arr['yj_topic.admin_digest']=0;
        }
        if($array['startime']&&$array['endtime']){
            $arr['yj_topic.addtime']=array('between',array($array['startime'],$array['endtime']));
        }
        if($array['subject']){
            $arr['yj_topic.subject']=array('like','%'.$array['subject'].'%');
        }
        if($array['istop']){
            $arr['_string'] = "(yj_topic.istop=".$array['istop'].")OR(yj_topic.isadmintop=".$array['istop'].")";
        }else{
            $arr['yj_topic.istop']=0;
            $arr['yj_topic.isadmintop']=0;
        }
        if($plus){
            $arr['_complex'] = $plus;
        }
        return $arr;
    }

    //做为创建话题者的查询数据的总数
    public function countda($array,$var){
        $condition = $this->condition($array,$var);
        $result = $this->join('yj_user on yj_user.id = yj_topic.uid')
            ->where($condition)
            ->count();
        return $result;
    }
    //做为参与话题者的查询数据的总数
    public function canCount($array,$var){
        $condition = $this->condition($array,$var);
        $result = $this->field('yj_topic_response.tid')
            ->join('yj_topic_response on yj_topic.id = yj_topic_response.tid')
            ->join('yj_user on yj_user.id = yj_topic.uid')
            ->where($condition)
            ->distinct('true')
            ->select();
        $count = $this->eachNum($result);
        return $count;
    }
    public function eachNum($array){
        $count=0;
        foreach($array as $k=>$v){
            if($array[$k]['tid']){
                $count+=1;
            }
        }
        return $count;
    }
    public function deleteTopic($array){
        $condition = $this->toChange($array);
        $topic = $this->where($condition)
                 ->order('deleteTime desc')
                 ->select();/*->join('yj_user on yj_topic.uid = yj_user.id')*/
        $topicVar = $this->changeDataId($topic);
        return $topicVar;
    }
    public function changeDataId($array){
        foreach($array as $key=>$value){
            $array[$key]['admin_name'] = $this->getNickname($value['admin_id']);
            $array[$key]['nickname'] = $this->getNickname($value['uid']);
            $array[$key]['forum_name'] = $this->getForumName($value['fid']);
            $array[$key]['url'] = U('Home/Topic/setStatus',array('id'=>$value['id']));
        }
        return $array;
    }
    public function getNickname($id){
        $user = M('User');
        $data['id']=$id;
        $name = $user->field('nickname')->where($data)->find();
        return $name['nickname'];
    }

    public function getForumName($id){
        $forum = M('Forum');
        $data['id']=$id;
        $name = $forum->field('name')->where($data)->find();
        return $name['name'];
    }

    public function toChange($array){
        if($array['nickname']) {
            $data['nickname'] = $array['nickname'];
            $user = M('User');
            $model = $user->field('id')->where($data)->find();
            $arr['uid'] = $model['id'];
        }
        if($array['startime']&&$array['endtime']){
            $arr['yj_topic.deleteTime']=array('between',array($array['startime'],$array['endtime']));
        }
        $arr['yj_topic.status']=1;
        return $arr;
    }

    /*将话题恢复*/
    public function deal($id){
        $data = array('status'=>0);
        $condition['id']=$id;
        $this->where($condition)->setField($data);
    }
    /*将话题删除*/
    public function setTopicStatusDetele($id){
        $data = array('status'=>1,'houtai_id'=>session('user_id'));
        $condition['id']=$id;
        $this->where($condition)->setField($data);
    }

    /*将话题加精*/
    public function setDigest($id){
        $data = array('admin_digest'=>1);
        $condition['id']=$id;
        $this->where($condition)->setField($data);
    }

    /*取消话题的加精*/
    public function cancelDigest($id){
        $data = array('admin_digest'=>0,'isdigest'=>0);
        $condition['id']=$id;
        $this->where($condition)->setField($data);
    }
    /*将话题置顶*/
    public function setIstop($id){
        $data = array('isadmintop'=>1);
        $condition['id']=$id;
        $this->where($condition)->setField($data);
    }
    /*取消话题置顶*/
    public function cancelIstop($id){
        $data = array('istop'=>0,'isadmintop'=>0);
        $condition['id']=$id;
        $this->where($condition)->setField($data);
    }
    public function getSendContentId($id){
        $data['id']=$id;
        $result = $this->field('uid,admin_id,houtai_id')->where($data)->find();
        return $result;
    }

    public function GetSendContentName($id){
        $build['tousername']=$this->getNickname($id);
        return $build;
    }

    //得到帖子的主题
    public function getTopicSubject($tid){
        $result = $this->field()
             ->where(array('id'=>$tid))
             ->select();
        return $result[0]['subject'];
    }

    //得到指定用户的帖子数
    public function getTopicNumByUid($uid,$status){
        return $this->where(array('uid'=>$uid,'status'=>$status))
                     ->count();
    }

    //得到指定帖子的加精数
    public function getDigestTopicNumByUid($uid){
        return $this->where(array('uid'=>$uid,'status'=>0,'isdigest'=>1))
                     ->count();
    }
   
    public function getMessageById($id){
        $condition['id'] = $id;
        $result = $this->where($condition)->find();
        return $result;
    }

    public function selectTopic($id){
        $condition['id']=$id;
        $topic = $this->where($condition)->find();
        return $topic;
    }

    public function saveTopicById($id,$data){
        $condition['id'] = $id;
        $topicData = $data;        
        $this->where($condition)->save($topicData);
    }

    public function addTopicData($data){
        $array = $data;
        $this->add($array);
    }
}
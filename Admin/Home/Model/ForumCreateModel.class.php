<?php
namespace Home\Model;

class ForumCreateModel extends CommonModel
{
    //获取未审核的创建工作室申请
    public function getAllJobCreate($Page)
    {
        $result = $this->field('yj_forum_create.id,yj_forum_create.status,forumname,representative,put_time,yj_user.nickname')
            ->join('yj_user ON yj_forum_create.uid = yj_user.id AND yj_forum_create.status = 0')
            ->order('yj_forum_create.status')
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();
        return $result;
    }

    public function getAllCount()
    {
        $result = $this->field('yj_forum_create.id,yj_forum_create.status,forumname,representative,put_time,yj_user.nickname')
            ->join('yj_user ON yj_forum_create.uid = yj_user.id AND yj_forum_create.status = 0')
            ->count();
        return $result;
    }

    //请求审核
    public function setDealJob($id,$method,$name)
    {
        if ($method == 1) {
            //请求上线
            $model = D('Forum');
            $data['name'] = $name;
            $result = $model->where($data)->find();
            if ($result) {
                $data = array('status' => $status);
                $condition['id'] = $id;
                $this->where($condition)->setField($data);
                return 1;
            } else {
                return 3;
            }
        } else if ($method == 2) {
            //请求不通过
            $data = array('status' => $status);
            $condition['id'] = $id;
            $this->where($condition)->setField($data);
            return 2;
        }

    }

    public function selectById($id)
    {
        $data['id'] = $id;
        $model = $this->where($data)->find();
        return $model;
    }


    public function getResultByForumName($name)
    {
        $data['name'] = $name;
        $forum = M('Forum');
        $model = $forum->where($data)->find();
        return $model;
    }

    public function getSelectId($id)
    {
        $data['id'] = $id;
        $id = $this->field('uid')->where($data)->find();
        return $id['uid'];
    }


}
<?php
/*** Created by PhpStorm.*/
namespace Home\Controller;

use Think\Controller;

class JobManageController extends CommonController
{
    /*获取所有创建工作室请求*/
    public function index()
    {
        $model = D('ForumCreate');
        $count = $model->getAllCount();
        $Page  = new \Think\Page($count, C('PAGE_LISTROWS'));
        parent::setPageConfig($Page);
        $show   = $Page->show();
        $result = $model->getAllJobCreate($Page);
        $this->assign('page', $show);
        $this->assign('jobRoom', $result);
        $this->display('jobManageIndex');
    }

    /*编辑工作室的状态函数*/
    public function dealWith()
    {
        $forumCreateModel = D('ForumCreate');
        $userModel        = D('User');
        $id               = I('request.id', null, 'intval');
        $status           = I('request.status', null, 'intval');
        $name             = I('request.name', null);
        $result           = $forumCreateModel->setDealJob($id, $status, $name);
        $uid              = $forumCreateModel->getFieldById($id, 'uid');
        $nickname         = $userModel->getFieldById($uid, 'nickname');
        $forumname        = $forumCreateModel->getFieldById($id, 'forumname');
        if ($result == 3) {
            header('Content-Type:text/html; charset=utf-8;');
            $error_message = '通过上线请求，请先编辑工作室，添加工作室';
            echo "<script>confirm('" . $error_message . "')</script>";
        } else if ($result == 1) {
            $this->sendContentToUser('Hi,亲爱的' . $nickname . '： 恭喜你，你的' . $forumname . '工作室创建申请已通过，快去召集小伙伴们一起到' . $forumname . '工作室交流吧！', $_GET['id'], '创建工作室审核通知');
        } else if ($result == 2) {
            $this->sendContentToUser(C('FORUM_NOT_LINE'), $_GET['id'], '创建工作室审核通知');
        }
        $this->index();
    }

    //恢复话题内容发送消息
    public function sendContentToUser($content, $id, $subject)
    {
        $model  = D('ForumCreate');
        $sendId = $model->getSelectId($id);
        $this->commonSend($content, $sendId['uid'], $subject);
    }

    /*编辑工作室的具体信息*/
    public function jobMassage($id)
    {
        $model        = D('Forum');
        $attach       = D('Attachment');
        $result       = $model->getForumMessage($id);
        $path         = $attach->getPathById($result['photo']);
        $banner       = $attach->getPathById($result['banner']);
        $indexphoto   = $attach->getPathById($result['indexphoto']);
        $datailbanner = $attach->getPathById($result['detailbanner']);
        $this->assign('datailbanner', $datailbanner);
        $this->assign('banne', $banner);
        $this->assign('indexphoto', $indexphoto);
        $this->assign('photo', $path);
        $this->assign('result', $result);
        $this->assign('id', $id);
        $instantStyleModel = D('InstantStyle');
        $styles            = $instantStyleModel->select();
        $this->assign('styles', $styles);
        $this->display('commonEdit');
    }

    public function upload($photo, $root)
    {
        $upload           = new \Think\Upload(); // 实例化上传类
        $upload->maxSize  = 3145728; // 设置附件上传大小
        $upload->exts     = array('jpg', 'gif', 'png', 'jpeg'); // 设置附件上传类型
        $upload->rootPath = $root; //'Uploads/forum/'; // 设置附件上传根目录
        //$upload->savePath  =      '';
        $info = $upload->uploadOne($_FILES[$photo]);
        if (!$info) {
// 上传错误提示错误信息
            return $this->error($upload->getError());
        } else {
// 上传成功 获取上传文件信息
            return $info['savepath'] . $info['savename'];
        }
    }

    public function putJobMassage($id)
    {
        $model        = D('Forum');
        $attachment   = D('Attachment');
        $array        = I('post.array');
        $rootPhoto    = 'Uploads/forum/photo/';
        $rootBanner   = 'Uploads/forum/banner/';
        $indexphoto   = 'Uploads/forum/indexphoto/';
        $detailbanner = 'Uploads/forum/detailbanner/';
        if ($_FILES["photo"]["size"] > 0) {
            $attach['path'] = '/Uploads/forum/photo/' . $this->upload('photo', $rootPhoto);
            $photo          = $model->getForumMessage($id);
            if ($photo['photo']) {
                $attachment->setAttachPath($photo['photo'], $attach);
            } else {
                $forum_photo    = $attachment->addAttachPath($attach);
                $array['photo'] = $forum_photo;
            }
        }
        if ($_FILES["banner"]["size"] > 0) {
            $attach['path'] = '/Uploads/forum/banner/' . $this->upload('banner', $rootBanner);
            $photo          = $model->getForumMessage($id);
            if ($photo['banner']) {
                $attachment->setAttachPath($photo['banner'], $attach);
            } else {
                $forum_banner    = $attachment->addAttachPath($attach);
                $array['banner'] = $forum_banner;
            }
        }
        if ($_FILES["indexphoto"]["size"] > 0) {
            $attach['path'] = '/Uploads/forum/indexphoto/' . $this->upload('indexphoto', $indexphoto);
            $photo          = $model->getForumMessage($id);
            if ($photo['indexphoto']) {
                $attachment->setAttachPath($photo['indexphoto'], $attach);
            } else {
                $forum_banner        = $attachment->addAttachPath($attach);
                $array['indexphoto'] = $forum_banner;
            }
        }
        if ($_FILES["detailbanner"]["size"] > 0) {
            $attach['path'] = '/Uploads/forum/detailbanner/' . $this->upload('detailbanner', $detailbanner);
            $photo          = $model->getForumMessage($id);
            if ($photo['detailbanner']) {
                $attachment->setAttachPath($photo['detailbanner'], $attach);
            } else {
                $forum_banner          = $attachment->addAttachPath($attach);
                $array['detailbanner'] = $forum_banner;
            }
        }
        $model->setForumMassage($id, $array);
        $this->success('编辑成功');
    }

    public function addJobMassage()
    {
        $model            = D('Forum');
        $array            = I('post.array');
        $array['addtime'] = date('Y-m-d');
        $rootPhoto        = 'Uploads/forum/photo/';
        $rootBanner       = 'Uploads/forum/banner/';
        $indexphoto       = 'Uploads/forum/indexphoto/';
        $detailbanner     = 'Uploads/forum/detailbanner/';
        if ($model->autoCheckToken($_POST)) {
            if ($_FILES["photo"]["size"] > 0) {
                $attachment     = D('Attachment');
                $attach['path'] = '/Uploads/forum/photo/' . $this->upload('photo', $rootPhoto);
                $result         = $attachment->add($attach);
                $array['photo'] = $result;
            }
            if ($_FILES["banner"]["size"] > 0) {
                $attachment      = D('Attachment');
                $atth['path']    = '/Uploads/forum/banner/' . $this->upload('banner', $rootBanner);
                $banner          = $attachment->add($atth);
                $array['banner'] = $banner;
            }
            if ($_FILES["indexphoto"]["size"] > 0) {
                $attachment          = D('Attachment');
                $atth['path']        = '/Uploads/forum/indexphoto/' . $this->upload('indexphoto', $indexphoto);
                $indexphoto          = $attachment->add($atth);
                $array['indexphoto'] = $indexphoto;
            }
            if ($_FILES["detailbanner"]["size"] > 0) {
                $attachment            = D('Attachment');
                $atth['path']          = '/Uploads/forum/detailbanner/' . $this->upload('detailbanner', $detailbanner);
                $detailbanner          = $attachment->add($atth);
                $array['detailbanner'] = $detailbanner;
            }
            $model->addForumMassage($array);
        }
        $this->index();
    }

    public function addJobManageSelf()
    {
        $model            = D('Forum');
        $array            = I('post.list');
        $array['addtime'] = date('Y-m-d');
        $rootPhoto        = 'Uploads/forum/photo/';
        $rootBanner       = 'Uploads/forum/banner/';
        $indexphoto       = 'Uploads/forum/indexphoto/';
        $detailbanner     = 'Uploads/forum/detailbanner/';
        if ($model->autoCheckToken($_POST)) {
            if ($_FILES["photo"]["size"] > 0) {
                $attachmen      = M('Attachment');
                $attach['path'] = '/Uploads/forum/photo/' . $this->upload('photo', $rootPhoto);
                $result         = $attachmen->add($attach);
                $array['photo'] = $result;
            }
            if ($_FILES["banner"]["size"] > 0) {
                $attachmen       = M('Attachment');
                $atth['path']    = '/Uploads/forum/banner/' . $this->upload('banner', $rootBanner);
                $banner          = $attachmen->add($atth);
                $array['banner'] = $banner;
            }
            if ($_FILES["indexphoto"]["size"] > 0) {
                $attachmen           = M('Attachment');
                $atth['path']        = '/Uploads/forum/indexphoto/' . $this->upload('indexphoto', $indexphoto);
                $indexphoto          = $attachmen->add($atth);
                $array['indexphoto'] = $indexphoto;
            }
            if ($_FILES["detailbanner"]["size"] > 0) {
                $attachment            = D('Attachment');
                $atth['path']          = '/Uploads/forum/detailbanner/' . $this->upload('detailbanner', $detailbanner);
                $detailbanner          = $attachment->add($atth);
                $array['detailbanner'] = $detailbanner;
            }
            $model->addForumMassage($array);
        }
        $this->showAllJob();

    }
    //编辑工作室的函数。
    public function addForum()
    {
        $model  = D('ForumCreate');
        $attach = D('Attachment');
        $id     = I('request.id', null, 'intval');
        $info   = $model->selectById(I('get.id'));
        $this->assign('result', $info);
        $instantStyleModel = D('InstantStyle');
        $styles            = $instantStyleModel->select();
        $this->assign('styles', $styles);
        $this->display('commonAdd');
    }

    public function showMessage()
    {
        $array = I('post.array');
        if ($array) {
            session('manage_user', $array);
        } else {
            $array = session('manage_user');
        }
        $model = D('Forum');
        $count = $model->getForumManageCount($array);
        if ($count > 0) {
            $Page = new \Think\Page($count, C('PAGE_LISTROWS'));
            parent::setPageConfig($Page);
            $show  = $Page->show();
            $forum = $model->getForumManage($array, $Page);
            $this->assign('condition', $array);
            $this->assign('page', $show);
            $this->assign('forum', $forum);
            $this->display('jobManageUser');
        } else {
            header('Content-Type:text/html; charset=utf-8;');
            $error_message = '还没有编辑工作室信息，请先进行编辑';
            echo "<script>confirm('" . $error_message . "')</script>";
            $this->index();
        }
    }

    public function showAllJob()
    {
        $model = D('Forum');
        $count = $model->count();
        $Page  = new \Think\Page($count, C('PAGE_LISTROWS'));
        parent::setPageConfig($Page);
        $show  = $Page->show();
        $forum = $model->getForumCount($Page);
        $this->assign('page', $show);
        $this->assign('forum', $forum);
        $this->display('jobManageShow');
    }

    public function dealJob()
    {
        $array = I('post.array');
        session('manage', $array);
        $model = D('Forum');
        $count = $model->getForumManageCount($array);
        $Page  = new \Think\AjaxPage($count, C('PAGE_LISTROWS'), 'indexManage');
        $show  = $Page->show();
        $forum = $model->getForumManage($array, $Page);
        if ($forum != null) {
            $this->assign('page', $show);
            $this->assign('forum', $forum);
            $this->display('index');
        } else {
            $this->display('edit');
        }

    }
    public function indexManage()
    {
        $array = session('manage');
        $model = D('Forum');
        $count = $model->getForumManageCount($array);
        $Page  = new \Think\AjaxPage($count, C('PAGE_LISTROWS'), 'indexManage');
        $show  = $Page->show();
        $forum = $model->getForumManage($array, $Page);
        $this->assign('page', $show);
        $this->assign('forum', $forum);
        $this->display('index');
    }
    public function testResetIndex()
    {
        $this->display('add');
    }

    public function testCommon()
    {
        $this->display('commonEdit');
    }

    /*首页工作室推荐*/
    public function showPromote()
    {
        $model = D('forum');
        $count = $model->forumPromoteCount();
        $Page  = new \Think\Page($count, C('PAGE_LISTROWS'));
        parent::setPageConfig($Page);
        $show  = $Page->show();
        $furom = $model->forumPromote($Page);
        $this->assign('page', $show);
        $this->assign('furom', $furom);
        $this->display('jobPromote');
    }
    /*首页工作室显示顺序编辑*/
    public function displayEdit()
    {
        $id = $_POST['display_id'];
        // $array['name'] = $_POST['forum_name'];
        $array['displayorder'] = $_POST['display_xunshi'];
        $model                 = D('forum');
        $model->saveMessage($id, $array);
        $this->showPromote();
    }

    /*删除工作室信息*/
    public function deleteForumData()
    {
        $id          = I('post.id');
        $forumModel  = D('Forum');
        $attachModel = D('Attachment');
        $idArray     = $forumModel->selectAttachId($id);
        $forumModel->deleteData($id);
        $attachModel->deleteAttachData($idArray['photo']);
        $attachModel->deleteAttachData($idArray['banner']);
        $attachModel->deleteAttachData($idArray['indexphoto']);
        $attachModel->deleteAttachData($idArray['detailbanner']);
        echo '{"code":"1"}';
    }

    //图片墙管理
    public function showPicture()
    {
        $fid               = I('request.fid', 1, 'intval');
        $p                 = I('request.p', 1, 'intval');
        $number            = I('request.number', 24, 'intval');
        $forumPictureModel = D('ForumPicture');
        $attachmentModel   = D('Attachment');

        $condition['fid']    = $fid;
        $condition['status'] = 0;
        $count               = $forumPictureModel->where($condition)->count();
        $pictures            = $forumPictureModel->where($condition)->order(array('id' => 'asc'))->limit(($p - 1) * $number, $number)->select();
        foreach ($pictures as $key => $val) {
            $pictures[$key]['img_url'] = $attachmentModel->getFieldById($val['attachmentid'], 'remote_url');
        }
        $Page = new \Think\Page($count, $number); //
        $show = $Page->show(); // 分页显示输出// 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $this->assign('page', $show); // 赋值分页输出
        $forums = R('Forum/listing', array(1, 100));
        $this->assign('forums', $forums);
        $this->assign('pictures', $pictures);
        $this->display('show_picture');
    }

    //图片墙删除
    public function deletePicture()
    {
        $ids     = I('request.ids', null);
        $ids_arr = explode(',', $ids);
        foreach ($ids_arr as $key => $val) {
            $forumPictureModel = D('ForumPicture');
            $forumPictureModel->where(array('id' => $val))->setField(array('status' => 1));
        }
        $info    = null;
        $code    = 0;
        $message = '图片删除成功';
        $return  = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    //爱情故事管理
    public function showStory()
    {
        $number = I('request.number',10,'intval');
        $p = I('request.p',1,'intval');
        $forumStoryModel = D('ForumStory');
        $count = $forumStoryModel->where(array('status'=>0))->count();
        $Page = new \Think\Page($count,$number);
        $page = $Page->show();
        $list = $forumStoryModel->where(array('status'=>0))->limit(($p-1)*$number,$number)->order(array('addtime'=>'desc'))->select();
        $this->assign('page',$page);
        $this->assign('stories',$list);
        $this->display('show_story');
    }

    //爱情故事编辑
    public function editStory(){
        $id = I('request.id',null,'intval');
        $forumStoryModel = D('ForumStory');
        $story = $forumStoryModel->where(array('id'=>$id))->find();
        if(empty($story)){
            $info = 'parameter_invalid';
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
        $this->assign('story',$story);
        $this->display('edit_story');
    }

    //爱情故事编辑
    public function editStoryDo(){
        $id = I('request.id',null,'intval');
        $content = I('request.content',null);
        if(empty($id)||empty($content)){
            $info = 'parameter_invalid';
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
        $forumStoryModel = D('ForumStory');
        $forumStoryModel->where(array('id'=>$id))->setField(array('content'=>$content));
        $this->success('编辑成功');
    }

    public function deleteStory(){
        $id = I('request.id',null,'intval');
        if(empty($id)){
            $info = 'parameter_invalid';
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
        $forumStoryModel = D('ForumStory');
        $forumStoryModel->where(array('id'=>$id))->setField(array('status'=>1));
        $info = null;
        $code = 0;
        $message  = '删除成功';
        $return = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    //一段话管理
    public function showPassage()
    {
        $number = I('request.number',10,'intval');
        $p = I('request.p',1,'intval');
        $forumPassageModel = D('ForumPassage');
        $count = $forumPassageModel->where(array('status'=>0))->count();
        $Page = new \Think\Page($count,$number);
        $page = $Page->show();
        $list = $forumPassageModel->where(array('status'=>0))->limit(($p-1)*$number,$number)->order(array('addtime'=>'desc'))->select();
        $this->assign('page',$page);
        $this->assign('passages',$list);
        $this->display('show_passage');
    }


    //爱情故事编辑
    public function editPassage(){
        $id = I('request.id',null,'intval');
        $forumPassageModel = D('ForumPassage');
        $passage = $forumPassageModel->where(array('id'=>$id))->find();
        if(empty($passage)){
            $info = 'parameter_invalid';
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
        $this->assign('passage',$passage);
        $this->display('edit_passage');
    }

    //爱情故事编辑
    public function editPassageDo(){
        $id = I('request.id',null,'intval');
        $content = I('request.content',null);
        if(empty($id)||empty($content)){
            $info = 'parameter_invalid';
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
        $forumStoryModel = D('ForumStory');
        $forumStoryModel->where(array('id'=>$id))->setField(array('content'=>$content));
        $this->success('编辑成功');
    }

    public function deletePassage(){
        $id = I('request.id',null,'intval');
        if(empty($id)){
            $info = 'parameter_invalid';
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
        $forumPassageModel = D('ForumPassage');
        $forumPassageModel->where(array('id'=>$id))->setField(array('status'=>1));
        $info = null;
        $code = 0;
        $message  = '删除成功';
        $return = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }


    //辩论管理
    public function showDebate()
    {
        $number = I('request.number',10,'intval');
        $p = I('request.p',1,'intval');
        $type = I('request.type',null,'intval');
        if(is_null($type)){
            $info = 'parameter_invalid';
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
        $debateModel = D('Debate');
        $count = $debateModel->where(array('type'=>$type,'status'=>0))->count();
        $Page = new \Think\Page($count,$number);
        $page = $Page->show();
        $list = $debateModel->where(array('type'=>$type,'status'=>0))->limit(($p-1)*$number,$number)->order(array('addtime'=>'desc'))->select();
        $this->assign('page',$page);
        $this->assign('debates',$list);
        $this->display('show_debate');
    }


    //爱情故事编辑
    public function editDebate(){
        $id = I('request.id',null,'intval');
        $debateModel = D('Debate');
        $debate = $debateModel->where(array('id'=>$id))->find();
        if(empty($debate)){
            $info = 'parameter_invalid';
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
        $this->assign('debate',$debate);
        $this->display('edit_debate');
    }

    //爱情故事编辑
    public function editDebateDo(){
        $id = I('request.id',null,'intval');
        $content = I('request.content',null);
        if(empty($id)||empty($content)){
            $info = 'parameter_invalid';
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
        $debateModel = D('Debate');
        $debateModel->where(array('id'=>$id))->setField(array('content'=>$content));
        $this->success('编辑成功');
    }

    public function deleteDebate(){
        $id = I('request.id',null,'intval');
        if(empty($id)){
            $info = 'parameter_invalid';
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
        $debateModel = D('Debate');
        $type = $debateModel->where(array('id'=>$id))->getField('type');
        $debateModel->where(array('id'=>$id))->setField(array('status'=>1));
        $debateTotalModel = D('DebateTotal');
        if($type==0){
            $debateTotalModel->where(array('id'=>1))->setDec('propositions');
        }else{
            $debateTotalModel->where(array('id'=>1))->setDec('oppositions'); 
        }
       
        $info = null;
        $code = 0;
        $message  = '删除成功';
        $return = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }


    //资讯管理
    public function showNews()
    {
        $fid    = I('request.fid', 16, 'intval');
        $number = I('request.number',10,'intval');
        $p = I('request.p',1,'intval');
        $forumNewsModel = D('ForumNews');
        $count = $forumNewsModel->where(array('status'=>0,'fid'=>$fid))->count();
        $Page = new \Think\Page($count,$number);
        $page = $Page->show();
        $list = $forumNewsModel->where(array('status'=>0,'fid'=>$fid))->limit(($p-1)*$number,$number)->order(array('datetime'=>'desc'))->select();
        $this->assign('page',$page);
        $this->assign('newses',$list);
        $forums = R('Forum/listing', array(1, 100));
        $this->assign('forums', $forums);
        $this->display('show_news');
    }

    //添加新闻
    public function addNews(){
        $forums = R('Forum/listing', array(1, 100));
        $this->assign('forums', $forums);
        $this->display('add_news');
    }

    public function addNewsDo(){
        $source  =I('request.source',null);
        $fid = I('request.fid',null);
        $subject = I('request.subject',null);
        $link = I('request.link',null);
        $datetime = I('request.datetime',null);

        if(empty($source)||empty($subject)||empty($datetime)||empty($link)){
            $this->error('请填写各个选项');
        }

        
        $data['source'] = $source;
        $data['fid'] = $fid;
        $data['subject'] = $subject;
        $data['link'] = $link;
        $data['datetime'] = strtotime($datetime);

        $forumNewsModel = D('ForumNews');
        $forumNewsModel->where(array('id'=>$id))->add($data);
        $this->success('添加成功');

    }

    //资讯编辑
    public function editNews(){
        $id = I('request.id',null,'intval');
        $forumNewsModel = D('ForumNews');
        $news = $forumNewsModel->where(array('id'=>$id))->find();
        if(empty($news)){
            $info = 'parameter_invalid';
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
        $forums = R('Forum/listing', array(1, 100));
        $this->assign('forums', $forums);
        $this->assign('news',$news);
        $this->display('edit_news');
    }

    //资讯编辑
    public function editNewsDo(){
        $id = I('request.id',null,'intval');
        $source  =I('request.source',null);
        $fid  =I('request.fid',null);
        $subject = I('request.subject',null);
        $datetime = I('request.datetime',null);
        if(empty($source)||empty($subject)||empty($datetime)){
            $this->error('请填写各个选项');
        }
        $data['source'] = $source;
        $data['fid'] = $fid;
        $data['subject'] = $subject;
        $data['datetime'] = strtotime($datetime);

        $forumNewsModel = D('ForumNews');
        $forumNewsModel->where(array('id'=>$id))->save($data);
        $this->success('编辑成功');
    }

    public function deleteNews(){
        $id = I('request.id',null,'intval');
        if(empty($id)){
            $info = 'parameter_invalid';
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
        $forumNewsModel = D('ForumNews');
        
        $forumNewsModel->where(array('id'=>$id))->setField(array('status'=>1));
        
        $info = null;
        $code = 0;
        $message  = '删除成功';
        $return = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }




}

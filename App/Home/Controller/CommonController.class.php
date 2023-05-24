<?php
namespace Home\Controller;

use Think\Controller;

class CommonController extends Controller
{
    private $access = "";
    protected $login = '';
    protected $uid = '';
    protected $fid = '';

    /**
     * @return string
     */
    public function getFid()
    {
        return $this->fid;
    }

    /**
     * @param string $fid
     */
    public function setFid($fid)
    {
        $this->fid = $fid;
    }

    /**
     * @return string
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * @param string $uid
     */
    public function setUid($uid)
    {
        $this->uid = $uid;
    }

    /**
     * @return string
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * @param string $login
     */
    public function setLogin($login)
    {
        $this->login = $login;
    }

    /**
     * @return string
     */
    public function getAccess()
    {
        return $this->access;
    }

    /**
     * @param string $access
     */
    public function setAccess($access)
    {
        $this->access = $access;
    }

    //构建ajax返回数据
    public function buildReturn($info, $code, $message)
    {
        return array(
            'data' => array(
                'code' => $code,
                'message' => $message,
                'info' => $info,
            ),
        );
    }

    public function __construct()
    {
        parent::__construct();
        $this->setTheme();
        $cookie_uid = cookie('uid');
        $cookie_password = cookie('uid_info');
        $uid = null;

        if (!empty($cookie_uid)) {
            $UserModel = D('User');
            $password = $UserModel->where(array('uid' => $cookie_uid))->getFieldById($cookie_uid, 'password');
            $encryption_password = md5(md5($password) . 'yingplus');
            if ($encryption_password == $cookie_password) {
                $uid = $cookie_uid;
                session('uid', $uid);
            } else {
                $uid = session('uid');
            }
        } else {
            $uid = session('uid');
        }

        //sso同步
        $sso = I('request.sso', null);
        if ($sso == 'login') {
            $userModel = D('User');
            $password = $userModel->getFieldById($uid, 'password');
            $uid_info = md5(md5($password) . 'yingplus');
            $this->assign('uid', $uid);
            $this->assign('uid_info', $uid_info);
        }

        $fid = I('request.fid', null, 'intval');
        $tid = I('request.tid', null, 'intval');
        $aid = I('request.aid', null, 'intval');

        if (is_null($fid)) {
            if (!is_null($tid)) {
                $fid = $this->getFidByTid($tid);
            } elseif (!is_null($aid)) {
                $fid = $this->getFidByAid($aid);
            }
        }

        $this->setUid($uid);
        $this->setFid($fid);
        $this->assign('fid', $fid);

        if (empty($uid)) {
            $this->setLogin(false);
            $this->assign('is_login', 'false');
        } else {
            $this->setLogin(true);
            $this->assign('is_login', 'true');
        }

        if ($this->blockUser()) {
            die;
        }

        //如果用户登录，将用户权限等级赋值到session中
        if ($this->login) {
            $forumUserModel = D('ForumUser');
            if (!empty($fid)) {
                $forum_access = $forumUserModel->getAccessInfo($fid, $uid);
                session('user_access', $forum_access);
            }

            $uid = $this->getUid();
            $admin_fid = $this->getAdminFidByUid($uid);
            session('admin_fid', $admin_fid);
        }

        //分享链接
        $uid = session('uid');
        $encode_uid = base64_encode(base64_encode($uid));

        $share_url = $_SERVER['HTTP_HOST'] . "?share_id=" . $encode_uid;
        $this->assign('share_url', $share_url);
    }

    //判断是否是超级管理员
    public function IsSuperAdmin()
    {
        $adminModel = D('Admin');
        $admin_id = session('user_id');
        $result = $adminModel->where(array('id' => $admin_id))->find();
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    //阻挡非法用户
    public function blockUser()
    {
        $block = D('Block');

        if ($this->checkLogin()) {
            $uid = $this->getUid();
            $result = $block->where(array('uid' => $uid))->find();
            if ($result) {
                return true;
            } else {
                return false;
            }
        } else {
            $ip = get_client_ip(0, true);
            $result = $block->where(array('ip' => $ip))->find();
            if ($result) {
                return true;
            } else {
                return false;
            }
        }
    }

    //短网址
    public function dwz($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://dwz.cn/create.php");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data = array('url' => $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $strRes = curl_exec($ch);
        curl_close($ch);

        $arrResponse = json_decode($strRes);

        if ($arrResponse->status == 0) {
            return $arrResponse->tinyurl;
        } else {
            return null;
        }
    }

    /**
     * 用户通过分享链接注册处理
     */
    public function shareRegisterDo()
    {
        $share_id = I('request.share_id', null);
        if (!empty($share_id)) {
            $share_user_id = base64_decode(base64_decode($share_id));
            $userModel = D('User');
            $result = $userModel->where(array('id' => $share_user_id))->find();
            if ($result) {
                //更新用户邀请人数
                $point = R('Point/sharePoint', array($share_user_id));
                if ($point) {
                    R('Point/addUserTotalPoint', array($point));
                }
                $userProfileModel = D('UserProfile');
                $userProfileModel->where(array('uid' => $share_user_id))->setInc('invites');
            }
        }
    }

    //活动状态修改
    public function updateActivityStatus($aid)
    {

        $now = time();

        if (!is_null($aid)) {
            $activityModel = D('Activity');
            $activity = $activityModel->getActivityInfoById($aid);
            if (in_array($activity['status'], array('0', '2', '3', '4', '5'))) {
                if ($now < $activity['encrollstartime']) {
                    $activityModel->where(array('id' => $aid))->setField(array('status' => 2));
                } elseif (($now > $activity['encrollstartime']) && ($now < $activity['holdstart'])) {
                    $activityModel->where(array('id' => $aid))->setField(array('status' => 3));
                } elseif (($now > $activity['holdstart']) && ($now < $activity['holdend'])) {
                    $activityModel->where(array('id' => $aid))->setField(array('status' => 4));
                } elseif ($now > $activity['holdend']) {
                    $activityModel->where(array('id' => $aid))->setField(array('status' => 5));
                }
            }
        }
    }

    //获取用户管理的工作室
    public function getAdminFidByUid($uid)
    {
        $forumUserModel = D('ForumUser');
        $forum = $forumUserModel->getAdminFidByUid($uid);
        return $forum['fid'];
    }

    //cookie密码加密
    public function encryptionUserInfo($uid, $password)
    {
        cookie('uid', $uid, 3600 * 24 * 7); //7天;
        cookie('uid_info', md5(md5($password) . 'yingplus'), 3600 * 24 * 7); //密码加密处理;
    }

    /**
     * 检查用户是否有权限访问
     */
    public function checkPrivilege($accessName)
    {
        $forum_access = session('user_access');

        if (is_null($forum_access)) {
            return false;
        }

        if ($forum_access['isadmin'] == 1) {
            return true;
        }

        foreach ($forum_access as $key => $val) {
            if ($accessName == $key) {
                if ($val == 1) {
                    return true;
                    break;
                } else {
                    return false;
                    break;
                }
            }
        }

        return true;
    }

    //用户封禁时间转换
    public function BanUserTimeFormat($time)
    {
        switch ($time) {
            case '3600':
                return '您被封禁一小时';
                break;
            case '86400':
                return '您被封禁一天';
                break;
            case '259200':
                return '您被封禁三天';
                break;
            default:
                return "";
                break;
        }
    }

    /**
     * 电影票房
     * @param string $id [http://www.cbooo.cn/m/ url中传递的id]
     * @return string [电影票房]
     **/
    public function BoxOffice($id)
    {
        $url = "http://www.cbooo.cn/m/" . $id;
        $params = array();
        $paramstring = httpdai_build_query($params);
        $content = $this->juhecurl($url, $paramstring);
        preg_match('#<span class="m-span">累计票房<br />([\d.]+)万</span>#', $content, $match);

        if (empty($match)) {
            return 0;
        } else {
            return intval($match[1]);
        }
    }

    /**
     * 聚合请求接口返回内容
     * @param  string $url [请求的URL地址]
     * @param  string $params [请求的参数]
     * @param  int $ipost [是否采用POST形式]
     * @return  string
     */
    public function juhecurl($url, $params = false, $ispost = 0)
    {
        $httpInfo = array();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'JuheData');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        if ($ispost) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch, CURLOPT_URL, $url);
        } else {
            if ($params) {
                curl_setopt($ch, CURLOPT_URL, $url . '?' . $params);
            } else {
                curl_setopt($ch, CURLOPT_URL, $url);
            }
        }
        $response = curl_exec($ch);
        if ($response === false) {
            //echo "cURL Error: " . curl_error($ch);
            return false;
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $httpInfo = array_merge($httpInfo, curl_getinfo($ch));
        curl_close($ch);
        return $response;
    }

    public function setTheme($theme = "")
    {
        empty($theme) ? $this->theme('default') : $this->theme($theme);
    }

    /**
     * 验证用户是否登录
     * @return bool
     */
    public function checkLogin()
    {
        if (session('uid')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 处理登录框，可以是弹出登录框，或者跳转到登录页面
     */
    public function showLogin()
    {
        $this->redirect('Index/index');
    }

    public function getHotActivities($fid)
    {
        //TODO:依据算法获取最热门的活动
        $activityModel = D('Activity');
        $hot_activities = $activityModel->getHotPromoteByFid($fid);
        return $hot_activities;
    }

    public function getHotTopics($fid)
    {
        //TODO: 依据算法获取最热门的话题
        $topicModel = D('Topic');
        $hot_topics = $topicModel->getHotPromoteByFid($fid);
        return $hot_topics;
    }

    /**
     *  @param $aid 活动id
     *  @return $fid 星吧id
     */
    public function getFidByAid($aid)
    {
        $activityModel = D('Activity');
        $fid = $activityModel->getFieldById($aid, 'fid');
        return $fid;
    }

    public function getFidByTid($tid)
    {
        $topicModel = D('Topic');
        $fid = $topicModel->getFieldById($tid, 'fid');
        return $fid;
    }

    public function getUserForum($uid)
    {
        $forumUserModel = D('ForumUser');
        $forums = $forumUserModel->getForumsByUid($uid);
        $ids = array();
        foreach ($forums as $key => $val) {
            array_push($ids, $val['fid']);
        }
        return $ids;
    }

    public function checkInForum($fid, $forums)
    {
        foreach ($forums as $val) {
            if ($fid == $val) {
                return true;
            }
        }
        return false;
    }
}

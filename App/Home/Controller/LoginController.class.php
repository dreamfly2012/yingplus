<?php

namespace Home\Controller;

use Think\Controller;

class LoginController extends Controller
{

    protected function log($content)
    {
        file_put_contents("log.txt", date("Y-m-d H:i:s") . $content . PHP_EOL, FILE_APPEND);
    }

    //登录
    public function login()
    {
        //进行普通登录
        $User = D('User');
        $telephone = I('request.telephone', null);
        $password = I('request.password', null, 'md5');
        $remember = I('request.remember', null);

        $uid = $User->loginByTelephone($telephone, $password);
        if (empty($uid)) {
            $info = null;
            $code = 1;
            $message = C('ERROR_PASSWORD_TELEPHONE');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        session('uid', $uid);
        if ($remember == 1) {
            $this->encryptionUserInfo($uid, md5($password));
        }
        $time = time();
        $ip = get_client_ip(0, true);
        $User->where(array('id' => $uid))->setField(array('lastlogintime' => $time, 'lastloginip' => $ip));
        $info = null;
        $code = 0;
        $message = C('LOGIN_SUCCESS');
        $return = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    //注册
    public function register()
    {
        $telephone = I('request.telephone', null);
        $password = I('request.password', null);
        $captcha = I('request.captcha', null);
        $session_captcha = session('captcha');
        $userModel = D('User');
        $userProfileModel = D('UserProfile');
        //参数错误
        if (empty($telephone) || empty($password) || empty($captcha)) {
            $info = null;
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        if (strlen($telephone) != 11) {
            $info = null;
            $code = 1;
            $message = '手机号格式不正确';
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        if (strlen($password) < 6 || strlen($password) > 14) {
            $info = null;
            $code = 1;
            $message = '密码位数应该在6-14位';
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $password = md5($password);

        //验证码错误
        if ($captcha != $session_captcha) {
            $info = null;
            $code = -2;
            $message = C('REGISTER_CAPTCHA_ERROR');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        //用户已经存在
        if ($userModel->checkExistPhone($telephone)) {
            $info = null;
            $code = -3;
            $message = C('TELEPHONE_EXIST');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $userModel->regtime = time();
        $userModel->regip = get_client_ip(0, true);
        $userModel->lastlogintime = time();

        $userModel->lastloginip = get_client_ip(0, true);
        $userModel->telephone = $telephone;
        $userModel->password = $password;
        //在这需要分配一个昵称
        $userModel->nickname = $this->getNickNameFromSystem();
        $id = $userModel->add();

        //说明用户注册成功，将用户字段进行填充
        $userProfileModel->add(array('uid' => $id));
        session('uid', $id);
        $info = null;
        $code = 0;
        $message = C('REGISTER_SUCCESS');
        $return = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    //忘记密码
    public function forgetpassword()
    {
        $telephone = I('request.telephone', null);
        $session_captcha = session('captcha');
        $captcha = I('request.captcha', null, '');
        $password = I('request.password', null, '');

        //参数错误
        if (empty($telephone) || empty($captcha) || empty($password)) {
            $info = null;
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        //电话号码格式不正确
        if (strlen($telephone) != 11) {
            $info = null;
            $code = 1;
            $message = '手机号格式不正确';
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        //密码格式不正确
        if (strlen($password) < 6 || strlen($password) > 14) {
            $info = null;
            $code = 1;
            $message = '密码位数应该在6-14位';
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $password = md5($password);

        //验证码是否正确
        if ($captcha != $session_captcha) {
            $info = null;
            $code = -2;
            $message = '验证码错误';
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        //电话不存在
        $User = D('User');
        if (!$User->checkExistPhone($telephone)) {
            $info = null;
            $code = -3;
            $message = '手机号不存在';
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $User->where(array('telephone' => $telephone))->setField(array('password' => $password));
        $uid = $User->getFieldByTelephone($telephone, 'id');
        session('uid', $uid);
        $info = null;
        $code = 0;
        $message = 'success';
        $return = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    //修改密码
    public function changePasswordDo()
    {
        $oldpassword = I('oldpassword', null, 'md5');
        $newpassword = I('newpassword', null, 'md5');
        $confirmpassword = I('confirmpassword', null, 'md5');
        $uid = $this->getUid();
        if (is_null($oldpassword) || is_null($confirmpassword)) {
            $this->ajaxReturn(array('status' => 0, 'content' => C('INFO_REQUIRED')));
        } else {
            $userModel = D('User');
            $result = $userModel->where(array('id' => $uid, 'password' => $oldpassword))->find();
            if ($result) {
                $userModel->where(array('id' => $uid))->setField(array('password' => $confirmpassword));
                $this->ajaxReturn(array('status' => 1, 'content' => 'success'));
            } else {
                $this->ajaxReturan(array('status' => 2, 'content' => C('ERROR_OLD_PASSWORD')));
            }
        }
    }

    //从系统得到一个唯一昵称
    public function getNickNameFromSystem()
    {
        $NickName = D('Nickname');
        $nickname = $NickName->getNickName();
        return $nickname;
    }

    /**
     * Name:loginByCaptcha
     * Describe:使用手机号通过验证码进行动态登录
     * Return:
     */
    public function loginByCaptcha()
    {
        //TODO: 进行动态登录
        $User = D('User');
        $telephone = I('telephone', null, '');
        $session_captcha = session('captcha');
        $post_captcha = I('captcha', null, '');
    }

    //QQ进行第三方登录
    public function loginByQQ()
    {
        $UserModel = D('User');
        $UserProfile = D('UserProfile');
        $code = I('get.code', null, ''); //得到链接中的code值
        $backurl = null;
        if (isMobile()) {
            $backurl = I('get.backurl', null, 'base64_decode'); //返回url
        } else {
            $backurl = I('get.backurl', null); //返回url
        }
        $QQLogin = new QQLoginController($code, C('QQ_CLIENT_ID'), C('QQ_CLIENT_SECRET'), C('QQ_REDIRECT_URL'));
        $userInfo = $QQLogin->getUserInfo();
        $userArr = $this->getUserArrayByQqInfo($userInfo);
        $this->log(json_encode($userArr));
        //查找该用户是否已经注册
        $uid = $UserModel->getUserIdByQqId($userArr['qqid']);

        if (!empty($uid)) {
            //说明该用户已经注册，将uid保存到session中
            session('uid', $uid);

            if (empty($backurl)) {
                if (isMobile()) {
                    $this->redirect('Mobile/index', array('sso' => 'login'));
                } else {
                    $this->redirect('Pc/index', array('sso' => 'login'));
                }
            } else {
                $index = stripos($backurl, '?');
                if ($index) {
                    $backurl = substr($backurl, 0, $index);
                }

                $password = $UserModel->getFieldById($uid, 'password');
                $uid_info = md5(md5($password) . 'yingplus');
                header('Location:' . $backurl . '?sso=login&uid=' . $uid . '&uid_info=' . $uid_info);
                die;
            }
        }

        if (!empty($userArr['qqid'])) {
            if (!empty($uid)) {
                //第三方绑定
                $uid = session('uid');
                $data['qq'] = $userArr['qqid'];
                $UserModel->where(array('id' => $uid))->save($data);

                //绑定跳转TODO:
                if (isMobile()) {
                    $this->redirect('Mobile/index');
                } else {
                    $this->redirect('Pc/index');
                }
            }

            //说明没有此用户，进行保存
            $nickname = $this->disposeUserNickName($userArr['nickname']);
            //将user表信息进行分别保存
            $this->log($nickname);
            $user_info = array(
                'nickname' => $nickname,
                'qq' => $userArr['qqid'],
                'regtime' => $userArr['regtime'],
                'lastlogintime' => $userArr['lastlogintime'],
                'regip' => $userArr['regip'],
                'lastloginip' => $userArr['lastloginip'],
                'status' => C('USER_NORMAL_STATUS'),
            );
            $uid = $UserModel->add($user_info);
            $this->log( $backurl);
            if ($uid > 0) {
                session('uid', $uid);
            }else{
                $this->log( "保存session失败");
            }
            $this->log( $uid);
            //将userProfile表信息进行保存
            $userProfile_info = array(
                'uid' => $uid,
                'photo' => $userArr['photo'],
                'gender' => $userArr['gender'],
            );
            $UserProfile->save($userProfile_info);
            $this->log( $backurl);
            if (empty($backurl)) {
                if (isMobile()) {
                    $this->redirect('Mobile/index', array('sso' => 'login'));
                } else {
                    $this->redirect('Pc/index', array('sso' => 'login'));
                }
            } else {
                $index = stripos($backurl, '?');
                if ($index) {
                    $backurl = substr($backurl, 0, $index);
                }

                $password = $UserModel->getFieldById($uid, 'password');
                $uid_info = md5(md5($password) . 'yingplus');
                header('Location:' . $backurl . '?sso=login&uid=' . $uid . '&uid_info=' . $uid_info);
            }
        }

        $this->log('没有获取到用户qq信息');

        if (isMobile()) {
            $this->redirect('Mobile/index', array('sso' => 'login'));
        } else {
            $this->redirect('Pc/index', array('sso' => 'login'));
        }
    }

    //weibo进行第三方登录
    public function loginByWeibo()
    {
        $UserModel = D('User');
        $UserProfile = D('UserProfile');
        $backurl = I('request.backurl'); //返回url
        $code = I('get.code', null, ''); //得到链接中的code值

        $weiboLogin = new WeiboLoginController($code, C('SINA_CLIENT_ID'), C('SINA_CLIENT_SECRET'), C('SINA_REDIRECT_URL'));

        $userInfo = $weiboLogin->getUserInfo();
        $userArr = $this->getUserArrayByWeiboInfo($userInfo);

        //查找该用户是否已经注册
        $uid = $UserModel->getUserIdByWeiboId($userArr['weiboid']);

        session('weibo_login', $code);
        if (!empty($uid)) {
            //说明该用户已经注册，将uid保存到session中
            session('uid', $uid);
            if (empty($backurl)) {
                $this->redirect('Pc/index', array('sso' => 'login'));
            } else {
                $index = stripos($backurl, '?');
                if ($index) {
                    $backurl = substr($backurl, 0, $index);
                }
                $password = $UserModel->getFieldById($uid, 'password');
                $uid_info = md5(md5($password) . 'yingplus');
                $access_token = session('access_token');
                header('Location: ' . $backurl . '?sso=login&uid=' . $uid . '&uid_info=' . $uid_info . '&code=' . $code . '&access_token=' . $access_token);
                die;
            }
        }

        if (!empty($userArr['weiboid'])) {
            if (!empty($uid)) {
                //第三方绑定
                $uid = session('uid');
                $data['weibo'] = $userArr['weiboid'];
                $UserModel->where(array('id' => $uid))->save($data);

                //绑定跳转
                $this->redirect('Pc/index');
            }

            //说明没有此用户，进行保存
            $nickname = $this->disposeUserNickName($userArr['nickname']);
            //将user表信息进行分别保存
            //user表：nickname、weiboid、regtime、lastlogintime、onlinetime、regip、lastloginip、status
            $user_info = array(
                'nickname' => $nickname,
                'weibo' => $userArr['weiboid'],
                'regtime' => $userArr['regtime'],
                'lastlogintime' => $userArr['lastlogintime'],
                'regip' => $userArr['regip'],
                'lastloginip' => $userArr['lastloginip'],
                'status' => C('USER_NORMAL_STATUS'),
            );

            $uid = $UserModel->add($user_info);
            if ($uid > 0) {
                session('uid', $uid);
                $userProfile_info = array(
                    'uid' => $uid,
                    'photo' => $userArr['photo'],
                    'gender' => $userArr['gender'],
                );
                $UserProfile->add($userProfile_info);
            }

            if (empty($backurl)) {
                $this->redirect('Pc/index', array('sso' => 'login'));
            } else {
                $index = stripos($backurl, '?');
                if ($index) {
                    $backurl = substr($backurl, 0, $index);
                }

                $password = $UserModel->getFieldById($uid, 'password');
                $uid_info = md5(md5($password) . 'yingplus');
                header('Location: ' . $backurl . '?sso=login&uid=' . $uid . '&uid_info=' . $uid_info . '&code=' . $code . '&access_token=' . $access_token);
                die;
            }
        }

        $this->redirect('Pc/index');
    }

    /**
     * Name:getUserArrayByQqInfo
     * Describe:通过QQ得到的用户信息对用户信息进行封装，变成数组
     * Param:$userInfo 用户的信息
     * Return:封装有用户信息的数组
     */
    public function getUserArrayByQqInfo($userInfo)
    {
        //对用户的信息进行处理
        $nickname = $userInfo['nickname']; //用户的昵称
        $qqid = $userInfo['openId']; //第三方登录得不到qq号，openId作为qq号的唯一标识
        $gender = 0; //用户的性别，默认是0 未知
        if ($userInfo->gender == "男") {
            $gender = 1;
        } elseif ($userInfo->gender == "女") {
            $gender = 2;
        } else {
            $gender = 0;
        }
        $photo = $userInfo['figureurl']; //用户的头像地址
        $regTime = strtotime("now"); //用户注册的时间
        $lastLoginTime = strtotime("now"); //用户最后登录的时间
        $regIp = get_client_ip(); //得到用户注册时的ip
        $lastLoginIp = get_client_ip(); //得到用户最后登录时的ip
        return array(
            'nickname' => $nickname,
            'qqid' => $qqid,
            'regtime' => $regTime,
            'lastlogintime' => $lastLoginTime,
            'gender' => $gender,
            'photo' => $photo,
            'regip' => $regIp,
            'lastloginip' => $lastLoginIp,
        );
    }

    /**
     * Name:getUserArrayByWeiboInfo
     * Describe:通过微博得到的用户信息对用户信息进行封装，变成数组
     * Param:$userInfo 用户的信息
     * Return:封装有用户信息的数组
     */
    public function getUserArrayByWeiboInfo($userInfo)
    {

        $nickname = $userInfo->screen_name; //用户的昵称
        $weiboid = $userInfo->id; //微博号，由于得不到，只能使用微博的id进行代替
        $gender = 0; //用户的性别
        if ($userInfo->gender == "m") {
            $gender = 1;
        } elseif ($userInfo->gender == "f") {
            $gender = 2;
        } else {
            $gender = 0;
        }
        $photo = $userInfo->profile_image_url; //用户头像地址
        $regTime = strtotime("now"); //用户注册的时间
        $lastLoginTime = strtotime("now"); //用户最后登录的时间
        $regIp = get_client_ip(); //得到用户注册时的ip
        $lastLoginIp = get_client_ip(); //得到用户最后登录时的ip
        return array(
            'nickname' => $nickname,
            'weiboid' => $weiboid,
            'regtime' => $regTime,
            'lastlogintime' => $lastLoginTime,
            'gender' => $gender,
            'photo' => $photo,
            'regip' => $regIp,
            'lastloginip' => $lastLoginIp,
        );
    }

    /**
     * Name:disposeUserNickName
     * Describe:对用户的昵称进行处理
     * Param:$nickname 用户的昵称
     * Return:返回用户的昵称，若昵称已经存在，则在原有基础上加上6位随机数，若不存在则使用原有的昵称
     */
    public function disposeUserNickName($nickname)
    {
        $user = D('User');
        $new_nickname = $nickname;
        //得到用户的昵称，查找昵称是否已经存在
        $id = $user->getUserByNickName($new_nickname);

        if ($id) {
            //说明此昵称已经存在，需要进行调整
            $rand = rand(1111111, 9999999);
            $new_nickname = $new_nickname . $rand;
            $new_nickname = $this->disposeUserNickName($new_nickname);
        }

        return $new_nickname;
    }

    //退出登录
    public function logout()
    {
        session_destroy();
        session('uid', null);
        cookie('uid', null);
        cookie('uid_info', null);
        header('location:' . getenv("HTTP_REFERER"));
    }

    //验证手机号是否存在，验证这个手机号是否被注册
    public function checktelephone()
    {
        $telephone = I('request.telephone', null);
        if (empty($telephone)) {
            $info = null;
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }
        $userModel = D('User');
        $result = $userModel->where(array('telephone' => $telephone))->find();

        if (empty($result)) {
            $info = false;
            $code = 0;
            $message = '电话号码不存在';
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $info = true;
        $code = 0;
        $message = '电话号码已存在';
        $return = $this->buildReturn($info, $code, $message);
        $this->ajaxReturn($return);
    }

    //cookie密码加密
    public function encryptionUserInfo($uid, $password)
    {
        cookie('uid', $uid, 3600 * 24 * 7); //7天;
        cookie('uid_info', md5(md5($password) . 'yingplus'), 3600 * 24 * 7); //密码加密处理;
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
}

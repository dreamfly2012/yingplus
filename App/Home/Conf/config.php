<?php

require 'status.code.config.php';

$db = array(
    'DB_TYPE' => 'mysql', // 数据库类型
    'DB_HOST' => '127.0.0.1', // 服务器地址
    'DB_NAME' => 'yingplus', // 数据库名
    'DB_USER' => 'yingplus', // 用户名
    'DB_PWD' => '4cc7c7c4db', // 密码
    'DB_PORT' => 3306, // 端口
    'DB_PREFIX' => 'yj_', // 数据库表前缀
    'DB_CHARSET' => 'utf8', // 字符集
    'DB_DEBUG' => true, // 数据库调试模式 开启后可以记录SQL日志 3.2.3新增
    'DATA_CACHE_TYPE' => 'Redis', //默认动态缓存为Redis
    'REDIS_RW_SEPARATE' => true, //Redis读写分离 true 开启
    'REDIS_HOST' => '127.0.0.1', //redis服务器ip，多台用逗号隔开；读写分离开启时，第一台负责写，其它[随机]负责读；
    'REDIS_PORT' => '6379', //端口号
    'REDIS_TIMEOUT' => '300', //超时时间
    'REDIS_PERSISTENT' => false, //是否长连接 false=短连接
    'REDIS_AUTH' => '', //AUTH认证密码
);
$template = array(
    'TMPL_L_DELIM' => '<{', // 模板引擎普通标签开始标记
    'TMPL_R_DELIM' => '}>', // 模板引擎普通标签结束标记
    'TAGLIB_BEGIN' => '<{', // 标签库标签开始标记
    'TAGLIB_END' => '}>', // 标签库标签结束标记
    'TAG_NESTED_LEVEL' => 5,
    'HTML_FILE_SUFFIX' => '.html',
);

// $cache = array(
//     'HTML_CACHE_ON'=>true, // 开启静态缓存
//     'HTML_FILE_SUFFIX'  =>  '.shtml', // 设置静态缓存后缀为.shtml
//     'HTML_CACHE_RULES'=> array(
//         'topic'            => array('{tid}', '60', ''),
//         'activity'            => array('{aid}', '60', ''),
//         'forum' => array('{fid}', '60', ''),
//         '*'=>array('{$_SERVER.REQUEST_URI|md5}'),
//         //…更多操作的静态规则
//     )
// );
$debug = array(
    //'SHOW_PAGE_TRACE'=>'true',
    'URL_MODEL' => 2,
);

$show = array(
    'ACTIVITY_PAGE_PER_NUM' => 5,
    'TOPIC_PAGE_PER_NUM' => 5,
    'NO_AUTH' => '你没有权限访问',
    'NO_LOGIN' => '没有登陆',
    'SHOW_TIME_INTERVAL' => 600,
    'ERROR_PASSWORD_TELEPHONE' => '手机号或者密码错误',
    'LOGIN_SUCCESS' => '登陆成功',
    'LOGIN_CAPTCHA_CODE_ERROR' => '验证码错误',
    'ERROR_OLD_PASSWORD' => '原密码错误',
    'REGISTER_SUCCESS' => '注册成功',
    'REGISTER_FAILED' => '注册失败',
    'REGISTER_CAPTCHA_ERROR' => '验证码错误',
    'FORGET_PASSWORD_CAPTCHA_ERROR' => '验证码错误',
    'TELEPHONE_EXIST' => '电话号码已经存在',
    'FORGET_PASSWORD_TELEPHONE_EMPTY' => '电话号码不能为空',
    'NO_EXIST_TELEPHONE' => '电话号码不存在',

    'REGISTER_TELEPHONE_EXIST' => '电话号码已经被注册',
    'REGISTER_CAPTCHA_ALREADY_SEND' => '验证码已发送',
    'REGISTER_CAPTCHA_SEND_FAILED' => '验证码发送失败',

    'LOSTPASSWORD_CAPTCHA_ALREADY_SEND' => '验证码已发送',
    'LOSTPASSWORD_CAPTCHA_SEND_FAILED' => '验证码发送失败',
    'LOST_TELEPHONE_NOT_EXIST' => '电话号码不存在',

    'INDEX_HOT_ACTIVITY_COUNT' => 5,
    'INDEX_TOPIC_HOT_VALUE' => 2,
    'INDEX_FORUM_PROMOTE_NUM' => 3,
    'INDEX_FORUM_NUM' => 3,
    'INDEX_HOT_TOPIC_COUNT' => 5,
    'MESSAGE_SHOW_COUNT' => 5, //消息页面显示条数
    'PERSON_CENTER_PAGE_NUM' => 8, //个人主页分页数
    'INFO_REQUIRED' => '请填写必要字段',
    'BAN_CREATE' => '禁止创建活动和话题',
    'BAN_PARTICIPATE' => '禁止参与活动和话题',
    'BIND_TELEPHONE' => '请绑定手机号',
    'ZHIBO_NEED_IMG' => '直播发布需要有图片',

);
$pay = array(

    //支付宝配置参数
    'alipay_config' => array(
        'partner' => '2088121759579303', //这里是你在成功申请支付宝接口后获取到的PID；
        'key' => '5y85m4hu6xo0llvs4y59jbs5v9g7a1d9', //这里是你在成功申请支付宝接口后获取到的Key
        'sign_type' => strtoupper('MD5'),
        'input_charset' => strtolower('utf-8'),
        'cacert' => getcwd() . '\\cacert.pem',
        'transport' => 'http',
    ),
    //以上配置项，是从接口包中alipay.config.php 文件中复制过来，进行配置；

    'alipay' => array(
        //这里是卖家的支付宝账号，也就是你申请接口时注册的支付宝账号
        'seller_email' => 'yingplus@163.com',
        //这里是异步通知页面url，提交到项目的Pay控制器的notifyurl方法；
        'notify_url' => 'http://yingplus.80shihua.com/index.php/home/Pay/notifyurl',
        //这里是页面跳转通知url，提交到项目的Pay控制器的returnurl方法；
        'return_url' => 'http://yingplus.80shihua.com/index.php/home/Pay/returnurl',
        //支付成功跳转到的页面，我这里跳转到项目的User控制器，myorder方法，并传参payed（已支付列表）
        'successpage' => 'http://yingplus.80shihua.com/index.php/home/Pay/msgSuccess',
        //支付失败跳转到的页面，我这里跳转到项目的User控制器，myorder方法，并传参unpay（未支付列表）
        'errorpage' => 'http://yingplus.80shihua.com/index.php/home/Pay/msgError',
    ),

    'wxpay' => array(
        'wx_notify_url' => 'http://yingplus.80shihua.com/index.php/home/Pay/wxnotifyurl',
    ),

);

$forum = array(
    'SUCCESS_REPORT_ADMIN' => '成功举报',
    'ERROR_REPORT_ADMIN' => '举报失败',
    'COMPLAIN_SUCCESS' => '申述已发送，请耐心等待',
    'SUCCESS_SUBMIT_FORUM' => '我们会在一个工作日内反馈您的信息，谢谢您的支持',
    'FORUM_ADMIN_CAN_NOT_CANCEL_FOLLOW' => '经纪人不能取消关注',
    'FAILED_SUBMIT_FORUM' => '提交工作室失败',
    'SEARCH_NO_FORUM' => '未找到该影星的工作室',
    'SEARCH_CREATE_FORUM' => '创建影星工作室',
    'SEARCH_CREATE_NEW_FORUM' => '创建新的工作室',
    'NO_AUTH_ACCESS' => '非法访问',
    'FORUM_INDEX_PAGE_COUNT' => 10,
    'NICKNAME_EXIST' => '昵称已经存在',
    'SETTING_UPDATE_SUCCESS' => '用户设置修改成功',
    'SETTING_UPDATE_FAILED' => '用户设置修改失败',
    'FORUM_FOLLOW_COUNT' => 3,
    'FOLLOW_FORUM_SUCCESS' => '关注工作室成功',
    'CANCEL_FOLLOW_FORUM_SUCCESS' => '取消关注工作室成功',
    'FOLLOW_FORUM_FAILED' => '关注工作室失败',
    'CANCEL_FOLLOW_FORUM_FAILED' => '取消关注工作室失败',
    'FOLLOW_FORUM_EXIST' => '已经关注工作室',
    'FOLLOW_FORUM_OVERFLOW' => '关注工作室超过限制',
    'FORUM_SIGN_ALREADY' => '已经签到过',
    'SIGN_IS_EXIST_FORUM' => '请先加入工作室',
    'FORUM_FOLLOW_FIRST' => '请先加入工作室',
    'NO_FOLLOW_FORUM' => '没有关注工作室',
    'HAS_JOIN_FORUM' => '已经加入过一个工作室',
    'HAS_POST_FORUM' => '您已经提交申请，请耐心等待审核结果',
    'SIGN_FORUM_SUCCESS' => '签到成功',
    'SIGN_FORUM_FAILED' => '签到失败',
    'SIGN_FORUM_EXIST' => '已经签到',
    'UN_LOGIN' => '用户未登录',
    'FORUM_DISPLAY_NAME' => '工作室',
    'DELETE_RESPONSE_SUCCESS' => '删除回复成功',
    'DELETE_RESPONSE_FAILED' => '删除回复失败',
    'RESPONSE_CANNOT_EMPTY' => '回复不能为空',
    'REALNAME_IS_INVALID' => '真实姓名非法！',
    'CAN_NOT_EMPTY' => '不能为空',
    'CLEAR_NEWS_NOTICE_SUCCESS' => '清空消息成功',

);

$activity = array(
    'ADMIN_PROMOTE_NUM' => 4,
    'ACTIVITY_DIGEST_NUM' => 2, //加精活动数量
    'ACTIVITY_DIGEST_OVERFLOW' => '加精数量超过限制',
    'ACTIVITY_RECOMMEND_EXIST' => '活动已经设置为推荐',
    'ACTIVITY_RECOMMEND_SUCCESS' => '活动推荐成功',
    'ACTIVITY_RECOMMEND_CANCEL_SUCCESS' => '活动取消推荐成功',
    'ACTIVITY_RECOMMEND_OVERFLOW' => '推荐活动超过限制',
    'ACTIVITY_RECOMMEND_COLLECT_SUCCESS' => '活动收录成功',
    'ACTIVITY_RECOMMEND_CANCEL_COLLECT_SUCCESS' => '活动取消收录成功',
    'ACTIVITY_RECOMMEND_NUM' => 3,
    'HOT_ACTIVITY_BASE' => 2,
    'ACTIVITY_RECOMMEND_PERMIT' => '允许推荐活动',
    'ACTIVITY_RECOMMEND_FAILED' => '推荐活动失败',
    'ACTIVITY_RECOMMEND_EXCEED' => '您已经推荐了3个活动，推荐新的活动前要先撤掉1个活动或者先收录此活动,留待以后推荐',
    'ACTIVITY_CREATE_SUCCESS' => '创建活动成功',
    'ACTIVITY_CREATE_FAILED' => '创建活动失败',
    'ACTIVITY_SAVEDRAFT_SUCCESS' => '保存草稿成功',
    'ACTIVITY_SAVEDRAFE_FAILED' => '保存草稿失败',
    'ACTIVITY_LIVE_INTERVAL' => 10000,
    'CANCEL_ACTIVITY_ENROLL_FAILED' => '取消活动报名失败',
    'CANCEL_ACTIVITY_ENROLL_SUCCESS' => '取消活动报名成功',
    'CANCEL_ACTIVITY_SUCCESS' => '取消活动成功',
    'CANCEL_ACTIVITY_FAILED' => '取消活动失败',
    'COLLECT_ACTIVITY_SUCCESS' => '活动收藏成功',
    'COLLECT_ACTIVITY_FAILED' => '活动收藏失败',
    'UN_COLLECT_ACTIVITY_SUCCESS' => '取消收藏活动成功',
    'UN_COLLECT_ACTIVITY_FAILED' => '取消收藏活动失败',
    'ACTIVITY_DIGEST_SUCCESS' => '活动加精成功',
    'ACTIVITY_DIGEST_FAILED' => '活动加精失败',
    'ACTIVITY_UNDIGEST_SUCCESS' => '活动取消加精成功',
    'ACTIVITY_UNDIGEST_FAILED' => '活动取消加精失败',
    'ACTIVITY_CANCEL_FAVOR_SUCCESS' => '活动取消点赞成功',
    'ACTIVITY_FAVOR_SUCCESS' => '活动点赞成功',
    'ACTIVITY_FAVOR_FAILED' => '活动点赞失败',
    'ACTIVITY_LIST_NUM' => 6,
    'JOIN' => '您的请求已发送，请耐心等待审核',
    'DELETE_ACTIVITY_SUCCESS' => '删除活动成功',
    'DELETE_ACTIVITY_FAILED' => '删除活动失败',
    'ACTIVITY_REPORT_SUCCESS' => '举报活动成功',
    'ACTIVITY_REPORT_FAILED' => '举报活动失败',
    'ACTIVITY_ENROLL_SUCCESS' => '活动报名成功',
    'ACTIVITY_ENROLL_ALREADY' => '已经报过名',
    'PARTICIPATE_ACTIVITY_OVERFLOW' => '超过人数限制',
    'CANCEL_ACIVITY_NOT_ALLOW' => '距离活动开始不到1天，禁止取消活动',

    'PREVIEW_ACTIVITY_RESPONSE_COUNT' => 4,
    'SAVE_DRAFT_INTERVAL' => 1000 * 60 * 10,

);

$upload = array(
    'UPLOAD_AVATAR_SIZE' => 3145728,
    'UPLOAD_AVATAR_EXT' => array('jpg', 'gif', 'png'),
    'UPLOAD_AVATAR_PATH' => '/User/Avatar/',
    'UPLOAD_AVATAR_THUMB_WIDTH' => 252,
    'UPLOAD_AVATAR_THUMB_HEIGHT' => 252,
    'UPLOAD_AVATAR_ERROR' => '上传头像失败',
    'ACTIVITY_UPLOAD_SIZE=>' => 5230000,
    'ACTIVITY_UPLOAD_EXT' => array('jpg', 'gif', 'png'),
    //'ACTIVITY_UPLOAD_PATH'=>'Activity/Img/',
    'ACTIVITY_UPLOAD_WIDTH' => 502,
    'UPLOAD_TEMP_PATH' => 'temp/',
    'ACTIVITY_UPLOAD_HEIGHT' => 472,
    'ACTIVITY_IMG_WIDHT' => 400,
    'ACTIVITY_IMG_HEIGHT' => 400,
);

$topic = array(
    'TOPIC_INTERVAL_TIME_SHOW' => 60 * 10,
    'TOPIC_CANCEL_FAVOR_SUCCESS' => '取消点赞成功',
    'TOPIC_FAVOR_SUCCESS' => '您已经成功点赞',
    'TOPIC_FAVOR_REPEATE' => '您已经点过赞了',
    'TOPIC_FAVOR_FAILED' => '点赞失败',
    'TOPIC_LIVE_INTERVAL' => 10000,
    'DELETE_TOPIC_SUCCESS' => '删除话题成功',
    'DELETE_TOPIC_FAILED' => '删除话题失败',
    'COLLECT_TOPIC_SUCCESS' => '收藏话题成功',
    'COLLECT_TOPIC_FAILED' => '收藏话题失败',
    'UN_COLLECT_TOPIC_SUCCESS' => '取消收藏话题成功',
    'UN_COLLECT_TOPIC_FAILED' => '取消收藏话题失败',
    'CREATE_TOPIC_SUCCESS' => '创建话题成功',
    'CREATE_TOPIC_FAILED' => '创建话题失败',
    'TOPIC_REPORT_SUCCESS' => '话题举报成功',
    'TOPIC_REPORT_FAILED' => '话题举报失败',
    'TOPIC_TOP_SUCCESS' => '话题置顶成功',
    'TOPIC_TOP_FAILED' => '话题置顶失败',
    'UPDATE_UNREAD_MESSAGE_SUCCESS' => '更新未读信息成功',
    'UPDATE_UNREAD_MESSAGE_FAILED' => '更新未读信息失败',
    'TOPIC_RESPONSE_SUCCESS' => '回复话题成功',
    'TOPIC_RESPONSE_FAILED' => '回复话题失败',
    'TOPIC_CREATE_SUCCESS' => '创建话题成功',
    'TOPIC_CREATE_FAILED' => '创建话题失败',
    'TOPIC_DIGEST_SUCCESS' => '话题加精成功',
    'TOPIC_DIGEST_FAILED' => '话题加精失败',
    'TOPIC_UNDIGEST_SUCCESS' => '话题取消加精成功',
    'TOPIC_UNDIGEST_FAILED' => '话题取消加精失败',
    'TOPIC_UNTOP_SUCCESS' => '话题取消置顶成功',
    'TOPIC_UNTOP_FAILED' => '话题取消置顶失败',
    'AGENT_REPORT_SUCCESS' => '举报经纪人成功',
    'AGENT_REPORT_FAILED' => '举报经纪人失败',
    'SET_TOPIC_TOP_NUM' => 3,
    'SET_TOPIC_TOP_OVERFLOW' => '话题置顶数目超过限制',
    'HOT_TOPIC_BASE' => '2', //热门值基准
    'OTHER_HOT_TOPIC_SHOW' => 5, //其他热门活动显示长度
);

$forum_manage = array(
    'FORUM_MANAGE_NUM' => 8,
    'UPLOAD_PHOTO_SIZE' => 3145728,
    'BAN_USER_SUCCESS' => '封禁成功',
    'BAN_USER_FAILE' => '封禁失败',
    'UNBAN_USER_SUCCESS' => '解封成功',
    'UNBAN_USER_FAILED' => '解封失败',
);

$api_key = array(
    'apistore_baidu_key' => 'ab2591fb2ed86718f0556035a04ebf5c',
);

$qcloud = array(
    'qcloud' => array(
        'appid' => '10014575',
        'bucket' => 'yingjia',
        'secret_id' => 'AKIDoamMaUSC1srKaiQ9VhALpfC6QZoRI3Xc',
        'secret_key' => 'c0h1h9pO9htXYkNdqJbCqiumzemZgtI8',
    ),
);

$share = array(
    'INVITE_SHARE_URL' => 'http://yingplus.80shihua.com/',
    'INVITE_SHARE_CONTENT' => '影加，一个专业的粉丝社区',
    'INVITE_SHARE_PIC' => 'http://yingplus.80shihua.com/templates/front/photo/liu_shi_shi/liu_shi_shi_s.png',
);

//手机短信验证码的常量配置
$phone_message = array(
    'PHONE_USERID' => '639',
    'PHONE_ACCOUNT' => 'yj',
    'PHONE_PASSWORD' => '0432yj',
    'PHONE_URL' => 'http://121.199.1.58:8888/sms.aspx',
    'PHONE_CAPTCHA_MESSAGE_PREFIX' => '【影加】手机验证码：',
    'PHONE_CAPTCHA_MESSAGE_POSTFIX' => '，请输入此验证码，谨防泄露。为了偶像，我们加油~',
    'PHONE_MIN' => 100000, //验证码随机数最小值
    'PHONE_MAX' => 999999, //验证码随机数最大值
    'PHONE_COUNT' => 10, //手机验证码发送的条数
    'IP_COUNT' => 20,
    'xiayouqiaomu_message_content' => "【影加】欢迎参加#夏有乔木雅望天堂#包场活动，凭借二维码向活动发起人领取影票，请妥善保管。电子票：",
);
//进行第三方登录时QQ的相关常量
$QQ_message = array(
    'QQ_CLIENT_ID' => '101543335', //QQ的ID
    'QQ_CLIENT_SECRET' => 'c7a9775a687a0a0acda20db746cdb66c', //QQ的AppKey
    'QQ_REDIRECT_URL' => 'http://yingplus.80shihua.com/Login/loginByQQ', //QQ的回调地址
    'QQ_MIN' => 100000,
    'QQ_MAX' => 999999,
);
//进行第三方登录时sina的相关常量
$sina_message = array(
    'SINA_CLIENT_ID' => '3573788978', //sina的ID
    'SINA_CLIENT_SECRET' => 'f9f51595c31eefd194a73191a8d8e65a', //sina的AppKey
    'SINA_REDIRECT_URL' => 'http://yingplus.80shihua.com', //sina的回调地址
);

//通用消息常量
$common_send_message = array(
    'COMMON_MESSAGE_ACTIVITY_CANCEL' => '活动取消信息',
    'COMMON_MESSAGE_ACTIVITY_AUDIT_REJECT' => '活动审核信息',
    'COMMON_MESSAGE_BAN_USER' => '封禁信息',
    'COMMON_MESSAGE_DELETE_TOPIC_RESPONSE' => '话题回复删除信息',
    'COMMON_MESSAGE_DELETE_ACTIVITY_RESPONSE' => '活动回复删除信息',
    'COMMON_MESSAGE_SET_TOPIC_TOP' => '活动回复删除信息',
);
//用户信息
$user_status = array(
    'USER_NORMAL_STATUS' => 0, //表示用户正常
    'USER_DISABLE_STATUS' => 1, //表示用户被禁用
    'USER_DELETE_STATUS' => 2, //表示用户被删除
);

//表单提示信息常量配置
return array_merge($return_code, $db, $template, $debug, $show, $pay, $forum, $forum_manage, $activity, $upload, $topic, $api_key, $qcloud, $share, $phone_message, $QQ_message, $sina_message, $common_send_message, $user_status);

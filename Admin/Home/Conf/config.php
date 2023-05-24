<?php
require 'message.php';
$db = array(
    'DB_TYPE' => 'mysql', // 数据库类型
    //'DB_HOST'   => '192.168.0.115', // 服务器地址
    'DB_HOST' => '127.0.0.1', // 服务器地址
    'DB_NAME' => 'yingplus', // 数据库名
    'DB_USER' => 'yingplus', // 用户名
    'DB_PWD' => '4cc7c7c4db', // 密码
    'DB_PORT' => 3306, // 端口
    'DB_PREFIX' => 'yj_', // 数据库表前缀
    'DB_CHARSET' => 'utf8', // 字符集
    'DB_DEBUG' => TRUE, // 数据库调试模式 开启后可以记录SQL日志 3.2.3新增
);

$template = array(
    'TMPL_L_DELIM' => '<{', // 模板引擎普通标签开始标记
    'TMPL_R_DELIM' => '}>', // 模板引擎普通标签结束标记
    'TAGLIB_BEGIN' => '<{', // 标签库标签开始标记
    'TAGLIB_END' => '}>', // 标签库标签结束标记
);

$api = array(
    'parameter_invalid' => '参数错误',
);

$qcloud = array(
    'qcloud' => array(
        'appid' => '10014575',
        'bucket' => 'yingjia',
        'secret_id' => 'AKIDoamMaUSC1srKaiQ9VhALpfC6QZoRI3Xc',
        'secret_key' => 'c0h1h9pO9htXYkNdqJbCqiumzemZgtI8',
    ),
);

$debug = array(
    'SHOW_PAGE_TRACE' => 'true',
    'URL_MODEL' => 0,
);

$check = array(
//    'TOKEN_ON'      =>    true,  // 是否开启令牌验证 默认关闭
    //    'TOKEN_NAME'    =>    '__hash__',    // 令牌验证的表单隐藏字段名称，默认为__hash__
    //    'TOKEN_TYPE'    =>    'md5',  //令牌哈希验证规则 默认为MD5
    //    'TOKEN_RESET'   =>    true,  //令牌验证出错后是否重置令牌 默认为true
);
$page = array(
    'PAGE_LISTROWS' => 5, //每页显示的条数
);
return array_merge($check, $message, $api, $qcloud, $activity_type, $activity_status, $ban_user_content, $db, $template, $debug, $page);
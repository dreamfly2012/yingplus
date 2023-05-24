<?php
namespace Home\Controller;

Vendor('Wxpay.Api');
Vendor('Wxpay.Notify');

class PayController extends CommonController
{
    //在类初始化方法中，引入相关类库
    public function _initialize()
    {
        vendor('Alipay.Corefunction');
        vendor('Alipay.Md5function');
        vendor('Alipay.Notify');
        vendor('Alipay.Submit');
    }

    //doalipay方法
    /*该方法其实就是将接口文件包下alipayapi.php的内容复制过来
    然后进行相关处理
     */
    public function doalipay($trade_no, $ordsubject, $ordtotal_fee, $ordbody, $ordshow_url)
    {
        /*********************************************************
        把alipayapi.php中复制过来的如下两段代码去掉，
        第一段是引入配置项，
        第二段是引入submit.class.php这个类。
        为什么要去掉？？
        第一，配置项的内容已经在项目的Config.php文件中进行了配置，我们只需用C函数进行调用即可；
        第二，这里调用的submit.class.php类库我们已经在PayAction的_initialize()中已经引入；所以这里不再需要；
         */
        // require_once("alipay.config.php");
        // require_once("lib/alipay_submit.class.php");

        //这里我们通过TP的C函数把配置项参数读出，赋给$alipay_config；
        $alipay_config = C('alipay_config');

        /**************************请求参数**************************/
        $payment_type      = "1"; //支付类型 //必填，不能修改
        $notify_url        = C('alipay.notify_url'); //服务器异步通知页面路径
        $return_url        = C('alipay.return_url'); //页面跳转同步通知页面路径
        $seller_email      = C('alipay.seller_email'); //卖家支付宝帐户必填
        $out_trade_no      = $trade_no; //商户订单号 通过支付页面的表单进行传递，注意要唯一！
        $subject           = $ordsubject; //订单名称 //必填 通过支付页面的表单进行传递
        $total_fee         = $ordtotal_fee; //付款金额  //必填 通过支付页面的表单进行传递
        $body              = $ordbody; //订单描述 通过支付页面的表单进行传递
        $show_url          = $ordshow_url; //商品展示地址 通过支付页面的表单进行传递
        $anti_phishing_key = ""; //防钓鱼时间戳 //若要使用请调用类文件submit中的query_timestamp函数
        $exter_invoke_ip   = get_client_ip(); //客户端的IP地址
        /************************************************************/

        //构造要请求的参数数组，无需改动
        $parameter = array(
            "service"           => "create_direct_pay_by_user",
            "partner"           => trim($alipay_config['partner']),
            "payment_type"      => $payment_type,
            "notify_url"        => $notify_url,
            "return_url"        => $return_url,
            "seller_email"      => $seller_email,
            "out_trade_no"      => $out_trade_no,
            "subject"           => $subject,
            "total_fee"         => $total_fee,
            "body"              => $body,
            "show_url"          => $show_url,
            "anti_phishing_key" => $anti_phishing_key,
            "exter_invoke_ip"   => $exter_invoke_ip,
            "_input_charset"    => trim(strtolower($alipay_config['input_charset'])),
        );

        //建立请求
        $alipaySubmit = new \AlipaySubmit($alipay_config);
        $html_text    = $alipaySubmit->buildRequestForm($parameter, "post", "确认");
        $info         = $html_text;
        $code         = 0;
        $message      = '订单成功';
        $return       = $this->buildReturn($info, $code, $message);
        header("Content-type:text/html;charset=utf-8");
        //$this->ajaxReturn($return);
        echo $html_text;
    }

    /******************************
    服务器异步通知页面方法
    其实这里就是将notify_url.php文件中的代码复制过来进行处理

     */
    public function notifyurl()
    {
        /*
        同理去掉以下两句代码；
         */
        //require_once("alipay.config.php");
        //require_once("lib/alipay_notify.class.php");

        //这里还是通过C函数来读取配置项，赋值给$alipay_config
        $alipay_config = C('alipay_config');
        //计算得出通知验证结果
        $alipayNotify  = new \AlipayNotify($alipay_config);
        $verify_result = $alipayNotify->verifyNotify();
        if ($verify_result) {
            //验证成功
            //获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
            $out_trade_no = $_POST['out_trade_no']; //商户订单号
            $trade_no     = $_POST['trade_no']; //支付宝交易号
            $trade_status = $_POST['trade_status']; //交易状态
            $total_fee    = $_POST['total_fee']; //交易金额
            $notify_id    = $_POST['notify_id']; //通知校验ID。
            $notify_time  = $_POST['notify_time']; //通知的发送时间。格式为yyyy-MM-dd HH:mm:ss。
            $buyer_email  = $_POST['buyer_email']; //买家支付宝帐号；
            $parameter    = array(
                "out_trade_no" => $out_trade_no, //商户订单编号；
                "trade_no"     => $trade_no, //支付宝交易号；
                "total_fee"    => $total_fee, //交易金额；
                "trade_status" => $trade_status, //交易状态
                "notify_id"    => $notify_id, //通知校验ID。
                "notify_time"  => $notify_time, //通知的发送时间。
                "buyer_email"  => $buyer_email, //买家支付宝帐号；
            );
            if ($_POST['trade_status'] == 'TRADE_FINISHED') {
                //
            } else if ($_POST['trade_status'] == 'TRADE_SUCCESS') {
                $activityMovieOrderModel   = D('ActivityMovieOrder');
                $donateOrderModel = D('DonateOrder');
                $order = $activityMovieOrderModel->where(array('trade_no' => $out_trade_no))->find();

                if(empty($order)){
                    $order = $donateOrderModel->where(array('trade_no' => $out_trade_no))->find();
                    $this->orderdonatehandle($out_trade_no);

                    //分享微博
                    $code = session('weibo_login');
                    if(!empty($code)){
                        $url = 'http://www.yingplus.cc/index.php/home/pc/baiwansenlin.html';
                        $weiboLogin = new WeiboLoginController($code, C('SINA_CLIENT_ID'), C('SINA_CLIENT_SECRET'), C('SINA_REDIRECT_URL'));
                        $content = '我参加了#吴亦凡出道四周年#公益应援活动，#为爱播种，拒绝荒漠#需要每个你'.$url.'！';
                        $pic = '@'.realpath(__ROOT__).'/Public/default/share_images/baiwansenlin.jpg;type=image/png;';
                        $weiboLogin->sendWeibo($content,$pic);
                    }
                }else{
                    //插入报名表
                    $activityEnrollModel = D('ActivityEnroll');
                    $order                = $activityMovieOrderModel->where(array('trade_no' => $out_trade_no))->find();
                    $aid                  = $order['aid'];
                    $uid                  = $order['uid'];
                    $ticketnum            = $order['goods_amount'];
                    $telephone            = $order['telephone'];
                    $addtime              = time();
                    $bool                 = $activityEnrollModel->where(array('aid' => $aid, 'uid' => $uid))->find();
                    if ($bool) {
                        $activityEnrollModel->where(array('uid' => $uid, 'aid' => $aid))->setField(array('telephone' => $telephone));
                        $activityEnrollModel->where(array('uid' => $uid, 'aid' => $aid))->setInc('ticketnum', $ticketnum);
                    } else {
                        $activityEnrollModel->add(array('uid' => $uid, 'aid' => $aid, 'ticketnum' => $ticketnum, 'telephone' => $telephone, 'addtime' => $addtime));
                    }
                    $this->orderhandle($out_trade_no);

                    //添加电影兑换码
                    $activityModel = D('Activity');
                    $activity = $activityModel->where(array('id'=>$aid))->find();
                    $activityMovieCodeModel = D('ActivityMovieCode');
                    $code_data['aid'] = $aid;
                    $code_data['oid'] = $order['id'];
                    $code_data['code'] = createRandomStr(8);
                    $code_data['exchangetime'] = $activity['holdstart']+3600*24;
                    $activityMovieCodeModel->add($code_data);

                    //发送短信
                    $message_url = "http://www.yingplus.cc".U('Index/paysuccess',array('aid'=>$aid,'code'=> $code_data['code']));
                    $message_url = $this->dwz($message_url);
                    $message_content = C('xiayouqiaomu_message_content').$message_url;
                    R("Captcha/SendSMS",array($order['telephone'],$message_content));

                    //分享微博
                    $code = session('weibo_login');
                    if(!empty($code)){
                        $url = 'http://www.yingplus.cc/'.U('Pc/activity',array('aid'=>$order['aid']));
                        $weiboLogin = new WeiboLoginController($code, C('SINA_CLIENT_ID'), C('SINA_CLIENT_SECRET'), C('SINA_REDIRECT_URL'));
                        $content = '我参加了#夏有乔木梅格妮包场观影#活动，快来为#吴亦凡#助力票房（'.$url.'）！';
                        $pic = '@'.realpath(__ROOT__).'/uploads/MovieActivity/20160411/570b00e6b0ee7.jpg;type=image/png;';
                        $weiboLogin->sendWeibo($content,$pic);
                    }                   
                }
                
                //进行订单处理，并传送从支付宝返回的参数；
            }
            echo "success"; //请不要修改或删除
        } else {
            //验证失败
            echo "fail";
        }
    }

    //微信服务器异步通知
    public function wxnotifyurl()
    {
        $notify = new PayNotifyCallBack();
        $notify->Handle(false);
    }

   

    /**
     * @param $trade_no
     * 处理订单，将订单更新为已支付
     */
    public function orderhandle($trade_no)
    {
        $activityMovieOrder = D('ActivityMovieOrder');
        $now = time();
        $activityMovieOrder->where(array('trade_no' => $trade_no))->setField(array('order_status' => 1,'pay_time'=>$now));
    }

    /**
     * @param $trade_no
     * 处理订单，将订单更新为已支付
     */
    public function orderdonatehandle($trade_no)
    {
        $donateOrderModel = D('DonateOrder');
        $now = time();
        $donateOrderModel->where(array('trade_no' => $trade_no))->setField(array('order_status' => 1,'pay_time'=>$now));
    }

    /**
     * @param $trade_no
     * 处理订单，将支付状态更新为支付,写入买家账号
     */
    public function orderPay($parameter)
    {
        $activityMovieOrder = D('ActivityMovieOrder');
        $activityMovieOrder->where(array('trade_no' => $parameter['trade_no']))->setField(array('order_status' => 1, 'buyer_email' => $parameter['buyer_email'], 'pay_time' => time()));

    }

    /**
     * @param $trade_no
     * 处理订单，将支付状态更新为支付,写入买家账号
     */
    public function orderDonatePay($parameter)
    {
        $donateOrderModel = D('donateOrder');
        $donateOrderModel->where(array('trade_no' => $parameter['trade_no']))->setField(array('order_status' => 1, 'buyer_email' => $parameter['buyer_email'], 'pay_time' => time()));
    }

    /*
    页面跳转处理方法；
    这里其实就是将return_url.php这个文件中的代码复制过来，进行处理；
     */
    public function returnurl()
    {
        //头部的处理跟上面两个方法一样，这里不罗嗦了！
        $alipay_config = C('alipay_config');
        $alipayNotify  = new \AlipayNotify($alipay_config); //计算得出通知验证结果
        $verify_result = $alipayNotify->verifyReturn();
        if ($verify_result) {
            //验证成功
            //获取支付宝的通知返回参数，可参考技术文档中页面跳转同步通知参数列表
            $out_trade_no = $_GET['out_trade_no']; //商户订单号
            $trade_no     = $_GET['trade_no']; //支付宝交易号
            $trade_status = $_GET['trade_status']; //交易状态
            $total_fee    = $_GET['total_fee']; //交易金额
            $notify_id    = $_GET['notify_id']; //通知校验ID。
            $notify_time  = $_GET['notify_time']; //通知的发送时间。
            $buyer_email  = $_GET['buyer_email']; //买家支付宝帐号；

            $parameter = array(
                "out_trade_no" => $out_trade_no, //商户订单编号；
                "trade_no"     => $trade_no, //支付宝交易号；
                "total_fee"    => $total_fee, //交易金额；
                "trade_status" => $trade_status, //交易状态
                "notify_id"    => $notify_id, //通知校验ID。
                "notify_time"  => $notify_time, //通知的发送时间。
                "buyer_email"  => $buyer_email, //买家支付宝帐号
            );

            if ($_GET['trade_status'] == 'TRADE_FINISHED' || $_GET['trade_status'] == 'TRADE_SUCCESS') {
                $activityMovieOrderModel   = D('ActivityMovieOrder');
                $donateOrderModel = D('DonateOrder');
                $order = $activityMovieOrderModel->where(array('trade_no' => $out_trade_no))->find();
                if(!empty($order)){
                    if ($order['order_status']==0) {
                        $this->orderPay($parameter); //进行订单处理，并传送从支付宝返回的参数；
                    }
                    header("Location: ".C('alipay.successpage')."/trade_no/".$out_trade_no);
                    //跳转到配置项中配置的支付成功页面；
                }else{
                    $order = $donateOrderModel->where(array('trade_no' => $out_trade_no))->find();
                    if ($order['order_status']==0) {
                        $this->orderDonatePay($parameter); //进行订单处理，并传送从支付宝返回的参数；
                    }

                    //分享微博
                    $code = session('weibo_login');
                    if(!empty($code)){
                        $url = 'http://www.yingplus.cc/index.php/home/pc/baiwansenlin.html';
                        $weiboLogin = new WeiboLoginController($code, C('SINA_CLIENT_ID'), C('SINA_CLIENT_SECRET'), C('SINA_REDIRECT_URL'));
                        $content = '我参加了#吴亦凡出道四周年#公益应援活动，#为爱播种，拒绝荒漠#需要每个你'.$url.'！';
                        $pic = '@'.realpath(__ROOT__).'/Public/default/share_images/baiwansenlin.jpg;type=image/png;';
                        $weiboLogin->sendWeibo($content,$pic);
                    }
                    header('Location:http://www.yingplus.cc'.U('Pc/baiwansenlin')); //跳转到配置项中配置的支付成功页面；
                }

                
            } else {
                echo "trade_status=" . $_GET['trade_status'];
                $this->redirect(C('alipay.errorpage')); //跳转到配置项中配置的支付失败页面；
            }
        } else {
            //验证失败
            //如要调试，请看alipay_notify.php页面的verifyReturn函数
            echo "支付失败！";
        }
    }

    public function msgSuccess()
    {
        //跳转到活动详情页
        $trade_no           = I('request.trade_no', null);
        $activityMovieOrder = D('ActivityMovieOrder');
        $order              = $activityMovieOrder->where(array('trade_no' => $trade_no))->find();
        $aid                = $order['aid'];

        header("Location: http://www.yingplus.cc/index.php/Home/Pc/activity/aid/".$aid);
        //$this->redirect('Pc/activity', array('aid' => $aid,'ticketnum'=>$ticketnum,'trade_no'=>$trade_no));
        //$this->redirect('MovieActivity/detail', array('aid' => $aid));
    }

    public function msgError()
    {
        $this->display('pc/msg_error');
    }
}

class PayNotifyCallBack extends \WxPayNotify
{
    //查询订单
    public function Queryorder($transaction_id)
    {
        $input = new \WxPayOrderQuery();
        $input->SetTransaction_id($transaction_id);
        $result = \WxPayApi::orderQuery($input);

        if (array_key_exists("return_code", $result)
            && array_key_exists("result_code", $result)
            && $result["return_code"] == "SUCCESS"
            && $result["result_code"] == "SUCCESS") {

            $out_trade_no = $result['out_trade_no'];
            $activityMovieOrderModel   = D('ActivityMovieOrder');
            $donateOrderModel = D('DonateOrder');
            $order = $activityMovieOrderModel->where(array('trade_no' => $out_trade_no))->find();

            if(empty($order)){
                $order = $donateOrderModel->where(array('trade_no' => $out_trade_no))->find();
                $now = time();
                $donateOrderModel->where(array('trade_no' => $out_trade_no))->setField(array('order_status' => 1,'pay_time'=>$now));
                //分享微博
              
                $code = session('weibo_login');
                if(!empty($code)){
                    $url = 'http://www.yingplus.cc/index.php/home/pc/baiwansenlin.html';
                    $weiboLogin = new WeiboLoginController($code, C('SINA_CLIENT_ID'), C('SINA_CLIENT_SECRET'), C('SINA_REDIRECT_URL'));
                    $content = '我参加了#吴亦凡出道四周年#公益应援活动，#为爱播种，拒绝荒漠#需要每个你'.$url.'！';
                    $pic = '@'.realpath(__ROOT__).'/Public/default/share_images/baiwansenlin.jpg;type=image/png;';
                    $weiboLogin->sendWeibo($content,$pic);
                }
            }else{
                //插入报名表
                $activityEnrollModel = D('ActivityEnroll');
                $aid                  = $order['aid'];
                $uid                  = $order['uid'];
                $ticketnum            = $order['goods_amount'];
                $telephone            = $order['telephone'];
                $addtime              = time();
                $bool                 = $activityEnrollModel->where(array('aid' => $aid, 'uid' => $uid))->find();
                if ($bool) {
                    $activityEnrollModel->where(array('uid' => $uid, 'aid' => $aid))->setField(array('telephone' => $telephone));
                    $activityEnrollModel->where(array('uid' => $uid, 'aid' => $aid))->setInc('ticketnum', $ticketnum);
                } else {
                    $activityEnrollModel->add(array('uid' => $uid, 'aid' => $aid, 'ticketnum' => $ticketnum, 'telephone' => $telephone, 'addtime' => $addtime));
                }
                //添加对订单支付处理逻辑
                $activityMovieOrderModel->where(array('trade_no' => $out_trade_no))->setField(array('order_status' => 1, 'pay_status' => time()));

                //添加电影兑换码
                $activityModel = D('Activity');
                $activity = $activityModel->where(array('id'=>$aid))->find();
                $activityMovieCodeModel = D('ActivityMovieCode');
                $code_data['aid'] = $aid;
                $code_data['oid'] = $order['id'];
                $code_data['code'] = createRandomStr(8);
                $code_data['exchangetime'] = $activity['holdstart']+3600*24;
                $activityMovieCodeModel->add($code_data);

                //发送短信
                $message_url = "http://www.yingplus.cc".U('Index/paysuccess',array('aid'=>$aid,'code'=> $code_data['code']));
                $message_url = $this->dwz($message_url);
                $message_content = C('xiayouqiaomu_message_content').$message_url;
                R("Captcha/SendSMS",array($order['telephone'],$message_content));

                //分享微博
                $code = session('weibo_login');
                if(!empty($code)){
                    $url = 'http://www.yingplus.cc/'.U('Pc/activity',array('aid'=>$order['aid']));

                    $weiboLogin = new WeiboLoginController($code, C('SINA_CLIENT_ID'), C('SINA_CLIENT_SECRET'), C('SINA_REDIRECT_URL'));
                    $content = '我参加了#夏有乔木梅格妮包场观影#活动，快来为#吴亦凡#助力票房（'.$url.'）！';
                    $pic = '@'.realpath(__ROOT__).'/uploads/MovieActivity/20160411/570b00e6b0ee7.jpg;type=image/png;';
                    $weiboLogin->sendWeibo($content,$pic);

                }

            }

            
            

            return true;
        }
        return false;
    }

    //重写回调处理函数
    public function NotifyProcess($data, &$msg)
    {
        $notfiyOutput = array();

        if (!array_key_exists("transaction_id", $data)) {
            $msg = "输入参数不正确";
            return false;
        }
        //查询订单，判断订单真实性
        if (!$this->Queryorder($data["transaction_id"])) {
            $msg = "订单查询失败";
            return false;
        }

        return true;
    }
}

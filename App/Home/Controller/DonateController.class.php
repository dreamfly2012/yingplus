<?php

namespace Home\Controller;

class DonateController extends CommonController{
	//检测订单是否支付
    public function checkHasPay() {
        $trade_no = I('request.trade_no', null);
        $DonateOrder = D('DonateOrder');
        $order = $DonateOrder->field('order_status')->where(array('trade_no' => $trade_no))->find();
        $order_status = $order['order_status'];
        if ($order_status == "1") {
            $code = session('weibo_login');
            if(!empty($code)){
                $url = 'http://www.yingplus.cc/index.php/home/pc/baiwansenlin.html';
                $weiboLogin = new WeiboLoginController($code, C('SINA_CLIENT_ID'), C('SINA_CLIENT_SECRET'), C('SINA_REDIRECT_URL'));
                $content = '我参加了#吴亦凡出道四周年#公益应援活动，#为爱播种，拒绝荒漠#需要每个你'.$url.'！';
                $pic = '@'.realpath(__ROOT__).'/Public/default/share_images/baiwansenlin.jpg;type=image/png;';
                $weiboLogin->sendWeibo($content,$pic);
            }
            $this->ajaxReturn(array('info' => 0, 'message' => '订单已支付'));
        } else {
            $this->ajaxReturn(array('info' => 1, 'message' => '订单未支付'));
        }
    }

    //支付
    public function payOrder()
    {
        //支付方式等信息
        $method = I('request.method', null, 'intval');
        $aid = I('request.aid', null, 'intval');
        $amount = I('request.quantity', null, 'intval');
        //$telephone = I('request.activity_telephone', null);
        $uid = $this->getUid();
        if (empty($uid)) {
            $info = null;
            $code = 2;
            $message = C('no_login');
            $return = $this->buildReturn($info, $code, $message);
            $this->buildReturn($return);
        }
        if (empty($aid) || empty($method)) {
            $info = null;
            $code = -1;
            $message = C('parameter_invalid');
            $return = $this->buildReturn($info, $code, $message);
            $this->ajaxReturn($return);
        }

        $total_fee = number_format($amount, 2);

        //支付页面处理
        $trade_no = $this->get_order_sn();
        $ordsubject = '百万森林';
        $ordtotal_fee = $total_fee;
        $ordbody = '百万森林公益项目';
        $ordshow_url = null;
        if(isMobile()){
            $ordshow_url = "http://" . $_SERVER['HTTP_HOST'] . U('Mobile/baiwansenlin');
        }else{
            $ordshow_url = "http://" . $_SERVER['HTTP_HOST'] . U('Pc/baiwansenlin');
        }
        

        //下订单
        $data = array(
            'trade_no' => $trade_no,
            'uid' => $uid,
            'aid' => $aid,
            'pay_id' => $method,
            'goods_amount' => 1,
            'order_fee' => $total_fee,
            'add_time' => time(),
        );

        //插入订单表
        $donateOrder = D('DonateOrder');
        $id = $donateOrder->add($data);

        if ($method == "1") {
            R('Pay/doalipay', array('trade_no' => $trade_no, 'ordsubject' => $ordsubject, 'ordtotal_fee' => $ordtotal_fee, 'ordbody' => $ordbody, 'ordshow_url' => $ordshow_url));
        } else {
            $info = $this->getWxPayInfo($ordsubject, $cinemaname, $total_fee, $trade_no, $id);
            $pay_url = urlencode($info['code_url']);
            $prepay_id = $info['prepay_id'];
            $sign = $info['sign'];
            $info = U('Donate/payweixin', array('aid' => $aid, 'trade_no' => $trade_no, 'pay_url' => $pay_url,'prepay_id'=>$prepay_id, 'sign'=>$sign));
            $code = 0;
            $message = '订单成功';
            $return = $this->buildReturn($info, $code, $message);
            //$this->ajaxReturn($return);
            header("Location:".$info);
        }
    }

    //微信扫码支付页面
    public function payweixin(){
        $aid = I('request.aid',null);
        $trade_no = I('request.trade_no',null);
        $prepay_id = I('request.prepay_id',null);
        $sign = I('request.sign',null);
        $pay_url = I('request.pay_url');
        $pay_url = urldecode($pay_url);
        
        $this->assign('aid', $aid);
        $this->assign('trade_no', $trade_no);
        
        if(isMobile()){
            //$pay_url = 'weixin://wap/pay?appid%3Dwx45c4cc832f8bb8f7%26noncestr%3D123%26package%3DWAP%26prepayid%3D'.$prepay_id.'%26sign%'.$sign.'%26timestamp%3D'.time();
            $this->assign('pay_url', $pay_url);
            $this->display('mobile/pay_weixin_donate_order');
        }else{
            $this->assign('pay_url', $pay_url);
            $this->display('pc/pay_weixin_donate_order');
        }
        
    }

    //显示微信支付
    public function getWxPayUrl($title, $cinemaname, $total_fee, $trade_no, $product_id)
    {
        Vendor('Wxpay.Api');
        Vendor('Wxpay.NativePay');

        $notify = new \NativePay();
        $total_fee = 100 * $total_fee;
        $notify_url = C('wxpay.wx_notify_url');
        $goods_tag = 'none';

        $input = new \WxPayUnifiedOrder();
        $input->SetBody($title);
        $input->SetAttach($cinemaname);
        $input->SetOut_trade_no($trade_no);
        $input->SetTotal_fee($total_fee); //金额，单位分
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 60 * 60));
        $input->SetGoods_tag($goods_tag); //代金券
        $input->SetNotify_url($notify_url);
        if(isMobile()){
            $input->SetTrade_type("WAP");
        }else{
            $input->SetTrade_type("NATIVE");
        }
        
        $input->SetProduct_id($product_id);
        $result = $notify->GetPayUrl($input);
        $url = $result["code_url"];
        return $url;

    }

    public function getWxPayInfo($title, $cinemaname, $total_fee, $trade_no, $product_id)
    {
        Vendor('Wxpay.Api');
        Vendor('Wxpay.NativePay');

        $notify = new \NativePay();
        $total_fee = 100 * $total_fee;
        $notify_url = C('wxpay.wx_notify_url');
        $goods_tag = 'none';

        $input = new \WxPayUnifiedOrder();
        $input->SetBody($title);
        $input->SetAttach($cinemaname);
        $input->SetOut_trade_no($trade_no);
        $input->SetTotal_fee($total_fee); //金额，单位分
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 60 * 60));
        $input->SetGoods_tag($goods_tag); //代金券
        $input->SetNotify_url($notify_url);
        
        if(isMobile()){
            $input->SetTrade_type("WAP");
        }else{
            $input->SetTrade_type("NATIVE");
        }
        $input->SetProduct_id($product_id);
        $result = $notify->GetPayUrl($input);
        $info = $result;//prepay_id,sign
        return $info;
    }

    /**
     * 得到新订单号
     * @return  string
     */
    public function get_order_sn()
    {
        /* 选择一个随机的方案 */
        mt_srand((double)microtime() * 1000000);

        return date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
    }
}
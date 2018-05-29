<?php
/*
+--------------------------------------------------------------------------
|   WeCenter [#RELEASE_VERSION#]
|   ========================================
|   by WeCenter Software
|   © 2011 - 2014 WeCenter. All Rights Reserved
|   http://www.wecenter.com
|   ========================================
|   Support: WeCenter@qq.com
|
+---------------------------------------------------------------------------
*/


if (!defined('IN_ANWSION'))
{
    die;
}

include(ROOT_PATH.'app'.DIRECTORY_SEPARATOR.'weixinpay'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR."WxPayApi.class.php");
include(ROOT_PATH.'app'.DIRECTORY_SEPARATOR.'weixinpay'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR."NativePay.class.php");
include(ROOT_PATH.'app'.DIRECTORY_SEPARATOR.'weixinpay'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR."log.php");

class main extends AWS_CONTROLLER
{
    public function get_access_rule()
    {
        $rule_action['rule_type'] = 'black';
        $rule_action['actions'] = array();

        return $rule_action;
    }

    public function index_action()
    {
		$notify = new NativePay();
		$input = new WxPayUnifiedOrder();
        
		$orderinfo=$this->model('pay')->getorder(28);
		$orderid=$orderinfo['trade_sn'];
		$input->SetBody($orderinfo['contactname']);
		$input->SetAttach($orderinfo['contactname']);
		$input->SetGoods_tag($orderinfo['contactname']);
		$money=$orderinfo['money']*100;

		$time=time();
		$ip=ip();
		$input->SetOut_trade_no($orderid);
		$input->SetTotal_fee($money);
		$input->SetTime_start(date("YmdHis"));
		$input->SetTime_expire(date("YmdHis", time() + 600));
		$input->SetNotify_url("http://www.9ask.cn/weixinceshi/notify/");
		//$input->SetTrade_type("MWEB");
		$input->SetTrade_type("NATIVE");
		$input->SetProduct_id("123456789");
        $input->SetSpbill_create_ip($ip);
        //echo $input->GetSpbill_create_ip();
        //echo '<br/>';
		try
 		{
			$result = $notify->GetPayUrl($input);
 		}
		catch(Exception $e)
 		{
 			echo 'Message: ' .$e->getMessage();
 		}
		exit(var_dump($result));
        $key=md5('9askpay'.date("md"));

        $redirect='http://wap.9ask.cn/lawfufei/'.$orderinfo['zxid'].'.html?rnd='.$orderid;
        $result['mweb_url'].='&redirect_url='.urlencode($redirect);

        $tmpInfo = json_encode($result);
        //echo $input->GetSpbill_create_ip();
        //echo '<br/>';
        echo $tmpInfo;
		 /*
		$url2 = $result["code_url"];

		$key=md5('9askpay'.date("md"));
		include template('pay','weixinpay');
        */
        //include template('weixinpay','weixinpay');
    }
}

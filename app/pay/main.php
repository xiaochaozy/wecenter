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

class main extends AWS_CONTROLLER
{
	public function get_access_rule()
	{
		$rule_action['rule_type'] = 'white'; //黑名单,黑名单中的检查  'white'白名单,白名单以外的检查
		$rule_action['actions'] = array('index','pay','zhifu');
		return $rule_action;
	}

	public function setup()
	{
		
		$this->crumb(AWS_APP::lang()->_t('支付'), '/pay/');
	}

	public function index_action()
	{		
			$trade_sn=$this->model('pay')->order($this->user_id);
			
			if(isset($_POST['dosubmit'])){
			require_once 'config.php';
//exit(var_dump(dirname(__FILE__)));
require_once dirname(__FILE__).'/pagepay/service/AlipayTradeService.php';

require_once dirname(__FILE__).'/pagepay/buildermodel/AlipayTradePagePayContentBuilder.php';

    //商户订单号，商户网站订单系统中唯一订单号，必填
    $out_trade_no = trim($_POST['WIDout_trade_no']);

    //订单名称，必填
    $subject = trim($_POST['WIDsubject']);

    //付款金额，必填
    $total_amount = trim($_POST['WIDtotal_amount']);

    //商品描述，可空
    $body = trim($_POST['WIDbody']);

	//构造参数
	$payRequestBuilder = new AlipayTradePagePayContentBuilder();
	$payRequestBuilder->setBody($body);
	$payRequestBuilder->setSubject($subject);
	$payRequestBuilder->setTotalAmount($total_amount);
	$payRequestBuilder->setOutTradeNo($out_trade_no);

	$aop = new AlipayTradeService($config);

	/**
	 * pagePay 电脑网站支付请求
	 * @param $builder 业务参数，使用buildmodel中的对象生成。
	 * @param $return_url 同步跳转地址，公网可以访问
	 * @param $notify_url 异步通知地址，公网可以访问
	 * @return $response 支付宝返回的信息
 	*/
	$response = $aop->pagePay($payRequestBuilder,$config['return_url'],$config['notify_url']);

	//输出表单
	var_dump($response);
			}else{
				TPL::assign('trade_sn', $trade_sn);
			TPL::output('pay/pagepay');
			}
			
	}
	
	public function success_action(){
		require_once("config.php");
		require_once 'pagepay/service/AlipayTradeService.php';

$arr=$_GET;
foreach($arr as $k=>$r){
 if(in_array($k,array('c','act','app'))){
    unset($arr[$k]);
  }
}
//exit(var_dump($arr));
$alipaySevice = new AlipayTradeService($config); 
$result = $alipaySevice->check($arr);

if($result) {//验证成功
	$out_trade_no = htmlspecialchars($_GET['out_trade_no']);

	//支付宝交易号
	$trade_no = htmlspecialchars($_GET['trade_no']);
	$res=$this->model('pay')->success($out_trade_no);	
	echo "验证成功<br />支付宝交易号：".$trade_no;
}
else {
    //验证失败
    echo "验证失败";
}
		
	}

	
}

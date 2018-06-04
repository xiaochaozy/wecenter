<?php
defined('IN_PHPCMS') or exit('No permission resources.');
pc_base::load_app_class('WxPayApi');
pc_base::load_app_class('NativePay');
require_once PC_PATH.'modules'.DIRECTORY_SEPARATOR.'weixinpay'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR."log.php";
class index {

	function __construct() {
		$this->db = pc_base::load_model('content_model');
		$this->db->table_name='ask_pay_account';
	}
	//测试
	public function init() {
		$notify = new NativePay();
		$input = new WxPayUnifiedOrder();
        if(!isset($_GET['orderid'])){
            $userid=param::get_cookie('_userid');
            $pay_url='http://www.XXXXX.cn/index.php?m=pay&c=pay_ffzx&a=public_weixin_ffzx&h5zf=1&zxid='.$_GET['zxid'].'&from='.$_GET['from'].'&userid='.$userid;
            $id=curl_function($pay_url);
        }else{
            $id=trim(strip_tags($_GET['orderid']));
        }
		$orderinfo=$this->db->get_one(array('id'=>$id));
		$orderid=$orderinfo['trade_sn'];
		$input->SetBody($orderinfo['contactname']);
		$input->SetAttach($orderinfo['contactname']);
		$input->SetGoods_tag($orderinfo['contactname']);
		$money=$orderinfo['money']*100;

		$time=time();
		$ip=isset($_GET['wxip'])?trim($_GET['wxip']):ip();
		$input->SetOut_trade_no($orderid);
		$input->SetTotal_fee($money);
		$input->SetTime_start(date("YmdHis"));
		$input->SetTime_expire(date("YmdHis", time() + 600));
		$input->SetNotify_url("http://www.XXXXX.cn/weixinceshi/notify/");
		$input->SetTrade_type("MWEB");
		$input->SetProduct_id("123456789");
        $input->SetSpbill_create_ip($ip);
        //echo $input->GetSpbill_create_ip();
        //echo '<br/>';
		$result = $notify->GetPayUrl($input);
        $key=md5('XXXXXpay'.date("md"));

        if($orderinfo['pay_type']=='calllawyer_wap' || $orderinfo['pay_type']=='calllawyer_wap3'){//做一个简单的判断进行登陆
            //param::set_cookie('_username',$orderinfo['username']);
            //param::set_cookie('_userid',$orderinfo['userid']);
            $redirect='http://wap.XXXXX.cn/lawfufei/'.$orderinfo['zxid'].'.html?rnd='.$orderid;
        }else{
            $posids=$orderinfo['pay_type']=='sms_wap'?50:32;
            if($orderinfo['pay_type']=='ydy_wap') $posids=999;
            $redirect='http://wap.XXXXX.cn/index.php?m=wap&c=index_other&posids='.$posids.'&a=wxSuccess&id='.$id.'&key='.$key;

            $filename2='wxpay'.'_'.date("Ym",time());
            sendmsglog($redirect,$filename2);
        }
        $result['mweb_url'].='&redirect_url='.urlencode($redirect);

        $tmpInfo = json_encode($result);
        $this->makelog($id,$result,$ip);
        //echo $input->GetSpbill_create_ip();
        //echo '<br/>';
        echo $tmpInfo;
        /*
		$url2 = $result["code_url"];

		$key=md5('XXXXXpay'.date("md"));
		include template('pay','weixinpay');
        */
        //include template('weixinpay','weixinpay');
	}
    public function makelog($id,$result,$ip=''){//记录日志
        $log=array(
            'orderid'=>$id,
            'return_code'=>$result['return_code'],
            'return_msg'=>$result['return_msg'],
            'inputtime'=>time()
        );
        if($result['return_code']=='FAIL'){
            $log['return_msg'].='-'.$ip;
        }
        if($result['err_code']){
            $log['err_code']=$result['err_code'];
            $log['err_code_des']=$result['err_code_des'];
        }
        $this->db->table_name='ask_wxpay_log';
        $this->db->insert($log,true);
    }
    public function getip(){
        $ip=ip();
        include template('weixinpay','getip');
    }
    /*生成二维码支付*/
    public function zfewm(){
        $notify = new NativePay();
        $input = new WxPayUnifiedOrder();

        $id=trim(strip_tags($_GET['orderid']));
        $orderinfo=$this->db->get_one(array('id'=>$id));
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
        $input->SetNotify_url("http://www.XXXXX.cn/weixinceshi/notify/");
        $input->SetTrade_type("NATIVE");
        $input->SetProduct_id("123456789");

        $result = $notify->GetPayUrl($input);
		$url2 = $result["code_url"];
		$key=md5('XXXXXpay'.date("md"));
        include template('pay','weixinpay');
    }

}
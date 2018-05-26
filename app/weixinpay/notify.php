<?php
pc_base::load_app_class('WxPayApi');
require_once PC_PATH.'modules'.DIRECTORY_SEPARATOR.'weixinpay'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR."WxPay.Notify.php";
require_once PC_PATH.'modules'.DIRECTORY_SEPARATOR.'weixinpay'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR."log.php";


//初始化日志
$logHandler= new CLogFileHandler(PC_PATH.'modules'.DIRECTORY_SEPARATOR.'weixinpay'.DIRECTORY_SEPARATOR."logs".DIRECTORY_SEPARATOR.date('Y-m-d').'.log');
$log = Log::Init($logHandler, 15);

class notify extends WxPayNotify
{
	public function __construct() {
		$this->db = pc_base::load_model('content_model');
		$this->db->table_name='ask_pay_account';
	}
	public function init(){
		Log::DEBUG("begin notify");
		$this->Handle(false);
	}
	//查询订单
	public function Queryorder($transaction_id)
	{
		$input = new WxPayOrderQuery();
		$input->SetTransaction_id($transaction_id);
		$result = WxPayApi::orderQuery($input);

		Log::DEBUG("query:". json_encode($result));
		if(array_key_exists("return_code", $result)
			&& array_key_exists("result_code", $result)
			&& $result["return_code"] == "SUCCESS"
			&& $result["result_code"] == "SUCCESS")
		{
			$orderinfo=$this->db->get_one(array('trade_sn'=>$result['out_trade_no']));
			$this->db->update(array('status'=>'succ'),array('trade_sn'=>$result['out_trade_no']));
            $monarr=array(24=>1,34=>1,44=>1,19=>1,29=>1,39=>1,49=>1,79=>2,89=>2,88=>2,99=>2,179=>3,199=>3,299=>3,0.01=>99);//小数最终也是被转化为整数执行，需要后台整理优化
            $orderinfo['money']=floatval($orderinfo['money']);
            $vip=$monarr[$orderinfo['money']]?$monarr[$orderinfo['money']]:0;//支付金额不在这三个之内则标记为0
            if($vip && in_array($orderinfo['pay_type'],array('zixun','zixun_wap','czfwb_wap','sms_wap'))){
                $this->db->table_name='ask_member';
                $this->db->update(array('vip'=>$vip),array('userid'=>$orderinfo['userid']));
            }
            if($orderinfo['pay_type']=='zsfwb' && $orderinfo['money']>99){
                $this->db->table_name = 'ask_member';
                $this->db->update(array('vip' =>3), array('userid' => $orderinfo['userid']));
                $this->db->table_name = 'ask_member_fwb';
                $data=array(
                    'userid'=>$orderinfo['userid'],
                    'stime'=>time(),
                    'svip'=>$orderinfo['zxid'],
                    'lawyernum'=>10,
                );
                if($data['svip']==2){
                    $data['etime']=strtotime(" +1 year +1 day");
                    $data['fwnum']=6;
                }else{
                    $data['etime']=strtotime(" +180 day");
                    $data['fwnum']=3;
                }
                $this->db->insert($data);
            }
            //付费之后的流程操作
            $key = md5("FS".date("md"));
            $url='http://www.9ask.cn/api.php?op=pay_ffzx_later&key='.$key;
            $res=curl_function($url,array('trade_sn'=>$result['out_trade_no']),'POST',5);

			return true;
		}
		return false;
	}
	
	//重写回调处理函数
	public function NotifyProcess($data, &$msg)
	{
		Log::DEBUG("call back:" . json_encode($data));
		$notfiyOutput = array();
		
		if(!array_key_exists("transaction_id", $data)){
			$msg = "输入参数不正确";
			return false;
		}
		//查询订单，判断订单真实性
		if(!$this->Queryorder($data["transaction_id"])){
			$msg = "订单查询失败";
			return false;
		}
		return true;
	}

}

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
			$res=$this->model('pay')->order($this->user_id);
			exit(var_dump($res));
	}
	
	public function zhifu_action(){
		
	}

	
}

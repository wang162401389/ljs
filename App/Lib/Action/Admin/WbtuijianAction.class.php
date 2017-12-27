<?php
class WbtuijianAction extends ACommonAction{

	public function index(){

		$search = [];
	    $ispage = 1; //是否翻页  1翻页，用在列表  0不翻页用在导出
	    //id
	    if($_REQUEST["id"]){
	        $where["m.id"] = $_REQUEST[id];
	        $search["id"] = $_REQUEST["id"];
	    }
	    //姓名
	    if($_REQUEST["username"]){
	        $where["mi.real_name"] = $_REQUEST["username"];
	        $search["username"] = $_REQUEST["username"];
	    }
	    //手机号
	    if($_REQUEST["user_phone"]){
	        $search["user_phone"] = $_REQUEST["user_phone"];
	        $where["user_phone"] = $_REQUEST["user_phone"];
	    }
	    
	    $start_time = strtotime("2017-01-9 00:00:00");
	    $end_time = strtotime("2017-01-22 23:59:59");
	    $company_user = C("CCFAX_USER").",0";
	    $where["m.recommend_id"] = array("not in",$company_user);
	    $where["m.reg_time"] = array("between",$start_time.",".$end_time);
	    $field = "m.recommend_id,mi.real_name,mm.user_phone,COUNT(m.id) as reg_count,ms.id_status";

	    $list = M("members m")->join("lzh_members mm ON mm.id = m.recommend_id")->join("lzh_members_status ms ON ms.uid = m.recommend_id")->join("lzh_member_info mi ON mi.uid = m.recommend_id")->where($where)->field($field)->group("m.recommend_id")->select();
	    $sum_fan = 0;
	    foreach ($list as $key => $value) {
	    	$fanxian = 0;
	    	$youxiao_count = 0;
	    	$where["m.recommend_id"] = $value["recommend_id"];
	    	$where['bi.add_time'] =  array("lt",array('exp','m.reg_time + (15*3600*24)'));
	    	$youxiao = M("members m")->join("lzh_borrow_investor bi ON bi.investor_uid = m.id")->where($where)->field("SUM(bi.investor_capital) as capital,m.id")->group("m.id")->select();
	    	 
	    	foreach ($youxiao as $y) {
	    		if(intval($y["capital"])>=1000){
		    		$youxiao_count = $youxiao_count +1 ;
		    	}

		    	if($youxiao_count >= 1 && $youxiao_count <= 5){
		    		$fanxian = $youxiao_count * 8;
		    	}elseif($youxiao_count >= 6 && $youxiao_count <= 10){
		    		$fanxian = $youxiao_count * 15;
		    	}elseif($youxiao_count >= 11 && $youxiao_count <= 20){
		    		$fanxian = $youxiao_count * 20;
		    	}elseif($youxiao_count > 20){
		    		$fanxian = $youxiao_count * 25;
		    	}
	    	}
	    		$list[$key]["youxiao"] = $youxiao_count;
		    	$list[$key]["fanxian"] = $fanxian;
		    	$sum_fan += $fanxian;
	    }
	     $this->assign("fan_money",$sum_fan);
	    if(time() >= $end_time){
	    	$this->assign("is_fan",1);
	    }elseif(time() < $end_time){
	    	$this->assign("is_fan",0);
	    }else{
	    	$this->assign("is_fan",2);
	    }


	    $this->assign("list",$list);
	    $this->assign("query", http_build_query($search));
	    $this->display();
	}


	public function fafang(){
		$start_time = strtotime("2017-01-9 00:00:00");
	    $end_time = strtotime("2017-01-22 23:59:59");
	    $company_user = C("CCFAX_USER").",0";
	    $where["m.recommend_id"] = array("not in",$company_user);
	    $where["ms.id_status"] = array("neq",0);
	    $where["m.reg_time"] = array("between",$start_time.",".$end_time);
	    $field = "m.recommend_id,mi.real_name,mm.user_phone,COUNT(m.id) as reg_count";

	    $list = M("members m")->join("lzh_members mm ON mm.id = m.recommend_id")->join("lzh_members_status ms ON ms.uid = m.recommend_id")->join("lzh_member_info mi ON mi.uid = m.recommend_id")->where($where)->field($field)->group("m.recommend_id")->select();
	    $sum_fan = 0;
	    foreach ($list as $key => $value) {
	    	$fanxian = 0;
	    	$youxiao_count = 0;
	    	$where["m.recommend_id"] = $value["recommend_id"];
	    	$where['bi.add_time'] =  array("lt",array('exp','m.reg_time + (15*3600*24)'));
	    	$youxiao = M("members m")->join("lzh_members_status ms ON ms.uid = m.recommend_id")->join("lzh_borrow_investor bi ON bi.investor_uid = m.id")->where($where)->field("SUM(bi.investor_capital) as capital,m.id")->group("m.id")->select();
	    	 
	    	foreach ($youxiao as $y) {
	    		if(intval($y["capital"])>=1000){
		    		$youxiao_count = $youxiao_count +1 ;
		    	}

		    	if($youxiao_count >= 1 && $youxiao_count <= 5){
		    		$fanxian = $youxiao_count * 8;
		    	}elseif($youxiao_count >= 6 && $youxiao_count <= 10){
		    		$fanxian = $youxiao_count * 15;
		    	}elseif($youxiao_count >= 11 && $youxiao_count <= 20){
		    		$fanxian = $youxiao_count * 20;
		    	}elseif($youxiao_count > 20){
		    		$fanxian = $youxiao_count * 25;
		    	}
	    	}
	    	if($youxiao_count == 0){
	    		unset($list[$key]);  
	    	}else{
	    		$list[$key]["youxiao"] = $youxiao_count;
		    	$list[$key]["fanxian"] = $fanxian;
		    	$sum_fan += $fanxian;
	    	}
	    }

	    import("@.Oauth.sina.Weibopay");
		$payConfig = FS("Webconfig/payconfig");
		$weibopay = new Weibopay();
		$data['service'] 			  = "create_hosting_collect_trade";							//接口名称
		$data['version']			  = $payConfig['sinapay']['version'];						//接口版本
		$data['request_time']		  = date('YmdHis');											//请求时间
		$data['partner_id'] 		  = $payConfig['sinapay']['partner_id'];					//合作者身份ID
		$data['_input_charset'] 	  = $payConfig['sinapay']['_input_charset'];				//网站编码格式
		$data['sign_type'] 			  = $payConfig['sinapay']['sign_type'];						//签名方式 MD5
		$data['out_trade_no']         = date('YmdHis').mt_rand( 100000,999999); 				// 交易订单号
		$data['out_trade_code']		  = '1002';													//交易码
		$data['summary']			  =	"外部邀请活动";				//摘要
		$data['payer_id']	  		  = $payConfig['sinapay']['email'];							//付款人邮箱
		$data['payer_identity_type']  = 'EMAIL';												//ID类型
		$data['payer_ip']=get_client_ip();
		$data['pay_method']			  = "balance^".$sum_fan."^BASIC";								//支付方式：支付方式^金额^扩展|支付方式^金额^扩展。扩展信息内容以“，”分隔
		ksort($data);
		$data['sign'] 				  = $weibopay->getSignMsg($data,$data['sign_type']);		//计算签名
		$setdata 					  = $weibopay->createcurl_data($data);
		$result						  = $weibopay->curlPost($payConfig['sinapay']['mas'],$setdata);//模拟表单提交
		$rs = checksinaerror($result);
		if($rs['response_code'] =="APPLY_SUCCESS"){
			$trade_list = ""; //新浪的交易列表
			$i = 0;
			$k = 0;
			$j = 0;
			foreach ($list as $key => $value) {
				$utype = M("members")->where("id={$value['recommend_id']}")->field("user_regtype")->find();
				if($utype['user_regtype']==1){
					$account_type = 'SAVING_POT';
				}else{
					$account_type = 'BASIC';
				}
				if($i < 200){
					if($k === 0){
						$trade_list[$j] = date('YmdHis').mt_rand( 100000,999999).'~20151008'.$value['recommend_id'].'~UID~'.$account_type.'~'.$value['fanxian'].'~~外部邀请活动返现';
						$k++;
					}else{
						$trade_list[$j] .= '$'.date('YmdHis').mt_rand( 100000,999999).'~20151008'.$value['recommend_id'].'~UID~'.$account_type.'~'.$value['fanxian'].'~~外部邀请活动返现';
					}
					$i++;
					if($i === 200){$i = 0;$k = 0;$j++;}
				}
			}
			foreach ($trade_list as $key => $value) {
				import("@.Oauth.sina.Weibopay");
				$payConfig = FS("Webconfig/payconfig");
				$weibopay = new Weibopay();
				$data['service'] 			  = "create_batch_hosting_pay_trade";						//接口名称
				$data['version']			  = $payConfig['sinapay']['version'];						//接口版本
				$data['request_time']		  = date('YmdHis');											//请求时间
				$data['user_ip']			  = get_client_ip();												//用户IP地址
				$data['partner_id'] 		  = $payConfig['sinapay']['partner_id'];					//合作者身份ID
				$data['_input_charset'] 	  = $payConfig['sinapay']['_input_charset'];				//网站编码格式
				$data['sign_type'] 			  = $payConfig['sinapay']['sign_type'];						//签名方式 MD5
				$data['out_pay_no']           = date('YmdHis').mt_rand( 100000,999999); 				//交易订单号
				$data['out_trade_code']		  = '2002';													//交易码 2001代付借款金 2002代付（本金/收益）金
				$data['trade_list']			  = $value;											//交易列表
				ksort($data);
				$data['sign'] 				  = $weibopay->getSignMsg($data,$data['sign_type']);		//计算签名
				$setdata 					  = $weibopay->createcurl_data($data);
				$result						  = $weibopay->curlPost($payConfig['sinapay']['mas'],$setdata);//模拟表单提交
				$rs1 = checksinaerror($result);
				if($rs1["response_code"] != "APPLY_SUCCESS"){
					$this->ajaxReturn($rs1);die;
				}
			}
			 // M("christmas_have")->where("gift_id = 1 and is_send = 0")->save(array("is_send"=>1));
			$this->ajaxReturn("success");
		}else{
			$this->ajaxReturn($rs);
		}
	}
}
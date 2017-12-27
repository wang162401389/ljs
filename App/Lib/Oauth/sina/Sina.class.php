<?php 
	/**
	* 新浪接口封装 
	*by liaozhaobin
	*/
	import("@.Oauth.sina.Weibopay");
	class Sina extends Action
	{
		var $version=NULL;				//接口版本
		var $partner_id=NULL; 			//合作者身份ID
		var $_input_charset=NULL; 		//网站编码格式
		var $sign_type=NULL; 			//签名方式 MD5
		var $payConfig = NULL;			//配置文件
		var $rsa_pub = NULL;			//加密文件

		//初始化
		function _initialize(){
			$this->sinafile 		= C('SINA_FILE');
			$this->payConfig 		= FS("Webconfig/payconfig");
			$this->version 			= $this->payConfig['sinapay']['version'];
			$this->partner_id		= $this->payConfig['sinapay']['partner_id'];
			$this->_input_charset 	= $this->payConfig['sinapay']['_input_charset'];
			$this->sign_type		= $this->payConfig['sinapay']['sign_type'];
			$this->email 			= $this->payConfig['sinapay']['email'];
			$this->rsa_pub 			= dirname(dirname(dirname(__FILE__)))."/Key/".$this->sinafile;
			$this->mgs				= $this->payConfig['sinapay']['mgs'];
			$this->mas 				= $this->payConfig['sinapay']['mas'];
		}

		/**
		 *创建激活会员
		 * @param $sina 用户ID，用户类型
		 * @return true 提交成功 false 提交失败
		 */
		function createmember($sina)
		{
			$weibopay = new Weibopay();
			$data['service'] 		= "create_activate_member"; 			//接口名称
			$data['version']		= $this->version;						//接口版本
			$data['request_time'] 	= date('YmdHis');						//请求时间
			$data['partner_id'] 	= $this->partner_id;					//合作者身份ID
			$data['_input_charset'] = $this->_input_charset;				//网站编码格式
			$data['sign_type'] 		= $this->sign_type;						//签名方式 MD5
			$data['identity_id'] 	= "20151008".$sina['identity_id'];		//用户ID
			$data['identity_type'] 	= "UID";								//用户标识类型 UID
			$data['member_type'] 	= $sina['member_type'];					//用户类型：1：个人用户 2：企业用户
			$data["client_ip"]=get_client_ip();                                     //获取ip
			ksort($data); 													//对签名参数数据数组排序
			$data['sign'] 			= $weibopay -> getSignMsg($data,$data['sign_type']);						//计算签名
			$createdata 			= $weibopay -> createcurl_data($data);
			$result 				= $weibopay -> curlPost($this->mgs,$createdata);			//模拟表单提交
			$rs = $this->checkerror($result);//验证
			

			if($rs["response_code"] == "APPLY_SUCCESS"){
				return true;
			}else{
				file_put_contents('sinaerrorlog.txt', "时间：".date("Y-m-d H:i:s")."用户ID：".$data["identity_id"].",类型：创建激活会员，信息:".$rs["response_message"]."\n", FILE_APPEND);
				return $rs["response_message"];
			}
		}

		/**
		 *绑定认证信息
		 * @param $uid 用户ID
		 * @param $phone 手机号码
		 * @return true 提交成功 false 提交失败
		 */
		function bindingverify($sina)
		{
			$weibopay = new Weibopay();
			$data['service'] 		= "binding_verify";						//接口名称
			$data['version'] 		= $this->version;						//接口版本
			$data['request_time'] 	= date('YmdHis');						//请求时间
			$data['partner_id'] 	= $this->partner_id;					//合作者身份ID
			$data['_input_charset'] = $this->_input_charset;				//网站编码格式
			$data['sign_type'] 		= $this->sign_type;						//签名方式 MD5
			$data['identity_id'] 	= "20151008".$sina['identity_id'];						//用户ID
			$data['identity_type'] 	= "UID";								//用户标识类型 UID
			$data['verify_type'] 	= "MOBILE"; 							//认证类型
			$data['client_ip']=get_client_ip();
			$verify_entity_rsa		= $weibopay->Rsa_encrypt($sina['phone'],$this->rsa_pub);		//对认证信息进行加密
			$data['verify_entity'] 	= $verify_entity_rsa;													//认证内容
			ksort($data);																					//对签名参数数据排序
			$data['sign'] 			= $weibopay->getSignMsg($data,$data['sign_type']);						//计算签名
			$bindingdata 			= $weibopay->createcurl_data($data);
			$result 				= $weibopay->curlPost($this->mgs,$bindingdata);		//模拟表单提交
			$rs = $this->checkerror($result);//验证
			file_put_contents('sinaerror.txt', $result, FILE_APPEND);
			file_put_contents('sinaerror1.txt', var_export($result,true), FILE_APPEND);
			if($rs["response_code"] == "APPLY_SUCCESS"){
				return true;
			}else{
				file_put_contents('sinaerrorlog.txt', "时间：".date("Y-m-d H:i:s")."用户ID：".$data["identity_id"].",类型：绑定认证信息，信息:".$rs["response_message"]."\n", FILE_APPEND);
				return $rs["response_message"];
			}
		}

		/**
		 *设置实名认证
		 * @param $uid 用户ID
		 * @param $real_name 真实姓名
		 * @param $cert_no 身份证号
		 * @return true 提交成功 $rs["response_message"] 失败信息
		 */
		function setrealname($sina)
		{
			$weibopay = new Weibopay();
			$data['service']		= "set_real_name";						//接口名称
			$data['version'] 		= $this->version;						//接口版本
			$data['request_time'] 	= date('YmdHis');						//请求时间
			$data['partner_id'] 	= $this->partner_id;					//合作者身份ID
			$data['_input_charset'] = $this->_input_charset;				//网站编码格式
			$data['sign_type'] 		= $this->sign_type;						//签名方式 MD5
			$data['identity_id'] 	= "20151008".$sina['identity_id'];		//用户ID
			$data['identity_type'] 	= "UID";								//用户标识类型 UID
			$data["client_ip"]=get_client_ip();
			$realname 				= $weibopay->Rsa_encrypt($sina['real_name'],$this->rsa_pub);	//对用户姓名进行rsa公钥加密
			$data['real_name'] 		= $realname;							//真是姓名
			$data['cert_type'] 		= "IC";									//用户标识类型 UID
			$cret_no 				= $weibopay->Rsa_encrypt($sina['cert_no'],$this->rsa_pub);		//对身份证号进行rsa公钥加密
			$data['cert_no'] 		= $cret_no;								//身份证号
			ksort($data);													//对签名参数数据排序
			$data['sign'] 			= $weibopay->getSignMsg($data,$data['sign_type']);						//计算签名
			$setdata 				= $weibopay->createcurl_data($data);
			$result 				= $weibopay->curlPost($this->mgs,$setdata);			//模拟表单提交
			$rs = $this->checkerror($result);//验证
			if($rs["response_code"] == "APPLY_SUCCESS"){
				return true;
			}else{
				file_put_contents('sinaerrorlog.txt', "时间：".date("Y-m-d H:i:s")."用户ID：".$data["identity_id"].",类型：设置实名认证，信息:".$rs["response_message"]."\n", FILE_APPEND);
				return $rs["response_message"];
			}
		}

		/**
		 *申请企业审核
		 * @param array $sina 
		 * @return true 提交成功 $rs["response_message"] 失败信息
		 */
		function auditmember($sina)
		{
			$weibopay = new Weibopay();
			$data['service']			= "audit_member_infos";						//接口名称
			$data['version']		 	= $this->version;							//接口版本
			$data['request_time']		= date('YmdHis');							//请求时间
			$data['partner_id']		 	= $this->partner_id;						//合作者身份ID
			$data['_input_charset']	 	= $this->_input_charset;					//网站编码格式
			$data['sign_type']		 	= $this->sign_type;							//签名方式 MD5
			$data['notify_url']		 	= "https://".$_SERVER['HTTP_HOST']."/Home/Sinanotify/companystatus"; 	// 异步回调地址
			$data['identity_id']	 	= '20151008'.$sina["identity_id"];			//用户ID
			$data['identity_type']	 	= "UID";									//用户标识类型 UID
			$data['audit_order_no']	 	= $sina['audit_order_no'];					//请求订单号
			$data['company_name']	 	= $sina['company_name'];					//公司全称
			$data['address']		 	= $sina['address'];							//企业地址
			$data['license_no']		 	= $weibopay->Rsa_encrypt($sina['license_no'],$this->rsa_pub);			//对执照号进行rsa公钥加密
			$data['license_address'] 	= $sina['license_address'];					//营业执照所在地
			$data['license_expire_date'] = $sina['license_expire_date'];			//执照过期日
			$data['business_scope']	 	= $sina['business_scope'];					//营业范围
			$data['telephone']		 	= $weibopay->Rsa_encrypt($sina['telephone'],$this->rsa_pub);			//对联系电话进行rsa公钥加密
			$data['email']			 	= $weibopay->Rsa_encrypt($sina['email'],$this->rsa_pub);				//对EMAIL进行rsa公钥加密
			$data['organization_no'] 	= $weibopay->Rsa_encrypt($sina['organization_no'],$this->rsa_pub);		//对组织机构代码进行rsa公钥加密
			$data['summary']		 	= $sina['summary'];							//企业简介
			$data['legal_person'] 	 	= $weibopay->Rsa_encrypt($sina['legal_person'],$this->rsa_pub);			//对企业法人进行rsa公钥加密
			$data['cert_no'] 		 	= $weibopay->Rsa_encrypt($sina['cert_no'],$this->rsa_pub);				//对法人证件号进行rsa公钥加密
			$data['cert_type']		 	= "IC";										//证件类型
			$data['legal_person_phone'] = $weibopay->Rsa_encrypt($sina['legal_person_phone'],$this->rsa_pub);	//对法人手机号进行rsa公钥加密
			$data['bank_code'] 		 	= $sina['bankcode'];						//银行编号
			$data['bank_account_no'] 	= $weibopay->Rsa_encrypt($sina['bank_num'],$this->rsa_pub);				//对银行卡号进行rsa公钥加密
			$data['card_type']		 	= "DEBIT";									//卡类型
			$data['card_attribute']	 	= "B";										//卡属性
			$data['province']		 	= $sina['bank_province'];					//开户行省份
			$data['city']			 	= $sina['bank_city'];						//开户行城市
			$data['bank_branch']	 	= $sina['bank_address'];					//支行名称
			$data['fileName']		 	= $sina['fileName'];						//文件名称
			$data['digest']			 	= $sina['digest'];							//文件摘要
			$data['digestType']		 	= "MD5";									//文件摘要算法
			$data["client_ip"]          =get_client_ip(); 
			ksort($data);															//对签名参数数据排序
			$data['sign'] 				= $weibopay->getSignMsg($data,$data['sign_type']);						//计算签名
			$setdata 					= $weibopay->createcurl_data($data);
			$result 					= $weibopay->curlPost($this->mgs,$setdata);			//模拟表单提交
			$rs = $this->checkerror($result);//验证
			if($rs["response_code"] == "APPLY_SUCCESS"){
				return true;
			}else{
				file_put_contents('sinaerrorlog.txt', "时间：".date("Y-m-d H:i:s")."用户ID：".$data["identity_id"].",类型：申请企业审核，信息:".$rs["response_message"]."\n", FILE_APPEND);
				return $rs["response_message"];
			}
		}

		/**
		 * 经办人信息
		 * @param array $sina 
		 * @return true 提交成功 $rs["response_message"] 失败信息
		 */
		function smtfundagentbuy($sina)
		{
			$weibopay = new Weibopay();
			$data['service']			= "smt_fund_agent_buy";						//接口名称
			$data['version']		 	= $this->version;							//接口版本
			$data['request_time']	 	= date('YmdHis');							//请求时间
			$data['partner_id']		 	= $this->partner_id;						//合作者身份ID
			$data['_input_charset']	 	= $this->_input_charset;					//网站编码格式
			$data['sign_type']		 	= $this->sign_type;							//签名方式 MD5
			$data['identity_id']	 	= '20151008'.$sina['uid'];					//用户ID
			$data['identity_type']	 	= "UID";									//用户标识类型 UID
			$data['agent_name']		 	= $weibopay->Rsa_encrypt($sina['agent_name'],$this->rsa_pub);			//对经办人姓名进行rsa公钥加密
			$data['license_no']		 	= $weibopay->Rsa_encrypt($sina['license_no'],$this->rsa_pub);			//对经办人身份证进行rsa公钥加密
			$data['license_type_code']	= "ID"; 									//证件类型
			$data['agent_mobile']    	= $weibopay->Rsa_encrypt($sina['agent_mobile'],$this->rsa_pub);			//对经办人手机进行rsa公钥加密
			$data['client_ip']          =get_client_ip();
			ksort($data);															//对签名参数数据排序
			$data['sign'] 				= $weibopay->getSignMsg($data,$data['sign_type']);						//计算签名
			$setdata 					= $weibopay->createcurl_data($data);
			$result 					= $weibopay->curlPost($this->mgs,$setdata);			//模拟表单提交
			$rs = $this->checkerror($result);//验证
			if($rs["response_code"] == "APPLY_SUCCESS"){
				return $rs;
			}else{
				file_put_contents('sinaerrorlog.txt', "时间：".date("Y-m-d H:i:s")."用户ID：".$data["identity_id"].",类型：经办人信息，信息:".$rs["response_message"]."\n", FILE_APPEND);
				return $rs["response_message"];
			}
		}

		/**
		 *查询用户绑卡信息
		 * @param $uid 用户ID
		 * @return $rs['card_list'] 银行卡列表 $rs["response_message"] 失败信息
		 */
		function querycard($uid)
		{
			$weibopay = new Weibopay();
			$data['service'] 			= "query_bank_card";						//接口名称
			$data['version'] 			= $this->version;							//接口版本
			$data['request_time'] 		= date('YmdHis');							//请求时间
			$data['partner_id'] 		= $this->partner_id;						//合作者身份ID
			$data['_input_charset'] 	= $this->_input_charset;					//网站编码格式
			$data['sign_type'] 			= $this->sign_type;							//签名方式 MD5
			$data['identity_id'] 		= "20151008".$uid;							//用户ID			
			$data['identity_type'] 		= "UID";									//用户标识类型 UID
			ksort($data);															//对签名参数数据排序
			$data['sign'] 				= $weibopay->getSignMsg($data,$data['sign_type']);						//计算签名
			$setdata 					= $weibopay->createcurl_data($data);
			$result 					= $weibopay->curlPost($this->mgs,$setdata);			//模拟表单提交
			$rs = $this->checkerror($result);//验证
			if($rs["response_code"] == "APPLY_SUCCESS"){
				return $rs['card_list'];
			}else{
				file_put_contents('sinaerrorlog.txt', "时间：".date("Y-m-d H:i:s")."用户ID：".$data["identity_id"].",类型：查询用户绑卡信息，信息:".$rs["response_message"]."\n", FILE_APPEND);
				return $rs["response_message"];
			}
		}

		/**
		 *解绑银行卡
		 * @param $uid 用户ID
		 * @param $carid 钱包编号
		 */
		function unbindcard($uid,$cardid)
		{
			$weibopay = new Weibopay();
			$data['service'] 			= "unbinding_bank_card";					//接口名称
			$data['version'] 			= $this->version;							//接口版本
			$data['request_time'] 		= date('YmdHis');							//请求时间
			$data['partner_id'] 		= $this->partner_id;						//合作者身份ID
			$data['_input_charset'] 	= $this->_input_charset;					//网站编码格式
			$data['sign_type'] 			= $this->sign_type;							//签名方式 MD5	
			$data['identity_id'] 		= "20151008".$uid;							//用户ID
			$data['identity_type'] 		= "UID";									//用户标识类型 UID
			$data['card_id'] 			= $cardid;									//钱包编号
			$data["client_ip"]          =get_client_ip();
			ksort($data);															//对签名参数数据排序
			$data['sign'] 				= $weibopay->getSignMsg($data,$data['sign_type']);						//计算签名
			$setdata 					= $weibopay->createcurl_data($data);
			$result 					= $weibopay->curlPost($this->mgs,$setdata);			//模拟表单提交
			$rs = $this->checkerror($result);//验证
			if($rs["response_code"] == "APPLY_SUCCESS"){
				return true;
			}else{
				file_put_contents('sinaerrorlog.txt', "时间：".date("Y-m-d H:i:s")."用户ID：".$data["identity_id"].",类型：解绑银行卡，信息:".$rs["response_message"]."\n", FILE_APPEND);
				return $rs["response_message"];
			}
		}

		/**
		 *提现记录查询
		 * @param $uid 用户ID
		 * @param $starttime 起始日期
		 * @param $endtime 结束日期
		 * @param $page_no 页码
		 * @return $rs["withdraw_list"] 提现记录列表 false 提交失败
		 */
		function querywithdraw($uid,$starttime=null,$endtime=null,$page_no=1)
		{
			$weibopay = new Weibopay();
			$data['service'] 			= "query_hosting_withdraw";								//接口名称
			$data['version'] 			= $this->version;										//接口版本
			$data['request_time'] 		= date('YmdHis');										//请求时间
			$data['partner_id'] 		= $this->partner_id;									//合作者身份ID
			$data['_input_charset'] 	= $this->_input_charset;								//网站编码格式
			$data['sign_type'] 			= $this->sign_type;										//签名方式 MD5	
			$data['identity_id'] 		= "20151008".$uid;										//用户ID
			$data['identity_type']		= "UID";												//用户标识类型 UID
			$data['account_type'] 		= "SAVING_POT";
			if($starttime != null){
				$data['start_time'] 	= date("YmdHis", strtotime($starttime." 00:00:00"));	//开始时间
			}else{
				$data['start_time'] 	= date("YmdHis",time()-3600*24*30);						//开始时间
			}
			if($endtime != null){
				$data['end_time'] 		= date("YmdHis", strtotime($endtime." 23:59:59"));		//结束时间
			}else{
				$data['end_time'] 		= date("YmdHis");										//结束时间
			}
			$data['page_no'] 			= $page_no;												//页号
			ksort($data);																		//对签名参数数据排序
			$data['sign'] 				= $weibopay->getSignMsg($data,$data['sign_type']);		//计算签名
			$setdata 					= $weibopay->createcurl_data($data);
			$result 					= $weibopay->curlPost($this->mas,$setdata);		//模拟表单提交
			$rs = $this->checkerror($result);//验证
			if($rs["response_code"] == "APPLY_SUCCESS"){
				return $rs["withdraw_list"];
			}else{
				file_put_contents('sinaerrorlog.txt', "时间：".date("Y-m-d H:i:s")."用户ID：".$data["identity_id"].",类型：提现记录查询，信息:".$rs["response_message"]."\n", FILE_APPEND);
				return false;
			}
		}

		/**
		 *收支明细查询
		 * @param $uid 用户ID
		 * @param $usertype 用户类型
		 * @param $starttime 起始日期
		 * @param $endtime 结束日期
		 * @param $page_no 页码
		 * @return $rs["detail_list"] 提现记录列表 false 提交失败
		 */
		function queryuserdet($uid,$usertype,$starttime=null,$endtime=null,$page_no=1)
		{
			$weibopay = new Weibopay();
			$data['service'] 			= "query_account_details";								//接口名称
			$data['version'] 			= $this->version;										//接口版本
			$data['request_time'] 		= date('YmdHis');										//请求时间
			$data['partner_id'] 		= $this->partner_id;									//合作者身份ID
			$data['_input_charset'] 	= $this->_input_charset;								//网站编码格式
			$data['sign_type'] 			= $this->sign_type;										//签名方式 MD5	
			$data['identity_id'] 		= "20151008".$uid;										//用户ID
			$data['identity_type']		= "UID";												//用户标识类型 UID
			$data['account_type'] 		= $usertype;
			$data['extend_param']		= "svp_trade_type^in_out";
			if($starttime != null){
				$data['start_time'] 	= date("YmdHis", strtotime($starttime." 00:00:00"));	//开始时间
			}else{
				$data['start_time'] 	= date("YmdHis",time()-3600*24*7);						//开始时间
			}
			if($endtime != null){
				$data['end_time'] 		= date("YmdHis", strtotime($endtime." 23:59:59"));		//结束时间
			}else{
				$data['end_time'] 		= date("YmdHis");										//结束时间
			}
			$data['page_no'] 			= $page_no;												//页号
			ksort($data);																		//对签名参数数据排序
			$data['sign'] 				= $weibopay->getSignMsg($data,$data['sign_type']);		//计算签名
			$setdata 					= $weibopay->createcurl_data($data);
			$result 					= $weibopay->curlPost($this->mgs,$setdata);		//模拟表单提交
			$rs = $this->checkerror($result);//验证
			if($rs["response_code"] == "APPLY_SUCCESS"){
				return $rs;
			}else{
				file_put_contents('sinaerrorlog.txt', "时间：".date("Y-m-d H:i:s")."用户ID：".$data["identity_id"].",类型：提现记录查询，信息:".$rs["response_message"]."\n", FILE_APPEND);
				return false;
			}
		}

		/**
		 *查询是否设置支付密码
		 * @param $uid 用户ID
		 */
		function issetpaypwd($uid)
		{
			$weibopay = new Weibopay();
			$data['service'] 		  = "query_is_set_pay_password";							//绑定认证信息的接口名称
			$data['version']		  = $this->version;											//接口版本
			$data['request_time']	  = date('YmdHis');											//请求时间
			$data['partner_id'] 	  = $this->partner_id;										//合作者身份ID
			$data['_input_charset']   = $this->_input_charset;									//网站编码格式
			$data['sign_type'] 		  = $this->sign_type;										//签名方式 MD5
			$data['identity_id']	  = "20151008".$uid;										//用户ID
			$data['identity_type'] 	  = "UID";													//用户标识类型 UID
			ksort($data);
			$data['sign'] 			  = $weibopay->getSignMsg($data,$data['sign_type']);		//计算签名
			$setdata 				  = $weibopay->createcurl_data($data);
			$result					  = $weibopay->curlPost($this->mgs,$setdata);		//模拟表单提交
			$rs = $this->checkerror($result);//验证
			if($rs["response_code"] == "APPLY_SUCCESS"){
				if($rs["is_set_paypass"] == "Y"){
					return true;
				}else{
					return false;
				}
				
			}else{
				file_put_contents('sinaerrorlog.txt', "时间：".date("Y-m-d H:i:s")."用户ID：".$data["identity_id"].",类型：查询是否设置支付密码，信息:".$rs["response_message"]."\n", FILE_APPEND);
				return false;
			}
		}

		/**
		 *设置支付密码重定向
		 * @param $uid 用户ID
		 * @param $returnurl 同步返回的地址
		 * @return 提交成功 返回$rs['redirect_url'] 失败返回 false
		 */
		function setpaypassword($uid,$returnurl)
		{
			import("@.Oauth.sina.Weibopay");
			$payConfig = FS("Webconfig/payconfig");
			$weibopay = new Weibopay();
			$data['service'] 		  = "set_pay_password";										//接口名称
			$data['version']		  = $this->version;											//接口版本
			$data['request_time']	  = date('YmdHis');											//请求时间
			$data['partner_id'] 	  = $this->partner_id;										//合作者身份ID
			$data['_input_charset']   = $this->_input_charset;									//网站编码格式
			$data['sign_type'] 		  = $this->sign_type;										//签名方式 MD5
			$data['identity_id']	  = "20151008".$uid;										//用户ID
			$data['identity_type'] 	  = "UID";													//用户标识类型 UID
			$data['return_url']		  = $returnurl; 											//同步回调地址
			ksort($data);
			$data['sign'] 			  = $weibopay->getSignMsg($data,$data['sign_type']);				//计算签名
			$setdata 				  = $weibopay->createcurl_data($data);
			$result					  = $weibopay->curlPost($this->mgs,$setdata);		//模拟表单提交
			$rs = $this->checkerror($result);//验证
			if($rs["response_code"] == "APPLY_SUCCESS"){
				return $rs['redirect_url'];
			}else{
				file_put_contents('sinaerrorlog.txt', "时间：".date("Y-m-d H:i:s")."用户ID：".$data["identity_id"].",类型：设置支付密码重定向，信息:".$rs["response_message"]."\n", FILE_APPEND);
				return false;
			}
		}

		/**
		 *找回支付密码
		 * @param $uid 用户ID
		 * @param $returnurl 同步返回的地址
		 * @return 提交成功 返回$rs['redirect_url'] 失败返回 false
		 */
		function findpaypwd()
		{
			$weibopay = new Weibopay();
			$data['service'] 		  = "find_pay_password";									//接口名称
			$data['version']		  = $this->version;											//接口版本
			$data['request_time']	  = date('YmdHis');											//请求时间
			$data['partner_id'] 	  = $this->partner_id;										//合作者身份ID
			$data['_input_charset']   = $this->_input_charset;									//网站编码格式
			$data['sign_type'] 		  = $this->sign_type;										//签名方式 MD5
			$data['identity_id']	  = "20151008".$uid;										//用户ID
			$data['identity_type'] 	  = "UID";													//用户标识类型 UID
			$data['return_url']       = $returnurl;												//同步回调地址
			ksort($data);	
			$data['sign'] 			  = $weibopay->getSignMsg($data,$data['sign_type']);				//计算签名
			$setdata 				  = $weibopay->createcurl_data($data);
			$result					  = $weibopay->curlPost($this->mgs,$setdata);		//模拟表单提交
			$rs = $this->checkerror($result);//验证
			if($rs["response_code"] == "APPLY_SUCCESS"){
				return $rs['redirect_url'];
			}else{
				file_put_contents('sinaerrorlog.txt', "时间：".date("Y-m-d H:i:s")."用户ID：".$data["identity_id"].",类型：找回支付密码，信息:".$rs["response_message"]."\n", FILE_APPEND);
				return false;
			}
		}

		/**
		 *查询余额
		 * @param $uid 用户ID
		 * @param $type 账户类型 BASIC 基本户 SAVING_POT 存钱罐
		 * @return 成功返回余额 失败则返回零元 
		 */
		function querybalance($uid,$type)
		{
			$weibopay = new Weibopay();
			$data['service'] 		  = "query_balance";									//接口名称
			$data['version']		  = $this->version;										//接口版本
			$data['request_time']	  = date('YmdHis');										//请求时间
			$data['partner_id'] 	  = $this->partner_id;									//合作者身份ID
			$data['_input_charset']   = $this->_input_charset;								//网站编码格式
			$data['sign_type'] 		  = $this->sign_type;									//签名方式 MD5
			$data['identity_id']	  = '20151008'.$uid;									//用户标识类型 UID
			$data['identity_type'] 	  = 'UID';												//ID类型
			$data['account_type']	  = $type;												//账户类型
			ksort($data);
			$data['sign'] 			  = $weibopay->getSignMsg($data,$data['sign_type']);				//计算签名
			$setdata 				  = $weibopay->createcurl_data($data);
			$result					  = $weibopay->curlPost($this->mgs,$setdata);		//模拟表单提交
			$rs = $this->checkerror($result);//验证
			if($rs["response_code"] == "APPLY_SUCCESS"){
				return $splitdata['balance'];
			}else{
				file_put_contents('sinaerrorlog.txt', "时间：".date("Y-m-d H:i:s")."用户ID：".$data["identity_id"].",类型：查询余额，信息:".$rs["response_message"]."\n", FILE_APPEND);
				return 0.00;
			}
		}

		/**
		 *Sina页面展示用户信息
		 * @param $uid 用户ID
		 * @return $result 返回url
		 */
		function showmemberinfo($uid)
		{	
			$weibopay = new Weibopay();
			$data['service'] 		  = "show_member_infos_sina";						//接口名称
			$data['version']		  = $this->version;									//接口版本
			$data['request_time']	  = date('YmdHis');									//请求时间
			$data['partner_id'] 	  = $this->partner_id;								//合作者身份ID
			$data['_input_charset']   = $this->_input_charset;							//网站编码格式
			$data['sign_type'] 		  = $this->sign_type;								//签名方式 MD5
			$data['identity_id'] 	  = "20151008".$uid;								//ID类型
			$data['identity_type']	  = "UID";		
			ksort($data);
			$data['sign'] 			  = $weibopay->getSignMsg($data,$data['sign_type']);				//计算签名
			$setdata 				  = $weibopay->createcurl_data($data);
			$result					  = $weibopay->curlPost($this->mgs,$setdata);		//模拟表单提交
			return $result;
		}

		/**
		 *托管充值
		 * @param $uid 用户ID
		 * @param $money 金额
		 * @param $orderno 订单号
		 * @param $usertype 用户类型
		 * @param $returnurl 同步回调地址
		 * @return $result 返回url
		 */
		function deposit($uid,$money,$orderno,$usertype,$returnurl)
		{
			$weibopay = new Weibopay();
			$data['service'] 		  = "create_hosting_deposit";						//接口名称
			$data['version']		  = $this->version;									//接口版本
			$data['request_time']	  = date('YmdHis');									//请求时间
			$data['partner_id'] 	  = $this->partner_id;								//合作者身份ID
			$data['_input_charset']   = $this->_input_charset;							//网站编码格式
			$data['sign_type'] 		  = $this->sign_type;								//签名方式 MD5
			$data['return_url']       = $returnurl;  									//
			$data['notify_url']       = "https://".$_SERVER['HTTP_HOST']."/sinanotify/depositnotify"; 	// 异步回调地址
			$data['out_trade_no']     = $orderno; 										// 交易订单号
			$data['summary']		  = "账户充值";										//摘要
			$data['identity_id']	  = "20151008".$uid;								//用户ID
			$data['identity_type'] 	  = "UID";											//用户标识类型 UID
			$data['amount'] 		  = number_format($money, 2, ".", "" ); // *元 充值金额（精确到分，不可为负）
			$data['payer_ip']			  = get_client_ip();
			$data['account_type']	  = "SAVING_POT";
			if($usertype == 1){
				$data['pay_method']	  = "online_bank^".$data['amount']."^SINAPAY,DEBIT,C";	//支付方式：支付方式^金额^扩展|支付方式^金额^扩展。扩展信息内容以“，”分隔
			}else{
				$data['pay_method']	  = "online_bank^".$data['amount']."^SINAPAY,DEBIT,B";	//支付方式：支付方式^金额^扩展|支付方式^金额^扩展。扩展信息内容以“，”分隔
			}
			ksort($data);
			$data['sign'] 			  = $weibopay->getSignMsg($data,$data['sign_type']);			//计算签名
			$setdata 				  = $weibopay->createcurl_data($data);
			$result 				  = $weibopay->curlPost($this->mas,$setdata);//模拟表单提交	
			return $result;
		}

		/**
		 *托管提现
		 * @param $uid 用户ID
		 * @param $money 金额
		 * @param $orderno 订单号
		 * @param $returnurl 同步回调地址
		 * @return $result 返回url
		 */
		function withdraw($uid,$returnurl,$money,$orderno)
		{
			$weibopay = new Weibopay();
			$data['service'] 		  = "create_hosting_withdraw";						//接口名称
			$data['version']		  = $this->version;									//接口版本
			$data['request_time']	  = date('YmdHis');									//请求时间
			$data['partner_id'] 	  = $this->partner_id;								//合作者身份ID
			$data['_input_charset']   = $this->_input_charset;							//网站编码格式
			$data['sign_type'] 		  = $this->sign_type;								//签名方式 MD5
			$data['out_trade_no']     = $orderno;										//交易订单号
			$data['return_url']       = $returnurl; 									//同步回调地址
			$data['notify_url']		  = "https://".$_SERVER['HTTP_HOST']."/Home/Sinanotify/withdrawnotify"; //异步回调地址
			$data['identity_id']	  = '20151008'.$uid;								//用户ID
			$data['identity_type'] 	  = 'UID';											//用户标识类型 UID
			$data['amount']			  = $money;											//金额
			$data['account_type']	  = "SAVING_POT";									//账户类型
			$data["user_ip"]=get_client_ip();
			ksort($data);
			$data['sign'] 			  = $weibopay->getSignMsg($data,$data['sign_type']);				//计算签名
			$setdata 				  = $weibopay->createcurl_data($data);
			$result 				  = $weibopay->curlPost($this->mas,$setdata);		//模拟表单提交	
			return $result;
		}

		/**
		 *托管代收
		 * @param $uid 用户ID
		 * @param $money 金额
		 * @param $orderno 订单号
		 * @param $code 交易码 1001代收投资金 1002代收还款金
		 * @param $content 描述
		 * @param $returnurl 同步回调地址
		 * @param $notifyurl 异步回调地址
		 * @return $result 返回url
		 */
		function collecttrade($uid,$money,$orderno,$code,$content,$returnurl,$notifyurl)
		{
			$weibopay = new Weibopay();
			$data['service'] 			  = "create_hosting_collect_trade";							//接口名称
			$data['version']			  = $this->version;											//接口版本
			$data['request_time']		  = date('YmdHis');											//请求时间
			$data['partner_id'] 		  = $this->partner_id;										//合作者身份ID
			$data['_input_charset'] 	  = $this->_input_charset;									//网站编码格式
			$data['sign_type'] 			  = $this->sign_type;										//签名方式 MD5
			$data['out_trade_no']         = $orderno; 				//交易订单号
			$data['out_trade_code']		  = $code;													//交易码
			$data['summary']			  = $content;												//摘要
			$data['payer_id']			  = '20151008'.$uid;										//用户ID
			$data['payer_identity_type']  = 'UID';													//用户标识类型 UID
			$data['payer_ip']=get_client_ip();
			$data['pay_method']			  = "online_bank^".$money."^SINAPAY,DEBIT,C";		//支付方式：支付方式^金额^扩展|支付方式^金额^扩展。扩展信息内容以“，”分隔
			$data['return_url']			  = $returnurl;												//同步回调地址
			$data['notify_url']			  = $notifyurl;												//异步回调地址
			$data['extend_param']		  = "channel_black_list^online_bank^binding_pay^quick_pay";	//拓展信息
			ksort($data);
			$data['sign'] 				  = $weibopay->getSignMsg($data,$data['sign_type']);		//计算签名
			$setdata 					  = $weibopay->createcurl_data($data);
			$result						  = $weibopay->curlPost($this->mas,$setdata);//模拟表单提交
			return $result;
		}

		/**
		 *托管代收（公司帐号）
		 * @param $uid 用户ID
		 * @param $money 金额
		 * @param $code 交易码 1001代收投资金 1002代收还款金
		 * @param $content 描述
		 * @param $notifyurl 异步回调地址
		 * @return $result 返回url
		 */
		function collecttradecompany($money,$content)
		{
			$weibopay = new Weibopay();
			$data['service'] 			  = "create_hosting_collect_trade";							//接口名称
			$data['version']			  = $this->version;											//接口版本
			$data['request_time']		  = date('YmdHis');											//请求时间
			$data['partner_id'] 		  = $this->partner_id;										//合作者身份ID
			$data['_input_charset'] 	  = $this->_input_charset;									//网站编码格式
			$data['sign_type'] 			  = $this->sign_type;										//签名方式 MD5
			$data['out_trade_no']         = date('YmdHis').mt_rand( 100000,999999); 				//交易订单号
			$data['out_trade_code']		  = "1001";													//交易码
			$data['summary']			  = $content;												//摘要
			$data['payer_id']			  = $this->email;											//用户ID
			$data['payer_identity_type']  = 'EMAIL';												//用户标识类型 UID
			$data['payer_ip']=get_client_ip();
			$data['pay_method']			  = "balance^".$money."^BASIC";		
			ksort($data);
			$data['sign'] 				  = $weibopay->getSignMsg($data,$data['sign_type']);		//计算签名
			$setdata 					  = $weibopay->createcurl_data($data);
			$result						  = $weibopay->curlPost($this->mas,$setdata);//模拟表单提交
			$rs = $this->checkerror($result);//验证
			return $rs["response_code"];
		}

		/**
		 *托管代付
		 * @param $uid 用户ID
		 * @param $money 金额
		 * @param $orderno 订单号
		 * @param $code 交易码 2001代付借款金 2002代付（本金/收益）金
		 * @param $content 描述
		 * @param $notifyurl 异步回调地址
		 * @return true 提交成功 false 提交失败
		 */
		function paytrade($orderno,$code,$money,$content,$uid,$notifyurl)
		{
			$weibopay = new Weibopay();
			$data['service'] 			  = "create_single_hosting_pay_trade";						//接口名称
			$data['version']			  = $this->version;											//接口版本
			$data['request_time']		  = date('YmdHis');											//请求时间
			$data['user_ip']			  = get_client_ip();												//用户IP地址
			$data['partner_id'] 		  = $this->partner_id;										//合作者身份ID
			$data['_input_charset'] 	  = $this->_input_charset;									//网站编码格式
			$data['sign_type'] 			  = $this->sign_type;										//签名方式 MD5
			$data['out_trade_no']         = $orderno; 												//交易订单号
			$data['out_trade_code']		  = $code;													//交易码
			$data['amount']			  	  = $money;													//金额
			$data['summary']			  = $content;												//摘要
			$data['payee_identity_id']	  = '20151008'.$uid;										//用户ID
			$data['payee_identity_type']  = 'UID';													//用户标识类型 UID
			$data['account_type']		  = "SAVING_POT";											//账户类型
			$data['notify_url']		 	  = $notifyurl;												//异步回调地址
			ksort($data);
			$data['sign'] 				  = $weibopay->getSignMsg($data,$data['sign_type']);		//计算签名
			$setdata 					  = $weibopay->createcurl_data($data);
			$result						  = $weibopay->curlPost($this->mas,$setdata);//模拟表单提交
			$rs = $this->checkerror($result);//验证
			if($rs["response_code"] == "APPLY_SUCCESS"){
				return true;
			}else{
				file_put_contents('sinaerrorlog.txt', "时间：".date("Y-m-d H:i:s").",类型：托管代付，信息:".$rs["response_message"]."\n", FILE_APPEND);
				return false;
			}
		}

		/**
		 *批量托管代付
		 * @param $orderno 订单号
		 * @param $trade_list 交易列表
		 * @return true 提交成功 false 提交失败
		 */
		function batchpaytrade($order_no,$money,$bid,$trade_list,$notify_action)
		{	
			Log::write(var_export($trade_list,true));
			$weibopay = new Weibopay();
			$data['service'] 			  = "create_batch_hosting_pay_trade";						//接口名称
			$data['version']			  = $this->version;											//接口版本
			$data['request_time']		  = date('YmdHis');											//请求时间
			$data['user_ip']			  = get_client_ip();												//用户IP地址
			$data['partner_id'] 		  = $this->partner_id;										//合作者身份ID
			$data['_input_charset'] 	  = $this->_input_charset;									//网站编码格式
			$data['sign_type'] 			  = $this->sign_type;										//签名方式 MD5
			$data['out_pay_no']           =  $order_no; 					//交易订单号
			$data['out_trade_code']		  = '2001';													//交易码 2001代付借款金 2002代付（本金/收益）金
			$data['trade_list']			  = $trade_list;											//交易列表
			$data['notify_method']		  = 'batch_notify';											//通知方式：single_notify: 交易逐笔通知 batch_notify: 批量通知
			$data['notify_url']		 	  = "https://".$_SERVER['HTTP_HOST']."/Home/Sinanotify/".$notify_action;	//异步回调地址
			ksort($data);
			$data['sign'] 				  = $weibopay->getSignMsg($data,$data['sign_type']);		//计算签名
			$setdata 					  = $weibopay->createcurl_data($data);
			$result						  = $weibopay->curlPost($this->mas,$setdata);//模拟表单提交
			$rs = $this->checkerror($result);//验证
			Log::write(var_export($data,true));
			Log::write(var_export($rs,true));
			if($rs["response_code"] == "APPLY_SUCCESS"){
                
				return true;
			}else{
				file_put_contents('sinaerrorlog.txt', "时间：".date("Y-m-d H:i:s").",类型：批量托管代付，信息:".$rs["response_message"]."\n", FILE_APPEND);
				return false;
			}
		}

		/**
		 *托管退款
		 * @param $orderno 订单号
		 * @param $tradeno 需要退款的商户订单号
		 * @param $money 退款金额
		 * @return true 提交成功 false 提交失败
		 */
		function refund($orderno,$tradeno,$money,$uid,$borrow_id)
		{
			$weibopay = new Weibopay();
			$data['service'] 			  = "create_hosting_refund";								//接口名称
			$data['version']			  = $this->version;											//接口版本
			$data['request_time']		  = date('YmdHis');											//请求时间
			$data['user_ip']			  = get_client_ip();												//用户IP地址
			$data['partner_id'] 		  = $this->partner_id;										//合作者身份ID
			$data['_input_charset'] 	  = $this->_input_charset;									//网站编码格式
			$data['sign_type'] 			  = $this->sign_type;										//签名方式 MD5
			$data['out_trade_no']	  	  = $orderno; 												//交易订单号
			$data['orig_outer_trade_no']  = $tradeno; 												//需要退款的商户订单号
			$data['refund_amount']		  = $money;													//退款金额
			$data['summary']			  = '投标失败';												//描述
			$data['notify_url']			  = "https://".$_SERVER['HTTP_HOST']."/Home/Sinanotify/refundnotify";
			ksort($data);
			$data['sign'] 				  = $weibopay->getSignMsg($data,$data['sign_type']);		//计算签名
			$setdata 					  = $weibopay->createcurl_data($data);
			$result						  = $weibopay->curlPost($this->mas,$setdata);//模拟表单提交
			$rs = $this->checkerror($result);//验证
			if($rs["response_code"] == "APPLY_SUCCESS"){
				return true;
			}else{
				file_put_contents('sinaerrorlog.txt', "时间：".date("Y-m-d H:i:s").",类型：托管退款，信息:".$rs["response_message"]."\n", FILE_APPEND);
				return false;
			}
		}

		/**
		 *单笔代付到提现卡交易
		 * @param $orderno 订单号
		 * @param $uid 用户ID
		 * @param $amount 金额
		 * @param $bid 标号
		 * @param $cardid 绑卡的ID
		 * @param  $type 1 默认 2 债权
		 */
		function paytocardtrade($orderno,$uid,$amount,$bid,$cardid,$type=1){
			$weibopay = new Weibopay();
			$data['service'] 			  = "create_single_hosting_pay_to_card_trade";								//接口名称
			$data['version']			  = $this->version;											//接口版本
			$data['request_time']		  = date('YmdHis');											//请求时间
			$data['partner_id'] 		  = $this->partner_id;										//合作者身份ID
			$data['_input_charset'] 	  = $this->_input_charset;									//网站编码格式
			$data['sign_type'] 			  = $this->sign_type;										//签名方式 MD5
			$data['out_trade_no']	  	  = $orderno; 												//交易订单号
			$data['out_trade_code'] 	  = 2001; 													//交易码
			$data['collect_method'] 	  = 'binding_card^20151008'.$uid.",UID,".$cardid;			//收款方式
			$data['amount']				  = $amount;													//金额
			$data['summary']			  = '第'.$bid."号标借款金代付提现卡";						//描述
			$data['goods_id']			  = $bid;													//标号
			if($type==1){
				$data['notify_url']			  = "https://".$_SERVER['HTTP_HOST']."/Home/Sinanotify/paytocardnotify";
			}else{
				$data['notify_url']			  = "https://".$_SERVER['HTTP_HOST']."/Home/Sinanotify/zhaiquan_paytocardnotify";
			}
			$data['user_ip']              = get_client_ip();
			$data['extend_param']		  = 'customNotify^Y';
			ksort($data);
			$data['sign'] 				  = $weibopay->getSignMsg($data,$data['sign_type']);		//计算签名
			$setdata 					  = $weibopay->createcurl_data($data);
			$result						  = $weibopay->curlPost($this->mas,$setdata);//模拟表单提交
			$rs = $this->checkerror($result);//验证
			if($rs["response_code"] == "APPLY_SUCCESS"){
				return true;
			}else{
				file_put_contents('sinaerrorlog.txt', "时间：".date("Y-m-d H:i:s").",类型：代付提现卡，信息:".$rs["response_message"]."\n", FILE_APPEND);
				return false;
			}
		}

		/**
		 *委托代扣重定向
		 * @param $uid 用户ID
		 */
		function handleauthority($uid,$url=NULL){
			$weibopay = new Weibopay();
			$data['service'] 			  = "handle_withhold_authority";								//接口名称
			$data['version']			  = $this->version;											//接口版本
			$data['request_time']		  = date('YmdHis');											//请求时间
			$data['partner_id'] 		  = $this->partner_id;										//合作者身份ID
			$data['_input_charset'] 	  = $this->_input_charset;									//网站编码格式
			$data['sign_type'] 			  = $this->sign_type;										//签名方式 MD5
			$data['identity_id']	  	  = "20151008".$uid;										//用户ID
			$data['identity_type'] 	  	  = "UID";													//用户标识类型 UID
			$data['quota'] 	  	 		  = "++";													//单笔额度
			$data['day_quota'] 	  		  = "++";													//日累计额度
			$data['auth_type_whitelist']  = "ALL";													//代扣授权类型白名单
            if($url !=NULL) $data['return_url']  = "https://".$_SERVER['HTTP_HOST'].$url;            //代扣重定向地址
			ksort($data);
			$data['sign'] 			  	  = $weibopay->getSignMsg($data,$data['sign_type']);		//计算签名
			$setdata 				      = $weibopay->createcurl_data($data);
			$result					      = $weibopay->curlPost($this->mgs,$setdata);		//模拟表单提交
			$rs = $this->checkerror($result);//验证
			if($rs["response_code"] == "APPLY_SUCCESS"){
				return $rs["redirect_url"];
			}else{
				file_put_contents('sinaerrorlog.txt', "时间：".date("Y-m-d H:i:s")."用户ID：".$data["identity_id"].",类型：委托代扣，信息:".$rs["response_message"]."\n", FILE_APPEND);
				return false;
			}
		}

		/**
		 *2.4查看用户是否代扣授权
		 * @param $uid 用户ID
		 */
		function queryauthority($uid){
			$weibopay = new Weibopay();
			$data['service'] 			  = "query_withhold_authority";								//接口名称
			$data['version']			  = $this->version;											//接口版本
			$data['request_time']		  = date('YmdHis');											//请求时间
			$data['partner_id'] 		  = $this->partner_id;										//合作者身份ID
			$data['_input_charset'] 	  = $this->_input_charset;									//网站编码格式
			$data['sign_type'] 			  = $this->sign_type;										//签名方式 MD5
			$data['identity_id']	  	  = "20151008".$uid;										//用户ID
			$data['identity_type'] 	  	  = "UID";													//用户标识类型 UID
			$data['auth_type']  		  = "ALL";													//授权类型
			ksort($data);
			$data['sign'] 			  	  = $weibopay->getSignMsg($data,$data['sign_type']);		//计算签名
			$setdata 				      = $weibopay->createcurl_data($data);
			$result					      = $weibopay->curlPost($this->mgs,$setdata);		//模拟表单提交
			$rs = $this->checkerror($result);//验证
			if($rs["response_code"] == "APPLY_SUCCESS"){
				return $rs["is_withhold_authoity"];
			}else{
				file_put_contents('sinaerrorlog.txt', "时间：".date("Y-m-d H:i:s")."用户ID：".$data["identity_id"].",类型：查看用户是否代扣授权，信息:".$rs["response_message"]."\n", FILE_APPEND);
				return false;
			}
		}

		//验证新浪接口响应信息
		function checkerror($data)
		{
			$weibopay = new Weibopay();
			$deresult = urldecode($data);
			$splitdata = array ();
			$splitdata = json_decode( $deresult, true );
			ksort ($splitdata); // 对签名参数据排序
			if ($weibopay->checkSignMsg ($splitdata,$splitdata["sign_type"]))
			{
				return $splitdata;
			}else{
				return "sing error!" ;
				exit();
			}
		}
	}
?>

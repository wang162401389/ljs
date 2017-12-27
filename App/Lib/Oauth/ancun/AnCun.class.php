<?php 
	/**
	 *安存接口封装
	*/
	import("@.Oauth.ancun.AospClient");
	class AnCun extends Action
	{
		private $apiAddress = null;
		private $partnerKey = null;
		private $secret = null;

		//初始化
		function _initialize(){
			$this->anCunKey 		= C('ANCUN');
			$this->apiAddress 		= $this->anCunKey['apiAddress'];
			$this->partnerKey		= $this->anCunKey['partnerKey'];
			$this->secret			= $this->anCunKey['secret'];
		}

		/**
		 * 用户个人信息保全
		 * @param $data (姓名，平台账号，身份证号，注册时间，认证成功时间)
		 */
		function userInfoSafe($data){
			$aospRequest = new AospRequest();
			$aospRequest->setItemKey('I-0247001');

			//保全数据
			$map = array();
			$map['real_name']	 	= $data['real_name'];						//姓名
			$map['user_name']	 	= $data['user_name'];						//平台账号
			$map['user_phone']	 	= $data['user_phone'];						//手机号
			$map['idcard']		 	= $data['idcard'];							//身份证号码
			$map['reg_time']		= date('Y-m-d H:i:s',$data['reg_time']);	//注册时间

			 //以下是业务数据的用户信息
			$user = array();
			$user["user_truename"]	= $data['real_name'];
			$user["user_idcard"]	= $data['idcard'];
			$user["user_mobile"]	= $data['user_phone'];
			$map["p_user"]			= $user;

			$aospRequest->setData($map);
			$aospClient = new AospClient($this->apiAddress,$this->partnerKey,$this->secret);
			$aospResponse = $aospClient->save($aospRequest);
			return $aospResponse->getData();
		}

		/**
		 *项目信息保全
		 *@param $data(项目名称，标的类型，借款金额，年化率，借款期限，借款用途，起投金额，计息规则，还款方式，发布时间，审核时间，截止时间，计划还款日期，计划还款金额)
		 */
		function projectSafe($data){
			$aospRequest = new AospRequest();
			$aospRequest->setItemKey('I-0247003');

			//保全数据
			$map = array();
			$map['borrow_name']			= $data['borrow_name'];
			$map['product_type']		= $data['product_type'];
			$map['borrow_money']		= $data['borrow_money'];
			$map['borrow_interest_rate']= $data['borrow_interest_rate'];
			$map['borrow_duration']		= $data['borrow_duration'];
			$map['borrow_use']			= $data['borrow_use'];
			$map['borrow_min']			= $data['borrow_min'];
			$map['second_verify_time']	= "复审之日开始计息";
			$map['repayment_type']		= $data['repayment_type'];
			$map['add_time']			= date('Y-m-d H:i:s',$data['add_time']);
			$map['first_verify_time']	= date('Y-m-d H:i:s',$data['first_verify_time']);
			$map['collect_time']		= date('Y-m-d H:i:s',$data['collect_time']);
			$map['deadline']			= date('Y-m-d H:i:s',$data['deadline']);
			$map['repayment_money']		= round($data['repayment_money'],2).'元';

			$aospRequest->addFile($data['src'], $data['content']);

			$aospRequest->setData($map);
			$aospClient = new AospClient($this->apiAddress,$this->partnerKey,$this->secret);
			$aospResponse = $aospClient->save($aospRequest);
			log::write('参数：'.var_export($aospRequest,true));

			log::write('项目保全：'.var_export($aospResponse,true));
			return $aospResponse->getData();
		}

		/**
		 * 充值数据保全
		 * @param $data (链金所账户ID,姓名,身份证号码,订单号,充值金额,操作时间,充值成功时间)
		 */
		function rechargeSafe($data){
			$aospRequest = new AospRequest();
			$aospRequest->setItemKey('I-0247004');

			//保全数据
			$map = array();
			$map['id']			 	= $data['uid'];								//链金所账户ID
			$map['real_name']	 	= $data['real_name'];							//姓名
			$map['idcard']		 	= $data['idcard'];								//身份证号码
			$map['order_no']	 	= $data['order_no'];							//订单号
			$map['money']		 	= $data['money'];								//充值金额
			$map['addtime']		 	= date('Y-m-d H:i:s',$data['addtime']);		//操作时间
			$map['completetime'] 	= date('Y-m-d H:i:s',$data['completetime']);	//充值成功时间

			 //以下是业务数据的用户信息
			$user = array();
			$user["user_truename"]	= $data['real_name'];
			$user["user_idcard"]	= $data['idcard'];
			$user["user_mobile"]	= $data['user_phone'];
			$map["p_user"]			= $user;

			$aospRequest->setData($map);
			$aospClient = new AospClient($this->apiAddress,$this->partnerKey,$this->secret);
			$aospResponse = $aospClient->save($aospRequest);
			return $aospResponse->getData();
		}

		/**
		 * 提现数据保全
		 * @param $data (链金所账户ID,姓名,身份证号码,订单号,提现金额,操作时间,提现成功时间)
		 */
		function withdrawSafe($data){
			$aospRequest = new AospRequest();
			$aospRequest->setItemKey('I-0247005');

			//保全数据
			$map = array();
			$map['id']			 	= $data['uid'];								//链金所账户ID
			$map['real_name']	 	= $data['real_name'];							//姓名
			$map['idcard']		 	= $data['idcard'];								//身份证号码
			$map['order_no']	 	= $data['order_no'];							//订单号
			$map['money']		 	= $data['money'];								//充值金额
			$map['addtime']		 	= date('Y-m-d H:i:s',$data['addtime']);		//操作时间
			$map['completetime'] 	= date('Y-m-d H:i:s',$data['completetime']);	//充值成功时间

			 //以下是业务数据的用户信息
			$user = array();
			$user["user_truename"]	= $data['real_name'];
			$user["user_idcard"]	= $data['idcard'];
			$user["user_mobile"]	= $data['user_phone'];
			$map["p_user"]			= $user;

			$aospRequest->setData($map);
			$aospClient = new AospClient($this->apiAddress,$this->partnerKey,$this->secret);
			$aospResponse = $aospClient->save($aospRequest);
			return $aospResponse->getData();
		}

		/**
		 * 投资交易过程数据保全(1)投资交易过程
		 * @param $data(姓名,手机,身份证,项目名称,标类型,借款金额,年化率,期限,借款用途,起投金额,计息规则,还款方式,投资金额,付款账户,收款账户,流水号,支付成功时间,购买时间)
		 */
		function investSafe1($data){
			$aospRequest = new AospRequest();
			$aospRequest->setItemKey('I-0247002');
			$aospRequest->setFlowNo('X-0478001');

			//保全数据
			$map = array();
			$map['real_name']			= $data['real_name'];							//姓名
			$map['user_phone']			= $data['user_phone'];							//手机号码
			$map['idcard']				= $data['idcard'];								//身份证号码
			$map['borrow_name']			= $data['borrow_name'];							//项目名称
			$map['product_type']		= $data['product_type'];						//标的类型
			$map['borrow_money']		= $data['borrow_money'];						//借款金额
			$map['borrow_interest_rate']= $data['borrow_interest_rate'];				//年化收益率
			$map['borrow_duration']		= $data['borrow_duration'];						//借款期限
			$map['borrow_use']			= $data['borrow_use'];							//借款用途
			$map['borrow_min']			= $data['borrow_min'];							//起投金额
			$map['second_verify_time']	= "复审之日开始计息";							//计息规则
			$map['repayment_type']		= $data['repayment_type'];						//还款方式
			$map['investor_captial']	= $data['investor_captial'];					//投资金额
			$map['investor_uid']		= $data['investor_uid'];						//付款方账户
			$map['borrow_uid']			= $data['borrow_uid'];							//收款方账户
			$map['order_no']	 		= $data['order_no'];							//支付流水号
			$map['addtime']				= date('Y-m-d H:i:s',$data['addtime']);			//购买时间
			$map['completetime'] 		= date('Y-m-d H:i:s',$data['completetime']);	//支付成功时间

			 //以下是业务数据的用户信息
			$user = array();
			$user["user_truename"]		= $data['real_name'];
			$user["user_idcard"]		= $data['idcard'];
			$user["user_mobile"]		= $data['user_phone'];
			$map["p_user"]				= $user;

			$aospRequest->setData($map);
			$aospClient = new AospClient($this->apiAddress,$this->partnerKey,$this->secret);
			$aospResponse = $aospClient->save($aospRequest);
			return $aospResponse->getData();
		}

		/**
		 * 投资交易过程数据保全(2)
		 * @param $data(上一个流程的保全号,实际回款日期,实际回款金额)
		 */
		function investSafe2($data){
			$aospRequest = new AospRequest();
			$aospRequest->setItemKey('I-0247002');
			$aospRequest->setFlowNo('X-0478002');
			$aospRequest->setRecordNo($data['reecordno']);

			//保全数据
			$map = array();
			$map['receive_interest']	= $data['receive_interest'];							//实际回款金额
			$map['repayment_time'] 		= date('Y-m-d H:i:s',$data['repayment_time']);			//实际回款日期

			$aospRequest->setData($map);
			$aospClient = new AospClient($this->apiAddress,$this->partnerKey,$this->secret);
			$aospResponse = $aospClient->save($aospRequest);
			return $aospResponse->getData();
		}

		/**
		 * 投资交易过程数据保全(3)
		 * @param $data(上一个流程的保全号,合同地址,合同描述)
		 */
		function investSafe3($data){
			$aospRequest = new AospRequest();
			$aospRequest->setItemKey('I-0247002');
			$aospRequest->setFlowNo('X-0478003');
			$aospRequest->setRecordNo($data['reecordno']);

			//保全数据
			$aospRequest->addFile($data['src'], $data['content']);

			$aospClient = new AospClient($this->apiAddress,$this->partnerKey,$this->secret);
			$aospResponse = $aospClient->save($aospRequest);
			return $aospResponse;
		}
	}
?>
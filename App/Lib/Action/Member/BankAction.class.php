<?php
// 本类由系统自动生成，仅供测试用途
class BankAction extends MCommonAction {

    public function index(){
    	redirect(bankcard($this->uid,session('xieyi')."://".$_SERVER['HTTP_HOST']."/member"));
		//$this->display();
    }

    public function bank(){
		// $ids = M('members_status')->getFieldByUid($this->uid,'id_status');
		// if($ids==1){
			// $voinfo = M("member_info")->field('idcard,real_name')->find($this->uid);
			// $vobank = M("member_banks")->field(true)->where("uid = {$this->uid} and bank_num !=''")->find();
			// $vobank['bank_province'] = M('area')->getFieldByName("{$vobank['bank_province']}",'id');
			// $vobank['bank_city'] = M('area')->getFieldByName("{$vobank['bank_city']}",'id');

			// $this->assign("voinfo",$voinfo);
			// $this->assign("vobank",$vobank);
			// $this->assign("bank_list",$this->gloconf['BANK_NAME']);
			// $this->assign('edit_bank', $this->glo['edit_bank']);
			// $this->assign("memberinfo", M('members')->find($this->uid)); // 判断企业还是个人
		// 	$cardlist = querycard($this->uid);
		//  	if($cardlist['card_list'] != null){
		// 		$cardinfo = explode('|', $cardlist['card_list']);
		// 		$i=0;
		// 		$card=null;
		// 		foreach ($cardinfo as $c) {
		// 			$card[$i]= explode('^', $c);
		// 			if($card[$i][1] == 'CMB'){
		// 				$card[$i][9]='招商银行';$card[$i][10]='#eb1526';
		// 			}elseif($card[$i][1] == 'SZPAB'){
		// 				$card[$i][9]='平安银行';$card[$i][10]='#e46814';
		// 			}elseif($card[$i][1] == 'ABC'){
		// 				$card[$i][9]='农业银行';$card[$i][10]='#009274';
		// 			}elseif($card[$i][1] == 'ICBC'){
		// 				$card[$i][9]='工商银行';$card[$i][10]='#e60012';
		// 			}elseif($card[$i][1] == 'CCB'){
		// 				$card[$i][9]='建设银行';$card[$i][10]='#4c89e0';
		// 			}elseif($card[$i][1] == 'PSBC'){
		// 				$card[$i][9]='邮政储蓄银行';$card[$i][10]='#20b469';
		// 			}elseif($card[$i][1] == 'COMM'){
		// 				$card[$i][9]='交通银行';$card[$i][10]='#3a5fd9';
		// 			}elseif($card[$i][1] == 'BOC'){
		// 				$card[$i][9]='中国银行';$card[$i][10]='#de5778';
		// 			}elseif($card[$i][1] == 'CEB'){
		// 				$card[$i][9]='光大银行';$card[$i][10]='#7e3791';
		// 			}elseif($card[$i][1] == 'CITIC'){
		// 				$card[$i][9]='中信银行';$card[$i][10]='#de5778';
		// 			}elseif($card[$i][1] == 'SPDB'){
		// 				$card[$i][9]='浦东发展银行';$card[$i][10]='#003b90';
		// 			}elseif($card[$i][1] == 'CMBC'){
		// 				$card[$i][9]='民生银行';$card[$i][10]='#5fad78';
		// 			}elseif($card[$i][1] == 'CIB'){
		// 				$card[$i][9]='兴业银行';$card[$i][10]='#256fbf';
		// 			}elseif($card[$i][1] == 'GDB'){
		// 				$card[$i][9]='广发银行';$card[$i][10]='#ef3e5a';
		// 			}elseif($card[$i][1] == 'BCCB'){
		// 				$card[$i][9]='北京银行';$card[$i][10]='#e44955';
		// 	 		}elseif($card[$i][1] == 'NJCB'){
		// 	 			$card[$i][9]='南京银行';$card[$i][10]='#e44955';
		// 	 		}elseif($card[$i][1] == 'CZB'){
		// 	 			$card[$i][9]='浙商银行';$card[$i][10]='#e4c235';
		// 	 		}elseif($card[$i][1] == 'CBHB'){
		// 	 			$card[$i][9]='渤海银行';$card[$i][10]='#2c6bb4';
		// 	 		}
		// 	 		if($card[$i][4] == 'DEBIT'){$card[$i][4]="借记卡";}else{$card[$i][4]="信用卡";}
		// 	 		$i++;
		// 	 		}
		// 	 		$this->assign("card",$card);
		// 	 		$data['html'] = $this->fetch();
  //          		}else{
  //          			$data['html'] = '<script type="text/javascript">alert("您还未绑定银行卡");window.location.href="'.__APP__.'/member";</script>';
  //          		}
			
		// // }
		// // else  $data['html'] = '<script type="text/javascript">alert("您还未完成身份验证，请先进行实名认证");window.location.href="'.__APP__.'/member/verify?id=1#fragment-3";</script>';

		// exit(json_encode($data));
		redirect(bankcard($this->uid,session('xieyi')."://".$_SERVER['HTTP_HOST']."/member"));
    }
	public function bindbank(){

	    $bank_info = M('member_banks')->field("uid, bank_num")->where("uid=".$this->uid)->find();

	    $usertype = M('members')->find($this->uid);
	    //查询是否企业用户
	    if($usertype['user_regtype'] == 2){
	    	$data['companyname']    = text($_POST['companyname']);
			$card_attribute = "B";
	    }else{
			$card_attribute = "C";
		}

		!$bank_info['uid'] && $data['uid'] = $this->uid;
		$data['bank_num']       = text($_POST['account']);
		$bankname = explode("_",text($_POST['bankname']));
		$data['bank_name']      = $bankname[0];
		$data['bank_address']   = text($_POST['bankaddress']);
		$data['bank_province']  = text($_POST['province']);
		$data['bank_city']      = text($_POST['cityName']);
		$data['add_ip']         = get_client_ip();
		$data['add_time']       = time();
		if($bank_info['uid']){
			/////////////////////新增银行卡修改锁定开关 开始 20130510 fans///////////////////////////
			if(intval($this->glo['edit_bank'])!= 1 && $bank_info['bank_num']){
				ajaxmsg("为了您的帐户资金安全，银行卡已锁定，如需修改，请联系客服", 0 );
			}
			/////////////////////新增银行卡修改锁定开关 结束 20130510 fans///////////////////////////
			$old = text($_POST['oldaccount']);
			if($bank_info['bank_num'] && $old <> $bank_info['bank_num']) ajaxmsg('原银卡号不对',0);
			$newid = M('member_banks')->where("uid=".$this->uid)->save($data);
			$cardid = M('member_banks')->where("uid=".$this->uid)->field('card_id')->find();
			$unbinddata['cardid'] = $cardid['card_id'];
			$unbinddata['identity_id'] = $this->uid;
			$this->unbindingcard($unbinddata);
		}else{
			$newid = M('member_banks')->add($data);
		}
		if($newid){
			//生成订单请求号
			$time = explode ( " ", microtime () );
			$time = $time [1] . ($time [0] * 1000);
			$time2 = explode ( ".", $time );
			$time = $time2 [0];
			$sina['request_no'] = $time;//订单请求号
			$sina['identity_id'] = $this->uid;//用户ID
			$sina['bank_code'] = $bankname[1];//银行编号
			$sina['bank_account_no'] = $data['bank_num'];//银行卡号
			$sina['card_attribute'] = $card_attribute;//卡属性 C 对私 B 对公
			$sina['province'] = $data['bank_province'];//省份
			$sina['city'] = $data['bank_city'];//城市
			$sina['bank_branch'] = $data['bank_address'];//开户行
			$result = $this->bindingBankCard($sina);
			if($result["response_code"] != 'APPLY_SUCCESS' ){
				$this->error($result["response_code"].":".$result["response_message"]);
			}else{
				$rs['card_id'] = $result["card_id"];
				M('member_banks')->where("uid=".$this->uid)->save($rs);
			}
			
			MTip('chk2',$this->uid);
			ajaxmsg();
		}
		else ajaxmsg('操作失败，请重试',0);
	}
	
	//解绑银行卡
		public function unbindingcard(){
			$rs = unbindcard($this->uid,$_REQUEST['cardid']);
			if($rs['response_code'] == "APPLY_SUCCESS"){
				$this->redirect('/member');
			}elseif( $rs['response_code'] =='UNBINDING_SECURITY_CARD_FORBIDDING'){
				$this->error($rs['response_message']);
			}else{
				$this->error('解绑失败');
			}

		}

	
	public function getarea(){
		$rid = intval($_GET['rid']);
		if(empty($rid)) return;
		$map['reid'] = $rid;
		// var_export($alist);
		$alist = M('area')->field('id,name')->order('sort_order DESC')->where($map)->select();
		if(count($alist)===0){
			$str="<option value=''>--该地区下无下级地区--</option>\r\n";
		}else{
			foreach($alist as $v){
				$str.="<option value='{$v['id']}'>{$v['name']}</option>\r\n";
			}
		}
		$data['option'] = $str;
		$res = json_encode($data);
		echo $res;
	}	

}
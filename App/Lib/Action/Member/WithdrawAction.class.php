<?php

class WithdrawAction extends MCommonAction {

    public function index(){
    	
		$this->display();
    }

    public function withdraw(){
        $this->del_mem_cach();
		$pre = C('DB_PREFIX');
		$field = "m.user_name,m.user_phone,m.user_regtype,m.is_vip,i.real_name";
		$vo = M('members m')->field($field)->join("{$pre}member_info i on i.uid = m.id")->where("m.id={$this->uid}")->find();
		if($vo['user_regtype'] == 2){
			$company = M('members_company')->where("uid={$this->uid}")->field('company_name')->find();
			$vo['real_name']=$company['company_name'];
		}
		$txfee = explode( "|", $this->glo['tx_fee']);
		$fee[0]= $txfee[0];	//提现手续费
		$txmoney = explode( "-", $txfee[1]); //提现金额范围
		$fee[1] = $txmoney[0];	//最小提现金额
		if($vo['user_regtype']==1){
			$fee[2] = $txmoney[1];	//最大提现金额(万元)
		}else{
			$fee[2] = $txfee[2];
		}
		

		
           //防止表单重复提交
        $_SESSION['phpcode']=time().mt_rand(1,9);
        $vo['phpcode']=$_SESSION['phpcode'];

        $_SESSION['token1']=time().mt_rand(1,9);
        $vo['token1']=$_SESSION['token1'];
        $_SESSION['token2']=time().mt_rand(1,9);
        $vo['token2']=$_SESSION['token2'];

		$this->assign( "fee",$fee);
		$this->assign( "vo",$vo);
		$data['html'] = $this->fetch();
		exit(json_encode($data));
    }
	
	public function checkfee(){
		if($_POST["token"] != $_SESSION['token2'] || $_POST["token"] == "" ) exit;
		$vo = M('members')->field('is_vip,user_regtype')->where("id={$this->uid}")->find();
    	M('withdrawlog')->where("uid={$this->uid} AND fee_status=0")->delete();
    	if($vo['is_vip'] == 0){
			$fee_null = M('withdrawlog')->where("uid={$this->uid} AND fee_status = 1 AND money_orderno is null")->field("fee_orderno,fee")->select();
			if($fee_null != null){
				foreach($fee_null as $n){
					//代收撤销
						$data1['uid'] = $this->uid;
						$data1['money'] = $n['fee'];
						$data1["orderno"] = $n['fee_orderno'];
						$rs1 = sinafeecancel($data1);
						if($rs1["response_code"] == "APPLY_SUCCESS"){
							M('withdrawlog')->where('uid='.$this->uid .' AND fee_orderno ="'.$n['fee_orderno'].'"')->delete();
						}
				}
			}
			
    		$fee_list = M('withdrawlog')->where("uid={$this->uid} AND fee_status = 1")->field('money_orderno')->select();
    		$j=0;
    		$flist = null;
    		foreach ($fee_list as $f) {
   				$flist[$j] = $f['money_orderno'];
   				$j++;
    		}

    		$info['uid']=$this->uid;
			$info['type']=2;
			$info['status']= array('in',array(2,4));
			$starttime = strtotime(date('Ym01',time())."000000");
			$endtime = strtotime(date("Ymd")."235959");
			$info['addtime']=array('between',array($starttime,$endtime));
			$withdrawlist = M("sinalog")->where($info)->field("order_no")->select();
			$orderno_list=null;
			$i=0;
			foreach ($withdrawlist as $list) {
				$orderno_list[$i] = $list["order_no"];
				$i++;
			}
    		//$withdrawlist[0] = checkwithdraw($this->uid,$vo['user_regtype']);
    // 		$itemtotal = ceil($withdrawlist['total_item']/20);
    // 		$a = 1;
    // 		if($itemtotal>1){
    // 			for ($b=1; $b <= $itemtotal; $b++) { 
    // 				$withdrawlist[$a] = checkwithdraw($this->uid,$vo['user_regtype'],($b+1));
    // 				$a++;
    // 			}
    // 		}
    // 		$i = 0;
    // 		foreach ($withdrawlist as $w) {
    // 			if($w['withdraw_list'] != null){
				// 	$withdrawinfo = explode('|', $w['withdraw_list']);
				// 	$i = 0;
				// 	$list = null;
				// 	$orderno_list=null;
				// 	foreach ($withdrawinfo as $ww) {
				// 		$list[$i]= explode('^', $ww);
				// 		if($list[$i][2] == 'SUCCESS' || $list[$i][2] == 'PROCESSING'){
				// 			$orderno_list[$i] = $list[$i][0];
				// 		}
				// 		$i++;
				// 	}
				// }
    // 		}
    		
			$different_list = array_diff($flist,$orderno_list);
			if(count($different_list)>0){
				foreach ($different_list as $d) {
					if($d==null || $d == ''){
						$fee_no = M('withdrawlog')->where("uid={$this->uid} AND money_orderno is null")->field("fee_orderno")->find();
					}else{
						$fee_no = M('withdrawlog')->where('uid='.$this->uid .' AND money_orderno = "'.$d.'"')->field("fee_orderno")->find();
					}
					//代收撤销
					$data['uid'] = $this->uid;
					$data['money'] = $money;
					$data["orderno"] = $fee_no['fee_orderno'];
					$rs = sinafeecancel($data);
					if($rs["response_code"] == "APPLY_SUCCESS"){
						M('withdrawlog')->where('uid='.$this->uid .' AND fee_orderno ="'.$fee_no['fee_orderno'].'"')->delete();
					}
				}
			}
    	}
	}
    public function output(){
    	if($_POST["token"] != $_SESSION['token1'] || $_POST["token"] == "" ) exit;
    	$pre = C('DB_PREFIX');
		$vo = M('members')->field("user_regtype,is_vip")->where("id={$this->uid}")->find();
		
		$txfee = explode( "|", $this->glo['tx_fee']);
		$fee[0]= $txfee[0];	//提现手续费
		$txmoney = explode( "-", $txfee[1]); //提现金额范围
		$fee[1] = $txmoney[0];	//最小提现金额
		//$fee[2] = $txmoney[1];	//最大提现金额(万元)
         
        //直接调取新浪余额
        if($vo['user_regtype'] == 1){
        	$fee[2] = $txmoney[1];	//最大提现金额(万元)
			$vo['all_money']=number_format( querysaving($this->uid), 2, ".", "" );
		}else{
			$fee[2] = $txfee[2];	//最大提现金额(万元)
			$vo['all_money']=number_format( querybalance($this->uid), 2, ".", "" );
		}
		//$withdrawlist = checkwithdraw($this->uid,$vo['user_regtype']);
		$totalnum = 0;
		$info['uid']=$this->uid;
		$info['type']=2;
		$info['status']= array('in',array(2,4));
		$starttime = strtotime(date('Ym01',time())."000000");
		$endtime = strtotime(date("Ymd")."235959");
		$info['addtime']=array('between',array($starttime,$endtime));
		$totalnum = M("sinalog")->where($info)->count();
		// $withdrawlist[0] = checkwithdraw($this->uid,$vo['user_regtype']);
  //   	$itemtotal = ceil($withdrawlist['total_item']/20);
  //   	$a = 1;
  //   	if($itemtotal>1){
  //   		for ($b=1; $b <= $itemtotal; $b++) { 
  //   			$withdrawlist[$a] = checkwithdraw($this->uid,$vo['user_regtype'],($b+1));
  //   			$a++;
  //   		}
  //   	}
  //   	$i = 0;
  //   	foreach ($withdrawlist as $w) {
  //   		if($w['withdraw_list'] != null){
		// 		$withdrawinfo = explode('|', $w['withdraw_list']);
		// 		$i = 0;
		// 		$list = null;
		// 		$orderno_list=null;
		// 		foreach ($withdrawinfo as $ww) {
		// 			$list[$i]= explode('^', $ww);
		// 			if($list[$i][2] == 'SUCCESS' || $list[$i][2] == 'PROCESSING'){
		// 				$totalnum++;
		// 			}
		// 			$i++;
		// 		}
		// 	}
  //   	}
    	session("num",$totalnum);
		if($totalnum>=3 && $vo['is_vip'] == 0){
			$num = 0;
			$vo['fee_txmoney'] = '0.00';
		}elseif($vo['is_vip'] == 1){
			$num = "∞";
			$vo['fee_txmoney'] = $vo['all_money'];
		}else{
			$num = 3-$totalnum;
			//免费提现额度
			$starttime=strtotime(date('Ymd',time()).'-14 day');
			$endtime=strtotime(date('Ymd',time()).'+1 day -1s');
			$wmap['i.investor_uid'] = $this->uid;
			$wmap['i.add_time'] = array("between","{$starttime},{$endtime}");
			$wmap['d.repayment_time'] = 0;
			$notbackmoney = M('borrow_investor i')->join('lzh_investor_detail d on d.invest_id = i.id')->where($wmap)->sum('d.capital');
			$map['uid'] = $this->uid;
			$map['completetime'] = array("between","{$starttime},{$endtime}");
			$map['type'] = 1;
			$map['status'] = 2;
			$czmoney = M('sinalog')->where($map)->sum('money');
			$vo['fee_txmoney'] = number_format($vo['all_money']+$notbackmoney-$czmoney, 2, '.', '');
			if($vo['fee_txmoney']<0){
				$vo['fee_txmoney']="0.00";
			}
		}
		$vo["num"] = $num;
		$this->ajaxReturn($vo);
    }
	
	public function validate(){
		$pre = C('DB_PREFIX');
		$withdraw_money = floatval($_POST['amount']);
		$vo = M('members')->field('user_regtype,is_vip')->where("id={$this->uid}")->find();
		if(!is_array($vo)) ajaxmsg("",0);
       
		$txfee = explode( "|", $this->glo['tx_fee']);
		$fee[0]= $txfee[0];	//提现手续费
		$txmoney = explode( "-", $txfee[1]); //提现金额范围
		$fee[1] = $txmoney[0];	//最小提现金额

		if($vo['user_regtype'] == 1){
			$fee[2] = $txmoney[1]*10000; //个人单笔最大提现金额
        	$sinamoney = querysaving($this->uid);
    	}else{
    		$fee[2] = $txfee[2]*10000;	//企业单笔最大提现金额
    		$sinamoney = querybalance($this->uid);
    	}
		if($sinamoney<$withdraw_money) ajaxmsg("提现金额大于帐户余额",2);
		if($withdraw_money<$fee[1] ||$withdraw_money>$fee[2]) ajaxmsg("单笔提现金额限制为{$fee[1]}-{$fee[2]}元",2);
		$totalnum = session("num");

		//计算提现手续费
		if($vo['user_regtype'] == 1 && $vo['is_vip'] == 0){
			//个人提现手续费
			if($totalnum>=3){
				$fee1 = $withdraw_money*$fee[0];
				if($fee1<2){
					$fee1="0.00";
				}else{
					$fee1 = number_format($fee1,2,'.','');
				}
			}else{
				//免费提现额度
				$starttime=strtotime(date('Ymd',time()).'-14 day');
				$endtime=strtotime(date('Ymd',time()).'+1 day -1s');
				$wmap['i.investor_uid'] = $this->uid;
				$wmap['i.add_time'] = array("between","{$starttime},{$endtime}");
				$wmap['d.repayment_time'] = 0;
				$notbackmoney = M('borrow_investor i')->join('lzh_investor_detail d on d.invest_id = i.id')->where($wmap)->sum('d.capital');
				$map['uid'] = $this->uid;
				$map['completetime'] = array("between","{$starttime},{$endtime}");
				$map['type'] = 1;
				$map['status'] = 2;
				$czmoney = M('sinalog')->where($map)->sum('money');
				$fee_txmoney = number_format($sinamoney+$notbackmoney-$czmoney, 2, '.', '');
				if($fee_txmoney<0){$fee_txmoney=0;}
				if($withdraw_money<$fee_txmoney){
					$fee1 = 0;
				}else{
					$fee1 = ($withdraw_money-$fee_txmoney) * $fee[0];
					if($fee1<2){
						$fee1="0.00";
					}else{
						$fee1 = number_format($fee1,2,'.','');
					}
				}
			}
		}elseif($vo['is_vip'] == 1){
			//借款端不收取手续费
			$fee1 = 0;
		}else{
			//企业提现手续费
			$fee1 = $withdraw_money* 0;
			$fee1 = number_format($fee1,2,'.','');
			if($fee1 > 200){
				$fee1 = number_format(200,2,'.','');
			}
		}

		if($fee1 != 0 && $vo['user_regtype'] == 1 && $vo['is_vip'] == 0){
			$message = "您的免费提现额度或次数已用完，需预先支付手续费{$fee1}元才可提现，确认提现吗？";
		}elseif($fee1 != 0 && $vo['user_regtype'] == 2 && $vo['is_vip'] == 0){
			$message = "您的本次提现金额为{$withdraw_money}元，将由新浪支付收取{$fee1}元手续费，确认提现吗？ ";
		}else{
			$message = "您的提现金额为{$withdraw_money}元，确认提现吗？";
		}
		
		
		ajaxmsg("{$message}",1);
	}
	
	public function actwithdraw(){
		//防止重复提交
		$phpcode1=$_POST['phpcode'];
		$phpcode2=$_SESSION['phpcode'];
		if($phpcode1!=$phpcode2){			
			ajaxmsg("你已经提现过，重复申请需要刷新页面",2);
			exit;
		}

		$pre = C('DB_PREFIX');
		$withdraw_money = floatval($_POST['amount']);
		$vo = M('members')->field('user_regtype,is_vip')->where("id={$this->uid}")->find();
		if(!is_array($vo)) ajaxmsg("",0);
       
		$txfee = explode( "|", $this->glo['tx_fee']);
		$fee[0]= $txfee[0];	//提现手续费
		$txmoney = explode( "-", $txfee[1]); //提现金额范围
		$fee[1] = $txmoney[0];	//最小提现金额

		if($vo['user_regtype'] == 1){
			$fee[2] = $txmoney[1]*10000; //个人单笔最大提现金额
        	$sinamoney = querysaving($this->uid);
    	}else{
    		$fee[2] = $txfee[2]*10000;	//企业单笔最大提现金额
    		$sinamoney = querybalance($this->uid);
    	}
		if($sinamoney<$withdraw_money) ajaxmsg("提现金额大于帐户余额",2);
		if($withdraw_money<$fee[1] ||$withdraw_money>$fee[2]) ajaxmsg("单笔提现金额限制为{$fee[1]}-{$fee[2]}元",2);
		$totalnum = session("num");
	
		//计算提现手续费
		if($vo['user_regtype'] == 1 && $vo['is_vip'] == 0){
			//个人提现手续费
			if($totalnum>=3){
				$fee1 = $withdraw_money*$fee[0];
				if($fee1<2){
					$fee1="0.00";
				}else{
					$fee1 = number_format($fee1,2,'.','');
				}
			}else{
				//免费提现额度
				$starttime=strtotime(date('Ymd',time()).'-14 day');
				$endtime=strtotime(date('Ymd',time()).'+1 day -1s');
				$wmap['i.investor_uid'] = $this->uid;
				$wmap['i.add_time'] = array("between","{$starttime},{$endtime}");
				$wmap['d.repayment_time'] = 0;
				$notbackmoney = M('borrow_investor i')->join('lzh_investor_detail d on d.invest_id = i.id')->where($wmap)->sum('d.capital');
				$map['uid'] = $this->uid;
				$map['completetime'] = array("between","{$starttime},{$endtime}");
				$map['type'] = 1;
				$map['status'] = 2;
				$czmoney = M('sinalog')->where($map)->sum('money');
				$fee_txmoney = number_format($sinamoney+$notbackmoney-$czmoney, 2, '.', '');
				if($fee_txmoney<0){$fee_txmoney=0;}
				if($withdraw_money<$fee_txmoney){
					$fee1 = 0;
				}else{
					$fee1 = ($withdraw_money-$fee_txmoney) * $fee[0];
					if($fee1<2){
						$fee1="0.00";
					}else{
						$fee1 = number_format($fee1,2,'.','');
					}
				}
			}
		}elseif($vo['is_vip'] == 1){
			//借款端不收取手续费
			$fee1 = 0;
		}else{
			//企业提现手续费
			$fee1 = $withdraw_money* 0;
			$fee1 = number_format($fee1,2,'.','');
			if($fee1 > 200){
				$fee1 = number_format(200,2,'.','');
			}
		}

		if($fee1 > 0){
			//收取手续费
			$money = $sinamoney - $withdraw_money - $fee1;
			if($money < 0){
				//提现金额+手续费 大于 余额 在提现金额扣取
				$result = $this->takesina($fee1,$withdraw_money-$fee1,$vo['user_regtype']);
			}else{
				//提现金额+手续费 小于 余额 在余额扣取
				$result = $this->takesina($fee1,$withdraw_money,$vo['user_regtype']);
			}
		}else{
			//不收手续费
			$result = $this->takesina($fee1,$withdraw_money,$vo['user_regtype']);
		}
		$this->ajaxReturn($result,'提现',3);
	}
	
	// public function backwithdraw(){
	// 	$id = intval($_GET['id']);
	// 	$map['withdraw_status'] = 0;
	// 	$map['uid'] = $this->uid;
	// 	$map['id'] = $id;
	// 	$vo = M('member_withdraw')->where($map)->find();
	// 	if(!is_array($vo)) ajaxmsg('',0);
	// 	///////////////////////////////////////////////
	// 	$field = "(mm.account_money+mm.back_money) all_money,mm.account_money,mm.back_money";
	// 	$m = M('member_money mm')->field($field)->where("mm.uid={$this->uid}")->find();
	// 	////////////////////////////////////////////////////
	// 	$newid = M('member_withdraw')->where($map)->delete();
	// 	if($newid){
	// 		$res = memberMoneyLog($this->uid,5,$vo['withdraw_money'],"撤消提现",'0','@网站管理员@');
	// 	}
	// 	if($res) ajaxmsg();
	// 	else ajaxmsg("",0);
	// }

	
	
	
	
	//操作新浪提现
	/**
		$fee 提现手续费
		$withdraw 提现金额
	*/
	public function takesina($fee,$withdraw,$utype){
		$sina['uid'] = $this->uid;
		$sina['withdraw'] = $withdraw;
		$sina['phone'] = $_REQUEST['phone'];
		if($utype==1){
			$sina['fee'] = getfloatvalue($fee,2);
			$sina['account_type'] = 'SAVING_POT';
		}else{
			$sina['user_fee'] = $fee;
			$sina['account_type'] = 'BASIC';
		}
		if($fee > "0.00" && $utype==1){
			return sinafreecollecttrade($sina);
		}else{
			return sinawithdraw($sina);
		}
	}

	//手续费收取完成跳转提现页
	public function sinawithdrawfee(){
		if($_REQUEST['fee_orderno'] != null){
			$sina['fee_orderno'] = $_REQUEST['fee_orderno'];
		}
		$sina['account_type'] = 'SAVING_POT';
		$sina['uid'] = $this->uid;
		$sina['withdraw'] = $_REQUEST['withdraw'];
		$sina['phone'] = "no";
		echo sinawithdraw($sina);
	}

	//提现完成返回页面
	public function sinareturn(){
		$this->success("您的提现正在处理中","/member");
	}

	//提现记录
    public function withdrawlog(){
        $pagesize = 20;
        $page = 1;
        if($_GET['page']>1){
            $page = $_GET['page'];
        }
        $start = ($page-1)*$pagesize;
        $where["uid"]=$this->uid;
        $where["type"]=array("in","2,14");
        $mywhere=array();
        if($_GET['start_time']){
            $mywhere[]=array("egt",strtotime($_GET['start_time']."000000"));
        }
        if($_GET['end_time']){
            $mywhere[]=array("elt",strtotime($_GET['end_time']."235959"));
        }
        if(count($mywhere)){
            $where['addtime']=$mywhere;
        }
        $limit=$start.",".$pagesize;
        $withdrawlist = M("sinalog")->where($where)->order("addtime desc")->limit($limit)->select();
        $count = M("sinalog")->where($where)->count();
        $totalpage = ceil($count/$pagesize);
        $i = $start;
        $list = null;
        foreach ($withdrawlist as $l) {
            $list[$i][1] = $l["money"];
            $list[$i][5] = $i+1;
            $list[$i][3] = date("Y-m-d H:i:s",$l["addtime"]);
            if($l["status"] == 2){
                $list[$i][2] = "提现成功";
            }elseif($l["status"] == 3){
                $list[$i][2] = "提现失败";
            }elseif($l["status"] == 4){
                $list[$i][2] = "处理中";
            }elseif($l["status"] == 1){
                $list[$i][2] = "未提现";
            }
            $i++;
        }
        $this->assign("list",$list);
        $this->assign("page",$page);
        $this->assign("total_item",$totalpage);
        $data['html'] = $this->fetch();
        exit(json_encode($data));
    }

}
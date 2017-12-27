<?php
class DoubleactivityAction extends ACommonAction{
    
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
	    
	    $start_time = strtotime("2016-12-13 00:00:00");
	    $end_time = strtotime("2017-01-18 23:59:59");
	    $where1["add_time"] = array("between",$start_time.",".$end_time);

	    $member_list = M("christmas_have ch")->join("lzh_members m ON m.id = ch.uid")->join("lzh_member_info mi ON mi.uid = m.id")->where($where)->field("m.id,mi.real_name,m.user_phone,m.reg_time")->group("m.id")->select();

	    foreach ($member_list as $key => $value) {
			$xu_count = M("christmas_number")->where(array("uid"=>$value["id"]))->count();
			$where1["investor_uid"] = $value["id"];
			$investor_money = M("borrow_investor")->where($where1)->SUM("investor_capital");
			$member_list[$key]["xu_count"] = $xu_count;
			$gift = M("christmas_have ch")->join("lzh_christmas_gift cg ON cg.id=ch.gift_id")->where(array("ch.uid"=>$value["id"]))->field("GROUP_CONCAT(cg.gift_name) as gift")->find();
			$member_list[$key]["gift"] = $gift["gift"];
			$member_list[$key]["investor_money"] = $investor_money;
			$member_list[$key]["no_xu"] =  floor($investor_money/1000)-$xu_count;

	    }
	    
	    $fan_count = M("christmas_have")->where("gift_id = 1")->count();
	    $yifan_count = M("christmas_have")->where("gift_id = 1 and is_send = 1")->count();

	    $fan = M("christmas_have")->where("gift_id = 1")->select();
	    $fan_money = 0;
	    foreach ($fan as $key => $value) {
	    	$where1["investor_uid"] = $value["uid"];
	    	$investor_money = M("borrow_investor")->where($where1)->SUM("investor_capital");
	    	$fan_money += round(($investor_money * 0.001),2);
	    }
	    $this->assign("fan_count",$fan_count);
	    $this->assign("fan_money",$fan_money);
	    if(time() >= $end_time && $yifan_count == 0){
	    	$this->assign("is_fan",1);
	    }elseif(time() < $end_time && $yifan_count == 0){
	    	$this->assign("is_fan",0);
	    }else{
	    	$this->assign("is_fan",2);
	    }

	    if($_REQUEST['execl'] == "execl"){
	        $ispage = 0;
	    }else{
            import("ORG.Util.PageFilter");
            $p = new PageFilter(count($countlist), $search, C('ADMIN_PAGE_SIZE'));
            $page = $p->show();
            $limit = "{$p->firstRow},{$p->listRows}";
            $sql .= "limit $limit";
	    }
	    
	    if($ispage == 0){
	        import("ORG.Io.Excel");
	        $row = array();
	        $row[0] = array('用户ID','姓名','手机号','注册时间','活动期间总投资额','已抽奖次数','未使用抽奖次数','获得奖品');
	        $i = 1;
	        foreach($member_list as $v) {
	            $row[$i]['uid'] = $v['id'];
	            $row[$i]['real_name'] = $v['real_name'];
	            $row[$i]["cell_phone"] = $v["user_phone"];
	            $row[$i]["reg_time"] = date("Y-m-d H:i:s",$v["reg_time"]);
	            $row[$i]["totalmoney"] = $v["investor_money"];
	            $row[$i]["prizedrawcount"] = $v["xu_count"];
	            $row[$i]['unused_count'] = $v['no_xu'];
	            $row[$i]['gift_name_str'] = $v['gift'];
	            $i++;
	        }
	        $xls = new Excel_XML('UTF-8', false, 'doubleactivity');
	        $xls->addArray($row);
	        $xls->generateXML("doubleactivity");
	        exit;
	    }


	    $this->assign("xaction", "index");
	    $this->assign('pagebar', $page);
	    $this->assign("list", $member_list);
	    $search["execl"] = "execl";
	    $this->assign("query", http_build_query($search));
	    $this->display();
	}
	
	public function shuangdanfan(){
		$start_time = strtotime("2016-12-13 00:00:00");
	    $end_time = strtotime("2017-01-18 23:59:59");
	    $where1["add_time"] = array("between",$start_time.",".$end_time);
	    $fan_count = M("christmas_have")->where("gift_id = 1 and is_send = 0")->count();
	    $fan = M("christmas_have")->where("gift_id = 1 and is_send = 0")->select();
	    $fan_list = array();
	    $fan_money = 0;
	    foreach ($fan as $key => $value) {
	    	$where1["investor_uid"] = $value["uid"];
	    	$investor_money = M("borrow_investor")->where($where1)->SUM("investor_capital");
	    	$fan_money += round(($investor_money * 0.001),2);
	    	$fan_list[$key]["uid"] = $value["uid"];
	    	$fan_list[$key]["fan_money"] = round(($investor_money * 0.001),2);
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
		$data['summary']			  =	"双蛋活动投资返现总额";				//摘要
		$data['payer_id']	  		  = $payConfig['sinapay']['email'];							//付款人邮箱
		$data['payer_identity_type']  = 'EMAIL';												//ID类型
		$data['payer_ip']=get_client_ip();
		$data['pay_method']			  = "balance^".$fan_money."^BASIC";								//支付方式：支付方式^金额^扩展|支付方式^金额^扩展。扩展信息内容以“，”分隔
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
			foreach ($fan_list as $key => $value) {
				$utype = M("members")->where("id={$value['uid']}")->field("user_regtype")->find();
				if($utype['user_regtype']==1){
					$account_type = 'SAVING_POT';
				}else{
					$account_type = 'BASIC';
				}
				if($i < 200){
					if($k === 0){
						$trade_list[$j] = date('YmdHis').mt_rand( 100000,999999).'~20151008'.$value['uid'].'~UID~'.$account_type.'~'.$value['fan_money'].'~~活动投资返现';
						$k++;
					}else{
						$trade_list[$j] .= '$'.date('YmdHis').mt_rand( 100000,999999).'~20151008'.$value['uid'].'~UID~'.$account_type.'~'.$value['fan_money'].'~~活动投资返现';
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
			 M("christmas_have")->where("gift_id = 1 and is_send = 0")->save(array("is_send"=>1));
			$this->ajaxReturn("success");
		}else{
			$this->ajaxReturn($rs);
		}
	}
	public function giftsetting(){
        $list = M("christmas_gift cg")
        	  ->join("lzh_christmas_have ch ON cg.id = ch.gift_id")
        	  ->field("cg.gift_set_no,cg.gift_name,cg.is_open,COUNT(ch.gift_set_no) AS kai_count")
        	  ->group("cg.gift_set_no")
        	  ->select();
        $this->assign('list',$list);
        $this->display();
	}
	
	public function changestatus(){
	    $is_open = abs($_GET['is_open'] - 1);
	    $id = $_GET['gift_set_no'];
	    $up_data['is_open'] = $is_open;
	    $res = M('christmas_gift')->where(array('gift_set_no' => $id))->save($up_data);
	    $gift = M('christmas_gift')->where(array('gift_set_no' => $id,"have_uid"=>0,"send_number"=>array("gt",0)))->find();
	    if($gift!=null){
		    $dataname = C('DB_NAME');
			$db_host = C('DB_HOST');
			$db_user = C('DB_USER');
			$db_pwd = C('DB_PWD');
			$bdb = new PDO('mysql:host='.$db_host.';dbname='.$dataname.'', ''.$db_user.'', ''.$db_pwd.'');
			$bdb->beginTransaction();
			$sql1 ="SELECT suo FROM lzh_christmas_gift_lock WHERE id = ? FOR UPDATE";
			$stmt1 = $bdb->prepare($sql1);
			$stmt1->bindParam(1, $gift_id);    //绑定第一个参数值
			$stmt1->execute();
			$christmas = M('christmas_gift');
			$christmas->startTrans();
			//开奖
			if($is_open==0){
				$lucksum = M("christmas_number")->where(array("gift_id"=>$gift["id"]))->sum("xu_number");
				$lucyavg = $lucksum/$gift["send_number"];
				$lucklist = M("christmas_number")->where(array("gift_id"=>$gift["id"]))->select();
				foreach ($lucklist as $key => $value) {
					$lucky_data["number_poor"] = abs($lucyavg-$value["xu_number"]);
					M("christmas_number")->where(array("xu_number"=>$value["xu_number"],"gift_id"=>$gift["id"]))->save($lucky_data);
				}
				$luck_usr = M("christmas_number")->where(array("gift_id"=>$gift["id"]))->order("number_poor ASC")->find();
				M("christmas_number")->where(array("xu_number"=>$luck_usr["xu_number"],"gift_id"=>$gift["id"]))->save(array("is_zhong"=>1));
				$have_data["uid"] = $luck_usr["uid"];
				$have_data["gift_no"] = $gift["gift_no"];
				$have_data["gift_id"] = $gift["id"];
				$have_data["have_number"] = $luck_usr["xu_number"];
				$have_data["gift_set_no"] = $gift["gift_set_no"];
				$have_data["is_xu"] = 1;
				M("christmas_have")->add($have_data);
				$gift_up["have_uid"] = $luck_usr["uid"];
				$gift_up["have_number"] = $luck_usr["xu_number"];
				$gift_up["have_poor"] = $luck_usr["number_poor"];
				$gift_up["avg_number"] = $lucyavg;
				M("christmas_gift")->where(array("id"=>$gift["id"]))->save($gift_up);
				$user = M("members")->where("uid=".$luck_usr["uid"])->find();
				$smscontent = "尊敬的链金所用户！恭喜您获得链金所新年许愿活动第".$gift["gift_no"]."期".$gift["gift_name"]."1台，您可登录活动页查看礼物，如有疑问请与客服中心联系400-6626-985。";
				sendsms($user["user_phone"],$smscontent);
				$new_gift["gift_set_no"] = $gift["gift_set_no"];
				$new_gift["gift_no"] = $gift["gift_no"]+1;
				$new_gift["gift_name"] = $gift["gift_name"];
				$new_gift["gift_number"] = $gift["gift_number"];
				$new_gift["is_open"] = $gift["is_open"];
				M("christmas_gift")->add($new_gift);
				M("christmas_gift_lock")->add(array("suo"=>0));
			}
			$christmas->commit();
		}
	    echo $res ? 1 : 0;
	}
}
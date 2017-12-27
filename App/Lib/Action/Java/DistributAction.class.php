<?php 
class DistributAction extends JCommonAction{

	//增加佣金策略
	public function addCpsPolicy(){
		if(count($_REQUEST) > 1){
			$data["policy_name"] 		= $_REQUEST['policy_name'];
			$data["commission_rate"] 	= $_REQUEST['commission_rate'];
			$data["is_permanent"] 		= $_REQUEST['is_permanent'];
			$data["begin_time"] 		= $_REQUEST['begin_time'];
			$data["end_time"] 			= $_REQUEST['end_time'];
			$data["check_status"] 		= $_REQUEST['check_status'];
			if($data["policy_name"] == null && $data["policy_name"] == ''){
				$result = array("code"=>-1,"msg"=>"策略名称不能为空");
				echo json_encode($result);
				exit;
			}
			if($data["commission_rate"] == null && $data["commission_rate"] == ''){
				$result = array("code"=>-1,"msg"=>"佣金比例不能为空");
				echo json_encode($result);
				exit;
			}elseif($data["commission_rate"] <= 0){
				$result = array("code"=>-1,"msg"=>"佣金比例必须大于0");
				echo json_encode($result);
				exit;
			}
			if($data["is_permanent"] == 0 ){
				if($data["begin_time"] == null && $data["begin_time"] == ''){
					$result = array("code"=>-1,"msg"=>"开始时间不能为空");
					echo json_encode($result);
					exit;
				}
				if($data["end_time"] == null && $data["end_time"] == ''){
					$result = array("code"=>-1,"msg"=>"结束时间不能为空");
					echo json_encode($result);
					exit;
				}
			}elseif($data["is_permanent"] == 1){
				if($data["begin_time"] == null && $data["begin_time"] == ''){
					$result = array("code"=>-1,"msg"=>"开始时间不能为空");
					echo json_encode($result);
					exit;
				}
			}
			if($data["check_status"] == null && $data["check_status"] == '' && $data["check_status"] > 0){
				$data["check_status"] = 0;
			}
			$rs = M("policy")->add($data);
			if(!$rs){
				$result = array("code"=>-1,"msg"=>"添加失败");
				echo json_encode($result);
			}else{
				$result = array("code"=>0,"msg"=>"提交成功");
				echo json_encode($result);
			}
		}else{
			$result = array("code"=>-1,"msg"=>"数据不能为空");
			echo json_encode($result);
		}
		
	}

	//修改佣金策略
	public function setCpsPolicy(){
		Log::write("修改数据：".var_export($_REQUEST,true));
		if(count($_REQUEST) > 1){
			if($_REQUEST["cps_id"] == null ){
				$result = array("code"=>-1,"msg"=>"策略ID不能为空");
				echo json_encode($result);
				exit;
			}
			$id 				= $_REQUEST["cps_id"];
			if($_REQUEST['policy_name'] != null)				$data["policy_name"] 		= $_REQUEST['policy_name'];
			if($_REQUEST['commission_rate'] != null)$data["commission_rate"] 	= $_REQUEST['commission_rate'];
			if($_REQUEST['is_permanent'] != null)				$data["is_permanent"] 		= $_REQUEST['is_permanent'];
			if($_REQUEST['begin_time'] != null)				$data["begin_time"] 		= $_REQUEST['begin_time'];
			if($_REQUEST['end_time'] != null)					$data["end_time"] 			= $_REQUEST['end_time'];
			if($_REQUEST['check_status'] != null)				$data["check_status"] 		= $_REQUEST['check_status'];
			$rs = M("policy")->where("id = {$id}")->save($data);
			if($rs == null){
				$result = array("code"=>-1,"msg"=>"修改失败");
				echo json_encode($result);
			}else{
				$result = array("code"=>0,"msg"=>"修改成功");
				echo json_encode($result);
			}
		}else{
			$result = array("code"=>-1,"msg"=>"数据不能为空");
			echo json_encode($result);
		}
	}

	//获取佣金策略
	public function getCpsPolicyList(){
		$page_no = 1;
		$page_number = 10;
		if($_REQUEST['policy_name'] != null)	$map["policy_name"] 	= array('like','%'.$_REQUEST['policy_name'].'%');
		if($_REQUEST['check_status'] != null)	$map["check_status"]	= $_REQUEST['check_status'];
		if($_REQUEST['page_no'] != null)		$page_no 				= $_REQUEST['page_no'];
		if($_REQUEST['page_number'] != null)	$page_number 			= $_REQUEST['page_number'];
		$start = ($page_no - 1)*$page_number;
		$policy_list = M('policy')->where($map)->limit("{$start},{$page_number}")->select();
		$record_total = M('policy')->where($map)->count();
		$page_total = ceil($record_total/$page_number);
		if($policy_list){
			$result = array("code"=>0,"msg"=>"查询成功","page_total"=>$page_total,"record_total"=>$record_total,"policy_list"=>$policy_list);
			echo json_encode($result);
		}else{
			$result = array("code"=>-1,"msg"=>"查询失败或无数据");
			echo json_encode($result);
		}
	}

	//获取分销效果
	public function getCpsResult(){
		$page_no = 1;
		$page_number = 10;
		if($_REQUEST['usr_id'] != null || $_REQUEST['usr_id'] != 0){
			$map["usr_id"] = $_REQUEST['usr_id'];
		}else{
			$result = array("code"=> -1,"msg"=>"usr_id不能为空");
			echo json_encode($result);
			exit;
		}
		if($_REQUEST['begin_date'] != null && $_REQUEST['end_date'] != null){
			$map['cps_date']=array("between",array($_REQUEST['begin_date'],$_REQUEST['end_date']));
		}elseif($_REQUEST['begin_date'] != null && $_REQUEST['end_date'] == null){
			$map['cps_date']=array("egt",$_REQUEST['begin_date']);
		}elseif($_REQUEST['begin_date'] == null && $_REQUEST['end_date'] != null){
			$map['cps_date']=array("elt",$_REQUEST['end_date']);
		}
		if($_REQUEST['page_no'] != null)		$page_no 			= $_REQUEST['page_no'];
		if($_REQUEST['page_number'] != null)	$page_number 		= $_REQUEST['page_number'];
		$start = ($page_no - 1)*$page_number;
		$cps_result_list = M('distribution')->where($map)->limit("{$start},{$page_number}")->select();
		$record_total = M('distribution')->where($map)->count();
		$page_total = ceil($record_total/$page_number);
		if($cps_result_list){
			$result = array("code"=>0,"msg"=>"查询成功","page_total"=>$page_total,"record_total"=>$record_total,"cps_result_list"=>$cps_result_list);
			echo json_encode($result);
		}else{
			$result = array("code"=>0,"msg"=>"无数据");
			echo json_encode($result);
		}
	}

	

	//获取佣金比例
	public function getRate(){
		$rate = 0;
		$ratecount = M('policy')->where("(end_time > '".date('Y-m-d')."' OR is_permanent = 1) AND check_status = 1")->count();
		$ratedata1 = M('policy')->where("end_time > '".date('Y-m-d')."' AND check_status = 1")->order("begin_time asc")->find();
		$ratedata2 = M('policy')->where("is_permanent = 1 AND check_status = 1")->order("begin_time asc")->find();
		if($ratecount == 1){
			$ratedata = M('policy')->where("(end_time > '".date('Y-m-d')."' OR is_permanent = 1) AND check_status = 1")->order("begin_time asc")->find();
			$rate = $ratedata["commission_rate"];
		}else{
			if($ratedata1['begin_time'] >= $ratedata2['begin_time']){
				$rate = $ratedata2['commission_rate'];
			}else{
				$rate = $ratedata1['commission_rate'];
			}	
		}
		
		return $rate;
	}

	//定时器执行写入流水
	public function execclearing(){
		import("@.redis.Distribut");
		$distribut = new Distribut();
		while(1){
            $bid = $distribut->get_distribut();
            if($bid){
			   Log::write(var_export($bid,true));
               $return=$this->addClearing($bid);
				if($return["code"]!=0){
					$fail['bid'] = $bid;
					$fail_list[]=$fail;
					Log::write($bid."发送数据失败");
				}
            }else{
                Log::write("目前没有任务");
                break;
            }
        }
	    //处理失败任务
	    if(count($fail_list)>0){
	        foreach($fail_list as $l){
	            $distribut->release_distribut($l["bid"]);
	        }
	    }

	}

	public function test(){
				import("@.redis.Distribut");
				$distribut = new Distribut();
				$distribut->release_distribut(846);
	}

	//写入结算流水
	public function addClearing($bid){
		$borrow_list = M('sinalog')->where("borrow_id = {$bid} AND type = 3 AND status = 4")->select();
		$borrow_info = M('borrow_info')->where("id = {$bid}")->field("borrow_name,borrow_money")->find();
		$i = 0;
		$rate = $this->getRate();
		$list = null;
		foreach ($borrow_list as $l) {
			$member = M("members")->where("id = {$l['uid']}")->find();
			$list[$i] = array("order_id"=>$l["order_no"],"goods_id"=>$bid,"goods_des"=>$borrow_info["borrow_name"],"goods_link"=>"https://".$_SERVER['HTTP_HOST']."/invest/{$bid}.html","goods_cnt"=>1,"goods_price"=>$borrow_info["borrow_money"],"store_id"=>$l["uid"],"store_name"=>$member["user_name"],"store_contact"=>$member["user_name"],"store_tel"=>$member["user_phone"],"deal_time"=>$l["completetime"],"deal_amount"=>$l["money"],"commission_rate"=>$rate,"commission"=>number_format($l["money"]*$rate, 2, '.', ''),"salesman_id"=>$member["recommend_id"],"commission_source"=>2,"cps_level"=>1,"salesman_commission_rate"=>"1.0000","salesman_commission"=>number_format($l["money"]*$rate, 2, '.', ''));
			$i++;
		}
		$data["clearing_list"] = json_encode($list);
		Log::write("写入流水参数：".json_encode($data));
		$result = $this->curl_post("http://115.159.208.43:8080/tscps_background/user/setClearingList.do",$data);
		return json_decode($result,true);
	}

	//curl Post提交
	public function curl_post($url,$data){
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_POST, 1);
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
		$result = curl_exec ($ch);
		Log::write("写入流水结果：".$result);
		return $result;
	}

}
?>
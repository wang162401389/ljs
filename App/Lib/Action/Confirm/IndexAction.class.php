<?php
// 本类由系统自动生成，仅供测试用途
class IndexAction extends CCommonAction {
	

	//PC综合服务费和咨询服务费列表页
	public function confirm(){
		$confirm = D("Confirm");
    	$list = $confirm->getAllList($this->uid);//获取未支付的综合服务费和咨询服务费列表
    	$fee = $confirm->getFeeSum($this->uid);//获取未支付的综合服务费总和
    	$danbao = $confirm->getDanbaoSum($this->uid);//获取未支付的咨询服务费总和
    	$count = $confirm->getAllCount($this->uid);//PC获取未支付的综合服务费和咨询服务费数量
    	$fee_count = $confirm->getFeeCount($this->uid);//获取未支付的综合服务费数量
    	$danbao_count = $confirm->getDanbaoCount($this->uid);//获取未支付的咨询服务费数量
        if($count > 0){
        	$this->assign("list",$list);
        	$this->assign("count",$count);
        	$this->assign("fee",$fee);
        	$this->assign("fee_count",$fee_count);
        	$this->assign("danbao",$danbao);
        	$this->assign("danbao_count",$danbao_count);
    		$this->display();
        }else{
            $this->redirect(__APP__."/Member");
        }
	}

	/**
	 * 债权手续费
	 */
	public function zhaiquanfee(){
		if($this->is_mobile()){
			if(!$this->uid){
				$this->redirect(__APP__."/m/pub/login");
				exit();
			}else{
				$fee = 0;
				$list = M("debt_borrow_info")->where(array("borrow_uid"=>$this->uid,"borrow_status"=>array('in','6,7'),"pay_fee"=>0))->select();
				foreach($list as $k=>$i){
					$fee = $fee + $i['colligate_fee'];
				}
			    $this->assign("list",$list);
			    $this->assign("fee",$fee);
				$simple_header_info["title"]="债权手续费";
				$this->assign("simple_header_info",$simple_header_info);
				$this->display("m_zhaiquanfee");
			}
		}else{
			if(!$this->uid){
					redirect(__APP__."/member/common/login/");
					exit;
			}else{
				$list = M("debt_borrow_info")->where(array("borrow_uid"=>$this->uid,"borrow_status"=>array('in','6,7'),"pay_fee"=>0))->select();
				foreach($list as $k=>$i){
					$fee = $fee + $i['colligate_fee'];
				}
			    $this->assign("list",$list);
			    $this->assign("fee",$fee);
				$this->display("zhaiquanfee");
			}
		}
	}

	/**
	 * 支付债权的费用
	 */
	public function pay_zhaiquanfee(){

		$list = M("debt_borrow_info")->where(array("borrow_uid"=>$this->uid,"borrow_status"=>array('in','6,7'),"pay_fee"=>0))->select();
		$fee = 0;
		$orderno = date('YmdHis').mt_rand( 100000,999999);
		foreach($list as $k=>$i){
			$fee = $fee + $i['colligate_fee'];
			sinalog($this->uid,$i["id"],19,$orderno,$i['colligate_fee'],time(),null);
		}

		$sina["uid"] = $this->uid;
		$sina["orderno"] = $orderno;
		$sina['money'] = $fee;
		if($this->is_mobile()){
				$sina['return_url'] = session('xieyi')."://".$_SERVER['HTTP_HOST']."/M/user/index.html";
		}else{
				$sina['return_url'] = session('xieyi')."://".$_SERVER['HTTP_HOST']."/member/withdraw#fragment-1";
		}
		echo sinapayfeecollecttrade($sina,1);
	}



	//手机端满标提醒列表
    public function m_mbtx(){
    	$confirm = D("Confirm");
    	$list = $confirm->getAllList($this->uid);
    	$fee_count = $confirm->getFeeCount($this->uid);
        if($fee_count > 0){
        	$this->assign("fee_count",$fee_count);
        	$this->assign("list",$list);
        	$this->assign("phone","yes");
        	$simple_header_info["title"]="满标提现";
    		$this->assign("simple_header_info",$simple_header_info);
    		$this->display();
        }else{
            $this->redirect(__APP__."/m/user/index");
        }
	}

	//手机端综合服务费列表
	public function m_zhfw(){
		$confirm = D("Confirm");
    	$list = $confirm->getFeeList($this->uid);
    	$fee = $confirm->getFeeSum($this->uid);
    	$this->assign("list",$list);
    	$this->assign("fee",$fee);
    	$this->assign("phone","yes");
    	$simple_header_info["title"]="满标提现";
		$this->assign("simple_header_info",$simple_header_info);
		$this->display();
	}

	//手机端咨询服务费列表
	public function m_zxfw(){
		$confirm = D("Confirm");
    	$list = $confirm->getDanbaoList($this->uid);
    	$danbao = $confirm->getDanbaoSum($this->uid);
    	$this->assign("list",$list);
    	$this->assign("danbao",$danbao);
		$this->assign("phone","yes");
    	$simple_header_info["title"]="满标提现";
		$this->assign("simple_header_info",$simple_header_info);
		$this->display();
	}

    //综合服务费支付协议
	public function protocol(){
	    $info = M("member_info")->where("uid={$this->uid}")->field("idcard,real_name")->find();
	    $this->assign("info",$info);
	    $this->assign("bid",$bid);
	    if($this->is_mobile()){
	    	$simple_header_info["title"]="综合服务费";
	    	$simple_header_info["url"]=__APP__."/Confirm/index/m_zhfw";
			$this->assign("simple_header_info",$simple_header_info);
	    	$this->display("m_protocol");		
	    }else{
	    	$this->display("protocol");
	    }	
    }

    //支付综合服务费
    public function payfee(){
    	$confirm = D("Confirm");
    	$sum = $confirm->getFeeSum($this->uid);
    	$list = $confirm->getFeeList($this->uid);
    	$danbaocount = $confirm->getDanbaoCount($this->uid);
    	$orderno = date('YmdHis').mt_rand( 100000,999999); 
    	foreach($list as $i){
    		sinalog($this->uid,$i["id"],10,$orderno,$i['fee'],time(),null);
    	}
    	$sina["uid"] = $this->uid;
    	$sina["orderno"] = $orderno;
    	$sina['money'] = $sum;
	    if($this->is_mobile()){
	    	if($danbaocount>0){
		    	$sina['return_url'] = session('xieyi')."://".$_SERVER['HTTP_HOST']."/confirm/index/m_zxfw";
	    	}else{
		    	$sina['return_url'] = session('xieyi')."://".$_SERVER['HTTP_HOST']."/M/user/cash.html";
	    	}
	    }else{
	    	if($danbaocount>0){
		    	$sina['return_url'] = session('xieyi')."://".$_SERVER['HTTP_HOST']."/confirm/index/confirm";
	    	}else{
	    		$sina['return_url'] = session('xieyi')."://".$_SERVER['HTTP_HOST']."/member/withdraw#fragment-1";
	    	}
	    }
    	echo sinapayfeecollecttrade($sina);
    }

    //担保金支付协议
	public function danbao_agreement(){
		$bid = $_REQUEST["bid"];
    	$info = M("member_info")->where("uid={$this->uid}")->field("idcard,real_name")->find();
    	$dbcompany = M("borrow_confirm b")->join("lzh_members_company c on b.danbao_id = c.uid")->where("b.bid = {$bid}")->field("c.company_name")->find();
    	$this->assign("dbcompany",$dbcompany["company_name"]);
    	$this->assign("info",$info);
    	$this->assign("bid",$bid);
    	if($this->is_mobile()){
    		$simple_header_info["title"]="咨询服务费";
    		$simple_header_info["url"]=__APP__."/Confirm/index/m_zxfw";
			$this->assign("simple_header_info",$simple_header_info);
    		$this->display("m_danbao_agreement");		
    	}else{
    		$this->display("danbao_agreement");
    	}
    }

    //支付担保费
    public function paydanbao(){
    	$confirm = D("Confirm");
    	$sum = $confirm->getDanbaoSum($this->uid);
    	$list = $confirm->getDanbaoList($this->uid);
    	$orderno = date('YmdHis').mt_rand( 100000,999999); 
    	foreach($list as $i){
    		sinalog($i["danbao_id"],$i["id"],12,$orderno,$i['danbao'],time(),null);
    	}
    	$sina["uid"] = $this->uid;
    	$sina["orderno"] = $orderno;
    	$sina['money'] = $sum;
	    if($this->is_mobile()){
	    	$sina['return_url'] = session('xieyi')."://".$_SERVER['HTTP_HOST']."/M/user/cash.html";
	    }else{
	    	$sina['return_url'] = session('xieyi')."://".$_SERVER['HTTP_HOST']."/member/withdraw#fragment-1";
	    }
    	echo sinapaydanbaotrade($sina);
    }

    protected function is_mobile() {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $mobile_agents = Array("240x320","acer","acoon","acs-","abacho","ahong","airness","alcatel","amoi","android","applewebkit/525","applewebkit/532","asus","audio","au-mic","avantogo","becker","benq","bilbo","bird","blackberry","blazer","bleu","cdm-","compal","coolpad","danger","dbtel","dopod","elaine","eric","etouch","fly ","fly_","fly-","go.web","goodaccess","gradiente","grundig","haier","hedy","hitachi","htc","huawei","hutchison","inno","ipad","ipaq","ipod","jbrowser","kddi","kgt","kwc","lenovo","lg ","lg2","lg3","lg4","lg5","lg7","lg8","lg9","lg-","lge-","lge9","longcos","maemo","mercator","meridian","micromax","midp","mini","mitsu","mmm","mmp","mobi","mot-","moto","nec-","netfront","newgen","nexian","nf-browser","nintendo","nitro","nokia","nook","novarra","obigo","palm","panasonic","pantech","philips","phone","pg-","playstation","pocket","pt-","qc-","qtek","rover","sagem","sama","samu","sanyo","samsung","sch-","scooter","sec-","sendo","sgh-","sharp","siemens","sie-","softbank","sony","spice","sprint","spv","symbian","tablet","talkabout","tcl-","teleca","telit","tianyu","tim-","toshiba","tsm","up.browser","utec","utstar","verykool","virgin","vk-","voda","voxtel","vx","wap","wellco","wig browser","wii","windows ce","wireless","xda","xde","zte");
        $is_mobile = false;
        foreach ($mobile_agents as $device) {
            if (stristr($user_agent, $device)) {
                $is_mobile = true;
                break;
            }
        }
        return $is_mobile;
    }
}
?>
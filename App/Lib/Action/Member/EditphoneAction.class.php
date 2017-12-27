<?php
// 本类由系统自动生成，仅供测试用途
class EditphoneAction extends MCommonAction {

    public function index(){
		$usertype = M('members')->where("id=".$this->uid)->find();
		if($usertype["user_regtype"]!=1){
        	$this->error("企业用户不允许改手机");die;
        }
		$this->assign('user_regtype',$usertype['user_regtype']);
		$this->display();
    }

    public function cellphone(){
    	
    	$phone = $this->sinaphone();
    	$this->assign("phone",$phone);
    	$data['html'] = $this->fetch();
		exit(json_encode($data));
    }

    public function sendphone(){
		$smsTxt = FS("Webconfig/smstxt");
		$phone = trim($_POST['newphone']);
		$code = rand_string_reg(6, 1, 2);
		$msg = "您正在修改登录手机号，验证码：{$code}，请您在3分钟内填写。若非您本人操作，请与客服中心联系400-6626-985。";
		$res = sendsms($phone,$msg);
		if($res){
			session("temp_phone",$phone);
			ajaxmsg();
		}
		else ajaxmsg("",0);
    }

    public function setphone(){
    	$phone = trim($_POST['newphone']);
    	$where['id'] = $this->uid;
    	$where1['uid'] = $this->uid;
        $urs_info = M("members")->where($where)->find();
        if($phone == $urs_info["user_phone"]){
        	ajaxmsg("手机号已存在，请把新浪的修改回来。", 3);
        	exit();
        }
    	if($_SESSION['code_temp'] != trim($_POST['code'])) {
        	ajaxmsg("验证码错误", 3);
        	exit();
        }
        $sina_phone = $this->sinaphone();
        if($phone == $sina_phone){
        	log::write("用户ID：".$this->uid."，新浪查手机:新手机：".$phone."，新浪的手机：".$sina_phone);
        	// ajaxmsg("新浪未改，修改失败。", 3);
        	// exit();
        }
        $javaphone_data['usr_id']=$this->uid;
        $javaphone_data['user_phone']=$phone;
        $setphone_url = C("UNIFY_INTERFACE.url").'/unify_interface/user/setUsrphone.do';
        $phone_res = curl_post($setphone_url,$javaphone_data);
        log::write("改手机JAVA:::".$phone_res);
        $j_res = json_decode($phone_res,true);
        if($j_res["code"] == 0){
        	
        	$data['user_phone'] = $phone;
        	if($urs_info["user_name"] == $urs_info["user_phone"]){
        		$data['user_name'] = $phone;
        		$javaname_data['usr_id']=$this->uid;
        		$javaname_data['user_name']=$urs_info["user_name"];
        		$javaname_data['user_name_new']=$phone;
        		$javaname_data['is_checkoldpass']=0;
        		$setusername_url = C("UNIFY_INTERFACE.url").'/unify_interface/user/setUsrname.do';
        		$javaname_result = curl_post($setusername_url,$javaname_data);
        	}
	        $data1['cell_phone'] = $phone;
	        $res = M("members")->where($where)->save($data);
	        $res1 = M("member_info")->where($where1)->save($data1);
	        $coup_data["user_phone"]=$phone;
	        $coup_where["user_phone"]=$urs_info["user_phone"];
	        M("coupons")->where($coup_where)->save($coup_data);
	        if($res && $res1){
	        	$this->unbindsinaphone();
	        	$this->bindsinaphone($phone);
	        	ajaxmsg("", 1);
	        }
        }else{
			ajaxmsg("修改失败", 3);
			exit();
        }
       
    }

    public function Sinasecurity(){
    	import("@.Oauth.sina.Weibopay");
		$payConfig = FS("Webconfig/payconfig");
		$weibopay = new Weibopay();
		$data['service'] 			  = "show_member_infos_sina";							//接口名称
		$data['version']			  = $payConfig['sinapay']['version'];						//接口版本
		$data['request_time']		  = date('YmdHis');											//请求时间
		$data['partner_id'] 		  = $payConfig['sinapay']['partner_id'];					//合作者身份ID
		$data['_input_charset'] 	  = $payConfig['sinapay']['_input_charset'];				//网站编码格式
		$data['sign_type'] 			  = $payConfig['sinapay']['sign_type'];						//签名方式 MD5
		$data['identity_id'] 		  = "20151008".$this->uid;													//ID类型
		$data['identity_type']		  = "UID";		//支付方式：支付方式^金额^扩展|支付方式^金额^扩展。扩展信息内容以“，”分隔
		$data['resp_method']		  = 1;
		$data['default_page']		  = "SAFETY_CENTER";
		$data['hide_pages']			  = "ORDER|WITHHOLD";
		ksort($data);
		$data['sign'] 				  = $weibopay->getSignMsg($data,$data['sign_type']);		//计算签名
		$setdata 					  = $weibopay->createcurl_data($data);
		$result						  = $weibopay->curlPost($payConfig['sinapay']['mgs'],$setdata);//模拟表单提交
		$rs = checksinaerror($result);
		$this->ajaxReturn($rs['redirect_url']);
    }

    private function sinaphone(){
    	import("@.Oauth.sina.Weibopay");
		$payConfig = FS("Webconfig/payconfig");
		$weibopay = new Weibopay();
		$data['service'] 			  = "query_verify";							//接口名称
		$data['version']			  = $payConfig['sinapay']['version'];						//接口版本
		$data['request_time']		  = date('YmdHis');											//请求时间
		$data['partner_id'] 		  = $payConfig['sinapay']['partner_id'];					//合作者身份ID
		$data['_input_charset'] 	  = $payConfig['sinapay']['_input_charset'];				//网站编码格式
		$data['sign_type'] 			  = $payConfig['sinapay']['sign_type'];						//签名方式 MD5
		$data['identity_id'] 		  = "20151008".$this->uid;													//ID类型
		$data['identity_type']		  = "UID";		//支付方式：支付方式^金额^扩展|支付方式^金额^扩展。扩展信息内容以“，”分隔
		$data['verify_type']		  = "MOBILE";
		$data['is_mask']		 	  = "N";
		ksort($data);
		$data['sign'] 				  = $weibopay->getSignMsg($data,$data['sign_type']);		//计算签名
		$setdata 					  = $weibopay->createcurl_data($data);
		$result						  = $weibopay->curlPost($payConfig['sinapay']['mgs'],$setdata);//模拟表单提交
		$rs = checksinaerror($result);
		return $rs["verify_entity"];
    }
    private function unbindsinaphone(){
    	import("@.Oauth.sina.Weibopay");
		$payConfig = FS("Webconfig/payconfig");
		$weibopay = new Weibopay();
		$data['service'] 			  = "unbinding_verify";							//接口名称
		$data['version']			  = $payConfig['sinapay']['version'];						//接口版本
		$data['request_time']		  = date('YmdHis');											//请求时间
		$data['partner_id'] 		  = $payConfig['sinapay']['partner_id'];					//合作者身份ID
		$data['_input_charset'] 	  = $payConfig['sinapay']['_input_charset'];				//网站编码格式
		$data['sign_type'] 			  = $payConfig['sinapay']['sign_type'];						//签名方式 MD5
		$data['identity_id'] 		  = "20151008".$this->uid;													//ID类型
		$data['identity_type']		  = "UID";		
		$data['verify_type']		  = "MOBILE";
		$data['verify_entity']		  = "MOBILE";
		$data['client_ip']			  = get_client_ip();
		ksort($data);
		$data['sign'] 				  = $weibopay->getSignMsg($data,$data['sign_type']);		//计算签名
		$setdata 					  = $weibopay->createcurl_data($data);
		$result						  = $weibopay->curlPost($payConfig['sinapay']['mgs'],$setdata);//模拟表单提交
    }
    private function bindsinaphone($phone){
    	$data["identity_id"] = $this->uid;
    	$data["phone"] = $phone;
    	import("@.Oauth.sina.Sina");
    	$sina = new Sina();
    	$sina->bindingverify($data);
    }
}
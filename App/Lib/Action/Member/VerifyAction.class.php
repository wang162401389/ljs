<?php
// 本类由系统自动生成，仅供测试用途
class VerifyAction extends MCommonAction {

    public function index(){
		//if(!$_GET['id']) redirect(__APP__."/member/verify?id=1#fragment-1");
		$usertype = M('members')->where("id=".$this->uid)->find();
		$this->assign('user_regtype',$usertype['user_regtype']);
		$this->display();
    }

    public function welcome(){
		$data['content'] = $this->fetch();
		exit(json_encode($data));
    }

    public function email(){
		$this->assign("email_status",M('members_status')->getFieldByUid($this->uid,'email_status'));
		$this->assign("email",M('members')->getFieldById($this->uid,'user_email'));
		$sq = M('member_safequestion')->find($this->uid);
		$this->assign("sq",$sq);
		$data['html'] = $this->fetch();
		exit(json_encode($data));
    }

    public function emailvalided(){
		$status = M("members_status")->getFieldByUid($this->uid,'email_status');
		ajaxmsg('',$status);
    }

  	public function emailvsend1(){
		$status=Notice(8,$this->uid);
		if($status) ajaxmsg();
		else  ajaxmsg('',0);
    }
	
    public function emailvsend(){
		$data['user_email'] = text($_POST['email']);
		$data['last_log_time']=time();
		$newid = M('members')->where("id = {$this->uid}")->save($data);//更改邮箱，重新激活
		if($newid){
			$status=Notice(8,$this->uid);
			if($status) ajaxmsg('邮件已发送，请注意查收！',1);
			else ajaxmsg('邮件发送失败,请重试！',0);
		}else{
			 ajaxmsg('新邮件修改失败',2);
		}
    }

	public function ckemail(){
		$map['user_email'] = text($_POST['Email']);
		$count = M('members')->where($map)->count('id');
        
		if ($count>1) {
			$json['status'] = 0;
			exit(json_encode($json));
        } else {
			$json['status'] = 1;
			exit(json_encode($json));
        }
	}
    public function idcard(){
		$ids = M('members_status')->where("uid={$this->uid}")->field("id_status,company_status")->find();
		$utype = M("members")->where("id={$this->uid}")->field("user_regtype")->find();
		$this->assign("bank_list",$this->gloconf['BANK_NAME']);
		if($ids["id_status"]==1){
			$vo = M("member_info")->field('idcard,real_name')->find($this->uid);
			$data['html'] = $this->fetch();
		}elseif($ids["company_status"]==3){
			$vo = M("members_company")->where("uid={$this->uid}")->field('company_name,license_no')->find();
			$vo["idcard"] = $vo["license_no"];
			$vo["real_name"] = $vo["company_name"];
			$data['html'] = $this->fetch();
		}elseif($ids["company_status"]==4){
			$vo = M("members_company")->where('uid='.$this->uid)->find();
		}
		if($utype["user_regtype"] == 1){
			$vo["utype"] = 1;
		}elseif($utype["user_regtype"] == 2){
			$vo["utype"] = 2;
		}
		$this->assign("vo",$vo);
		$this->assign("ids",$ids);
		$data['html'] = $this->fetch();
		exit(json_encode($data));
    }
    //实名认证
    public function saveid(){
		$data['real_name'] = text($_POST['realname']);
		$data['idcard'] = text($_POST['idcard']);
		$data['up_time'] = time();
		/////////////////////////
		$data1['idcard'] = text($_POST['idcard']);
		$data1['up_time'] = time();
		$data1['uid'] = $this->uid;
		$data1['status'] = 0;
		$b = M('name_apply')->where("uid = {$this->uid}")->count('uid');
		if($b==1){
			M('name_apply')->where("uid ={$this->uid}")->save($data1);
		}else{
			M('name_apply')->add($data1);
		}
		////////////////////////
		if(empty($data['real_name'])||empty($data['idcard']))  ajaxmsg("请填写真实姓名和身份证号码",0);
		$xuid = M('member_info')->getFieldByIdcard($data['idcard'],'uid');
		//if($xuid>0 && $xuid!=$this->uid) ajaxmsg("此身份证号码已被人使用",0);
		$c = M('member_info')->where("uid = {$this->uid}")->count('uid');
		$uinfo = M("members")->where("id={$this->uid}")->field("user_phone")->find();
		
		$sina['identity_id'] = $this->uid;//用户ID
		$sina['member_type'] = 1;	//用户类型
		$sina['phone'] = $uinfo['user_phone'];	//用户手机
		$sina['real_name'] = $data['real_name'];//用户真实姓名
		$sina['cert_no'] = $data['idcard'];//用户身份证号
		$rs = sinamember($sina);
		// $result = $this->setrealname($sina);//调用新浪设置实名信息接口
		if($rs == 1){
			$rs1['status'] = 1;
			M('name_apply')->where("uid ={$this->uid}")->save($rs1);
		}else{
			ajaxmsg($rs,0);
			exit;
		}
		if($c==1){
			$newid = M('member_info')->where("uid = {$this->uid}")->save($data);
			ancunUser($this->uid);
		}else{
			$data['uid'] = $this->uid;
			$newid = M('member_info')->add($data);
		}
        $this->del_mem_cach();
        //实名注册以后，发送5000虚拟体验金
        if(C("V_INVEST.enable")){
            $minfo=M("members")->where("id={$this->uid}")->field("user_phone")->find();
            $where["uid"]=$this->uid;
            $res=M("members")->db(1,C("V_INVEST.db"))->where($where)->find();
            if(is_array($res)){
                Log::write("{$this->uid}已经领取过了");
            }
            else{
                $deadtime=time()+15*24*3600;
                $sql="INSERT INTO lzh_members (uid,v_money,tel,deadtime) VALUES ('".$this->uid."','5000','".$minfo["user_phone"]."','".$deadtime."')";
                $id=M("members")->db(1,C("V_INVEST.db"))->query($sql);
                if($id>0){
                    Log::write("{$this->uid}领取体验金成功");
                }else{
                    Log::write("{$this->uid}领取体验金失败");
                }
            }
        }
		if($newid){
			//$ms=M('members_status')->where("uid={$this->uid}")->setField('id_status',1);
			//if($ms==1){
				ajaxmsg();
			//}
		}
		//session('idcardimg',NULL);
		//session('idcardimg2',NULL);
		// if($newid && $result['response_code'] == 'APPLY_SUCCESS'){
		// 	$ms=M('members_status')->where("uid={$this->uid}")->setField('id_status',1);
		// 	if($ms==1){
		// 		ajaxmsg();
		// 	}else{
		// 		$dt['uid'] = $this->uid;
		// 		$dt['id_status'] = 1;
		// 		M('members_status')->add($dt);
		// 	}
		// 	ajaxmsg();
		// }
		// else  ajaxmsg("保存失败，请重试",0);
    }


	// //新浪设置实名信息
	// public function setrealname($sina){
	// 	$payConfig = FS("Webconfig/payconfig");
	// 	$sinafile = C('SINA_FILE');
	// 	import("@.Oauth.sina.Weibopay");
	// 	$weibopay = new Weibopay();
	// 	//设置实名信息
	// 	$data['service'] = "set_real_name";				//绑定认证信息的接口名称
	// 	$data['version'] = $payConfig['sinapay']['version'];						//接口版本
	// 	$data['request_time'] = date('YmdHis');			//请求时间
	// 	$data['partner_id'] = $payConfig['sinapay']['partner_id'];			//合作者身份ID
	// 	$data['_input_charset'] = $payConfig['sinapay']['_input_charset'];				//网站编码格式
	// 	$data['sign_type'] = $payConfig['sinapay']['sign_type'];						//签名方式 MD5
	// 	$data['identity_id'] = '20151008'.$sina['identity_id'];		//用户ID
	// 	$data['identity_type'] = "UID";					//用户标识类型 UID
	// 	$data["client_ip"]=get_client_ip();
	// 	$realname = $weibopay -> Rsa_encrypt($sina['real_name'],dirname(dirname(dirname(__FILE__)))."/Key/".$sinafile);//对用户姓名进行rsa公钥加密
	// 	$data['real_name'] = $realname;					//真是姓名
	// 	$data['cert_type'] = "IC";						//用户标识类型 UID
	// 	$cret_no = $weibopay->Rsa_encrypt($sina['cert_no'],dirname(dirname(dirname(__FILE__)))."/Key/".$sinafile);//对身份证号进行rsa公钥加密
	// 	$data['cert_no'] = $cret_no;				//身份证号
	// 	$data["client_ip"]=get_client_ip();
	// 	ksort($data);									//对签名参数数据排序
	// 	$data['sign'] = $weibopay->getSignMsg($data,$data['sign_type']);//计算签名
	// 	$setdata = $weibopay->createcurl_data($data);
	// 	$result = $weibopay->curlPost($payConfig['sinapay']['mgs'],$setdata);//模拟表单提交
	// 	return checksinaerror($result);//验证
	// }
	
	public function company(){

		$ids = M('members_status')->getFieldByUid($this->uid,'company_status');
		$vo = M("members_company")->where('uid='.$this->uid)->find();
		$this->assign("vo",$vo);
		if($ids!=2){
			$data['html'] = $this->fetch();
		}
		//$this->assign("vobank",$vobank);
		$this->assign("bank_list",$this->gloconf['BANK_NAME']);
		$this->assign("company_status",$ids);
		$data['html'] = $this->fetch();

		exit(json_encode($data));
    }

    public function uploadcomfile(){
    	$sinaupload = C('UPLOAD_ZIP');
    	import("ORG.Net.UploadFile");
		$upload = new UploadFile();// 实例化上传类
		$upload->maxSize  = 5242880;// 设置附件上传大小
		$upload->allowExts  = array('zip');// 设置附件上传类型
		$upload->savePath =  '/UF/zip/';// 设置附件上传目录
		$upload->uploadReplace = true; //同名覆盖
		if(!$upload->upload()) {// 上传错误提示错误信息
			$this->error($upload->getErrorMsg());
		}else{// 上传成功 获取上传文件信息
			$info =  $upload->getUploadFileInfo();
			$path = $sinaupload["UPLOAD_PATH"].$info[0]['savepath'].$info[0]["savename"];
			 session("filepath",$path);
		     session("filename",$info[0]["savename"]);
		     session("unfilepath",$sinaupload["UPLOAD_PATH"].$info[0]['savepath']."unzip/");


		     //公司资料解压缩，图片路劲json转换
		     $comimg_url = $this->uncomzip($path);
		     Log::write("解压图片路径JSON：".$comimg_url."\n");
		     session("company_img",$comimg_url);
		}
    }

	// public function test(){
	// 	$path="/UF/zip/1.zip";
	// 	$zip = new ZipArchive() ;
	// 	$og=$zip->open($path) ;
	// 	if ($og !== TRUE) {
	// 		$af=array();
	// 		$ad=array();
	// 		for($i = 0; $i < $zip->numFiles; $i++) {
	// 			$filename = $zip->getNameIndex($i);
	// 			$fileinfo = pathinfo($filename);
	// 			$af[]=$fileinfo;
	// 			$ad[]=$filename;
	// 		}
	// 		//将压缩文件解压到指定的目录下
	// 		$destination=dirname($path)."/".$this->uid;
	// 		echo $destination;
	// 		echo "<br/>";
	// 		if(!file_exists($destination)){
	// 			$gt=mkdir($destination,0775,true);
	// 			echo "create dir:";
	// 			var_dump($gt);
	// 			echo "<br/>";
	// 		}
	// 		$bool=$zip->extractTo($destination);
	// 		echo "jieyasuo:";
	// 		var_dump($bool);
	// 		echo "<br/>";
	// 		//关闭zip文档
	// 		$zip->close();
	// 		var_dump($af);
	// 		echo "<br/>";
	// 		var_dump($ad);
	// 		var_dump("qwerqewrqewr");
	// 		exit();
	// 	}else{
	// 		var_dump($og);
	// 		var_dump("1111111111111");
	// 		exit();
	// 	}
	// }
    
    //提交企业资质审核
    public function savecompany(){
		$filepath = session("filepath");
		$filename = session("filename");
    	import("@.Oauth.sina.Weibopay");
		$weibopay = new Weibopay();
		$digest=$weibopay->md5_file($filepath);//文件摘要
		$is_upload=$weibopay->sftp_upload($filepath,$filename);

		if($is_upload){
			//平台保存参数
	    	$data['uid'] = $this->uid;
	    	$data['company_name'] = text($_POST['company_name']);
	    	$data['address'] = text($_POST['address']);
	    	$data['license_no'] = text($_POST['license_no']);
	    	$data['license_address'] = text($_POST['license_address']);
	    	$data['license_expire_date'] = strtotime($_POST['license_expire_date']);
	    	$data['business_scope'] = text($_POST['business_scope']);
	    	$data['telephone'] = text($_POST['telephone']);
	    	$data['email'] = text($_POST['email']);
	    	$data['organization_no'] = text($_POST['organization_no']);
	    	$data['summary'] = text($_POST['summary']);
	    	$data['legal_person'] = text($_POST['legal_person']);
	    	$data['cert_no'] = text($_POST['cert_no']);
	    	$data['legal_person_phone'] = text($_POST['legal_person_phone']);
			$data['addtime']       = time();
			$data['audit_order_no'] = date('YmdHis').mt_rand( 100000,999999); 
			$data['result'] = null;
			$data["agent_name"] = text($_POST["agent_name"]);
			$data["agent_mobile"] = text($_POST["agent_mobile"]);
			$data["alicense_no"] = text($_POST["alicense_no"]);
			$data1['uid'] = $this->uid;
	    	$data1['bank_num']       = text($_POST['bank_num']);
			$bankname = explode("_",text($_POST['bank_name']));
			$data1['bank_name']      = $bankname[0];
			$data1['bank_address']   = text($_POST['txt_bankName']);
			$data1['bank_province']  = text($_POST['province']);
			$data1['bank_city']      = text($_POST['city']);
			$data1['add_ip']         = get_client_ip();
			$data1['add_time']       = time();
			//新浪参数
			$sina['company_name'] = $data['company_name']; //公司名称
	    	$sina['address'] = $data['address'];	//公司地址
	    	$sina['license_no'] = $data['license_no'];	//执照号
	    	$sina['license_address'] = $data['license_address'];	//执照所在地
	    	$sina['license_expire_date'] = date("Ymd",$_POST['license_expire_date']);	//执照过期日
	    	$sina['business_scope'] = $data['business_scope'];	//营业范围
	    	$sina['telephone'] = $data['telephone'];	//联系电话
	    	$sina['email'] = $data['email'];	//Email
	    	$sina['organization_no'] = $data['organization_no'];	//组织机构代码证
	    	$sina['summary'] = $data['summary'];	//企业简介
	    	$sina['legal_person'] = $data['legal_person'];	//企业法人
	    	$sina['cert_no'] = $data['cert_no'];	//企业法人证件号
	    	$sina['legal_person_phone'] = $data['legal_person_phone'];	//法人手机号
			$sina['audit_order_no'] = $data['audit_order_no']; 	//请求订单号
	    	$sina['bank_num']       = $data1['bank_num'];	//银行卡号
			$sina['bankcode']      = $bankname[1];	//银行名称
			$sina['bank_address']   = $data1['bank_address'] ;	//支行名称
			$sina['bank_province']  = $data1['bank_province'];	//开户行省
			$sina['bank_city']      = $data1['bank_city'];	//开户行城市
			$sina['fileName']=$filename; //文件名称
			$sina['digest']=$digest;		//文件摘要
			$sina['identity_id']=$this->uid;
			$sina['member_type']=2;
			$rs = sinamember($sina);
			//$rs = $this->auditmember($sina);
			file_put_contents('logtest.txt', var_export($sina,true), FILE_APPEND);
			file_put_contents('logtest.txt', "result:".var_export($rs,true), FILE_APPEND);
			if($rs===true){
				$data["zizhi"] = session("company_img");
				// M("members_company")->where(array("uid"=>$this->uid))->save(array("zizhi"=>));
				$status['company_status']=1;
				M('members_status')->where('uid='.$this->uid)->save($status);
				$rs1 = M('members_company')->where('uid='.$this->uid)->count();
				if($rs1 >0){
					M('members_company')->where('uid='.$this->uid)->save($data); //记录公司资质表
					M('member_banks')->where('uid='.$this->uid)->save($data1);	 //记录银行帐号
				}else{
					M('members_company')->add($data); //记录公司资质表
					M('member_banks')->add($data1);	 //记录银行帐号
				}
                if(C("V_INVEST.enable")){
                    $minfo=M("members")->where("id={$this->uid}")->field("user_phone")->find();
                    $where["uid"]=$this->uid;
                    $res=M("members")->db(1,C("V_INVEST.db"))->where($where)->find();
                    if(is_array($res)){
                        Log::write("{$this->uid}已经领取过了");
                    }
                    else{
                        $deadtime=time()+15*24*3600;
                        $sql="INSERT INTO lzh_members (uid,v_money,tel,deadtime) VALUES ('".$this->uid."','5000','".$minfo["user_phone"]."','".$deadtime."')";
                        $id=M("members")->db(1,C("V_INVEST.db"))->query($sql);
                        if($id>0){
                            Log::write("{$this->uid}领取体验金成功");
                        }else{
                            Log::write("{$this->uid}领取体验金失败");
                        }
                    }
                }
				$this->success("提交成功","/member/verify#fragment-1");
			}else{
				$this->error($rs,"/member/verify#fragment-1");
			}
		}else{
			$this->error("上传资质文件失败","/member/verify#fragment-1");
		}
    }

    public function uncomzip($file){
			$zipflo=$file;
			$unzipflo=session("unfilepath");
		    $zipfloresult = $this->mkFolder($zipflo);
		    $unzipfloresult = $this->mkFolder($unzipflo);
		    $zip = new \ZipArchive;
			        $res = $zip->open($file);
			        if ($res === TRUE) {
			            //解压缩到文件夹
			            $serveresult=$zip->extractTo($unzipflo);
			            if($serveresult){
			            	Log::write("保存成功\n");
			            }else{
			            	Log::write("保存失败\n");
			            	return false;
			            	die();
			            }
			            $zip->close();
			        }else{
			        	Log::write("解压失败\n");
			        	return false;
			        	die();
			        }

		    $handler = opendir($unzipflo);
		    $files_path=null;
		    $i = 0;
		    while( ($filename = readdir($handler)) !== false )
		    {
		        if($filename !="." && $filename !=".."){
		            $files=$unzipflo.$filename;
		             $filesnames = scandir($files);
		             $array_file = array();
		             $filename_path = "/UF/zip/unzip/".substr(session("filename"), 0, -4);
		             foreach ($filesnames as $key => $value) {
		             	if ($value != "." && $value != "..") {
							$array_file[] = $filename_path."/".$value; //输出文件名
						}
		             }
		        }
		    }

		    closedir($handler);
		    return json_encode($array_file);
    }

    public function mkFolder($path)
	{
		if (!file_exists($path))
		{
			mkdir($path, 0777,true);
			return true;
		}
		return false;
	}

	//新浪企业会员审核
	public function auditmember($sina){
		$payConfig = FS("Webconfig/payconfig");
		$sinafile = C('SINA_FILE');
		import("@.Oauth.sina.Weibopay");
		$weibopay = new Weibopay();
		$data['service']		 = "audit_member_infos";						//绑定认证信息的接口名称
		$data['version']		 = $payConfig['sinapay']['version'];			//接口版本
		$data['request_time']	 = date('YmdHis');								//请求时间
		$data['partner_id']		 = $payConfig['sinapay']['partner_id'];			//合作者身份ID
		$data['_input_charset']	 = $payConfig['sinapay']['_input_charset'];		//网站编码格式
		$data['sign_type']		 = $payConfig['sinapay']['sign_type'];			//签名方式 MD5
		$data['notify_url']		 = "https://".$_SERVER['HTTP_HOST']."/Home/Sinanotify/companystatus"; 		// 异步后台通知地址,如果不传此参数，则不会后台通知
		$data['identity_id']	 = '20151008'.$this->uid;						//用户ID
		$data['identity_type']	 = "UID";										//用户标识类型 UID
		$data['audit_order_no']	 = $sina['audit_order_no'];						//请求订单号
		$data['company_name']	 = $sina['company_name'];						//公司全称
		$data['address']		 = $sina['address'];							//企业地址
		$data['license_no']		 = $weibopay->Rsa_encrypt($sina['license_no'],dirname(dirname(dirname(__FILE__)))."/Key/".$sinafile);//对执照号进行rsa公钥加密
		$data['license_address'] = $sina['license_address'];					//营业执照所在地
		$data['license_expire_date'] = $sina['license_expire_date'];			//执照过期日
		$data['business_scope']	 = $sina['business_scope'];						//营业范围
		$data['telephone']		 = $weibopay->Rsa_encrypt($sina['telephone'],dirname(dirname(dirname(__FILE__)))."/Key/".$sinafile);//对联系电话进行rsa公钥加密
		$data['email']			 = $weibopay->Rsa_encrypt($sina['email'],dirname(dirname(dirname(__FILE__)))."/Key/".$sinafile);//对EMAIL进行rsa公钥加密
		$data['organization_no'] = $weibopay->Rsa_encrypt($sina['organization_no'],dirname(dirname(dirname(__FILE__)))."/Key/".$sinafile);//对组织机构代码进行rsa公钥加密
		$data['summary']		 = $sina['summary'];							//企业简介
		$data['legal_person'] 	 = $weibopay->Rsa_encrypt($sina['legal_person'],dirname(dirname(dirname(__FILE__)))."/Key/".$sinafile);//对企业法人进行rsa公钥加密
		$data['cert_no'] 		 = $weibopay->Rsa_encrypt($sina['cert_no'],dirname(dirname(dirname(__FILE__)))."/Key/".$sinafile);//对法人证件号进行rsa公钥加密
		$data['cert_type']		 = "IC";										//证件类型
		$data['legal_person_phone'] = $weibopay->Rsa_encrypt($sina['legal_person_phone'],dirname(dirname(dirname(__FILE__)))."/Key/".$sinafile);//对法人手机号进行rsa公钥加密
		$data['bank_code'] 		 = $sina['bankcode'];							//银行编号
		$data['bank_account_no'] = $weibopay->Rsa_encrypt($sina['bank_num'],dirname(dirname(dirname(__FILE__)))."/Key/".$sinafile);//对银行卡号进行rsa公钥加密
		$data['card_type']		 = "DEBIT";										//卡类型
		$data['card_attribute']	 = "B";											//卡属性
		$data['province']		 = $sina['bank_province'];						//开户行省份
		$data['city']			 = $sina['bank_city'];							//开户行城市
		$data['bank_branch']	 = $sina['bank_address'];						//支行名称
		$data['fileName']		 = $sina['fileName'];							//文件名称
		$data['digest']			 = $sina['digest'];								//文件摘要
		$data['digestType']		 = "MD5";										//文件摘要算法
		$data["client_ip"]       =get_client_ip(); 
		ksort($data);									//对签名参数数据排序
		$data['sign'] = $weibopay->getSignMsg($data,$data['sign_type']);//计算签名
		$setdata = $weibopay->createcurl_data($data);
		$result = $weibopay->curlPost($payConfig['sinapay']['mgs'],$setdata);//模拟表单提交
		return checksinaerror($result);//验证
	}

	//新浪经办人录入接口
	public function sinasmt(){
		$payConfig = FS("Webconfig/payconfig");
		$sinafile = C('SINA_FILE');
		import("@.Oauth.sina.Weibopay");
		$weibopay = new Weibopay();
		$data['service']		 = "smt_fund_agent_buy";						//绑定认证信息的接口名称
		$data['version']		 = $payConfig['sinapay']['version'];			//接口版本
		$data['request_time']	 = date('YmdHis');								//请求时间
		$data['partner_id']		 = $payConfig['sinapay']['partner_id'];			//合作者身份ID
		$data['_input_charset']	 = $payConfig['sinapay']['_input_charset'];		//网站编码格式
		$data['sign_type']		 = $payConfig['sinapay']['sign_type'];			//签名方式 MD5
		$data['identity_id']	 = '20151008'.$this->uid;						//用户ID
		$data['identity_type']	 = "UID";										//用户标识类型 UID
		$data['agent_name']		 = $weibopay->Rsa_encrypt($_POST['agent_name'],dirname(dirname(dirname(__FILE__)))."/Key/".$sinafile);//对经办人姓名进行rsa公钥加密
		$data['license_no']		 = $weibopay->Rsa_encrypt($_POST['license_no'],dirname(dirname(dirname(__FILE__)))."/Key/".$sinafile);//对经办人身份证进行rsa公钥加密
		$data['license_type_code']		 = "ID"; 								//证件类型
		$data['agent_mobile']    = $weibopay->Rsa_encrypt($_POST['agent_mobile'],dirname(dirname(dirname(__FILE__)))."/Key/".$sinafile);//对经办人手机进行rsa公钥加密
		$data["client_ip"]=get_client_ip();
		ksort($data);									//对签名参数数据排序
		$data['sign'] = $weibopay->getSignMsg($data,$data['sign_type']);//计算签名
		$setdata = $weibopay->createcurl_data($data);
		$result = $weibopay->curlPost($payConfig['sinapay']['mgs'],$setdata);//模拟表单提交
		$rs = checksinaerror($result);//验证
		file_put_contents('log.txt', var_export($rs,true), FILE_APPEND);
		if($rs['response_code'] == "APPLY_SUCCESS"){
			$data['company_status'] = 3;
			$rs = M('members_status')->where("uid=".$this->uid)->save($data);
			$this->success("经办人信息录入成功","/member");
		}else{
			$this->error($rs['response_message'],"/member");
		}
	}
	
    public function safequestion(){
		$isid = M('members_status')->getFieldByUid($this->uid,'safequestion_status');
		if($isid==1){
			$sq = M('member_safequestion')->find($this->uid);
			$this->assign("sq",$sq);
			$this->assign("userphone",M('members')->getFieldById($this->uid,'user_phone'));
		}
		$this->assign("safe_question",$isid);
		$data['html'] = $this->fetch();
		exit(json_encode($data));
    }
	
	public function questionsave(){
		$data['question1'] = text($_POST['q1']);
		$data['question2'] = text($_POST['q2']);
		$data['answer1'] = text($_POST['a1']);
		$data['answer2'] = text($_POST['a2']);
		$data['add_time'] = time();
		$c = M('member_safequestion')->where("uid = {$this->uid}")->count('uid');
		if($c==1) $newid = M("member_safequestion")->where("uid={$this->uid}")->save($data);
		else{
			$data['uid'] = $this->uid;
			$newid = M('member_safequestion')->add($data);
		}
		if($newid){
			M('members_status')->where("uid = {$this->uid}")->setField('safequestion_status',1);
			$newid = setMemberStatus($this->uid, 'safequestion', 1, 6, '安全问题');
			if($newid){
				addInnerMsg($this->uid,"您的安全问题已设置","您的安全问题已设置");
			}
			ajaxmsg();
		}
		else  ajaxmsg("",0);
	}


    public function cellphone(){
		$isid = M('members_status')->getFieldByUid($this->uid,'phone_status');
		$phone = M('members')->getFieldById($this->uid,'user_phone');
		$this->assign("phone",$phone);
		$sq = M('member_safequestion')->find($this->uid);
		$this->assign("sq",$sq);
		$this->assign("phone_status",$isid);
		$datag = get_global_setting();
		$is_manual=$datag['is_manual'];
		$this->assign("is_manual",$is_manual);
		$data['html'] = $this->fetch();
		exit(json_encode($data));
    }

    /*public function sendphone(){
		$smsTxt = FS("Webconfig/smstxt");
		$smsTxt=de_xie($smsTxt);
		$phone = text($_POST['cellphone']);
		$xuid = M('members')->getFieldByUserPhone($phone,'id');
		//if($xuid>0 && $xuid<>$this->uid) ajaxmsg("",2);
		
		$code = rand_string($this->uid,6,1,2);
		$res = sendsms($phone,str_replace(array("#UserName#","#CODE#"),array(session('u_user_name'),$code),$smsTxt['verify_phone']));
		if($res){
			session("temp_phone",$phone);
			ajaxmsg();
		}
		else ajaxmsg("",0);
    }*/
	    public function sendphone(){
		$smsTxt = FS("Webconfig/smstxt");
		$smsTxt=de_xie($smsTxt);
		$phone = text($_POST['cellphone']);
		$xuid = M('members')->getFieldByUserPhone($phone,'id');
		if($xuid>0 && $xuid<>$this->uid) ajaxmsg("",2);
		
		$code = rand_string($this->uid,6,1,2);
		$datag = get_global_setting();
		$is_manual=$datag['is_manual'];
		if($is_manual==0){//如果未开启后台人工手机验证，则由系统向会员自动发送手机验证码到会员手机，
			$res = sendsms($phone,str_replace(array("#UserName#","#CODE#"),array(session('u_user_name'),$code),$smsTxt['verify_phone']));
		}else{//否则，则由后台管理员来手动审核手机验证
			$res = true;
			$phonestatus = M('members_status')->getFieldByUid($this->uid,'phone_status');
			if($phonestatus==1) ajaxmsg("手机已经通过验证",1);
			$updata['phone_status'] = 3;//待审核
			
			$updata1['user_phone'] = $phone;
			$a = M('members')->where("id = {$this->uid}")->count('id');
			if($a==1) $newid = M("members")->where("id={$this->uid}")->save($updata1);
			else{
				M('members')->where("id={$this->uid}")->setField('user_phone',$phone);
			}
			
			$updata2['cell_phone'] = $phone;
			$b = M('member_info')->where("uid = {$this->uid}")->count('uid');
			if($b==1) $newid = M("member_info")->where("uid={$this->uid}")->save($updata2);
			else{
				$updata2['uid'] = $this->uid;
				$updata2['cell_phone'] = $phone;
				M('member_info')->add($updata2);
			}
			$c = M('members_status')->where("uid = {$this->uid}")->count('uid');
			if($c==1) $newid = M("members_status")->where("uid={$this->uid}")->save($updata);
			else{
				$updata['uid'] = $this->uid;
				$newid = M('members_status')->add($updata);
			}
			if($newid){
				ajaxmsg();
			}
			else  ajaxmsg("验证失败",0);
			
			//////////////////////////////////////////////////////////////
		}
		if($res){
			session("temp_phone",$phone);
			ajaxmsg();
		}
		else ajaxmsg("",0);
    }

    public function done(){
		$data['html'] = $this->fetch();
		exit(json_encode($data));
    }

    public function validatephone(){
		$phonestatus = M('members_status')->getFieldByUid($this->uid,'phone_status');
		if($phonestatus==1) ajaxmsg("手机已经通过验证",1);
		if( is_verify($this->uid,text($_POST['code']),2,10*60) ){
			$updata['phone_status'] = 1;
			if(!session("temp_phone")) ajaxmsg("验证失败",0);
			
			$updata1['user_phone'] = session("temp_phone");
			$a = M('members')->where("id = {$this->uid}")->count('id');
			if($a==1) $newid = M("members")->where("id={$this->uid}")->save($updata1);
			else{
				M('members')->where("id={$this->uid}")->setField('user_phone',session("temp_phone"));
			}
			
			$updata2['cell_phone'] = session("temp_phone");
			$b = M('member_info')->where("uid = {$this->uid}")->count('uid');
			if($b==1) $newid = M("member_info")->where("uid={$this->uid}")->save($updata2);
			else{
				$updata2['uid'] = $this->uid;
				$updata2['cell_phone'] = session("temp_phone");
				M('member_info')->add($updata2);
			}
			$c = M('members_status')->where("uid = {$this->uid}")->count('uid');
			if($c==1) $newid = M("members_status")->where("uid={$this->uid}")->save($updata);
			else{
				$updata['uid'] = $this->uid;
				$newid = M('members_status')->add($updata);
			}
			if($newid){
				$newid = setMemberStatus($this->uid, 'phone', 1, 10, '手机');
				ajaxmsg();
				
			}
			else  ajaxmsg("验证失败",0);
		}else{
			ajaxmsg("验证校验码不对，请重新输入！",2);
		}
    }

	public function ajaxupimg(){
		if(!empty($_FILES['imgfile']['name'])){
			$this->fix = false;
			$this->saveRule = date("YmdHis",time()).mt_rand(0,1000)."_{$this->uid}";
			$this->savePathNew = C('MEMBER_UPLOAD_DIR').'Idcard/' ;
			$this->thumbMaxWidth = "1000,1000";
			$this->thumbMaxHeight = "1000,1000";
			$info = $this->CUpload();
			$img = $info[0]['savepath'].$info[0]['savename'];
		}
		if($img){
			$c = M('member_info')->where("uid = {$this->uid}")->count('uid');
			if($c==1){
				$newid = M("member_info")->where("uid={$this->uid}")->setField('card_img',$img);
			}else{
				$data['uid'] = $this->uid;
				$data['card_img'] = $img;
				$newid = M('member_info')->add($data);
			}
			session("idcardimg","1");
			ajaxmsg('',1);
		}
		else  ajaxmsg('',0);
	}

	public function ajaxupimg2(){
		if(!empty($_FILES['imgfile2']['name'])){
			$this->fix = false;
			$this->saveRule = date("YmdHis",time()).mt_rand(0,1000)."_{$this->uid}_back";
			$this->savePathNew = C('MEMBER_UPLOAD_DIR').'Idcard/' ;
			$this->thumbMaxWidth = "1000,1000";
			$this->thumbMaxHeight = "1000,1000";
			$info = $this->CUpload();
			$img = $info[0]['savepath'].$info[0]['savename'];
		}
		if($img){
			$c = M('member_info')->where("uid = {$this->uid}")->count('uid');
			if($c==1){
				$newid = M("member_info")->where("uid={$this->uid}")->setField('card_back_img',$img);
			}else{
				$data['uid'] = $this->uid;
				$data['card_back_img'] = $img;
				$newid = M('member_info')->add($data);
			}
			session("idcardimg2","1");
			ajaxmsg('',1);
		}
		else  ajaxmsg('',0);
	}

    public function face(){
		$this->assign("face",M('members_status')->field("face_status")->where("uid=".$this->uid)->find());
		$data['html'] = $this->fetch();
		exit(json_encode($data));
    }
	public function register3(){
		session("code_temp",NULL);
		session("send_time",NULL);
		session("temp_phone",NULL);
		session("name_temp",NULL);
		session("pwd_temp",NULL);
		$minfo =getMinfo($this->uid,true);
	    $this->assign("uname",$minfo['user_name']);
		$global = get_global_setting();
		$this->assign("reward",$global['reg_reward']);
		$this->display();
	}
	public function register4(){
		session("code_temp",NULL);
		session("send_time",NULL);
		session("temp_phone",NULL);
		session("name_temp",NULL);
		session("pwd_temp",NULL);
		$minfo =getMinfo($this->uid,true);
	    $this->assign("uname",$minfo['user_name']);
		$global = get_global_setting();
		$this->assign("reward",$global['reg_reward']);
		$this->display();
	}
    public function video(){
		$this->assign("video",M('members_status')->field("video_status")->where("uid=".$this->uid)->find());
		$data['html'] = $this->fetch();
		exit(json_encode($data));
    }

	////////////////////////////
	public function changesafe(){
		$map['answer1'] = text($_POST['a1']);
		$map['answer2']  = text($_POST['a2']);
		$map['uid']  = $this->uid;
		$c = M('member_safequestion')->where($map)->count('uid');
		if($c==0) ajaxmsg('',0);
		else{
			session('temp_safequestion',1);
			ajaxmsg();
		}
	}
	public function changesafeact(){
		$is_can = session('temp_safequestion');
		if($is_can==1){
			$data['uid'] = $this->uid;
			$data['question1'] = text($_POST['q1']);
			$data['question2'] = text($_POST['q2']);
			$data['answer1'] = text($_POST['a1']);
			$data['answer2'] = text($_POST['a2']);
			$newid = M('member_safequestion')->save($data);
			if($newid){
				session('temp_safequestion',NULL);
				ajaxmsg();
			}
			else ajaxmsg('',0);
		}else{
			ajaxmsg('',0);
		}
	
	}

	public function sendphonecode(){
		$r = Notice(3,$this->uid);
		if($r) ajaxmsg();
		else ajaxmsg('',0);
	}
	public function sendphonecodex(){
		$p = text($_POST['phone']);
		$c = M('members')->where("user_phone='{$p}'")->count('id');
		if($c>0) ajaxmsg('',2);
		$r = Notice(4,$this->uid,array('phone'=>$p));
		if($r) ajaxmsg();
		else ajaxmsg('',0);
	}
	public function changephone(){
		$vcode = text($_POST['code']);
		$pcode = is_verify($this->uid,$vcode,4,10*60);
		if($pcode){
			session('temp_phone',1);
			ajaxmsg();
		}
		else ajaxmsg('',0);
	}
	public function changephoneact(){
		$xs = session('temp_phone');
		$vcode = text($_POST['code']);
		$pcode = is_verify($this->uid,$vcode,5,10*60);
		if($pcode&&$xs==1){
			$newid = M('members')->where("id={$this->uid}")->setField('user_phone',text($_POST['phone']));
			M('member_info')->where("uid={$this->uid}")->setField('cell_phone',text($_POST['phone']));
			session('temp_phone',NULL);
			$url = $this->modifymobile();
			//$this->error($url);
			if($newid) $this->ajaxReturn($url,'修改认证',1);
			else  ajaxmsg('',0);
		}
		else ajaxmsg('',0);
	}
	
	//新浪修改认证信息
	public function modifymobile(){
		import("@.Oauth.sina.Weibopay");
		$payConfig = FS("Webconfig/payconfig");
		$weibopay = new Weibopay();
		$data['service'] 			  = "modify_verify_mobile";									//接口名称
		$data['version']			  = $payConfig['sinapay']['version'];						//接口版本
		$data['request_time']		  = date('YmdHis');											//请求时间
		$data['partner_id'] 		  = $payConfig['sinapay']['partner_id'];					//合作者身份ID
		$data['_input_charset'] 	  = $payConfig['sinapay']['_input_charset'];				//网站编码格式
		$data['sign_type'] 			  = $payConfig['sinapay']['sign_type'];						//签名方式 MD5
		$data['identity_id'] 		  = '20151008'.$this->uid;									//用户ID
		$data['identity_type']  	  = 'UID';													//用户标识类型
		ksort($data);									//对签名参数数据排序
		$data['sign'] = $weibopay->getSignMsg($data,$data['sign_type']);//计算签名
		$setdata = $weibopay->createcurl_data($data);
		$result =  $weibopay->curlPost($payConfig['sinapay']['mgs'],$setdata);//模拟表单提交
		$deresult = urldecode ( $result );
		$splitdata = array ();
		$splitdata = json_decode ( $deresult, true );
		ksort ( $splitdata ); // 对签名参数据排序
		if ($weibopay->checkSignMsg ($splitdata, $splitdata['sign_type'])) {
			$url=$splitdata['redirect_url'];
		}
		return $url;
	}
	
	public function sendemailtov(){
		$r = Notice(5,$this->uid);
		if($r) ajaxmsg();
		else ajaxmsg('',0);
	}
	
	public function doemailchangephone(){
		$code = text($_POST['safecode']);
		$r = is_verify($this->uid,$code,6,10*60);
		if(!$r) ajaxmsg("",2);
		$map['answer1'] = text($_POST['qone']);
		$map['answer2']  = text($_POST['qtwo']);
		$map['uid']  = $this->uid;
		$c = M('member_safequestion')->where($map)->count('uid');
		if($c==0) ajaxmsg('',0);
		session('temp_phone',1);
		ajaxmsg();
	}
	
	
	public function sendverify(){
		$r = Notice(2,$this->uid);
		if($r) echo(1);
		else echo(0);
	}
	
	public function verifyep(){
		$pcode = is_verify($this->uid,text($_POST['pcode']),3,10*60);
		$ecode = is_verify($this->uid,text($_POST['ecode']),3,10*60);

		if($pcode && $ecode){
			session('temp_safequestion',1);
			ajaxmsg();
		}else{
			ajaxmsg('',0);
		}
	}
	
	///////////////////////////////

}
<?php
// 本类由系统自动生成，仅供测试用途
class UserAction extends MCommonAction {

    public function index(){
        $this->del_mem_cach();
		$this->display();
    }

    public function header(){
		$uid=$this->uid;
		$targetFolder="http://".$_SERVER['SERVER_NAME']."/UF/Uploads/Use/";
		$userinfo=M("members")->where(array("id"=>$uid))->find();
		$this->assign("uid",$uid);
		$this->assign("sessionid",session_id());
		$this->assign("useimg",$userinfo["user_img"]);
		$this->assign("path",$targetFolder);
		$data['html'] = $this->fetch();
		exit(json_encode($data));
    }

	/**
	 * 上传用户头像
	 */
	public function uploadimg(){
		$rootpath=dirname(APP_PATH);
		if(PHP_OS=="WINNT"){
			$rootpath=str_replace("\\","/",$rootpath);
		}
		$basepath="/UF/Uploads/Use/";
		$targetFolder=$rootpath.$basepath;
		if(!is_dir($targetFolder)){
			$flag=mkdir($targetFolder,0777,true);
			if(!$flag){
				logw("创建文件路劲失败");
			}
		}
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");

		if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
			exit; // finish preflight CORS requests here
		}
		if ( !empty($_REQUEST[ 'debug' ]) ) {
			$random = mt_rand(0, intval($_REQUEST[ 'debug' ]) );
			if ( $random === 0 ) {
				header("HTTP/1.0 500 Internal Server Error");
				exit;
			}
		}
// header("HTTP/1.0 500 Internal Server Error");
// exit;
// 5 minutes execution time
		@set_time_limit(5 * 60);
// Uncomment this one to fake upload time
// usleep(5000);
// Settings
// $targetDir = ini_get("upload_tmp_dir") . DIRECTORY_SEPARATOR . "plupload";
		$targetDir = '/tmp/uf';
		$uploadDir=$targetFolder;
		$cleanupTargetDir = true; // Remove old files
		$maxFileAge = 5 * 3600; // Temp file age in seconds
// Create target dir
		if (!file_exists($targetDir)) {
			@mkdir($targetDir);
		}
// Create target dir
		if (!file_exists($uploadDir)) {
			@mkdir($uploadDir);
		}
// Get a file name
		if (isset($_REQUEST["name"])) {
			$fileName = $_REQUEST["name"];
		} elseif (!empty($_FILES)) {
			$fileName = $_FILES["file"]["name"];
		} else {
			$fileName = uniqid("file_");
		}
		$filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;
		$uploadPath = $uploadDir . DIRECTORY_SEPARATOR . $fileName;
// Chunking might be enabled
		$chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
		$chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 1;
// Remove old temp files
		if ($cleanupTargetDir) {
			if (!is_dir($targetDir) || !$dir = opendir($targetDir)) {
				die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "不能打开当前目录."}, "id" : "id"}');
			}
			while (($file = readdir($dir)) !== false) {
				$tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;
				// If temp file is current file proceed to the next
				if ($tmpfilePath == "{$filePath}_{$chunk}.part" || $tmpfilePath == "{$filePath}_{$chunk}.parttmp") {
					continue;
				}
				// Remove temp file if it is older than the max age and is not the current file
				if (preg_match('/\.(part|parttmp)$/', $file) && (@filemtime($tmpfilePath) < time() - $maxFileAge)) {
					@unlink($tmpfilePath);
				}
			}
			closedir($dir);
		}

		if (!$out = @fopen("{$filePath}_{$chunk}.parttmp", "wb")) {
			die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
		}
		if (!empty($_FILES)) {
			if ($_FILES["file"]["error"] || !is_uploaded_file($_FILES["file"]["tmp_name"])) {
				die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
			}
			// Read binary input stream and append it to temp file
			if (!$in = @fopen($_FILES["file"]["tmp_name"], "rb")) {
				die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
			}
		} else {
			if (!$in = @fopen("php://input", "rb")) {
				die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
			}
		}
		while ($buff = fread($in, 4096)) {
			fwrite($out, $buff);
		}
		@fclose($out);
		@fclose($in);
		rename("{$filePath}_{$chunk}.parttmp", "{$filePath}_{$chunk}.part");
		$index = 0;
		$done = true;
		for( $index = 0; $index < $chunks; $index++ ) {
			if ( !file_exists("{$filePath}_{$index}.part") ) {
				$done = false;
				break;
			}
		}
		if ( $done ) {
			if (!$out = @fopen($uploadPath, "wb")) {
				die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
			}
			if ( flock($out, LOCK_EX) ) {
				for( $index = 0; $index < $chunks; $index++ ) {
					if (!$in = @fopen("{$filePath}_{$index}.part", "rb")) {
						break;
					}
					while ($buff = fread($in, 4096)) {
						fwrite($out, $buff);
					}
					@fclose($in);
					@unlink("{$filePath}_{$index}.part");
				}
				flock($out, LOCK_UN);
			}
			@fclose($out);
		}
		M("members")->where(array("id"=>$this->uid))->save(array("user_img"=>$basepath.$fileName));
		die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
		    /**
			$tempFile = $_FILES['Filedata']['tmp_name'];
			$targetPath =$targetFolder;
			$targetFile = rtrim($targetPath,'/') . '/' . $_FILES['Filedata']['name'];
			$fileTypes = array('jpg','jpeg','gif','png'); // File extensions
			$fileParts = pathinfo($_FILES['Filedata']['name']);
			if (in_array($fileParts['extension'],$fileTypes)) {
				move_uploaded_file($tempFile,$targetFile);
				M("members")->where(array("id"=>$this->uid))->save(array("user_img"=>$basepath.$_FILES['Filedata']['name']));
			} else {

			}
			 * **/
	}

    public function password(){
		$data['html'] = $this->fetch();
		exit(json_encode($data));
    }

    public function pinpass(){
		$data['html'] = $this->fetch();
		exit(json_encode($data));
    }

    public function changepass(){
		$old = md5($_POST['oldpwd']);
		$newpwd1 = md5($_POST['newpwd1']);
		$username = M('members')->field('user_name')->where("id={$this->uid}")->find();
		//Java修改密码
		$params['usr_id']=$this->uid;
		$params['user_name'] = $username['user_name'];
		$params['user_pass'] = $old;
		$params['user_pass_new'] = md5($_POST['newpwd1']);
		$params['is_checkoldpass'] = 1;
		import("@.Phpconectjava.usersapi");
		$users = new usersapi();
		$vo = $users->setUsrpwd($params);
		$vo1 = json_decode($vo,true);
		$vo2 = json_decode($vo1['resultText'],true);
		if(is_null($vo1['code'])){
                Log::write("服务器失败");
                ajaxmsg('修改失败！',2);
        }
		if($vo1['code']==-1){
			ajaxmsg($vo1['resultText'],2);
		}
		
		// $c = M('members')->where("id={$this->uid} AND user_pass = '{$old}'")->count('id');
		// if($c==0) ajaxmsg('',2);
		$newid = M('members')->where("id={$this->uid}")->setField('user_pass',$newpwd1);
        $u = M('members')->field('user_phone')->find($this->uid);
        $user_phone = $u['user_phone'];
        $ucontent = "您正在修改链金所平台会员密码，若非您本人注册，请与客服中心联系400-6626-985【链金所-融汇财富，产业帮扶】";
		if($newid){
            sendsms($user_phone,$ucontent);
			ajaxmsg();
            // MTip('chk1',$this->uid);
		}
		else ajaxmsg('',0);
    }

    public function changepin(){
		$old = md5($_POST['oldpwd']);
		$newpwd1 = md5($_POST['newpwd1']);
		$c = M('members')->where("id={$this->uid}")->find();
        $u = M('members')->field('user_phone')->find($this->uid);
        $user_phone = $u['user_phone'];
        $ucontent = "您正在修改链金所平台会员支付密码，若非您本人注册，请与客服中心联系400-6626-985【链金所-融汇财富，产业帮扶】";
		if($old==$newpwd1){
			ajaxmsg("设置失败，请勿让新密码与老密码相同。",0);
		}
		if(empty($c['pin_pass'])){
			if($c['user_pass'] == $old){
				$newid = M('members')->where("id={$this->uid}")->setField('pin_pass',$newpwd1);
				if($newid) ajaxmsg();
				else ajaxmsg("设置失败，请重试",0);
			}else{
				ajaxmsg("原支付密码(即登陆密码)错误，请重试",0);
			}
		}else{
			if($c['pin_pass'] == $old){
				$newid = M('members')->where("id={$this->uid}")->setField('pin_pass',$newpwd1);
				if($newid){
                    // MTip('chk19',$this->uid);
                    sendsms($user_phone,$ucontent);
                    ajaxmsg();
				}else{
                    ajaxmsg("设置失败，请重试",0);
                }
			}else{
				ajaxmsg("原支付密码错误，请重试",0);
			}
		}
    }

    public function phonecode(){
        $u = M('members')->field('user_phone')->find($this->uid);
        $user_phone = $u['user_phone'];
        Notice(11,$this->uid,array('phone'=>$user_phone));
    }

    public function ptchangepin(){
        $ptnewpwd   = md5($_GET['newpwd']); // 新支付密码
        $vcode = text($_GET['some']);
        $pcode = is_verify($this->uid,$vcode,5,10*60);
        if($pcode){
			$newid = M('members')->where("id={$this->uid}")->setField('pin_pass',$ptnewpwd);
			if($newid){
                    ajaxmsg("平台支付密码重置成功",0);
				}else{
                    ajaxmsg("设置失败，请重试",0);
                }
		}
    }

    public function msgset(){
		$this->assign("vo",M('sys_tip')->find($this->uid));
		$data['html'] = $this->fetch();
		exit(json_encode($data));
    }
	
	public function savetip(){
		$oldtip = M('sys_tip')->where("uid={$this->uid}")->count('uid');
		$data['tipset'] = text($_POST['Params']);
		$data['uid'] = $this->uid;
		if($oldtip) $newid = M('sys_tip')->save($data);
		else $newid = M('sys_tip')->add($data);
		//$this->display('Public:_footer');
		if($newid) echo 1;
		else echo 0;
	}

	public function sinapwd(){
		$data['html'] = $this->fetch();
		exit(json_encode($data));
	}
	
	//新浪找回支付密码
	public function setsinapwd(){
		import("@.Oauth.sina.Weibopay");
		$payConfig = FS("Webconfig/payconfig");
		$weibopay = new Weibopay();
		$data['service'] 			  = "find_pay_password";							//绑定认证信息的接口名称
		$data['version']			  = $payConfig['sinapay']['version'];						//接口版本
		$data['request_time']		  = date('YmdHis');											//请求时间
		$data['partner_id'] 		  = $payConfig['sinapay']['partner_id'];					//合作者身份ID
		$data['_input_charset'] 	  = $payConfig['sinapay']['_input_charset'];				//网站编码格式
		$data['sign_type'] 			  = $payConfig['sinapay']['sign_type'];						//签名方式 MD5
		$data['identity_id']		  = "20151008".$this->uid;						//用户ID
		$data['identity_type'] 		  = "UID";													//用户标识类型 UID
		ksort($data);
		$data['sign'] 				  = $weibopay->getSignMsg($data,$data['sign_type']);		//计算签名
		$setdata 					  = $weibopay->createcurl_data($data);
		$result						  = $weibopay->curlPost($payConfig['sinapay']['mgs'],$setdata);//模拟表单提交
		$rs = $this->checksinaerror($result);
		redirect($rs['redirect_url']);
	}
	//新浪验证
	public function checksinaerror($result){
		import("@.Oauth.sina.Weibopay");
		$weibopay = new Weibopay();
		$deresult = urldecode($result);
		$splitdata = array ();
		$splitdata = json_decode( $deresult, true );
		ksort ($splitdata); // 对签名参数据排序
		//$this->error($splitdata);
		if ($weibopay->checkSignMsg ($splitdata,$splitdata["sign_type"]))
		{
			return $splitdata;
		}else{
			return "sing error!" ;
			exit();
		}
	}

}
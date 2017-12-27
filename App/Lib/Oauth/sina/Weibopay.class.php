<?php
/**
 * 微博钱包 api 部分接口封装
 */
class Weibopay {
	/**
	 * getSignMsg 计算前面
	 * 
	 * @param array $pay_params
	 *        	计算前面数据
	 * @param string $sign_type
	 *        	签名类型
	 * @return string $signMsg 返回密文
	 */
	function getSignMsg($pay_params = array(), $sign_type) {
		$sinarsa = C('SINA_RSAFILE');
		$params_str = "";
		$signMsg = "";
		
		foreach ( $pay_params as $key => $val ) {
			if ($key != "sign" && $key != "sign_type" && $key != "sign_version" && isset ( $val ) && @$val != "") {
				$params_str .= $key . "=" . $val . "&";
			}
		}
		$params_str = substr ($params_str, 0, - 1 );
		switch (@$sign_type) {
			case 'RSA' :
				$priv_key = file_get_contents (  dirname(dirname(dirname(__FILE__)))."/Key/".$sinarsa['private']  );
				$pkeyid = openssl_pkey_get_private ( $priv_key );
			 	openssl_sign ( $params_str, $signMsg, $pkeyid, OPENSSL_ALGO_SHA1 );
				openssl_free_key ( $pkeyid );
				$signMsg = base64_encode ( $signMsg );
				break;
			case 'MD5' :
			default :
				$params_str = $params_str . @"wwwccfaxcnzhouwei2850605582";
				$signMsg = strtolower ( md5 ( $params_str ) );
				break;
		}
		return $signMsg;
	}
	/**
	 * 通过公钥进行rsa加密
	 * 
	 * @param type $name
	 *        	Descriptiondata
	 *        	$data 需要进行rsa公钥加密的数据 必传
	 *        	$pu_key 加密所使用的公钥 必传
	 * @return 加密好的密文
	 */
	public function Rsa_encrypt($data, $public_key) {
		$encrypted = "";
		$cert = file_get_contents ( $public_key );
		$pu_key = openssl_pkey_get_public ($cert ); // 这个函数可用来判断公钥是否是可用的
		openssl_public_encrypt ( $data, $encrypted, $pu_key ); // 公钥加密
		$encrypted = base64_encode ( $encrypted ); // 进行编码
		return $encrypted;
	}
	/**
	 * 生成form表单使用的url
	 * @param unknown $pay_url
	 * @param unknown $pay_params
	 * @return string
	 */
	function createRequestUrl_Jump($pay_url,$pay_params=array())
	{
		$params_str = "";
		foreach($pay_params as $key=>$val){
			if(isset($val) && !is_null($val) && @$val!="")
			{
				$params_str .= "&".$key."=".urlencode(trim($val));
			}
		}
		if($params_str)
		{
			$params_str=substr($params_str,1);
		}
		return $pay_url."?".$params_str;
	}
		/**
	 * [createcurl_data 拼接模拟提交数据]
	 *
	 * @param array $pay_params
	 * @return string url格式字符串
	 */
	function createcurl_data($pay_params = array()) {
		$params_str = "";
		foreach ($pay_params as $key => $val ) {
			if (isset ( $val ) && ! is_null ( $val ) && @$val != "") {
				$params_str .= "&" . $key . "=" . urlencode(urlencode ( trim ( $val ) ) );
			}
		}
		if ($params_str) {
			$params_str = substr ($params_str, 1 );
		}
		return $params_str;
	}
	/**
	 * checkSignMsg 回调签名验证
	 * 
	 * @param array $pay_params        	
	 * @param string $sign_type        	
	 * @return boolean
	 */
	function checkSignMsg($pay_params = array(), $sign_type) {
		$sinarsa = C('SINA_RSAFILE');
		$params_str = "";
		$signMsg = "";
		$return = false;
		foreach ( $pay_params as $key => $val ) {
			if ($key != "sign" && $key != "sign_type" && $key != "sign_version" && ! is_null ( $val ) && @$val != "") {
				$params_str .= "&" . $key . "=" . $val;
			}
		}
		if ($params_str){
			$params_str = substr ( $params_str, 1 );
		}
		switch (@$sign_type) {
			case 'RSA' :
				$cert = file_get_contents ( dirname(dirname(dirname(__FILE__)))."/Key/".$sinarsa['public'] );
				$pubkeyid = openssl_pkey_get_public ( $cert );
				$ok = openssl_verify ( $params_str, base64_decode ( $pay_params ['sign'] ), $cert, OPENSSL_ALGO_SHA1 );
				$return = $ok == 1 ? true : false;
				openssl_free_key ( $pubkeyid );
				break;
			case 'MD5' :
			default :
				$params_str = $params_str . "wwwccfaxcnzhouwei2850605582";
				$signMsg = strtolower ( md5 ( $params_str ));
				$return = (@$signMsg == @strtolower ( $pay_params ['sign'] )) ? true : false;
				break;
		}
		return $return;
	}
	/**
	 * [curlPost 模拟表单提交]
	 * 
	 * @param string $url        	
	 * @param string $data        	
	 * @return string $data
	 */
	function curlPost($url, $data) {
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_POST, 1);
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
      	curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
		$data = curl_exec ( $ch );
		curl_close ( $ch );
		return $data;
	}
	/**
	 * 文件摘要算法
	 */
	function md5_file($filename) {
		return md5_file($filename);
	}
	/**
	 * sftp上传企业资质
	 * sftp upload
	 * @param $file 上传文件路径
	 * @return FAIL 失败   SUCCESS 成功
	 */
	function sftp_upload($file,$filename) {
		$sinaupload = C('UPLOAD_ZIP');
		$strServer = $sinaupload["strServer"];
		$strServerPort = "50022";
		$strServerUsername = $sinaupload["strServerUsername"];
		$strServerprivatekey = dirname(dirname(dirname(__FILE__)))."/Key/".$sinaupload["strServerprivatekey"];
		$strServerpublickey = dirname(dirname(dirname(__FILE__)))."/Key/".$sinaupload["strServerpublickey"];
		$resConnection = ssh2_connect ($strServer,$strServerPort);
		if (ssh2_auth_pubkey_file ($resConnection,$strServerUsername,$strServerpublickey,$strServerprivatekey)) {
			$resSFTP = ssh2_sftp ($resConnection);
			file_put_contents ("ssh2.sftp://{$resSFTP}/upload/".$filename,$file);
			if (!copy($file,"ssh2.sftp://{$resSFTP}/upload/".$filename)) {
				return false;
			}else{
				if (file_exists($file)) {
           			 //unlink($file);
           		}
			}
		}else{
			return false;
		}
		return true;
	}
	
	/**	 * sftp下载文件
	 * sftp upload
	 * @param $file 保存zip 下载文件路径
	 * @param $filename 下载文件名称
	 * @return FAIL 失败   SUCCESS 成功
	 */
	function sftp_download($file,$filename) {
		$sinaupload = C('UPLOAD_ZIP');
		$start_time=microtime(true);
		$strServer = $sinaupload["strServer"];
		$strServerPort = "50022";
		$strServerUsername = $sinaupload["strServerUsername"];
		$strServerprivatekey = dirname(dirname(dirname(__FILE__)))."/Key/".$sinaupload["strServerprivatekey"];
		$strServerpublickey = dirname(dirname(dirname(__FILE__)))."/Key/".$sinaupload["strServerpublickey"];
		$resConnection = ssh2_connect ($strServer, $strServerPort );

		if (ssh2_auth_pubkey_file ( $resConnection, $strServerUsername, $strServerpublickey, $strServerprivatekey )) {
			$resSFTP = ssh2_sftp ( $resConnection );
			$opts = array(
				'http'=>array(
					'method'=>"GET",
					'timeout'=>60,
				)
			);
			$context = stream_context_create($opts);
        	$strData = file_get_contents("ssh2.sftp://{$resSFTP}/upload/busiexport/$filename", false, $context);
			if (! file_put_contents($file.$filename, $strData)) {
				file_put_contents('sftplog.txt', '下载失败'.$file.$filename."\n", FILE_APPEND);
				return false;
			}else{
				file_put_contents('sftplog.txt', '下载成功'.$filename."\n", FILE_APPEND);
			}
		}
			return true;
	}
	/**
	 * @param $path 需要创建的文件夹目录
	 * @return bool true 创建成功 false 创建失败
	 */
	function mkFolder($path)
	{
		//self::write_log("开始创建文件夹");
		if (!file_exists($path))
		{
			mkdir($path, 0777,true);
			//self::write_log("文件夹创建成功".$path);
			return true;
		}
		//self::write_log("文件夹创建失败".$path);
		return false;
	}
}

?>

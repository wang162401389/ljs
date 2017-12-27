<?php
/**
 * 天御防护api 接口封装
 */
class TencentProtection extends Action {

	var $SECRET_ID;
	var $SECRET_KEY;
	var $url = 'csec.api.qcloud.com/v2/index.php';
	// function _initialize(){
	// 	$this->SECRET_ID = C("TIANYU.SecretId");
	// 	$this->SECRET_KEY = C("TIANYU.SecretKey");
	// }

	public function RegisterProtection($phone,$ip,$pwd){

		$params['accountType'] = '4';
		$params['uid'] = $phone;
		$params['nickName'] = $phone;
		$params['phoneNumber'] = $phone;
		$params['passwordHash'] = hash('md5',$pwd);
		$params['registerTime'] = time();
		$params['registerIp'] = $ip;
		$params['registerSource'] = '1';
		$params['userAgent'] = $_SERVER['HTTP_USER_AGENT'];
		if($_SERVER['HTTP_X_FORWARDED_FOR']){
			$params['xForwardedFor'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		$params['businessId'] = '1';

	    file_put_contents("TencentProtectionLog.txt", "[".date("Y-m-d H:i:s")."] 注册保护请求参数：".var_export($params,true)."\r\n",FILE_APPEND);
		
		$url = $this->makeURL('GET', 'RegisterProtection', 'szjr', C("TIANYU.SecretId"), C("TIANYU.SecretKey"), $params);
	    $result = $this->sendRequest($url);
	    file_put_contents("TencentProtectionLog.txt", "[".date("Y-m-d H:i:s")."] 注册保护返回信息：".var_export($result,true)."\r\n",FILE_APPEND);
	    return $result;
	}

	/* Generates an available URL */
	private function makeURL($method, $action, $region, $secretId, $secretKey, $args)
	{
	    /* Add common parameters */
	    $args['Nonce'] = (string)rand(0, 0x7fffffff);
	    $args['Action'] = $action;
	    $args['Region'] = $region;
	    $args['SecretId'] = $secretId;
	    $args['Timestamp'] = (string)time();

	    /* Sort by key (ASCII order, ascending), then calculate signature using HMAC-SHA1 algorithm */
	    ksort($args);
	    $args['Signature'] = base64_encode(
	        hash_hmac(
	            'sha1', $method . $this->url . '?' . $this->makeQueryString($args, false),
	            $secretKey, true
	        )
	    );

	    /* Assemble final request URL */

	    return 'https://' . $this->url . '?' . $this->makeQueryString($args, true);
	}

	/* Construct query string from array */
	private function makeQueryString($args, $isURLEncoded)
	{
	    $arr = array();
	    foreach ($args as $key => $value) {
	        if (!$isURLEncoded) {
	            $arr[] = "$key=$value";
	        } else {
	            $arr[] = $key . '=' . urlencode($value);
	        }
	    }
	    return implode('&', $arr);
	}


	/* Basic request URL */
	private function sendRequest($url, $method = 'POST')
	{
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	    if (false !== strpos($url, "https")) {
	        // 证书
	        // curl_setopt($ch,CURLOPT_CAINFO,"ca.crt");
	        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	    }
	    $resultStr = curl_exec($ch);
	    $result = json_decode($resultStr, true);

	    return $result;
	}
}
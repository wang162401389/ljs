<?php
require APP_PATH."Common/Lib.php";
require APP_PATH."Common/DataSource.php";
require APP_PATH."Common/EnumUserStatus.php";
require APP_PATH."Common/EnumCouponType.php";

//require APP_PATH."Common/Refusedcc.php";//防御CC攻击  fan  2013-11-28
function acl_get_key(){
	empty($model)?$model=strtolower(MODULE_NAME):$model=strtolower($model);
	empty($action)?$action=strtolower(ACTION_NAME):$action=strtolower($action);
	
	$keys = array($model,'data','eqaction_'.$action);
	require C('APP_ROOT')."Common/acl.inc.php";
	$inc = $acl_inc;
	
	$array = array();
	foreach($inc as $key => $v){
			if(isset($v['low_leve'][$model])){
				$array = $v['low_leve'];
				continue;
			}
	}//找到acl.inc中对当前模块的定义的数组
	
	$num = count($keys);
	$num_last = $num - 1;
	$this_array_0 = &$array;
	$last_key = $keys[$num_last];
	
	for ($i = 0; $i < $num_last; $i++){
		$this_key = $keys[$i];
		$this_var_name = 'this_array_' . $i;
		$next_var_name = 'this_array_' . ($i + 1);        
		if (!array_key_exists($this_key, $$this_var_name)) {            
			break;       
		}        
		$$next_var_name = &${$this_var_name}[$this_key];    
	}    
	/*取得条件下的数组  ${$next_var_name}得到data数组 $last_key即$keys = array($model,'data','eqaction_'.$action);里面的'eqaction_'.$action,所以总的组成就是，在acl.inc数组里找到键为$model的数组里的键为data的数组里的键为'eqaction_'.$action的值;*/
	$actions = ${$next_var_name}[$last_key];//这个值即为当前action的别名,然后用别名与用户的权限比对,如果是带有参数的条件则$actions是数组，数组里有相关的参数限制
	if(is_array($actions)){
		foreach($actions as $key_s => $v_s){
			$ma = true;
			if(isset($v_s['POST'])){
				foreach($v_s['POST'] as $pkey => $pv){
					switch($pv){
						case 'G_EMPTY';//必须为空
							if( isset($_POST[$pkey]) && !empty($_POST[$pkey]) ) $ma = false;
						break;
					
						case 'G_NOTSET';//不能设置
							if( isset($_POST[$pkey]) ) $ma = false;
						break;
					
						case 'G_ISSET';//必须设置
							if( !isset($_POST[$pkey]) ) $ma = false;
						break;
					
						default;//默认
							if( !isset($_POST[$pkey]) || strtolower($_POST[$pkey]) != strtolower($pv) ) $ma = false;
						break;
					}
				}
			}
			
			if(isset($v_s['GET'])){
				foreach($v_s['GET'] as $pkey => $pv){
					switch($pv){
						case 'G_EMPTY';//必须为空
							if( isset($_GET[$pkey]) && !empty($_GET[$pkey]) ) $ma = false;
						break;
					
						case 'G_NOTSET';//不能设置
							if( isset($_GET[$pkey]) ) $ma = false;
						break;
					
						case 'G_ISSET';//必须设置
							if( !isset($_GET[$pkey]) ) $ma = false;
						break;
					
						default;//默认
							if( !isset($_GET[$pkey]) || strtolower($_GET[$pkey]) != strtolower($pv) ) $ma = false;
						break;
					}
					
				}
			}
			if($ma)	return $key_s;
			else $actions="0";
		}//foreach
	}else{
		return $actions;
	}
}
/**
 * 获取客户端IP地址
 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @param boolean $adv 是否进行高级模式获取（有可能被伪装）
 * @return mixed
 */
function getIp($type = 0,$adv=false) {
	static $ip = NULL;
	if ($ip !== NULL) return $ip;
	if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
		$pos =  array_search('unknown',$arr);
		if(false !== $pos) unset($arr[$pos]);
		$ip   =  trim($arr[0]);
	}elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	}elseif (isset($_SERVER['REMOTE_ADDR'])) {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	// IP地址合法验证
	$ip = (false !== ip2long($ip)) ? $ip : '0.0.0.0';
	return $ip;
}

	//移动支付MD5方式签名
	  function MD5sign($okey,$odata){
	  		$signdata=hmac("",$odata);			     
	  		return hmac($okey,$signdata);
	  }
	  
	function hmac ($key, $data){
		$key = iconv('gb2312', 'utf-8', $key);
		$data = iconv('gb2312', 'utf-8', $data);
		$b = 64;
		if (strlen($key) > $b) {
				$key = pack("H*",md5($key));
		}
		$key = str_pad($key, $b, chr(0x00));
		$ipad = str_pad('', $b, chr(0x36));
		$opad = str_pad('', $b, chr(0x5c));
		$k_ipad = $key ^ $ipad ;
		$k_opad = $key ^ $opad;
		return md5($k_opad . pack("H*",md5($k_ipad . $data)));
	} 

    // /**
    //  * 获取用户状态
    //  * @return 状态 正数表id
    //  *              负数表状态
    //  *        
    //  */
    // function getUserStatus()
    // {
    //     $status = -1;

    //     if(!isset($_SESSION['u_id'])||intval(session('u_id'))===false)
    //     {
    //         $status = -1;
    //         return $status;
    //     }else{
    //     	$status = 1;
    //     }

    //     $info = M('members_status')->find(session('u_id'));

    //     if(null == $info)
    //     {
    //         //
    //         $status = 1000;
    //     }

    //     // 开通新浪支付
    //     if($info['is_pay_passwd'] == 1)
    //     {
    //         $status = $status*3;
    //     }

    //     //实名认证
    //     if($info['id_status'] == 1)
    //     {
    //         $status = $status*5;
    //     }

    //     //绑卡
    //     import("@.Oauth.sina.Sina");
    //     $sina = new Sina();
    //     $bindcard = $sina->querycard(session('u_id'));
    //     if(!empty($bindcard)){
    //         $status = $status*7;
    //     }

    //     //已注册,但未投资
    //     $list = M('borrow_investor')->where(['investor_uid'=>session('u_id')])->select();
    //     if(is_array($list)&&sizeof($list)>0)
    //     {
    //         $status = $status*11;
    //     }

    //     return $status;

    // }
//////////////////////////////////// 第三方支付--移动支付专用 结束 fan 2014-06-07 ////////////////////////////	 

?>
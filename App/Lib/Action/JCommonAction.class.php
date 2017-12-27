<?php
// 全局设置
class JCommonAction extends Action
{
    //验证身份
    function _initialize(){
    	$ip=$_SERVER['REMOTE_ADDR'];
        $this->checkip($ip);
    }
     function checkip($userip){   	
    	//限制ip
    	$ips=require APP_PATH.'Conf/ipconfig.php';   	
    	if(!in_array($userip,$ips)){
    		$this->error('非法ip操作');   		
    		exit;
    	}
    	return true;
	}


}
?>
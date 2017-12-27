<?php
class ApiAction extends HCommonAction {
	public function uc(){
		require C("APP_ROOT")."Lib/Uc/uc.php";
	}

	/**
	 * 注册成功调用接口
	 * @return [type] [description]
	 */
	public function regSuccessApi()
    {
    	// 获得新注册id
    	$uid = 0;
    	$uid = $_GET['uid']?$_GET['uid']:$_POST['uid'];
    	Log::write("*********************************** fengche  uid = ".$uid);
       	regSuccess($uid);
    }

}
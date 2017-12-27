<?php
// 对账管理
class SinasftpAction extends ACommonAction
{
   /**
    +----------------------------------------------------------
    * 默认操作
    +----------------------------------------------------------
    */
    public function index()
    {
    	$this->assign("type", array("zhye-yh-cqg"=>'存钱罐',"jymx-zjtg"=>'交易明细'));
        $this->display();
    }

    public function execut(){
    	$url=C('CCFAXAPI_SINA');
    	$data["time"]=$_POST['time'];
    	$data["type"]=$_POST['type'];
    	$result = $this->curl_post($url,$data);
    	$rs = json_decode($result);
    	$this->ajaxReturn($rs);
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
		return $result;
	}
}
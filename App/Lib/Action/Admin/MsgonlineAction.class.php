<?php
// 全局设置
class MsgonlineAction extends ACommonAction
{
    /**
    +----------------------------------------------------------
    * 默认操作
    +----------------------------------------------------------
    */
    public function index()
    {

		$msgconfig = FS("Webconfig/msgconfig");
		$type = $msgconfig['sms']['type'];// type=0 吉信通短信接口   type=1 漫道短信接口 type=4亿美短信
		$uid1=$msgconfig['sms']['user1']; //分配给你的账号
		$pwd1=$msgconfig['sms']['pass1']; //密码 
		
		$uid2=$msgconfig['sms']['user2']; //分配给你的账号
		$pwd2=$msgconfig['sms']['pass2']; //密码 
		
		$uid3=$msgconfig['sms']['user3']; //分配给你的账号
		$pwd3=$msgconfig['sms']['pass3']; //密码 

		$uid4=$msgconfig['sms']['user4']; //分配给你的账号
		$pwd4=$msgconfig['sms']['pass4']; //密码 
		$sessionkey=$msgconfig['sms']['sessionkey']; //密码 
		if($type==0){
			$d = @file_get_contents("http://121.37.40.49:8009/webservice/public/remoney.asp?uid={$uid1}&pwd={$pwd1}",false);
			if($d<0) $d="用户名或密码错误";
			else $d = "￥".$d;
			$this->assign('winic',$d);
		}else if($type==1){
			$d=@file_get_contents("http://sdk2.zucp.net:8060/webservice.asmx/balance?sn={$uid2}&pwd={$pwd2}",false);
			preg_match('/<string.*?>(.*?)<\/string>/', $d, $matches);
			
			if($matches[1]<0){ 
				switch($matches[1]){
					case -2:
						$d="帐号/密码不正确或者序列号未注册";
					break;
					case -4:
						$d="余额不足";
					break;
					case -6:
						$d="参数有误";
					break;
					case -7:
						$d="权限受限,该序列号是否已经开通了调用该方法的权限";
					break;
					case -12:
						$d="序列号状态错误，请确认序列号是否被禁用";
					break;
					default:
						$d="用户名或密码错误";
					break;
				}
			}else{
				$d = $d."条";
			}
			$this->assign('zucp',$d);
		}elseif($type==4){
			import("@.Oauth.sms.Client");
			$gwUrl = 'http://sdk999ws.eucp.b2m.cn:8080/sdk/SDKService';
			$serialNumber = $uid4;
			$password = $pass4;
			$sessionKey = $sessionkey;
			$connectTimeOut = 2;
			$readTimeOut = 10;
			$client = new Client($gwUrl,$serialNumber,$password,$sessionKey,$proxyhost,$proxyport,$proxyusername,$proxypassword,$connectTimeOut,$readTimeOut);
			$balance = $client->getBalance();
			// $url = "http://sdk999ws.eucp.b2m.cn:8080/sdkproxy/querybalance.action";
   //          $cdkey = $msgconfig['sms']['user4'];
   //          $password = $msgconfig['sms']['pass4'];
   //          $ch = curl_init();
   //          curl_setopt($ch,CURLOPT_URL,$url);
   //          curl_setopt($ch,CURLOPT_POST,true);
   //          curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
   //          $data=array('cdkey' => $cdkey, 'password' => $password);
   //          curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
   //          $balance=curl_exec($ch);
   //          curl_close($ch);
			$this->assign('balance',$balance);
		}else{
			$d = @file_get_contents("http://sdk229ws.eucp.b2m.cn:8080/sdkproxy/querybalance.action?cdkey={$uid3}&password={$pwd3}",false);
			preg_match_all('/<response>(.*)<\/response>/isU',$d,$arr);
			foreach($arr[1] as $k=>$v){
				preg_match_all('#<message>(.*)</message>#isU',$v,$ar[$k]);
				$data[]=$ar[$k][1];
			}
			
			$d = $data[0][0]*10;
			if($d<0) $d="用户名或密码错误";
			else $d = $d."条";
			$this->assign('emay',$d);
		}
		
		$this->assign('stmp_config',$msgconfig['stmp']);
		$this->assign('sms_config',$msgconfig['sms']);
		$this->assign('sms_config_type',$msgconfig['sms']['type']);
		$this->assign('baidu_config',$msgconfig['baidu']);
		$this->assign("type_list", array("3"=>'关闭短信平台服务',"1"=>'漫道短信提供商',"2"=>'亿美软通短信提供商',"0"=>'吉信通短信提供商',"4"=>"亿美短信（新）"));
        $this->display();
    }
    public function save()
    {	$status = $_POST['msg']['sms']['type'];
		if($status=='1'){
			$pwd = $_POST['msg']['sms']['user2'].$_POST['msg']['sms']['pwd'];
			$_POST['msg']['sms']['pass2'] =strtoupper(md5($pwd));//$pwd
			$_POST['msg']['sms']['pwd'] = $_POST['msg']['sms']['pwd'];
		}elseif($status == '4'){
			// import("@.Oauth.sms.Client");
			// $gwUrl = 'http://sdk999ws.eucp.b2m.cn:8080/sdk/SDKService';
			// $serialNumber = $_POST['msg']['sms']['user4'];
			// $password = $_POST['msg']['sms']['pass4'];
			// $sessionKey = $_POST['msg']['sms']['sessionkey'];
			// $connectTimeOut = 2;
			// $readTimeOut = 10;
			// $client = new Client($gwUrl,$serialNumber,$password,$sessionKey,$proxyhost,$proxyport,$proxyusername,$proxypassword,$connectTimeOut,$readTimeOut);
			// $client->setOutgoingEncoding("UTF-8");
			// $statusCode = $client->login($sessionKey);
			// if ($statusCode!=null && $statusCode=="0")
			// {
			// 	//登录成功，并且做保存 $sessionKey 的操作，用于以后相关操作的使用
			// 	file_put_contents('smslog.txt',"登录成功, session key:".$client->getSessionKey(), FILE_APPEND);
			// }else{
			// 	//登录失败处理
			// 	file_put_contents('smslog.txt',"登录失败,返回:".$statusCode, FILE_APPEND);
			// }

		}else{
			// import("@.Oauth.sms.Client");
			// $msgconfig = FS("Webconfig/msgconfig");
			// $gwUrl = 'http://sdk999ws.eucp.b2m.cn:8080/sdk/SDKService';
			// $serialNumber = $msgconfig['sms']['user4'];
			// $password = $msgconfig['sms']['pass4'];
			// $sessionKey = $msgconfig['sms']['sessionkey'];
			// $connectTimeOut = 2;
			// $readTimeOut = 10;
			// $client = new Client($gwUrl,$serialNumber,$password,$sessionKey,$proxyhost,$proxyport,$proxyusername,$proxypassword,$connectTimeOut,$readTimeOut);
			// $statusCode = $client->logout();
			// file_put_contents('messagelog.txt',"序列号:".$serialNumber.",密码：".$password."，key：".$sessionKey, FILE_APPEND);
			// file_put_contents('smslog.txt',"处理状态码:".$statusCode, FILE_APPEND);
		}
		
		FS("msgconfig",$_POST['msg'],"Webconfig/");
		alogs("Msgonline",0,1,'成功执行了通知信息接口的编辑操作！');//管理员操作日志
		$this->success("操作成功",__URL__."/index/");
    }
	
	
    public function templet()
    {
		$emailTxt = FS("Webconfig/emailtxt");
		$smsTxt = FS("Webconfig/smstxt");
		$msgTxt = FS("Webconfig/msgtxt");

		$this->assign('emailTxt',de_xie($emailTxt));
		$this->assign('smsTxt',de_xie($smsTxt));
		$this->assign('msgTxt',de_xie($msgTxt));
        $this->display();
    }
	
    public function templetsave()
    {
		FS("emailtxt",$_POST['email'],"Webconfig/");
		FS("smstxt",$_POST['sms'],"Webconfig/");
		FS("msgtxt",$_POST['msg'],"Webconfig/");
		alogs("Msgonline",0,1,'成功执行了通知信息模板的编辑操作！');//管理员操作日志
		$this->success("操作成功",__URL__."/templet/");
    }
}
?>
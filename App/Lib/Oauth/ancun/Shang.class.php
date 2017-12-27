<?php 
	/**
	 *上上签接口封装
	*/
	class Shang extends Action
	{
		var $url;
		var $ccfaxurl;
		//初始化
		function _initialize(){
			$this->ccfaxurl = C("SHANG_CCFAXURL");
			$this->url = C("SHANG_URL");
		}

		function shanglogin($mobile,$uid,$id_no,$addr,$name){
			$data["mobile"] = $mobile;
			$data["usrid"]  = $uid;
			$data["password"] = "123456";
			$data["identNo"]  = $id_no;
			$data["address"]  = $addr;
			$data["true_name"] = $name;
			$shang_url = $this->ccfaxurl."/ccfax_background/store/shangshangqianLogin.do";
			$result = $this->curl_post($shang_url,$data);
			file_put_contents('shangshang.txt', "CCFAX上上签：".var_export($result,true)."\r\n",FILE_APPEND);
		}

		function hetong($src,$file_name,$user_list,$com_xy){
			$data["contract_pdf"] = new \CURLFile(realpath($src));
			$data["file_name"]  = $file_name;
			$data["receive_user_list"] = $user_list;
			$data["send_user_page_num"] = $com_xy["p"];
			$data["send_user_x"] = $com_xy["x"];//0.01;
			$data["send_user_y"] = $com_xy["y"];//0.53;
			$shang_url = $this->ccfaxurl."/ccfax_background/contract/sendContractAndSign.do";
			// print_r($data);die;
			$result = $this->curl_post($shang_url,$data);
			file_put_contents('shangshang.txt', "合同上上签：".var_export($result,true)."\r\n",FILE_APPEND);
			return json_decode($result,true);
		}

		function gethetong($sign_id,$doc_id){
			$data["sign_id"]  = $sign_id;
			$data["doc_id"] = $doc_id;
			$shang_url = $this->ccfaxurl."/ccfax_background/contract/getSSQContract.do";
			$result = $this->curl_post($shang_url,$data);
			file_put_contents('shangshang.txt', "获取合同上上签：".var_export($result,true)."\r\n",FILE_APPEND);
			return json_decode($result,true);
		}

		//curl Post提交
		function curl_post($url,$data){
			$ch = curl_init ();
			curl_setopt ( $ch, CURLOPT_URL, $url );
			curl_setopt ( $ch, CURLOPT_POST, 1);
			curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );
			curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
			curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
			$result = curl_exec ($ch);
			curl_close($ch);
			return $result;
			
		}
	}
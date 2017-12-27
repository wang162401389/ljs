<?php 
	/**
	* 一块购接口封装 
	*by liaozhaobin
	*/
	class Tsykg extends Action
	{
		var $key=NULL; 				//合作者KEY
		var $getBeanUrl = null;		//获取链金豆地址
		var $addBeanUrl = null;		//赠送链金豆地址

		//初始化
		function _initialize(){
			$config 				= C('TS1KG');
			$this->key 				= $config['key'];
			$this->getBeanUrl		= $config['host']."/api/getUserBean";	
			$this->addBeanUrl		= $config['host']."/api/addBean";	
		}


		/**
		 * 获取链金豆
		 * @param $uid 用户ID
		 * @return 链金豆数量
		 */
		function getBean($uid){
			$data = $this->getBeanUrl."?usr_id=".$uid;
			$result = $this->curGet($data);
			$rs =  $this->checkdata($result);
			if($rs['status'] == 0){
				return $rs['data']['bean'];
			}else{
				file_put_contents('Tsykgerrorlog.txt', "时间：".date("Y-m-d H:i:s")."用户ID：".$uid.",类型：获取欢乐豆，信息:".$rs["msg"]."\n", FILE_APPEND);
				return 0;
			}
		}

		/**
		 * 赠送欢乐豆
		 * @param $uid 用户ID $type(0:首次投资赠送500，1:投资达到条件奖励链金豆)
		 * @return true 成功 false 失败
		 */
		function addBean($uid,$type){
			$data['code'] 			= date('YmdHis').mt_rand( 100000,999999);
			$data['usr_id'] 		= $uid;
			$data['type'] 			= $type;
			$data['timestamp'] 		= time();
			$data['sign'] 			= $this -> getSignMsg($data);
			$result = $this->curlPost($this->addBeanUrl,$data);
			$rs =  $this->checkdata($result);
			if($rs['status'] == 0){
				return true;
			}else{
				file_put_contents('Tsykgerrorlog.txt', "时间：".date("Y-m-d H:i:s")."用户ID：".$uid.",类型：赠送欢乐豆，信息:".$rs["msg"]."\n", FILE_APPEND);
				return flase;
			}
		}

		/**
		 * curlPost提交
		 */
		private function curlPost($url, $data) {
            $ch = curl_init ();
            curl_setopt ( $ch, CURLOPT_URL, $url );
            curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
            curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );
            curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
            curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
            $data = curl_exec ( $ch );
            curl_close ( $ch );
            return $data;
        }

        /**
         * curlGet请求
         */
        private function curGet($url){
            $ch = curl_init ();
            curl_setopt ( $ch, CURLOPT_URL, $url );
            curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
            curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
            curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
            $data = curl_exec ( $ch );
            curl_close ( $ch );
            return $data;
        }

        /**
		 * getSignMsg 计算前面
		 * 
		 * @param array $pay_params
		 *        	计算前面数据
		 * @param string $sign_type
		 *        	签名类型
		 * @return string $signMsg 返回密文
		 */
		private function getSignMsg($pay_params = array(), $sign_type) {
			$params_str = "";
			$signMsg = "";
			
			foreach ( $pay_params as $key => $val ) {
				if ($key != "sign" && $key != "sign_type" && $key != "sign_version" && isset ( $val )) {
					$params_str .= $key . "=" . $val . "&";
				}
			}
			$params_str = $params_str . "key=".$this->key;
			$signMsg = strtoupper ( md5 ( $params_str ) );
			return $signMsg;
		}

        //解析数据
		private function checkdata($data){
			$deresult = urldecode($data);
			$splitdata = array ();
			$splitdata = json_decode( $deresult, true );
			ksort ($splitdata); // 对签名参数据排序
			return $splitdata;

		}
	}
?>

<?php
/**
* 储蓄罐收益
*/
class PiggybankAction extends HCommonAction
{
	
	public function earnings(){
		$pigmodel=M("member_piggbanklog");
		$date=strtotime(date("Y-m-d",time()));//日期的时间戳
		$list=$pigmodel->where(array("time"=>$date))->find();
		if($list){
			$this->error("存钱罐已经处理");
		}else{
			$data = sftp();
			$i=-1;
			foreach ($data as $k => $v) {
				$uid = substr(trim($v[0]),8);
				if(!is_numeric($uid)){
					unset($data[$k]);
				}else{
					$list[$i]['uid'] = $uid;
					$list[$i]['type'] = 88;
					$list[$i]['affect_money'] = $v[4];
					$list[$i]['info'] = "用户存钱罐昨日收益";
					$list[$i]['add_time'] = time();
					$list[$i]['add_ip'] = get_client_ip();
					$list[$i]['target_uname'] = "@sina@";
					$info[$i]['uid'] = $uid;
					$info[$i]['available_balance'] = $v[1];//可用余额
					$info[$i]['amount_frozen'] = $v[2];//冻结余额
					$info[$i]['total_balance'] = $v[3];//总余额
					$info[$i]['earnings_yesterday'] = $v[4];//昨日收益
					$info[$i]['thirty_earnings'] = $v[5];//30日收益
					$info[$i]['total_revenue'] = $v[6];//总收益
					$info[$i]['time'] = time();
				}
				$i++;
			}
			$res = M('member_moneylog')->addAll($list);
			$res1 = M('member_piggybank')->addAll($info);
			if($res&&$res1){
				$pigmodel->add(array("name"=>"用户存钱罐昨日收益","time"=>$date));
				file_put_contents('sftplog.txt', '入库成功'."\n", FILE_APPEND);
			}else{
				file_put_contents('sftplog.txt', '写入数据库失败'.$list."\n", FILE_APPEND);
			}

		}
	}
    /**
	 * 存钱罐收益按照指定日期来下载数据
	 */
	public function toolearn(){
		$mydate=$_GET["date"];
		if(empty($mydate)){
			$this->error("日期不能为空");
		}
		$is_date=strtotime($mydate)?strtotime($mydate):false;
		if($is_date==false){
			$this->error("日期格式错误");
		}
		$pigmodel=M("member_piggbanklog");
		$date=strtotime($mydate);//日期的时间戳
		$list=$pigmodel->where(array("time"=>$date))->find();
		if($list){
			$this->error("存钱罐已经处理");
		}else{
			$data = $this->getdata($mydate);
			$i=-1;
			foreach ($data as $k => $v) {
				$uid = substr(trim($v[0]),8);
				if(!is_numeric($uid)){
					unset($data[$k]);
				}else{
					$list[$i]['uid'] = $uid;
					$list[$i]['type'] = 88;
					$list[$i]['affect_money'] = $v[4];
					$list[$i]['info'] = "用户存钱罐昨日收益";
					$list[$i]['add_time'] = $date;
					$list[$i]['add_ip'] = get_client_ip();
					$list[$i]['target_uname'] = "@sina@";
					$info[$i]['uid'] = $uid;
					$info[$i]['available_balance'] = $v[1];//可用余额
					$info[$i]['amount_frozen'] = $v[2];//冻结余额
					$info[$i]['total_balance'] = $v[3];//总余额
					$info[$i]['earnings_yesterday'] = $v[4];//昨日收益
					$info[$i]['thirty_earnings'] = $v[5];//30日收益
					$info[$i]['total_revenue'] = $v[6];//总收益
					$info[$i]['time'] =$date;
				}
				$i++;
			}
			$res = M('member_moneylog')->addAll($list);
			$res1 = M('member_piggybank')->addAll($info);
			if($res&&$res1){
				$pigmodel->add(array("name"=>"用户存钱罐昨日收益","time"=>$date));
				file_put_contents('sftplog.txt', '入库成功'."\n", FILE_APPEND);
			}else{
				file_put_contents('sftplog.txt', '写入数据库失败'.$list."\n", FILE_APPEND);
			}

		}
	}

	/**
	 * @param $date 年月日，日期字符串
	 * @return array
	 */
	private function getdata($date){
		import("@.Oauth.sina.Weibopay");
		$weibopay = new Weibopay();
		$filename = array();
		$filename["zhye-yh-cqg"] = 'zhye-yh-cqg';//存钱罐账户余额及收益
		$filetype = ".zip";//目前对账文件都是打成zip压缩包提供下载
		//按照对账日期创建文件夹
		$zipflo=dirname(dirname(dirname(__FILE__)))."/UF/tmp/zip" . $date."/";
		$unzipflo=dirname(dirname(dirname(__FILE__)))."/UF/tmp/zip" .$date."/unzip/";
		$weibopay->mkFolder($zipflo);
		$weibopay->mkFolder($unzipflo);
		$zip = new ZipArchive;
		foreach ($filename as $key => $value) {
			$result = $weibopay->sftp_download($zipflo, $date . "_" . $value . $filetype);
			if($result){
				$res = $zip->open($zipflo.$date . "_" . $value . $filetype);
				if ($res === TRUE) {
					//解压缩到文件夹
					$serveresult=$zip->extractTo($unzipflo);
					if($serveresult){
						file_put_contents('sftplog.txt', '保存成功'.$unzipflo."\n", FILE_APPEND);
					}else{
						cunqianguan_filelog($date);
						file_put_contents('sftplog.txt', '保存失败'.$unzipflo."\n", FILE_APPEND);
						die();
					}
					$zip->close();
				}else{
					cunqianguan_filelog($date);
					file_put_contents('sftplog.txt', '解压缩失败'.$zipflo.$date . "_" . $value . $filetype."\n", FILE_APPEND);
					die();
				}
			}else{
				cunqianguan_filelog($date);
				file_put_contents('sftplog.txt', '下载失败'.$unzipflo."\n", FILE_APPEND);
				die();
			}
		}
		$handler = opendir($unzipflo);
		while( ($filename = readdir($handler)) !== false )
		{
			if($filename !="." && $filename !=".."){
				$row = 1;
				$file=$unzipflo.$filename;
				$handle = fopen($file,"r");
				$resultarray=array();
				while ($data = fgetcsv($handle)) {
					//统计数据行数
					$num = count($data);
					$row++;
					//对数组进行迭代，迭代每条数据
					for ($c = 0; $c < $num; $c++) {
						//注意中文乱码问题
						$data[$c] = iconv("gbk", "utf-8//IGNORE", $data[$c]);
						//将数据放在2维数组进行存放
						$resultarray[$row][$c] = $data[$c];
					}
				}
				fclose($handle);
			}
		}
		closedir($handler);
		return $resultarray;
	}

}

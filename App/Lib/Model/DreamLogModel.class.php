<?php
// +----------------------------------------------------------------------
// | ThinkPHP
// +----------------------------------------------------------------------
// | Copyright (c) 2008 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// $Id$

// 会员模型
class DreamLogModel extends ACommonModel {


	public function songLog($record,$uid)
	{
		$minfo = M('members')->find($uid);
		if(null==$minfo){
			throw new Exception("user of id {$uid} not exist!", 1);
		}
		$data['create_time'] = time();
		$data['desc']        = "20179月活动送获奖".$record['value'].',获奖人'.$minfo['user_phone'].',id'.$uid;
		$data['type']        = 1001;
		return $this->add($data);
	}

	public function zaLog($record,$uid)
	{
		$minfo = M('members')->find($uid);
		if(null==$minfo){
			throw new Exception("user of id {$uid} not exist!", 1);
		}
		$data['create_time'] = time();
		$data['desc']        = "20179月活动砸获奖".$record['value'].',获奖人'.$minfo['user_phone'].',id'.$uid;
		$data['type']        = 1002;
		return $this->add($data);
	}

	public function qiangLog($record,$uid)
	{
		$minfo = M('members')->find($uid);
		if(null==$minfo){
			throw new Exception("user of id {$uid} not exist!", 1);
		}
		$data['create_time'] = time();
		$data['desc']        = "20179月活动抢获奖".$record['value'].',获奖人'.$minfo['user_phone'].',id'.$uid;
		$data['type']        = 1003;
		return $this->add($data);
	}

	public function newUserLog($uid)
	{
		$minfo = M('members')->find($uid);
		if(null==$minfo){
			throw new Exception("user of id {$uid} not exist!", 1);
		}
		$data['create_time'] = time();
		$data['desc']        = "{$uid} register as pro9 new user";
		$data['type']        = 1004;
		return $this->add($data);
	}



	public function newUserLog2($uid)
	{
		$minfo = M('members')->find($uid);
		if(null==$minfo){
			throw new Exception("user of id {$uid} not exist!", 1);
		}
		$data['create_time'] = time();
		$data['desc']        = "{$uid} register as pro9 new user for song";
		$data['type']        = 1005;
		return $this->add($data);
	}

	public function p9RecommendLog($uid,$parent_id)
	{
		$minfo = M('members')->find($uid);
		if(null==$minfo){
			throw new Exception("user of id {$uid} not exist!", 1);
		}
		$data['create_time'] = time();
		$data['desc']        = "{$uid} recommended by {$parent_id}";
		$data['type']        = 1006;
		return $this->add($data);
	}

	public function p9RecommendErrorLog($uid,$parent_id)
	{
		$minfo = M('members')->find($uid);
		if(null==$minfo){
			throw new Exception("user of id {$uid} not exist!", 1);
		}
		$data['create_time'] = time();
		$data['desc']        = "{$uid} recommended by {$parent_id} error ";
		$data['type']        = 1007;
		return $this->add($data);
	}

	public function p9RechargeLog($uid)
	{
		$minfo = M('members')->find($uid);
		if(null==$minfo){
			throw new Exception("user of id {$uid} not exist!", 1);
		}
		$data['create_time'] = time();
		$data['desc']        = "{$uid} recharge ,add count_1 by 1";
		$data['type']        = 1008;
		return $this->add($data);
	}

	

	public function decrease1($uid)
	{
		$minfo = M('members')->find($uid);
		if(null==$minfo){
			throw new Exception("user of id {$uid} not exist!", 1);
		}
		$data['create_time'] = time();
		$data['desc']        = "{$uid} za success ,count_1 - 1";
		$data['type']        = 1009;
		return $this->add($data);
	}

	public function decrease2($uid)
	{
		$minfo = M('members')->find($uid);
		if(null==$minfo){
			throw new Exception("user of id {$uid} not exist!", 1);
		}
		$data['create_time'] = time();
		$data['desc']        = "{$uid} qiang success count -1";
		$data['type']        = 1010;
		return $this->add($data);
	}

	public function decrease0($uid)
	{
		$minfo = M('members')->find($uid);
		if(null==$minfo){
			throw new Exception("user of id {$uid} not exist!", 1);
		}
		$data['create_time'] = time();
		$data['desc']        = "{$uid} song success ,count_1 - 1";
		$data['type']        = 1011;
		return $this->add($data);
	}

	public function decreaseNum($uid,$id,$prizename,$curno1=0,$curno2=0)
	{
		$minfo = M('members')->find($uid);
		if(null==$minfo){
			throw new Exception("user of id {$uid} not exist!", 1);
		}
		$data['create_time'] = time();
		$data['desc']        = "{$id} {$prizename} decrease by 1 by {$uid}, curno1=$curno1, curno2=$curno2";
		$data['type']        = 1012;
		return $this->add($data);
	}

	public function newUserInvestLog($uid, $money)
	{
		$minfo = M('members')->find($uid);
		if(null==$minfo){
			throw new Exception("user of id {$uid} not exist!", 1);
		}
		$data['create_time'] = time();
		$data['desc']        = "{$uid} invest {$money}, qualified as new user";
		$data['type']        = 1013;
		return $this->add($data);
	}
	

	public function p9RechargeErrorLog($uid)
	{
		$minfo = M('members')->find($uid);
		if(null==$minfo){
			throw new Exception("user of id {$uid} not exist!", 1);
		}
		$data['create_time'] = time();
		$data['desc']        = "{$uid} recharge error";
		$data['type']        = 1014;
		return $this->add($data);
	}

	public function refreshQiangLog(){
		$data['create_time'] = time();
		$dd = date("Y-m-d H:i:s", time());
		$data['desc']        = " 更新抢活动奖品数量 {$dd}";
		$data['type']        = 10015;
		return $this->add($data);
	}

	public function huodong201711Regist($uid){
		$data['create_time'] = time();
		$minfo = M('members')->find($uid);
		if(null==$minfo){
			throw new Exception("user of id {$uid} not exist!", 1);
		}
		$dd = date("Y-m-d H:i:s", time());
		$data['desc']        = "{$dd} {$uid}--{$minfo['user_phone']} recommend by {$minfo['recommend_id']}, 201711huodong aligible";
		$data['type']        = 10016;
		return $this->add($data);	
	}

	public function huodong201711FirstInvestLog($uid, $money, $bid){
		$data['create_time'] = time();
		$minfo = M('members')->find($uid);
		if(null==$minfo){
			throw new Exception("user of id {$uid} not exist!", 1);
		}
		$dd = date("Y-m-d H:i:s", time());
		$data['desc']        = "201711 activity: {$uid} first {$bid},invest money {$money}";
		$data['type']        = 10017;
		return $this->add($data);	
	}

	public function huodong201711InvestTotalLog($uid, $money){
		$data['create_time'] = time();
		$minfo = M('members')->find($uid);
		if(null==$minfo){
			throw new Exception("user of id {$uid} not exist!", 1);
		}
		$dd = date("Y-m-d H:i:s", time());
		$data['desc']        = "201711 activity: {$uid}  invest {$money}";
		$data['type']        = 10018;
		return $this->add($data);	
	}

	public function huodong201711FirstInvestReleaseLog($uid, $bid, $money){
		$data['create_time'] = time();
		$minfo = M('members')->find($uid);
		if(null==$minfo){
			throw new Exception("user of id {$uid} not exist!", 1);
		}
		$dd = date("Y-m-d H:i:s", time());
		$data['desc']        = "201711 activity: uid:{$uid} bid:{$bid} money:{$money} release success";
		$data['type']        = 10019;
		return $this->add($data);	
	}



	public function songExceptionLog($record,$uid)
	{
		$minfo = M('members')->find($uid);
		$data['create_time'] = time();
		$data['desc']        = "20179月活动送获奖error".$record['value'].',获奖人'.$minfo['user_phone'].'id'.$uid;
		$data['type']        = 1999;
		return $this->add($data);	
	}

	public function zaExceptionLog($record,$uid)
	{
		$minfo = M('members')->find($uid);
		$data['create_time'] = time();
		$data['desc']        = "20179月活动送获奖error".$record['value'].',获奖人'.$minfo['user_phone'].'id'.$uid;
		$data['type']        = 1998;
		return $this->add($data);	
	}

	public function qiangExceptionLog($record,$uid)
	{
		$minfo = M('members')->find($uid);
		$data['create_time'] = time();
		$data['desc']        = "20179月活动送获奖error".$record['value'].',获奖人'.$minfo['user_phone'].'id'.$uid;
		$data['type']        = 1997;
		return $this->add($data);	
	}

	
}
?>
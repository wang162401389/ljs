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
class Huodong201711CountModel extends ACommonModel {
	
	/**
	 * 减少抽奖次数
	 * @param  [type] $index [description]
	 * @return [type]        [description]
	 */
	public function descCount($uid,$index = 1)
	{
		$dreamLogModel = new DreamLogModel();
		$con['uid'] = $uid;
		$update['count_'.$index] = array('exp','count_'.$index.'-1');
		if($this->where($con)->save($update) !== false)
		{
			//记录日志
			if($index == 1)
			{
				$dreamLogModel->decrease1($uid);	
			}else{
				$dreamLogModel->decrease2($uid);		
			}
			
			return true;
		}else{
			throw new Exception(mysql_error(), 1);
		}
	}

	/**
	 * 获取201711首投用户名单
	 * @return [type] [description]
	 */
	public function get201711UnreleaseList($bid)
	{
		$con['a.bid'] = $bid;
		$con['a.first_invest'] = array("NEQ", 0);
		$con['a.is_released'] = 0;
		
		$result = M('huodong_201711_count a')->join('lzh_huodong_201711_detail d on a.id = d.count_id')
											 ->field("a.*,d.invest,d.rebate,d.bid,d.days,d.create_time as invest_time")
											 ->where($con)
											 ->group("a.uid")
											 ->select();
		return $result;
	}

	public function get201711ReleaseTotalList($uid)
	{
		//$con['a.is_released'] = 1;
		$con['a.parent_id'] = $uid;
		$result = M('huodong_201711_count a')->join('lzh_huodong_201711_detail d on a.id = d.count_id')
											 ->field("a.user_phone,a.create_time as reg_time,d.invest,d.rebate,d.bid,d.days,d.create_time as invest_time,a.is_released")
											 ->where($con)
										     ->select();


		if(false === $result)
			return false;

		if(null === $result)
			return [];

		foreach ($result as $key => $value) {
			$result[$key]['invest'] = getFloatValue($value['invest'], 2);
			$result[$key]['reg_time'] = date("Y.m.d", $result[$key]['reg_time']);
			$result[$key]['user_phone'] = substr($result[$key]['user_phone'],0,3)."*****".substr($result[$key]['user_phone'],8,11);
			$result[$key]['rebate'] = getFloatValue($value['rebate'], 2);
		    $result[$key]['rebate'] = $result[$key]['rebate']==null?getFloatValue(0, 2):$result[$key]['rebate'];
		}
		return $result;
	}

	/**
	 * 获取好友首投列表
	 * @param  [type] $uid [description]
	 * @return [type]      [description]
	 */
	public function get201711ReleaseFirstList($uid)
	{
		//$con['is_released'] = 1;
		$con['parent_id'] = $uid;
		$result = M('huodong_201711_count')->where($con)
										   ->field("parent_id,uid,user_phone,create_time as reg_time,first_invest,is_released")
										   ->select();

		if (false === $result)
			return $result;

		if(null === $result)
			return [];

		foreach ($result as $key => $value) {
			if($value['first_invest'] < 5000) {
				$result[$key]['rebate'] = getFloatValue(0, 2);
			} elseif($value['first_invest'] < 10000) {
				$result[$key]['rebate'] = getFloatValue(30, 2);
			} elseif($value['first_invest'] < 20000) {
				$result[$key]['rebate'] = getFloatValue(60, 2);
			} elseif($value['first_invest'] < 50000) {
				$result[$key]['rebate'] = getFloatValue(200, 2);
			} elseif($value['first_invest'] < 100000) {
				$result[$key]['rebate'] = getFloatValue(500, 2);
			} else {
				$result[$key]['rebate'] = getFloatValue(1200, 2);
			}

			$result[$key]['first_invest'] = getFloatValue($value['first_invest'], 2);
			$result[$key]['reg_time'] = date("Y.m.d", $result[$key]['reg_time']);
			$result[$key]['user_phone'] = substr($result[$key]['user_phone'],0,3)."*****".substr($result[$key]['user_phone'],8,11);

		}
		return $result;
	}


}
?>
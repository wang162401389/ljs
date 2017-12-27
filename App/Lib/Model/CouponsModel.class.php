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
class CouponsModel extends ACommonModel {

	/**
	 * 赠送加息券,默认30天有效期
	 * @param [type] $uid   [description]
	 * @param [type] $value [description]
	 */
	public function addInterestCoupon($uid,$value,$source,$operator="system")
	{
		$minfo = M('members')->find($uid);
		$data['user_phone']      = $minfo['user_phone'];
		
		$data['status']          = 0;
		$data['serial_number']   = 0;
		$data['type']            = EnumCouponType::Interest;
		$data['name']            = $source;
		$data['addtime']         = date("Y-m-d H:i:s",time());
		$data['isexperience']    = 0;
		$data["serial_number"]   = time() . rand(100000, 999999);

		$data['money']           = $value;
		$data['use_money']       = 0;
		$data['endtime']         = time()+86400*30;
		if(abs($value-0.5)/0.5<0.00001){
			$data['use_money']       = 500;
			$data['endtime']         = time()+86400*30;
		}elseif(abs($value-1.0)/1.0<0.00001){
			$data['use_money']       = 1001;
			$data['endtime']         = time()+86400*30;
		}elseif(abs($value-1.5)/1.5<0.00001){
			$data['use_money']       = 5001;
			$data['endtime']         = time()+86400*7;
		}elseif (abs($value-2.0)/2.0<0.00001) {
			$data['use_money']       = 30001;
			$data['endtime']         = time()+86400*7;
		}

		$data['admin']           = 0;
		$data['admin_name']      = $operator;
		$data['min_investrange'] = 0;
		$this->add($data);
	}

	// /**
	//  * 赠送加息券,默认30天有效期
	//  * @param [type] $uid   [description]
	//  * @param [type] $value [description]
	//  */
	// public function addCouponFor9($uid,$value,$source,$operator="system")
	// {
	// 	$minfo = M('members')->find($uid);
	// 	$data['user_phone']      = $minfo['user_phone'];
	// 	$data['money']           = $value;
	// 	$data['endtime']         = time()+86400*30;
	// 	$data['status']          = 0;
	// 	$data['serial_number']   = 0;
	// 	$data['type']            = EnumCouponType::Interest;
	// 	$data['name']            = $source;
	// 	$data['addtime']         = time();
	// 	$data['isexperience']    = 0;
	// 	$data['use_money']       = 0;
	// 	$data['admin']           = 0;
	// 	$data['admin_name']      = $operator;
	// 	$data['min_investrange'] = 0;
	// 	$this->add($data);
	// }

	/**
	 * 赠送投资券,默认30天有效期
	 * @param [type] $uid   [description]
	 * @param [type] $value [description]
	 */
	public function addInvestCouponFor9($uid,$value,$source,$operator="system")
	{
		$minfo = M('members')->find($uid);
		$data['user_phone']      = $minfo['user_phone'];
		$data['money']           = $value;
		$data['endtime']         = time()+86400*30;
		$data['status']          = 0;
		$data["serial_number"]   = time() . rand(100000, 999999);
		$data['type']            = EnumCouponType::Invest;
		$data['name']            = $source;
		$data['addtime']         = date("Y-m-d H:i:s",time());
		$data['isexperience']    = 0;
		$data['use_money']       = intval($value*100);
		$data['admin']           = 0;
		$data['admin_name']      = $operator;
		$data['min_investrange'] = 0;
		$this->add($data);
	}

	public function addInvestCoupon($uid,$value,$source,$operator="system")
	{

	}

}
?>
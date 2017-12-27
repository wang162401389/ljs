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
class P9PrizeModel extends ACommonModel {
	
	/**
	 * 减少抽奖次数
	 * @param  [type] $index [description]
	 * @return [type]        [description]
	 */
	public function descNum($id,$uid,$prizename)
	{
		$dreamLogModel = new DreamLogModel();
		$con['id'] = $id;
		$update['num_left'] = array('exp','num_left-1');

		$minfo = M('p9_count')->where(['uid'=>$uid])->find();

		if($this->where($con)->save($update) !== false)
		{
			$dreamLogModel->decreaseNum($uid,$id,$prizename,$minfo['count_1'],$minfo['count_2']);	
			return true;
		}else{
			throw new Exception(mysql_error(), 1);
		}
	}

	/**
	 * 每日凌晨 1 点,更新抢奖品次数
	 * @return [type] [description]
	 */
	public function refreshQiang()
	{
		$dreamLogModel = new DreamLogModel();
		$con['active_type'] = 3;
		$update['num_left'] = array('exp','num_total');
		$result =  $this->where($con)->save($update);
		if($result!==false){
			$dreamLogModel->refreshQiangLog();
		}
		return $result;
	}
}
?>
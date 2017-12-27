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
class P9CountModel extends ACommonModel {
	
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
}
?>
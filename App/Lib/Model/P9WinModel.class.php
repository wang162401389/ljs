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
class P9WinModel extends ACommonModel {
	protected $tableName = 'p9_win';


	/**
	 * 记录送出去的奖品
	 * @param  [type] $record [description]
	 * @param  [type] $uid    [description]
	 * @return [type]         [description]
	 */
	public function insertSongRecord($record,$uid)
	{	
		$minfo = M('members')->find($uid);
		$data['uid']         = $uid;
		$data['user_phone']  = $minfo['user_phone'];
		$data['value']       = $record['value'];
		$data['name']        = $record['info'];
		$data['create_time'] = time();
		$data['desc']        = "201709送";
		$data['type']        = "0";
        $this->add($data);
	}

	/**
	 * 记录"砸"的奖品,实物活动之后送,券或者现金及时送
	 * @param  [type] $record [description]
	 * @param  [type] $uid    [description]
	 * @return [type]         [description]
	 */
	public function insertZaRecord($record,$uid)
	{	
		$minfo = M('members')->find($uid);
		$data['uid']         = $uid;
		$data['user_phone']  = $minfo['user_phone'];
		$data['value']       = $record['value'];
		$data['name']        = $record['info'];
		$data['create_time'] = time();
		if($record['type'] == 0)
		{
			$data['status'] = 1;
		}elseif($record['type'] == 3){
			$data['status'] = 1;
		}
		$data['desc']        = "201709砸";
		$data['type']        = "1";
        $this->add($data);
	}

	
	/**
	 * 记录"砸"的奖品,实物活动之后送,券或者现金及时送
	 * @param  [type] $record [description]
	 * @param  [type] $uid    [description]
	 * @return [type]         [description]
	 */
	public function insertQiangRecord($record,$uid)
	{	
		$minfo = M('members')->find($uid);
		$data['uid']         = $uid;
		$data['user_phone']  = $minfo['user_phone'];
		$data['value']       = $record['value'];
		$data['name']        = $record['info'];
		$data['create_time'] = time();
		if($record['type'] == 0)
		{
			$data['status'] = 1;
		}
		$data['desc']        = "201709抢";
		$data['type']        = "2";
        $this->add($data);
	}

	/**
	 * 是否达到每天5次打砸冰上限
	 */
	public function  zaReachLimit()
	{
		
		$con['uid'] = session('u_id');
		$con['value'] = array('EGT',0);
		$con['type'] = 1;
		$morning = strtotime(date('Y-m-d 00:00:00',time()));
		$evening = strtotime(date('Y-m-d 23:59:59',time()));
		$con['create_time'] = array('between',$morning.','.$evening);
		$count = $this->where($con)->count();
		return $count;
	}

	/**
	 * 是否达到每天5次打砸冰上限
	 */
	public function  qiangReachLimit()
	{
		
		$con['uid'] = session('u_id');
		$con['value'] = array('GT',0);
		$con['type'] = 2;
		$morning = strtotime(date('Y-m-d 00:00:00',time()));
		$evening = strtotime(date('Y-m-d 23:59:59',time()));
		$con['create_time'] = array('between',$morning.','.$evening);
		$count = $this->where($con)->count();
		return $count;
	}

	/**
	 * 减少奖品个数
	 * @param  [type] $index [description]
	 * @return [type]        [description]
	 */
	public function descNum($uid,$id,$prizename)
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
	 * 今天剩余的加息券个数
	 * @return [type] [description]
	 */
	public function icLeft()
	{
		// $morning = strtotime(date('Y-m-d 00:00:00',time()));
		// $evening = strtotime(date('Y-m-d 23:59:59',time()));
		// $con['create_time'] = array('between',$morning.','.$evening);
		// $con['type'] = 2;
		// $count = $this->where($con)->count();

		$numleft = new P9PrizeModel();
		return $numleft->where(['active_type'=>3])->sum('num_left');
		//return 100-$count;
	}

	/**
	 * 今天抢次数
	 * @param  [type] $uid [description]
	 * @return [type]      [description]
	 */
	public function timesOfToday($uid)
	{
		$morning = strtotime(date('Y-m-d 00:00:00',time()));
		$evening = strtotime(date('Y-m-d 23:59:59',time()));
		$con['create_time'] = array('between',$morning.','.$evening);
		$con['uid'] = $uid;
		$con['type'] = 2;
		$count = $this->where($con)->count();
		return $count;
	}
}
?>
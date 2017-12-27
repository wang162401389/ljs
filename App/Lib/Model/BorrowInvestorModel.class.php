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

// 管理用户模型
class BorrowInvestorModel extends ACommonModel {
	protected $tableName = 'borrow_investor';

	/**
     * 获得已注册但是未投资的用户Id 列表
     * @return [type] [description]
     */
    public function getInvestedIdList($start_time=null,$end_time=null)
    {
    	//$con['status'] = 4;
    	$con = [];
    	if($start_time!=null)
    	{
    		$con['add_time'] = array('gt',$start_time);
    	}
    	if($end_time!=null)
    	{
    		$con['add_time'] = array('lt',$end_time);
    	}
        $list = $this->where($con)->select();
        return array_column($list, 'investor_uid');
    }
}
?>